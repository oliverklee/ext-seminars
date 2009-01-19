<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1.php');
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_seminarchild.php');

/**
 * Testcase for the registration manager class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_registrationmanager_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** a seminar to which the fixture relates */
	private $seminar = null;

	/** the UID of a fake front-end user */
	private $frontEndUserUid = 0;

	/** UID of a fake login page */
	private $loginPageUid = 0;

	/** UID of a fake registration page */
	private $registrationPageUid = 0;

	/** an instance of the Seminar Manager pi1 class */
	private $pi1 = null;

	/** @var tx_seminars_seminarchild a fully booked seminar */
	private $fullyBookedSeminar = null;

	/** @var tx_seminars_seminarchild a seminar */
	private $cachedSeminar = null;

	protected function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();
		tx_oelib_mailerFactory::getInstance()->enableTestMode();

		$this->seminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'title' => 'test event',
					'begin_date' => mktime() + 1000,
					'end_date' => mktime() + 2000,
					'attendees_max' => 10,
				)
			)
		);

		$this->fixture = new tx_seminars_registrationmanager();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		if ($this->pi1) {
			$this->pi1->__destruct();
		}
		$this->seminar->__destruct();
		$this->fixture->__destruct();

		if ($this->fullyBookedSeminar) {
			$this->fullyBookedSeminar->__destruct();
			unset($this->fullyBookedSeminar);
		}
		if ($this->cachedSeminar) {
			$this->cachedSeminar->__destruct();
			unset($this->cachedSeminar);
		}
		unset(
			$this->seminar, $this->pi1, $this->fixture, $this->testingFramework
		);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates a dummy login page and registration page and stores their UIDs
	 * in $this->loginPageUid and $this->registrationPageUid.
	 *
	 * In addition, it provides the fixture's configuration with the UIDs.
	 */
	private function createFrontEndPages() {
		$this->loginPageUid = $this->testingFramework->createFrontEndPage();
		$this->registrationPageUid
			= $this->testingFramework->createFrontEndPage();

		$this->pi1 = new tx_seminars_pi1();

		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'loginPID' => $this->loginPageUid,
				'registerPID' => $this->registrationPageUid,
			)
		);
	}

	/**
	 * Creates a FE user, stores it UID in $this->frontEndUserUid and logs it
	 * in.
	 */
	private function createAndLogInFrontEndUser() {
		$this->frontEndUserUid
			= $this->testingFramework->createAndLoginFrontEndUser();
	}

	/**
	 * Creates a seminar which is booked out.
	 */
	private function createBookedOutSeminar() {
		$this->fullyBookedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'title' => 'test event',
					'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
					'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
					'attendees_max' => 10,
					'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				)
			)
		);
		$this->fullyBookedSeminar->setNumberOfAttendances(10);
		$this->fullyBookedSeminar->setRegistrationQueueSize(5);
		$this->fullyBookedSeminar->setNumberOfAttendancesOnQueue(5);
	}


	////////////////////////////////////
	// Tests for the utility functions
	////////////////////////////////////

	public function testCreateFrontEndPagesCreatesNonZeroLoginPageUid() {
		$this->createFrontEndPages();

		$this->assertGreaterThan(
			0,
			$this->loginPageUid
		);
	}

	public function testCreateFrontEndPagesCreatesNonZeroRegistrationPageUid() {
		$this->createFrontEndPages();

		$this->assertGreaterThan(
			0,
			$this->registrationPageUid
		);
	}

	public function testCreateFrontEndPagesCreatesPi1() {
		$this->createFrontEndPages();

		$this->assertNotNull(
			$this->pi1
		);
		$this->assertTrue(
			$this->pi1 instanceof tx_seminars_pi1
		);
	}

	public function testCreateAndLogInFrontEndUserCreatesNonZeroUserUid() {
		$this->createAndLogInFrontEndUser();

		$this->assertGreaterThan(
			0,
			$this->frontEndUserUid
		);
	}

	public function testCreateAndLogInFrontEndUserLogsInFrontEndUser() {
		$this->createAndLogInFrontEndUser();
		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testCreateBookedOutSeminarSetsSeminarInstance() {
		$this->createBookedOutSeminar();

		$this->assertTrue(
			$this->fullyBookedSeminar instanceof tx_seminars_seminar
		);
	}

	public function testCreatedBookedOutSeminarHasUidGreaterZero() {
		$this->createBookedOutSeminar();

		$this->assertTrue(
			$this->fullyBookedSeminar->getUid() > 0
		);
	}


	/////////////////////////////////////////////////
	// Tests for the link to the registration page
	/////////////////////////////////////////////////

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkTag() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'<a ',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserContainsRedirectWithEventUid() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'redirect_url',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
		$this->assertContains(
			'%255Bseminar%255D%3D' . $this->seminar->getUid(),
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkTag() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'<a ',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'?id=' . $this->registrationPageUid,
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserDoesNotContainRedirect() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertNotContains(
			'redirect_url',
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}


	///////////////////////////////////////////////
	// Tests for the getRegistrationLink function
	///////////////////////////////////////////////

	public function testGetRegistrationLinkForLoggedInUserAndSeminarWithVacanciesReturnsLinkToRegistrationPage() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'?id=' . $this->registrationPageUid,
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function testGetRegistrationLinkForLoggedInUserAndSeminarWithVacanciesReturnsLinkWithSeminarUid() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function testGetRegistrationLinkForLoggedOutUserAndSeminarWithVacanciesReturnsLoginLink() {
		$this->createFrontEndPages();
		$this->testingFramework->logoutFrontEndUser();

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function testGetRegistrationLinkForLoggedInUserAndFullyBookedSeminarReturnsFullyBookedString() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->createBookedOutSeminar();

		$this->assertEquals(
			$this->fixture->translate('message_fullyBooked'),
			$this->fixture->getRegistrationLink(
				$this->pi1, $this->fullyBookedSeminar
			)
		);
	}

	public function testGetRegistrationLinkForLoggedOutUserAndFullyBookedSeminarReturnsFullyBookedString() {
		$this->createFrontEndPages();
		$this->testingFramework->logoutFrontEndUser();

		$this->createBookedOutSeminar();

		$this->assertEquals(
			$this->fixture->translate('message_fullyBooked'),
			$this->fixture->getRegistrationLink(
				$this->pi1, $this->fullyBookedSeminar
			)
		);
	}

	public function testGetRegistrationLinkForBeginDateBeforeCurrentDateReturnsEmptyString() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'title' => 'test event',
					'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
					'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
					'attendees_max' => 10
				)
			)
		);

		$this->assertEquals(
			'',
			$this->fixture->getRegistrationLink(
				$this->pi1, $this->cachedSeminar
			)
		);

	}

	public function testGetRegistrationLinkForAlreadyEndedRegistrationDeadlineReturnsEmptyString() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'title' => 'test event',
					'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
					'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
					'attendees_max' => 10,
					'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
				)
			)
		);

		$this->assertEquals(
			'',
			$this->fixture->getRegistrationLink(
				$this->pi1, $this->cachedSeminar
			)
		);
	}


	///////////////////////////////////////////
	// Tests concerning canRegisterIfLoggedIn
	///////////////////////////////////////////

	public function testCanRegisterIfLoggedInForLoggedOutUserAndSeminarRegistrationOpenReturnsTrue() {
		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedInUserAndRegistrationOpenReturnsTrue() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserReturnsFalse() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserAndSeminarWithMultipleRegistrationsAllowedReturnsTrue() {
		$this->seminar->setAllowsMultipleRegistrations(true);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedInButBlockedUserReturnsFalse() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
					'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
					'attendees_max' => 10,
					'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				)
			)
		);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->cachedSeminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarReturnsFalse() {
		$this->createBookedOutSeminar();

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->fullyBookedSeminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedOutUserAndCanceledSeminarReturnsFalse() {
		$this->seminar->setCanceled(true);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedOutUserAndSeminarWithoutRegistrationReturnsFalse() {
		$this->seminar->setAttendancesMax(0);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}


	/////////////////////////////////////////////
	// Test concerning userFulfillsRequirements
	/////////////////////////////////////////////

	public function testUserFulfillsRequirementsForEventWithoutRequirementsReturnsTrue() {
		$this->testingFramework->createAndLogInFrontEndUser();

		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertTrue(
			$this->fixture->userFulfillsRequirements($this->cachedSeminar)
		);
	}

	public function testUserFulfillsRequirementsForEventWithOneFulfilledRequirementReturnsTrue() {
		$requiredTopicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$requiredDateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $requiredTopicUid,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $requiredDateUid,
				'user' => $this->testingFramework->createAndLogInFrontEndUser(),
			)
		);

		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertTrue(
			$this->fixture->userFulfillsRequirements($this->cachedSeminar)
		);
	}

	public function testUserFulfillsRequirementsForEventWithOneUnfulfilledRequirementReturnsFalse() {
		$this->testingFramework->createAndLogInFrontEndUser();
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
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertFalse(
			$this->fixture->userFulfillsRequirements($this->cachedSeminar)
		);
	}


	//////////////////////////////////////////////
	// Tests concerning getMissingRequiredTopics
	//////////////////////////////////////////////

	public function testGetMissingRequiredTopicsReturnsSeminarBag() {
		$this->assertTrue(
			$this->fixture->getMissingRequiredTopics($this->seminar)
				instanceof tx_seminars_seminarbag
		);
	}

	public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsOneItem() {
		$this->testingFramework->createAndLogInFrontEndUser();
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
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertEquals(
			1,
			$missingTopics->count()
		);

		$missingTopics->__destruct();
	}

	public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsRequiredTopic() {
		$this->testingFramework->createAndLogInFrontEndUser();
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
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertEquals(
			$requiredTopicUid,
			$missingTopics->current()->getUid()
		);

		$missingTopics->__destruct();
	}

	public function testGetMissingRequiredTopicsForTopicWithOneTwoNotFulfilledRequirementReturnsTwoItems() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);

		$requiredTopicUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $requiredTopicUid1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid1, 'requirements'
		);

		$requiredTopicUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $requiredTopicUid2,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid2, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertEquals(
			2,
			$missingTopics->count()
		);

		$missingTopics->__destruct();
	}

	public function testGetMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledReturnsUnfulfilledTopic() {
		$userUid = $this->testingFramework->createAndLogInFrontEndUser();
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);

		$requiredTopicUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$requiredDateUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $requiredTopicUid1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $requiredDateUid1, 'user' => $userUid)
		);

		$requiredTopicUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid2, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertEquals(
			$requiredTopicUid2,
			$missingTopics->current()->getUid()
		);

		$missingTopics->__destruct();
	}

	public function testGetMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledDoesNotReturnFulfilledTopic() {
		$userUid = $this->testingFramework->createAndLogInFrontEndUser();
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);

		$requiredTopicUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$requiredDateUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $requiredTopicUid1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $requiredDateUid1, 'user' => $userUid)
		);

		$requiredTopicUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topicUid, $requiredTopicUid2, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'object_type' => SEMINARS_RECORD_TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertEquals(
			1,
			$missingTopics->count()
		);

		$missingTopics->__destruct();
	}


	////////////////////////////////////////
	// Tests concerning removeRegistration
	////////////////////////////////////////

	public function testRemoveRegistrationHidesRegistrationOfUser() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$seminarUid = $this->seminar->getUid();
		$this->createFrontEndPages();

		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
				'hidden' => 0,
			)
		);

		$this->fixture->removeRegistration($registrationUid, $this->pi1);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				SEMINARS_TABLE_ATTENDANCES,
				'user = ' . $userUid . ' AND seminar = ' . $seminarUid .
					' AND hidden = 1'
			)
		);
	}
}
?>