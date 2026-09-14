<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * provider.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_checkin\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_user_data_provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("checkin", [
            "teacheripuserid" => "privacy:metadata:checkin:teacheripuserid",
            "teacherip" => "privacy:metadata:checkin:teacherip",
            "teacheriptime" => "privacy:metadata:checkin:teacheriptime",
            "teacherlocationuserid" => "privacy:metadata:checkin:teacherlocationuserid",
            "teacherlatitude" => "privacy:metadata:checkin:teacherlatitude",
            "teacherlongitude" => "privacy:metadata:checkin:teacherlongitude",
            "teacheraccuracy" => "privacy:metadata:checkin:teacheraccuracy",
            "teacherlocationtime" => "privacy:metadata:checkin:teacherlocationtime",
        ], "privacy:metadata:checkin");

        $collection->add_database_table("checkin_records", [
            "userid" => "privacy:metadata:records:userid",
            "timecreated" => "privacy:metadata:records:timecreated",
            "ipaddress" => "privacy:metadata:records:ipaddress",
            "latitude" => "privacy:metadata:records:latitude",
            "longitude" => "privacy:metadata:records:longitude",
            "accuracy" => "privacy:metadata:records:accuracy",
            "distance" => "privacy:metadata:records:distance",
        ], "privacy:metadata:records");

        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {checkin} c ON c.id = cm.instance
             LEFT JOIN {checkin_records} r ON r.checkinid = c.id AND r.userid = :recorduserid
                 WHERE ctx.contextlevel = :contextlevel
                   AND (r.id IS NOT NULL OR c.teacheripuserid = :ipuserid OR c.teacherlocationuserid = :locationuserid)";
        $params = [
            "modname" => "checkin",
            "recorduserid" => $userid,
            "contextlevel" => CONTEXT_MODULE,
            "ipuserid" => $userid,
            "locationuserid" => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return mixed Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $checkin = self::get_checkin_for_context($context);
            if (!$checkin) {
                continue;
            }

            $export = new \stdClass();
            $record = $DB->get_record("checkin_records", ["checkinid" => $checkin->id, "userid" => $userid]);
            if ($record) {
                $export->record = (object)[
                    "time" => transform::datetime($record->timecreated),
                    "ipaddress" => $record->ipaddress,
                    "latitude" => $record->latitude,
                    "longitude" => $record->longitude,
                    "accuracy" => $record->accuracy,
                    "distance" => $record->distance,
                ];
            }

            if ((int)$checkin->teacheripuserid === $userid || (int)$checkin->teacherlocationuserid === $userid) {
                $reference = new \stdClass();
                if ((int)$checkin->teacheripuserid === $userid) {
                    $reference->ipaddress = $checkin->teacherip;
                    $reference->iptime = $checkin->teacheriptime ? transform::datetime($checkin->teacheriptime) : null;
                }
                if ((int)$checkin->teacherlocationuserid === $userid) {
                    $reference->latitude = $checkin->teacherlatitude;
                    $reference->longitude = $checkin->teacherlongitude;
                    $reference->accuracy = $checkin->teacheraccuracy;
                    $reference->locationtime = $checkin->teacherlocationtime
                        ? transform::datetime($checkin->teacherlocationtime)
                        : null;
                }
                $export->reference = $reference;
            }

            if (!empty((array)$export)) {
                writer::with_context($context)->export_data([], $export);
            }
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param context $context Parameter context.
     * @return mixed Return value.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        $checkin = self::get_checkin_for_context($context);
        if (!$checkin) {
            return;
        }

        $DB->delete_records("checkin_records", ["checkinid" => $checkin->id]);
        self::clear_all_reference_data($checkin->id);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return mixed Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $checkin = self::get_checkin_for_context($context);
            if (!$checkin) {
                continue;
            }

            $DB->delete_records("checkin_records", ["checkinid" => $checkin->id, "userid" => $userid]);
            self::clear_user_reference_data($checkin, $userid);
        }
    }

    /**
     * Method get_users_in_context.
     *
     * @param userlist $userlist Parameter userlist.
     * @return mixed Return value.
     */
    public static function get_users_in_context(userlist $userlist) {
        $checkin = self::get_checkin_for_context($userlist->get_context());
        if (!$checkin) {
            return;
        }

        $userlist->add_from_sql("userid", "SELECT userid FROM {checkin_records} WHERE checkinid = :checkinid", [
            "checkinid" => $checkin->id,
        ]);
        $userlist->add_from_sql("userid", "SELECT teacheripuserid AS userid FROM {checkin}
            WHERE id = :id AND teacheripuserid IS NOT NULL", ["id" => $checkin->id]);
        $userlist->add_from_sql("userid", "SELECT teacherlocationuserid AS userid FROM {checkin}
            WHERE id = :id AND teacherlocationuserid IS NOT NULL", ["id" => $checkin->id]);
    }

    /**
     * Method delete_data_for_users.
     *
     * @param approved_userlist $userlist Parameter userlist.
     * @return mixed Return value.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $checkin = self::get_checkin_for_context($userlist->get_context());
        if (!$checkin) {
            return;
        }

        $userids = array_map("intval", $userlist->get_userids());
        if (!$userids) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, "uid");
        $params["checkinid"] = $checkin->id;
        $DB->delete_records_select("checkin_records", "checkinid = :checkinid AND userid {$insql}", $params);
        if (in_array((int)$checkin->teacheripuserid, $userids, true)) {
            self::clear_ip_reference($checkin->id);
        }
        if (in_array((int)$checkin->teacherlocationuserid, $userids, true)) {
            self::clear_location_reference($checkin->id);
        }
    }

    /**
     * Method get_checkin_for_context.
     *
     * @param context $context Parameter context.
     * @return ?\stdClass Return value.
     */
    private static function get_checkin_for_context(context $context): ?\stdClass {
        global $DB;

        if ($context->contextlevel !== CONTEXT_MODULE) {
            return null;
        }
        $cm = get_coursemodule_from_id("checkin", $context->instanceid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return null;
        }
        return $DB->get_record("checkin", ["id" => $cm->instance]) ?: null;
    }

    /**
     * Method clear_user_reference_data.
     *
     * @param \stdClass $checkin Parameter checkin.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    private static function clear_user_reference_data(\stdClass $checkin, int $userid): void {
        if ((int)$checkin->teacheripuserid === $userid) {
            self::clear_ip_reference($checkin->id);
        }
        if ((int)$checkin->teacherlocationuserid === $userid) {
            self::clear_location_reference($checkin->id);
        }
    }

    /**
     * Method clear_all_reference_data.
     *
     * @param int $checkinid Parameter checkinid.
     * @return void Return value.
     */
    private static function clear_all_reference_data(int $checkinid): void {
        self::clear_ip_reference($checkinid);
        self::clear_location_reference($checkinid);
    }

    /**
     * Method clear_ip_reference.
     *
     * @param int $checkinid Parameter checkinid.
     * @return void Return value.
     */
    private static function clear_ip_reference(int $checkinid): void {
        global $DB;

        $DB->update_record("checkin", (object)[
            "id" => $checkinid,
            "teacherip" => null,
            "teacheripuserid" => null,
            "teacheriptime" => null,
        ]);
    }

    /**
     * Method clear_location_reference.
     *
     * @param int $checkinid Parameter checkinid.
     * @return void Return value.
     */
    private static function clear_location_reference(int $checkinid): void {
        global $DB;

        $DB->update_record("checkin", (object)[
            "id" => $checkinid,
            "teacherlatitude" => null,
            "teacherlongitude" => null,
            "teacheraccuracy" => null,
            "teacherlocationuserid" => null,
            "teacherlocationtime" => null,
        ]);
    }
}
