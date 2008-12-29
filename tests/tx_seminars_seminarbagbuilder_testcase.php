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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Testcase for the seminar bag builder class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
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

		unset($this->fixture, $this->testingFramework);
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testBuilderFindsHiddenEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('hidden' => 1)
		);

		$this->fixture->setBackEndMode();

		$this->assertFalse(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testBuilderIgnoresTimedEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => mktime() - 1000)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testBuilderFindsTimedEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => mktime() - 1000)
		);

		$this->fixture->setBackEndMode();

		$this->assertFalse(
			$this->fixture->build()->isEmpty()
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain categories.
	////////////////////////////////////////////////////////////////

	public function testSkippingLimitToCategoriesResultsInAllEvents() {
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
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEmptyCategoryUidResultsInAllEvents() {
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

		$this->fixture->limitToCategories('');

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEmptyCategoryAfterLimitToNotEmptyCategoriesUidResultsInAllEvents() {
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

		$this->fixture->limitToCategories($categoryUid);
		$this->fixture->limitToCategories('');

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCategoriesCanResultInOneEvent() {
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

		$this->fixture->limitToCategories($categoryUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCategoriesCanResultInTwoEvents() {
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

		$this->fixture->limitToCategories($categoryUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCategoriesWillExcludeUnassignedEvents() {
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

		$this->fixture->limitToCategories($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToCategoriesWillExcludeEventsOfOtherCategories() {
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

		$this->fixture->limitToCategories($categoryUid1);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertEquals(
			$eventUid1,
			$bag->current()->getUid()
		);
	}

	public function testLimitToCategoriesResultsInAnEmptyBagIfThereAreNoMatches() {
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

		$this->fixture->limitToCategories($categoryUid2);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCategoriesIgnoresTopicRecords() {
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

		$this->fixture->limitToCategories($categoryUid);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCategoriesFindsDateRecordForTopic() {
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

		$this->fixture->limitToCategories($categoryUid);

		$bag = $this->fixture->build();
		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToCategoriesFindsDateRecordForSingle() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRecord(
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

		$this->fixture->limitToCategories($categoryUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCategoriesIgnoresTopicOfDateRecord() {
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

		$this->fixture->limitToCategories($categoryUid2);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCategoriesCanFindEventsFromMultipleCategories() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid2, $categoryUid2
		);

		$this->fixture->limitToCategories($categoryUid1 . ','  . $categoryUid2);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}


	///////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain places
	///////////////////////////////////////////////////////////

	public function testLimitToPlacesFindsEventsInOnePlace() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToPlaces(array($siteUid));

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToPlacesIgnoresEventsWithoutPlace() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToPlaces(array($siteUid));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToPlacesFindsEventsInTwoPlaces() {
		$siteUid1 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$siteUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid2,
			$siteUid2
		);
		$this->fixture->limitToPlaces(array($siteUid1, $siteUid2));

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToPlacesWithEmptyPlacesArrayFindsAllEvents() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToPlaces(array($siteUid));
		$this->fixture->limitToPlaces();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToPlacesIgnoresEventsWithDifferentPlace() {
		$siteUid1 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$siteUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM, $eventUid, $siteUid1
		);
		$this->fixture->limitToPlaces(array($siteUid2));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToPlacesWithOnePlaceFindsEventInTwoPlaces() {
		$siteUid1 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$siteUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM, $eventUid, $siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM, $eventUid, $siteUid2
		);
		$this->fixture->limitToPlaces(array($siteUid1));

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledDisabledThenEnabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->allowCanceledEvents();
		$this->fixture->ignoreCanceledEvents();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
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
			$this->fixture->build()->count()
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events of certain event types
	////////////////////////////////////////////////////////////////

	public function testSkippingLimitToEventTypesResultsInAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEmptyTypeUidResultsInAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);

		$this->fixture->limitToEventTypes();

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEmptyTypeUidAfterLimitToNotEmptyTypesResultsInAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid));
		$this->fixture->limitToEventTypes();

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventTypesCanResultInOneEvent() {
		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid));

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventTypesCanResultInTwoEvents() {
		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid));

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventTypesWillExcludeUnassignedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid));
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToEventTypesWillExcludeEventsOfOtherTypes() {
		$typeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid2,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid1));
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertEquals(
			$eventUid1,
			$bag->current()->getUid()
		);
	}

	public function testLimitToEventTypesResultsInAnEmptyBagIfThereAreNoMatches() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);

		$typeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);

		$this->fixture->limitToEventTypes(array($typeUid2));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToEventTypesIgnoresTopicRecords() {
		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'event_type' => $typeUid,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToEventTypesFindsDateRecordForTopic() {
		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'event_type' => $typeUid,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid));

		$bag = $this->fixture->build();
		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToEventTypesFindsDateRecordForSingle() {
		$typeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid));

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventTypesIgnoresTopicOfDateRecord() {
		$typeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'event_type' => $typeUid2,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid2));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToEventTypesCanFindEventsFromMultipleTypes() {
		$typeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $typeUid2,
			)
		);

		$this->fixture->limitToEventTypes(array($typeUid1, $typeUid2));

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}


	//////////////////////////////
	// Tests for limitToCities()
	//////////////////////////////

	public function testLimitToCitiesFindsEventsInOneCity() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 1')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 1'));

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToCitiesIgnoresEventsInOtherCity() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 2')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 1'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCitiesWithTwoCitiesFindsEventsEachInOneOfBothCities() {
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 2')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid2,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 1', 'test city 2'));

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCitiesWithEmptyCitiesArrayFindsEventsWithCities() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 1')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 2'));
		$this->fixture->limitToCities();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCitiesIgnoresEventsWithDifferentCity() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 1')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 2'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCitiesIgnoresEventWithPlaceWithoutCity() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 1'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCitiesWithTwoCitiesFindsOneEventInBothCities() {
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 2')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 1', 'test city 2'));

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCitiesWithOneCityFindsEventInTwoCities() {
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 2')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 2)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 1'));

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCitiesWithTwoCitiesOneDifferentFindsEventInOneOfTheCities() {
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'test city 3')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 2)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 2', 'test city 3'));

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}


	/////////////////////////////////
	// Tests for limitToCountries()
	/////////////////////////////////

	public function testLimitToCountriesFindsEventsInOneCountry() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('DE'));

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToCountriesIgnoresEventsInOtherCountry() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('US'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCountriesFindsEventsInTwoCountries() {
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'US')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'DE')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid2,
			$siteUid2
		);
		$this->fixture->limitToCountries(array('US', 'DE'));

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCountriesWithEmptyCountriesArrayFindsAllEvents() {
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'US')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1,
			$siteUid1
		);
		$this->fixture->limitToCountries(array('DE'));
		$this->fixture->limitToCountries();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToCountriesIgnoresEventsWithDifferentCountry() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('US'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCountriesIgnoresEventsWithPlaceWithoutCountry() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('DE'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToCountriesWithOneCountryFindsEventInTwoCountries() {
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'US')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$siteUid2
		);
		$this->fixture->limitToCountries(array('US'));

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}


	/////////////////////////////////
	// Tests for limitToLanguages()
	/////////////////////////////////

	public function testLimitToLanguagesFindsEventsInOneLanguage() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'DE')
		);
		$this->fixture->limitToLanguages(array('DE'));

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToLanguagesFindsEventsInTwoLanguages() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'EN')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'DE')
		);
		$this->fixture->limitToLanguages(array('EN', 'DE'));

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToLanguagesWithEmptyLanguagesArrayFindsAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'EN')
		);
		$this->fixture->limitToLanguages(array('DE'));
		$this->fixture->limitToLanguages();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToLanguagesIgnoresEventsWithDifferentLanguage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'DE')
		);
		$this->fixture->limitToLanguages(array('EN'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToLanguagesIgnoresEventsWithoutLanguage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToLanguages(array('EN'));

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	////////////////////////////////////
	// Tests for limitToTopicRecords()
	////////////////////////////////////

	public function testLimitToTopicRecordsFindsTopicEventRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->fixture->limitToTopicRecords();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToTopicRecordsIgnoresSingleEventRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->fixture->limitToTopicRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToTopicRecordsIgnoresEventDateRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_DATE)
		);
		$this->fixture->limitToTopicRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	//////////////////////////////////////////
	// Tests for removeLimitToTopicRecords()
	//////////////////////////////////////////

	public function testRemoveLimitToTopicRecordsFindsSingleEventRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->fixture->limitToTopicRecords();
		$this->fixture->removeLimitToTopicRecords();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testRemoveLimitToTopicRecordsFindsEventDateRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_DATE)
		);
		$this->fixture->limitToTopicRecords();
		$this->fixture->removeLimitToTopicRecords();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}


	/////////////////////////////
	// Tests for limitToOwner()
	/////////////////////////////

	public function testLimitToOwnerWithNegativeFeUserUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $feUserUid must be >= 0.'
		);

		$this->fixture->limitToOwner(-1);
	}

	public function testLimitToOwnerWithPositiveFeUserUidFindsEventsWithOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('owner_feuser' => $feUserUid)
		);
		$this->fixture->limitToOwner($feUserUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToOwnerWithPositiveFeUserUidIgnoresEventsWithoutOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToOwner($feUserUid);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToOwnerWithPositiveFeUserUidIgnoresEventsWithDifferentOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('owner_feuser' => ($feUserUid + 1))
		);
		$this->fixture->limitToOwner($feUserUid);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToOwnerWithZeroFeUserUidFindsEventsWithoutOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToOwner($feUserUid);
		$this->fixture->limitToOwner(0);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToOwnerWithZeroFeUserUidFindsEventsWithOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('owner_feuser' => $feUserUid)
		);
		$this->fixture->limitToOwner($feUserUid);
		$this->fixture->limitToOwner(0);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}


	////////////////////////////////////////////
	// Tests for limitToDateAndSingleRecords()
	////////////////////////////////////////////

	public function testLimitToDateAndSingleRecordsFindsDateRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_DATE)
		);
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToDateAndSingleRecordsFindsSingleRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToDateAndSingleRecordsIgnoresTopicRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testRemoveLimitToDateAndSingleRecordsFindsTopicRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->fixture->limitToDateAndSingleRecords();
		$this->fixture->removeLimitToDateAndSingleRecords();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}


	////////////////////////////////////
	// Tests for limitToEventManager()
	////////////////////////////////////

	public function testLimitToEventManagerWithNegativeFeUserUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $feUserUid must be >= 0.'
		);

		$this->fixture->limitToEventManager(-1);
	}

	public function testLimitToEventManagerWithPositiveFeUserUidFindsEventsWithEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('vips' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_VIPS_MM,
			$eventUid,
			$feUserUid
		);

		$this->fixture->limitToEventManager($feUserUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventManagerWithPositiveFeUserUidIgnoresEventsWithoutEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->fixture->limitToEventManager($feUserUid);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToEventManagerWithZeroFeUserUidFindsEventsWithoutEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->fixture->limitToEventManager($feUserUid);
		$this->fixture->limitToEventManager(0);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}


	/////////////////////////////////////
	// Tests for limitToEventsNextDay()
	/////////////////////////////////////

	public function testLimitToEventsNextDayFindsEventsNextDay() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('begin_date' => ONE_DAY, 'end_date' => (ONE_DAY + 60 * 60))
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => (2 * ONE_DAY),
				'end_date' => (60 * 60 + 2 * ONE_DAY),
				)
		);
		$this->fixture->limitToEventsNextDay(
			new tx_seminars_seminar($eventUid1)
		);

		$this->assertEquals(
			$eventUid2,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToEventsNextDayIgnoresEarlierEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('begin_date' => ONE_DAY, 'end_date' => (ONE_DAY + 60 * 60))
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => (60 * 60),
				)
		);
		$this->fixture->limitToEventsNextDay(
			new tx_seminars_seminar($eventUid)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToEventsNextDayIgnoresEventsLaterThanOneDay() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('begin_date' => ONE_DAY, 'end_date' => (ONE_DAY + 60 * 60))
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => (3 * ONE_DAY),
				'end_date' => (60 * 60 + 3 * ONE_DAY),
				)
		);
		$this->fixture->limitToEventsNextDay(
			new tx_seminars_seminar($eventUid)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToEventsNextDayWithEventWithEmptyEndDateThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The event object given in the first parameter $event must ' .
				'have an end date set.'
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToEventsNextDay(
			new tx_seminars_seminar($eventUid)
		);
	}


	//////////////////////////////////////////////
	// Tests for limitToOtherDatesForThisTopic()
	//////////////////////////////////////////////

	public function testLimitToOtherDatesForTopicFindsOtherDateForTopic() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$dateUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$date = new tx_seminars_seminar($dateUid1);
		$this->fixture->limitToOtherDatesForTopic($date);

		$this->assertEquals(
			$dateUid2,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToOtherDatesForTopicWithTopicRecordFindsAllDatesForTopic() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$topic = new tx_seminars_seminar($topicUid);
		$this->fixture->limitToOtherDatesForTopic($topic);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToOtherDatesForTopicWithSingleEventRecordThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The first parameter $event must be either a date or a topic record.'
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$event = new tx_seminars_seminar($eventUid);
		$this->fixture->limitToOtherDatesForTopic($event);
	}

	public function testLimitToOtherDatesForTopicIgnoresDateForOtherTopic() {
		$topicUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$topicUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid1,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid2,
			)
		);
		$date = new tx_seminars_seminar($dateUid1);
		$this->fixture->limitToOtherDatesForTopic($date);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToOtherDatesForTopicIgnoresSingleEventRecordWithTopic() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'topic' => $topicUid,
			)
		);
		$date = new tx_seminars_seminar($dateUid);
		$this->fixture->limitToOtherDatesForTopic($date);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testRemoveLimitToOtherDatesForTopicRemovesLimitAndFindsAllDateAndTopicRecords() {
		$topicUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$topicUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid1,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid2,
			)
		);
		$date = new tx_seminars_seminar($dateUid1);
		$this->fixture->limitToOtherDatesForTopic($date);
		$this->fixture->removeLimitToOtherDatesForTopic();

		$this->assertEquals(
			4,
			$this->fixture->build()->count()
		);
	}

	public function testRemoveLimitToOtherDatesForTopicFindsSingleEventRecords() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'topic' => $topicUid,
			)
		);
		$date = new tx_seminars_seminar($dateUid);
		$this->fixture->limitToOtherDatesForTopic($date);
		$this->fixture->removeLimitToOtherDatesForTopic();

		$this->assertEquals(
			3,
			$this->fixture->build()->count()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limitToFullTextSearch() for single event records
	/////////////////////////////////////////////////////////////////

	public function testLimitToFullTextSearchWithTwoCommasAsSearchWordFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch(',,');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedByTwoSpacesFindsEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo  bar');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTwoCommasSeparatedByTwoSpacesFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch(',  ,');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTooShortSearchWordFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('o');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInAccreditationNumber() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'accreditation_number' => 'foo bar event',
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInAccreditationNumber() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'accreditation_number' => 'bar event',
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTitle() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInSubtitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('subtitle' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInSubtitle() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('subtitle' => 'bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTeaser() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('teaser' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTeaser() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('teaser' => 'bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInDescription() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('description' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInDescription() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('description' => 'bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInSpeakerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInSpeakerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInSpeakerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInSpeakerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPartnerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPartnerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPartnerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPartnerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPartnerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPartnerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTutorTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTutorTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTutorOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTutorOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTutorDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTutorDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInLeaderTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInLeaderTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInLeaderOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInLeaderOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInLeaderDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInLeaderDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'foo bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPlaceAddress() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'foo bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPlaceAddress() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'foo bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo bar event type')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'event_type' => $eventTypeUid,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'bar event type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'event_type' => $eventTypeUid,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'foo bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => $organizerUid,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'bar organizer')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => $organizerUid,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTargetGroupTitle() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('title' => 'foo bar target group')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'target_groups' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TARGET_GROUPS_MM,
			$eventUid,
			$targetGroupUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTargetGroupTitle() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('title' => 'bar target group')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'target_groups' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TARGET_GROUPS_MM,
			$eventUid,
			$targetGroupUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'foo bar category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'categories' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM,
			$eventUid,
			$categoryUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'bar category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'categories' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM,
			$eventUid,
			$categoryUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedBySpaceFindsTwoEventsWithSearchWordsInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'foo event bar')
		);
		$this->fixture->limitToFullTextSearch('foo bar');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedByCommaFindsTwoEventsWithSearchWordsInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'foo event bar')
		);
		$this->fixture->limitToFullTextSearch('foo,bar');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests for limitToFullTextSearch() for topic event records
	////////////////////////////////////////////////////////////////

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicTitle() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'foo bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			$dateUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicTitle() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicSubtitle() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'subtitle' => 'foo bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			$dateUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicSubtitle() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'subtitle' => 'bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicTeaser() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'teaser' => 'foo bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			$dateUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicTeaser() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'teaser' => 'bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicDescription() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'description' => 'foo bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			$dateUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicDescription() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'description' => 'bar event',
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'foo bar category')
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'categories' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM,
			$topicUid,
			$categoryUid
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			$dateUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'bar category')
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'categories' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM,
			$topicUid,
			$categoryUid
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicTargetGroupTitle() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('title' => 'foo bar target group')
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'target_groups' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TARGET_GROUPS_MM,
			$topicUid,
			$targetGroupUid
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			$dateUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicTargetGroupTitle() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('title' => 'bar target group')
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'target_groups' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TARGET_GROUPS_MM,
			$topicUid,
			$targetGroupUid
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo bar event type')
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'event_type' => $eventTypeUid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertEquals(
			$dateUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'bar event type')
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'event_type' => $eventTypeUid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');
		$this->fixture->limitToDateAndSingleRecords();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for limitToFullTextSearch() for event date records
	///////////////////////////////////////////////////////////////

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInAccreditationNumber() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'accreditation_number' => 'foo bar event',
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInAccreditationNumber() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'accreditation_number' => 'bar event',
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'foo bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => $organizerUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'bar organizer')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => $organizerUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInSpeakerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInSpeakerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInSpeakerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInSpeakerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'speakers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPartnerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPartnerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPartnerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPartnerOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPartnerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPartnerDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'partners' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInTutorTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInTutorTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInTutorOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInTutorOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInTutorDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInTutorDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'tutors' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInLeaderTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInLeaderTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInLeaderOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInLeaderOrganization() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInLeaderDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInLeaderDescription() {
		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'leaders' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'foo bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPlaceAddress() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'foo bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPlaceAddress() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'foo bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertEquals(
			$eventUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'place' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	///////////////////////////////////////////
	// Tests concerning limitToRequiredEvents
	///////////////////////////////////////////

	public function testLimitToRequiredEventsCanFindOneRequiredEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('requirements' => 1)
		);
		$requiredEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$eventUid,
			$requiredEventUid
		);
		$this->fixture->limitToRequiredEventTopics($eventUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToRequiredEventsCanFindTwoRequiredEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('requirements' => 1)
		);
		$requiredEventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$requiredEventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$eventUid,
			$requiredEventUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$eventUid,
			$requiredEventUid2
		);

		$this->fixture->limitToRequiredEventTopics($eventUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToRequiredEventsFindsOnlyRequiredEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('requirements' => 1)
		);
		$requiredEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$eventUid,
			$requiredEventUid
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$dependingEventUid,
			$eventUid
		);

		$this->fixture->limitToRequiredEventTopics($eventUid);

		$foundEvents = $this->fixture->build();

		$this->assertEquals(
			1,
			$foundEvents->count()
		);

		$this->assertNotEquals(
			$dependingUid,
			$foundEvents->current()->getUid()
		);
	}


	///////////////////////////////////////////
	// Tests concerning limitToDependingEvents
	///////////////////////////////////////////

	public function testLimitToDependingEventsCanFindOneDependingEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('requirements' => 1)
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$dependingEventUid,
			$eventUid
		);
		$this->fixture->limitToDependingEventTopics($eventUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToDependingEventsCanFindTwoDependingEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('requirements' => 1)
		);
		$dependingEventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$dependingEventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$dependingEventUid1,
			$eventUid
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$dependingEventUid2,
			$eventUid
		);

		$this->fixture->limitToDependingEventTopics($eventUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToDependingEventsFindsOnlyDependingEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('requirements' => 1)
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$requiredEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$dependingEventUid,
			$eventUid
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_REQUIREMENTS_MM,
			$eventUid,
			$requiredEventUid
		);

		$this->fixture->limitToDependingEventTopics($eventUid);
		$foundEvents = $this->fixture->build();

		$this->assertEquals(
			1,
			$foundEvents->count()
		);

		$this->assertNotEquals(
			$requiredEventUid,
			$foundEvents->current()->getUid()
		);

	}
}
?>