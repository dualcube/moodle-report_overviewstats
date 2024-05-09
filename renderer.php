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
  * HTML rendering methods are defined here
  *
  * @category output
  * @package report_overviewstats
  * @copyright 2023 DualCube <admin@dualcube.com>
  * @copyright based on work by 2013 David Mudrak <david@moodle.com>
  * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

 /**
  * Overview statistics renderer
  *
  * @category output
  * @package report_overviewstats
  * @copyright 2023 DualCube <admin@dualcube.com>
  * @copyright based on work by 2013 David Mudrak <david@moodle.com>
  * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */
class report_overviewstats_renderer extends plugin_renderer_base {

    /**
     * Render the report charts
     *
     * @see report_overviewstats_chart::get_content() for the expected structure
     * @param array $charts list of {@link report_overviewstats_chart} instances
     * @return string
     */
    public function charts($course) {
        $chartsdata = [];
        if (is_null($course)) {
            $chartsdata[] = report_overviewstats_chart::report_overviewstats_chart_logins();
            $chartsdata[] = report_overviewstats_chart::report_overviewstats_chart_countries();
            $chartsdata[] = report_overviewstats_chart::report_overviewstats_chart_langs();
            $chartsdata[] = report_overviewstats_chart::report_overviewstats_chart_courses();
        } else {
            $chartsdata[] = report_overviewstats_chart::report_overviewstats_chart_enrolments($course);
        }

        $outlist = '';
        $outbody = '';

        $counter = 0;
        foreach ($chartsdata as $chart) {
            foreach ($chart as $title => $content) {
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
        $out .= html_writer::start_tag('ul', ['class' => 'chartslist']);
        $out .= $outlist;
        $out .= html_writer::end_tag('ul');
        $out .= html_writer::div($outbody, 'charts');
        $out .= $this->output->footer();

        return $out;
    }
}
