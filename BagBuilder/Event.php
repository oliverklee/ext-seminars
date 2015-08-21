<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This builder class creates customized event bags.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BagBuilder_Event extends tx_seminars_BagBuilder_Abstract {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_Bag_Event';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = 'tx_seminars_seminars';

	/**
	 * @var string[] list of the valid keys for time-frames
	 */
	private static $validTimeFrames = array(
		'past', 'pastAndCurrent', 'current', 'currentAndUpcoming', 'upcoming',
		'upcomingWithBeginDate', 'deadlineNotOver', 'all', 'today',
	);

	/**
	 * @var string[][] a list of field names of m:n associations in which we can search, grouped by record type
	 */
	private static $searchFieldList = array(
		'speakers' => array('title'),
		'places' => array('title', 'city'),
		'categories' => array('title'),
		'target_groups' => array('title'),
	);

	/**
	 * @var string the character list to trim the search words for
	 */
	const TRIM_CHARACTER_LIST = " ,\t\n\r\0\x0b";

	/**
	 * @var int the minimum search word length
	 */
	const MINIMUM_SEARCH_WORD_LENGTH = 4;

	/**
	 * Configures the seminar bag to work like a BE list: It will use the
	 * default sorting in the BE, and hidden records will be shown.
	 *
	 * @return void
	 */
	public function setBackEndMode() {
		$this->useBackEndSorting();
		parent::setBackEndMode();
	}

	/**
	 * Sets the sorting to be the same as in the BE.
	 *
	 * @return void
	 */
	private function useBackEndSorting() {
		$this->orderBy = 'begin_date DESC';
	}

	/**
	 * Limits the bag to events from any of the categories with the UIDs
	 * provided as the parameter $categoryUids.
	 *
	 * @param string $categoryUids
	 *        comma-separated list of UIDs of the categories which the bag
	 *        should be limited to, set to an empty string for no limitation
	 *
	 * @return void
	 */
	public function limitToCategories($categoryUids) {
		if ($categoryUids === '') {
			unset($this->whereClauseParts['categories']);
			return;
		}

		$directMatchUids = tx_oelib_db::selectColumnForMultiple(
			'uid_local',
			'tx_seminars_seminars_categories_mm',
			'uid_foreign IN(' . $categoryUids . ')'
		);
		if (empty($directMatchUids)) {
			$this->whereClauseParts['categories'] = '(1 = 0)';
			return;
		}

		$uidMatcher = ' IN(' . implode(',', $directMatchUids) . ')';

		$this->whereClauseParts['categories'] =
			'(' .
			'(object_type <> ' . tx_seminars_Model_Event::TYPE_DATE . ' AND ' .
			'tx_seminars_seminars.uid' . $uidMatcher . ')' .
			' OR ' .
			'(object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ' AND ' .
			'tx_seminars_seminars.topic' . $uidMatcher . ')' .
			')';
	}

	/**
	 * Limits the bag to events at any of the places with the UIDs provided as
	 * the parameter $placeUids.
	 *
	 * @param string[] $placeUids place UIDs, set to an empty array for no limitation, need not be SQL-safe
	 *
	 * @return void
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
			'tx_seminars_seminars_place_mm WHERE ' .
			'tx_seminars_seminars_place_mm.uid_local = ' .
			'tx_seminars_seminars.uid AND ' .
			'tx_seminars_seminars_place_mm.uid_foreign IN(' . $safePlaceUids . ')' .
		')';
	}

	/**
	 * Sets the bag to ignore canceled events.
	 *
	 * @return void
	 */
	public function ignoreCanceledEvents() {
		$this->whereClauseParts['hideCanceledEvents'] = 'cancelled <> ' .
			tx_seminars_seminar::STATUS_CANCELED;
	}

	/**
	 * Allows the bag to include canceled events again.
	 *
	 * @return void
	 */
	public function allowCanceledEvents() {
		unset($this->whereClauseParts['hideCanceledEvents']);
	}

	/**
	 * Sets the time-frame for the events that will be found by this bag.
	 *
	 * @param string $timeFrameKey
	 *        key for the selected time-frame, must not be empty, must be one of the following:
	 *        past, pastAndCurrent, current, currentAndUpcoming, upcoming, deadlineNotOver, all
	 *
	 * @return void
	 */
	public function setTimeFrame($timeFrameKey) {
		if (!in_array($timeFrameKey, self::$validTimeFrames)) {
			throw new InvalidArgumentException('The time-frame key ' . $timeFrameKey . ' is not valid.', 1333292705);
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
				$where = 'tx_seminars_seminars.begin_date <> 0 ' .
					'AND ( (tx_seminars_seminars.end_date <> 0 ' .
							'AND tx_seminars_seminars.end_date <= ' . $now .
						') OR (' .
							'tx_seminars_seminars.end_date = 0 ' .
							'AND tx_seminars_seminars.begin_date <= ' . $now .
						')' .
					')';
				break;
			case 'pastAndCurrent':
				// As past and current events, shows the following:
				// 1. Generally, only events that have a begin date set, AND
				// 2. the begin date lies in the past.
				// (So events without a begin date won't be listed here.)
				$where = 'tx_seminars_seminars.begin_date <> 0 ' .
					'AND tx_seminars_seminars.begin_date <= ' . $now;
				break;
			case 'current':
				// As current events, shows the following:
				// 1. Events that have both a begin and end date, AND
				// 2. The begin date lies in the past, AND
				// 3. The end date lies in the future.
				$where = 'tx_seminars_seminars.begin_date <> 0 ' .
					'AND tx_seminars_seminars.begin_date <= ' . $now . ' ' .
					// This implies that end_date is != 0.
					'AND tx_seminars_seminars.end_date > ' . $now;
				break;
			case 'currentAndUpcoming':
				// As current and upcoming events, shows the following:
				// 1. Events with an existing end date in the future, OR
				// 2. Events without an end date, but with an existing begin date
				//    in the future (open-ended events that have not started yet),
				//    OR
				// 3. Events that have no (begin) date set yet.
				$where = 'tx_seminars_seminars.end_date > ' . $now .
					' OR (' .
						'tx_seminars_seminars.end_date = 0 ' .
						'AND tx_seminars_seminars.begin_date > ' . $now .
					') OR ' .
						'tx_seminars_seminars.begin_date = 0';
				break;
			case 'upcoming':
				// As upcoming events, shows the following:
				// 1. Events with an existing begin date in the future
				//    (events that have not started yet), OR
				// 3. Events that have no (begin) date set yet.
				$where = 'tx_seminars_seminars.begin_date > ' . $now .
					' OR tx_seminars_seminars.begin_date = 0';
				break;
			case 'upcomingWithBeginDate':
				$where = 'tx_seminars_seminars.begin_date > ' . $now;
				break;
			case 'deadlineNotOver':
				// As events for which the registration deadline is not over yet,
				// shows the following:
				// 1. Events that have a deadline set that lies in the future, OR
				// 2. Events that have *no* deadline set, but
				//    with an existing begin date in the future
				//    (events that have not started yet), OR
				// 3. Events that have no (begin) date set yet.
				$where = '(' .
						'tx_seminars_seminars.deadline_registration <> 0 ' .
						'AND tx_seminars_seminars.deadline_registration > ' . $now .
					') OR (' .
						'tx_seminars_seminars.deadline_registration = 0 ' .
						'AND (' .
							'tx_seminars_seminars.begin_date > ' . $now .
							' OR tx_seminars_seminars.begin_date = 0' .
						')' .
					')';
				break;
			case 'today':
				$day = date('j', $now);
				$month = date('n', $now);
				$year = date('Y', $now);

				$todayBegin = mktime(0, 0, 0, $month, $day, $year);
				$todayEnd = mktime(23, 59, 59, $month, $day, $year);

				$where = '(' .
					'tx_seminars_seminars.begin_date BETWEEN ' .
					$todayBegin . ' AND ' . $todayEnd .
					') OR ( ' .
					'tx_seminars_seminars.end_date BETWEEN ' .
					$todayBegin . ' AND ' . $todayEnd .
					') OR ( '.
					'tx_seminars_seminars.begin_date < ' . $todayBegin .
					' AND tx_seminars_seminars.end_date > ' . $todayEnd .
					')';
					break;
			case 'all':
			default:
				// To show all events, we don't need any additional parameters.
				$where = '';
				break;
		}

		if ($where != '') {
			$this->whereClauseParts['timeFrame'] = '(' . $where . ')';
		}
	}

	/**
	 * Limits the bag to events from any of the event types with the UIDs
	 * provided as the parameter $eventTypeUids.
	 *
	 * @param string[] $eventTypeUids event type UIDs, set to an empty array for no limitation, need not be SQL-safe
	 *
	 * @return void
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
			'(object_type=' . tx_seminars_Model_Event::TYPE_COMPLETE . ' AND ' .
			'event_type IN(' . $safeEventTypeUids . '))' .
			' OR ' .
			'(object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ' AND ' .
			'EXISTS (SELECT * FROM ' .
			'tx_seminars_seminars AS topic WHERE ' .
			'topic.uid = ' .
			'tx_seminars_seminars.topic AND ' .
			'topic.event_type IN(' . $safeEventTypeUids . ')' .
			'))' .
		')';
	}

	/**
	 * Limits the bag to events in the cities given in the first parameter
	 * $cities.
	 *
	 * @param string[] $cities city names, set to an empty array for no limitation, may not be SQL-safe
	 *
	 * @return void
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
				'tx_seminars_sites'
			)
		);

		$this->whereClauseParts['cities'] = 'tx_seminars_seminars.uid IN(' .
			'SELECT tx_seminars_seminars.uid' .
			' FROM tx_seminars_seminars' .
			' LEFT JOIN tx_seminars_seminars_place_mm ON ' .
				'tx_seminars_seminars.uid=' .
				'tx_seminars_seminars_place_mm.uid_local' .
			' LEFT JOIN tx_seminars_sites ON ' .
				'tx_seminars_seminars_place_mm.uid_foreign = ' .
				'tx_seminars_sites.uid' .
			' WHERE tx_seminars_sites.city IN(' . $cityNames . ')' .
		')';
	}

	/**
	 * Limits the bag to events in the countries given in the first parameter
	 * $countries.
	 *
	 * @param string[] $countries
	 *        ISO 3166-2 (alpha2) country codes, invalid country codes are allowed, set to an empty array for no limitation,
	 *        need not be SQL-safe
	 *
	 * @return void
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
				'tx_seminars_sites'
			)
		);

		$this->whereClauseParts['countries'] = 'tx_seminars_seminars.uid IN(' .
			'SELECT tx_seminars_seminars.uid' .
			' FROM tx_seminars_seminars' .
			' LEFT JOIN tx_seminars_seminars_place_mm ON ' .
				'tx_seminars_seminars.uid=' .
				'tx_seminars_seminars_place_mm.uid_local' .
			' LEFT JOIN tx_seminars_sites ON ' .
				'tx_seminars_seminars_place_mm.uid_foreign = ' .
				'tx_seminars_sites.uid' .
			' WHERE tx_seminars_sites.country IN(' . $countryCodes . ')' .
		')';
	}

	/**
	 * Limits the bag to events in the languages given in the first parameter
	 * $languages.
	 *
	 * @param string[] $languages
	 *        ISO 639-1 (alpha2) language codes, invalid language codes are allowed, set to an empty array for no limitation,
	 *        need not be SQL-safe
	 *
	 * @return void
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
				'tx_seminars_sites'
			)
		);

		$this->whereClauseParts['languages'] = 'tx_seminars_seminars' .
			'.language IN (' . $languageCodes . ')';
	}

	/**
	 * Limits the bag to topic event records.
	 *
	 * @return void
	 */
	public function limitToTopicRecords() {
		$this->whereClauseParts['topic'] = 'tx_seminars_seminars' .
			'.object_type = ' . tx_seminars_Model_Event::TYPE_TOPIC;
	}

	/**
	 * Removes the limitation for topic event records.
	 *
	 * @return void
	 */
	public function removeLimitToTopicRecords() {
		unset($this->whereClauseParts['topic']);
	}

	/**
	 * Limits the bag to events where the FE user given in the parameter
	 * $feUserUid is the owner.
	 *
	 * @param int $feUserUid the FE user UID of the owner to limit for, set to 0 to remove the limitation, must be >= 0
	 *
	 * @return void
	 */
	public function limitToOwner($feUserUid) {
		if ($feUserUid < 0) {
			throw new InvalidArgumentException('The parameter $feUserUid must be >= 0.', 1333292720);
		}

		if ($feUserUid == 0) {
			unset($this->whereClauseParts['owner']);
			return;
		}

		$this->whereClauseParts['owner'] = 'tx_seminars_seminars' .
			'.owner_feuser = ' . $feUserUid;
	}

	/**
	 * Limits the bag to date and single records.
	 *
	 * @return void
	 */
	public function limitToDateAndSingleRecords() {
		$this->whereClauseParts['date_single'] = '(tx_seminars_seminars' .
			'.object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ' OR ' .
			'tx_seminars_seminars.object_type = ' .
			tx_seminars_Model_Event::TYPE_COMPLETE .')';
	}

	/**
	 * Removes the limitation for date and single records.
	 *
	 * @return void
	 */
	public function removeLimitToDateAndSingleRecords() {
		unset($this->whereClauseParts['date_single']);
	}

	/**
	 * Limits the bag to events with the FE user UID given in the parameter
	 * $feUserUid as event manager.
	 *
	 * @param int $feUserUid
	 *        the FE user UID of the event manager to limit for, set to 0 to remove the limitation, must be >= 0
	 *
	 * @return void
	 */
	public function limitToEventManager($feUserUid) {
		if ($feUserUid < 0) {
			throw new InvalidArgumentException('The parameter $feUserUid must be >= 0.', 1333292729);
		}

		if ($feUserUid == 0) {
			$this->removeAdditionalTableName('tx_seminars_seminars_feusers_mm');
			unset($this->whereClauseParts['vip']);
			return;
		}

		$this->addAdditionalTableName('tx_seminars_seminars_feusers_mm');
		$this->whereClauseParts['vip'] = 'tx_seminars_seminars.uid = ' .
			'tx_seminars_seminars_feusers_mm.uid_local AND ' .
			'tx_seminars_seminars_feusers_mm.uid_foreign = ' . $feUserUid;
	}

	/**
	 * Limits the bag to events on the day after the end date of the event given
	 * event in the first parameter $event.
	 *
	 * @param tx_seminars_seminar $event the event object with the end date to limit for, must have an end date
	 *
	 * @return void
	 */
	public function limitToEventsNextDay(tx_seminars_seminar $event) {
		if (!$event->hasEndDate()) {
			throw new InvalidArgumentException(
				'The event object given in the first parameter $event must have an end date set.', 1333292744
			);
		}

		$endDate = $event->getEndDateAsTimestamp();
		$midnightBeforeEndDate = $endDate
			- ($endDate % tx_oelib_Time::SECONDS_PER_DAY);
		$secondMidnightAfterEndDate = $midnightBeforeEndDate
			+ 2 * tx_oelib_Time::SECONDS_PER_DAY;

		$this->whereClauseParts['next_day'] = 'begin_date>=' . $endDate .
			' AND begin_date<' . $secondMidnightAfterEndDate;
	}

	/**
	 * Removes the limitation to events on the next day.
	 *
	 * @return void
	 */
	public function removeLimitToEventsNextDay() {
		unset($this->whereClauseParts['next_day']);
	}

	/**
	 * Limits the bag to date event records of the same topic as the event
	 * given in the first parameter $event.
	 *
	 * @param tx_seminars_seminar $event the date or topic object to find other dates of the same topic for
	 *
	 * @return void
	 */
	public function limitToOtherDatesForTopic(tx_seminars_seminar $event) {
		if (!$event->isEventDate() && !$event->isEventTopic()) {
			throw new InvalidArgumentException('The first parameter $event must be either a date or a topic record.', 1333292764);
		}

		$this->whereClauseParts['other_dates'] = '(' .
			'tx_seminars_seminars.topic = ' . $event->getTopicUid() .
			' AND object_type = ' . tx_seminars_Model_Event::TYPE_DATE .
			' AND uid <> ' . $event->getUid() .
		')';
	}

	/**
	 * Removes the limitation for other dates of this topic.
	 *
	 * @return void
	 */
	public function removeLimitToOtherDatesForTopic() {
		unset($this->whereClauseParts['other_dates']);
	}

	/**
	 * Limits the bag based on the input search words (using OR of full-text search).
	 *
	 * @param string $searchWords the search words, separated by spaces or commas, may be empty, need not be SQL-safe
	 *
	 * @return void
	 */
	public function limitToFullTextSearch($searchWords) {
		$searchWords = trim($searchWords, self::TRIM_CHARACTER_LIST);

		if ($searchWords === '') {
			unset($this->whereClauseParts['search']);
			return;
		}

		$keywords = preg_split('/[ ,]/', $searchWords);

		$allWhereParts = array();

		foreach ($keywords as $keyword) {
			$safeKeyword = $this->prepareSearchWord($keyword);

			// Only search for words with a certain length.
			// Skips the current iteration of the loop for empty search words.
			// We use strlen instead of mb_strlen because having a search word
			// consisting of just an umlaut is okay, and this avoids problems
			// on installations without mb_string enabled.
			if (strlen($safeKeyword) < self::MINIMUM_SEARCH_WORD_LENGTH) {
				continue;
			}

			$safeKeyword = '"' . $safeKeyword . '"';

			$wherePartsForCurrentSearchWord = array_merge(
				$this->getSearchWherePartIndependentFromEventRecordType($safeKeyword),
				$this->getSearchWherePartForEventTopics($safeKeyword),
				$this->getSearchWherePartForSpeakers($safeKeyword),
				$this->getSearchWherePartForPlaces($safeKeyword),
				$this->getSearchWherePartForEventTypes($safeKeyword),
				$this->getSearchWherePartForCategories($safeKeyword),
				$this->getSearchWherePartForTargetGroups($safeKeyword)
			);

			if (!empty($wherePartsForCurrentSearchWord)) {
				$allWhereParts[] = '(' . implode(' OR ', $wherePartsForCurrentSearchWord) . ')';
			}
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
	 *
	 * @return void
	 */
	public function limitToCancelationDeadlineReminderNotSent() {
		$this->whereClauseParts['cancelation_reminder_not_sent']
			= 'tx_seminars_seminars.cancelation_deadline_reminder_sent = 0';
	}

	/**
	 * Limits the bag to future events for which the reminder that an event is
	 * about to take place has not been sent yet.
	 *
	 * @return void
	 */
	public function limitToEventTakesPlaceReminderNotSent() {
		$this->whereClauseParts['event_takes_place_reminder_not_sent']
			= 'tx_seminars_seminars.event_takes_place_reminder_sent = 0';
	}

	/**
	 * Limits the bag to events in status $status.
	 *
	 * @param int $status tx_seminars_seminar::STATUS_PLANNED, ::STATUS_CONFIRMED or ::STATUS_CANCELED
	 *
	 * @return void
	 */
	public function limitToStatus($status) {
		$this->whereClauseParts['event_status']
			= 'tx_seminars_seminars.cancelled = ' . $status;
	}

	/**
	 * Limits the bag to events which are currently $days days before their
	 * begin date.
	 *
	 * @param int $days days before the begin date, must be > 0
	 *
	 * @return void
	 */
	public function limitToDaysBeforeBeginDate($days) {
		$nowPlusDays = ($GLOBALS['SIM_EXEC_TIME']
			+ ($days * tx_oelib_Time::SECONDS_PER_DAY));

		$this->whereClauseParts['days_before_begin_date'] =
			'tx_seminars_seminars.begin_date < ' . $nowPlusDays;
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in categories
	 * based on the search word given in the first parameter $quotedSearchWord.
	 *
	 * @param string $quotedSearchWord the current search word, must not be empty, must be SQL-safe
	 *
	 * @return string[] the WHERE clause parts for the search in categories
	 */
	private function getSearchWherePartForCategories($quotedSearchWord) {
		return $this->getSearchWherePartInMmRelationForTopicOrSingleEventRecord(
			$quotedSearchWord,
			'categories',
			'tx_seminars_categories',
			'tx_seminars_seminars_categories_mm'
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in target groups
	 * based on the search word given in the first parameter $quotedSearchWord.
	 *
	 * @param string $quotedSearchWord the current search word, must not be empty, must be SQL-safe
	 *
	 * @return string[] the WHERE clause parts for the search in categories
	 */
	private function getSearchWherePartForTargetGroups($quotedSearchWord) {
		return $this->getSearchWherePartInMmRelationForTopicOrSingleEventRecord(
			$quotedSearchWord,
			'target_groups',
			'tx_seminars_target_groups',
			'tx_seminars_seminars_target_groups_mm'
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in event
	 * types based on the search word given in the first parameter $quotedSearchWord.
	 *
	 * @param string $quotedSearchWord the current search word, must not be empty, must be SQL-safe
	 *
	 * @return string[] the WHERE clause parts for the search in event types
	 */
	private function getSearchWherePartForEventTypes($quotedSearchWord) {
		return array(
			'EXISTS (' .
				'SELECT * FROM tx_seminars_event_types, tx_seminars_seminars s1, tx_seminars_seminars s2' .
				' WHERE (MATCH (tx_seminars_event_types.title) AGAINST (' . $quotedSearchWord . ' IN BOOLEAN MODE)' .
				' AND tx_seminars_event_types.uid = s1.event_type' .
				' AND ((s1.uid = s2.topic AND s2.object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ') ' .
					'OR (s1.uid = s2.uid AND s1.object_type <> ' . tx_seminars_Model_Event::TYPE_DATE . '))' .
				' AND s2.uid = tx_seminars_seminars.uid)' .
			')'
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in places
	 * based on the search word given in the first parameter $quotedSearchWord.
	 *
	 * @param string $quotedSearchWord the current search word, must not be empty
	 *
	 * @return string[] the WHERE clause parts for the search in places
	 */
	private function getSearchWherePartForPlaces($quotedSearchWord) {
		return $this->getSearchWherePartForMmRelation(
			$quotedSearchWord,
			'places',
			'tx_seminars_sites',
			'tx_seminars_seminars_place_mm'
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in event
	 * topics based on the search word given in the first parameter $quotedSearchWord.
	 *
	 * @param string $quotedSearchWord the current search word, must not be empty
	 *
	 * @return string[] the WHERE clause parts for the search in event topics
	 */
	private function getSearchWherePartForEventTopics($quotedSearchWord) {
		$where = array();
		$where[] = 'MATCH (title, subtitle, description) AGAINST (' . $quotedSearchWord . ' IN BOOLEAN MODE)';

		$matchingUids = tx_oelib_db::selectColumnForMultiple(
			'uid',
			'tx_seminars_seminars',
			'(' . implode(' OR ', $where) . ')' . tx_oelib_db::enableFields('tx_seminars_seminars')
		);
		if (empty($matchingUids)) {
			return array();
		}

		$inUids = ' IN (' . implode(',', $matchingUids) . ')';
		return array(
			'(object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE . ' AND tx_seminars_seminars.uid' . $inUids . ')',
			'(tx_seminars_seminars.object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ' AND ' .
				'tx_seminars_seminars.topic' . $inUids . ')',
		);
	}

	/**
	 * Generates and returns the WHERE clause parts for the search independent
	 * from the event record type based on the search word given in the first
	 * parameter $quotedSearchWord.
	 *
	 * @param string $quotedSearchWord the current search word, must not be empty, must be SQL-safe
	 *
	 * @return string[] the WHERE clause parts for the search independent from the event record type
	 */
	private function getSearchWherePartIndependentFromEventRecordType($quotedSearchWord) {
		return array('MATCH (tx_seminars_seminars.accreditation_number) AGAINST (' . $quotedSearchWord . ' IN BOOLEAN MODE)');
	}

	/**
	 * Generates and returns the WHERE clause parts for the search in speakers
	 * based on the search word given in the first parameter $quotedSearchWord.
	 *
	 * @param string $quotedSearchWord the current search word, must not be empty,
	 *               must be SQL-safe
	 *
	 * @return string[] the WHERE clause parts for the search in speakers
	 */
	private function getSearchWherePartForSpeakers($quotedSearchWord) {
		return $this->getSearchWherePartForMmRelation(
			$quotedSearchWord, 'speakers', 'tx_seminars_speakers', 'tx_seminars_seminars_speakers_mm'
		);
	}

	/**
	 * Generates and returns the WHERE clause part for the search in an m:n
	 * relation between a date or single event record.
	 *
	 * Searches for $searchWord in $field in $foreignTable using the m:n table
	 * $mmTable.
	 *
	 * @param string $quotedSearchWord
	 *        the current search word, must not be empty, must be SQL-safe
	 * @param string $searchFieldKey
	 *        the key of the search field list, must not be empty, must be an valid key of $this->searchFieldList
	 * @param string $foreignTable
	 *        the foreign table to search in, must not be empty
	 * @param string $mmTable
	 *        the m:n relation table, must not be empty
	 *
	 * @return string[] the WHERE clause parts for the search in categories
	 */
	private function getSearchWherePartInMmRelationForTopicOrSingleEventRecord(
		$quotedSearchWord, $searchFieldKey, $foreignTable, $mmTable
	) {
		$this->checkParametersForMmSearchFunctions($quotedSearchWord, $searchFieldKey, $foreignTable, $mmTable);

		$matchQueryPart = 'MATCH (' .
			$foreignTable . '.' . implode(',' . $foreignTable . '.', self::$searchFieldList[$searchFieldKey]) .
			') AGAINST (' . $quotedSearchWord . ' IN BOOLEAN MODE)';
		return array(
			'EXISTS ' .
			'(SELECT * FROM ' . 'tx_seminars_seminars s1, ' . $mmTable . ', ' . $foreignTable .
			' WHERE ((tx_seminars_seminars.object_type = ' .
			tx_seminars_Model_Event::TYPE_DATE . ' AND s1.object_type <> ' . tx_seminars_Model_Event::TYPE_DATE .
			' AND tx_seminars_seminars.topic = s1.uid)' .
			' OR (tx_seminars_seminars.object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE .
			' AND tx_seminars_seminars.uid = s1.uid))' .
			' AND ' . $mmTable . '.uid_local = s1.uid' .
			' AND ' . $mmTable . '.uid_foreign = ' . $foreignTable . '.uid' .
			' AND ' . $matchQueryPart . ')'
		);
	}

	/**
	 * Generates and returns the WHERE clause part for the search in an m:n
	 * relation between a date or single event record.
	 *
	 * Searches for $searchWord in $field in $foreignTable using the m:n table
	 * $mmTable.
	 *
	 * @param string $quotedSearchWord
	 *        the current search word, must not be empty, must be SQL-safe
	 * @param string $searchFieldKey
	 *        the key of the search field list, must not be empty, must be a
	 *        valid key of $this->searchFieldList
	 * @param string $foreignTable
	 *        the name of the foreign table to search in, must not be empty
	 * @param string $mmTable
	 *        the m:n relation table, must not be empty
	 *
	 * @return string[] the WHERE clause parts for the search in categories, will not be empty
	 */
	private function getSearchWherePartForMmRelation($quotedSearchWord, $searchFieldKey, $foreignTable, $mmTable) {
		$this->checkParametersForMmSearchFunctions($quotedSearchWord, $searchFieldKey, $foreignTable, $mmTable);

		$matchQueryPart = 'MATCH (' . implode(',', self::$searchFieldList[$searchFieldKey]) . ') AGAINST (' . $quotedSearchWord
			. ' IN BOOLEAN MODE)';
		$foreignUids = tx_oelib_db::selectColumnForMultiple(
			'uid', $foreignTable, $matchQueryPart . tx_oelib_db::enableFields($foreignTable)
		);
		if (empty($foreignUids)) {
			return array();
		}

		$localUids = tx_oelib_db::selectColumnForMultiple(
			'uid_local',
			$mmTable,
			'uid_foreign IN (' . implode(',', $foreignUids). ')'
		);
		if (empty($localUids)) {
			return array();
		}

		$result = array('tx_seminars_seminars.uid IN (' . implode(',', $localUids) . ')');

		return $result;
	}

	/**
	 * SQL-escapes and trims a potential search word.
	 *
	 * @param string $searchWord single search word (may be prefixed or postfixed with spaces), may be empty
	 *
	 * @return string the trimmed and SQL-escaped $searchword
	 */
	private function prepareSearchWord($searchWord) {
		return $GLOBALS['TYPO3_DB']->quoteStr(trim($searchWord, self::TRIM_CHARACTER_LIST), 'tx_seminars_seminars');
	}

	/**
	 * Checks the parameters for the m:n search functions and throws exceptions
	 * if at least one of the parameters is empty.
	 *
	 * @param string $searchWord
	 *        the current search word, must not be empty, must already be SQL-safe
	 * @param string $searchFieldKey
	 *        the key of the search field list, must not be empty, must be an valid key of self::$searchFieldList
	 * @param string $foreignTable
	 *        the foreign table to search in, must not be empty
	 * @param string $mmTable
	 *        the m:n relation table, must not be empty
	 *
	 * @return void
	 */
	private function checkParametersForMmSearchFunctions($searchWord, $searchFieldKey, $foreignTable, $mmTable) {
		if (trim($searchWord, self::TRIM_CHARACTER_LIST . '\'%') === '') {
			throw new InvalidArgumentException('The first parameter $searchWord must no be empty.', 1333292804);
		}
		if ($searchFieldKey === '') {
			throw new InvalidArgumentException('The second parameter $searchFieldKey must not be empty.', 1333292809);
		}
		if (!array_key_exists($searchFieldKey, self::$searchFieldList)) {
			throw new InvalidArgumentException(
				'The second parameter $searchFieldKey must be a valid key of self::$searchFieldList.', 1333292815
			);
		}
		if ($foreignTable === '') {
			throw new InvalidArgumentException('The third parameter $foreignTable must not be empty.', 1333292820);
		}
		if ($mmTable === '') {
			throw new InvalidArgumentException('The fourth parameter $mmTable must not be empty.', 1333292829);
		}
	}

	/**
	 * Limits the search results to topics which are required for the
	 * given topic.
	 *
	 * @param int $eventUid the UID of the topic event for which the requirements should be found, must be > 0
	 *
	 * @return void
	 */
	public function limitToRequiredEventTopics($eventUid) {
		$this->whereClauseParts['requiredEventTopics'] =
			'tx_seminars_seminars_requirements_mm.uid_local = ' . $eventUid .
			' AND tx_seminars_seminars_requirements_mm.uid_foreign = ' .
			'tx_seminars_seminars.uid';
		$this->addAdditionalTableName('tx_seminars_seminars_requirements_mm');
		$this->setOrderBy('tx_seminars_seminars_requirements_mm.sorting');
	}

	/**
	 * Limits the search result to topics which depend on the given topic.
	 *
	 * @param int $eventUid the UID of the topic event which the searched events depend on, must be > 0
	 *
	 * @return void
	 */
	public function limitToDependingEventTopics($eventUid) {
		$this->whereClauseParts['dependingEventTopics'] =
			'tx_seminars_seminars_requirements_mm.uid_foreign = ' . $eventUid .
			' AND tx_seminars_seminars_requirements_mm.uid_local = ' .
			'tx_seminars_seminars.uid';
		$this->addAdditionalTableName('tx_seminars_seminars_requirements_mm');
		$this->setOrderBy(
			'tx_seminars_seminars_requirements_mm.sorting_foreign ASC'
		);
	}

	/**
	 * Limits the search result to topics for which there is no registration by
	 * the front-end user with the UID $uid.
	 *
	 * Registrations for dates that have a non-zero expiry date in the past will
	 * be counted as not existing.
	 *
	 * @param int $uid the UID of the front-end user whose registered events should be removed from the bag, must be > 0
	 *
	 * @return void
	 */
	public function limitToTopicsWithoutRegistrationByUser($uid) {
		$this->limitToTopicRecords();
		$this->whereClauseParts['topicsWithoutUserRegistration'] =
			'NOT EXISTS (' .
				'SELECT * FROM tx_seminars_attendances, ' .
					'tx_seminars_seminars dates ' .
				'WHERE tx_seminars_attendances.user = ' . $uid .
				' AND tx_seminars_attendances.seminar = dates.uid' .
				' AND dates.topic = tx_seminars_seminars.uid' .
				' AND (dates.expiry = 0 OR dates.expiry > ' .
					$GLOBALS['SIM_EXEC_TIME'] . ')' .
			')';
	}

	/**
	 * Limits the bag to events which start later than $earliestBeginDate or which are still running at $earliestBeginDate.
	 *
	 * A $earliestBeginDate of 0 will remove the filter.
	 *
	 * @param int $earliestBeginDate the earliest begin date as UNIX time-stamp, 0 will remove the limit
	 *
	 * @return void
	 */
	public function limitToEarliestBeginOrEndDate($earliestBeginDate) {
		if ($earliestBeginDate == 0) {
			unset($this->whereClauseParts['earliestBeginDate']);

			return;
		}

		$this->whereClauseParts['earliestBeginDate'] = '('
			. 'tx_seminars_seminars.begin_date = 0 OR '
			. '(tx_seminars_seminars.begin_date >= '. $earliestBeginDate
			. ' OR (tx_seminars_seminars.begin_date <= ' . $earliestBeginDate
			. ' AND tx_seminars_seminars.end_date > ' . $earliestBeginDate . '))'
			. ')';
	}

	/**
	 * Limits the bag to events which have a begin_date lower than the given
	 * time-stamp, but greater than zero.
	 *
	 * A $latestBeginDate of 0 will remove the filter.
	 *
	 * @param int $latestBeginDate the latest begin date as UNIX time-stamp, 0 will remove the limit
	 *
	 * @return void
	 */
	public function limitToLatestBeginOrEndDate($latestBeginDate) {
		if ($latestBeginDate === 0) {
			unset($this->whereClauseParts['latestBeginDate']);
			return;
		}

		$this->whereClauseParts['latestBeginDate'] =
			'(tx_seminars_seminars.begin_date <> 0 AND ' .
			'tx_seminars_seminars.begin_date <= ' . $latestBeginDate . ' OR ' .
			'tx_seminars_seminars.end_date <> 0 AND ' .
			'tx_seminars_seminars.end_date <= ' . $latestBeginDate . ')';
	}

	/**
	 * Limits the bag to events which are not fully-booked yet (or have a queue).
	 *
	 * @return void
	 */
	public function limitToEventsWithVacancies() {
		$seats = '(SELECT COALESCE(SUM(seats),0) FROM tx_seminars_attendances ' .
			'WHERE seminar = tx_seminars_seminars.uid' .
			tx_oelib_db::enableFields('tx_seminars_attendances') . ')';
		$hasVacancies = '(attendees_max > (' . $seats . ' + offline_attendees))';

		$this->whereClauseParts['eventsWithVacancies'] =
			'(needs_registration = 0 OR (needs_registration = 1 AND ' .
				'(attendees_max = 0 OR ' .
					'(attendees_max > 0 AND ' . $hasVacancies . ')' .
				'))'.
			')';
	}

	/**
	 * Limits the bag to events with the given organizers.
	 *
	 * @param string $organizerUids
	 *               comma-separated list of organizer UIDs to limit the bag to,
	 *               may be empty
	 *
	 * @return void
	 */
	public function limitToOrganizers($organizerUids) {
		if ($organizerUids == '') {
			return;
		}
		$eventUids = implode(',', tx_oelib_db::selectColumnForMultiple(
			'uid_local',
			'tx_seminars_seminars_organizers_mm',
			'uid_foreign IN (' . $organizerUids .')'
		));

		if ($eventUids == '') {
			$this->whereClauseParts['eventsWithOrganizers'] = '(0 = 1)';

			return;
		}

		$this->whereClauseParts['eventsWithOrganizers'] =
			'((object_type = '. tx_seminars_Model_Event::TYPE_COMPLETE . ') ' .
				'AND (tx_seminars_seminars.uid IN (' . $eventUids .
				')) OR (' .
				'(object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ') AND (' .
				'tx_seminars_seminars.topic IN (' . $eventUids . ')))' .
			')';
	}

	/**
	 * Limits the bag to events which have target groups with age limits within
	 * the provided age.
	 *
	 * @param int $age the age to limit the bag to, must be >= 0
	 *
	 * @return void
	 */
	public function limitToAge($age) {
		if ($age == 0) {
			return;
		}

		$matchingTargetGroups = implode(',',
			tx_oelib_db::selectColumnForMultiple(
				'uid',
				'tx_seminars_target_groups',
				'(minimum_age <= ' . $age . ' AND (maximum_age = 0 OR maximum_age >= ' .
					$age . '))'
			)
		);

		$eventsWithoutTargetGroup = tx_oelib_db::selectColumnForMultiple(
			'uid',
			'tx_seminars_seminars',
			'(object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE . ' OR ' .
				'object_type = ' . tx_seminars_Model_Event::TYPE_TOPIC . ') AND ' .
				'(target_groups = 0)' .
				tx_oelib_db::enableFields('tx_seminars_seminars')
		);
		if ($matchingTargetGroups != '') {
			$eventsWithMatchingTargetGroup
				= tx_oelib_db::selectColumnForMultiple(
					'uid_local',
					'tx_seminars_seminars_target_groups_mm',
					'uid_foreign IN (' . $matchingTargetGroups . ')',
					'uid_local'
				);

			$matchingEventsUids = array_merge(
				$eventsWithMatchingTargetGroup,
				$eventsWithoutTargetGroup
			);
		} else {
			$matchingEventsUids = $eventsWithoutTargetGroup;
		}

		if (empty($matchingEventsUids)) {
			$this->whereClauseParts['ageLimit'] = '(0 = 1)';
		} else {
			$matchingEventsUidList = implode(',', $matchingEventsUids);
			$this->whereClauseParts['ageLimit'] =
				'((object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE . ' AND ' .
					'tx_seminars_seminars.uid IN (' . $matchingEventsUidList . ')) ' .
					'OR ' .
					'(object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ' AND ' .
					'topic IN (' . $matchingEventsUidList . '))' .
				')';
		}
	}

	/**
	 * Limits the bag to events which have a price lower or equal to the given
	 * maximum price.
	 *
	 * @param int $maximumPrice
	 *                the maximum price an event is allowed to cost, must
	 *                be >= 0
	 *
	 * @return void
	 */
	public function limitToMaximumPrice($maximumPrice) {
		if ($maximumPrice == 0) {
			return;
		}

		$notZeroAndInRange = '(%1$s > 0 AND %1$s <= %2$u)';
		$now = $GLOBALS['SIM_EXEC_TIME'];

		$whereClause = '(object_type = ' . tx_seminars_Model_Event::TYPE_TOPIC . ' OR ' .
			'object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE . ') AND ('.
			'(deadline_early_bird < ' . $now . ' AND ' .
				'(price_regular <= ' . $maximumPrice . ' OR ' .
					sprintf(
						$notZeroAndInRange, 'price_special', $maximumPrice
					) .')) '.
			'OR (deadline_early_bird > ' . $now . ' AND ((' .
					'(price_regular_early = 0 AND price_regular <= ' .
						$maximumPrice .	') ' .
					'OR (price_special_early = 0 AND price_special > 0 ' .
						'AND price_special <= ' . $maximumPrice .
					')' .
				') OR (' .
					sprintf(
						$notZeroAndInRange, 'price_regular_early', $maximumPrice
					) . ' OR ' .
					sprintf(
						$notZeroAndInRange, 'price_special_early', $maximumPrice
					) .
				'))) ' .
			'OR ' .
				sprintf(
					$notZeroAndInRange, 'price_regular_board', $maximumPrice
				) . ' OR ' .
				sprintf(
					$notZeroAndInRange, 'price_special_board', $maximumPrice
				) .
			')' . tx_oelib_db::enableFields('tx_seminars_seminars');

		$foundUids = implode(
			',',
			tx_oelib_db::selectColumnForMultiple(
				'uid', 'tx_seminars_seminars', $whereClause
			)
		);

		if ($foundUids == '') {
			$this->whereClauseParts['maximumPrice'] = '(0 = 1)';
		} else {
			$this->whereClauseParts['maximumPrice'] =
				'((object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE . ' ' .
				'AND tx_seminars_seminars.uid IN (' . $foundUids .
				')) OR ' .
				'(object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ' AND ' .
				'topic IN (' . $foundUids . ')))';
		}
	}

	/**
	 * Limits the bag to events which have a price higher or equal to the given
	 * minimum price.
	 *
	 * @param int $minimumPrice
	 *                the minimum price an event is allowed to cost, must
	 *                be >= 0
	 *
	 * @return void
	 */
	public function limitToMinimumPrice($minimumPrice) {
		if ($minimumPrice == 0) {
			return;
		}

		$now = $GLOBALS['SIM_EXEC_TIME'];
		$whereClause = '(object_type = ' . tx_seminars_Model_Event::TYPE_TOPIC . ' OR ' .
			'object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE . ') AND (' .
			'(deadline_early_bird < ' . $now . ' ' .
				'AND (price_regular >= ' . $minimumPrice . ' ' .
				'OR price_special >= ' . $minimumPrice . ')' .
			') OR (deadline_early_bird > ' . $now . ' ' .
				'AND ((' .
						'(price_regular_early = 0 ' .
							'AND price_regular >= ' . $minimumPrice . ') ' .
						'OR (price_special_early = 0 ' .
							'AND price_special >= ' . $minimumPrice . ')) ' .
					'OR (price_regular_early >= ' . $minimumPrice . ' ' .
						'OR price_special_early >= ' . $minimumPrice . ') ' .
				')) ' .
			'OR price_regular_board >= ' . $minimumPrice . ' ' .
			'OR price_special_board >= ' . $minimumPrice . ') ' .
			tx_oelib_db::enableFields('tx_seminars_seminars');

		$foundUids = implode(
			',',
			tx_oelib_db::selectColumnForMultiple(
				'uid', 'tx_seminars_seminars', $whereClause
			)
		);

		if ($foundUids == '') {
			$this->whereClauseParts['maximumPrice'] = '(0 = 1)';
		} else {
			$this->whereClauseParts['maximumPrice'] =
				'((object_type = ' . tx_seminars_Model_Event::TYPE_COMPLETE . ' ' .
				'AND tx_seminars_seminars.uid IN (' . $foundUids .
				')) OR ' .
				'(object_type = ' . tx_seminars_Model_Event::TYPE_DATE . ' AND ' .
				'topic IN (' . $foundUids . ')))';
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Event.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Event.php']);
}