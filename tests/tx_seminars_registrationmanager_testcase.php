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
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_registrationchild.php');

/**
 * Testcase for the registration manager class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_registrationmanager_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_registrationmanager
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_seminarchild a seminar to which the fixture relates
	 */
	private $seminar = null;

	/**
	 * @var integer the UID of a fake front-end user
	 */
	private $frontEndUserUid = 0;

	/**
	 * @var integer UID of a fake login page
	 */
	private $loginPageUid = 0;

	/**
	 * @var integer UID of a fake registration page
	 */
	private $registrationPageUid = 0;

	/**
	 * @var tx_seminars_pi1 a front-end plugin
	 */
	private $pi1 = null;

	/**
	 * @var tx_seminars_seminarchild a fully booked seminar
	 */
	private $fullyBookedSeminar = null;

	/**
	 * @var tx_seminars_seminarchild a seminar
	 */
	private $cachedSeminar = null;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		tx_seminars_registrationchild::purgeCachedSeminars();
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_TEXT_MAIL
		);

		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'test organizer',
				'email' => 'mail@example.com',
			)
		);

		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'test event',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
				'attendees_min' => 1,
				'attendees_max' => 10,
				'needs_registration' => 1,
				'organizers' => 1,
			)
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $seminarUid, $organizerUid
		);

		$this->seminar = new tx_seminars_seminarchild($seminarUid);

		$this->fixture = tx_seminars_registrationmanager::getInstance();
		$this->fixture->setConfigurationValue(
			'templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		if ($this->pi1) {
			$this->pi1->__destruct();
		}
		$this->seminar->__destruct();

		if ($this->fullyBookedSeminar) {
			$this->fullyBookedSeminar->__destruct();
			unset($this->fullyBookedSeminar);
		}
		if ($this->cachedSeminar) {
			$this->cachedSeminar->__destruct();
			unset($this->cachedSeminar);
		}

		tx_seminars_registrationmanager::purgeInstance();
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
					'needs_registration' => 1,
					'queue_size' => 0,
				)
			)
		);
		$this->fullyBookedSeminar->setNumberOfAttendances(10);
	}

	/**
	 * Returns and creates a registration.
	 *
	 * A new front-end user will be created and the event in $this->seminar will
	 * be used.
	 *
	 * @return tx_seminars_registration the created registration
	 */
	private function createRegistration() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			'',
			array(
				'name' => 'foo_user',
				'email' => 'foo@bar.com',
			)
		);

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminar->getUid(),
				'user' => $frontEndUserUid,
			)
		);

		return new tx_seminars_registrationchild($registrationUid);
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


	////////////////////////////////////////////
	// Tests regarding the Singleton property.
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getInstanceReturnsRegistrationManagerInstance() {
		$this->assertTrue(
			tx_seminars_registrationmanager::getInstance() instanceof
				tx_seminars_registrationmanager
		);
	}

	/**
	 * @test
	 */
	public function getInstanceTwoTimesReturnsSameInstance() {
		$this->assertSame(
			tx_seminars_registrationmanager::getInstance(),
			tx_seminars_registrationmanager::getInstance()
		);
	}

	/**
	 * @test
	 */
	public function getInstanceAfterPurgeInstanceReturnsNewInstance() {
		$firstInstance = tx_seminars_registrationmanager::getInstance();
		tx_seminars_registrationmanager::purgeInstance();

		$this->assertNotSame(
			$firstInstance,
			tx_seminars_registrationmanager::getInstance()
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

	public function test_GetLinkToRegistrationOrLoginPage_WithLoggedInUserAndSeminarWithoutDate_HasLinkWithPrebookLabel() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setBeginDate(0);

		$this->assertContains(
			$this->pi1->translate('label_onlinePrebooking'),
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function test_GetLinkToRegistrationOrLoginPage_WithLoggedInUserSeminarWithoutDateAndNoVacancies_ContainsRegistrationLabel() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setBeginDate(0);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setAttendancesMax(5);

		$this->assertContains(
			$this->pi1->translate('label_onlineRegistration'),
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function test_GetLinkToRegistrationOrLoginPage_WithLoggedInUserAndFullyBookedSeminarWithQueue_ContainsQueueRegistrationLabel() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setRegistrationQueue(true);

		$this->assertContains(
			sprintf($this->pi1->translate('label_onlineRegistrationOnQueue'), 0),
			$this->fixture->getLinkToRegistrationOrLoginPage(
				$this->pi1, $this->seminar
			)
		);
	}

	public function test_GetLinkToRegistrationOrLoginPage_WithLoggedOutUserAndFullyBookedSeminarWithQueue_ContainsQueueRegistrationLabel() {
		$this->createFrontEndPages();
		$this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setRegistrationQueue(true);

		$this->assertContains(
			sprintf($this->pi1->translate('label_onlineRegistrationOnQueue'), 0),
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

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function test_GetRegistrationLink_ForLoggedInUserAndFullyBookedSeminar_ReturnsEmptyString() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->createBookedOutSeminar();

		$this->assertEquals(
			'',
			$this->fixture->getRegistrationLink(
				$this->pi1, $this->fullyBookedSeminar
			)
		);
	}

	public function test_GetRegistrationLink_ForLoggedOutUserAndFullyBookedSeminar_ReturnsEmptyString() {
		$this->createFrontEndPages();

		$this->createBookedOutSeminar();

		$this->assertEquals(
			'',
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

	public function test_GetRegistrationLink_ForLoggedInUserAndSeminarWithUnlimitedVacancies_ReturnsLinkWithSeminarUid() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setUnlimitedVacancies();

		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function test_GetRegistrationLink_ForLoggedOutUserAndSeminarWithUnlimitedVacancies_ReturnsLoginLink() {
		$this->createFrontEndPages();
		$this->seminar->setUnlimitedVacancies();

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function test_GetRegistrationLink_ForLoggedInUserAndFullyBookedSeminarWithQueueEnabled_ReturnsLinkWithSeminarUid() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setNeedsRegistration(true);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(true);

		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function test_GetRegistrationLink_ForLoggedOutUserAndFullyBookedSeminarWithQueueEnabled_ReturnsLoginLink() {
		$this->createFrontEndPages();
		$this->seminar->setNeedsRegistration(true);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(true);

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
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
		$this->seminar->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function testCanRegisterIfLoggedInForLoggedOutUserAndSeminarWithoutRegistrationReturnsFalse() {
		$this->seminar->setAttendancesMax(0);
		$this->seminar->setNeedsRegistration(false);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedIn_ForLoggedOutUserAndSeminarWithUnlimitedVacancies_ReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedIn_ForLoggedInUserAndSeminarWithUnlimitedVacancies_ReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedIn_ForLoggedOutUserAndFullyBookedSeminarWithQueue_ReturnsTrue() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(true);

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedIn_ForLoggedInUserAndFullyBookedSeminarWithQueue_ReturnsTrue() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(true);
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}


	//////////////////////////////////////////////////
	// Tests concerning canRegisterIfLoggedInMessage
	//////////////////////////////////////////////////

	public function test_CanRegisterIfLoggedInMessage_ForLoggedOutUserAndSeminarRegistrationOpen_ReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedInUserAndRegistrationOpen_ReturnsEmptyString() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedInButAlreadyRegisteredUser_ReturnsAlreadyRegisteredMessage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->assertEquals(
			$this->fixture->translate('message_alreadyRegistered'),
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedInButAlreadyRegisteredUserAndSeminarWithMultipleRegistrationsAllowed_ReturnsEmptyString() {
		$this->seminar->setAllowsMultipleRegistrations(true);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedInButBlockedUser_ReturnsUserIsBlockedMessage() {
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

		$this->assertEquals(
			$this->fixture->translate('message_userIsBlocked'),
			$this->fixture->canRegisterIfLoggedInMessage($this->cachedSeminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedOutUserAndFullyBookedSeminar_ReturnsFullyBookedMessage() {
		$this->createBookedOutSeminar();

		$this->assertEquals(
			'message_noVacancies',
			$this->fixture->canRegisterIfLoggedInMessage($this->fullyBookedSeminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedOutUserAndCanceledSeminar_ReturnsSeminarCancelledMessage() {
		$this->seminar->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertEquals(
			'message_seminarCancelled',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedOutUserAndSeminarWithoutRegistration_ReturnsNoRegistrationNeededMessage() {
		$this->seminar->setAttendancesMax(0);
		$this->seminar->setNeedsRegistration(false);

		$this->assertEquals(
			'message_noRegistrationNecessary',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedOutUserAndSeminarWithUnlimitedVacancies_ReturnsEmptyString() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedInUserAndSeminarWithUnlimitedVacancies_ReturnsEmptyString() {
		$this->seminar->setUnlimitedVacancies();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedOutUserAndFullyBookedSeminarWithQueue_ReturnsEmptyString() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(true);

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	public function test_CanRegisterIfLoggedInMessage_ForLoggedInUserAndFullyBookedSeminarWithQueue_ReturnsEmptyString() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(true);
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
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


	//////////////////////////////////////
	// Tests concerning canRegisterSeats
	//////////////////////////////////////

	public function test_CanRegisterSeats_ForFullyBookedEventAndZeroSeatsGiven_ReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventAndOneSeatGiven_ReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventAndEmptyStringGiven_ReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, '')
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventAndInvalidStringGiven_ReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 'foo')
		);
	}

	public function test_CanRegisterSeats_ForEventWithOneVacancyAndZeroSeatsGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}


	public function test_CanRegisterSeats_ForEventWithOneVacancyAndOneSeatGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	public function test_CanRegisterSeats_ForEventWithOneVacancyAndTwoSeatsGiven_ReturnsFalse() {
		$this->seminar->setAttendancesMax(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	public function test_CanRegisterSeats_ForEventWithOneVacancyAndEmptyStringGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, '')
		);
	}

	public function test_CanRegisterSeats_ForEventWithOneVacancyAndInvalidStringGiven_ReturnsFalse() {
		$this->seminar->setAttendancesMax(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 'foo')
		);
	}

	public function test_CanRegisterSeats_ForEventWithTwoVacanciesAndOneSeatGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(2);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	public function test_CanRegisterSeats_ForEventWithTwoVacanciesAndTwoSeatsGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(2);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	public function test_CanRegisterSeats_ForEventWithTwoVacanciesAndThreeSeatsGiven_ReturnsFalse() {
		$this->seminar->setAttendancesMax(2);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 3)
		);
	}

	public function test_CanRegisterSeats_ForEventWithUnlimitedVacanciesAndZeroSeatsGiven_ReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}

	public function test_CanRegisterSeats_ForEventWithUnlimitedVacanciesAndOneSeatGiven_ReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	public function test_CanRegisterSeats_ForEventWithUnlimitedVacanciesAndTwoSeatsGiven_ReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	public function test_CanRegisterSeats_ForEventWithUnlimitedVacanciesAndFortytwoSeatsGiven_ReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 42)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventWithQueueAndZeroSeatsGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(true);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventWithQueueAndOneSeatGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(true);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventWithQueueAndTwoSeatsGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(true);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventWithQueueAndEmptyStringGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(true);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, '')
		);
	}

	public function test_CanRegisterSeats_ForEventWithTwoVacanciesAndWithQueueAndFortytwoSeatsGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(2);
		$this->seminar->setRegistrationQueue(true);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 42)
		);
	}


	////////////////////////////////////
	// Tests concerning notifyAttendee
	////////////////////////////////////

	public function test_NotifyAttendee_SendsMailToAttendeesMailAdress() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertEquals(
			'foo@bar.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastRecipient()
		);
	}

	public function test_NotifyAttendee_MailSubjectContainsConfirmationSubject() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('email_confirmationSubject'),
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastSubject()
		);
	}

	public function test_NotifyAttendee_MailBodyContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'test event',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_MailSubjectContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'test event',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastSubject()
		);
	}

	public function test_NotifyAttendee_SetsOrganizerAsSender() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'From: "test organizer" <mail@example.com>',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastHeaders()
		);
	}

	public function test_NotifyAttendee_ForHtmlMailSet_HasHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'<html',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForTextMailSet_DoesNotHaveHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			'<html',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForMailSetToUserModeAndUserSetToHtmlMails_HasHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_USER_MAIL
		);
		$registration = $this->createRegistration();
		$registration->getFrontEndUser()->setData(
			array(
				'module_sys_dmail_html' => true,
				'email' => 'foo@bar.com',
			)
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'<html',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForMailSetToUserModeAndUserSetToTextMails_DoesNotHaveHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_USER_MAIL
		);
		$registration = $this->createRegistration();
		$registration->getFrontEndUser()->setData(
			array(
				'module_sys_dmail_html' => false,
				'email' => 'foo@bar.com',
			)
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			'<html',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForHtmlMails_ContainsNameOfUserInBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'foo_user',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForHtmlMails_HasLinkToSeminarInBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$registration = $this->createRegistration();
		$registration->getFrontEndUser()->setData(
			array(
				'email' => 'foo@bar.com',
			)
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$seminarLink = $registration->getSeminarObject()->getDetailedViewUrl($pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'<a href=3D"' . $seminarLink,
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForConfirmedEvent_DoesNotHavePlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$registration = $this->createRegistration();
		$registration->getSeminarObject()->setStatus(
			tx_seminars_seminar::STATUS_CONFIRMED
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			$this->fixture->translate('label_planned_disclaimer'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()
					->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForCancelledEvent_DoesNotHavePlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$registration = $this->createRegistration();
		$registration->getSeminarObject()->setStatus(
			tx_seminars_seminar::STATUS_CANCELED
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			$this->fixture->translate('label_planned_disclaimer'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()
					->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlannedEvent_DisplaysPlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$registration = $this->createRegistration();
		$registration->getSeminarObject()->setStatus(
			tx_seminars_seminar::STATUS_PLANNED
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('label_planned_disclaimer'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_hiddenDisclaimerFieldAndPlannedEvent_HidesPlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$this->fixture->setConfigurationValue(
			'hideFieldsInThankYouMail', 'planned_disclaimer'
		);
		$registration = $this->createRegistration();
		$registration->getSeminarObject()->setStatus(
			tx_seminars_seminar::STATUS_PLANNED
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			$this->fixture->translate('label_planned_disclaimer'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()
					->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForHtmlMails_HasCssStylesFromFile() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'style=',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	public function test_NotifyAttendee_MailBodyCanContainAttendeesNames() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$registration->setAttendeesNames('foo1 foo2');
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'foo1 foo2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlainTextMail_EnumeratesAttendeesNames() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$registration->setAttendeesNames('foo1' . LF . 'foo2');
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'1. foo1' . LF . '2. foo2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForHtmlMail_ReturnsAttendeesNamesInOrderedList() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$registration->setAttendeesNames('foo1' . LF . 'foo2');
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertRegExp(
			'/\<ol>.*<li>foo1<\/li>.*<li>foo2<\/li>.*<\/ol>/s',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	public function test_NotifyAttendee_CanSendPlaceTitleInMailBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => 'foo_place')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'foo_place',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_CanSendPlaceAddressInMailBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('address' => 'foo_street')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'foo_street',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForEventWithNoPlace_SendsWillBeAnnouncedMessage() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlainTextMail_SeparatesPlacesTitleAndAddressWithLF() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'place_title','address' => 'place_address')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'place_title' . LF . 'place_address',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForHtmlMail_SeparatesPlacesTitleAndAddressWithBreaks() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'place_title','address' => 'place_address')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'place_title<br>place_address',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	public function test_NotifyAttendee_StripsHtmlTagsFromPlaceAddress() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'place_title','address' => 'place<h2>_address</h2>')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'place_title' . LF . 'place_address',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddress_ReplacesLineFeedsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address1' . LF . 'address2')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address1 address2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddress_ReplacesCarriageReturnsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address1' . CR . 'address2')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address1 address2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddress_ReplacesCarriageReturnAndLineFeedWithOneSpace() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address1' . CRLF . 'address2')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address1 address2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddress_ReplacesMultipleCarriageReturnsWithOneSpace() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address1' . CR . CR . 'address2')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address1 address2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressAndPlainTextMails_ReplacesMultipleLineFeedsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address1' . LF . LF . 'address2')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address1 address2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressAndHtmlMails_ReplacesMultipleLineFeedsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address1' . LF . LF . 'address2')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address1 address2',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForPlaceAddress_ReplacesMultipleLineFeedAndCarriageReturnsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address1' . LF . 'address2' . CR . CRLF . 'address3')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address1 address2 address3',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressAndPlainTextMails_SendsCityOfPlace() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => 'footown')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'footown',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressAndPlainTextMails_SendsCountryOfPlace() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'footown', 'country' => $country->getIsoAlpha2Code())
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$country->getLocalShortName(),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressAndPlainTextMails_SeparatesAddressAndCityWithNewline() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address', 'city' => 'footown')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address' . LF . 'footown',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressAndHtmlMails_SeparatresAddressAndCityLineWithBreaks() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setConfigurationValueInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
		);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address', 'city' => 'footown')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'address<br>footown',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressWithCountryAndCity_SeparatesCountryAndCityWithComma() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array(
				'address' => 'address',
				'city' => 'footown',
				'country' => $country->getIsoAlpha2Code(),
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			'footown, ' . $country->getLocalShortName(),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForPlaceAddressWithCityAndNoCountry_DoesNotAddSurplusCommaAfterCity() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('address' => 'address', 'city' => 'footown')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			'footown,',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}


	////////////////////////////////////
	// Tests concerning the salutation
	////////////////////////////////////

	public function test_NotifyAttendee_ForInformalSalutation_ContainsInformalSalutation() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$this->fixture->setConfigurationValue('salutation', 'informal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_salutation_informal'),
				'foo_user'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForFormalSalutationAndGenderUnknown_ContainsFormalUnknownSalutation() {
		if (t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is ' .
					'not loaded.'
			);
		}

		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$this->fixture->setConfigurationValue('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_salutation_formal_2'),
				'foo_user'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForFormalSalutationAndGenderMale_ContainsFormalMaleSalutation() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is ' .
					'loaded.'
			);
		}

		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$this->fixture->setConfigurationValue('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com', 'gender' => 0)
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_salutation_formal_0'),
				'foo_user'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForFormalSalutationAndGenderFemale_ContainsFormalFemaleSalutation() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is ' .
					'loaded.'
			);
		}

		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$this->fixture->setConfigurationValue('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com', 'gender' => 1)
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_salutation_formal_1'),
				'foo_user'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForFormalSalutationAndConfirmation_ContainsFormalConfirmationText() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$this->fixture->setConfigurationValue('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('email_confirmationHello_formal'),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForInformalSalutationAndConfirmation_ContainsInformalConfirmationText() {
		$this->fixture->setConfigurationValue('sendConfirmation', true);
		$this->fixture->setConfigurationValue('salutation', 'informal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('email_confirmationHello_informal'),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForFormalSalutationAndUnregistration_ContainsFormalUnregistrationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnUnregistration', true
		);
		$this->fixture->setConfigurationValue('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnUnregistration'
		);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate(
				'email_confirmationOnUnregistrationHello_formal'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForInformalSalutationAndUnregistration_ContainsInformalUnregistrationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnUnregistration', true
		);
		$this->fixture->setConfigurationValue('salutation', 'informal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnUnregistration'
		);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate(
				'email_confirmationOnUnregistrationHello_informal'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForFormalSalutationAndQueueConfirmation_ContainsFormalQueueConfirmationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnRegistrationForQueue', true
		);
		$this->fixture->setConfigurationValue('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnRegistrationForQueue'
		);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate(
				'email_confirmationOnRegistrationForQueueHello_formal'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForInformalSalutationAndQueueConfirmation_ContainsInformalQueueConfirmationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnRegistrationForQueue', true
		);
		$this->fixture->setConfigurationValue('salutation', 'informal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnRegistrationForQueue'
		);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate(
				'email_confirmationOnRegistrationForQueueHello_informal'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForFormalSalutationAndQueueUpdate_ContainsFormalQueueUpdateText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnQueueUpdate', true
		);
		$this->fixture->setConfigurationValue('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnQueueUpdate'
		);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate(
				'email_confirmationOnQueueUpdateHello_formal'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	public function test_NotifyAttendee_ForInformalSalutationAndQueueUpdate_ContainsInformalQueueUpdateText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnQueueUpdate', true
		);
		$this->fixture->setConfigurationValue('salutation', 'informal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnQueueUpdate'
		);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate(
				'email_confirmationOnQueueUpdateHello_informal'
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}


	///////////////////////////////////////////////////
	// Tests regarding the notification of organizers
	///////////////////////////////////////////////////

	public function test_NotifyOrganizers_IncludesHelloIfNotHidden() {
		$registration = $this->createRegistration();
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'hideFieldsInNotificationMail', ''
		);

		$this->fixture->notifyOrganizers($registration);
		$registration->__destruct();

		$this->assertContains(
			'Hello',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyOrganizers_ForEventWithOneVacancy_ShowsVacanciesLabelWithVacancyNumber() {
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'showSeminarFieldsInNotificationMail', 'vacancies'
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('needs_registration' => 1, 'attendees_max' => 2)
		);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);
		$registration->__destruct();

		$this->assertRegExp(
			'/' . $this->fixture->translate('label_vacancies') . ': 1$/',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyOrganizers_ForEventWithUnlimitedVacancies_ShowsVacanciesLabelWithUnlimtedLabel() {
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'showSeminarFieldsInNotificationMail', 'vacancies'
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('needs_registration' => 1, 'attendees_max' => 0)
		);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('label_vacancies') . ': ' .
				$this->fixture->translate('label_unlimited'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyOrganizers_ForRegistrationWithCompany_ShowsLabelOfCompany() {
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'showAttendanceFieldsInNotificationMail', 'company'
		);

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminar->getUid(),
				'user' => $this->testingFramework->createFrontEndUser(),
				'company' => 'foo inc.',
			)
		);

		$registration = tx_oelib_ObjectFactory::make(
			'tx_seminars_registrationchild', $registrationUid
		);
		$this->fixture->notifyOrganizers($registration);
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('label_company'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_NotifyOrganizers_ForRegistrationWithCompany_ShowsCompanyOfRegistration() {
		$this->fixture->setConfigurationValue('sendNotification', true);
		$this->fixture->setConfigurationValue(
			'showAttendanceFieldsInNotificationMail', 'company'
		);

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminar->getUid(),
				'user' => $this->testingFramework->createFrontEndUser(),
				'company' => 'foo inc.',
			)
		);

		$registration = tx_oelib_ObjectFactory::make(
			'tx_seminars_registrationchild', $registrationUid
		);
		$this->fixture->notifyOrganizers($registration);
		$registration->__destruct();

		$this->assertContains(
			'foo inc.',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}


	////////////////////////////////////////////////
	// Tests concerning sendAdditionalNotification
	////////////////////////////////////////////////

	public function test_SendAdditionalNotification_CanSendEmailToOneOrganizer() {
		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertContains(
			'mail@example.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastRecipient()
		);
	}

	public function test_SendAdditionalNotification_CanSendEmailsToTwoOrganizers() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'test organizer 2',
				'email' => 'mail2@example.com',
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_ORGANIZERS_MM, $this->seminar->getUid(),
			$organizerUid
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('organizers' => 2)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertEquals(
			2,
			count(
				tx_oelib_mailerFactory::getInstance()
					->getMailer()->getAllEmail()
			)
		);
	}

	public function test_SendAdditionalNotification_UsesTheFirstOrganizerAsSenderIfEmailIsSentToTwoOrganizers() {
		$organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'test organizer 2',
				'email' => 'mail2@example.com',
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_ORGANIZERS_MM, $this->seminar->getUid(),
			$organizerUid
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('organizers' => 2)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$sentEmails = tx_oelib_mailerFactory::getInstance()
			->getMailer()->getAllEmail();

		$this->assertContains(
			'mail@example.com',
			$sentEmails[0]['headers']
		);
		$this->assertContains(
			'mail@example.com',
			$sentEmails[1]['headers']
		);
	}

	public function test_SendAdditionalNotification_ForEventWithEnoughAttendancesSendsEnoughAttendancesMail() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('attendees_min' => 1, 'attendees_max' => 42)
		);

		unset($this->fixture);
		tx_seminars_registrationmanager::purgeInstance();
		$this->fixture = tx_seminars_registrationmanager::getInstance();
		$this->fixture->setConfigurationValue(
			'templateFile',
			'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_additionalNotificationEnoughRegistrationsSubject'
				),
				$this->seminar->getUid(),
				''
			),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	public function test_SendAdditionalNotification_ForEventWithZeroAttendeesMin_DoesNotSendAnyMail() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('attendees_min' => 0, 'attendees_max' => 42)
		);

		unset($this->fixture);
		tx_seminars_registrationmanager::purgeInstance();
		$this->fixture = tx_seminars_registrationmanager::getInstance();
		$this->fixture->setConfigurationValue(
			'templateFile',
			'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertEquals(
			array(),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail()
		);
	}

	public function test_SendAdditionalNotification_ForBookedOutEventSendsEmailWithBookedOutSubject() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('attendees_max' => 1)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_additionalNotificationIsFullSubject'
				),
				$this->seminar->getUid(),
				''
			),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	public function test_SendAdditionalNotification_ForBookedOutEventSendsEmailWithBookedOutMessage() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('attendees_max' => 1)
		);
		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('email_additionalNotificationIsFull'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_SendAdditionalNotification_ForEventWithNotEnoughAttendancesAndNotBookedOutSendsNoEmail() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array('attendees_min' => 5, 'attendees_max' => 5)
		);


		$fixture = new tx_seminars_registrationmanager();
		$fixture->setConfigurationValue(
			'templateFile',
			'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);

		$registration = $this->createRegistration();
		$fixture->sendAdditionalNotification($registration);
		$fixture->__destruct();
		$registration->__destruct();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getAllEmail())
		);
	}

	public function test_SendAdditionalNotification_ForEventWithEnoughAttendancesAndUnlimitedVacancies_SendsEmail() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array(
				'attendees_min' => 1,
				'attendees_max' => 0,
				'needs_registration' => 1
			)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertEquals(
			1,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getAllEmail())
		);
	}

	public function test_SendAdditionalNotification_ForEventWithEnoughAttendancesAndOneVacancy_ShowsVacanciesLabelWithVacancyNumber() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array(
				'attendees_min' => 1,
				'attendees_max' => 2,
				'needs_registration' => 1
			)
		);
		$this->fixture->setConfigurationValue(
			'showSeminarFieldsInNotificationMail', 'vacancies'
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertRegExp(
			'/' . $this->fixture->translate('label_vacancies') . ': 1$/',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_SendAdditionalNotification_ForEventWithEnoughAttendancesAndUnlimitedVacancies_ShowsVacanciesLabelWithUnlimitedLabel() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(),
			array(
				'attendees_min' => 1,
				'attendees_max' => 0,
				'needs_registration' => 1
			)
		);
		$this->fixture->setConfigurationValue(
			'showSeminarFieldsInNotificationMail', 'vacancies'
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);
		$registration->__destruct();

		$this->assertContains(
			$this->fixture->translate('label_vacancies') . ': ' .
				$this->fixture->translate('label_unlimited'),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}


	//////////////////////////////////////////////
	// Tests concerning allowsRegistrationByDate
	//////////////////////////////////////////////

	public function test_allowsRegistrationByDate_ForEventWithoutDateAndRegistrationForEventsWithoutDateAllowed_ReturnsTrue() {
		$this->seminar->setAllowRegistrationForEventsWithoutDate(1);
		$this->seminar->setBeginDate(0);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	public function test_allowsRegistrationByDate_ForEventWithoutDateAndRegistrationForEventsWithoutDateNotAllowed_ReturnsFalse() {
		$this->seminar->setAllowRegistrationForEventsWithoutDate(0);
		$this->seminar->setBeginDate(0);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	public function test_allowsRegistrationByDate_ForBeginDateAndRegistrationDeadlineOver_ReturnsFalse() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] - 42);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	public function test_allowsRegistrationByDate_ForBeginDateAndRegistrationDeadlineInFuture_ReturnsTrue() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] + 42);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	public function test_allowsRegistrationByDateForRegistrationBeginInFuture_ReturnsFalse() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] + 10);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	public function test_allowsRegistrationByDate_ForRegistrationBeginInPast_ReturnsTrue() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	public function test_allowsRegistrationByDate_ForNoRegistrationBegin_ReturnsTrue() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationBeginDate(0);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	public function test_allowsRegistrationByDate_ForBeginDateInPastAndRegistrationBeginInPast_ReturnsFalse() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);
		$this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 50);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	///////////////////////////////////////////////
	// Tests concerning allowsRegistrationBySeats
	///////////////////////////////////////////////

	public function test_allowsRegistrationBySeats_ForEventWithNoVacanciesAndNoQueue_ReturnsFalse() {
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setRegistrationQueue(false);

		$this->assertFalse(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}

	public function test_allowsRegistrationBySeats_ForEventWithUnlimitedVacancies_ReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}

	public function test_allowsRegistrationBySeats_ForEventWithRegistrationQueue_ReturnsTrue() {
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setRegistrationQueue(true);

		$this->assertTrue(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}

	public function test_allowsRegistrationBySeats_ForEventWithVacancies_ReturnsTrue() {
		$this->seminar->setNumberOfAttendances(0);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setRegistrationQueue(false);

		$this->assertTrue(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}


	////////////////////////////////////////////
	// Tests concerning registrationHasStarted
	////////////////////////////////////////////

	public function test_registrationHasStarted_ForEventWithoutRegistrationBegin_ReturnsTrue() {
		$this->seminar->setRegistrationBeginDate(0);

		$this->assertTrue(
			$this->fixture->registrationHasStarted($this->seminar)
		);
	}

	public function test_registrationHasStarted_ForEventWithRegistrationBeginInPast_ReturnsTrue() {
		$this->seminar->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] - 42
		);

		$this->assertTrue(
			$this->fixture->registrationHasStarted($this->seminar)
		);
	}

	public function test_registrationHasStarted_ForEventWithRegistrationBeginInFuture_ReturnsFalse() {
		$this->seminar->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] + 42
		);

		$this->assertFalse(
			$this->fixture->registrationHasStarted($this->seminar)
		);
	}
}
?>