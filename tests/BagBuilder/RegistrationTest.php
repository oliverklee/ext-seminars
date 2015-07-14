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
class tx_seminars_BagBuilder_RegistrationTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BagBuilder_Registration
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_BagBuilder_Registration();
		$this->fixture->setTestMode();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBagBuilderBuildsARegistrationBag() {
		self::assertTrue(
			$this->fixture->build() instanceof tx_seminars_Bag_Registration
		);
	}

	public function testBuildReturnsBagWhichIsSortedAscendingByCrDate() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Title 2', 'crdate' => ($GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY))
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Title 1', 'crdate' => $GLOBALS['SIM_EXEC_TIME'])
		);

		$registrationBag = $this->fixture->build();
		self::assertEquals(
			2,
			$registrationBag->count()
		);

		self::assertEquals(
			'Title 1',
			$registrationBag->current()->getTitle()
		);
		self::assertEquals(
			'Title 2',
			$registrationBag->next()->getTitle()
		);
	}

	public function testBuildWithoutLimitReturnsBagWithAllRegistrations() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 1', 'seminar' => $eventUid1)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 2', 'seminar' => $eventUid1)
		);
		$registrationBag = $this->fixture->build();

		self::assertEquals(
			2,
			$registrationBag->count()
		);
	}


	/////////////////////////////
	// Tests for limitToEvent()
	/////////////////////////////

	public function testLimitToEventWithNegativeEventUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $eventUid must be > 0.'
		);

		$this->fixture->limitToEvent(-1);
	}

	public function testLimitToEventWithZeroEventUidThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $eventUid must be > 0.'
		);

		$this->fixture->limitToEvent(0);
	}

	public function testLimitToEventWithValidEventUidFindsRegistrationOfEvent() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 1', 'seminar' => $eventUid1)
		);
		$this->fixture->limitToEvent($eventUid1);
		$registrationBag = $this->fixture->build();

		self::assertEquals(
			'Attendance 1',
			$registrationBag->current()->getTitle()
		);
	}

	public function testLimitToEventWithValidEventUidIgnoresRegistrationOfOtherEvent() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 2', 'seminar' => $eventUid2)
		);
		$this->fixture->limitToEvent($eventUid1);
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->isEmpty()
		);
	}


	////////////////////////////
	// Tests for limitToPaid()
	////////////////////////////

	public function testLimitToPaidFindsPaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 2', 'datepaid' => $GLOBALS['SIM_EXEC_TIME'])
		);
		$this->fixture->limitToPaid();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->current()->isPaid()
		);
	}

	public function testLimitToPaidIgnoresUnpaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('title' => 'Attendance 1', 'datepaid' => 0)
		);
		$this->fixture->limitToPaid();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->isEmpty()
		);
	}


	//////////////////////////////
	// Tests for limitToUnpaid()
	//////////////////////////////

	public function testLimitToUnpaidFindsUnpaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => 0)
		);
		$this->fixture->limitToUnpaid();
		$registrationBag = $this->fixture->build();

		self::assertFalse(
			$registrationBag->current()->isPaid()
		);
	}

	public function testLimitToUnpaidIgnoresPaidRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => $GLOBALS['SIM_EXEC_TIME'])
		);
		$this->fixture->limitToUnpaid();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->isEmpty()
		);
	}


	////////////////////////////////////////
	// Tests for removePaymentLimitation()
	////////////////////////////////////////

	public function testRemovePaymentLimitationRemovesPaidLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => 0)
		);
		$this->fixture->limitToPaid();
		$this->fixture->removePaymentLimitation();
		$registrationBag = $this->fixture->build();

		self::assertFalse(
			$registrationBag->current()->isPaid()
		);
	}

	public function testRemovePaymentLimitationRemovesUnpaidLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('datepaid' => $GLOBALS['SIM_EXEC_TIME'])
		);
		$this->fixture->limitToUnpaid();
		$this->fixture->removePaymentLimitation();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->current()->isPaid()
		);
	}


	///////////////////////////////
	// Tests for limitToOnQueue()
	///////////////////////////////

	public function testLimitToOnQueueFindsRegistrationOnQueue() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 1)
		);
		$this->fixture->limitToOnQueue();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}

	public function testLimitToOnQueueIgnoresRegularRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 0)
		);
		$this->fixture->limitToOnQueue();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->isEmpty()
		);
	}


	///////////////////////////////
	// Tests for limitToRegular()
	///////////////////////////////

	public function testLimitToRegularFindsRegularRegistration() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 0)
		);
		$this->fixture->limitToRegular();
		$registrationBag = $this->fixture->build();

		self::assertFalse(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}

	public function testLimitToRegularIgnoresRegistrationOnQueue() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 1)
		);
		$this->fixture->limitToRegular();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->isEmpty()
		);
	}


	//////////////////////////////////////
	// Tests for removeQueueLimitation()
	//////////////////////////////////////

	public function testRemoveQueueLimitationRemovesOnQueueLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 0)
		);
		$this->fixture->limitToOnQueue();
		$this->fixture->removeQueueLimitation();
		$registrationBag = $this->fixture->build();

		self::assertFalse(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}

	public function testRemoveQueueLimitationRemovesRegularLimit() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('registration_queue' => 1)
		);
		$this->fixture->limitToRegular();
		$this->fixture->removeQueueLimitation();
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}


	///////////////////////////////////
	// Tests for limitToSeatsAtMost()
	///////////////////////////////////

	public function testLimitToSeatsAtMostWithNegativeVacanciesThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $seats must be >= 0.'
		);

		$this->fixture->limitToSeatsAtMost(-1);
	}

	public function testLimitToSeatsAtMostFindsRegistrationWithEqualSeats() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(2);
		$registrationBag = $this->fixture->build();

		self::assertEquals(
			2,
			$registrationBag->current()->getSeats()
		);
	}

	public function testLimitToSeatsAtMostFindsRegistrationWithLessSeats() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 1)
		);
		$this->fixture->limitToSeatsAtMost(2);
		$registrationBag = $this->fixture->build();

		self::assertEquals(
			1,
			$registrationBag->current()->getSeats()
		);
	}

	public function testLimitToSeatsAtMostIgnoresRegistrationWithMoreSeats() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(1);
		$registrationBag = $this->fixture->build();

		self::assertTrue(
			$registrationBag->isEmpty()
		);
	}

	public function testLimitToSeatsAtMostWithZeroSeatsFindsAllRegistrations() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(1);
		$this->fixture->limitToSeatsAtMost(0);
		$registrationBag = $this->fixture->build();

		self::assertFalse(
			$registrationBag->isEmpty()
		);
	}


	//////////////////////////////
	// Tests for limitToAttendee
	//////////////////////////////

	/**
	 * @test
	 */
	public function limitToAttendeeWithUserFindsRegistrationsWithAttendee() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'user' => $feUserUid)
		);

		/** @var tx_seminars_Model_FrontEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')->find($feUserUid);
		$this->fixture->limitToAttendee($user);
		$bag = $this->fixture->build();

		self::assertEquals(
			$registrationUid,
			$bag->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function limitToAttendeeWithUserFindsRegistrationsWithUserAsAdditionalRegisteredPerson() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'additional_persons' => 1)
		);
		$feUserUid = $this->testingFramework->createFrontEndUser(
			'', array('tx_seminars_registration' => $registrationUid)
		);

		/** @var tx_seminars_Model_FrontEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')->find($feUserUid);
		$this->fixture->limitToAttendee($user);
		$bag = $this->fixture->build();

		self::assertEquals(
			$registrationUid,
			$bag->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function limitToAttendeeWithUserIgnoresRegistrationsWithoutAttendee() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord('tx_seminars_seminars');

		/** @var tx_seminars_Model_FrontEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')->find($feUserUid);
		$this->fixture->limitToAttendee($user);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToAttendeeWithUserIgnoresRegistrationsWithOtherAttendee() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$feUserUid = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$feUserUid2 = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'user' => $feUserUid2)
		);

		/** @var tx_seminars_Model_FrontEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')->find($feUserUid);
		$this->fixture->limitToAttendee($user);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function limitToAttendeeWithNullFindsRegistrationsWithOtherAttendee() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$feUserUid = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$feUserUid2 = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $eventUid, 'user' => $feUserUid2)
		);

		/** @var tx_seminars_Model_FrontEndUser $user */
		$user = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')->find($feUserUid);
		$this->fixture->limitToAttendee($user);
		$this->fixture->limitToAttendee(NULL);
		$bag = $this->fixture->build();

		self::assertEquals(
			$registrationUid,
			$bag->current()->getUid()
		);
	}


	//////////////////////////////////////
	// Tests for setOrderByEventColumn()
	//////////////////////////////////////

	public function testSetOrderByEventColumnCanSortAscendingByEventTitle() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 1')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 2')
		);
		$registrationUid1 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid1)
		);
		$registrationUid2 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid2)
		);

		$this->fixture->setOrderByEventColumn(
			'tx_seminars_seminars.title ASC'
		);
		$bag = $this->fixture->build();

		self::assertEquals(
			$bag->current()->getUid(),
			$registrationUid1
		);
		self::assertEquals(
			$bag->next()->getUid(),
			$registrationUid2
		);
	}

	public function testSetOrderByEventColumnCanSortDescendingByEventTitle() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 1')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'test title 2')
		);
		$registrationUid1 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid1)
		);
		$registrationUid2 = $this->testingFramework->createRecord(
			'tx_seminars_attendances', array('seminar' => $eventUid2)
		);

		$this->fixture->setOrderByEventColumn(
			'tx_seminars_seminars.title DESC'
		);
		$bag = $this->fixture->build();

		self::assertEquals(
			$bag->current()->getUid(),
			$registrationUid2
		);
		self::assertEquals(
			$bag->next()->getUid(),
			$registrationUid1
		);
	}


	//////////////////////////////////////////
	// Tests concerning limitToExistingUsers
	//////////////////////////////////////////

	public function testLimitToExistingUsersFindsRegistrationWithExistingUser() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('user' => $this->testingFramework->createFrontEndUser())
		);
		$this->fixture->limitToExistingUsers();
		$bag = $this->fixture->build();

		self::assertFalse(
			$bag->isEmpty()
		);
	}

	public function testLimitToExistingUsersDoesNotFindRegistrationWithDeletedUser() {
		$feUserUid = $this->testingFramework->createFrontEndUser();

		$this->testingFramework->changeRecord(
			'fe_users', $feUserUid, array('deleted' => 1)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('user' => $feUserUid)
		);
		$this->fixture->limitToExistingUsers();
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}
}