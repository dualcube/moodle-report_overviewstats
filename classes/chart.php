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
  * plugin overviewstats
  *
  * @package report_overviewstats
  * @author DualCube <admin@dualcube.com>
  * @copyright 2023 DualCube <admin@dualcube.com>
  * @copyright based on work by 2013 David Mudrak <david@moodle.com>
  * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

  /**
   * Base class for all charts to be reported
   *
   * @package report_overviewstats
   * @author DualCube <admin@dualcube.com>
   * @copyright 2023 DualCube <admin@dualcube.com>
   * @copyright based on work by 2013 David Mudrak <david@moodle.com>
   * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
   */
class report_overviewstats_chart {
    /**
     * create login for login chart
     *
     * @return array
     */
    public static function report_overviewstats_chart_logins() {
        $maindata = self::prepare_data_login_parday_chart();
        $title = get_string('chart-logins', 'report_overviewstats');
        $titleperday = get_string('chart-logins-perday', 'report_overviewstats');

        return [
            $title => [
                $titleperday => html_writer::tag('div',
                    self::get_chart(new \core\chart_line(),
                        get_string('user-numbers', 'report_overviewstats'),
                        $maindata['loggedins'],
                        $maindata['dates'],
                        false
                    ),
                    [
                        'id' => 'chart_logins_perday',
                        'class' => 'chartplaceholder',
                        'style' => 'min-height: 300px;',
                        'dir' => 'ltr',
                    ]
                ),
            ],
        ];
    }

    /**
     * prepare data for login perday chart
     *
     * @return array
     */
    protected static function prepare_data_login_parday_chart() {
        global $DB, $CFG;

        $now = strtotime('today midnight');
        $lastmonth = [];
        for ($i = 30; $i >= 0; $i--) {
            $lastmonth[$now - $i * DAYSECS] = [];
        }
        $logmanger = get_log_manager();
        $readers = $logmanger->get_readers('\core\log\sql_reader');
        $reader = reset($readers);
        $params = ['component' => 'core',
            'eventname' => '\core\event\user_loggedin',
            'guestid' => $CFG->siteguest,
            'timestart' => $now - 30 * DAYSECS, ];
        $select = "component = :component AND eventname = :eventname AND userid <> :guestid AND timecreated >= :timestart";
        $recordset = $reader->get_events_select($select, $params, 'timecreated DESC', 0, 0);

        foreach ($recordset as $record) {
            foreach (array_reverse($lastmonth, true) as $timestamp => $loggedin) {
                $date = usergetdate($timestamp);
                if ($record->timecreated >= $timestamp) {
                    $lastmonth[$timestamp][$record->userid] = true;
                    break;
                }
            }
        }
        $maindata = [
            'dates' => [],
            'loggedins' => [],
        ];
        $format = get_string('strftimedateshort', 'core_langconfig');
        foreach ($lastmonth as $timestamp => $loggedin) {
            $date = userdate($timestamp, $format);
            $maindata['dates'][] = $date;
            $maindata['loggedins'][] = count($loggedin);
        }

        return $maindata;
    }

    /**
     * create chart for countries
     *
     * @return array
     */
    public static function report_overviewstats_chart_countries() {
        $maindata = self::prepare_data_chart_countries();
        $title = get_string('chart-countries', 'report_overviewstats');
        $info = html_writer::div(
            get_string('chart-countries-info',
            'report_overviewstats', count($maindata['counts'])),
            'chartinfo');
        $chart = html_writer::tag('div',
            self::get_chart(
                new \core\chart_bar(),
                get_string('user-numbers', 'report_overviewstats'),
                $maindata['counts'],
                $maindata['countrys'],
                true
            ),
            [
                'id' => 'chart_countries',
                'class' => 'chartplaceholder',
                'style' => 'min-height: ' . max(66, (count($maindata['counts']) * 20)) . 'px;',
                'dir' => 'ltr',
            ]
        );

        return [$title => $info . $chart];
    }

