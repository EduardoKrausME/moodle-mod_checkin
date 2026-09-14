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
 * restore_checkin_stepslib.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore_checkin_activity_structure_step
 */
class restore_checkin_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $paths = [new restore_path_element("checkin", "/activity/checkin")];
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("checkin_record", "/activity/checkin/records/record");
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_checkin.
     *
     * @param mixed $data Parameter data.
     * @return mixed Return value.
     */
    protected function process_checkin($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->checkincode = !empty($data->usecode)
            ? \mod_checkin\checkin_manager::generate_code((int)$data->codelength)
            : null;
        $data->teacherip = null;
        $data->teacheripuserid = null;
        $data->teacheriptime = null;
        $data->teacherlatitude = null;
        $data->teacherlongitude = null;
        $data->teacheraccuracy = null;
        $data->teacherlocationuserid = null;
        $data->teacherlocationtime = null;

        $newitemid = $DB->insert_record("checkin", $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Method process_checkin_record.
     *
     * @param mixed $data Parameter data.
     * @return mixed Return value.
     */
    protected function process_checkin_record($data) {
        global $DB;

        $data = (object)$data;
        $data->checkinid = $this->get_new_parentid("checkin");
        $data->userid = $this->get_mappingid("user", $data->userid, 0);
        if (!$data->userid) {
            return;
        }
        $DB->insert_record("checkin_records", $data);
    }

    /**
     * Method after_execute.
     *
     * @return mixed Return value.
     */
    protected function after_execute() {
        $this->add_related_files("mod_checkin", "intro", null);
    }
}
