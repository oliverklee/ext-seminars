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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_registrationBagBuilder.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the registration bag builder class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registrationBagBuilder_testcase extends tx_phpunit_testcase {
	/**
	 * @var	tx_seminars_pi1
	 */
	private $fixture;
	/**
	 * @var	tx_oelib_testingFramework
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
			$registrationBag->getObjectCountWithoutLimit()
		);

		$this->assertEquals(
			'Title 1',
			$registrationBag->getCurrent()->getTitle()
		);
		$this->assertEquals(
			'Title 2',
			$registrationBag->getNext()->getTitle()
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
			$registrationBag->getObjectCountWithoutLimit()
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
			$registrationBag->getCurrent()->getTitle()
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

		$this->assertEquals(
			0,
			$registrationBag->getObjectCountWithoutLimit()
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
			$registrationBag->getCurrent()->isPaid()
		);
	}

	public function testLimitToPaidIgnoresUnpaidRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('title' => 'Attendance 1', 'paid' => 0)
		);
		$this->fixture->limitToPaid();
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			0,
			$registrationBag->getObjectCountWithoutLimit()
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
			$registrationBag->getCurrent()->isPaid()
		);
	}

	public function testLimitToUnpaidIgnoresPaidRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('paid' => 1)
		);
		$this->fixture->limitToUnpaid();
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			0,
			$registrationBag->getObjectCountWithoutLimit()
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
			$registrationBag->getCurrent()->isPaid()
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
			$registrationBag->getCurrent()->isPaid()
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
			$registrationBag->getCurrent()->isOnRegistrationQueue()
		);
	}

	public function testLimitToOnQueueIgnoresRegularRegistration() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 0)
		);
		$this->fixture->limitToOnQueue();
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			0,
			$registrationBag->getObjectCountWithoutLimit()
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
			$registrationBag->getCurrent()->isOnRegistrationQueue()
		);
	}

	public function testLimitToRegularIgnoresRegistrationOnQueue() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('registration_queue' => 1)
		);
		$this->fixture->limitToRegular();
		$registrationBag = $this->fixture->build();

		$this->assertEquals(
			0,
			$registrationBag->getObjectCountWithoutLimit()
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
			$registrationBag->getCurrent()->isOnRegistrationQueue()
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
			$registrationBag->getCurrent()->isOnRegistrationQueue()
		);
	}
}
?>