    /**
     * prepaire data for country chart
     *
     * @return array
     */
    protected static function prepare_data_chart_countries() {
        global $DB;

        $sql = "SELECT country, COUNT(*)
                  FROM {user}
                 WHERE country IS NOT NULL AND country <> '' AND deleted = 0 AND confirmed = 1
              GROUP BY country
              ORDER BY COUNT(*) DESC, country ASC";

        $maindata = [
            'countrys' => [],
            'counts' => [],
        ];
        foreach ($DB->get_records_sql_menu($sql) as $country => $count) {
            if (get_string_manager()->string_exists($country, 'core_countries')) {
                $countryname = get_string($country, 'core_countries');
            } else {
                $countryname = $country;
            }
            $maindata['countrys'][] = $countryname;
            $maindata['counts'][] = $count;
        }
        return $maindata;
    }

    /**
     * create the language chart
     *
     * @return array
     */
    public static function report_overviewstats_chart_langs() {
        $maindata = self::prepare_data_chart_langs();

        $title = get_string('chart-langs', 'report_overviewstats');
        $info = html_writer::div(get_string('chart-langs-info', 'report_overviewstats', count($maindata['counts'])), 'chartinfo');
        $chart = html_writer::tag('div',
            self::get_chart(
                new \core\chart_bar(),
                get_string('user-numbers', 'report_overviewstats'),
                $maindata['counts'],
                $maindata['languages'],
                true
            ),
            [
                'id' => 'chart_langs',
                'class' => 'chartplaceholder',
                'style' => 'min-height: ' . max(66, (count($maindata['counts']) * 20)) . 'px;',
                'dir' => 'ltr',
            ]
        );

        return [$title => $info . $chart];
    }

    /**
     * prepare data for language chart
     *
     * @return array
     */
    protected static function prepare_data_chart_langs() {
        global $DB;
        $sql = "SELECT lang, COUNT(*)
                  FROM {user}
                 WHERE deleted = 0 AND confirmed = 1
              GROUP BY lang
              ORDER BY COUNT(*) DESC";

        $maindata = [
            'languages' => [],
            'counts' => [],
        ];
        foreach ($DB->get_records_sql_menu($sql) as $lang => $count) {
            if (get_string_manager()->translation_exists($lang)) {
                $langname = get_string_manager()->get_string('thislanguageint', 'core_langconfig', null, $lang);
            } else {
                $langname = $lang;
            }
            $maindata['languages'][] = $langname;
            $maindata['counts'][] = $count;
        }

        return $maindata;
    }

    /**
     * create the chart for courses
     *
     * @return array
     */
    public static function report_overviewstats_chart_courses() {
        global $OUTPUT;

        $maindata = self::prepare_data_chart_courses();

        $title = get_string('chart-courses', 'report_overviewstats');
        $titlepercategory = get_string('chart-courses-percategory', 'report_overviewstats');

        $percategorydata = new html_table();
        $percategorydata->head = [
            get_string('chart-courses-percategory-categoryname', 'report_overviewstats'),
            get_string('chart-courses-percategory-coursesrecursive', 'report_overviewstats'),
            get_string('chart-courses-percategory-coursesown', 'report_overviewstats'),
        ];
        foreach ($maindata['percategory'] as $catdata) {
            $percategorydata->data[] = new html_table_row([
                $catdata['categoryname'],
                $catdata['coursesrecursive'],
                $catdata['coursesown'],
            ]);
        }

        $titlesizes = sprintf('%s %s', get_string('chart-courses-sizes', 'report_overviewstats'),
            $OUTPUT->help_icon('chart-courses-sizes', 'report_overviewstats'));

        return [
            $title => [
                $titlepercategory => html_writer::tag('div',
                    html_writer::table($percategorydata),
                    [
                        'id' => 'chart_courses_percategory',
                        'class' => 'simple_data_table',
                    ],
                ),
                $titlesizes => html_writer::tag('div',
                    self::get_chart(
                        new \core\chart_bar(),
                        get_string('course-numbers', 'report_overviewstats'),
                        $maindata['sizes']['courses'],
                        $maindata['sizes']['course_size'],
                        false
                    ),
                    [
                        'id' => 'chart_courses_sizes',
                        'class' => 'chartplaceholder',
                        'style' => 'min-height: 300px;',
                        'dir' => 'ltr',
                    ],
                ),
            ],
        ];
    }

