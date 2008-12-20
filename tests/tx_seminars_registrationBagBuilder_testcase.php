<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the registration bag builder class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registrationBagBuilder_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_pi1
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_registrationBagBuilder();
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
			$this->fixture->build() instanceof tx_seminars_bag
		);
	}

	public function testBagBuilderBuildsARegistrationBag() {
		$this->assertTrue(
			$this->fixture->build() instanceof tx_seminars_registrationbag
		);
	}

	public function testBuildReturnsBagWhichIsSortedAscendingByCrDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Title 2', 'crdate' => (time() + ONE_DAY))
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Title 1', 'crdate' => time())
		);

		$registrationBag = $this->fixture->build();
		$this->assertEquals(
			2,
			$registrationBag->count()
		);

		$this->assertEquals(
			'Title 1',
			$registrationBag->current()->getTitle()
		);
		$this->assertEquals(
			'Title 2',
			$registrationBag->next()->getTitle()
		);
	}

	public function testBuildWithoutLimitReturnsBagWithAllRegistrations() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Attendance 1', 'seminar' => $eventUid1)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Attendance 2', 'seminar' => $eventUid1)
		);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			2,
			$registrationBag->count()
		);
	}


	/////////////////////////////
	// Tests for limitToEvent()
	/////////////////////////////

	public function testLimitToEventWithNegativeEventUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $eventUid must be > 0.'
		);

		$this->fixture->limitToEvent(-1);
	}

	public function testLimitToEventWithZeroEventUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $eventUid must be > 0.'
		);

		$this->fixture->limitToEvent(0);
	}

	public function testLimitToEventWithValidEventUidFindsRegistrationOfEvent() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Attendance 1', 'seminar' => $eventUid1)
		);
		$this->fixture->limitToEvent($eventUid1);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			'Attendance 1',
			$registrationBag->current()->getTitle()
		);
	}

	public function testLimitToEventWithValidEventUidIgnoresRegistrationOfOtherEvent() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Attendance 2', 'seminar' => $eventUid2)
		);
		$this->fixture->limitToEvent($eventUid1);
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);
	}


	////////////////////////////
	// Tests for limitToPaid()
	////////////////////////////

	public function testLimitToPaidFindsPaidRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Attendance 2', 'paid' => 1)
		);
		$this->fixture->limitToPaid();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isPaid()
		);
	}

	public function testLimitToPaidIgnoresUnpaidRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Attendance 1', 'paid' => 0)
		);
		$this->fixture->limitToPaid();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);
	}


	//////////////////////////////
	// Tests for limitToUnpaid()
	//////////////////////////////

	public function testLimitToUnpaidFindsUnpaidRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('paid' => 0)
		);
		$this->fixture->limitToUnpaid();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isPaid()
		);
	}

	public function testLimitToUnpaidIgnoresPaidRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('paid' => 1)
		);
		$this->fixture->limitToUnpaid();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);
	}


	////////////////////////////////////////
	// Tests for removePaymentLimitation()
	////////////////////////////////////////

	public function testRemovePaymentLimitationRemovesPaidLimit() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('paid' => 0)
		);
		$this->fixture->limitToPaid();
		$this->fixture->removePaymentLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isPaid()
		);
	}

	public function testRemovePaymentLimitationRemovesUnpaidLimit() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('paid' => 1)
		);
		$this->fixture->limitToUnpaid();
		$this->fixture->removePaymentLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isPaid()
		);
	}


	///////////////////////////////
	// Tests for limitToOnQueue()
	///////////////////////////////

	public function testLimitToOnQueueFindsRegistrationOnQueue() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 1)
		);
		$this->fixture->limitToOnQueue();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}

	public function testLimitToOnQueueIgnoresRegularRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 0)
		);
		$this->fixture->limitToOnQueue();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);
	}


	///////////////////////////////
	// Tests for limitToRegular()
	///////////////////////////////

	public function testLimitToRegularFindsRegularRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 0)
		);
		$this->fixture->limitToRegular();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}

	public function testLimitToRegularIgnoresRegistrationOnQueue() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 1)
		);
		$this->fixture->limitToRegular();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);
	}


	//////////////////////////////////////
	// Tests for removeQueueLimitation()
	//////////////////////////////////////

	public function testRemoveQueueLimitationRemovesOnQueueLimit() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 0)
		);
		$this->fixture->limitToOnQueue();
		$this->fixture->removeQueueLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}

	public function testRemoveQueueLimitationRemovesRegularLimit() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 1)
		);
		$this->fixture->limitToRegular();
		$this->fixture->removeQueueLimitation();
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->current()->isOnRegistrationQueue()
		);
	}


	///////////////////////////////////
	// Tests for limitToSeatsAtMost()
	///////////////////////////////////

	public function testLimitToSeatsAtMostWithNegativeVacanciesThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $seats must be >= 0.'
		);

		$this->fixture->limitToSeatsAtMost(-1);
	}

	public function testLimitToSeatsAtMostFindsRegistrationWithEqualSeats() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(2);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			2,
			$registrationBag->current()->getSeats()
		);
	}

	public function testLimitToSeatsAtMostFindsRegistrationWithLessSeats() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seats' => 1)
		);
		$this->fixture->limitToSeatsAtMost(2);
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			1,
			$registrationBag->current()->getSeats()
		);
	}

	public function testLimitToSeatsAtMostIgnoresRegistrationWithMoreSeats() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(1);
		$registrationBag = $this->fixture->build();

		$this->assertTrue(
			$registrationBag->isEmpty()
		);
	}

	public function testLimitToSeatsAtMostWithZeroSeatsFindsAllRegistrations() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seats' => 2)
		);
		$this->fixture->limitToSeatsAtMost(1);
		$this->fixture->limitToSeatsAtMost(0);
		$registrationBag = $this->fixture->build();

		$this->assertFalse(
			$registrationBag->isEmpty()
		);
	}


	////////////////////////////////
	// Tests for limitToAttendee()
	////////////////////////////////

	public function testLimitToAttendeeWithNegativeFeUserUidThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $frontEndUserUid must be >= 0.'
		);

		$this->fixture->limitToAttendee(-1);
	}

	public function testLimitToAttendeeWithPositiveFeUserUidFindsRegistrationsWithAttendee() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $eventUid, 'user' => $feUserUid)
		);

		$this->fixture->limitToAttendee($feUserUid);

		$this->assertEquals(
			$registrationUid,
			$this->fixture->build()->current()->getUid()
		);
	}

	public function testLimitToAttendeeWithPositiveFeUserUidIgnoresRegistrationsWithoutAttendee() {
		$feUserUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$this->fixture->limitToAttendee($feUserUid);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToAttendeeWithZeroFeUserUidFindsRegistrationsWithOtherAttendee() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$feUserUid = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$feUserUid2 = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$eventUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);
		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $eventUid, 'user' => $feUserUid2)
		);

		$this->fixture->limitToAttendee($feUserUid);
		$this->fixture->limitToAttendee(0);

		$this->assertEquals(
			$registrationUid,
			$this->fixture->build()->current()->getUid()
		);
	}


	//////////////////////////////////////
	// Tests for setOrderByEventColumn()
	//////////////////////////////////////

	public function testSetOrderByEventColumnCanSortAscendingByEventTitle() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'test title 1')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'test title 2')
		);
		$registrationUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES, array('seminar' => $eventUid1)
		);
		$registrationUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES, array('seminar' => $eventUid2)
		);

		$this->fixture->setOrderByEventColumn(
			SEMINARS_TABLE_SEMINARS . '.title ASC'
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$bag->current()->getUid(),
			$registrationUid1
		);
		$this->assertEquals(
			$bag->next()->getUid(),
			$registrationUid2
		);
	}

	public function testSetOrderByEventColumnCanSortDescendingByEventTitle() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'test title 1')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('title' => 'test title 2')
		);
		$registrationUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES, array('seminar' => $eventUid1)
		);
		$registrationUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES, array('seminar' => $eventUid2)
		);

		$this->fixture->setOrderByEventColumn(
			SEMINARS_TABLE_SEMINARS . '.title DESC'
		);
		$bag = $this->fixture->build();

		$this->assertEquals(
			$bag->current()->getUid(),
			$registrationUid2
		);
		$this->assertEquals(
			$bag->next()->getUid(),
			$registrationUid1
		);
	}
}
?>