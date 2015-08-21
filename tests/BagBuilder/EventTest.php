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
	private $fixture = NULL;
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework = NULL;

	/**
	 * @var int a UNIX timestamp in the past.
	 */
	private $past = 0;

	/**
	 * @var int a UNIX timestamp in the future.
	 */
	private $future = 0;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_BagBuilder_Event();
		$this->fixture->setTestMode();
		$this->future = $GLOBALS['EXEC_TIME'] + 50;
		$this->past = $GLOBALS['EXEC_TIME'] - 50;
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
	}


	/*
	 * Tests for the basic builder functions.
	 */

	public function testBuilderBuildsABag() {
		$bag = $this->fixture->build();

		self::assertTrue(
			is_subclass_of($bag, 'tx_seminars_Bag_Abstract')
		);
	}

	/**
	 * @test
	 */
	public function builderIgnoresHiddenEventsByDefault() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('hidden' => 1)
		);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testBuilderFindsHiddenEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('hidden' => 1)
		);

		$this->fixture->setBackEndMode();
		$bag = $this->fixture->build();


		self::assertFalse(
			$bag->isEmpty()
		);
	}

	public function testBuilderIgnoresTimedEventsByDefault() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000)
		);
		$bag = $this->fixture->build();


		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testBuilderFindsTimedEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000)
		);

		$this->fixture->setBackEndMode();
		$bag = $this->fixture->build();


		self::assertFalse(
			$bag->isEmpty()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid1,
			$bag->current()->getUid()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);

		$matchingUids = explode(',', $bag->getUids());
		self::assertTrue(
			in_array($topicUid, $matchingUids)
		);
		self::assertTrue(
			in_array($dateUid, $matchingUids)
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToPlacesIgnoresEventsWithoutPlace() {
		$siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->fixture->limitToPlaces(array($siteUid));
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
	}

	public function testLimitToPlacesWithEmptyPlacesArrayFindsAllEvents() {
		$siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->fixture->limitToPlaces(array($siteUid));
		$this->fixture->limitToPlaces();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledEvents() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testBuilderFindsConfirmedEventsWithHideCanceledEvents() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->ignoreCanceledEvents();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsDisabled() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => 1)
		);

		$this->fixture->allowCanceledEvents();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsEnabledThenDisabled() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents();
		$this->fixture->allowCanceledEvents();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledDisabledThenEnabled() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => 1)
		);

		$this->fixture->allowCanceledEvents();
		$this->fixture->ignoreCanceledEvents();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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


		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			0,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid1,
			$bag->current()->getUid()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
	}

	public function testLimitToLanguagesWithEmptyLanguagesArrayFindsAllEvents() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('language' => 'EN')
		);
		$this->fixture->limitToLanguages(array('DE'));
		$this->fixture->limitToLanguages();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToLanguagesIgnoresEventsWithDifferentLanguage() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('language' => 'DE')
		);
		$this->fixture->limitToLanguages(array('EN'));
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToLanguagesIgnoresEventsWithoutLanguage() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->fixture->limitToLanguages(array('EN'));
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToTopicRecordsIgnoresSingleEventRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->limitToTopicRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToTopicRecordsIgnoresEventDateRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
		);
		$this->fixture->limitToTopicRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testRemoveLimitToTopicRecordsFindsEventDateRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
		);
		$this->fixture->limitToTopicRecords();
		$this->fixture->removeLimitToTopicRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToOwnerWithPositiveFeUserUidIgnoresEventsWithoutOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->fixture->limitToOwner($feUserUid);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToOwnerWithPositiveFeUserUidIgnoresEventsWithDifferentOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('owner_feuser' => ($feUserUid + 1))
		);
		$this->fixture->limitToOwner($feUserUid);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToOwnerWithZeroFeUserUidFindsEventsWithoutOwner() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->fixture->limitToOwner($feUserUid);
		$this->fixture->limitToOwner(0);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToDateAndSingleRecordsFindsSingleRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToDateAndSingleRecordsIgnoresTopicRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testRemoveLimitToDateAndSingleRecordsFindsTopicRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->limitToDateAndSingleRecords();
		$this->fixture->removeLimitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToEventManagerWithPositiveFeUserUidIgnoresEventsWithoutEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->fixture->limitToEventManager($feUserUid);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToEventManagerWithZeroFeUserUidFindsEventsWithoutEventManager() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->fixture->limitToEventManager($feUserUid);
		$this->fixture->limitToEventManager(0);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid2,
			$bag->current()->getUid()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid2,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			4,
			$bag->count()
		);
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

		self::assertSame(
			3,
			$bag->count()
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests for limitToFullTextSearch() for single event records
	/////////////////////////////////////////////////////////////////

	public function testLimitToFullTextSearchWithTwoCommasAsSearchWordFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'avocado paprika event')
		);
		$this->fixture->limitToFullTextSearch(',,');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedByTwoSpacesFindsEvents() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'avocado paprika event')
		);
		$this->fixture->limitToFullTextSearch('avocado  paprika');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTwoCommasSeparatedByTwoSpacesFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'avocado paprika event')
		);
		$this->fixture->limitToFullTextSearch(',  ,');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTooShortSearchWordFindsAllEvents() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'avocado paprika event')
		);
		$this->fixture->limitToFullTextSearch('o');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInAccreditationNumber() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'avocado paprika event',
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInAccreditationNumber() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'paprika event',
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('title' => 'avocado paprika event')
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTitle() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('title' => 'paprika event')
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInSubtitle() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('subtitle' => 'avocado paprika event')
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInSubtitle() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('subtitle' => 'paprika event')
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInDescription() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('description' => 'avocado paprika event')
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInDescription() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('description' => 'paprika event')
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('title' => 'avocado paprika speaker')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('title' => 'paprika speaker')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'avocado paprika place')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'paprika place')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'avocado paprika city')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'paprika city')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types',
			array('title' => 'avocado paprika event type')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'event_type' => $eventTypeUid,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types',
			array('title' => 'paprika event type')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'event_type' => $eventTypeUid,
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
			)
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'avocado paprika category')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'paprika category')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedBySpaceFindsTwoEventsWithSearchWordsInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('title' => 'avocado event paprika')
		);
		$this->fixture->limitToFullTextSearch('avocado paprika');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchWithTwoSearchWordsSeparatedByCommaFindsTwoEventsWithSearchWordsInTitle() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('title' => 'avocado event paprika')
		);
		$this->fixture->limitToFullTextSearch('avocado,paprika');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function limitToFullTextSearchFindsEventWithSearchWordInTargetGroupTitle() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('title' => 'avocado paprika place')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTargetGroupTitle() {
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups',
			array('title' => 'paprika place')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	////////////////////////////////////////////////////////////////
	// Tests for limitToFullTextSearch() for topic event records
	////////////////////////////////////////////////////////////////

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicTitle() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'avocado paprika event',
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicTitle() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'paprika event',
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicSubtitle() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'subtitle' => 'avocado paprika event',
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicSubtitle() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'subtitle' => 'paprika event',
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicDescription() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'description' => 'avocado paprika event',
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicDescription() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'description' => 'paprika event',
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'avocado paprika category')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'paprika category')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventWithSearchWordInTopicEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types',
			array('title' => 'avocado paprika event type')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventWithoutSearchWordInTopicEventTypeTitle() {
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types',
			array('title' => 'paprika event type')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$this->fixture->limitToDateAndSingleRecords();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}


	///////////////////////////////////////////////////////////////
	// Tests for limitToFullTextSearch() for event date records
	///////////////////////////////////////////////////////////////

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInAccreditationNumber() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'avocado paprika event',
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInAccreditationNumber() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'accreditation_number' => 'paprika event',
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
			)
		);
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('title' => 'avocado paprika speaker')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInSpeakerTitle() {
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('title' => 'paprika speaker')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'avocado paprika place')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPlaceTitle() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'paprika place')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testLimitToFullTextSearchFindsEventDateWithSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'avocado paprika city')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$eventUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToFullTextSearchIgnoresEventDateWithoutSearchWordInPlaceCity() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('city' => 'paprika city')
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
		$this->fixture->limitToFullTextSearch('avocado');
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertNotEquals(
			$dependingEventUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertNotEquals(
			$requiredEventUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$topicUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$topicUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$topicUid,
			$bag->current()->getUid()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToCancelationDeadlineReminderNotSentNotFindsEventWithCancelationReminderSentFlagTrue() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('cancelation_deadline_reminder_sent' => 1)
		);

		$this->fixture->limitToCancelationDeadlineReminderNotSent();
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToEventTakesPlaceReminderNotSentNotFindsEventWithConfirmationInformationSentFlagTrue() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('event_takes_place_reminder_sent' => 1)
		);

		$this->fixture->limitToEventTakesPlaceReminderNotSent();
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToStatusNotFindsEventWithStatusPlannedIfLimitIsStatusCanceled() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_PLANNED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CANCELED);
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
	}

	public function testLimitToStatusNotFindsEventWithStatusConfirmedIfLimitIsStatusCanceled() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CANCELED);
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
	}

	public function testLimitToStatusFindsEventWithStatusConfirmedIfLimitIsStatusConfirmed() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CONFIRMED);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToStatusNotFindsEventWithStatusPlannedIfLimitIsStatusConfirmed() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_PLANNED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CONFIRMED);
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
	}

	public function testLimitToStatusNotFindsEventWithStatusCanceledIfLimitIsStatusConfirmed() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_CANCELED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_CONFIRMED);
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
	}

	public function testLimitToStatusFindsEventWithStatusPlannedIfLimitIsStatusPlanned() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_PLANNED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_PLANNED);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testLimitToStatusNotFindsEventWithStatusConfirmedIfLimitIsStatusPlanned() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_CONFIRMED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_PLANNED);
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
	}

	public function testLimitToStatusNotFindsEventWithStatusCanceledIfLimitIsStatusPlanned() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('cancelled' => tx_seminars_seminar::STATUS_CANCELED)
		);

		$this->fixture->limitToStatus(tx_seminars_seminar::STATUS_PLANNED);
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testlimitToDaysBeforeBeginDateFindsEventWithPastBeginDateWithinProvidedDays() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY)
		);

		$this->fixture->limitToDaysBeforeBeginDate(3);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testlimitToDaysBeforeBeginDateNotFindsEventWithFutureBeginDateOutOfProvidedDays() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (2 * tx_oelib_Time::SECONDS_PER_DAY))
		);

		$this->fixture->limitToDaysBeforeBeginDate(1);
		$bag = $this->fixture->build();

		self::assertSame(
			0,
			$bag->count()
		);
	}

	public function testlimitToDaysBeforeBeginDateFindsEventWithPastBeginDate() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_EXEC_TIME'] - (2 * tx_oelib_Time::SECONDS_PER_DAY))
		);

		$this->fixture->limitToDaysBeforeBeginDate(1);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	public function testlimitToDaysBeforeBeginDateFindsEventWithNoBeginDate() {
		$this->testingFramework->createRecord('tx_seminars_seminars');

		$this->fixture->limitToDaysBeforeBeginDate(1);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}


	/*
	 * Tests concerning limitToEarliestBeginOrEndDate
	 */

	/**
	 * @test
	 */
	public function limitToEarliestBeginOrEndDateForEventWithoutBeginDateFindsThisEvent() {
		$this->testingFramework->createRecord('tx_seminars_seminars');
		$this->fixture->limitToEarliestBeginOrEndDate(42);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToEarliestBeginOrEndDateForEventWithBeginDateEqualToGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginOrEndDate(42);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToEarliestBeginOrEndDateForEventWithGreaterBeginDateThanGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginOrEndDate(21);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToEarliestBeginOrEndDateForEventWithBeginDateLowerThanGivenTimestampDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToEarliestBeginOrEndDate(84);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToEarliestBeginOrEndDateForZeroGivenAsTimestampUnsetsFilter() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 21)
		);

		$this->fixture->limitToEarliestBeginOrEndDate(42);

		$this->fixture->limitToEarliestBeginOrEndDate(0);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToEarliestBeginOrEndDateForFindsEventStartingBeforeAndEndingAfterDeadline() {
		$this->testingFramework->createRecord('tx_seminars_seminars', array('begin_date' => 8, 'end_date' => 10));
		$this->fixture->limitToEarliestBeginOrEndDate(9);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}


	/*
	 * Tests concerning limitToLatestBeginOrEndDate
	 */

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForEventWithoutDateDoesNotFindThisEvent() {
		$this->testingFramework->createRecord('tx_seminars_seminars');
		$this->fixture->limitToLatestBeginOrEndDate(42);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForEventBeginDateEqualToGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginOrEndDate(42);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForEventWithBeginDateAfterGivenTimestampDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginOrEndDate(21);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForEventBeginDateBeforeGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$this->fixture->limitToLatestBeginOrEndDate(84);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForEventEndDateEqualToGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('end_date' => 42)
		);
		$this->fixture->limitToLatestBeginOrEndDate(42);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForEventWithEndDateAfterGivenTimestampDoesNotFindThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('end_date' => 42)
		);
		$this->fixture->limitToLatestBeginOrEndDate(21);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForEventEndDateBeforeGivenTimestampFindsThisEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('end_date' => 42)
		);
		$this->fixture->limitToLatestBeginOrEndDate(84);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
	}

	/**
	 * @test
	 */
	public function limitToLatestBeginOrEndDateForZeroGivenUnsetsTheFilter() {
		$this->testingFramework->createRecord('tx_seminars_seminars');

		$this->fixture->limitToLatestBeginOrEndDate(42);
		$this->fixture->limitToLatestBeginOrEndDate(0);
		$bag = $this->fixture->build();

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			2,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
		self::assertSame(
			$dateUid,
			$bag->current()->getUid()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToMinimumPriceForPriceGivenAndEventWithoutPricesDoesNotFindThisEvent() {
		$this->testingFramework->createRecord('tx_seminars_seminars');

		$this->fixture->limitToMinimumPrice(16);
		$bag = $this->fixture->build();

		self::assertTrue(
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertTrue(
			$bag->isEmpty()
		);
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

		self::assertSame(
			1,
			$bag->count()
		);
	}
}