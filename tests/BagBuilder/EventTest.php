<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BagBuilder_EventTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BagBuilder_Event
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

		$this->fixture = new tx_seminars_BagBuilder_Event();
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

	public function testBuilderBuildsABag() {
		$bag = $this->fixture->build();

		$this->assertTrue(
			is_subclass_of($bag, 'tx_seminars_Bag_Abstract')
		);

		$bag->__destruct();
	}

	public function testBuilderIgnoresHiddenEventsByDefault() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_categories'
		);

		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $topicUid, $categoryUid1
		);

		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid
			)
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
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
			'tx_seminars_categories'
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
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
		$siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('place' => 1)
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
		$siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->fixture->limitToPlaces(array($siteUid));
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testLimitToPlacesFindsEventsInTwoPlaces() {
		$siteUid1 = $this->testingFramework->createRecord('tx_seminars_sites');
		$siteUid2 = $this->testingFramework->createRecord('tx_seminars_sites');
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('place' => 1)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('place' => 1)
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
		$siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
		$siteUid1 = $this->testingFramework->createRecord('tx_seminars_sites');
		$siteUid2 = $this->testingFramework->createRecord('tx_seminars_sites');
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('place' => 1)
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
		$siteUid1 = $this->testingFramework->createRecord('tx_seminars_sites');
		$siteUid2 = $this->testingFramework->createRecord('tx_seminars_sites');
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('place' => 1)
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'InvalidArgumentException',
			'The time-frame key  is not valid.'
		);
		$this->fixture->setTimeFrame('');
	}

	public function testSetTimeFrameFailsWithInvalidKey() {
		$this->setExpectedException(
			'InvalidArgumentException',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 2 * tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK,
				'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK,
				'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_WEEK,
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_WEEK
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
			'tx_seminars_seminars',
			array(
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$typeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_event_types'
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$typeUid1 = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
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
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
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
			'tx_seminars_event_types'
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'event_type' => $typeUid,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_event_types'
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'event_type' => $typeUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_event_types'
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'event_type' => $typeUid1,
			)
		);

		$typeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_sites',
			array('city' => 'test city 1')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('city' => 'test city 2')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'test city 2')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('place' => 1)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('city' => 'test city 1')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('city' => 'test city 1')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
		$siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'test city 2')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'test city 2')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('city' => 'test city 1')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'test city 3')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('country' => 'US')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('country' => 'DE')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('place' => 1)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('country' => 'US')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
		$siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_sites',
			array('country' => 'US')
		);
		$siteUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('country' => 'DE')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array('language' => 'EN')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
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
			'InvalidArgumentException',
			'The parameter $feUserUid must be >= 0.'
		);

		$this->fixture->limitToOwner(-1);
	}

	public function testLimitToOwnerWithPositiveFeUserUidFindsEventsWithOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
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
			'InvalidArgumentException',
			'The parameter $feUserUid must be >= 0.'
		);

		$this->fixture->limitToEventManager(-1);
	}

	public function testLimitToEventManagerWithPositiveFeUserUidFindsEventsWithEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars'
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
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('begin_date' => tx_oelib_Time::SECONDS_PER_DAY, 'end_date' => (tx_oelib_Time::SECONDS_PER_DAY + 60 * 60))
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => (2 * tx_oelib_Time::SECONDS_PER_DAY),
				'end_date' => (60 * 60 + 2 * tx_oelib_Time::SECONDS_PER_DAY),
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
			'tx_seminars_seminars',
			array('begin_date' => tx_oelib_Time::SECONDS_PER_DAY, 'end_date' => (tx_oelib_Time::SECONDS_PER_DAY + 60 * 60))
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array('begin_date' => tx_oelib_Time::SECONDS_PER_DAY, 'end_date' => (tx_oelib_Time::SECONDS_PER_DAY + 60 * 60))
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => (3 * tx_oelib_Time::SECONDS_PER_DAY),
				'end_date' => (60 * 60 + 3 * tx_oelib_Time::SECONDS_PER_DAY),
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
			'InvalidArgumentException',
			'The event object given in the first parameter $event must ' .
				'have an end date set.'
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$dateUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'InvalidArgumentException',
			'The first parameter $event must be either a date or a topic record.'
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$event = new tx_seminars_seminar($eventUid);
		$this->fixture->limitToOtherDatesForTopic($event);
	}

	public function testLimitToOtherDatesForTopicIgnoresDateForOtherTopic() {
		$topicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$topicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid1,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$topicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid1,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars', array('title' => 'foo bar event')
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
			'tx_seminars_seminars', array('title' => 'foo bar event')
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
			'tx_seminars_seminars', array('title' => 'foo bar event')
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
			'tx_seminars_seminars', array('title' => 'foo bar event')
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
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'foo bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_sites',
			array('title' => 'foo bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_sites',
			array('title' => 'bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_sites',
			array('address' => 'foo bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_sites',
			array('address' => 'bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_sites',
			array('city' => 'foo bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_sites',
			array('city' => 'bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_event_types',
			array('title' => 'foo bar event type')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'event_type' => $eventTypeUid,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_event_types',
			array('title' => 'bar event type')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'event_type' => $eventTypeUid,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_organizers',
			array('title' => 'foo bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'organizers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_organizers',
			array('title' => 'bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'organizers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_target_groups',
			array('title' => 'foo bar target group')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'target_groups' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_target_groups',
			array('title' => 'bar target group')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'target_groups' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_categories',
			array('title' => 'foo bar category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'categories' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_categories',
			array('title' => 'bar category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'categories' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'title' => 'foo bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array(
				'title' => 'bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array(
				'subtitle' => 'foo bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array(
				'subtitle' => 'bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array(
				'description' => 'foo bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array(
				'description' => 'bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_categories',
			array('title' => 'foo bar category')
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'categories' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm',
			$topicUid,
			$categoryUid
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_categories',
			array('title' => 'bar category')
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'categories' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm',
			$topicUid,
			$categoryUid
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_target_groups',
			array('title' => 'foo bar target group')
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'target_groups' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_target_groups_mm',
			$topicUid,
			$targetGroupUid
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_target_groups',
			array('title' => 'bar target group')
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'target_groups' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_target_groups_mm',
			$topicUid,
			$targetGroupUid
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_event_types',
			array('title' => 'foo bar event type')
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'event_type' => $eventTypeUid,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_event_types',
			array('title' => 'bar event type')
		);
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'event_type' => $eventTypeUid,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'foo bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'bar event',
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_organizers',
			array('title' => 'foo bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'organizers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_organizers',
			array('title' => 'bar organizer')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'organizers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'speakers' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'partners' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'tutors' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('title' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('organization' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'foo bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_speakers',
			array('description' => 'bar speaker')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'leaders' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_sites',
			array('title' => 'foo bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_sites',
			array('title' => 'bar place')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_sites',
			array('address' => 'foo bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_sites',
			array('address' => 'bar address')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_sites',
			array('city' => 'foo bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_sites',
			array('city' => 'bar city')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'place' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array('requirements' => 1)
		);
		$requiredEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('requirements' => 1)
		);
		$requiredEventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$requiredEventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('requirements' => 1)
		);
		$requiredEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('requirements' => 1)
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('requirements' => 1)
		);
		$dependingEventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$dependingEventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('requirements' => 1)
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$requiredEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
				'expiry' => 0
			)
		);
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
				'expiry' => $this->future
			)
		);
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
				'expiry' => $this->past
			)
		);
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$userUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $dateUid, 'user' => $userUid)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredTopicUid,
			)
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'requirements' => 1,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC
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
			'tx_seminars_seminars', array('cancelation_deadline_reminder_sent' => 0)
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
			'tx_seminars_seminars', array('cancelation_deadline_reminder_sent' => 1)
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
			'tx_seminars_seminars', array('event_takes_place_reminder_sent' => 0)
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
			'tx_seminars_seminars', array('event_takes_place_reminder_sent' => 1)
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY)
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
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY)
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
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (2 * tx_oelib_Time::SECONDS_PER_DAY))
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
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] - (2 * tx_oelib_Time::SECONDS_PER_DAY))
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
		$this->testingFramework->createRecord('tx_seminars_seminars');

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

	/**
	 * @test
	 */
	public function limitToEarliestBeginDateForEventWithoutBeginDateFindsThisEvent() {
		$this->testingFramework->createRecord('tx_seminars_seminars');
		$this->fixture->limitToEarliestBeginDate(42);
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
	public function limitToEarliestBeginDateForEventWithBeginDateEqualToGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginDate(42);
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
	public function limitToEarliestBeginDateForEventWithGreaterBeginDateThanGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginDate(21);
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
	public function limitToEarliestBeginDateForEventWithBeginDateLowerThanGivenTimestampDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginDate(84);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToEarliestBeginDateForZeroGivenAsTimestampUnsetsFilter() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 21)
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

	/**
	 * @test
	 */
	public function limitToLatestBeginDateForEventWithoutDateDoesNotFindThisEvent() {
		$this->testingFramework->createRecord('tx_seminars_seminars');
		$this->fixture->limitToLatestBeginDate(42);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginDateForEventBeginDateEqualToGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginDate(42);
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
	public function limitToLatestBeginDateForEventWithBeginDateAfterGivenTimestampDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginDate(21);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginDateForEventBeginDateBeforeGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginDate(84);
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
	public function limitToLatestBeginDateForZeroGivenUnsetsTheFilter() {
		$this->testingFramework->createRecord('tx_seminars_seminars');

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

	/**
	 * @test
	 */
	public function showHiddenRecordsForHiddenEventFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('hidden' => 1)
		);

		$this->fixture->showHiddenRecords();
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
	public function showHiddenRecordsForVisibleEventFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('hidden' => 0)
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

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithNoRegistrationNeededFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('needs_registration' => 0)
		);

		$this->fixture->limitToEventsWithVacancies();
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
	public function limitToEventsWithVacanciesForEventWithUnlimitedVacanciesFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventNoVacanciesAndQueueDoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'needs_registration' => 1,
				'attendees_max' => 1,
				'queue_size' => 1,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'seats' => 1)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithOneVacancyFindsThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('needs_registration' => 1, 'attendees_max' => 2)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithNoVacanciesDoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('needs_registration' => 1, 'attendees_max' => 1)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'seats' => 1)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithNoVacanciesThroughOfflineRegistrationsDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithNoVacanciesThroughRegistrationsWithMultipleSeatsDoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'needs_registration' => 1,
				'attendees_max' => 10,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'seats' => 10)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithNoVacanciesThroughRegularAndOfflineRegistrationsDoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'needs_registration' => 1,
				'attendees_max' => 10,
				'offline_attendees' => 5,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'seats' => 5)
		);

		$this->fixture->limitToEventsWithVacancies();
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithVacanciesAndNoAttendeesFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToEventsWithVacanciesForEventWithVacanciesAndOnlyOfflineAttendeesFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToOrganizersForOneProvidedOrganizerAndEventWithThisOrganizerFindsThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid);
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
	public function limitToOrganizersForOneProvidedOrganizerAndEventWithoutOrganizerDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToOrganizersForOneProvidedOrganizerAndEventWithOtherOrganizerDoesNotFindThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$organizerUid1 = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);
		$organizerUid2 = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid1, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid2);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToOrganizersForTwoProvidedOrganizersAndEventWithFirstOrganizerFindsThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$organizerUid1 = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);
		$organizerUid2 = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid1, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid1 . ',' . $organizerUid2);
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
	public function limitToOrganizersForProvidedOrganizerAndTwoEventsWithThisOrganizerFindsTheseEvents() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid1, $organizerUid, 'organizers'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid2, $organizerUid, 'organizers'
		);

		$this->fixture->limitToOrganizers($organizerUid);
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
	public function limitToOrganizersForProvidedOrganizerAndTopicWithOrganizerReturnsTheTopicsDate() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $organizerUid, 'organizers'
		);

		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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

	/**
	 * @test
	 */
	public function limitToOrganizersForNoProvidedOrganizerFindsEventWithOrganizer() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid, 'organizers'
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

	/**
	 * @test
	 */
	public function limitToAgeForAgeWithinEventsAgeRangeFindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(6);
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
	public function limitToAgeForAgeEqualToLowerLimitOfAgeRangeFindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 15, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
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
	public function limitToAgeForAgeEqualToHigherLimitOfAgeRangeFindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 15)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
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
	public function limitToAgeForNoLowerLimitAndAgeLowerThanMaximumAgeFindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 0, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
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
	public function limitToAgeForAgeHigherThanMaximumAgeDoesNotFindThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 0, 'maximum_age' => 50)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(51);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToAgeForNoHigherLimitAndAgeHigherThanMinimumAgeFindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 0)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
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
	public function limitToAgeForAgeLowerThanMinimumAgeFindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 0)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(4);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToAgeForEventWithoutTargetGroupAndAgeProvidedFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->fixture->limitToAge(15);
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
	public function limitToAgeForEventWithTargetGroupWithNoLimitsFindsThisEvent() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups'
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
		);

		$this->fixture->limitToAge(15);
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
	public function limitToAgeForEventWithTwoTargetGroupOneWithMatchingRangeAndOneWithoutMatchingRangeFindsThisEvent() {
		$targetGroupUid1 = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 20, 'maximum_age' => 50)
		);
		$targetGroupUid2 = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 20)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid1, 'target_groups'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid2, 'target_groups'
		);

		$this->fixture->limitToAge(21);
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
	public function limitToAgeForEventWithTwoTargetGroupBothWithMatchingRangesFindsThisEventOnlyOnce() {
		$targetGroupUid1 = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 50)
		);
		$targetGroupUid2 = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 20)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid1, 'target_groups'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid2, 'target_groups'
		);

		$this->fixture->limitToAge(6);
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
	public function limitToAgeForAgeZeroGivenFindsEventWithAgeLimits() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('minimum_age' => 5, 'maximum_age' => 15)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $targetGroupUid, 'target_groups'
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithRegularPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 42)
		);

		$this->fixture->limitToMaximumPrice(43);
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
	public function limitToMaximumPriceForEventWithRegularPriceZeroFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 0)
		);

		$this->fixture->limitToMaximumPrice(50);
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
	public function limitToMaximumPriceForEventWithRegularPriceEqualToMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 50)
		);

		$this->fixture->limitToMaximumPrice(50);
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
	public function limitToMaximumPriceForEventWithRegularPriceHigherThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 51)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithSpecialPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithSpecialPriceEqualToMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithSpecialPriceHigherThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('price_regular' => 43, 'price_special' => 43)
		);

		$this->fixture->limitToMaximumPrice(42);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithRegularBoardPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithRegularBoardPriceEqualToMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithRegularBoardPriceHigherThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('price_regular' => 51, 'price_regular_board' => 51)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithSpecialBoardPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithSpecialBoardPriceEqualToMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithSpecialBoardPriceHigherThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('price_regular' => 51, 'price_special_board' => 51)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMaximumPriceForTopicWithRegularPriceLowerThanMaximumFindsTheDateForThisEvent() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'price_regular' => 49,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForTopicWithRegularPriceHigherThanMaximumDoesNotFindTheDateForThisEvent() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'price_regular' => 51,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'topic' => $topicUid,
				'object_type' => tx_seminars_Model_Event::TYPE_DATE
			)
		);

		$this->fixture->limitToMaximumPrice(50);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceEqualToMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceHigherThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInPastAndRegularEarlyPriceLowerThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceEqualToMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceHigherThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForFutureEarlyBirdDeadlineAndNoEarlyBirdPriceDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInPastAndSpecialEarlyPriceLowerThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceHigherThanAndRegularPriceLowerThanMaximumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndNoSpecialEarlyPriceAndRegularPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndNoEarlySpecialPriceAndSpecialPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndNoRegularEarlyPriceAndRegularPriceLowerThanMaximumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMaximumPriceForZeroGivenFindsEventWithNonZeroPrice() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEventWithRegularPriceLowerThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMinimumPriceForPriceGivenAndEventWithoutPricesDoesNotFindThisEvent() {
		$this->testingFramework->createRecord('tx_seminars_seminars');

		$this->fixture->limitToMinimumPrice(16);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEventWithRegularPriceEqualToMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithRegularPriceGreaterThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithRegularBoardPriceGreaterThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular_board' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithRegularBoardPriceEqualToMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular_board' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithRegularBoardPriceLowerThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular_board' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEventWithSpecialBoardPriceGreaterThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_special_board' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithSpecialBoardPriceEqualToMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_special_board' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithSpecialBoardPriceLowerThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_special_board' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEventWithSpecialPriceGreaterThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_special' => 16)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithSpecialPriceEqualToMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_special' => 15)
		);

		$this->fixture->limitToMinimumPrice(15);
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
	public function limitToMinimumPriceForEventWithSpecialPriceLowerThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_special' => 14)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceZeroAndRegularPriceHigherThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureNoPriceSetDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('deadline_early_bird' => $this->future)
		);

		$this->fixture->limitToMinimumPrice(15);
		$bag = $this->fixture->build();

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceLowerThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceEqualToMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceHigherThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceZeroAndSpecialPriceHigherThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceLowerThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceEqualToMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceHigherThanMinimumFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInPastAndRegularEarlyPriceHigherThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForEarlyBirdDeadlineInPastAndSpecialEarlyPriceHigherThanMinimumDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function limitToMinimumPriceForMinimumPriceZeroFindsEventWithRegularPrice() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('price_regular' => 16)
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