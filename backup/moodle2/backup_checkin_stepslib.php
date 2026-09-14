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
 * backup_checkin_stepslib.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * backup_checkin_activity_structure_step
 */
class backup_checkin_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $checkin = new backup_nested_element("checkin", ["id"], [
            "name",
            "intro",
            "introformat",
            "timestart",
            "timeend",
            "usecode",
            "codelength",
            "requireteacherip",
            "requirelocation",
            "locationradius",
            "completioncheckin",
            "timecreated",
            "timemodified",
        ]);

        $records = new backup_nested_element("records");
        $record = new backup_nested_element("record", ["id"], [
            "userid",
            "timecreated",
            "ipaddress",
            "latitude",
            "longitude",
            "accuracy",
            "distance",
        ]);

        $checkin->add_child($records);
        $records->add_child($record);

        $checkin->set_source_table("checkin", ["id" => backup::VAR_ACTIVITYID]);
        if ($userinfo) {
            $record->set_source_table("checkin_records", ["checkinid" => backup::VAR_PARENTID]);
        }

        $record->annotate_ids("user", "userid");

        $checkin->annotate_files("mod_checkin", "intro", null);

        return $this->prepare_activity_structure($checkin);
    }
}
