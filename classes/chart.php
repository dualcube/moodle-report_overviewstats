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
 * Base class for all charts to be reported
 *
 * @package     report_overviewstats
 * @author      DualCube <admin@dualcube.com>
 * @copyright  	Dualcube (https://dualcube.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class report_overviewstats_manager {
	/**
	 * Factory method returning list of charts to be displayed for the site
	 *
	 * @return array of {@link report_overviewstats_chart} html
	 */
	public function get_site_charts() {
		$list = array(
			$this->report_overviewstats_chart_logins(),
			$this->report_overviewstats_chart_countries(),
			$this->report_overviewstats_chart_langs(),
			$this->report_overviewstats_chart_courses(),
		);
		return $list;
	}

	/**
	 * Factory method returning list of charts to be displayed for the given course
	 *
	 * @param stdClass $course The reported course's record
	 * @return array of {@link report_overviewstats_chart} html
	 */
	public function get_course_charts(stdClass $course) {
		$list = array(
			$this->report_overviewstats_chart_enrolments($course),
		);
		return $list;
	}

	/**
	 * @return array
	 */
	protected function report_overviewstats_chart_logins() {
		$main_data = $this->prepare_data_login_parday_chart();
		$title = get_string('chart-logins', 'report_overviewstats');
		$titleperday = get_string('chart-logins-perday', 'report_overviewstats');

		return array($title => array(
			$titleperday => html_writer::tag('div', $this->get_chart(new \core\chart_line(), 'Logedins', $main_data['loggedins'], $main_data['dates'], false), array(
				'id' => 'chart_logins_perday',
				'class' => 'chartplaceholder',
				'style' => 'min-height: 300px;',
				'dir' => 'ltr',
			)),
		));
	}

	/**
	 * @return array
	 */
	protected function prepare_data_login_parday_chart() {
		global $DB, $CFG;

		$now = strtotime('today midnight');

		$lastmonth = array();
		for ($i = 30; $i >= 0; $i--) {
			$lastmonth[$now - $i * DAYSECS] = array();
		}
		if ($CFG->branch >= 27) {
			$logmanger = get_log_manager();
			if ($CFG->branch >= 29) {
				$readers = $logmanger->get_readers('\core\log\sql_reader');
			} else {
				$readers = $logmanger->get_readers('\core\log\sql_select_reader');
			}
			$reader = reset($readers);
			$params = array('component' => 'core',
				'eventname' => '\core\event\user_loggedin',
				'guestid' => $CFG->siteguest,
				'timestart' => $now - 30 * DAYSECS);
			$select = "component = :component AND eventname = :eventname AND userid <> :guestid AND timecreated >= :timestart";
			$rs = $reader->get_events_select($select, $params, 'timecreated DESC', 0, 0);

			foreach ($rs as $record) {
				foreach (array_reverse($lastmonth, true) as $timestamp => $loggedin) {
					$date = usergetdate($timestamp);
					if ($record->timecreated >= $timestamp) {
						$lastmonth[$timestamp][$record->userid] = true;
						break;
					}
				}
			}
		} else {
			$sql = "SELECT time, userid
			          FROM {log}
			          WHERE time >= :timestart AND userid <> :guestid AND action = 'login'";

			$params = array('timestart' => $now - 30 * DAYSECS, 'guestid' => $CFG->siteguest);

			$rs = $DB->get_recordset_sql($sql, $params);

			foreach ($rs as $record) {
				foreach (array_reverse($lastmonth, true) as $timestamp => $loggedin) {
					$date = usergetdate($timestamp);
					if ($record->time >= $timestamp) {
						$lastmonth[$timestamp][$record->userid] = true;
						break;
					}
				}
			}
			$rs->close();
		}
		$main_data = [
			'dates' => [],
			'loggedins' => [],
		];
		$format = get_string('strftimedateshort', 'core_langconfig');
		foreach ($lastmonth as $timestamp => $loggedin) {
			$date = userdate($timestamp, $format);
			$main_data['dates'][] = $date;
			$main_data['loggedins'][] = count($loggedin);
		}

		return $main_data;
	}

	/**
	 * @return array
	 */
	protected function report_overviewstats_chart_countries() {
		$main_data = $this->prepare_data_chart_countries();

		$title = get_string('chart-countries', 'report_overviewstats');
		$info = html_writer::div(get_string('chart-countries-info', 'report_overviewstats', count($main_data['counts'])), 'chartinfo');
		$chart = html_writer::tag('div', $this->get_chart(new \core\chart_bar(), 'Nuber of user', $main_data['counts'], $main_data['countrys'], true), array(
			'id' => 'chart_countries',
			'class' => 'chartplaceholder',
			'style' => 'min-height: ' . max(66, (count($main_data['counts']) * 20)) . 'px;',
			'dir' => 'ltr',
		));

		return array($title => $info . $chart);
	}

	/**
	 * @return array
	 */
	protected function prepare_data_chart_countries() {
		global $DB;

		$sql = "SELECT country, COUNT(*)
							FROM {user}
						 WHERE country IS NOT NULL AND country <> '' AND deleted = 0 AND confirmed = 1
					GROUP BY country
					ORDER BY COUNT(*) DESC, country ASC";

		$main_data = [
			'countrys' => [],
			'counts' => [],
		];
		foreach ($DB->get_records_sql_menu($sql) as $country => $count) {
			if (get_string_manager()->string_exists($country, 'core_countries')) {
				$countryname = get_string($country, 'core_countries');
			} else {
				$countryname = $country;
			}
			$main_data['countrys'][] = $countryname;
			$main_data['counts'][] = $count;
		}
		return $main_data;
	}

	/**
	 * @return array
	 */
	protected function report_overviewstats_chart_langs() {
		$main_data = $this->prepare_data_chart_langs();

		$title = get_string('chart-langs', 'report_overviewstats');
		$info = html_writer::div(get_string('chart-langs-info', 'report_overviewstats', count($main_data['counts'])), 'chartinfo');
		$chart = html_writer::tag('div', $this->get_chart(new \core\chart_bar(), 'Nuber of user', $main_data['counts'], $main_data['languages'], true), array(
			'id' => 'chart_langs',
			'class' => 'chartplaceholder',
			'style' => 'min-height: ' . max(66, (count($main_data['counts']) * 20)) . 'px;',
			'dir' => 'ltr',
		));

		return array($title => $info . $chart);
	}

	/**
	 * @return array
	 */
	protected function prepare_data_chart_langs() {
		global $DB;

		$sql = "SELECT lang, COUNT(*)
		          FROM {user}
		         WHERE deleted = 0 AND confirmed = 1
		      GROUP BY lang
		      ORDER BY COUNT(*) DESC";

		$main_data = [
			'languages' => [],
			'counts' => [],
		];
		foreach ($DB->get_records_sql_menu($sql) as $lang => $count) {
			if (get_string_manager()->translation_exists($lang)) {
				$langname = get_string_manager()->get_string('thislanguageint', 'core_langconfig', null, $lang);
			} else {
				$langname = $lang;
			}
			$main_data['languages'][] = $langname;
			$main_data['counts'][] = $count;
		}

		return $main_data;
	}

	/**
	 * @return array
	 */
	protected function report_overviewstats_chart_courses() {
		global $OUTPUT;

		$main_data = $this->prepare_data_chart_courses();

		$title = get_string('chart-courses', 'report_overviewstats');
		$titlepercategory = get_string('chart-courses-percategory', 'report_overviewstats');

		$percategorydata = new html_table();
		$percategorydata->head = array(
			get_string('chart-courses-percategory-categoryname', 'report_overviewstats'),
			get_string('chart-courses-percategory-coursesrecursive', 'report_overviewstats'),
			get_string('chart-courses-percategory-coursesown', 'report_overviewstats'),
		);
		foreach ($main_data['percategory'] as $catdata) {
			$percategorydata->data[] = new html_table_row(array(
				$catdata['categoryname'],
				$catdata['coursesrecursive'],
				$catdata['coursesown'],
			));
		}

		$titlesizes = sprintf('%s %s', get_string('chart-courses-sizes', 'report_overviewstats'),
			$OUTPUT->help_icon('chart-courses-sizes', 'report_overviewstats'));

		return array($title => array(
			$titlepercategory => html_writer::tag('div',
				html_writer::table($percategorydata),
				array(
					'id' => 'chart_courses_percategory',
					'class' => 'simple_data_table',
				)
			),
			$titlesizes => html_writer::tag('div', $this->get_chart(new \core\chart_bar(), 'Nuber of courses', $main_data['sizes']['courses'], $main_data['sizes']['course_size'], false), array(
				'id' => 'chart_courses_sizes',
				'class' => 'chartplaceholder',
				'style' => 'min-height: 300px;',
				'dir' => 'ltr',
			)),
		));
	}

	/**
	 * @return array
	 */
	protected function prepare_data_chart_courses() {
		global $DB;
		$main_data = [];
		// Number of courses per category.
		$cats = core_course_category::make_categories_list();
		$main_data['percategory'] = array();
		$total = 0;

		foreach ($cats as $catid => $catname) {
			$cat = core_course_category::get($catid);
			$coursesown = $cat->get_courses_count();
			$total += $coursesown;
			$main_data['percategory'][] = array(
				'categoryname' => $catname,
				'coursesrecursive' => $cat->get_courses_count(array('recursive' => true)),
				'coursesown' => $coursesown,
			);
		}

		$main_data['percategory'][] = array(
			'categoryname' => html_writer::tag('strong', get_string('total')),
			'coursesrecursive' => '',
			'coursesown' => html_writer::tag('strong', $total),
		);

		// Distribution graph of number of activities per course.
		$sql = "SELECT course, COUNT(id) AS modules
		          FROM {course_modules}
		         GROUP BY course";

		$rs = $DB->get_recordset_sql($sql);

		$max = 0;
		$data = [];
		$main_data['sizes'] = [
			'course_size' => [],
			'courses' => [],
		];

		foreach ($rs as $record) {
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

		$rs->close();

		for ($i = 0; $i <= $max; $i++) {
			if (!isset($data[$i])) {
				$data[$i] = 0;
			}
		}
		ksort($data);

		foreach ($data as $distributiongroup => $courses) {
			$distributiongroupname = sprintf("%d-%d", $distributiongroup * 5, $distributiongroup * 5 + 4);
			$main_data['sizes']['course_size'][] = $distributiongroupname;
			$main_data['sizes']['courses'][] = $courses;
		}

		return $main_data;
	}

	/**
	 * @return array
	 */
	protected function report_overviewstats_chart_enrolments($course) {
		$main_data = $this->prepare_data_chart_enrollments($course);

		$title = get_string('chart-enrolments', 'report_overviewstats');
		$titlemonth = get_string('chart-enrolments-month', 'report_overviewstats');
		$titleyear = get_string('chart-enrolments-year', 'report_overviewstats');

		return array($title => array(
			$titlemonth => html_writer::tag('div', $this->get_chart(new \core\chart_line(), 'Enrolled', $main_data['lastmonth']['enrolled'], $main_data['lastmonth']['date'], false), array(
				'id' => 'chart_enrolments_lastmonth',
				'class' => 'chartplaceholder',
				'style' => 'min-height: 300px;',
			)),
			$titleyear => html_writer::tag('div', $this->get_chart(new \core\chart_line(), 'Enrolled', $main_data['lastyear']['enrolled'], $main_data['lastyear']['date'], false), array(
				'id' => 'chart_enrolments_lastyear',
				'class' => 'chartplaceholder',
				'style' => 'min-height: 300px;',
			)),
		));
	}

	protected function prepare_data_chart_enrollments($course) {
		global $DB, $CFG;

		if (is_null($course)) {
			throw new coding_exception('Course level report invoked without the reference to the course!');
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

		$lastmonth = array();
		for ($i = 30; $i >= 0; $i--) {
			$lastmonth[$now - $i * DAYSECS] = $current;
		}

		$lastyear = array();
		for ($i = 12; $i >= 0; $i--) {
			$lastyear[$now - $i * 30 * DAYSECS] = $current;
		}

		// Fetch all the enrol/unrol log entries from the last year.
		if ($CFG->branch >= 27) {

			$logmanger = get_log_manager();
			if ($CFG->branch >= 29) {
				$readers = $logmanger->get_readers('\core\log\sql_reader');
			} else {
				$readers = $logmanger->get_readers('\core\log\sql_select_reader');
			}
			$reader = reset($readers);
			$select = "component = :component AND (eventname = :eventname1 OR eventname = :eventname2) AND timecreated >= :timestart";
			$params = array(
				'component' => 'core',
				'eventname1' => '\core\event\user_enrolment_created',
				'eventname2' => '\core\event\user_enrolment_deleted',
				'timestart' => $now - 30 * DAYSECS,
			);
			$events = $reader->get_events_select($select, $params, 'timecreated DESC', 0, 0);

			foreach ($events as $event) {
				foreach (array_reverse($lastmonth, true) as $key => $value) {
					if ($event->timecreated >= $key) {
						// We need to amend all days up to the key.
						foreach ($lastmonth as $mkey => $mvalue) {
							if ($mkey <= $key) {
								if ($event->eventname === '\core\event\user_enrolment_created' and $lastmonth[$mkey] > 0) {
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
								if ($event->eventname === '\core\event\user_enrolment_created' and $lastyear[$ykey] > 0) {
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

		} else {
			$sql = "SELECT time, action
			          FROM {log}
			         WHERE time >= :timestart AND course = :courseid AND (action = 'enrol' OR action = 'unenrol')";

			$params = array(
				'timestart' => $now - YEARSECS,
				'courseid' => $this->course->id,
			);

			$rs = $DB->get_recordset_sql($sql, $params);

			foreach ($rs as $record) {
				foreach (array_reverse($lastmonth, true) as $key => $value) {
					if ($record->time >= $key) {
						// We need to amend all days up to the key.
						foreach ($lastmonth as $mkey => $mvalue) {
							if ($mkey <= $key) {
								if ($record->action === 'enrol' and $lastmonth[$mkey] > 0) {
									$lastmonth[$mkey]--;
								} else if ($record->action === 'unenrol') {
									$lastmonth[$mkey]++;
								}
							}
						}
						break;
					}
				}
				foreach (array_reverse($lastyear, true) as $key => $value) {
					if ($record->time >= $key) {
						// We need to amend all months up to the key.
						foreach ($lastyear as $ykey => $yvalue) {
							if ($ykey <= $key) {
								if ($record->action === 'enrol' and $lastyear[$ykey] > 0) {
									$lastyear[$ykey]--;
								} else if ($record->action === 'unenrol') {
									$lastyear[$ykey]++;
								}
							}
						}
						break;
					}
				}
			}

			$rs->close();
		}

		$main_data = [
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
			$main_data['lastmonth']['date'][] = $date;
			$main_data['lastmonth']['enrolled'][] = $enrolled;
		}
		foreach ($lastyear as $timestamp => $enrolled) {
			$date = userdate($timestamp, $format);
			$main_data['lastyear']['date'][] = $date;
			$main_data['lastyear']['enrolled'][] = $enrolled;
		}

		return $main_data;
	}

	/**
	 * @return chart html
	 */
	protected function get_chart($chart, $series_name, $series_data, $labels_data, $is_horizontal) {
		global $OUTPUT;
		$series = new \core\chart_series($series_name, $series_data);
		$labels = $labels_data;
		if ($is_horizontal) {
			$chart->set_horizontal(true);
		}
		$chart->add_series($series);
		$chart->set_labels($labels);
		return $OUTPUT->render($chart);
	}
}