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
 * Testcase for the seminar bag builder class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbagbuilder.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_seminarbagbuilder_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_seminarbagbuilder();
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBuilderBuildsABagChildObject() {
		$this->assertTrue(
			is_subclass_of($this->fixture->build(), 'tx_seminars_bag')
		);
	}

	public function testBuilderIgnoresHiddenEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('hidden' => 1)
		);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsHiddenEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('hidden' => 1)
		);

		$this->fixture->setBackEndMode();

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresTimedEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => mktime() - 1000)
		);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsTimedEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => mktime() - 1000)
		);

		$this->fixture->setBackEndMode();

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain categories.
	////////////////////////////////////////////////////////////////

	public function testSkippingLimitToCategoryResultsInAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToZeroCategoryUidResultsInAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory(0);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToNegativeCategoryUidResultsInAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory(-2);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryCanResultInOneEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryCanResultInTwoEvents() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid2, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryWillExcludeUnassignedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->getObjectCountWithoutLimit()
		);
		$this->assertEquals(
			$eventUid,
			$bag->getCurrent()->getUid()
		);
	}

	public function testLimitToCategoryWillExcludeEventsOfOtherCategories() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid2, $categoryUid2
		);

		$this->fixture->limitToCategory($categoryUid1);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->getObjectCountWithoutLimit()
		);
		$this->assertEquals(
			$eventUid1,
			$bag->getCurrent()->getUid()
		);
	}

	public function testLimitToCategoryResultsInAnEmptyBagIfThereAreNoMatches() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);

		$this->fixture->limitToCategory($categoryUid2);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryIgnoresTopicRecords() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryFindsDateRecordForTopic() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $topicUid, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);

		$bag = $this->fixture->build();
		$this->assertEquals(
			1,
			$bag->getObjectCountWithoutLimit()
		);
		$this->assertEquals(
			$dateUid,
			$bag->getCurrent()->getUid()
		);
	}

	public function testLimitToCategoryFindsDateRecordForSingle() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $topicUid, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryIgnoresTopicOfDateRecord() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $topicUid, $categoryUid1
		);

		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid
			)
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $dateUid, $categoryUid2
		);

		$this->fixture->limitToCategory($categoryUid2);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	//////////////////////////////////////
	// Tests concerning canceled events.
	//////////////////////////////////////

	public function testBuilderFindsCanceledEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents();

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsDisabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->allowCanceledEvents();

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsEnabledThenDisabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents();
		$this->fixture->allowCanceledEvents();

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledDisabledThenEnabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->allowCanceledEvents();
		$this->fixture->ignoreCanceledEvents();

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * validity checks
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFrameFailsWithEmptyKey() {
		$this->setExpectedException(
			'Exception',
			'The time-frame key  is not valid.'
		);
		$this->fixture->setTimeFrame('');
	}

	public function testSetTimeFrameFailsWithInvalidKey() {
		$this->setExpectedException(
			'Exception',
			'The time-frame key foo is not valid.'
		);
		$this->fixture->setTimeFrame('foo');
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * past events
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFramePastFindsPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => time() - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('past');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastFindsOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('past');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastIgnoresCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_DAY,
				'end_date' => time() + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('past');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastIgnoresUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('past');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastIgnoresUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('past');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastIgnoresEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('past');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * past and current events
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFramePastAndCurrentFindsPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => time() - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastAndCurrentFindsOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastAndCurrentFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_DAY,
				'end_date' => time() + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastAndCurrentIgnoresUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastAndCurrentIgnoresUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFramePastAndCurrentIgnoresEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * current events
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFrameCurrentIgnoresPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => time() - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('current');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('current');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_DAY,
				'end_date' => time() + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('current');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentIgnoresUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('current');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentIgnoresUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('current');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentIgnoresEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('current');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * current and upcoming events
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFrameCurrentAndUpcomingIgnoresPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => time() - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentAndUpcomingIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentAndUpcomingFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_DAY,
				'end_date' => time() + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentAndUpcomingFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentAndUpcomingFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameCurrentAndUpcomingFindsEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * upcoming events
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFrameUpcomingIgnoresPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => time() - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('upcoming');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameUpcomingIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcoming');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameUpcomingIgnoresCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_DAY,
				'end_date' => time() + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('upcoming');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameUpcomingFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('upcoming');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameUpcomingFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcoming');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameUpcomingFindsEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcoming');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * events for which the registration deadline is not over yet
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFrameDeadlineNotOverIgnoresPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => time() - ONE_DAY,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameDeadlineNotOverIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => 0,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameDeadlineNotOverIgnoresCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_DAY,
				'end_date' => time() + ONE_DAY,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameDeadlineNotOverFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameDeadlineNotOverFindsUpcomingEventsWithUpcomingDeadline() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + 2 * ONE_DAY,
				'end_date' => time() + ONE_WEEK,
				'deadline_registration' => time() + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameDeadlineNotOverIgnoresUpcomingEventsWithPassedDeadline() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK,
				'deadline_registration' => time() - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameDeadlineNotOverFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => 0,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameDeadlineNotOverFindsEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * all events
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFrameAllFindsPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => time() - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('all');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameAllFindsOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('all');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameAllFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() - ONE_DAY,
				'end_date' => time() + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('all');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameAllFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => time() + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('all');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameAllFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => time() + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('all');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testSetTimeFrameAllFindsEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('all');

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}
}
?>