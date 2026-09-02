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

namespace report_overviewstats\output;

use html_writer;
use plugin_renderer_base;
use report_overviewstats\chart;

/**
 * Overview statistics renderer.
 *
 * @package report_overviewstats
 * @category output
 * @author DualCube <admin@dualcube.com>
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @copyright 2023 DualCube <admin@dualcube.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Render the report charts
     *
     * @param \stdClass|null $course
     * @param int $groupid id of the group to filter the course-level report by, or 0 for all participants
     * @return string
     */
    public function charts($course, $groupid = 0) {
        $chartsdata = [];
        if (is_null($course)) {
            $chartsdata[] = chart::logins();
            $chartsdata[] = chart::countries();
            $chartsdata[] = chart::langs();
            $chartsdata[] = chart::courses();
        } else {
            $chartsdata[] = chart::access($course, $groupid);
            $chartsdata[] = chart::enrolments($course, $groupid);
        }

        $outlist = '';
        $outbody = '';

        $counter = 0;
        foreach ($chartsdata as $chartdata) {
            foreach ($chartdata as $title => $content) {
                $counter++;
                $outlist .= html_writer::tag('li', html_writer::link('#chart_seq_' . $counter, s($title)));
                $outbody .= html_writer::start_div('chart', ['id' => 'chart_seq_' . $counter]);
                $outbody .= $this->output->heading($title, 2);
                if (is_array($content)) {
                    foreach ($content as $subtitle => $subcontent) {
                        $outbody .= html_writer::start_div('subchart');
                        $outbody .= $this->output->heading($subtitle, 3);
                        $outbody .= $subcontent;
                        $outbody .= html_writer::end_div();
                    }
                } else {
                    $outbody .= $content;
                }
                $outbody .= html_writer::end_div();
            }
        }

        $out = $this->output->header();
        if (!is_null($course) && groups_get_course_groupmode($course) != NOGROUPS) {
            $out .= groups_print_course_menu($course, $this->page->url, true);
        }
        $out .= html_writer::start_tag('ul', ['class' => 'chartslist']);
        $out .= $outlist;
        $out .= html_writer::end_tag('ul');
        $out .= html_writer::div($outbody, 'charts');
        $out .= $this->output->footer();

        return $out;
    }
}