    /**
     * prepaire data for course chart
     *
     * @return array
     */
    protected static function prepare_data_chart_courses() {
        global $DB;
        $maindata = [];
        // Number of courses per category.
        $categorieslist = core_course_category::make_categories_list();
        $maindata['percategory'] = [];
        $total = 0;

        foreach ($categorieslist as $catid => $catname) {
            $cat = core_course_category::get($catid);
            $coursesown = $cat->get_courses_count();
            $total += $coursesown;
            $maindata['percategory'][] = [
                'categoryname' => $catname,
                'coursesrecursive' => $cat->get_courses_count(['recursive' => true]),
                'coursesown' => $coursesown,
            ];
        }

        $maindata['percategory'][] = [
            'categoryname' => html_writer::tag('strong', get_string('total')),
            'coursesrecursive' => '',
            'coursesown' => html_writer::tag('strong', $total),
        ];

        // Distribution graph of number of activities per course.
        $sql = "SELECT course, COUNT(id) AS modules
                  FROM {course_modules}
              GROUP BY course";

        $recordset = $DB->get_recordset_sql($sql);

        $max = 0;
        $data = [];
        $maindata['sizes'] = [
            'course_size' => [],
            'courses' => [],
        ];

        foreach ($recordset as $record) {
            $distributiongroup = floor($record->modules / 5); // 0 for 0-4, 1 for 5-9, 2 for 10-14 etc.
            if (!isset($data[$distributiongroup])) {
                $data[$distributiongroup] = 1;
            } else {
                $data[$distributiongroup]++;
            }
            if ($distributiongroup > $max) {
                $max = $distributiongroup;
            }
        }

        $recordset->close();

        for ($i = 0; $i <= $max; $i++) {
            if (!isset($data[$i])) {
                $data[$i] = 0;
            }
        }
        ksort($data);

        foreach ($data as $distributiongroup => $courses) {
            $distributiongroupname = sprintf("%d-%d", $distributiongroup * 5, $distributiongroup * 5 + 4);
            $maindata['sizes']['course_size'][] = $distributiongroupname;
            $maindata['sizes']['courses'][] = $courses;
        }

        return $maindata;
    }

    /**
     * create enrolment chart
     *
     * @return array
     */
    public static function report_overviewstats_chart_enrolments($course) {
        $maindata = self::prepare_data_chart_enrollments($course);

        $title = get_string('chart-enrolments', 'report_overviewstats');
        $titlemonth = get_string('chart-enrolments-month', 'report_overviewstats');
        $titleyear = get_string('chart-enrolments-year', 'report_overviewstats');

        return [
            $title => [
                $titlemonth => html_writer::tag('div',
                    self::get_chart(
                        new \core\chart_line(),
                        get_string('enrolled', 'report_overviewstats'),
                        $maindata['lastmonth']['enrolled'],
                        $maindata['lastmonth']['date'],
                        false
                    ),
                    [
                        'id' => 'chart_enrolments_lastmonth',
                        'class' => 'chartplaceholder',
                        'style' => 'min-height: 300px;',
                    ]
                ),
                $titleyear => html_writer::tag('div',
                    self::get_chart(
                        new \core\chart_line(),
                        get_string('enrolled', 'report_overviewstats'),
                        $maindata['lastyear']['enrolled'],
                        $maindata['lastyear']['date'],
                        false
                    ),
                    [
                        'id' => 'chart_enrolments_lastyear',
                        'class' => 'chartplaceholder',
                        'style' => 'min-height: 300px;',
                    ]
                ),
            ],
        ];
    }

