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
 * checkin.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['absent'] = 'No check-in';
$string['accuracy'] = 'Accuracy';
$string['alreadycheckedin'] = 'Your presence has already been recorded.';
$string['availability'] = 'Check-in window';
$string['capturelocation'] = 'Capture my location';
$string['checkin'] = 'I\'m present';
$string['checkin:addinstance'] = 'Add a new class check-in';
$string['checkin:checkin'] = 'Record own presence';
$string['checkin:manage'] = 'Manage class check-in';
$string['checkin:view'] = 'View class check-in';
$string['checkin:viewreports'] = 'View attendance report';
$string['checkinname'] = 'Check-in name';
$string['checkinsuccess'] = 'Presence recorded successfully.';
$string['code'] = 'Code';
$string['codelength'] = 'Code length';
$string['coderegenerated'] = 'A new check-in code was generated.';
$string['completioncheckin'] = 'Student must successfully check in';
$string['completiondetail:checkin'] = 'Check in successfully';
$string['coordinates'] = 'Coordinates';
$string['currentip'] = 'Current IP';
$string['digits4'] = '4 digits';
$string['digits6'] = '6 digits';
$string['distance'] = 'Distance';
$string['entercode'] = 'Enter the code shown by the teacher';
$string['errorendbeforestart'] = 'The closing time must be after the opening time.';
$string['errorradius'] = 'The radius must be between 5 and 10000 metres.';
$string['eventattendancemarked'] = 'Check-in recorded';
$string['geolocationerror'] = 'Could not obtain your location. Allow location access in the browser and try again.';
$string['geolocationunsupported'] = 'This browser does not support geolocation.';
$string['invalidcode'] = 'The check-in code is incorrect.';
$string['ipaddress'] = 'IP address';
$string['ipmismatch'] = 'Your IP address does not match the teacher\'s registered IP address.';
$string['ipregistered'] = 'Classroom IP address registered.';
$string['location'] = 'Location';
$string['locationinvalid'] = 'The location received from the browser is invalid.';
$string['locationnotset'] = 'The teacher has not captured the classroom location yet.';
$string['locationradius'] = 'Maximum distance from teacher location (metres)';
$string['locationregistered'] = 'Classroom location registered.';
$string['locationrequired'] = 'Location is required for this check-in.';
$string['metres'] = 'm';
$string['modulename'] = 'Class check-in';
$string['modulenameplural'] = 'Class check-ins';
$string['notset'] = 'Not set';
$string['outsideallowedradius'] = 'You are outside the allowed check-in area.';
$string['pending'] = 'Pending';
$string['pluginadministration'] = 'Class check-in administration';
$string['pluginname'] = 'Class check-in';
$string['present'] = 'Present';
$string['privacy:export:record'] = 'My check-in';
$string['privacy:export:reference'] = 'Reference data registered by me';
$string['privacy:metadata:checkin'] = 'Teacher network and location reference data used for presence validation.';
$string['privacy:metadata:checkin:teacheraccuracy'] = 'The accuracy reported for the teacher reference location.';
$string['privacy:metadata:checkin:teacherip'] = 'The reference IP address registered by a teacher.';
$string['privacy:metadata:checkin:teacheriptime'] = 'When the reference IP address was registered.';
$string['privacy:metadata:checkin:teacheripuserid'] = 'The user who registered the reference IP address.';
$string['privacy:metadata:checkin:teacherlatitude'] = 'The teacher reference latitude.';
$string['privacy:metadata:checkin:teacherlocationtime'] = 'When the reference location was registered.';
$string['privacy:metadata:checkin:teacherlocationuserid'] = 'The user who registered the reference location.';
$string['privacy:metadata:checkin:teacherlongitude'] = 'The teacher reference longitude.';
$string['privacy:metadata:records'] = 'Student presence records.';
$string['privacy:metadata:records:accuracy'] = 'The location accuracy reported by the user\'s browser.';
$string['privacy:metadata:records:distance'] = 'The calculated distance from the teacher reference location.';
$string['privacy:metadata:records:ipaddress'] = 'The IP address seen by Moodle during check-in.';
$string['privacy:metadata:records:latitude'] = 'The latitude reported by the user\'s browser.';
$string['privacy:metadata:records:longitude'] = 'The longitude reported by the user\'s browser.';
$string['privacy:metadata:records:timecreated'] = 'When the user checked in.';
$string['privacy:metadata:records:userid'] = 'The user who checked in.';
$string['privacywarning'] = 'This activity can store IP address and precise location when those validation options are enabled.';
$string['radius'] = 'Radius';
$string['regeneratecode'] = 'Generate new code';
$string['registeredip'] = 'Registered classroom IP';
$string['registeredlocation'] = 'Registered classroom location';
$string['registerthisip'] = 'Use this IP';
$string['report'] = 'Attendance report';
$string['requirelocation'] = 'Require geolocation';
$string['requirelocation_help'] = 'The teacher captures the classroom location. The student\'s browser must provide a position inside the configured radius. Browser permission and HTTPS are required.';
$string['requireteacherip'] = 'Require the same IP address as the teacher';
$string['requireteacherip_help'] = 'The teacher must register the current IP address in the activity. Students can check in only when Moodle sees the same public IP address.';
$string['security'] = 'Presence validation';
$string['status'] = 'Status';
$string['student'] = 'Student';
$string['teachercontrols'] = 'Teacher controls';
$string['teacheripnotset'] = 'The teacher has not registered the classroom IP address yet.';
$string['time'] = 'Time';
$string['timeend'] = 'Closes at';
$string['timestart'] = 'Opens at';
$string['totalpresent'] = 'Present:  of ';
$string['usecode'] = 'Require a numeric code';
$string['windowclosed'] = 'Check-in is closed.';
$string['windowlabel'] = 'Window';
$string['windownotstarted'] = 'Check-in has not opened yet.';
$string['windowopen'] = 'Check-in is open';
