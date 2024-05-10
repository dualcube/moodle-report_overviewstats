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
  * Displays some overview statistics for the site
  *
  * @package report_overviewstats
  * @author DualCube <admin@dualcube.com>
  * @copyright 2023 DualCube <admin@dualcube.com>
  * @copyright based on work by 2013 David Mudrak <david@moodle.com>
  * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$courseid = optional_param('course', null, PARAM_INT);
$course = null;

if (is_null($courseid)) {
    // Site level reports.
    admin_externalpage_setup('overviewstats', '', null, '', ['pagelayout' => 'report']);
} else {
    // Course level report.
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $context = context_course::instance($course->id);

    require_login($course, false);
    require_capability('report/overviewstats:view', $context);
    if ($course->id == 1) {
        redirect(new moodle_url('/'));
    }
    $PAGE->set_url(new moodle_url('/report/overviewstats/index.php', ['course' => $course->id]));
    $PAGE->set_pagelayout('report');
    $PAGE->set_title($course->shortname . ' - ' . get_string('pluginname', 'report_overviewstats'));
    $PAGE->set_heading($course->fullname . ' - ' . get_string('pluginname', 'report_overviewstats'));
}

$output = $PAGE->get_renderer('report_overviewstats');

echo $output->charts($course);
