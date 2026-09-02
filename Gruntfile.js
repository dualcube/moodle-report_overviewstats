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

/* eslint-env node */

/**
 * Local Grunt tooling for report_overviewstats.
 *
 * Lints this plugin's CSS with the same rules Moodle core's own Gruntfile
 * enforces (see .stylelintrc.json), so issues that moodle-plugin-ci's
 * "grunt" CI step would flag can be caught locally without a full Moodle
 * checkout.
 *
 * @package report_overviewstats
 * @author DualCube <admin@dualcube.com>
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @copyright 2023 DualCube <admin@dualcube.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
module.exports = function(grunt) {
    grunt.loadNpmTasks('grunt-stylelint');

    grunt.initConfig({
        stylelint: {
            css: {
                options: {
                    configFile: '.stylelintrc.json',
                    quietDeprecationWarnings: true,
                },
                src: ['styles.css'],
            },
        },
    });

    grunt.registerTask('default', ['stylelint']);
};
