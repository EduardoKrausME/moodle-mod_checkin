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
 * view.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/lib.php");

use core\output\notification;
use mod_checkin\checkin_manager;

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("checkin", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$checkin = $DB->get_record("checkin", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/checkin:view", $context);

$PAGE->set_url("/mod/checkin/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($checkin->name));
$PAGE->set_heading(format_string($course->fullname));

$viewurl = new moodle_url("/mod/checkin/view.php", ["id" => $cm->id]);
$action = optional_param("action", "", PARAM_ALPHA);

$readcoordinate = static function(string $name): ?float {
    $value = optional_param($name, "", PARAM_RAW_TRIMMED);
    if ($value === "") {
        return null;
    }
    if (!is_numeric($value)) {
        throw new moodle_exception("locationinvalid", "mod_checkin");
    }
    return (float)$value;
};

if ($action !== "" && data_submitted()) {
    require_sesskey();

    try {
        switch ($action) {
            case "registerip":
                require_capability("mod/checkin:manage", $context);
                if (empty($checkin->requireteacherip)) {
                    throw new moodle_exception("invalidaccess");
                }
                checkin_manager::register_teacher_ip($checkin, $USER->id);
                redirect($viewurl, get_string("ipregistered", "mod_checkin"), null, notification::NOTIFY_SUCCESS);

            case "regeneratecode":
                require_capability("mod/checkin:manage", $context);
                if (empty($checkin->usecode)) {
                    throw new moodle_exception("invalidaccess");
                }
                checkin_manager::regenerate_code($checkin);
                redirect($viewurl, get_string("coderegenerated", "mod_checkin"), null, notification::NOTIFY_SUCCESS);

            case "setlocation":
                require_capability("mod/checkin:manage", $context);
                if (empty($checkin->requirelocation)) {
                    throw new moodle_exception("invalidaccess");
                }
                $latitude = $readcoordinate("latitude");
                $longitude = $readcoordinate("longitude");
                $accuracy = $readcoordinate("accuracy");
                if ($latitude === null || $longitude === null) {
                    throw new moodle_exception("locationrequired", "mod_checkin");
                }
                checkin_manager::register_teacher_location($checkin, $USER->id, $latitude, $longitude, $accuracy);
                redirect($viewurl, get_string("locationregistered", "mod_checkin"), null, notification::NOTIFY_SUCCESS);

            case "checkin":
                require_capability("mod/checkin:checkin", $context);
                $latitude = $readcoordinate("latitude");
                $longitude = $readcoordinate("longitude");
                $accuracy = $readcoordinate("accuracy");
                $code = optional_param("code", "", PARAM_RAW_TRIMMED);
                checkin_manager::mark_presence(
                    $checkin,
                    $cm,
                    $USER->id,
                    $code,
                    $latitude,
                    $longitude,
                    $accuracy
                );
                redirect($viewurl, get_string("checkinsuccess", "mod_checkin"), null, notification::NOTIFY_SUCCESS);

            default:
                throw new moodle_exception("invalidaccess");
        }
    } catch (moodle_exception $exception) {
        redirect($viewurl, $exception->getMessage(), null, notification::NOTIFY_ERROR);
    }
}

$checkin = $DB->get_record("checkin", ["id" => $cm->instance], "*", MUST_EXIST);
$record = $DB->get_record("checkin_records", ["checkinid" => $checkin->id, "userid" => $USER->id]);
$now = time();
$windowopen = $now >= (int)$checkin->timestart && $now <= (int)$checkin->timeend;
$windowstatus = $windowopen
    ? get_string("windowopen", "mod_checkin")
    : ($now < (int)$checkin->timestart
        ? get_string("windownotstarted", "mod_checkin")
        : get_string("windowclosed", "mod_checkin"));

$currentip = checkin_manager::current_ip();
$canmanage = has_capability("mod/checkin:manage", $context);
$cancheckin = has_capability("mod/checkin:checkin", $context);
$canviewreports = has_capability("mod/checkin:viewreports", $context);
$haslocation = $checkin->teacherlatitude !== null && $checkin->teacherlongitude !== null;

$templatecontext = [
    "name" => format_string($checkin->name),
    "intro" => format_module_intro("checkin", $checkin, $cm->id),
    "windowstatus" => $windowstatus,
    "windowopen" => $windowopen,
    "windowtext" => userdate($checkin->timestart) . " – " . userdate($checkin->timeend),
    "canmanage" => $canmanage,
    "cancheckin" => $cancheckin,
    "canviewreports" => $canviewreports,
    "alreadycheckedin" => !empty($record),
    "checkintime" => $record ? userdate($record->timecreated) : "",
    "usecode" => !empty($checkin->usecode),
    "checkincode" => $canmanage ? (string)$checkin->checkincode : "",
    "requireteacherip" => !empty($checkin->requireteacherip),
    "currentip" => $currentip,
    "teacherip" => !empty($checkin->teacherip) ? (string)$checkin->teacherip : get_string("notset", "mod_checkin"),
    "teacheripset" => !empty($checkin->teacherip),
    "requirelocation" => !empty($checkin->requirelocation),
    "teacherlocationset" => $haslocation,
    "teacherlocation" => $haslocation
        ? format_float((float)$checkin->teacherlatitude, 6) . ", " . format_float((float)$checkin->teacherlongitude, 6)
        : get_string("notset", "mod_checkin"),
    "teacheraccuracy" => $checkin->teacheraccuracy !== null ? format_float((float)$checkin->teacheraccuracy, 1) : "",
    "locationradius" => (int)$checkin->locationradius,
    "privacywarning" => !empty($checkin->requireteacherip) || !empty($checkin->requirelocation),
    "sesskey" => sesskey(),
    "viewurl" => $viewurl->out(false),
    "reporturl" => (new moodle_url("/mod/checkin/report.php", ["id" => $cm->id]))->out(false),
    "geolocationerror" => get_string("geolocationerror", "mod_checkin"),
    "geolocationunsupported" => get_string("geolocationunsupported", "mod_checkin"),
];

if ($canmanage || (!empty($checkin->requirelocation) && $cancheckin && empty($record))) {
    $PAGE->requires->js_call_amd("mod_checkin/checkin", "init");
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("mod_checkin/view", $templatecontext);
echo $OUTPUT->footer();
