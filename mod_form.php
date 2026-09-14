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
 * mod_form.php
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * Class mod_checkin_mod_form.
 */
class mod_checkin_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return mixed Return value.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("checkinname", "mod_checkin"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");

        $this->standard_intro_elements();

        $mform->addElement("html", html_writer::tag("h3", get_string("availability", "mod_checkin")));
        $mform->addElement("date_time_selector", "timestart", get_string("timestart", "mod_checkin"));
        $mform->addElement("date_time_selector", "timeend", get_string("timeend", "mod_checkin"));

        $mform->addElement("html", html_writer::tag("h3", get_string("security", "mod_checkin")));
        $mform->addElement("advcheckbox", "usecode", get_string("usecode", "mod_checkin"));
        $mform->addElement("select", "codelength", get_string("codelength", "mod_checkin"), [
            4 => get_string("digits4", "mod_checkin"),
            6 => get_string("digits6", "mod_checkin"),
        ]);
        $mform->setDefault("codelength", 4);
        $mform->hideIf("codelength", "usecode", "notchecked");

        $mform->addElement("advcheckbox", "requireteacherip", get_string("requireteacherip", "mod_checkin"));
        $mform->addHelpButton("requireteacherip", "requireteacherip", "mod_checkin");

        $mform->addElement("advcheckbox", "requirelocation", get_string("requirelocation", "mod_checkin"));
        $mform->addHelpButton("requirelocation", "requirelocation", "mod_checkin");
        $mform->addElement("text", "locationradius", get_string("locationradius", "mod_checkin"), ["size" => 8]);
        $mform->setType("locationradius", PARAM_INT);
        $mform->setDefault("locationradius", 100);
        $mform->hideIf("locationradius", "requirelocation", "notchecked");

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return mixed Return value.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ((int)$data["timeend"] <= (int)$data["timestart"]) {
            $errors["timeend"] = get_string("errorendbeforestart", "mod_checkin");
        }

        if (!empty($data["requirelocation"])) {
            $radius = (int)$data["locationradius"];
            if ($radius < 5 || $radius > 10000) {
                $errors["locationradius"] = get_string("errorradius", "mod_checkin");
            }
        }

        return $errors;
    }

    /**
     * Method data_postprocessing.
     *
     * @param mixed $data Parameter data.
     * @return mixed Return value.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);

        $suffix = $this->get_suffix();
        $completionfield = "completion" . $suffix;
        $rulefield = "completioncheckin" . $suffix;
        $autocompletion = !empty($data->{$completionfield})
            && (int)$data->{$completionfield} === COMPLETION_TRACKING_AUTOMATIC;
        if (!$autocompletion || empty($data->{$rulefield})) {
            $data->{$rulefield} = 0;
        }
    }

    /**
     * Method add_completion_rules.
     *
     * @return mixed Return value.
     */
    public function add_completion_rules() {
        $mform = $this->_form;
        $suffix = $this->get_suffix();
        $element = "completioncheckin" . $suffix;
        $mform->addElement("advcheckbox", $element, "", get_string("completioncheckin", "mod_checkin"));
        return [$element];
    }

    /**
     * Method completion_rule_enabled.
     *
     * @param mixed $data Parameter data.
     * @return mixed Return value.
     */
    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        return !empty($data["completioncheckin" . $suffix]);
    }
}
