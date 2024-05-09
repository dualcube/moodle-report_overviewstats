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
  * Provides the classes used internally in the plugin
  *
  * @package report_overviewstats
  * @author DualCube <admin@dualcube.com>
  * @copyright 2023 DualCube <admin@dualcube.com>
  * @copyright based on work by 2013 David Mudrak <david@moodle.com>
  * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

defined('MOODLE_INTERNAL') || die();

// Need to load the base classes first so we can extend them.
require_once($CFG->dirroot . '/report/overviewstats/classes/chart.php');

// Load all classes files 
$classfiles = new DirectoryIterator($CFG->dirroot . '/report/overviewstats/classes/');
foreach ($classfiles as $classfile) {
    if ($classfile->isDot()) {
        continue;
    }
    if ($classfile->isLink()) {
        throw new coding_exception(get_string('link-exception', 'report_overviewstats'));
    }
    if ($classfile->isFile() && substr($classfile->getFilename(), -4) === '.php') {
        require_once($classfile->getPathname());
    }
}
