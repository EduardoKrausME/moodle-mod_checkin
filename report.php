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
 * report.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");
require_once($CFG->libdir . "/tablelib.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("checkin", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$checkin = $DB->get_record("checkin", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/checkin:viewreports", $context);

$PAGE->set_url("/mod/checkin/report.php", ["id" => $cm->id]);
$PAGE->set_title(get_string("report", "mod_checkin"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string("report", "mod_checkin"));

$users = get_enrolled_users(
    $context,
    "mod/checkin:checkin",
    0,
    "u.id,u.firstname,u.lastname,u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.email",
    "u.lastname,u.firstname"
);
$records = $DB->get_records("checkin_records", ["checkinid" => $checkin->id], "", "*");
$byuser = [];
foreach ($records as $record) {
    $byuser[$record->userid] = $record;
}

$present = count(array_intersect(array_keys($byuser), array_keys($users)));
$total = count($users);
$summary = get_string("totalpresent", "mod_checkin", (object)["present" => $present, "total" => $total]);

$table = new flexible_table("mod-checkin-report");
$table->define_columns(["student", "status", "time", "ip", "location", "distance"]);
$table->define_headers([
    get_string("student", "mod_checkin"),
    get_string("status", "mod_checkin"),
    get_string("time", "mod_checkin"),
    get_string("ipaddress", "mod_checkin"),
    get_string("coordinates", "mod_checkin"),
    get_string("distance", "mod_checkin"),
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute("class", "generaltable generalbox");
$table->setup();

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($checkin->name));
echo html_writer::tag("p", s($summary), ["class" => "lead"]);
echo html_writer::tag("p", s(userdate($checkin->timestart) . " – " . userdate($checkin->timeend)), ["class" => "text-muted"]);

foreach ($users as $user) {
    $record = $byuser[$user->id] ?? null;
    if ($record) {
        $status = get_string("present", "mod_checkin");
        $time = userdate($record->timecreated);
        $ip = s((string)$record->ipaddress);
        $location = $record->latitude !== null && $record->longitude !== null
            ? format_float((float)$record->latitude, 6) . ", " . format_float((float)$record->longitude, 6)
            : "-";
        $distance = $record->distance !== null
            ? format_float((float)$record->distance, 1) . " " . get_string("metres", "mod_checkin")
            : "-";
    } else {
        $status = time() > (int)$checkin->timeend ? get_string("absent", "mod_checkin") : get_string("pending", "mod_checkin");
        $time = "-";
        $ip = "-";
        $location = "-";
        $distance = "-";
    }

    $profileurl = new moodle_url("/user/view.php", ["id" => $user->id, "course" => $course->id]);
    $student = html_writer::link($profileurl, fullname($user));
    $table->add_data([$student, $status, $time, $ip, $location, $distance]);
}

$table->finish_output();
echo $OUTPUT->footer();
