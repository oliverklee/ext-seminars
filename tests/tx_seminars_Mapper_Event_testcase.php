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
	public function getCategoriesReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getCategories() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesWithOneCategoryReturnsListOfCategories() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
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
	public function getCategoriesWithOneCategoryReturnsOneCategory() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
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


	////////////////////////////////////
	// Tests regarding getEventType().
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getEventTypeWithoutEventTypeReturnsNull() {
		$this->assertNull(
			$this->fixture->getLoadedTestingModel(array())->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeWithEventTypeReturnsEventTypeInstance() {
		$eventType = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_EventType')
			->getLoadedTestingModel(array());

		$this->assertTrue(
			$this->fixture->getLoadedTestingModel(
				array('event_type' => $eventType->getUid())
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getTimeSlots() instanceof tx_oelib_List
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getPlaces() instanceof tx_oelib_List
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getLodgings() instanceof tx_oelib_List
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getFoods() instanceof tx_oelib_List
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getSpeakers() instanceof tx_oelib_List
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getPartners() instanceof tx_oelib_List
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getTutors() instanceof tx_oelib_List
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getLeaders() instanceof tx_oelib_List
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
	public function getPaymentMethodsReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getPaymentMethods() instanceof
				tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithOnePaymentMethodReturnsListOfPaymentMethods() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('payment_methods' => $paymentMethod->getUid())
		);

		$this->assertTrue(
			$this->fixture->find($uid)->getPaymentMethods()->first() instanceof
				tx_seminars_Model_PaymentMethod
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithOnePaymentMethodReturnsOnePaymentMethod() {
		$paymentMethod = tx_oelib_MapperRegistry::
			get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('payment_methods' => $paymentMethod->getUid())
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getOrganizers() instanceof tx_oelib_List
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
			$this->fixture->find($uid)->getOrganizingPartners() instanceof
				tx_oelib_List
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
	public function getTargetGroupsReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getTargetGroups() instanceof
				tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsWithOneTargetGroupReturnsListOfTargetGroups() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
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
	public function getTargetGroupsWithOneTargetGroupReturnsOneTargetGroup() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
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
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getEventManagers() instanceof
				tx_oelib_List
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
	public function getCheckboxesReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getCheckboxes() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getCheckboxesWithOneCheckboxReturnsListOfCheckboxes() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
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
	public function getCheckboxesWithOneCheckboxReturnsOneCheckbox() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
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


	///////////////////////////////////////
	// Tests regarding getRequirements().
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getRequirementsReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getRequirements() instanceof
				tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsWithOneRequirementReturnsListOfEvents() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
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
	public function getRequirementsWithOneRequirementsReturnsOneRequirement() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$event = $this->fixture->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $event->getUid(), 'requirements'
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
	public function getDependenciesReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$this->assertTrue(
			$this->fixture->find($uid)->getDependencies() instanceof
				tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesWithOneDependencyReturnsListOfEvents() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
	public function getDependenciesWithOneDependencyReturnsOneDependency() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$relatedUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $relatedUid, $uid, 'dependencies'
		);

		$this->assertEquals(
			$uid,
			$this->fixture->find($relatedUid)->getDependencies()->getUids()
		);
	}
}
?>