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

namespace report_overviewstats;

/**
 * Base class for all charts to be reported.
 *
 * @package report_overviewstats
 * @author DualCube <admin@dualcube.com>
 * @copyright 2013 David Mudrak <david@moodle.com>
 * @copyright 2023 DualCube <admin@dualcube.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chart {
    /**
     * create login for login chart
     *
     * @return array
     */
    public static function logins() {
        $maindata = self::prepare_data_login_parday_chart();
        $title = get_string('chart-logins', 'report_overviewstats');
        $titleperday = get_string('chart-logins-perday', 'report_overviewstats');

        return [
            $title => [
                $titleperday => \html_writer::tag(
                    'div',
                    self::get_chart(
                        new \core\chart_line(),
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
        global $CFG;

        $now = strtotime('today midnight');
        $select = "component = :component AND eventname = :eventname AND userid <> :guestid AND timecreated >= :timestart";
        $params = [
            'component' => 'core',
            'eventname' => '\core\event\user_loggedin',
            'guestid' => $CFG->siteguest,
            'timestart' => $now - 30 * DAYSECS,
        ];

        $result = self::count_unique_users_per_day($now, $select, $params);

        return [
            'dates' => $result['dates'],
            'loggedins' => $result['counts'],
        ];
    }

    /**
     * count unique userids per day, over the last 30 days, from log events matching $select/$params
     *
     * Shared by the site-wide logins chart and the course access chart, which both need
     * "how many distinct users triggered this event on each of the last 30 days".
     *
     * @param int $now today's midnight timestamp
     * @param string $select SQL WHERE clause for get_events_select(), must filter to events carrying a userid
     * @param array $params SQL parameters for $select
     * @param array $groupmemberids optional userid => true map to restrict matches to, or empty for no restriction
     * @return array ['dates' => string[], 'counts' => int[]]
     */
    protected static function count_unique_users_per_day($now, $select, array $params, array $groupmemberids = []) {
        $buckets = array_fill_keys(
            array_map(fn($daysago) => $now - $daysago * DAYSECS, range(30, 0, -1)),
            []
        );

        $logmanger = get_log_manager();
        $readers = $logmanger->get_readers('\core\log\sql_reader');
        $reader = reset($readers);
        $records = $reader->get_events_select($select, $params, 'timecreated DESC', 0, 0);
        if ($groupmemberids) {
            $records = array_filter($records, fn($record) => isset($groupmemberids[$record->userid]));
        }

        $bucketstarts = array_reverse(array_keys($buckets));
        foreach ($records as $record) {
            foreach ($bucketstarts as $bucketstart) {
                if ($record->timecreated < $bucketstart) {
                    continue;
                }
                $buckets[$bucketstart][$record->userid] = true;
                break;
            }
        }

        $format = get_string('strftimedateshort', 'core_langconfig');

        return [
            'dates' => array_values(array_map(fn($timestamp) => userdate($timestamp, $format), array_keys($buckets))),
            'counts' => array_values(array_map('count', $buckets)),
        ];
    }

    /**
     * create chart for countries
     *
     * @return array
     */
    public static function countries() {
        $maindata = self::prepare_data_chart_countries();
        $title = get_string('chart-countries', 'report_overviewstats');
        $info = \html_writer::div(
            get_string(
                'chart-countries-info',
                'report_overviewstats',
                count($maindata['counts'])
            ),
            'chartinfo'
        );
        $chart = \html_writer::tag(
            'div',
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
    public static function langs() {
        $maindata = self::prepare_data_chart_langs();

        $title = get_string('chart-langs', 'report_overviewstats');
        $info = \html_writer::div(get_string('chart-langs-info', 'report_overviewstats', count($maindata['counts'])), 'chartinfo');
        $chart = \html_writer::tag(
            'div',
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
    public static function courses() {
        global $OUTPUT;

        $maindata = self::prepare_data_chart_courses();

        $title = get_string('chart-courses', 'report_overviewstats');
        $titlepercategory = get_string('chart-courses-percategory', 'report_overviewstats');

        $percategorydata = new \html_table();
        $percategorydata->head = [
            get_string('chart-courses-percategory-categoryname', 'report_overviewstats'),
            get_string('chart-courses-percategory-coursesrecursive', 'report_overviewstats'),
            get_string('chart-courses-percategory-coursesown', 'report_overviewstats'),
        ];
        foreach ($maindata['percategory'] as $catdata) {
            $percategorydata->data[] = new \html_table_row([
                $catdata['categoryname'],
                $catdata['coursesrecursive'],
                $catdata['coursesown'],
            ]);
        }

        $titlesizes = sprintf(
            '%s %s',
            get_string('chart-courses-sizes', 'report_overviewstats'),
            $OUTPUT->help_icon('chart-courses-sizes', 'report_overviewstats')
        );

        return [
            $title => [
                $titlepercategory => \html_writer::tag(
                    'div',
                    \html_writer::table($percategorydata),
                    [
                        'id' => 'chart_courses_percategory',
                        'class' => 'simple_data_table',
                    ],
                ),
                $titlesizes => \html_writer::tag(
                    'div',
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
        $categorieslist = \core_course_category::make_categories_list();
        $maindata['percategory'] = [];
        $total = 0;

        foreach ($categorieslist as $catid => $catname) {
            $cat = \core_course_category::get($catid);
            $coursesown = $cat->get_courses_count();
            $total += $coursesown;
            $maindata['percategory'][] = [
                'categoryname' => $catname,
                'coursesrecursive' => $cat->get_courses_count(['recursive' => true]),
                'coursesown' => $coursesown,
            ];
        }

        $maindata['percategory'][] = [
            'categoryname' => \html_writer::tag('strong', get_string('total')),
            'coursesrecursive' => '',
            'coursesown' => \html_writer::tag('strong', $total),
        ];

        // Distribution graph of number of activities per course.
        $sql = "SELECT course, COUNT(id) AS modules
                  FROM {course_modules}
              GROUP BY course";

        $recordset = $DB->get_recordset_sql($sql);
        $modulecounts = iterator_to_array($recordset, false);
        $recordset->close();

        // 0 for 0-4 activities, 1 for 5-9, 2 for 10-14 etc.
        $distributiongroups = array_map(fn($record) => (int) floor($record->modules / 5), $modulecounts);
        $data = array_count_values($distributiongroups);
        $max = max(array_merge([0], $distributiongroups));
        for ($i = 0; $i <= $max; $i++) {
            $data[$i] = $data[$i] ?? 0;
        }
        ksort($data);

        $maindata['sizes'] = [
            'course_size' => array_values(array_map(
                fn($distributiongroup) => sprintf('%d-%d', $distributiongroup * 5, $distributiongroup * 5 + 4),
                array_keys($data)
            )),
            'courses' => array_values($data),
        ];

        return $maindata;
    }

    /**
     * create course access chart
     *
     * @param \stdClass $course
     * @param int $groupid id of the group to filter the report by, or 0 for all participants
     * @return array
     */
    public static function access($course, $groupid = 0) {
        $maindata = self::prepare_data_course_access_parday_chart($course, $groupid);

        if ($groupid) {
            $title = get_string('chart-access-group', 'report_overviewstats', groups_get_group_name($groupid));
        } else {
            $title = get_string('chart-access', 'report_overviewstats');
        }
        $titleperday = get_string('chart-access-perday', 'report_overviewstats');

        return [
            $title => [
                $titleperday => \html_writer::tag(
                    'div',
                    self::get_chart(
                        new \core\chart_line(),
                        get_string('user-numbers', 'report_overviewstats'),
                        $maindata['accessed'],
                        $maindata['dates'],
                        false
                    ),
                    [
                        'id' => 'chart_access_perday',
                        'class' => 'chartplaceholder',
                        'style' => 'min-height: 300px;',
                        'dir' => 'ltr',
                    ]
                ),
            ],
        ];
    }

    /**
     * prepare data for the course access per day chart
     *
     * Counts unique registered users (not visits) who viewed the course each day,
     * derived from \core\event\course_viewed log entries.
     *
     * @param \stdClass $course
     * @param int $groupid id of the group to filter the report by, or 0 for all participants
     * @return array
     */
    protected static function prepare_data_course_access_parday_chart($course, $groupid = 0) {
        $now = strtotime('today midnight');
        $select = "component = :component AND eventname = :eventname AND courseid = :courseid AND timecreated >= :timestart";
        $params = [
            'component' => 'core',
            'eventname' => '\core\event\course_viewed',
            'courseid' => $course->id,
            'timestart' => $now - 30 * DAYSECS,
        ];
        $groupmemberids = $groupid ? groups_get_members($groupid, 'u.id') : [];

        $result = self::count_unique_users_per_day($now, $select, $params, $groupmemberids);

        return [
            'dates' => $result['dates'],
            'accessed' => $result['counts'],
        ];
    }

    /**
     * create enrolment chart
     *
     * @param \stdClass $course
     * @param int $groupid id of the group to filter the report by, or 0 for all participants
     * @return array
     */
    public static function enrolments($course, $groupid = 0) {
        $maindata = self::prepare_data_chart_enrollments($course, $groupid);

        if ($groupid) {
            $title = get_string('chart-enrolments-group', 'report_overviewstats', groups_get_group_name($groupid));
        } else {
            $title = get_string('chart-enrolments', 'report_overviewstats');
        }
        $titlemonth = get_string('chart-enrolments-month', 'report_overviewstats');
        $titleyear = get_string('chart-enrolments-year', 'report_overviewstats');

        return [
            $title => [
                $titlemonth => \html_writer::tag(
                    'div',
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
                $titleyear => \html_writer::tag(
                    'div',
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
     * @param \stdClass $course
     * @param int $groupid id of the group to filter the report by, or 0 for all participants
     * @return array
     */
    protected static function prepare_data_chart_enrollments($course, $groupid = 0) {
        if (is_null($course)) {
            throw new \coding_exception(get_string('null-course-exception', 'report_overviewstats'));
        }

        $current = self::get_current_enrolment_count($course, $groupid);
        $now = usergetmidnight(time(), \core_date::get_user_timezone());

        // Don't extend the graphs further back than the course itself started.
        $coursestart = usergetmidnight(max($course->startdate, 0), \core_date::get_user_timezone());
        $dayssincestart = max(0, (int) floor(($now - $coursestart) / DAYSECS));
        $monthssincestart = max(0, (int) floor(($now - $coursestart) / (30 * DAYSECS)));

        $lastmonth = self::build_enrolment_baseline($now, DAYSECS, min(30, $dayssincestart), $current);
        $lastyear = self::build_enrolment_baseline($now, 30 * DAYSECS, min(12, $monthssincestart), $current);

        // The log-based delta below can only attribute an enrol/unenrol event
        // to a group using the affected user's CURRENT group membership,
        // since historical membership isn't recorded - this is consistent
        // with the rest of this method already projecting today's numbers
        // backwards using the log records, rather than tracking exact history.
        $groupmemberids = $groupid ? groups_get_members($groupid, 'u.id') : [];

        $eventsfrom = max($now - 360 * DAYSECS, $coursestart);
        foreach (self::get_enrolment_events($course, $eventsfrom) as $event) {
            if ($groupid && !isset($groupmemberids[$event->relateduserid])) {
                continue;
            }
            self::apply_enrolment_delta($lastmonth, $event);
            self::apply_enrolment_delta($lastyear, $event);
        }

        return [
            'lastmonth' => self::format_enrolment_series($lastmonth),
            'lastyear' => self::format_enrolment_series($lastyear),
        ];
    }

    /**
     * get the number of currently enrolled users
     *
     * @param \stdClass $course
     * @param int $groupid id of the group to filter the report by, or 0 for all participants
     * @return int
     */
    protected static function get_current_enrolment_count($course, $groupid) {
        global $DB;

        $context = \context_course::instance($course->id);
        [$esql, $params] = get_enrolled_sql($context, '', $groupid);
        $sql = "SELECT COUNT(u.id)
                  FROM {user} u
                  JOIN ($esql) je ON je.id = u.id
                 WHERE u.deleted = 0";

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * build a series of timestamp => initial value pairs, going back from $now
     *
     * @param int $now the most recent timestamp in the series
     * @param int $step seconds between each series entry
     * @param int $count number of steps to go back
     * @param int $initial value to seed every entry with
     * @return array
     */
    protected static function build_enrolment_baseline($now, $step, $count, $initial) {
        $series = [];
        for ($i = $count; $i >= 0; $i--) {
            $series[$now - $i * $step] = $initial;
        }
        return $series;
    }

    /**
     * fetch all the enrol/unenrol log entries for the course since $since
     *
     * @param \stdClass $course
     * @param int $since only fetch events at or after this timestamp
     * @return \Iterator
     */
    protected static function get_enrolment_events($course, $since) {
        $logmanger = get_log_manager();
        $readers = $logmanger->get_readers('\core\log\sql_reader');
        $reader = reset($readers);
        $select = "component = :component AND (eventname = :eventname1 OR eventname = :eventname2) " .
        "AND timecreated >= :timestart AND courseid = :courseid";
        $params = [
            'component' => 'core',
            'eventname1' => '\core\event\user_enrolment_created',
            'eventname2' => '\core\event\user_enrolment_deleted',
            'timestart' => $since,
            'courseid' => $course->id,
        ];
        return $reader->get_events_select($select, $params, 'timecreated DESC', 0, 0);
    }

    /**
     * amend a lastmonth/lastyear series in place for a single enrol/unenrol event
     *
     * @param array $series timestamp => enrolled count, amended in place
     * @param \stdClass $event
     * @return void
     */
    protected static function apply_enrolment_delta(array &$series, $event) {
        $matchingkeys = array_filter(array_keys($series), fn($key) => $event->timecreated >= $key + DAYSECS);
        if (!$matchingkeys) {
            return;
        }
        $key = max($matchingkeys);

        // Amend all entries up to the key.
        $targetkeys = array_filter(array_keys($series), fn($entrykey) => $entrykey <= $key);
        foreach ($targetkeys as $entrykey) {
            // Events are always created/deleted only (see get_enrolment_events()), so
            // "not deleted" here is safe to treat as "created" without rechecking.
            if ($event->eventname === '\core\event\user_enrolment_deleted') {
                $series[$entrykey]++;
            } else if ($series[$entrykey] > 0) {
                $series[$entrykey]--;
            }
        }
    }

    /**
     * convert a timestamp => enrolled series into date/enrolled arrays for the chart
     *
     * @param array $series timestamp => enrolled count
     * @return array
     */
    protected static function format_enrolment_series(array $series) {
        $formatted = [
            'date' => [],
            'enrolled' => [],
        ];
        $format = get_string('strftimedateshort', 'core_langconfig');
        foreach ($series as $timestamp => $enrolled) {
            $formatted['date'][] = userdate($timestamp, $format);
            $formatted['enrolled'][] = $enrolled;
        }
        return $formatted;
    }

    /**
     * create chart function based on inputes
     *
     * @param \core\chart_base $chart
     * @param string $seriesname
     * @param array $seriesdata
     * @param array $labelsdata
     * @param bool $ishorizontal
     * @return string
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
