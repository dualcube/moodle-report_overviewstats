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
 * Number of activity modules report
 *
 * @package     report_overviewstats
 * @copyright   2013 David Mudrak <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Reports the number of used activity modules
 */
class report_overviewstats_chart_modules extends report_overviewstats_chart {

    /**
     * @return string
     */
    public function get_content() {

        $this->prepare_data();

        $title = get_string('chart-modules', 'report_overviewstats');
        $titleinstances = get_string('chart-modules-instances', 'report_overviewstats');
        $titleusage = get_string('chart-modules-usage', 'report_overviewstats');

        return array($title => array(
            $titleinstances => html_writer::tag('div', '', array(
                'id' => 'chart_modules_instances',
                'class' => 'chartplaceholder',
                'style' => 'min-height: 300px;',
            )),
            $titleusage => html_writer::tag('div', '', array(
                'id' => 'chart_modules_usage',
                'class' => 'chartplaceholder',
                'style' => 'min-height: 300px;',
            )),
        ));
    }

    public function inject_page_requirements(moodle_page $page) {

        $this->prepare_data();

        $page->requires->yui_module(
            'moodle-report_overviewstats-charts',
            'M.report_overviewstats.charts.modules.init',
            array($this->data)
        );
    }

    /**
     * Returns the list of modules used in the course or site and their count
     *
     * @return array
     */
    public function prepare_data() {
        global $DB;

        if (!is_null($this->data)) {
            return;
        }

        // Number of instances

        $params = array();
        $sql = "SELECT m.name, COUNT(cm.id)
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module ";

        if (!is_null($this->course)) {
            $sql .= "WHERE cm.course = :courseid ";
            $params['courseid'] = $this->course->id;
        }

        $sql .= "GROUP BY m.name
                 ORDER BY COUNT(cm.id) DESC";

        $instances = array();
        foreach ($DB->get_records_sql_menu($sql, $params) as $module => $count) {
            if (get_string_manager()->string_exists('pluginname', $module)) {
                $modulename = get_string('pluginname', $module);
            } else {
                $modulename = $module;
            }
            $instances[] = array(
                'module' => $modulename,
                'count' => $count
            );
        }

        // Usage - forum posts

        $params = array();
        $sql = "SELECT COUNT(fp.id)
                  FROM {forum_posts} fp";

        if (!is_null($this->course)) {
            $sql .= " JOIN {forum_discussions} fd ON fd.id = fp.discussion
                     WHERE fd.course = :courseid";
            $params['courseid'] = $this->course->id;
        }

        $posts = $DB->get_field_sql($sql, $params);

        $this->data = array(
            'instances' => $instances,
            'usage' => array(
                array(
                    'indicator' => get_string('chart-modules-usage-posts', 'report_overviewstats'),
                    'value' => $posts,
                ),
            ),
        );

        // Usage - glossary entries

        $params = array();
        $sql = "SELECT COUNT(ge.id)
                  FROM {glossary_entries} ge";

        if (!is_null($this->course)) {
            $sql .= " JOIN {glossary} g ON g.id = ge.glossaryid
                     WHERE g.course = :courseid";
            $params['courseid'] = $this->course->id;
        }

        $glentries = $DB->get_field_sql($sql, $params);

        $this->data = array(
            'instances' => $instances,
            'usage' => array(
                array(
                    'indicator' => get_string('chart-modules-usage-posts', 'report_overviewstats'),
                    'value' => $posts,
                ),
                array(
                    'indicator' => get_string('chart-modules-usage-glentries', 'report_overviewstats'),
                    'value' => $glentries,
                ),
            ),
        );
    }
}
