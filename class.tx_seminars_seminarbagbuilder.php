<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class 'tx_seminars_seminarbagbuilder' for the 'seminars' extension.
 *
 * This builder class creates customized seminarbag objects.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_bagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_seminarbag.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_registrationbag.php');

class tx_seminars_seminarbagbuilder extends tx_seminars_bagbuilder {
	/** class name of the bag class that will be built */
	protected $bagClassName = 'tx_seminars_seminarbag';

	/** list of the valid keys for time-frames */
	private static $validTimeFrames = array(
		'past', 'pastAndCurrent', 'current', 'currentAndUpcoming', 'upcoming',
		'deadlineNotOver', 'all'
	);

	/**
	 * Configures the seminar bag to work like a BE list: It will use the
	 * default sorting in the BE, and hidden records will be shown.
	 *
	 * @access	public
	 */
	public function setBackEndMode() {
		$this->useBackEndSorting();
		parent::setBackEndMode();
	}

	/**
	 * Sets the sorting to be the same as in the BE.
	 */
	private function useBackEndSorting() {
		// unserializes the configuration array
		$globalConfiguration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']
		);
		$this->orderBy = ($globalConfiguration['useManualSorting'])
			? 'sorting' : 'begin_date DESC';
	}

	/**
	 * Limits the bag to events from any of the categories with the UIDs
	 * provided as the parameter $categoryUids.
	 *
	 * @param	string		comma-separated list of UIDs of the categories which
	 * 						the bag should be limited to, set to an empty string
	 * 						for no limitation
	 */
	public function limitToCategories($categoryUids) {
		if ($categoryUids == '') {
			unset($this->whereClauseParts['categories']);
			return;
		}

		$this->whereClauseParts['categories']
			= '(' .
			'(object_type=' . SEMINARS_RECORD_TYPE_COMPLETE . ' AND ' .
			'EXISTS (SELECT * FROM ' .
			SEMINARS_TABLE_CATEGORIES_MM . ' WHERE ' .
			SEMINARS_TABLE_CATEGORIES_MM . '.uid_local=' .
			SEMINARS_TABLE_SEMINARS . '.uid AND ' .
			SEMINARS_TABLE_CATEGORIES_MM . '.uid_foreign IN(' . $categoryUids .
			')' .
			'))' .
			' OR ' .
			'(object_type=' . SEMINARS_RECORD_TYPE_DATE . ' AND ' .
			'EXISTS (SELECT * FROM ' .
			SEMINARS_TABLE_CATEGORIES_MM . ' WHERE ' .
			SEMINARS_TABLE_CATEGORIES_MM . '.uid_local=' .
			SEMINARS_TABLE_SEMINARS . '.topic AND ' .
			SEMINARS_TABLE_CATEGORIES_MM . '.uid_foreign IN(' . $categoryUids .
			')' .
			'))' .
			')';
	}

	/**
	 * Limits the bag to events at any of the places with the UIDs provided as
	 * the parameter $placeUids.
	 *
	 * @param	string		comma-separated list of UIDs of the placed which
	 * 						the bag should be limited to, set to an empty string
	 * 						for no limitation
	 */
	public function limitToPlaces($placeUids) {
		if ($placeUids == '') {
			unset($this->whereClauseParts['places']);
			return;
		}

		$this->whereClauseParts['places']
			= '(object_type IN(' . SEMINARS_RECORD_TYPE_COMPLETE . ',' .
			SEMINARS_RECORD_TYPE_DATE . ') AND ' .
			'EXISTS (SELECT * FROM ' .
			SEMINARS_TABLE_SITES_MM . ' WHERE ' .
			SEMINARS_TABLE_SITES_MM . '.uid_local=' .
			SEMINARS_TABLE_SEMINARS . '.uid AND ' .
			SEMINARS_TABLE_SITES_MM . '.uid_foreign IN(' . $placeUids .
			')' .
			'))';
	}

	/**
	 * Sets the bag to ignore canceled events.
	 */
	public function ignoreCanceledEvents() {
		$this->whereClauseParts['hideCanceledEvents'] = 'cancelled=0';
	}

	/**
	 * Allows the bag to include canceled events again.
	 */
	public function allowCanceledEvents() {
		unset($this->whereClauseParts['hideCanceledEvents']);
	}

	/**
	 * Sets the time-frame for the events that will be found by this bag.
	 *
	 * @param	string		key for the selected time-frame, must not be empty,
	 * 						must be one of the following:
	 *						past, pastAndCurrent, current, currentAndUpcoming,
	 *						upcoming, deadlineNotOver, all
	 */
	public function setTimeFrame($timeFrameKey) {
		if (!in_array($timeFrameKey, self::$validTimeFrames)) {
			throw new Exception(
				'The time-frame key '.$timeFrameKey.' is not valid.'
			);
		}

		$now = time();

		// Works out from which time-frame we'll find event records.
		// We also need to deal with the case that an event has no end date set
		// (ie. it is open-ended).
		switch ($timeFrameKey) {
			case 'past':
				// As past events, shows the following:
				// 1. Generally, only events that have a begin date set, AND:
				// 2. If the event has an end date, does it lie in the past?, OR
				// 3. If the event has *no* end date, does the *begin* date lie
				//    in the past?
				$where = SEMINARS_TABLE_SEMINARS.'.begin_date != 0 '
					.'AND (	('.SEMINARS_TABLE_SEMINARS.'.end_date != 0 '
							.'AND '.SEMINARS_TABLE_SEMINARS.'.end_date <= '.$now
						.') OR ('
							.SEMINARS_TABLE_SEMINARS.'.end_date = 0 '
							.'AND '.SEMINARS_TABLE_SEMINARS.'.begin_date <= '.$now
						.')'
					.')';
				break;
			case 'pastAndCurrent':
				// As past and current events, shows the following:
				// 1. Generally, only events that have a begin date set, AND
				// 2. the begin date lies in the past.
				// (So events without a begin date won't be listed here.)
				$where = SEMINARS_TABLE_SEMINARS.'.begin_date != 0 '
					.'AND '.SEMINARS_TABLE_SEMINARS.'.begin_date <= '.$now;
				break;
			case 'current':
				// As current events, shows the following:
				// 1. Events that have both a begin and end date, AND
				// 2. The begin date lies in the past, AND
				// 3. The end date lies in the future.
				$where = SEMINARS_TABLE_SEMINARS.'.begin_date != 0 '
					.'AND '.SEMINARS_TABLE_SEMINARS.'.begin_date <= '.$now.' '
					// This implies that end_date is != 0.
					.'AND '.SEMINARS_TABLE_SEMINARS.'.end_date > '.$now;
				break;
			case 'currentAndUpcoming':
				// As current and upcoming events, shows the following:
				// 1. Events with an existing end date in the future, OR
				// 2. Events without an end date, but with an existing begin date
				//    in the future (open-ended events that have not started yet),
				//    OR
				// 3. Events that have no (begin) date set yet.
				$where = SEMINARS_TABLE_SEMINARS.'.end_date > '.$now
					.' OR ('
						.SEMINARS_TABLE_SEMINARS.'.end_date = 0 '
						.'AND '.SEMINARS_TABLE_SEMINARS.'.begin_date > '.$now
					.') OR '
						.SEMINARS_TABLE_SEMINARS.'.begin_date = 0';
				break;
			case 'upcoming':
				// As upcoming events, shows the following:
				// 1. Events with an existing begin date in the future
				//    (events that have not started yet), OR
				// 3. Events that have no (begin) date set yet.
				$where = SEMINARS_TABLE_SEMINARS.'.begin_date > '.$now
					.' OR '.SEMINARS_TABLE_SEMINARS.'.begin_date = 0';
				break;
			case 'deadlineNotOver':
				// As events for which the registration deadline is not over yet,
				// shows the following:
				// 1. Events that have a deadline set that lies in the future, OR
				// 2. Events that have *no* deadline set, but
				//    with an existing begin date in the future
				//    (events that have not started yet), OR
				// 3. Events that have no (begin) date set yet.
				$where = '('
						.SEMINARS_TABLE_SEMINARS.'.deadline_registration != 0 '
						.'AND '.SEMINARS_TABLE_SEMINARS
							.'.deadline_registration > '.$now
					.') OR ('
						.SEMINARS_TABLE_SEMINARS.'.deadline_registration = 0 '
						.'AND ('
							.SEMINARS_TABLE_SEMINARS.'.begin_date > '.$now
							.' OR '.SEMINARS_TABLE_SEMINARS.'.begin_date = 0'
						.')'
					.')';
				break;
			case 'all':
			default:
				// To show all events, we don't need any additional parameters.
				$where = '';
				break;
		}

		if ($where != '') {
			$this->whereClauseParts['timeFrame'] = '('.$where.')';
		}
	}

	/**
	 * Limits the bag to events from any of the event types with the UIDs
	 * provided as the parameter $eventTypeUids.
	 *
	 * @param	string		comma-separated list of UIDs of the event types
	 * 						which the bag should be limited to, set to an empty
	 * 						string for no limitation
	 */
	public function limitToEventTypes($eventTypeUids) {
		if ($eventTypeUids == '') {
			unset($this->whereClauseParts['eventTypes']);
			return;
		}

		$this->whereClauseParts['eventTypes']
			= '(' .
			'(object_type=' . SEMINARS_RECORD_TYPE_COMPLETE . ' AND ' .
			'event_type IN(' . $eventTypeUids . '))' .
			' OR ' .
			'(object_type=' . SEMINARS_RECORD_TYPE_DATE . ' AND ' .
			'EXISTS (SELECT * FROM ' .
			SEMINARS_TABLE_SEMINARS . ' AS topic WHERE ' .
			'topic.uid=' .
			SEMINARS_TABLE_SEMINARS . '.topic AND ' .
			'topic.event_type IN(' . $eventTypeUids . ')' .
			'))' .
			')';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']);
}
?>