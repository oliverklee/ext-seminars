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
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Mapper_EventTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	/**
	 * @var Tx_Seminars_Mapper_Event
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
		tx_oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

		$this->fixture = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////
	// Tests regarding find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidReturnsEventInstance() {
		self::assertTrue(
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			'Big event',
			$model->getTitle()
		);
	}


	////////////////////////////////////
	// Tests regarding getTimeSlots().
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getTimeSlotsReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getTimeSlots()
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getTimeSlots()->first() instanceof tx_seminars_Model_TimeSlot
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

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$timeSlotUid,
			$model->getTimeSlots()->getUids()
		);
	}


	/////////////////////////////////
	// Tests regarding getPlaces().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getPlacesReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getPlaces()
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithOnePlaceReturnsListOfPlaces() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$place = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Place::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $place->getUid(), 'place'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertInstanceOf(Tx_Seminars_Model_Place::class, $model->getPlaces()->first());
	}

	/**
	 * @test
	 */
	public function getPlacesWithOnePlaceReturnsOnePlace() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$place = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Place::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $place->getUid(), 'place'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$place->getUid(),
			$model->getPlaces()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getLodgings().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getLodgingsReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getLodgings()
		);
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsListOfLodgings() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$lodging = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Lodging::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $lodging->getUid(), 'lodgings'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertInstanceOf(Tx_Seminars_Model_Lodging::class, $model->getLodgings()->first());
	}

	/**
	 * @test
	 */
	public function getLodgingsWithOneLodgingReturnsOneLodging() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$lodging = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Lodging::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $lodging->getUid(), 'lodgings'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$lodging->getUid(),
			$model->getLodgings()->getUids()
		);
	}


	////////////////////////////////
	// Tests regarding getFoods().
	////////////////////////////////

	/**
	 * @test
	 */
	public function getFoodsReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getFoods()
		);
	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsListOfFoods() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$food = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Food::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $food->getUid(), 'foods'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertInstanceOf(Tx_Seminars_Model_Food::class, $model->getFoods()->first());
	}

	/**
	 * @test
	 */
	public function getFoodsWithOneFoodReturnsOneFood() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$food = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Food::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $food->getUid(), 'foods'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$food->getUid(),
			$model->getFoods()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getSpeakers().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getSpeakersReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getSpeakers()
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithOneSpeakerReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'speakers'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getSpeakers()->first() instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithOneSpeakerReturnsOneSpeaker() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'speakers'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$speaker->getUid(),
			$model->getSpeakers()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getPartners().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getPartnersReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getPartners()
		);
	}

	/**
	 * @test
	 */
	public function getPartnersWithOnePartnerReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'partners'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getPartners()->first() instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getPartnersWithOnePartnerReturnsOnePartner() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'partners'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$speaker->getUid(),
			$model->getPartners()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getTutors().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getTutorsReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getTutors()
		);
	}

	/**
	 * @test
	 */
	public function getTutorsWithOneTutorReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'tutors'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getTutors()->first() instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getTutorsWithOneTutorReturnsOneTutor() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'tutors'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$speaker->getUid(),
			$model->getTutors()->getUids()
		);
	}


	///////////////////////////////////
	// Tests regarding getLeaders().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getLeadersReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getLeaders()
		);
	}

	/**
	 * @test
	 */
	public function getLeadersWithOneLeaderReturnsListOfSpeakers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'leaders'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getLeaders()->first() instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getLeadersWithOneLeaderReturnsOneLeader() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$speaker = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Speaker::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $speaker->getUid(), 'leaders'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$speaker->getUid(),
			$model->getLeaders()->getUids()
		);
	}


	/////////////////////////////////////
	// Tests regarding getOrganizers().
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizersReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getOrganizers()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithOneOrganizerReturnsListOfOrganizers() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('organizers' => 1)
		);
		$organizer = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Organizer::class)
			->getNewGhost();
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $uid, $organizer->getUid()
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getOrganizers()->first() instanceof tx_seminars_Model_Organizer
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithOneOrganizerReturnsOneOrganizer() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('organizers' => 1)
		);
		$organizer = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Organizer::class)
			->getNewGhost();
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $uid, $organizer->getUid()
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$organizer->getUid(),
			$model->getOrganizers()->getUids()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding getOrganizingPartners().
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizingPartnersReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getOrganizingPartners()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizingPartnersWithOneOrganizingReturnsListOfOrganizers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$organizer = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Organizer::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $organizer->getUid(), 'organizing_partners'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getOrganizingPartners()->first() instanceof tx_seminars_Model_Organizer
		);
	}

	/**
	 * @test
	 */
	public function getOrganizingPartnersWithOneOrganizingPartnersReturnsOneOrganizingPartner() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$organizer = tx_oelib_MapperRegistry::get(Tx_Seminars_Mapper_Organizer::class)
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $organizer->getUid(), 'organizing_partners'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$organizer->getUid(),
			$model->getOrganizingPartners()->getUids()
		);
	}



	////////////////////////////////
	// Tests regarding getOwner().
	////////////////////////////////

	/**
	 * @test
	 */
	public function getOwnerWithoutOwnerReturnsNull() {
		self::assertNull(
			$this->fixture->getLoadedTestingModel(array())->getOwner()
		);
	}

	/**
	 * @test
	 */
	public function getOwnerWithOwnerReturnsOwnerInstance() {
		$frontEndUser = tx_oelib_MapperRegistry::
			get(Tx_Oelib_Mapper_FrontEndUser::class)->getLoadedTestingModel(array());

		self::assertInstanceOf(
			Tx_Oelib_Model_FrontEndUser::class,
			$this->fixture->getLoadedTestingModel(array('owner_feuser' => $frontEndUser->getUid()))->getOwner()
		);
	}


	////////////////////////////////////////
	// Tests regarding getEventManagers().
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEventManagersReturnsListInstance() {
		self::assertInstanceOf(
			Tx_Oelib_List::class,
			$this->fixture->getLoadedTestingModel(array())->getEventManagers()
		);
	}

	/**
	 * @test
	 */
	public function getEventManagersWithOneEventManagerReturnsListOfFrontEndUsers() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$frontEndUser = tx_oelib_MapperRegistry::
			get(Tx_Oelib_Mapper_FrontEndUser::class)->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $frontEndUser->getUid(), 'vips'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertInstanceOf(Tx_Oelib_Model_FrontEndUser::class, $model->getEventManagers()->first());
	}

	/**
	 * @test
	 */
	public function getEventManagersWithOneEventManagerReturnsOneEventManager() {
		$uid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$frontEndUser = tx_oelib_MapperRegistry::
			get(Tx_Oelib_Mapper_FrontEndUser::class)->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $uid, $frontEndUser->getUid(), 'vips'
		);

		/** @var tx_seminars_Model_Event $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			$frontEndUser->getUid(),
			$model->getEventManagers()->getUids()
		);
	}


	///////////////////////////////////////////
	// Tests concerning findByPublicationHash
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function findByPublicationHashForEmptyPublicationHashGivenThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The given publication hash was empty.'
		);

		$this->fixture->findByPublicationHash('');
	}

	/**
	 * @test
	 */
	public function findByPublicationForEventWithProvidedPublicationHashReturnsThisEvent() {
		$publicationHash = 'blubb';

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('publication_hash' => $publicationHash)
		);

		self::assertEquals(
			$eventUid,
			$this->fixture->findByPublicationHash($publicationHash)->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findByPublicationForNoEventWithProvidedPublicationHashReturnsNull() {
		$this->testingFramework->createRecord('tx_seminars_seminars');

		self::assertNull(
			$this->fixture->findByPublicationHash('foo')
		);
	}

	/**
	 * @test
	 */
	public function findByPublicationForEventWithProvidedPublicationHashReturnsEventModel() {
		$publicationHash = 'blubb';

		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('publication_hash' => $publicationHash)
		);

		self::assertTrue(
			$this->fixture->findByPublicationHash($publicationHash)
				instanceof tx_seminars_Model_Event
		);
	}


	///////////////////////////////////////
	// Tests concerning the registrations
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegistrationsWithOneRegistrationReturnsOneRegistration() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('registrations' => 1)
		);
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid)
		);

		/** @var tx_seminars_Model_Event $event */
		$event = $this->fixture->find($eventUid);
		self::assertEquals(
			$registrationUid,
			$event->getRegistrations()->getUids()
		);
	}


	////////////////////////////////////////
	// Tests concerning findAllByBeginDate
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function findAllByBeginDateForPositiveSameMinimumAndMaximumNotThrowsException() {
		$this->fixture->findAllByBeginDate(42, 42);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateForZeroMinimumAndPositiveMaximumNotThrowsException() {
		$this->fixture->findAllByBeginDate(0, 1);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findAllByBeginDateForZeroMinimumAndZeroMaximumThrowsException() {
		$this->fixture->findAllByBeginDate(0, 0);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateForMinimumSmallerThanMaximumNotThrowsException() {
		$this->fixture->findAllByBeginDate(1, 2);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findAllByBeginDateForNegativeMinimumSmallerThanMaximumThrowsException() {
		$this->fixture->findAllByBeginDate(-1, 1);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findAllByBeginDateForMinimumGreaterThanMaximumThrowsException() {
		$this->fixture->findAllByBeginDate(2, 1);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateNotFindsEventWithBeginDateSmallerThanMinimum() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 41)
		);

		self::assertTrue(
			$this->fixture->findAllByBeginDate(42, 91)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateFindsEventWithBeginDateEqualToMinimum() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);

		self::assertTrue(
			$this->fixture->findAllByBeginDate(42, 91)->hasUid($uid)
		);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateFindsEventWithBeginDateBetweenMinimumAndMaximum() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 2)
		);

		self::assertTrue(
			$this->fixture->findAllByBeginDate(1, 3)->hasUid($uid)
		);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateFindsEventWithBeginDateEqualToMaximum() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 91)
		);

		self::assertTrue(
			$this->fixture->findAllByBeginDate(42, 91)->hasUid($uid)
		);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateNotFindsEventWithBeginDateGreaterThanMaximum() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 92)
		);

		self::assertTrue(
			$this->fixture->findAllByBeginDate(42, 91)->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateCanFindEventWithZeroBeginDate() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 0)
		);

		self::assertTrue(
			$this->fixture->findAllByBeginDate(0, 1)->hasUid($uid)
		);
	}

	/**
	 * @test
	 */
	public function findAllByBeginDateCanFindTwoEvents() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 42)
		);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => 43)
		);

		self::assertEquals(
			2,
			$this->fixture->findAllByBeginDate(42, 91)->count()
		);
	}


	//////////////////////////////////////
	// Tests concerning findNextUpcoming
	//////////////////////////////////////

	/**
	 * @test
	 * @expectedException Tx_Oelib_Exception_NotFound
	 */
	public function findNextUpcomingWithNoEventsThrowsEmptyQueryResultException() {
		$this->fixture->findNextUpcoming();
	}

	/**
	 * @test
	 * @expectedException Tx_Oelib_Exception_NotFound
	 */
	public function findNextUpcomingWithPastEventThrowsEmptyQueryResultException() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_ACCESS_TIME'] - 1000)
		);

		$this->fixture->findNextUpcoming();
	}

	/**
	 * @test
	 */
	public function findNextUpcomingWithUpcomingEventReturnsModelOfUpcomingEvent() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000)
		);

		self::assertSame(
			$uid,
			$this->fixture->findNextUpcoming()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function findNextUpcomingWithTwoUpcomingEventsReturnsOnlyModelOfNextUpcomingEvent() {
		$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array('begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 2000)
		);
		$uid = $this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array('begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000)
		);

		self::assertSame(
			$uid,
			$this->fixture->findNextUpcoming()->getUid()
		);
	}
}