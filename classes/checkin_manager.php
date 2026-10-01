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
 * checkin_manager.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_checkin;

use completion_info;
use context_module;
use mod_checkin\event\attendance_marked;
use moodle_exception;
use stdClass;

/**
 * Class checkin_manager.
 */
class checkin_manager {
    /**
     * Method generate_code.
     *
     * @param int $length Parameter length.
     * @return string Return value.
     */
    public static function generate_code(int $length): string {
        $length = $length === 6 ? 6 : 4;
        $max = (10 ** $length) - 1;
        return str_pad((string)random_int(0, $max), $length, "0", STR_PAD_LEFT);
    }

    /**
     * Method register_teacher_ip.
     *
     * @param stdClass $checkin Parameter checkin.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    public static function register_teacher_ip(stdClass $checkin, int $userid): void {
        global $DB;

        $DB->set_field("checkin", "teacherip", self::current_ip(), ["id" => $checkin->id]);
        $DB->set_field("checkin", "teacheripuserid", $userid, ["id" => $checkin->id]);
        $DB->set_field("checkin", "teacheriptime", time(), ["id" => $checkin->id]);
    }

    /**
     * Method register_teacher_location.
     *
     * @param stdClass $checkin Parameter checkin.
     * @param int $userid Parameter userid.
     * @param float $latitude Parameter latitude.
     * @param float $longitude Parameter longitude.
     * @param ?float $accuracy Parameter accuracy.
     * @return void Return value.
     */
    public static function register_teacher_location(
        stdClass $checkin,
        int $userid,
        float $latitude,
        float $longitude,
        ?float $accuracy
    ): void {
        global $DB;

        self::validate_coordinates($latitude, $longitude);

        $record = (object)[
            "id" => $checkin->id,
            "teacherlatitude" => $latitude,
            "teacherlongitude" => $longitude,
            "teacheraccuracy" => $accuracy,
            "teacherlocationuserid" => $userid,
            "teacherlocationtime" => time(),
            "timemodified" => time(),
        ];
        $DB->update_record("checkin", $record);
    }

    /**
     * Method regenerate_code.
     *
     * @param stdClass $checkin Parameter checkin.
     * @return string Return value.
     */
    public static function regenerate_code(stdClass $checkin): string {
        global $DB;

        $code = self::generate_code((int)$checkin->codelength);
        $DB->set_field("checkin", "checkincode", $code, ["id" => $checkin->id]);
        $DB->set_field("checkin", "timemodified", time(), ["id" => $checkin->id]);
        return $code;
    }

    /**
     * Method mark_presence.
     *
     * @param stdClass $checkin Parameter checkin.
     * @param stdClass $cm Parameter cm.
     * @param int $userid Parameter userid.
     * @param string $code Parameter code.
     * @param ?float $latitude Parameter latitude.
     * @param ?float $longitude Parameter longitude.
     * @param ?float $accuracy Parameter accuracy.
     * @return stdClass Return value.
     */
    public static function mark_presence(
        stdClass $checkin,
        stdClass $cm,
        int $userid,
        string $code = "",
        ?float $latitude = null,
        ?float $longitude = null,
        ?float $accuracy = null
    ): stdClass {
        global $CFG, $DB;

        require_once($CFG->libdir . "/completionlib.php");

        $existing = $DB->get_record("checkin_records", ["checkinid" => $checkin->id, "userid" => $userid]);
        if ($existing) {
            return $existing;
        }

        $now = time();
        if ($now < (int)$checkin->timestart) {
            throw new moodle_exception("windownotstarted", "mod_checkin");
        }
        if ($now > (int)$checkin->timeend) {
            throw new moodle_exception("windowclosed", "mod_checkin");
        }

        if (!empty($checkin->usecode)) {
            $expected = (string)$checkin->checkincode;
            if ($expected === "" || !hash_equals($expected, trim($code))) {
                throw new moodle_exception("invalidcode", "mod_checkin");
            }
        }

        $ipaddress = null;
        if (!empty($checkin->requireteacherip)) {
            $ipaddress = self::current_ip();
            if (empty($checkin->teacherip)) {
                throw new moodle_exception("teacheripnotset", "mod_checkin");
            }
            if (!hash_equals(self::normalize_ip((string)$checkin->teacherip), self::normalize_ip($ipaddress))) {
                throw new moodle_exception("ipmismatch", "mod_checkin");
            }
        }

        $distance = null;
        if (!empty($checkin->requirelocation)) {
            if ($checkin->teacherlatitude === null || $checkin->teacherlongitude === null) {
                throw new moodle_exception("locationnotset", "mod_checkin");
            }
            if ($latitude === null || $longitude === null) {
                throw new moodle_exception("locationrequired", "mod_checkin");
            }
            self::validate_coordinates($latitude, $longitude);
            $distance = self::distance_metres(
                (float)$checkin->teacherlatitude,
                (float)$checkin->teacherlongitude,
                $latitude,
                $longitude
            );
            if ($distance > (float)$checkin->locationradius) {
                throw new moodle_exception("outsideallowedradius", "mod_checkin");
            }
        } else {
            $latitude = null;
            $longitude = null;
            $accuracy = null;
        }

        $record = (object)[
            "checkinid" => $checkin->id,
            "userid" => $userid,
            "timecreated" => $now,
            "ipaddress" => $ipaddress,
            "latitude" => $latitude,
            "longitude" => $longitude,
            "accuracy" => $accuracy,
            "distance" => $distance,
        ];
        $record->id = $DB->insert_record("checkin_records", $record);

        $context = context_module::instance($cm->id);
        $event = attendance_marked::create([
            "objectid" => $record->id,
            "context" => $context,
            "relateduserid" => $userid,
            "other" => ["checkinid" => $checkin->id],
        ]);
        $event->add_record_snapshot("checkin_records", $record);
        $event->trigger();

        if (!empty($checkin->completioncheckin)) {
            $course = get_course($cm->course);
            $completion = new completion_info($course);
            $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
        }

        return $record;
    }

    /**
     * Method current_ip.
     *
     * @return string Return value.
     */
    public static function current_ip(): string {
        return (string)getremoteaddr();
    }

    /**
     * Method distance_metres.
     *
     * @param float $lat1 Parameter lat1.
     * @param float $lon1 Parameter lon1.
     * @param float $lat2 Parameter lat2.
     * @param float $lon2 Parameter lon2.
     * @return float Return value.
     */
    public static function distance_metres(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthradius = 6371000.0;
        $lat1rad = deg2rad($lat1);
        $lat2rad = deg2rad($lat2);
        $dlat = deg2rad($lat2 - $lat1);
        $dlon = deg2rad($lon2 - $lon1);

        $a = sin($dlat / 2) ** 2
            + cos($lat1rad) * cos($lat2rad) * sin($dlon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthradius * $c, 2);
    }

    /**
     * Method validate_coordinates.
     *
     * @param float $latitude Parameter latitude.
     * @param float $longitude Parameter longitude.
     * @return void Return value.
     */
    private static function validate_coordinates(float $latitude, float $longitude): void {
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new moodle_exception("locationinvalid", "mod_checkin");
        }
    }

    /**
     * Method normalize_ip.
     *
     * @param string $ip Parameter ip.
     * @return string Return value.
     */
    private static function normalize_ip(string $ip): string {
        $ip = trim($ip);
        if (str_starts_with($ip, "::ffff:")) {
            $mapped = substr($ip, 7);
            if (filter_var($mapped, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return $mapped;
            }
        }
        return strtolower($ip);
    }
}
