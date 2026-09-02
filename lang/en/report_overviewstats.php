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
 * Provides a list of strings for the plugin
 *
 * @package report_overviewstats
 * @category string
 * @author DualCube <admin@dualcube.com>
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @copyright 2023 DualCube <admin@dualcube.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Overview statistics';
$string['chart-access'] = 'Course access';
$string['chart-access-group'] = 'Course access ({$a})';
$string['chart-access-perday'] = 'Per day';
$string['chart-countries'] = 'User countries';
$string['chart-countries-info'] = 'Number of different countries: {$a}';
$string['chart-courses'] = 'Courses';
$string['chart-courses-percategory'] = 'Number of courses in a course category';
$string['chart-courses-percategory-categoryname'] = 'Course category';
$string['chart-courses-percategory-coursesrecursive'] = 'Courses (recursive)';
$string['chart-courses-percategory-coursesown'] = 'Courses (own only)';
$string['chart-courses-sizes'] = 'Number of courses per size';
$string['chart-courses-sizes_help'] = 'Displays the distribution graph of number of activities per course. That is, how many courses are there with 0-4 activities, 5-9 activities, 10-14 activities etc.';
$string['chart-enrolments'] = 'Enrolled users';
$string['chart-enrolments-group'] = 'Enrolled users ({$a})';
$string['chart-enrolments-month'] = 'Last month';
$string['chart-enrolments-year'] = 'Last year';
$string['chart-langs'] = 'User preferred languages';
$string['chart-langs-info'] = 'Number of different languages: {$a}';
$string['chart-logins'] = 'Users logging in';
$string['chart-logins-perday'] = 'Per day';
$string['overviewstats:view'] = 'View overview statistics';
$string['privacy:metadata:core_enrol'] = 'The overview statistics plugin reads the number of currently enrolled users, via the core enrolment subsystem, to build the enrolments chart. It does not store this data itself.';
$string['privacy:metadata:core_group'] = 'The overview statistics plugin reads group membership, via the core group subsystem, to filter the enrolments chart by group. It does not store this data itself.';
$string['privacy:metadata:core_user'] = 'The overview statistics plugin reads user country and language fields, via the core user subsystem, to build the countries and languages charts. It does not store this data itself.';
$string['privacy:metadata:logstore'] = 'The overview statistics plugin reads login, course access, and enrolment log events, via the installed log store, to build historical charts. It does not store this data itself.';
$string['user-numbers'] = 'Number of users';
$string['course-numbers'] = 'Number of course';
$string['enrolled'] = 'Enrolled';
$string['null-course-exception'] = 'Course level report invoked without the reference to the course!';
