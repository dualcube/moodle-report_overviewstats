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

namespace report_overviewstats\privacy;

use core_privacy\local\metadata\collection;

/**
 * Privacy provider for report_overviewstats.
 *
 * @package report_overviewstats
 * @author DualCube <admin@dualcube.com>
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @copyright 2023 DualCube <admin@dualcube.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\provider {
    /**
     * Returns metadata about the personal data this plugin reads.
     *
     * This plugin stores no data of its own - it only reads data already
     * owned and managed by other subsystems to build its charts.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data read through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->link_subsystem('core_user', 'privacy:metadata:core_user');
        $collection->link_subsystem('core_group', 'privacy:metadata:core_group');
        $collection->link_subsystem('core_enrol', 'privacy:metadata:core_enrol');
        $collection->add_plugintype_link('logstore', [], 'privacy:metadata:logstore');

        return $collection;
    }
}
