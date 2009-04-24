<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_seminarbagbuilder extends tx_seminars_bagbuilder {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_seminarbag';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = SEMINARS_TABLE_SEMINARS;

	/**
	 * @var array list of the valid keys for time-frames
	 */
	private static $validTimeFrames = array(
		'past', 'pastAndCurrent', 'current', 'currentAndUpcoming', 'upcoming',
		'upcomingWithBeginDate', 'deadlineNotOver', 'all',
	);

	/**
	 * @var array a list of field names in which we can search, grouped by
	 *            record type
	 *
	 * 'seminars' is the list of fields that are always stored in the seminar
	 * record.
	 * 'seminars_topic' is the list of fields that might be stored in the topic
	 * record if we are searching a date record (that refers to a topic record).
	 */
	private static $searchFieldList = array(
		'seminars' => array('accreditation_number'),
		'seminars_topic' => array('title', 'subtitle', 'teaser', 'description'),
		'speakers' => array('title', 'organization', 'description'),
		'partners' => array('title', 'organization', 'description'),
		'tutors' => array('title', 'organization', 'description'),
		'leaders' => array('title', 'organization', 'description'),
		'places' => array('title', 'address', 'city'),
		'event_types' => array('title'),
		'organizers' => array('title'),
		'target_groups' => array('title'),
		'categories' => array('title'),
	);

	/**
	 * @var string the character list to trim the search words for
	 */
	const TRIM_CHARACTER_LIST = " ,\t\n\r\0\x0b";

	/**
	 * @var integer the minimum search word length
	 */
	const MINIMUM_SEARCH_WORD_LENGTH = 2;

	/**
	 * Configures the seminar bag to work like a BE list: It will use the
	 * default sorting in the BE, and hidden records will be shown.
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
	 * @param string comma-separated list of UIDs of the categories which
	 *               the bag should be limited to, set to an empty string
	 *               for no limitation
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
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . ' WHERE ' .
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.uid_local=' .
			SEMINARS_TABLE_SEMINARS . '.uid AND ' .
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.uid_foreign IN(' . $categoryUids .
			')' .
			'))' .
			' OR ' .
			'(object_type=' . SEMINARS_RECORD_TYPE_DATE . ' AND ' .
			'EXISTS (SELECT * FROM ' .
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . ' WHERE ' .
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.uid_local=' .
			SEMINARS_TABLE_SEMINARS . '.topic AND ' .
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM . '.uid_foreign IN(' . $categoryUids .
			')' .
			'))' .
			')';
	}

	/**
	 * Limits the bag to events at any of the places with the UIDs provided as
	 * the parameter $placeUids.
	 *
	 * @param array place UIDs, set to an empty array for no limitation, need not
	 *              be SQL-safe
	 */
	public function limitToPlaces(array $placeUids = array()) {
		if (empty($placeUids)) {
			unset($this->whereClauseParts['places']);
			return;
		}

		$safePlaceUids = implode(
			',', $GLOBALS['TYPO3_DB']->cleanIntArray($placeUids)
		);

		$this->whereClauseParts['places'] = 'EXISTS (SELECT * FROM ' .
			SEMINARS_TABLE_SEMINARS_SITES_MM . ' WHERE ' .
			SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_local=' .
			SEMINARS_TABLE_SEMINARS . '.uid AND ' .
			SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_foreign IN(' . $safePlaceUids . ')' .
		')';
	}

	/**
	 * Sets the bag to ignore canceled events.
	 */
	public function ignoreCanceledEvents() {
		$this->whereClauseParts['hideCanceledEvents'] = 'cancelled!=' .
			tx_seminars_seminar::STATUS_CANCELED;
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
	 * @param string key for the selected time-frame, must not be empty,
	 *               must be one of the following:
	 *               past, pastAndCurrent, current, currentAndUpcoming,
	 *               upcoming, deadlineNotOver, all
	 */
	public function setTimeFrame($timeFrameKey) {
		if (!in_array($timeFrameKey, self::$validTimeFrames)) {
			throw new Exception(
				'The time-frame key '.$timeFrameKey.' is not valid.'
			);
		}

		$now = $GLOBALS['SIM_EXEC_TIME'];

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
					.'AND ( ('.SEMINARS_TABLE_SEMINARS.'.end_date != 0 '
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
			case 'upcomingWithBeginDate':
				$where = SEMINARS_TABLE_SEMINARS . '.begin_date > ' . $now;
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
	 * @param array event type UIDs, set to an empty array for no limitation,
	 *              need not be SQL-safe
	 */
	public function limitToEventTypes(array $eventTypeUids = array()) {
		if (empty($eventTypeUids)) {
			unset($this->whereClauseParts['eventTypes']);
			return;
		}

		$safeEventTypeUids = implode(
			',', $GLOBALS['TYPO3_DB']->cleanIntArray($eventTypeUids)
		);

		$this->whereClauseParts['eventTypes'] = '(' .
			'(object_type=' . SEMINARS_RECORD_TYPE_COMPLETE . ' AND ' .
			'event_type IN(' . $safeEventTypeUids . '))' .
			' OR ' .
			'(object_type=' . SEMINARS_RECORD_TYPE_DATE . ' AND ' .
			'EXISTS (SELECT * FROM ' .
			SEMINARS_TABLE_SEMINARS . ' AS topic WHERE ' .
			'topic.uid=' .
			SEMINARS_TABLE_SEMINARS . '.topic AND ' .
			'topic.event_type IN(' . $safeEventTypeUids . ')' .
			'))' .
		')';
	}

	/**
	 * Limits the bag to events in the cities given in the first parameter
	 * $cities.
	 *
	 * @param array array of city names, set to an empty array for no
	 *              limitation, may not be SQL-safe
	 */
	public function limitToCities(array $cities = array()) {
		if (empty($cities)) {
			unset($this->whereClauseParts['cities']);
			return;
		}

		$cityNames = implode(
			',',
			$GLOBALS['TYPO3_DB']->fullQuoteArray(
				$cities,
				SEMINARS_TABLE_SITES
			)
		);

		$this->whereClauseParts['cities'] = SEMINARS_TABLE_SEMINARS . '.uid IN(' .
			'SELECT ' . SEMINARS_TABLE_SEMINARS . '.uid' .
			' FROM ' . SEMINARS_TABLE_SEMINARS .
			' LEFT JOIN ' . SEMINARS_TABLE_SEMINARS_SITES_MM . ' ON ' .
				SEMINARS_TABLE_SEMINARS . '.uid=' .
				SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_local' .
			' LEFT JOIN ' . SEMINARS_TABLE_SITES . ' ON ' .
				SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_foreign=' .
				SEMINARS_TABLE_SITES . '.uid' .
			' WHERE ' . SEMINARS_TABLE_SITES .
				'.city IN(' . $cityNames . ')' .
		')';
	}

	/**
	 * Limits the bag to events in the countries given in the first parameter
	 * $countries.
	 *
	 * @param array ISO 3166-2 (alpha2) country codes, invalid country codes are
	 *              allowed, set to an empty array for no limitation, may not be
	 *              SQL-safe
	 */
	public function limitToCountries(array $countries = array()) {
		if (empty($countries)) {
			unset($this->whereClauseParts['countries']);
			return;
		}

		$countryCodes = implode(
			',',
			$GLOBALS['TYPO3_DB']->fullQuoteArray(
				$countries,
				SEMINARS_TABLE_SITES
			)
		);

		$this->whereClauseParts['countries'] = SEMINARS_TABLE_SEMINARS . '.uid IN(' .
			'SELECT ' . SEMINARS_TABLE_SEMINARS . '.uid' .
			' FROM ' . SEMINARS_TABLE_SEMINARS .
			' LEFT JOIN ' . SEMINARS_TABLE_SEMINARS_SITES_MM . ' ON ' .
				SEMINARS_TABLE_SEMINARS . '.uid=' .
				SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_local' .
			' LEFT JOIN ' . SEMINARS_TABLE_SITES . ' ON ' .
				SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_foreign=' .
				SEMINARS_TABLE_SITES . '.uid' .
			' WHERE ' . SEMINARS_TABLE_SITES .
				'.country IN(' . $countryCodes . ')' .
		')';
	}

	/**
	 * Limits the bag to events in the languages given in the first parameter
	 * $languages.
	 *
	 * @param array ISO 639-1 (alpha2) language codes, invalid language codes
	 *              are allowed, set to an empty array for no limitation, may
	 *              not be SQL-safe
	 */
	public function limitToLanguages(array $languages = array()) {
		if (empty($languages)) {
			unset($this->whereClauseParts['languages']);
			return;
		}

		$languageCodes = implode(
			',',
			$GLOBALS['TYPO3_DB']->fullQuoteArray(
				$languages,
				SEMINARS_TABLE_SITES
			)
		);

		$this->whereClauseParts['languages'] = SEMINARS_TABLE_SEMINARS .
			'.language IN (' . $languageCodes . ')';
	}

	/**
	 * Limits the bag to topic event records.
	 */
	public function limitToTopicRecords() {
		$this->whereClauseParts['topic'] = SEMINARS_TABLE_SEMINARS .
			'.object_type=' . SEMINARS_RECORD_TYPE_TOPIC;
	}

	/**
	 * Removes the limitation for topic event records.
	 */
	public function removeLimitToTopicRecords() {
		unset($this->whereClauseParts['topic']);
	}

	/**
	 * Limits the bag to events where the FE user given in the parameter
	 * $feUserUid is the owner.
	 *
	 * @param integer the FE user UID of the owner to limit for, set to 0 to
	 *                remove the limitation, must be >= 0
	 */
	public function limitToOwner($feUserUid) {
		if ($feUserUid < 0) {
			throw new Exception('The parameter $feUserUid must be >= 0.');
		}

		if ($feUserUid == 0) {
			unset($this->whereClauseParts['owner']);
			return;
		}

		$this->whereClauseParts['owner'] = SEMINARS_TABLE_SEMINARS .
			'.owner_feuser=' . $feUserUid;
	}

	/**
	 * Limits the bag to date and single records.
	 */
	public function limitToDateAndSingleRecords() {
		$this->whereClauseParts['date_single'] = '(' . SEMINARS_TABLE_SEMINARS .
			'.object_type=' . SEMINARS_RECORD_TYPE_DATE . ' OR ' .
			SEMINARS_TABLE_SEMINARS . '.object_type=' .
			SEMINARS_RECORD_TYPE_COMPLETE .')';
	}

	/**
	 * Removes the limitation for date and single records.
	 */
	public function removeLimitToDateAndSingleRecords() {
		unset($this->whereClauseParts['date_single']);
	}

	/**
	 * Limits the bag to events with the FE user UID given in the parameter
	 * $feUserUid as event manager.
	 *
	 * @param integer the FE user UID of the event manager to limit for, set to
	 *                0 to remove the limitation, must be >= 0
	 */
	public function limitToEventManager($feUserUid) {
		if ($feUserUid < 0) {
			throw new Exception('The parameter $feUserUid must be >= 0.');
		}

		if ($feUserUid == 0) {
			$this->removeAdditionalTableName(SEMINARS_TABLE_SEMINARS_MANAGERS_MM);
			unset($this->whereClauseParts['vip']);
			return;
		}

		$this->addAdditionalTableName(SEMINARS_TABLE_SEMINARS_MANAGERS_MM);
		$this->whereClauseParts['vip'] = SEMINARS_TABLE_SEMINARS . '.uid=' .
			SEMINARS_TABLE_SEMINARS_MANAGERS_MM . '.uid_local AND ' .
			SEMINARS_TABLE_SEMINARS_MANAGERS_MM . '.uid_foreign=' . $feUserUid;
	}

	/**
	 * Limits the bag to events on the day after the end date of the event given
	 * event in the first parameter $event.
	 *
	 * @param tx_seminars_seminar the event object with the end date to
	 *                            limit for, must have an end date
	 */
	public function limitToEventsNextDay(tx_seminars_seminar $event) {
		if (!$event->hasEndDate()) {
			throw new Exception(
				'The event object given in the first parameter $event must ' .
					'have an end date set.'
			);
		}

		$endDate = $event->getEndDateAsTimestamp();
		$midnightBeforeEndDate = $endDate - ($endDate % ONE_DAY);
		$secondMidnightAfterEndDate = $midnightBeforeEndDate + 2 * ONE_DAY;

		$this->whereClauseParts['next_day'] = 'begin_date>=' . $endDate .
			' AND begin_date<' . $secondMidnightAfterEndDate;
	}

	/**
	 * Removes the limitation to events on the next day.
	 */
	public function removeLimitToEventsNextDay() {
		unset($this->whereClauseParts['next_day']);
	}

	/**
	 * Limits the bag to date event records of the same topic as the event
	 * given in the first parameter $event.
	 *
	 * @param tx_seminars_seminar the date or topic object to find other
	 *                            dates of the same topic for
	 */
	public function limitToOtherDatesForTopic(tx_seminars_seminar $event) {
		if (!$event->isEventDate() && !$event->isEventTopic()) {
			throw new Exception(
				'The first parameter $event must be either a date or a topic ' .
					'record.'
			);
		}

		$this->whereClauseParts['other_dates'] = '(' .
			SEMINARS_TABLE_SEMINARS . '.topic=' . $event->getTopicUid() .
			' AND ' . 'object_type=' . SEMINARS_RECORD_TYPE_DATE .
			' AND ' . 'uid!=' . $event->getUid() .
		')';
	}

	/**
	 * Removes the limitation for other dates of this topic.
	 */
	public function removeLimitToOtherDatesForTopic() {
		unset($this->whereClauseParts['other_dates']);
	}

	/**
	 * Limits the bag based on the input search words.
	 *
	 * Example: The $searchWords is "content management, system" (from an input
	 * form) and the search field list is "bodytext,header" then the output
	 * will be ' AND (bodytext LIKE "%content%" OR header LIKE "%content%")
	 * AND (bodytext LIKE "%management%" OR header LIKE "%management%")
	 * AND (bodytext LIKE "%system%" OR header LIKE "%system%")'.
	 *
	 * @param string the search words, separated by spaces or commas,
	 *               may be empty, need not be SQL-safe
	 */
	public function limitToFullTextSearch($searchWords) {
		$searchWords = trim($searchWords, self::TRIM_CHARACTER_LIST);

		if ($searchWords == '') {
			unset($this->whereClauseParts['search']);
			return;
		}

		$keywords = split('[ ,]', $searchWords);

		$allWhereParts = array();

		foreach ($keywords as $keyword) {
			$safeKeyword = $this->prepareSearchWord($keyword);

			// Only search for words with a certain length.
			// Skips the current iteration of the loop for empty search words.
			if (mb_strlen($safeKeyword) < self::MINIMUM_SEARCH_WORD_LENGTH) {
				continue;
			}

			$safeKeyword = '\'%' . $safeKeyword . '%\'';

			$wherePartsForCurrentSearchword = array_merge(
				$this->getSearchWherePartIndependentFromEventRecordType(
					$safeKeyword
				),
				$this->getSearchWherePartForEventTopics(
					$safeKeyword
				),
				$this->getSearchWherePartForSpeakers(
					$safeKeyword
				),
				$this->getSearchWherePartForPlaces(
					$safeKeyword
				),
				$this->getSearchWherePartForEventTypes(
					$safeKeyword
				),
				$this->getSearchWherePartForOrganizers(
					$safeKeyword
				),
				$this->getSearchWherePartForTargetGroups(
					$safeKeyword
				),
				$this->getSearchWherePartForCategories(
					$safeKeyword
				)
			);

			$allWhereParts[] = '(' .
				implode(' OR ', $wherePartsForCurrentSearchword) . ')';
		}

		if (!empty($allWhereParts)) {
			$this->whereClauseParts['search'] = implode(' AND ', $allWhereParts);
		} else {
			unset($this->whereClauseParts['search']);
		}
	}

	/**
	 * Limits the bag to future events for which the cancelation deadline
	 * reminder has not been sent yet.
	 */
	public function limitToCancelationDeadlineReminderNotSent() {
		$this->whereClauseParts['cancelation_reminder_not_sent']
			= SEMINARS_TABLE_SEMINARS . '.cancelation_deadline_reminder_sent = 0';
	}

	/**
	 * Limits the bag to future events for which the reminder that an event is
	 * about to take place has not been sent yet.
	 */
	public function limitToEventTakesPlaceReminderNotSent() {
		$this->whereClauseParts['event_takes_place_reminder_not_sent']
			= SEMINARS_TABLE_SEMINARS . '.event_takes_place_reminder_sent = 0';
	}

	/**
	 * Limits the bag to events in status $status.
	 *
	 * @param integer tx_seminars_seminar::STATUS_PLANNED, ::STATUS_CONFIRMED or
	 *                ::STATUS_CANCELED
	 */
	public function limitToStatus($status) {
		$this->whereClauseParts['event_status']
			= SEMINARS_TABLE_SEMINARS . '.cancelled = ' . $status;
	}

	/**
	 * Limits the bag to events which are currently $days days before their
	 * begin date.
	 *
	 * @param integer days before the begin date, must be > 0
	 */
	public function limitToDaysBeforeBeginDate($days) {
		$nowPlusDays = ($GLOBALS['SIM_EXEC_TIME'] + ($days * ONE_DAY));

		$this->whereClauseParts['days_before_begin_date'] =
			SEMINARS_TABLE_SEMINARS . '.begin_date' . ' < ' . $nowPlusDays;
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in categories
	 * based on the search word given in the first parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 *
	 * @return array the WHERE clause parts for the search in categories
	 */
	private function getSearchWherePartForCategories($searchWord) {
		return $this->getSearchWherePartInMmRelationForTopicOrSingleEventRecord(
			$searchWord,
			'categories',
			SEMINARS_TABLE_CATEGORIES,
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in target
	 * groups based on the search word given in the first parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 *
	 * @return array the WHERE clause parts for the search in target groups
	 */
	private function getSearchWherePartForTargetGroups($searchWord) {
		return $this->getSearchWherePartInMmRelationForTopicOrSingleEventRecord(
			$searchWord,
			'target_groups',
			SEMINARS_TABLE_TARGET_GROUPS,
			SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in organizers
	 * based on the search word given in the first parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 *
	 * @return array the WHERE clause parts for the search in organizers
	 */
	private function getSearchWherePartForOrganizers($searchWord) {
		return $this->getSearchWherePartForMmRelation(
			$searchWord,
			'organizers',
			SEMINARS_TABLE_ORGANIZERS,
			SEMINARS_TABLE_SEMINARS_ORGANIZERS_MM
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in event
	 * types based on the search word given in the first parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 *
	 * @return array the WHERE clause parts for the search in event types
	 */
	private function getSearchWherePartForEventTypes($searchWord) {
		$result = array();

		foreach (self::$searchFieldList['event_types'] as $field) {
			$result[] = 'EXISTS (' .
				'SELECT * FROM ' . SEMINARS_TABLE_EVENT_TYPES . ', ' .
					SEMINARS_TABLE_SEMINARS . ' s1, ' .
					SEMINARS_TABLE_SEMINARS . ' s2' .
				' WHERE (' . SEMINARS_TABLE_EVENT_TYPES . '.' . $field .
					' LIKE ' . $searchWord .
				' AND ' . SEMINARS_TABLE_EVENT_TYPES . '.uid=s1.event_type' .
				' AND ((s1.uid=s2.topic AND s2.object_type=' .
						SEMINARS_RECORD_TYPE_DATE . ') ' .
					'OR (s1.uid=s2.uid AND s1.object_type!=' .
						SEMINARS_RECORD_TYPE_DATE . '))' .
				' AND s2.uid=' . SEMINARS_TABLE_SEMINARS . '.uid)' .
			')';
		}

		return $result;
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in places
	 * based on the search word given in the first parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty
	 *
	 * @return array the WHERE clause parts for the search in places
	 */
	private function getSearchWherePartForPlaces($searchWord) {
		return $this->getSearchWherePartForMmRelation(
			$searchWord,
			'places',
			SEMINARS_TABLE_SITES,
			SEMINARS_TABLE_SEMINARS_SITES_MM
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in event
	 * topics based on the search word given in the first parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty
	 *
	 * @return array the WHERE clause parts for the search in event topics
	 */
	private function getSearchWherePartForEventTopics($searchWord) {
		$result = array();

		foreach (self::$searchFieldList['seminars_topic'] as $field) {
			$result[] = 'EXISTS (' .
				'SELECT * FROM ' . SEMINARS_TABLE_SEMINARS . ' s1,' .
						SEMINARS_TABLE_SEMINARS . ' s2' .
					' WHERE (s1.' . $field . ' LIKE ' . $searchWord .
						' AND ((s1.uid=s2.topic AND s2.object_type=' .
							SEMINARS_RECORD_TYPE_DATE . ') ' .
							' OR (s1.uid=s2.uid AND s1.object_type!=' .
							SEMINARS_RECORD_TYPE_DATE . ')))' .
					' AND s2.uid=' . SEMINARS_TABLE_SEMINARS . '.uid' .
			')';
		}

		return $result;
	}

	/**
	 * Generates and returns the WHERE clause parts for the search independent
	 * from the event record type based on the search word given in the first
	 * parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 *
	 * @return array the WHERE clause parts for the search independent
	 *               from the event record type
	 */
	private function getSearchWherePartIndependentFromEventRecordType(
		$searchWord
	) {
		$result = array();

		foreach (self::$searchFieldList['seminars'] as $field) {
			$result[] = SEMINARS_TABLE_SEMINARS . '.' . $field .
				' LIKE ' . $searchWord;
		}

		return $result;
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in speakers
	 * based on the search word given in the first parameter $searchWord.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 *
	 * @return array the WHERE clause parts for the search in speakers
	 */
	private function getSearchWherePartForSpeakers($searchWord) {
		$mmTables = array(
			'speakers' => SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			'partners' => SEMINARS_TABLE_SEMINARS_PARTNERS_MM,
			'tutors' => SEMINARS_TABLE_SEMINARS_TUTORS_MM,
			'leaders' => SEMINARS_TABLE_SEMINARS_LEADERS_MM,
		);

		$result = array();

		foreach ($mmTables as $key => $currentMmTable) {
			$result = array_merge(
				$result,
				$this->getSearchWherePartForMmRelation(
					$searchWord,
					$key,
					SEMINARS_TABLE_SPEAKERS,
					$currentMmTable
				)
			);
		}

		return $result;
	}

	/**
	 * Generates and returns the WHERE clause part for the search in an m:n
	 * relation between a date or single event record.
	 *
	 * Searches for $searchWord in $field in $foreignTable using the m:n table
	 * $mmTable.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 * @param string the key of the search field list, must not be empty,
	 *               must be an valid key of $this->searchFieldList
	 * @param string the foreign table to search in, must not be empty
	 * @param string the m:n relation table, must not be empty
	 *
	 * @return array the WHERE clause parts for the search in categories
	 */
	private function getSearchWherePartInMmRelationForTopicOrSingleEventRecord(
		$searchWord, $searchFieldKey, $foreignTable, $mmTable
	) {
		$this->checkParametersForMmSearchFunctions(
			$searchWord, $searchFieldKey, $foreignTable, $mmTable
		);

		$result = array();

		foreach (self::$searchFieldList[$searchFieldKey] as $field) {
			$result[] = 'EXISTS ' .
				'(SELECT * FROM ' .
					SEMINARS_TABLE_SEMINARS . ' s1, ' .
					$mmTable . ', ' .
					$foreignTable .
				' WHERE ((' . SEMINARS_TABLE_SEMINARS . '.object_type=' .
						SEMINARS_RECORD_TYPE_DATE .
						' AND s1.object_type!=' . SEMINARS_RECORD_TYPE_DATE .
						' AND ' . SEMINARS_TABLE_SEMINARS . '.topic=s1.uid)' .
					' OR (' . SEMINARS_TABLE_SEMINARS . '.object_type=' .
						SEMINARS_RECORD_TYPE_COMPLETE .
						' AND ' . SEMINARS_TABLE_SEMINARS . '.uid=s1.uid))' .
					' AND ' . $mmTable . '.uid_local=s1.uid' .
					' AND ' . $mmTable . '.uid_foreign=' .
						$foreignTable . '.uid' .
					' AND ' . $foreignTable . '.' . $field .
						' LIKE ' . $searchWord .
			')';
		}

		return $result;
	}

	/**
	 * Generates and returns the WHERE clause part for the search in an m:n
	 * relation between a date or single event record.
	 *
	 * Searches for $searchWord in $field in $foreignTable using the m:n table
	 * $mmTable.
	 *
	 * @param string the current search word, must not be empty,
	 *               must be SQL-safe and quoted for LIKE
	 * @param string the key of the search field list, must not be empty,
	 *               must be an valid key of $this->searchFieldList
	 * @param string the foreign table to search in, must not be empty
	 * @param string the m:n relation table, must not be empty
	 *
	 * @return array the WHERE clause parts for the search in categories
	 */
	private function getSearchWherePartForMmRelation(
		$searchWord, $searchFieldKey, $foreignTable, $mmTable
	) {
		$this->checkParametersForMmSearchFunctions(
			$searchWord, $searchFieldKey, $foreignTable, $mmTable
		);

		$result = array();

		foreach (self::$searchFieldList[$searchFieldKey] as $field) {
			$result[] = 'EXISTS (' .
				'SELECT * FROM ' . $foreignTable . ', ' . $mmTable .
					' WHERE ' . $foreignTable . '.' . $field .
							' LIKE ' . $searchWord .
						' AND ' . $mmTable . '.uid_local=' .
							SEMINARS_TABLE_SEMINARS . '.uid' .
						' AND ' . $mmTable . '.uid_foreign=' .
							$foreignTable . '.uid' .
			')';
		}

		return $result;
	}

	/**
	 * SQL-escapes and trims a potential search word.
	 *
	 * @param string single search word (may be prefixed or postfixed
	 *               with spaces), may be empty
	 *
	 * @return string the trimmed and SQL-escaped $searchword
	 */
	private function prepareSearchWord($searchWord) {
		return $GLOBALS['TYPO3_DB']->escapeStrForLike(
			$GLOBALS['TYPO3_DB']->quoteStr(
				trim($searchWord, self::TRIM_CHARACTER_LIST),
				SEMINARS_TABLE_SEMINARS
			),
			SEMINARS_TABLE_SEMINARS
		);
	}

	/**
	 * Checks the parameters for the m:n search functions and throws exceptions
	 * if at least one of the parameters is empty.
	 *
	 * @param string the current search word, must not be empty,
	 *               may be quoted for LIKE
	 * @param string the key of the search field list, must not be empty,
	 *               must be an valid key of self::$searchFieldList
	 * @param string the foreign table to search in, must not be empty
	 * @param string the m:n relation table, must not be empty
	 */
	private function checkParametersForMmSearchFunctions($searchWord, $searchFieldKey, $foreignTable, $mmTable) {
		if (trim($searchWord, self::TRIM_CHARACTER_LIST . '\'%') == '') {
			throw new Exception(
				'The first parameter $searchWord must no be empty.'
			);
		}

		if ($searchFieldKey == '') {
			throw new Exception(
				'The second parameter $searchFieldKey must not be empty.'
			);
		}

		if (!array_key_exists($searchFieldKey, self::$searchFieldList)) {
			throw new Exception(
				'The second parameter $searchFieldKey must be a valid key of ' .
					'self::$searchFieldList.'
			);
		}

		if ($foreignTable == '') {
			throw new Exception(
				'The third parameter $foreignTable must not be empty.'
			);
		}

		if ($mmTable == '') {
			throw new Exception(
				'The fourth parameter $mmTable must not be empty.'
			);
		}
	}

	/**
	 * Limits the search results to topics which are required for the
	 * given topic.
	 *
	 * @param integer the UID of the topic event for which the requirements
	 *                should be found, must be > 0
	 */
	public function limitToRequiredEventTopics($eventUid) {
		$this->whereClauseParts['requiredEventTopics'] =
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM . '.uid_local=' . $eventUid .
			' AND ' . SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM . '.uid_foreign=' .
			SEMINARS_TABLE_SEMINARS . '.uid';
		$this->addAdditionalTableName(SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM);
		$this->setOrderBy(SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM . '.sorting');
	}

	/**
	 * Limits the search result to topics which depend on the given topic.
	 *
	 * @param integer the UID of the topic event which the searched events
	 *                depend on, must be > 0
	 */
	public function limitToDependingEventTopics($eventUid) {
		$this->whereClauseParts['dependingEventTopics'] =
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM . '.uid_foreign=' . $eventUid .
			' AND '. SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM . '.uid_local=' .
			SEMINARS_TABLE_SEMINARS . '.uid';
		$this->addAdditionalTableName(SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM);
		$this->setOrderBy(
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM . '.sorting_foreign ASC'
		);
	}

	/**
	 * Limits the search result to topics for which there is no registration by
	 * the front-end user with the UID $uid.
	 *
	 * Registrations for dates that have a non-zero expiry date in the past will
	 * be counted as not existing.
	 *
	 * @param integer the UID of the front-end user whose registered events
	 *                should be removed from the bag, must be > 0
	 */
	public function limitToTopicsWithoutRegistrationByUser($uid) {
		$this->limitToTopicRecords();
		$this->whereClauseParts['topicsWithoutUserRegistration'] =
			'NOT EXISTS (' .
				'SELECT * FROM ' . SEMINARS_TABLE_ATTENDANCES . ', ' .
					SEMINARS_TABLE_SEMINARS . ' dates ' .
				'WHERE ' . SEMINARS_TABLE_ATTENDANCES . '.user = ' . $uid .
				' AND ' . SEMINARS_TABLE_ATTENDANCES . '.seminar = dates.uid' .
				' AND dates.topic = ' . SEMINARS_TABLE_SEMINARS . '.uid' .
				' AND (dates.expiry = 0 OR dates.expiry > ' .
					$GLOBALS['SIM_EXEC_TIME'] . ')' .
			')';
	}

	/**
	 * Limits the bag to events which have a begin_date greater than the given
	 * time-stamp or without a begin_date.
	 *
	 * A $earliestBeginDate of 0 will remove the filter.
	 *
	 * @param integer the earliest begin date as UNIX time-stamp, 0 will remove
	 *                the limit
	 */
	public function limitToEarliestBeginDate($earliestBeginDate) {
		if ($earliestBeginDate == 0) {
			unset($this->whereClauseParts['earliestBeginDate']);

			return;
		}

		$this->whereClauseParts['earliestBeginDate'] = '(' .
			SEMINARS_TABLE_SEMINARS . '.begin_date = 0 OR ' .
			SEMINARS_TABLE_SEMINARS . '.begin_date >= '. $earliestBeginDate .
			')';
	}

	/**
	 * Limits the bag to events which have a begin_date lower than the given
	 * time-stamp, but greater than zero.
	 *
	 * A $latestBeginDate of 0 will remove the filter.
	 *
	 * @param integer the latest begin date as UNIX time-stamp, 0 will remove
	 *                the limit
	 */
	public function limitToLatestBeginDate($latestBeginDate) {
		if ($latestBeginDate == 0) {
			unset($this->whereClauseParts['latestBeginDate']);

			return;
		}

		$this->whereClauseParts['latestBeginDate'] =
			SEMINARS_TABLE_SEMINARS . '.begin_date > 0 AND ' .
			SEMINARS_TABLE_SEMINARS . '.begin_date <= ' . $latestBeginDate;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminarbagbuilder.php']);
}
?>