    /**
     * prepare chart enrolments data
     *
     * @return array
     */
    protected static function prepare_data_chart_enrollments($course) {
        global $DB, $CFG;

        if (is_null($course)) {
            throw new coding_exception(get_string('null-course-exception', 'report_overviewstats'));
        }

        // Get the number of currently enrolled users.

        $context = context_course::instance($course->id);
        list($esql, $params) = get_enrolled_sql($context);
        $sql = "SELECT COUNT(u.id)
                  FROM {user} u
                  JOIN ($esql) je ON je.id = u.id
                 WHERE u.deleted = 0";

        $current = $DB->count_records_sql($sql, $params);

        // Construct the estimated number of enrolled users in the last month
        // and the last year using the current number and the log records.

        $now = time();

        $lastmonth = [];
        for ($i = 30; $i >= 0; $i--) {
            $lastmonth[$now - $i * DAYSECS] = $current;
        }

        $lastyear = [];
        for ($i = 12; $i >= 0; $i--) {
            $lastyear[$now - $i * 30 * DAYSECS] = $current;
        }

        // Fetch all the enrol/unrol log entries from the last year.
        $logmanger = get_log_manager();
        $readers = $logmanger->get_readers('\core\log\sql_reader');
        $reader = reset($readers);
        $select = "component = :component AND (eventname = :eventname1 OR eventname = :eventname2) ".
        "AND timecreated >= :timestart AND courseid = :courseid";
        $params = [
            'component' => 'core',
            'eventname1' => '\core\event\user_enrolment_created',
            'eventname2' => '\core\event\user_enrolment_deleted',
            'timestart' => $now - 30 * DAYSECS,
            'courseid' => $course->id,
        ];
        $events = $reader->get_events_select($select, $params, 'timecreated DESC', 0, 0);

        foreach ($events as $event) {
            foreach (array_reverse($lastmonth, true) as $key => $value) {
                if ($event->timecreated >= $key) {
                    // We need to amend all days up to the key.
                    foreach ($lastmonth as $mkey => $mvalue) {
                        if ($mkey <= $key) {
                            if ($event->eventname === '\core\event\user_enrolment_created' && $lastmonth[$mkey] > 0) {
                                $lastmonth[$mkey]--;
                            } else if ($event->eventname === '\core\event\user_enrolment_deleted') {
                                $lastmonth[$mkey]++;
                            }
                        }
                    }
                    break;
                }
            }
            foreach (array_reverse($lastyear, true) as $key => $value) {
                if ($event->timecreated >= $key) {
                    // We need to amend all months up to the key.
                    foreach ($lastyear as $ykey => $yvalue) {
                        if ($ykey <= $key) {
                            if ($event->eventname === '\core\event\user_enrolment_created' && $lastyear[$ykey] > 0) {
                                $lastyear[$ykey]--;
                            } else if ($event->eventname === '\core\event\user_enrolment_deleted') {
                                $lastyear[$ykey]++;
                            }
                        }
                    }
                    break;
                }
            }
        }

        $maindata = [
            'lastmonth' => [
                'date' => [],
                'enrolled' => [],
            ],
            'lastyear' => [
                'date' => [],
                'enrolled' => [],
            ],
        ];

        $format = get_string('strftimedateshort', 'core_langconfig');
        foreach ($lastmonth as $timestamp => $enrolled) {
            $date = userdate($timestamp, $format);
            $maindata['lastmonth']['date'][] = $date;
            $maindata['lastmonth']['enrolled'][] = $enrolled;
        }
        foreach ($lastyear as $timestamp => $enrolled) {
            $date = userdate($timestamp, $format);
            $maindata['lastyear']['date'][] = $date;
            $maindata['lastyear']['enrolled'][] = $enrolled;
        }
        return $maindata;
    }

    /**
     * create chart function based on inputes
     *
     * @param \core\chart_line $chart
     * @param string $seriesname
     * @param array $seriesdata
     * @param array $labelsdata
     * @param bool $ishorizontal
     * @return chart
     */
    protected static function get_chart($chart, $seriesname, $seriesdata, $labelsdata, $ishorizontal) {
        global $OUTPUT;
        $series = new \core\chart_series($seriesname, $seriesdata);
        $labels = $labelsdata;
        if ($ishorizontal) {
            $chart->set_horizontal(true);
        }
        $chart->add_series($series);
        $chart->set_labels($labels);
        return $OUTPUT->render($chart);
    }
}
