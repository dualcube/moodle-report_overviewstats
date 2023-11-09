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
 * Reports the number of users from each country
 *
 * @package     report_overviewstats
 * @author 		DualCube <admin@dualcube.com>
 * @copyright  	Dualcube (https://dualcube.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Reports the number of users from each country
 */
class report_overviewstats_chart_countries extends report_overviewstats_chart {

	/**
	 * @return array
	 */
	public function get_content() {

		$this->prepare_data();

		$title = get_string('chart-countries', 'report_overviewstats');
		$info = html_writer::div(get_string('chart-countries-info', 'report_overviewstats', count($this->data['counts'])), 'chartinfo');
		$chart = html_writer::tag('div', $this->get_countries_chart(), array(
			'id' => 'chart_countries',
			'class' => 'chartplaceholder',
			'style' => 'min-height: ' . max(66, (count($this->data['counts']) * 20)) . 'px;',
			'dir' => 'ltr',
		));

		return array($title => $info . $chart);
	}

	/**
	 * @return chart html
	 */
	protected function get_countries_chart() {
		global $OUTPUT;
		$sales = new \core\chart_series('Nuber of user', $this->data['counts']);
		$labels = $this->data['countrys'];
		$chart = new \core\chart_bar();
		$chart->set_horizontal(true);
		$chart->add_series($sales);
		$chart->set_labels($labels);
		return $OUTPUT->render($chart);
	}

	/**
	 * Prepares data to report.
	 */
	protected function prepare_data() {
		global $DB;

		if (!is_null($this->data)) {
			return;
		}

		$sql = "SELECT country, COUNT(*)
                  FROM {user}
                 WHERE country IS NOT NULL AND country <> '' AND deleted = 0 AND confirmed = 1
              GROUP BY country
              ORDER BY COUNT(*) DESC, country ASC";

		// $data = array();
		$this->data = [
			'countrys' => [],
			'counts' => [],
		];
		foreach ($DB->get_records_sql_menu($sql) as $country => $count) {
			if (get_string_manager()->string_exists($country, 'core_countries')) {
				$countryname = get_string($country, 'core_countries');
			} else {
				$countryname = $country;
			}
			$this->data['countrys'][] = $countryname;
			$this->data['counts'][] = $count;
		}
	}
}