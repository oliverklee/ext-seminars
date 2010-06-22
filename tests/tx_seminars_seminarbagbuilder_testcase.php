<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
	/**
	 * @var tx_seminars_seminarbagbuilder
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer a UNIX timestamp in the past.
	 */
	private $past;

	/**
	 * @var integer a UNIX timestamp in the future.
	 */
	private $future;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_seminarbagbuilder();
		$this->fixture->setTestMode();
		$this->future = $GLOBALS['EXEC_TIME'] + 50;
		$this->past = $GLOBALS['EXEC_TIME'] - 50;
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
		unset(
			$this->fixture, $this->testingFramework, $this->past, $this->future
		);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBuilderBuildsABagChildObject() {
		$bag = $this->fixture->build();

		$this->assertTrue(
			is_subclass_of($bag, 'tx_seminars_bag')
		);

		$bag->__destruct();
	}

	public function testBuilderIgnoresHiddenEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('hidden' => 1)
		);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testBuilderFindsHiddenEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('hidden' => 1)
		);

		$this->fixture->setBackEndMode();
		$bag = $this->fixture->build();


		$this->assertFalse(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testBuilderIgnoresTimedEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000)
		);
		$bag = $this->fixture->build();


		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testBuilderFindsTimedEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000)
		);

		$this->fixture->setBackEndMode();
		$bag = $this->fixture->build();


		$this->assertFalse(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}


	////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain categories.
	////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function skippingLimitToCategoriesResultsInAllEvents() {
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
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToEmptyCategoryUidResultsInAllEvents() {
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
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->fixture->limitToCategories('');
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToEmptyCategoryAfterLimitToNonEmptyCategoriesUidResultsInAllEvents() {
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
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->fixture->limitToCategories($categoryUid);
		$this->fixture->limitToCategories('');
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesCanResultInOneEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->fixture->limitToCategories($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesCanResultInTwoEvents() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid2, $categoryUid
		);

		$this->fixture->limitToCategories($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesExcludesUnassignedEvents() {
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
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
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

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesExcludesEventsOfOtherCategories() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid2, $categoryUid2
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

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesForNoMatchesResultsInEmptyBag() {
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
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);

		$this->fixture->limitToCategories($categoryUid2);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesCanFindTopicRecords() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->fixture->limitToCategories($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesForMatchingTopicFindsDateRecordAndTopic() {
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
			'tx_seminars_seminars_categories_mm', $topicUid, $categoryUid
		);

		$this->fixture->limitToCategories($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$matchingUids = explode(',', $bag->getUids());
		$this->assertTrue(
			in_array($topicUid, $matchingUids)
		);
		$this->assertTrue(
			in_array($dateUid, $matchingUids)
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesFindsDateRecordForSingle() {
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
			'tx_seminars_seminars_categories_mm', $topicUid, $categoryUid
		);

		$this->fixture->limitToCategories($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesIgnoresTopicOfDateRecord() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $topicUid, $categoryUid1
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
			'tx_seminars_seminars_categories_mm', $dateUid, $categoryUid2
		);

		$this->fixture->limitToCategories($categoryUid2);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToCategoriesCanFindEventsFromMultipleCategories() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid2, $categoryUid2
		);

		$this->fixture->limitToCategories($categoryUid1 . ','  . $categoryUid2);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToPlaces(array($siteUid));
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToPlacesIgnoresEventsWithoutPlace() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToPlaces(array($siteUid));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid2,
			$siteUid2
		);
		$this->fixture->limitToPlaces(array($siteUid1, $siteUid2));
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToPlacesWithEmptyPlacesArrayFindsAllEvents() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToPlaces(array($siteUid));
		$this->fixture->limitToPlaces();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToPlacesIgnoresEventsWithDifferentPlace() {
		$siteUid1 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$siteUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm', $eventUid, $siteUid1
		);
		$this->fixture->limitToPlaces(array($siteUid2));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToPlacesWithOnePlaceFindsEventInTwoPlaces() {
		$siteUid1 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$siteUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm', $eventUid, $siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm', $eventUid, $siteUid2
		);
		$this->fixture->limitToPlaces(array($siteUid1));
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	//////////////////////////////////////
	// Tests concerning canceled events.
	//////////////////////////////////////

	public function testBuilderFindsCanceledEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testBuilderFindsConfirmedEventsWithHideCanceledEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->ignoreCanceledEvents();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsDisabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->allowCanceledEvents();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsEnabledThenDisabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents();
		$this->fixture->allowCanceledEvents();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledDisabledThenEnabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->allowCanceledEvents();
		$this->fixture->ignoreCanceledEvents();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('past');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastFindsOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('past');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastIgnoresCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('past');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastIgnoresUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('past');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastIgnoresUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('past');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();


		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastAndCurrentFindsOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastAndCurrentFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastAndCurrentIgnoresUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFramePastAndCurrentIgnoresUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('pastAndCurrent');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('current');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('current');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('current');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentIgnoresUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('current');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentIgnoresUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('current');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentAndUpcomingIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentAndUpcomingFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentAndUpcomingFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameCurrentAndUpcomingFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('currentAndUpcoming');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('upcoming');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcoming');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingIgnoresCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('upcoming');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('upcoming');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcoming');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * upcoming events with begin date
	/////////////////////////////////////////////////////////////////

	public function testSetTimeFrameUpcomingWithBeginDateIgnoresPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('upcomingWithBeginDate');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingWithBeginDateIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcomingWithBeginDate');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingWithBeginDateIgnoresCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('upcomingWithBeginDate');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingWithBeginDateFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('upcomingWithBeginDate');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingWithBeginDateFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcomingWithBeginDate');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameUpcomingWithBeginDateNotFindsEventsWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('upcomingWithBeginDate');
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
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
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameDeadlineNotOverIgnoresOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameDeadlineNotOverIgnoresCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameDeadlineNotOverFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameDeadlineNotOverFindsUpcomingEventsWithUpcomingDeadline() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 2 * ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK,
				'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameDeadlineNotOverIgnoresUpcomingEventsWithPassedDeadline() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK,
				'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameDeadlineNotOverFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0,
				'deadline_registration' => 0
			)
		);

		$this->fixture->setTimeFrame('deadlineNotOver');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limiting the bag to events in certain time-frames.
	//
	// * today
	/////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setTimeFrameTodayFindsOpenEndedEventStartingToday() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
				'end_date' => 0,
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function setTimeFrameTodayNotFindsOpenEndedEventStartingTomorrow() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0,
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function setTimeFrameTodayFindsEventStartingTodayEndingTomorrow() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function setTimeFrameTodayFindsEventStartingYesterdayEndingToday() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function setTimeFrameTodayFindsEventStartingYesterdayEndingTomorrow() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function setTimeFrameTodayIgnoresEventStartingLastWeekEndingYesterday() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function setTimeFrameTodayIgnoresEventStartingTomorrowEndingNextWeek() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK,
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function setTimeFrameTodayIgnoresEventWithoutDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => 0,
				'end_date' => 0,
			)
		);

		$this->fixture->setTimeFrame('today');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('all');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameAllFindsOpenEndedPastEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_WEEK,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('all');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameAllFindsCurrentEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY
			)
		);

		$this->fixture->setTimeFrame('all');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameAllFindsUpcomingEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_WEEK
			)
		);

		$this->fixture->setTimeFrame('all');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testSetTimeFrameAllFindsUpcomingOpenEndedEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'end_date' => 0
			)
		);

		$this->fixture->setTimeFrame('all');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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

		$bag->__destruct();
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

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 1'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 1'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid2,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 1', 'test city 2'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 2'));
		$this->fixture->limitToCities();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 2'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToCitiesIgnoresEventWithPlaceWithoutCity() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCities(array('test city 1'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 1', 'test city 2'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 1'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid2
		);
		$this->fixture->limitToCities(array('test city 2', 'test city 3'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('DE'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('US'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid2,
			$siteUid2
		);
		$this->fixture->limitToCountries(array('US', 'DE'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid1,
			$siteUid1
		);
		$this->fixture->limitToCountries(array('DE'));
		$this->fixture->limitToCountries();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('US'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToCountriesIgnoresEventsWithPlaceWithoutCountry() {
		$siteUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('place' => 1)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid
		);
		$this->fixture->limitToCountries(array('DE'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$siteUid2
		);
		$this->fixture->limitToCountries(array('US'));
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToLanguagesWithEmptyLanguagesArrayFindsAllEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'EN')
		);
		$this->fixture->limitToLanguages(array('DE'));
		$this->fixture->limitToLanguages();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToLanguagesIgnoresEventsWithDifferentLanguage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'DE')
		);
		$this->fixture->limitToLanguages(array('EN'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToLanguagesIgnoresEventsWithoutLanguage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToLanguages(array('EN'));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToTopicRecordsIgnoresSingleEventRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->fixture->limitToTopicRecords();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToTopicRecordsIgnoresEventDateRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_DATE)
		);
		$this->fixture->limitToTopicRecords();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testRemoveLimitToTopicRecordsFindsEventDateRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_DATE)
		);
		$this->fixture->limitToTopicRecords();
		$this->fixture->removeLimitToTopicRecords();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToOwnerWithPositiveFeUserUidIgnoresEventsWithoutOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToOwner($feUserUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToOwnerWithPositiveFeUserUidIgnoresEventsWithDifferentOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('owner_feuser' => ($feUserUid + 1))
		);
		$this->fixture->limitToOwner($feUserUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToOwnerWithZeroFeUserUidFindsEventsWithoutOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->fixture->limitToOwner($feUserUid);
		$this->fixture->limitToOwner(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToOwnerWithZeroFeUserUidFindsEventsWithOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('owner_feuser' => $feUserUid)
		);
		$this->fixture->limitToOwner($feUserUid);
		$this->fixture->limitToOwner(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToDateAndSingleRecordsFindsSingleRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_COMPLETE)
		);
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToDateAndSingleRecordsIgnoresTopicRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testRemoveLimitToDateAndSingleRecordsFindsTopicRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->fixture->limitToDateAndSingleRecords();
		$this->fixture->removeLimitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_feusers_mm',
			$eventUid,
			$feUserUid
		);

		$this->fixture->limitToEventManager($feUserUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToEventManagerWithPositiveFeUserUidIgnoresEventsWithoutEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->fixture->limitToEventManager($feUserUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToEventManagerWithZeroFeUserUidFindsEventsWithoutEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->fixture->limitToEventManager($feUserUid);
		$this->fixture->limitToEventManager(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
		$event = new tx_seminars_seminar($eventUid1);
		$this->fixture->limitToEventsNextDay($event);
		$bag = $this->fixture->build();
		$event->__destruct();

		$this->assertEquals(
			$eventUid2,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$event = new tx_seminars_seminar($eventUid);
		$this->fixture->limitToEventsNextDay($event);
		$bag = $this->fixture->build();
		$event->__destruct();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$event = new tx_seminars_seminar($eventUid);
		$this->fixture->limitToEventsNextDay($event);
		$bag = $this->fixture->build();
		$event->__destruct();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();
		$date->__destruct();

		$this->assertEquals(
			$dateUid2,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();
		$topic->__destruct();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();
		$date->__destruct();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();
		$date->__destruct();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();
		$date->__destruct();

		$this->assertEquals(
			4,
			$bag->count()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();
		$date->__destruct();

		$this->assertEquals(
			3,
			$bag->count()
		);

		$bag->__destruct();
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limitToFullTextSearch() for single event records
	/////////////////////////////////////////////////////////////////

	public function testLimitToFullTextSearchWithTwoCommasAsSearchWordFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch(',,');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedByTwoSpacesFindsEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo  bar');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchWithTwoCommasSeparatedByTwoSpacesFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch(',  ,');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchWithTooShortSearchWordFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('o');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTitle() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInSubtitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('subtitle' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInSubtitle() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('subtitle' => 'bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInDescription() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('description' => 'foo bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInDescription() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('description' => 'bar event')
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'foo bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm',
			$eventUid,
			$organizerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm',
			$eventUid,
			$organizerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_target_groups_mm',
			$eventUid,
			$targetGroupUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_target_groups_mm',
			$eventUid,
			$targetGroupUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_categories_mm',
			$eventUid,
			$categoryUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_categories_mm',
			$eventUid,
			$categoryUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedBySpaceFindsTwoEventsWithSearchWordsInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'foo event bar')
		);
		$this->fixture->limitToFullTextSearch('foo bar');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedByCommaFindsTwoEventsWithSearchWordsInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'foo event bar')
		);
		$this->fixture->limitToFullTextSearch('foo,bar');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_categories_mm',
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_categories_mm',
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_target_groups_mm',
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_target_groups_mm',
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'foo bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm',
			$eventUid,
			$organizerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInOrganizerTitle() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array('title' => 'bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm',
			$eventUid,
			$organizerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_partners',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_tutors',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_speakers_mm_leaders',
			$eventUid,
			$speakerUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertEquals(
			$eventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_place_mm',
			$eventUid,
			$placeUid
		);
		$this->fixture->limitToFullTextSearch('foo');
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_requirements_mm',
			$eventUid,
			$requiredEventUid
		);
		$this->fixture->limitToRequiredEventTopics($eventUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_requirements_mm',
			$eventUid,
			$requiredEventUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$eventUid,
			$requiredEventUid2
		);

		$this->fixture->limitToRequiredEventTopics($eventUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_requirements_mm',
			$eventUid,
			$requiredEventUid
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependingEventUid,
			$eventUid
		);

		$this->fixture->limitToRequiredEventTopics($eventUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertNotEquals(
			$dependingEventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_requirements_mm',
			$dependingEventUid,
			$eventUid
		);
		$this->fixture->limitToDependingEventTopics($eventUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_requirements_mm',
			$dependingEventUid1,
			$eventUid
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependingEventUid2,
			$eventUid
		);

		$this->fixture->limitToDependingEventTopics($eventUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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
			'tx_seminars_seminars_requirements_mm',
			$dependingEventUid,
			$eventUid
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$eventUid,
			$requiredEventUid
		);

		$this->fixture->limitToDependingEventTopics($eventUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertNotEquals(
			$requiredEventUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}


	////////////////////////////////////////////////////////////
	// Tests concerning limitToTopicsWithoutRegistrationByUser
	////////////////////////////////////////////////////////////

	public function testLimitToTopicsWithoutRegistrationByUserFindsTopicWithoutDate() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);

		$this->fixture->limitToTopicsWithoutRegistrationByUser(
			$this->testingFramework->createFrontEndUser()
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$topicUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToTopicsWithoutRegistrationByUserFindsTopicWithDate() {
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

		$this->fixture->limitToTopicsWithoutRegistrationByUser(
			$this->testingFramework->createFrontEndUser()
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$topicUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToTopicsWithoutRegistrationByUserNotFindsDate() {
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

		$this->fixture->limitToTopicsWithoutRegistrationByUser(
			$this->testingFramework->createFrontEndUser()
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToTopicsWithoutRegistrationByUserFindsTopicWithDateWithRegistrationByOtherUser() {
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
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $dateUid,
				'user' => $this->testingFramework->createFrontEndUser()
			)
		);

		$this->fixture->limitToTopicsWithoutRegistrationByUser(
			$this->testingFramework->createFrontEndUser()
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$topicUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
	}

	public function testLimitToWithoutRegistrationByUserDoesNotFindTopicWithDateRegistrationByTheUserWithoutExpiry() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'expiry' => 0
			)
		);
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $dateUid, 'user' => $userUid)
		);

		$this->fixture->limitToTopicsWithoutRegistrationByUser($userUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToWithoutRegistrationByUserDoesNotFindTopicWithDateRegistrationByTheUserWithFutureExpiry() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'expiry' => $this->future
			)
		);
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $dateUid, 'user' => $userUid)
		);

		$this->fixture->limitToTopicsWithoutRegistrationByUser($userUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToWithoutRegistrationByUserFindsTopicWithDateRegistrationByTheUserWithPastExpiry() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'expiry' => $this->past
			)
		);
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $dateUid, 'user' => $userUid)
		);

		$this->fixture->limitToTopicsWithoutRegistrationByUser($userUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToWithoutRegistrationByUserDoesNotFindTopicWithDateRegistrationByTheUserAndOtherUser() {
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
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $dateUid, 'user' => $userUid)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $dateUid,
				'user' => $this->testingFramework->createFrontEndUser()
			)
		);

		$this->fixture->limitToTopicsWithoutRegistrationByUser($userUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToTopicsWithoutRegistrationByUserAndLimitToRequiredEventTopicsCanReturnOneEntry() {
		$requiredTopicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $requiredTopicUid,
			)
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'requirements' => 1,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$eventUid,
			$requiredTopicUid
		);

		$this->fixture->limitToRequiredEventTopics($eventUid);
		$this->fixture->limitToTopicsWithoutRegistrationByUser(
			$this->testingFramework->createFrontEndUser()
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	//////////////////////////////////////////////////////
	// Test concerning limitToCancelationReminderNotSent
	//////////////////////////////////////////////////////

	public function testLimitToCancelationDeadlineReminderNotSentFindsEventWithCancelationReminderSentFlagFalse() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('cancelation_deadline_reminder_sent' => 0)
		);

		$this->fixture->limitToCancelationDeadlineReminderNotSent();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToCancelationDeadlineReminderNotSentNotFindsEventWithCancelationReminderSentFlagTrue() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('cancelation_deadline_reminder_sent' => 1)
		);

		$this->fixture->limitToCancelationDeadlineReminderNotSent();
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}


	//////////////////////////////////////////////////////////
	// Test concerning limitToEventTakesPlaceReminderNotSent
	//////////////////////////////////////////////////////////

	public function testLimitToEventTakesPlaceReminderNotSentFindsEventWithConfirmationInformationSentFlagFalse() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('event_takes_place_reminder_sent' => 0)
		);

		$this->fixture->limitToEventTakesPlaceReminderNotSent();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToEventTakesPlaceReminderNotSentNotFindsEventWithConfirmationInformationSentFlagTrue() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('event_takes_place_reminder_sent' => 1)
		);

		$this->fixture->limitToEventTakesPlaceReminderNotSent();
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}


	///////////////////////////////////
	// Tests concerning limitToStatus
	///////////////////////////////////

	public function testLimitToStatusFindsEventWithStatusCanceledIfLimitIsStatusCanceled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_CANCELED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CANCELED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusNotFindsEventWithStatusPlannedIfLimitIsStatusCanceled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_PLANNED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CANCELED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusNotFindsEventWithStatusConfirmedIfLimitIsStatusCanceled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CANCELED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusFindsEventWithStatusConfirmedIfLimitIsStatusConfirmed() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CONFIRMED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusNotFindsEventWithStatusPlannedIfLimitIsStatusConfirmed() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_PLANNED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CONFIRMED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusNotFindsEventWithStatusCanceledIfLimitIsStatusConfirmed() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_CANCELED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CONFIRMED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusFindsEventWithStatusPlannedIfLimitIsStatusPlanned() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_PLANNED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_PLANNED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusNotFindsEventWithStatusConfirmedIfLimitIsStatusPlanned() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_PLANNED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testLimitToStatusNotFindsEventWithStatusCanceledIfLimitIsStatusPlanned() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => tx_seminars_seminar::STATUS_CANCELED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_PLANNED);
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}


	//////////////////////////////////////////////////
	// Tests concerning limitToDaysBeforeBeginDate
	//////////////////////////////////////////////////

	public function testlimitToDaysBeforeBeginDateFindsEventWithFutureBeginDateWithinProvidedDays() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY)
		);

		$this->fixture->limitToDaysBeforeBeginDate(2);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testlimitToDaysBeforeBeginDateFindsEventWithPastBeginDateWithinProvidedDays() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY)
		);

		$this->fixture->limitToDaysBeforeBeginDate(3);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testlimitToDaysBeforeBeginDateNotFindsEventWithFutureBeginDateOutOfProvidedDays() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (2 * ONE_DAY))
		);

		$this->fixture->limitToDaysBeforeBeginDate(1);
		$bag = $this->fixture->build();

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testlimitToDaysBeforeBeginDateFindsEventWithPastBeginDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] - (2 * ONE_DAY))
		);

		$this->fixture->limitToDaysBeforeBeginDate(1);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testlimitToDaysBeforeBeginDateFindsEventWithNoBeginDate() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$this->fixture->limitToDaysBeforeBeginDate(1);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	//////////////////////////////////////////////
	// Tests concerning limitToEarliestBeginDate
	//////////////////////////////////////////////

	public function test_limitToEarliestBeginDate_ForEventWithoutBeginDate_FindsThisEvent() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);
		$this->fixture->limitToEarliestBeginDate(42);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToEarliestBeginDate_ForEventWithBeginDateEqualToGivenTimestamp_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginDate(42);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToEarliestBeginDate_ForEventWithGreaterBeginDateThanGivenTimestamp_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginDate(21);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToEarliestBeginDate_ForEventWithBeginDateLowerThanGivenTimestamp_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginDate(84);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToEarliestBeginDate_ForZeroGivenAsTimestamp_UnsetsFilter() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('begin_date' => 21)
		);

		$this->fixture->limitToEarliestBeginDate(42);

		$this->fixture->limitToEarliestBeginDate(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	////////////////////////////////////////////
	// Tests concerning limitToLatestBeginDate
	////////////////////////////////////////////

	public function test_limitToLatestBeginDate_ForEventWithoutDate_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);
		$this->fixture->limitToLatestBeginDate(42);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToLatestBeginDate_ForEventBeginDateEqualToGivenTimestamp_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginDate(42);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToLatestBeginDate_ForEventWithBeginDateAfterGivenTimestamp_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginDate(21);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToLatestBeginDate_ForEventBeginDateBeforeGivenTimestamp_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginDate(84);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToLatestBeginDate_ForZeroGiven_UnsetsTheFilter() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$this->fixture->limitToLatestBeginDate(42);
		$this->fixture->limitToLatestBeginDate(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	///////////////////////////////////////
	// Tests concerning showHiddenRecords
	///////////////////////////////////////

	public function test_showHiddenRecords_ForHiddenEvent_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('hidden' => 1)
		);

		$this->fixture->showHiddenRecords();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_showHiddenRecords_ForVisibleEvent_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('hidden' => 0)
		);

		$this->fixture->showHiddenRecords();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	////////////////////////////////////////////////
	// Tests concerning limitToEventsWithVacancies
	////////////////////////////////////////////////

	public function test_LimitToEventsWithVacancies_ForEventWithNoRegistrationNeeded_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('needs_registration' => 0)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_LimitToEventsWithVacancies_ForEventWithUnlimitedVacancies_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('needs_registration' => 1, 'attendees_max' => 0)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_LimitToEventsWithVacancies_ForEventNoVacanciesAndQueue_DoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'needs_registration' => 1,
				'attendees_max' => 1,
				'queue_size' => 1,
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $eventUid, 'seats' => 1)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_LimitToEventsWithVacancies_ForEventWithOneVacancy_FindsThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('needs_registration' => 1, 'attendees_max' => 2)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $eventUid, 'seats' => 1)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_LimitToEventsWithVacancies_ForEventWithNoVacancies_DoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('needs_registration' => 1, 'attendees_max' => 1)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $eventUid, 'seats' => 1)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_LimitToEventsWithVacancies_ForEventWithNoVacanciesThroughOfflineRegistrations_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'needs_registration' => 1,
				'attendees_max' => 10,
				'offline_attendees' => 10,
			)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_LimitToEventsWithVacancies_ForEventWithNoVacanciesThroughRegistrationsWithMultipleSeats_DoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'needs_registration' => 1,
				'attendees_max' => 10,
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $eventUid, 'seats' => 10)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_LimitToEventsWithVacancies_ForEventWithNoVacanciesThroughRegularAndOfflineRegistrations_DoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'needs_registration' => 1,
				'attendees_max' => 10,
				'offline_attendees' => 5,
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $eventUid, 'seats' => 5)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToEventsWithVacanciesForEventWithVacanciesAndNoAttendees_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('needs_registration' => 1, 'attendees_max' => 10)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToEventsWithVacanciesForEventWithVacanciesAndOnlyOfflineAttendees_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'needs_registration' => 1,
				'attendees_max' => 10,
				'offline_attendees' => 9,
			)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	///////////////////////////////////////
	// Tests concerning limitToOrganizers
	///////////////////////////////////////

	public function test_limitToOrganizers_ForOneProvidedOrganizerAndEventWithThisOrganizer_FindsThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $organizerUid, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToOrganizers_ForOneProvidedOrganizerAndEventWithoutOrganizer_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);

		$this->fixture->limitToOrganizers($organizerUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToOrganizers_ForOneProvidedOrganizerAndEventWithOtherOrganizer_DoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$organizerUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);
		$organizerUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $organizerUid1, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid2);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToOrganizers_ForTwoProvidedOrganizersAndEventWithFirstOrganizer_FindsThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$organizerUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);
		$organizerUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $organizerUid1, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid1 . ',' . $organizerUid2);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToOrganizers_ForProvidedOrganizerAndTwoEventsWithThisOrganizer_FindsTheseEvents() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid1, $organizerUid, 'organizers'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid2, $organizerUid, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToOrganizers_ForProvidedOrganizerAndTopicWithOrganizer_ReturnsTheTopicsDate() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $topicUid, $organizerUid, 'organizers'
		);

		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);

		$this->fixture->limitToOrganizers($organizerUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->getUids()
		);

		$bag->__destruct();
	}

	public function test_limitToOrganizers_ForNoProvidedOrganizer_FindsEventWithOrganizer() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $organizerUid, 'organizers'
		);

		$this->fixture->limitToOrganizers('');
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	////////////////////////////////
	// Tests concerning limitToAge
	////////////////////////////////

	public function test_limitToAge_ForAgeWithinEventsAgeRange_FindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(6);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForAgeEqualToLowerLimitOfAgeRange_FindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 15, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForAgeEqualToHigherLimitOfAgeRange_FindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 15)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForNoLowerLimitAndAgeLowerThanMaximumAge_FindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 0, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForAgeHigherThanMaximumAge_DoesNotFindThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 0, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(51);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForNoHigherLimitAndAgeHigherThanMinimumAge_FindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 0)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForAgeLowerThanMinimumAge_FindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 0)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(4);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForEventWithoutTargetGroupAndAgeProvided_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->fixture->limitToAge(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForEventWithTargetGroupWithNoLimits_FindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForEventWithTwoTargetGroupOneWithMatchingRangeAndOneWithoutMatchingRange_FindsThisEvent() {
		$targetGroupUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 20, 'maximum_age' => 50)
		);
		$targetGroupUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 20)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid1, 'target_groups'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid2, 'target_groups'
		);

		$this->fixture->limitToAge(21);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForEventWithTwoTargetGroupBothWithMatchingRanges_FindsThisEventOnlyOnce() {
		$targetGroupUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 50)
		);
		$targetGroupUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 20)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid1, 'target_groups'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid2, 'target_groups'
		);

		$this->fixture->limitToAge(6);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToAge_ForAgeZeroGiven_FindsEventWithAgeLimits() {
		$targetGroupUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS,
			array('minimum_age' => 5, 'maximum_age' => 15)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	/////////////////////////////////////////
	// Tests concerning limitToMaximumPrice
	/////////////////////////////////////////

	public function test_limitToMaximumPrice_ForEventWithRegularPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 42)
		);

		$this->fixture->limitToMaximumPrice(43);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithRegularPriceZero_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 0)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithRegularPriceEqualToMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 50)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithRegularPriceHigherThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 51)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithSpecialPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 51, 'price_special' => 49)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithSpecialPriceEqualToMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 43, 'price_special' => 42)
		);

		$this->fixture->limitToMaximumPrice(42);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithSpecialPriceHigherThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 43, 'price_special' => 43)
		);

		$this->fixture->limitToMaximumPrice(42);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithRegularBoardPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 51, 'price_regular_board' => 49)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithRegularBoardPriceEqualToMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 51, 'price_regular_board' => 50)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithRegularBoardPriceHigherThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 51, 'price_regular_board' => 51)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithSpecialBoardPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 51, 'price_special_board' => 49)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithSpecialBoardPriceEqualToMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 51, 'price_special_board' => 50)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithSpecialBoardPriceHigherThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('price_regular' => 51, 'price_special_board' => 51)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForTopicWithRegularPriceLowerThanMaximum_FindsTheDateForThisEvent() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 49,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$dateUid,
			$bag->getUids()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForTopicWithRegularPriceHigherThanMaximum_DoesNotFindTheDateForThisEvent() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'topic' => $topicUid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_regular_early' => 49,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceEqualToMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_regular_early' => 50,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceHigherThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_regular_early' => 51,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInPastAndRegularEarlyPriceLowerThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_regular_early' => 49,
				'deadline_early_bird' => $this->past,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_special_early' => 49,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceEqualToMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_special_early' => 50,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceHigherThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_special_early' => 51,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForFutureEarlyBirdDeadlineAndNoEarlyBirdPrice_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInPastAndSpecialEarlyPriceLowerThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_special_early' => 49,
				'deadline_early_bird' => $this->past,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceHigherThanAndRegularPriceLowerThanMaximum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 49,
				'price_regular_early' => 51,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndNoSpecialEarlyPriceAndRegularPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 49,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndNoEarlySpecialPriceAndSpecialPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 51,
				'price_special' => 49,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForEventWithEarlyBirdDeadlineInFutureAndNoRegularEarlyPriceAndRegularPriceLowerThanMaximum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 49,
				'deadline_early_bird' => $this->future,
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMaximumPrice_ForZeroGiven_FindsEventWithNonZeroPrice() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 15,
			)
		);

		$this->fixture->limitToMaximumPrice(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	/////////////////////////////////////////
	// Tests concerning limitToMinimumPrice
	/////////////////////////////////////////

	public function test_limitToMinimumPrice_ForEventWithRegularPriceLowerThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForPriceGivenAndEventWithoutPrices_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$this->fixture->limitToMinimumPrice(16);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);
	}

	public function test_limitToMinimumPrice_ForEventWithRegularPriceEqualToMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithRegularPriceGreaterThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithRegularBoardPriceGreaterThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular_board' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithRegularBoardPriceEqualToMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular_board' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithRegularBoardPriceLowerThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular_board' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithSpecialBoardPriceGreaterThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_special_board' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithSpecialBoardPriceEqualToMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_special_board' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithSpecialBoardPriceLowerThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_special_board' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithSpecialPriceGreaterThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_special' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithSpecialPriceEqualToMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_special' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEventWithSpecialPriceLowerThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_special' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndRegularEarlyPriceZeroAndRegularPriceHigherThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular' => 16,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureNoPriceSet_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('deadline_early_bird' => $this->future)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndRegularEarlyPriceLowerThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular_early' => 14,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndRegularEarlyPriceEqualToMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular_early' => 15,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndRegularEarlyPriceHigherThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular_early' => 16,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceZeroAndSpecialPriceHigherThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_special' => 16,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceLowerThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_special_early' => 14,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceEqualToMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_special_early' => 15,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceHigherThanMinimum_FindsThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_special_early' => 16,
				'deadline_early_bird' => $this->future
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInPastAndRegularEarlyPriceHigherThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_regular_early' => 16,
				'deadline_early_bird' => $this->past
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForEarlyBirdDeadlineInPastAndSpecialEarlyPriceHigherThanMinimum_DoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'price_special_early' => 16,
				'deadline_early_bird' => $this->past
			)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function test_limitToMinimumPrice_ForMinimumPriceZero_FindsEventWithRegularPrice() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('price_regular' => 16)
		);

		$this->fixture->limitToMinimumPrice(0);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}
}
?>