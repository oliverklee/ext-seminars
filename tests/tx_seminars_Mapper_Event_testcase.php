<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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

/**
 * Testcase for the 'event mapper' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Mapper_Event_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_Mapper_Event
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////
	// Tests regarding find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidReturnsEventInstance() {
		$this->assertTrue(
			$this->fixture->find(1) instanceof tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'Big event')
		);

		$this->assertEquals(
			'Big event',
			$this->fixture->find($uid)->getTitle()
		);
	}


	/////////////////////////////////
	// Tests regarding getTopic().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getTopicForSingleRecordThrowsException() {
		$this->setExpectedException(
			'Exception', 'This function may only be called for date records.'
		);

		$this->fixture->getLoadedTestingModel(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		)->getTopic();
	}

	/**
	 * @test
	 */
	public function getTopicForTopicRecordThrowsException() {
		$this->setExpectedException(
			'Exception', 'This function may only be called for date records.'
		);

		$this->fixture->getLoadedTestingModel(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		)->getTopic();
	}

	/**
	 * @test
	 */
	public function getTopicWithoutTopicReturnsNull() {
		$this->assertNull(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
			)->getTopic()
		);
	}

	/**
	 * @test
	 */
	public function getTopicWithTopicReturnsEventInstance() {
		$topic = $this->fixture->getNewGhost();

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'topic' => $topic->getUid(),
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				)
			)->getTopic() instanceof tx_seminars_Model_Event
		);
	}


	//////////////////////////////////////
	// Tests regarding getCategories().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getCategoriesForSingleEventReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getCategories() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForSingleEventWithOneCategoryReturnsListOfCategories() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $category->getUid(), 'categories'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCategories()->first() instanceof
				tx_seminars_Model_Category
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForSingleEventWithOneCategoryReturnsOneCategory() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $category->getUid(), 'categories'
		);

		$this->assertEquals(
			$category->getUid(),
			$this->fixture->find($uid)->getCategories()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventTopicReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getCategories() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventTopicWithOneCategoryReturnsListOfCategories() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $category->getUid(), 'categories'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCategories()->first() instanceof
				tx_seminars_Model_Category
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventTopicWithOneCategoryReturnsOneCategory() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $category->getUid(), 'categories'
		);

		$this->assertEquals(
			$category->getUid(),
			$this->fixture->find($uid)->getCategories()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getCategories() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventDateWithOneCategoryReturnsListOfCategories() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $category->getUid(), 'categories'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCategories()->first() instanceof
				tx_seminars_Model_Category
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesForEventDateWithOneCategoryReturnsOneCategory() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $category->getUid(), 'categories'
		);

		$this->assertEquals(
			$category->getUid(),
			$this->fixture->find($uid)->getCategories()->getUids()
		);
	}


	////////////////////////////////////
	// Tests regarding getEventType().
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getEventTypeForSingleEventWithoutEventTypeReturnsNull() {
		$this->assertNull(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForSingleEventWithEventTypeReturnsEventTypeInstance() {
		$eventType = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_EventType')
			->getLoadedTestingModel(array());

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
					'event_type' => $eventType->getUid(),
				)
			)->getEventType() instanceof tx_seminars_Model_EventType
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForEventTopicWithoutEventTypeReturnsNull() {
		$this->assertNull(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForEventTopicWithEventTypeReturnsEventTypeInstance() {
		$eventType = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_EventType')
			->getLoadedTestingModel(array());

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
					'event_type' => $eventType->getUid(),
				)
			)->getEventType() instanceof tx_seminars_Model_EventType
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForEventDateWithoutEventTypeReturnsNull() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());

		$this->assertNull(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topic,
				)
			)->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForEventDateWithEventTypeReturnsEventTypeInstance() {
		$eventType = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_EventType')
			->getLoadedTestingModel(array());
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('event_type' => $eventType->getUid()));

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topic->getUid(),
				)
			)->getEventType() instanceof tx_seminars_Model_EventType
		);
	}


	////////////////////////////////////
	// Tests regarding getTimeSlots().
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getTimeSlotsReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getTimeSlots()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTimeSlotsWithOneTimeSlotReturnsListOfTimeSlots() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$timeSlotUid = $this->testingFramework->createRecord(
			'tx_seminars_timeslots', array('seminar' => $uid)
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $uid, array('timeslots' => $timeSlotUid)
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getTimeSlots()->first() instanceof
				tx_seminars_Model_TimeSlot
		);
	}

	/**
	 * @test
	 */
	public function getTimeSlotsWithOneTimeSlotReturnsOneTimeSlot() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$timeSlotUid = $this->testingFramework->createRecord(
			'tx_seminars_timeslots', array('seminar' => $uid)
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $uid, array('timeslots' => $timeSlotUid)
		);

		$this->assertEquals(
			$timeSlotUid,
			$this->fixture->find($uid)->getTimeSlots()->getUids()
		);
	}


	/////////////////////////////////
	// Tests regarding getPlaces().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getPlacesReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getPlaces()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithOnePlaceReturnsListOfPlaces() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$place = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Place')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $place->getUid(), 'place'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getPlaces()->first() instanceof
				tx_seminars_Model_Place
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithOnePlaceReturnsOnePlace() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$place = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Place')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $place->getUid(), 'place'
		);

		$this->assertEquals(
			$place->getUid(),
			$this->fixture->find($uid)->getPlaces()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getLodgings().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getLodgingsReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getLodgings()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsListOfLodgings() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$lodging = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Lodging')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $lodging->getUid(), 'lodgings'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getLodgings()->first() instanceof
				tx_seminars_Model_Lodging
		);
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsOneLodging() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$lodging = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Lodging')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $lodging->getUid(), 'lodgings'
		);

		$this->assertEquals(
			$lodging->getUid(),
			$this->fixture->find($uid)->getLodgings()->getUids()
		);
	}


	////////////////////////////////
	// Tests regarding getFoods().
	////////////////////////////////

	/**
	 * @test
	 */
	public function getFoodsReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getFoods()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsListOfFoods() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$food = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Food')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $food->getUid(), 'foods'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getFoods()->first() instanceof
				tx_seminars_Model_Food
		);
	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsOneFood() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$food = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Food')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $food->getUid(), 'foods'
		);

		$this->assertEquals(
			$food->getUid(),
			$this->fixture->find($uid)->getFoods()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getSpeakers().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getSpeakersReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getSpeakers()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithOneSpeakerReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'speakers'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getSpeakers()->first()
				instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithOneSpeakerReturnsOneSpeaker() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'speakers'
		);

		$this->assertEquals(
			$speaker->getUid(),
			$this->fixture->find($uid)->getSpeakers()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getPartners().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getPartnersReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getPartners()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPartnersWithOnePartnerReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'partners'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getPartners()->first()
				instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getPartnersWithOnePartnerReturnsOnePartner() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'partners'
		);

		$this->assertEquals(
			$speaker->getUid(),
			$this->fixture->find($uid)->getPartners()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getTutors().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getTutorsReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getTutors()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTutorsWithOneTutorReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'tutors'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getTutors()->first()
				instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getTutorsWithOneTutorReturnsOneTutor() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'tutors'
		);

		$this->assertEquals(
			$speaker->getUid(),
			$this->fixture->find($uid)->getTutors()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getLeaders().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getLeadersReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getLeaders()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getLeadersWithOneLeaderReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'leaders'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getLeaders()->first()
				instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getLeadersWithOneLeaderReturnsOneLeader() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'leaders'
		);

		$this->assertEquals(
			$speaker->getUid(),
			$this->fixture->find($uid)->getLeaders()->getUids()
		);
	}


	/////////////////////////////////////////
	// Tests regarding getPaymentMethods().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentMethodsForSingleEventReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getPaymentMethods() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsListOfPaymentMethods() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'payment_methods' => $paymentMethod->getUid(),
			)
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getPaymentMethods()->first() instanceof
				tx_seminars_Model_PaymentMethod
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForSingleEventWithOnePaymentMethodReturnsOnePaymentMethod() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'payment_methods' => $paymentMethod->getUid(),
			)
		);

		$this->assertEquals(
			$paymentMethod->getUid(),
			$this->fixture->find($uid)->getPaymentMethods()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventTopicReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getPaymentMethods() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsListOfPaymentMethods() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'payment_methods' => $paymentMethod->getUid(),
			)
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getPaymentMethods()->first() instanceof
				tx_seminars_Model_PaymentMethod
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventTopicWithOnePaymentMethodReturnsOnePaymentMethod() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'payment_methods' => $paymentMethod->getUid(),
			)
		);

		$this->assertEquals(
			$paymentMethod->getUid(),
			$this->fixture->find($uid)->getPaymentMethods()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getPaymentMethods() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsListOfPaymentMethods() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('payment_methods' => $paymentMethod->getUid())
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getPaymentMethods()->first() instanceof
				tx_seminars_Model_PaymentMethod
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsForEventDateWithOnePaymentMethodReturnsOnePaymentMethod() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('payment_methods' => $paymentMethod->getUid())
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);

		$this->assertEquals(
			$paymentMethod->getUid(),
			$this->fixture->find($uid)->getPaymentMethods()->getUids()
		);
	}


	/////////////////////////////////////
	// Tests regarding getOrganizers().
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizersReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getOrganizers()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithOneOrganizerReturnsListOfOrganizers() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('organizers' => 1)
		);
		$organizer = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer')
			->getNewGhost();
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $uid, $organizer->getUid()
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getOrganizers()->first() instanceof
				tx_seminars_Model_Organizer
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithOneOrganizerReturnsOneOrganizer() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('organizers' => 1)
		);
		$organizer = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer')
			->getNewGhost();
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $uid, $organizer->getUid()
		);

		$this->assertEquals(
			$organizer->getUid(),
			$this->fixture->find($uid)->getOrganizers()->getUids()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding getOrganizingPartners().
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizingPartnersReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getOrganizingPartners()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getOrganizingPartnersWithOneOrganizingReturnsListOfOrganizers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$organizer = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $organizer->getUid(), 'organizing_partners'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getOrganizingPartners()->first()
				instanceof tx_seminars_Model_Organizer
		);
	}

	/**
	 * @test
	 */
	public function getOrganizingPartnersWithOneOrganizingPartnersReturnsOneOrganizingPartner() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$organizer = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $organizer->getUid(), 'organizing_partners'
		);

		$this->assertEquals(
			$organizer->getUid(),
			$this->fixture->find($uid)->getOrganizingPartners()->getUids()
		);
	}


	///////////////////////////////////////
	// Tests regarding getTargetGroups().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getTargetGroupsForSingleEventReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getTargetGroups() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsListOfTargetGroups() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getTargetGroups()->first() instanceof
				tx_seminars_Model_TargetGroup
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForSingleEventWithOneTargetGroupReturnsOneTargetGroup() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertEquals(
			$targetGroup->getUid(),
			$this->fixture->find($uid)->getTargetGroups()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventTopicReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getTargetGroups() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsListOfTargetGroups() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getTargetGroups()->first() instanceof
				tx_seminars_Model_TargetGroup
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventTopicWithOneTargetGroupReturnsOneTargetGroup() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertEquals(
			$targetGroup->getUid(),
			$this->fixture->find($uid)->getTargetGroups()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getTargetGroups() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventDateWithOneTargetGroupReturnsListOfTargetGroups() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getTargetGroups()->first() instanceof
				tx_seminars_Model_TargetGroup
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsForEventDateWithOneTargetGroupReturnsOneTargetGroup() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$targetGroup = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_TargetGroup')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $targetGroup->getUid(), 'target_groups'
		);

		$this->assertEquals(
			$targetGroup->getUid(),
			$this->fixture->find($uid)->getTargetGroups()->getUids()
		);
	}


	////////////////////////////////
	// Tests regarding getOwner().
	////////////////////////////////

	/**
	 * @test
	 */
	public function getOwnerWithoutOwnerReturnsNull() {
		$this->assertNull(
			$this->fixture->getLoadedTestingModel(array())->getOwner()
		);
	}

	/**
	 * @test
	 */
	public function getOwnerWithOwnerReturnsOwnerInstance() {
		$frontEndUser = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_FrontEndUser')->getLoadedTestingModel(array());

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('owner_feuser' => $frontEndUser->getUid())
			)->getOwner() instanceof
				tx_oelib_Model_FrontEndUser
		);
	}


	////////////////////////////////////////
	// Tests regarding getEventManagers().
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEventManagersReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(array())->getEventManagers()
			instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getEventManagersWithOneEventManagerReturnsListOfFrontEndUsers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$frontEndUser = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $frontEndUser->getUid(), 'vips'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getEventManagers()->first() instanceof
				tx_oelib_Model_FrontEndUser
		);
	}

	/**
	 * @test
	 */
	public function getEventManagersWithOneEventManagerReturnsOneEventManager() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$frontEndUser = tx_oelib_MapperRegistry::
			get('tx_oelib_Mapper_FrontEndUser')->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $frontEndUser->getUid(), 'vips'
		);

		$this->assertEquals(
			$frontEndUser->getUid(),
			$this->fixture->find($uid)->getEventManagers()->getUids()
		);
	}


	/////////////////////////////////////
	// Tests regarding getCheckboxes().
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getCheckboxesForSingleEventReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getCheckboxes() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForSingleEventWithOneCheckboxReturnsListOfCheckboxes() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCheckboxes()->first() instanceof
				tx_seminars_Model_Checkbox
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForSingleEventWithOneCheckboxReturnsOneCheckbox() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertEquals(
			$checkbox->getUid(),
			$this->fixture->find($uid)->getCheckboxes()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventTopicReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getCheckboxes() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventTopicWithOneCheckboxReturnsListOfCheckboxes() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCheckboxes()->first() instanceof
				tx_seminars_Model_Checkbox
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventTopicWithOneCheckboxReturnsOneCheckbox() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertEquals(
			$checkbox->getUid(),
			$this->fixture->find($uid)->getCheckboxes()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getCheckboxes() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventDateWithOneCheckboxReturnsListOfCheckboxes() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getCheckboxes()->first() instanceof
				tx_seminars_Model_Checkbox
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesForEventDateWithOneCheckboxReturnsOneCheckbox() {
		$topicUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$checkbox = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Checkbox')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $checkbox->getUid(), 'checkboxes'
		);

		$this->assertEquals(
			$checkbox->getUid(),
			$this->fixture->find($uid)->getCheckboxes()->getUids()
		);
	}


	///////////////////////////////////////
	// Tests regarding getRequirements().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getRequirementsForSingleReturnsEmptyList() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getRequirements()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventTopicReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getRequirements() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventTopicWithOneRequirementReturnsListOfEvents() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $event->getUid(), 'requirements'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getRequirements()->first() instanceof
				tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventTopicWithOneRequirementsReturnsOneRequirement() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $event->getUid(), 'requirements'
		);

		$this->assertEquals(
			$event->getUid(),
			$this->fixture->find($uid)->getRequirements()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getRequirements() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventDateWithOneRequirementReturnsListOfEvents() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $event->getUid(), 'requirements'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getRequirements()->first() instanceof
				tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForEventDateWithOneRequirementsReturnsOneRequirement() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $topicUid, $event->getUid(), 'requirements'
		);

		$this->assertEquals(
			$event->getUid(),
			$this->fixture->find($uid)->getRequirements()->getUids()
		);
	}


	///////////////////////////////////////
	// Tests regarding getDependencies().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getDependenciesForSingleEventReturnsEmptyList() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
			)->getDependencies()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventTopicReturnsListInstance() {
		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
			)->getDependencies() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventTopicWithOneDependencyReturnsListOfEvents() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $uid, 'dependencies'
		);

		$this->assertTrue(
			$this->fixture->find($relatedUid)->getDependencies()->first() instanceof
				tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventTopicWithOneDependencyReturnsOneDependency() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $uid, 'dependencies'
		);

		$this->assertEquals(
			$uid,
			$this->fixture->find($relatedUid)->getDependencies()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventDateReturnsListInstance() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)->getDependencies() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventDateWithOneDependencyReturnsListOfEvents() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $relatedUid,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $topicUid, 'dependencies'
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getDependencies()->first() instanceof
				tx_seminars_Model_Event
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForEventDateWithOneDependencyReturnsOneDependency() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $relatedUid,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $topicUid, 'dependencies'
		);

		$this->assertEquals(
			$topicUid,
			$this->fixture->find($uid)->getDependencies()->getUids()
		);
	}
}
?>