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
 * lib.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * checkin_supports
 *
 * @param $feature
 * @return string|true|null
 */
function checkin_supports($feature) {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_COMPLETION_HAS_RULES => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_ADMINISTRATION,
        default => null,
    };
}

/**
 * checkin_add_instance
 *
 * @param $data
 * @param $mform
 * @return bool|int
 * @throws dml_exception
 */
function checkin_add_instance($data, $mform = null) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->checkincode = !empty($data->usecode)
        ? \mod_checkin\checkin_manager::generate_code((int)$data->codelength)
        : null;

    return $DB->insert_record("checkin", $data);
}

/**
 * checkin_update_instance
 *
 * @param $data
 * @param $mform
 * @return bool
 * @throws dml_exception
 */
function checkin_update_instance($data, $mform = null) {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();

    $current = $DB->get_record("checkin", ["id" => $data->id], "id,usecode,codelength,checkincode", MUST_EXIST);
    if (!empty($data->usecode)) {
        if (empty($current->checkincode) || (int)$current->codelength !== (int)$data->codelength) {
            $data->checkincode = \mod_checkin\checkin_manager::generate_code((int)$data->codelength);
        }
    } else {
        $data->checkincode = null;
    }

    return $DB->update_record("checkin", $data);
}

/**
 * checkin_delete_instance
 *
 * @param $id
 * @return bool
 * @throws dml_exception
 */
function checkin_delete_instance($id) {
    global $DB;

    if (!$DB->record_exists("checkin", ["id" => $id])) {
        return false;
    }

    $DB->delete_records("checkin_records", ["checkinid" => $id]);
    $DB->delete_records("checkin", ["id" => $id]);
    return true;
}
