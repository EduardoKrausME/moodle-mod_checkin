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
 * checkin.js
 *
 * @package   mod_checkin
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery"], function($) {
    var captureLocation = function($form) {
        var unsupported = $form.data("location-unsupported");
        var error = $form.data("location-error");

        if (!navigator.geolocation) {
            window.alert(unsupported);
            return;
        }

        var $button = $form.find("button[type='submit']");
        $button.prop("disabled", true);

        navigator.geolocation.getCurrentPosition(function(position) {
            $form.find("input[name='latitude']").val(position.coords.latitude);
            $form.find("input[name='longitude']").val(position.coords.longitude);
            $form.find("input[name='accuracy']").val(position.coords.accuracy || "");
            $form.attr("data-location-ready", "1");
            $form.get(0).submit();
        }, function() {
            $button.prop("disabled", false);
            window.alert(error);
        }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });
    };

    return {
        init: function() {
            $(document).on("submit", "form[data-checkin-location-form='1']", function(event) {
                var $form = $(this);
                if ($form.attr("data-location-ready") === "1") {
                    return;
                }
                event.preventDefault();
                captureLocation($form);
            });

            $(document).on("submit", "form[data-checkin-student-form='1']", function(event) {
                var $form = $(this);
                if ($form.data("require-location") !== 1 || $form.attr("data-location-ready") === "1") {
                    return;
                }
                event.preventDefault();
                captureLocation($form);
            });
        }
    };
});
