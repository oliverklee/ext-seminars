<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Oliver Klee (typo3-coding@oliverklee.de)
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

	/**
	 * backed-up extension configuration of the TYPO3 configuration variables
	 *
	 * @var array
	 */
	private $extConfBackup = array();

	/**
	 * backed-up T3_VAR configuration
	 *
	 * @var array
	 */
	private $t3VarBackup = array();

	protected function setUp() {
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		tx_seminars_registrationchild::purgeCachedSeminars();
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_TEXT_MAIL
		);

		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'test organizer',
				'email' => 'mail@example.com',
				'email_footer' => 'organizer footer',
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

		tx_oelib_templateHelper::setCachedConfigurationValue(
			'templateFile',
			'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);

		$this->seminar = new tx_seminars_seminarchild($seminarUid);
		$this->fixture = tx_seminars_registrationmanager::getInstance();
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

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
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

	/**
	 * Creates a subclass of the fixture class that makes protected methods
	 * public where necessary.
	 *
	 * @return string the class name of the subclass, will not be empty
	 */
	private function createAccessibleProxyClass() {
		$testingClassName = 'tx_seminars_registrationmanager' . uniqid();

		if (!class_exists($testingClassName)) {
			eval(
				'class ' . $testingClassName .
					' extends tx_seminars_registrationmanager {' .
				'public function setRegistrationData(' .
				'  tx_seminars_Model_Registration $registration, array $formData' .
				') {' .
				'  parent::setRegistrationData($registration, $formData);' .
				'}' .
				'}'
			);
		}

		return $testingClassName;
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

	/**
	 * @test
	 */
	public function createAccessibleProxyClassCreatesFixtureSubclass() {
		$className = $this->createAccessibleProxyClass();
		$instance = new $className();

		$this->assertTrue(
			$instance instanceof tx_seminars_registrationmanager
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
		$this->seminar->setRegistrationQueue(TRUE);

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
		$this->seminar->setRegistrationQueue(TRUE);

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
		$this->seminar->setNeedsRegistration(TRUE);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	public function test_GetRegistrationLink_ForLoggedOutUserAndFullyBookedSeminarWithQueueEnabled_ReturnsLoginLink() {
		$this->createFrontEndPages();
		$this->seminar->setNeedsRegistration(TRUE);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}


	///////////////////////////////////////////
	// Tests concerning canRegisterIfLoggedIn
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedOutUserAndSeminarRegistrationOpenReturnsTrue() {
		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedInUserAndRegistrationOpenReturnsTrue() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserReturnsFalse() {
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

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserAndSeminarWithMultipleRegistrationsAllowedReturnsTrue() {
		$this->seminar->setAllowsMultipleRegistrations(TRUE);

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

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedInButBlockedUserReturnsFalse() {
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

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarReturnsFalse() {
		$this->createBookedOutSeminar();

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->fullyBookedSeminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedOutUserAndCanceledSeminarReturnsFalse() {
		$this->seminar->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedOutUserAndSeminarWithoutRegistrationReturnsFalse() {
		$this->seminar->setAttendancesMax(0);
		$this->seminar->setNeedsRegistration(FALSE);

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarWithQueueReturnsTrue() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForLoggedInUserAndFullyBookedSeminarWithQueueReturnsTrue() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(TRUE);
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForRegistrationPossibleCallsCanRegisterForSeminarHook() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$user = tx_oelib_FrontEndLoginManager::getInstance()
			->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
		$hook->expects($this->once())->method('canRegisterForSeminar')
			->with($this->seminar, $user);

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$this->fixture->canRegisterIfLoggedIn($this->seminar);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForRegistrationNotPossibleNotCallsCanRegisterForSeminarHook() {
		$this->seminar->setStatus(tx_seminars_seminar::STATUS_CANCELED);
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
		$hook->expects($this->never())->method('canRegisterForSeminar');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$this->fixture->canRegisterIfLoggedIn($this->seminar);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForRegistrationPossibleAndHookReturningTrueReturnsTrue() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
		$hook->expects($this->once())->method('canRegisterForSeminar')
			->will($this->returnValue(TRUE));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$this->assertTrue(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForRegistrationPossibleAndHookReturningFalseReturnsFalse() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
		$hook->expects($this->once())->method('canRegisterForSeminar')
			->will($this->returnValue(FALSE));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}


	//////////////////////////////////////////////////
	// Tests concerning canRegisterIfLoggedInMessage
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarRegistrationOpenReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInUserAndRegistrationOpenReturnsEmptyString() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredUserReturnsAlreadyRegisteredMessage() {
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

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredUserAndSeminarWithMultipleRegistrationsAllowedReturnsEmptyString() {
		$this->seminar->setAllowsMultipleRegistrations(TRUE);

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

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInButBlockedUserReturnsUserIsBlockedMessage() {
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

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarReturnsFullyBookedMessage() {
		$this->createBookedOutSeminar();

		$this->assertEquals(
			'message_noVacancies',
			$this->fixture->canRegisterIfLoggedInMessage($this->fullyBookedSeminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndCanceledSeminarReturnsSeminarCancelledMessage() {
		$this->seminar->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertEquals(
			'message_seminarCancelled',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarWithoutRegistrationReturnsNoRegistrationNeededMessage() {
		$this->seminar->setAttendancesMax(0);
		$this->seminar->setNeedsRegistration(FALSE);

		$this->assertEquals(
			'message_noRegistrationNecessary',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsEmptyString() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsEmptyString() {
		$this->seminar->setUnlimitedVacancies();
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarWithQueueReturnsEmptyString() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInUserAndFullyBookedSeminarWithQueueReturnsEmptyString() {
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setRegistrationQueue(TRUE);
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForRegistrationPossibleCallsCanUserRegisterForSeminarMessageHook() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$user = tx_oelib_FrontEndLoginManager::getInstance()
			->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
		$hook->expects($this->once())->method('canRegisterForSeminarMessage')
			->with($this->seminar, $user);

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$this->fixture->canRegisterIfLoggedInMessage($this->seminar);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForRegistrationNotPossibleNotCallsCanUserRegisterForSeminarMessageHook() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->seminar->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
		$hook->expects($this->never())->method('canRegisterForSeminarMessage');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$this->fixture->canRegisterIfLoggedInMessage($this->seminar);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsStringFromHook() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
		$hook->expects($this->once())->method('canRegisterForSeminarMessage')
			->will($this->returnValue('Hello world!'));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$this->assertEquals(
			'Hello world!',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsNonEmptyStringFromFirstHook() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass1 = uniqid('tx_registrationHook');
		$hook1 = $this->getMock($hookClass1, array('canRegisterForSeminarMessage'));
		$hook1->expects($this->any())->method('canRegisterForSeminarMessage')
			->will($this->returnValue('message 1'));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass1] = $hook1;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass1] = $hookClass1;

		$hookClass2 = uniqid('tx_registrationHook');
		$hook2 = $this->getMock($hookClass2, array('canRegisterForSeminarMessage'));
		$hook2->expects($this->any())->method('canRegisterForSeminarMessage')
			->will($this->returnValue('message 2'));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass2] = $hook2;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass2] = $hookClass2;

		$this->assertEquals(
			'message 1',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsFirstNonEmptyStringFromHooks() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass1 = uniqid('tx_registrationHook');
		$hook1 = $this->getMock($hookClass1, array('canRegisterForSeminarMessage'));
		$hook1->expects($this->any())->method('canRegisterForSeminarMessage')
			->will($this->returnValue(''));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass1] = $hook1;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass1] = $hookClass1;

		$hookClass2 = uniqid('tx_registrationHook');
		$hook2 = $this->getMock($hookClass2, array('canRegisterForSeminarMessage'));
		$hook2->expects($this->any())->method('canRegisterForSeminarMessage')
			->will($this->returnValue('message 2'));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass2] = $hook2;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass2] = $hookClass2;

		$this->assertEquals(
			'message 2',
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

	/**
	 * @test
	 */
	public function removeRegistrationHidesRegistrationOfUser() {
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

	/**
	 * @test
	 */
	public function removeRegistrationCallsSeminarRegistrationRemovedHook() {
		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('seminarRegistrationRemoved'));
		// We cannot test for the expected parameters because the registration
		// instance does not exist yet at this point.
		$hook->expects($this->once())->method('seminarRegistrationRemoved');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$seminarUid = $this->seminar->getUid();
		$this->createFrontEndPages();

		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
			)
		);

		$this->fixture->removeRegistration($registrationUid, $this->pi1);
	}

	/**
	 * @test
	 */
	public function removeRegistrationWithFittingQueueRegistrationMovesItFromQueue() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$seminarUid = $this->seminar->getUid();
		$this->createFrontEndPages();

		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
			)
		);
		$queueRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
				'seats' => 1,
				'registration_queue' => 1,
			)
		);

		$this->fixture->removeRegistration($registrationUid, $this->pi1);

		$this->testingFramework->existsRecord(
			SEMINARS_TABLE_ATTENDANCES,
			'registration_queue = 0 AND uid = ' . $queueRegistrationUid
		);
	}

	/**
	 * @test
	 */
	public function removeRegistrationWithFittingQueueRegistrationCallsSeminarRegistrationMovedFromQueueHook() {
		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('seminarRegistrationMovedFromQueue'));
		// We cannot test for the expected parameters because the registration
		// instance does not exist yet at this point.
		$hook->expects($this->once())->method('seminarRegistrationMovedFromQueue');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$seminarUid = $this->seminar->getUid();
		$this->createFrontEndPages();

		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
			)
		);
		$queueRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
				'seats' => 1,
				'registration_queue' => 1,
			)
		);

		$this->fixture->removeRegistration($registrationUid, $this->pi1);
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
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventWithQueueAndOneSeatGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventWithQueueAndTwoSeatsGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	public function test_CanRegisterSeats_ForFullyBookedEventWithQueueAndEmptyStringGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, '')
		);
	}

	public function test_CanRegisterSeats_ForEventWithTwoVacanciesAndWithQueueAndFortytwoSeatsGiven_ReturnsTrue() {
		$this->seminar->setAttendancesMax(2);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 42)
		);
	}


	////////////////////////////////////
	// Tests concerning notifyAttendee
	////////////////////////////////////

	/**
	 * @test
	 */
	public function notifyAttendeeSendsMailToAttendeesMailAdress() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForAttendeeWithoutMailAdressNotSendsEmail() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->seminar->getUid(),
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$registration = new tx_seminars_registrationchild($registrationUid);

		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertEquals(
			array(),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailSubjectContainsConfirmationSubject() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeMailBodyContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeMailSubjectContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeSetsOrganizerAsSender() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForHtmlMailSetHasHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
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

	/**
	 * @test
	 */
	public function notifyAttendeeForTextMailSetDoesNotHaveHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForTextMailSetHasNoUnreplacedMarkers() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			'###',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForHtmlMailHasNoUnreplacedMarkers() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
			);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();

		$this->assertNotContains(
			'###',
			tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForMailSetToUserModeAndUserSetToHtmlMailsHasHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_USER_MAIL
			);
		$registration = $this->createRegistration();
		$registration->getFrontEndUser()->setData(
			array(
				'module_sys_dmail_html' => TRUE,
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

	/**
	 * @test
	 */
	public function notifyAttendeeForMailSetToUserModeAndUserSetToTextMailsNotHasHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_USER_MAIL
			);
		$registration = $this->createRegistration();
		$registration->getFrontEndUser()->setData(
			array(
				'module_sys_dmail_html' => FALSE,
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

	/**
	 * @test
	 */
	public function notifyAttendeeForHtmlMailsContainsNameOfUserInBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
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

	/**
	 * @test
	 */
	public function notifyAttendeeForHtmlMailsHasLinkToSeminarInBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
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

	/**
	 * @test
	 */
	public function notifyAttendeeAppendsOrganizersFooterToMailBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
		$registration->__destruct();
		$pi1->__destruct();

		$this->assertContains(
			LF . '-- ' . LF . 'organizer footer',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForConfirmedEventNotHasPlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForCancelledEventNotHasPlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlannedEventDisplaysPlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeehiddenDisclaimerFieldAndPlannedEventHidesPlannedDisclaimer() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForHtmlMailsHasCssStylesFromFile() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
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

	/**
	 * @test
	 */
	public function notifyAttendeeMailBodyCanContainAttendeesNames() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlainTextMailEnumeratesAttendeesNames() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForHtmlMailReturnsAttendeesNamesInOrderedList() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
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

	/**
	 * @test
	 */
	public function notifyAttendeeCanSendPlaceTitleInMailBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('title' => 'foo_place')
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

	/**
	 * @test
	 */
	public function notifyAttendeeCanSendPlaceAddressInMailBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('address' => 'foo_street')
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

	/**
	 * @test
	 */
	public function notifyAttendeeForEventWithNoPlaceSendsWillBeAnnouncedMessage() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlainTextMailSeparatesPlacesTitleAndAddressWithLinefeed() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForHtmlMailSeparatesPlacesTitleAndAddressWithBreaks() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
			);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeStripsHtmlTagsFromPlaceAddress() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressReplacesLineFeedsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressReplacesCarriageReturnsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressReplacesCarriageReturnAndLineFeedWithOneSpace() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressReplacesMultipleCarriageReturnsWithOneSpace() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressAndPlainTextMailsReplacesMultipleLineFeedsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressAndHtmlMailsReplacesMultipleLineFeedsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
			);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressReplacesMultipleLineFeedAndCarriageReturnsWithSpaces() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
			);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCityOfPlace() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('city' => 'footown')
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsZipAndCityOfPlace() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('zip' => '12345', 'city' => 'footown')
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
			'12345 footown',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCountryOfPlace() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressAndPlainTextMailsSeparatesAddressAndCityWithNewline() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressAndHtmlMailsSeparatresAddressAndCityLineWithBreaks() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger(
				'eMailFormatForAttendees',
				tx_seminars_registrationmanager::SEND_HTML_MAIL
			);
		$this->fixture->setConfigurationValue(
			'cssFileForAttendeeMail',
			'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
		);

		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressWithCountryAndCitySeparatesCountryAndCityWithComma() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForPlaceAddressWithCityAndNoCountryNotAddsSurplusCommaAfterCity() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
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

	/**
	 * @test
	 */
	public function notifyAttendeeForInformalSalutationContainsInformalSalutation() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::getInstance()->get('plugin.tx_seminars')
			->setAsString('salutation', 'informal');
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
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_hello_informal'),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndGenderUnknownContainsFormalUnknownSalutation() {
		if (t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is ' .
					'not loaded.'
			);
		}

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::getInstance()->get('plugin.tx_seminars')
			->setAsString('salutation', 'formal');
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
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_hello_formal_2'),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndGenderMaleContainsFormalMaleSalutation() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is ' .
					'loaded.'
			);
		}

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::getInstance()->get('plugin.tx_seminars')
			->setAsString('salutation', 'formal');
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
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_hello_formal_0'),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndGenderFemaleContainsFormalFemaleSalutation() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is ' .
					'loaded.'
			);
		}

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::getInstance()->get('plugin.tx_seminars')
			->setAsString('salutation', 'formal');
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
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')
				->translate('email_hello_formal_1'),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndConfirmationContainsFormalConfirmationText() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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
				$this->fixture->translate('email_confirmationHello_formal'),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForInformalSalutationAndConfirmationContainsInformalConfirmationText() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
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
				$this->fixture->translate('email_confirmationHello_informal'),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndUnregistrationContainsFormalUnregistrationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnUnregistration', TRUE
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
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnUnregistrationHello_formal'
				),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForInformalSalutationAndUnregistrationContainsInformalUnregistrationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnUnregistration', TRUE
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
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnUnregistrationHello_informal'
				),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndQueueConfirmationContainsFormalQueueConfirmationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnRegistrationForQueue', TRUE
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
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnRegistrationForQueueHello_formal'
				),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForInformalSalutationAndQueueConfirmationContainsInformalQueueConfirmationText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnRegistrationForQueue', TRUE
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
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnRegistrationForQueueHello_informal'
				),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndQueueUpdateContainsFormalQueueUpdateText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnQueueUpdate', TRUE
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
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnQueueUpdateHello_formal'
				),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForInformalSalutationAndQueueUpdateContainsInformalQueueUpdateText() {
		$this->fixture->setConfigurationValue(
			'sendConfirmationOnQueueUpdate', TRUE
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
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnQueueUpdateHello_informal'
				),
				$this->seminar->getTitle()
			),
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()
				->getLastBody()
			)
		);
	}


	///////////////////////////////////////////////
	// Tests concerning the unregistration notice
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function notifyAttendeeForUnregistrationMailDoesNotAppendUnregistrationNotice() {
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager', array('getUnregistrationNotice')
		);
		$fixture->expects($this->never())->method('getUnregistrationNotice');

		$fixture->setConfigurationValue('sendConfirmationOnUnregistration', TRUE);
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
			)
		);
		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnUnregistration'
		);
		$pi1->__destruct();
		$registration->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForRegistrationMailAndNoUnregistrationPossibleNotAddsUnregistrationNotice() {
		tx_oelib_templatehelper::setCachedConfigurationValue(
			'allowUnregistrationWithEmptyWaitingList', FALSE
		);

		$fixture = $this->getMock(
			'tx_seminars_registrationmanager', array('getUnregistrationNotice')
		);
		$fixture->expects($this->never())->method('getUnregistrationNotice');
		$fixture->setConfigurationValue('sendConfirmation', TRUE);

		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
			)
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForRegistrationMailAndUnregistrationPossibleAddsUnregistrationNotice() {
		tx_oelib_templatehelper::setCachedConfigurationValue(
			'allowUnregistrationWithEmptyWaitingList', TRUE
		);

		$fixture = $this->getMock(
			'tx_seminars_registrationmanager', array('getUnregistrationNotice')
		);
		$fixture->expects($this->once())->method('getUnregistrationNotice');
		$fixture->setConfigurationValue('sendConfirmation', TRUE);

		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
			)
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$fixture->notifyAttendee($registration, $pi1);
		$pi1->__destruct();
		$registration->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForRegistrationOnQueueMailAndUnregistrationPossibleAddsUnregistrationNotice() {
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager', array('getUnregistrationNotice')
		);
		$fixture->expects($this->once())->method('getUnregistrationNotice');

		$fixture->setConfigurationValue(
			'sendConfirmationOnRegistrationForQueue', TRUE
		);
		$registration = $this->createRegistration();
		$this->createRegistration();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'queue_size' => 1,
			)
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_ATTENDANCES, $registration->getUid(),
			array('registration_queue' => 1)
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnRegistrationForQueue'
		);
		$pi1->__destruct();
		$registration->__destruct();
		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForQueueUpdateMailAndUnregistrationPossibleAddsUnregistrationNotice() {
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager', array('getUnregistrationNotice')
		);
		$fixture->expects($this->once())->method('getUnregistrationNotice');

		$fixture->setConfigurationValue('sendConfirmationOnQueueUpdate', TRUE);
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
				'queue_size' => 1,
			)
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_ATTENDANCES, $registration->getUid(),
			array('registration_queue' => 1)
		);

		$pi1 = new tx_seminars_pi1();
		$pi1->init();

		$fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnQueueUpdate'
		);
		$pi1->__destruct();
		$registration->__destruct();
		$fixture->__destruct();
	}


	///////////////////////////////////////////////////
	// Tests regarding the notification of organizers
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function notifyOrganizersUsesOrganizerAsFrom() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);
		$registration->__destruct();

		$this->assertContains(
			'From: "test organizer" <mail@example.com>',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersUsesOrganizerAsTo() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);
		$registration->__destruct();

		$this->assertEquals(
			'mail@example.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastRecipient()
		);
	}

	public function test_NotifyOrganizers_IncludesHelloIfNotHidden() {
		$registration = $this->createRegistration();
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
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
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
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
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
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
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
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
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
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
			'tx_seminars_organizers',
			array(
				'title' => 'test organizer 2',
				'email' => 'mail2@example.com',
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $this->seminar->getUid(),
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
			'tx_seminars_organizers',
			array(
				'title' => 'test organizer 2',
				'email' => 'mail2@example.com',
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $this->seminar->getUid(),
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
		$this->seminar->setRegistrationQueue(FALSE);

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
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}

	public function test_allowsRegistrationBySeats_ForEventWithVacancies_ReturnsTrue() {
		$this->seminar->setNumberOfAttendances(0);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setRegistrationQueue(FALSE);

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


	////////////////////////////////////////
	// Tests concerning createRegistration
	////////////////////////////////////////

	/**
	 * @TODO: This is just a transitional test that needs to be removed once
	 * createRegistration uses the data mapper to save the registration.
	 *
	 * @test
	 */
	public function createRegistrationSavesRegistration() {
		$this->createAndLogInFrontEndUser();

		$plugin = new tx_seminars_pi1();
		$plugin->cObj = $GLOBALS['TSFE']->cObj;
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager',
			array(
				'notifyAttendee', 'notifyOrganizers',
				'sendAdditionalNotification', 'setRegistrationData'
			)
		);

		$fixture->createRegistration(
			$this->seminar, array(), $plugin
		);

		$this->assertTrue(
			$fixture->getRegistration() instanceof tx_seminars_registration
		);
		$uid = $fixture->getRegistration()->getUid();
		$this->assertTrue(
			// We're not using the testing framework here because the record
			// is not marked as dummy record.
			tx_oelib_db::existsRecordWithUid(
				'tx_seminars_attendances', $uid
			)
		);

		$fixture->__destruct();
		$plugin->__destruct();

		tx_oelib_db::delete('tx_seminars_attendances', 'uid = ' . $uid);
	}

	/**
	 * @test
	 */
	public function createRegistrationReturnsRegistration() {
		$this->createAndLogInFrontEndUser();

		$plugin = new tx_seminars_pi1();
		$plugin->cObj = $GLOBALS['TSFE']->cObj;
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager',
			array(
				'notifyAttendee', 'notifyOrganizers',
				'sendAdditionalNotification', 'setRegistrationData'
			)
		);

		$registration = $fixture->createRegistration($this->seminar, array(), $plugin);

		$uid = $fixture->getRegistration()->getUid();
		// @TODO: This line needs to be removed once createRegistration uses
		// the data mapper to save the registration.
		tx_oelib_db::delete('tx_seminars_attendances', 'uid = ' . $uid);

		$this->assertTrue(
			$registration instanceof tx_seminars_Model_Registration
		);

		$fixture->__destruct();
		$plugin->__destruct();
	}


	/**
	 * @TODO: This is just a transitional test that can be removed once
	 * createRegistration does not use the old registration model anymore.
	 *
	 * @test
	 */
	public function createRegistrationCreatesOldAndNewRegistrationModelForTheSameUid() {
		// Drops the non-saving mapper so that the registration mapper (once we
		// use it) actually saves the registration.
		tx_oelib_MapperRegistry::purgeInstance();
		tx_oelib_MapperRegistry::getInstance()->activateTestingMode(
			$this->testingFramework
		);
		$this->testingFramework->markTableAsDirty('tx_seminars_seminars');

		$this->createAndLogInFrontEndUser();

		$plugin = new tx_seminars_pi1();
		$plugin->cObj = $GLOBALS['TSFE']->cObj;
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager',
			array(
				'notifyAttendee', 'notifyOrganizers',
				'sendAdditionalNotification', 'setRegistrationData'
			)
		);

		$registration = $fixture->createRegistration(
			$this->seminar, array(), $plugin
		);

		$uid = $fixture->getRegistration()->getUid();
		// @TODO: This line needs to be removed once createRegistration uses
		// the data mapper to save the registration.
		tx_oelib_db::delete('tx_seminars_attendances', 'uid = ' . $uid);

		$this->assertEquals(
			$registration->getUid(),
			$fixture->getRegistration()->getUid()
		);

		$fixture->__destruct();
		$plugin->__destruct();
	}

	/**
	 * @test
	 */
	public function createRegistrationCallsSeminarRegistrationCreatedHook() {
		$this->createAndLoginFrontEndUser();

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('seminarRegistrationCreated'));
		// We cannot test for the expected parameters because the registration
		// instance does not exist yet at this point.
		$hook->expects($this->once())->method('seminarRegistrationCreated');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
			[$hookClass] = $hookClass;

		$plugin = new tx_seminars_pi1();
		$plugin->cObj = $GLOBALS['TSFE']->cObj;
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager',
			array(
				'notifyAttendee', 'notifyOrganizers',
				'sendAdditionalNotification', 'setRegistrationData'
			)
		);

		$fixture->createRegistration(
			$this->seminar, array(), $plugin
		);

		$uid = $fixture->getRegistration()->getUid();

		$fixture->__destruct();
		$plugin->__destruct();

		// @TODO Remove this delete once the registration is saved by the data
		// mapper.
		tx_oelib_db::delete('tx_seminars_attendances', 'uid = ' . $uid);
	}


	///////////////////////////////////////////
	// Tests concerning setRegistrationData()
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function setRegistrationDataForPositiveSeatsSetsSeats() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('seats' => '3')
		);

		$this->assertEquals(
			3,
			$registration->getSeats()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingSeatsSetsOneSeat() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			1,
			$registration->getSeats()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForZeroSeatsSetsOneSeat() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('seats' => '0')
		);

		$this->assertEquals(
			1,
			$registration->getSeats()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNegativeSeatsSetsOneSeat() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('seats' => '-1')
		);

		$this->assertEquals(
			1,
			$registration->getSeats()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForRegisteredThemselvesOneSetsItToTrue() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('registered_themselves' => '1')
		);

		$this->assertTrue(
			$registration->hasRegisteredThemselves()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForRegisteredThemselvesZeroSetsItToFalse() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('registered_themselves' => '0')
		);

		$this->assertFalse(
			$registration->hasRegisteredThemselves()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForRegisteredThemselvesMissingSetsItToFalse() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertFalse(
			$registration->hasRegisteredThemselves()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePricePutsSelectedPriceCodeToPrice() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getAvailablePrices')
		);
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12, 'special' => 3)));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'special')
		);

		$this->assertEquals(
			'special',
			$registration->getPrice()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedNotAvailablePricePutsFirstPriceCodeToPrice() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getAvailablePrices')
		);
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'early_bird_regular')
		);

		$this->assertEquals(
			'regular',
			$registration->getPrice()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNoSelectedPricePutsFirstPriceCodeToPrice() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getAvailablePrices')
		);
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'regular',
			$registration->getPrice()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNoSelectedAndOnlyFreeRegularPriceAvailablePutsRegularPriceCodeToPrice() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getAvailablePrices')
		);
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 0)));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'regular',
			$registration->getPrice()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForOneSeatsCalculatesTotalPriceFromSelectedPriceAndSeats() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getAvailablePrices')
		);
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'regular', 'seats' => '1')
		);

		$this->assertEquals(
			12.0,
			$registration->getTotalPrice()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForTwoSeatsCalculatesTotalPriceFromSelectedPriceAndSeats() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getAvailablePrices')
		);
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'regular', 'seats' => '2')
		);

		$this->assertEquals(
			24.0,
			$registration->getTotalPrice()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyAttendeesNamesSetsAttendeesNames() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('attendees_names' => 'John Doe' . LF . 'Jane Doe')
		);

		$this->assertEquals(
			'John Doe' . LF . 'Jane Doe',
			$registration->getAttendeesNames()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromAttendeesNames() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('attendees_names' => 'John <em>Doe</em>')
		);

		$this->assertEquals(
			'John Doe',
			$registration->getAttendeesNames()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyAttendeesNamesSetsEmptyAttendeesNames() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('attendees_names' => '')
		);

		$this->assertEquals(
			'',
			$registration->getAttendeesNames()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingAttendeesNamesSetsEmptyAttendeesNames() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'',
			$registration->getAttendeesNames()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForPositiveKidsSetsNumberOfKids() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('kids' => '3')
		);

		$this->assertEquals(
			3,
			$registration->getKids()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingKidsSetsZeroKids() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			0,
			$registration->getKids()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForZeroKidsSetsZeroKids() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('kids' => '0')
		);

		$this->assertEquals(
			0,
			$registration->getKids()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNegativeKidsSetsZeroKids() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('kids' => '-1')
		);

		$this->assertEquals(
			0,
			$registration->getKids()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneSetsIt() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('method_of_payment' => $paymentMethod->getUid())
		);

		$this->assertSame(
			$paymentMethod,
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePaymentMethodFromTwoSetsIt() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod1 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethod2 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod1);
		$paymentMethods->add($paymentMethod2);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('method_of_payment' => $paymentMethod2->getUid())
		);

		$this->assertSame(
			$paymentMethod2,
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneForFreeEventsSetsNoPaymentMethod() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 0)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('method_of_payment' => $paymentMethod->getUid())
		);

		$this->assertNull(
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingPaymentMethodAndNoneAvailableSetsNone() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 0)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue(new tx_oelib_List()));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertNull(
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingPaymentMethodAndTwoAvailableSetsNone() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod1 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethod2 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod1);
		$paymentMethods->add($paymentMethod2);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertNull(
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingPaymentMethodAndOneAvailableSetsIt() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertSame(
			$paymentMethod,
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForUnavailablePaymentMethodAndTwoAvailableSetsNone() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod1 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethod2 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod1);
		$paymentMethods->add($paymentMethod2);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('method_of_payment' =>
				max($paymentMethod1->getUid(), $paymentMethod2->getUid()) + 1
			)
		);

		$this->assertNull(
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForUnavailablePaymentMethodAndOneAvailableSetsAvailable() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getAvailablePrices', 'getPaymentMethods')
		);
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('method_of_payment' => $paymentMethod->getUid() + 1)
		);

		$this->assertSame(
			$paymentMethod,
			$registration->getPaymentMethod()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyAccountNumberSetsAccountNumber() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_number' => '123 455 ABC')
		);

		$this->assertEquals(
			'123 455 ABC',
			$registration->getAccountNumber()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromAccountNumber() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_number' => '123 <em>455</em> ABC')
		);

		$this->assertEquals(
			'123 455 ABC',
			$registration->getAccountNumber()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInAccountNumber() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('account_number' => '123' . CRLF . '455'  . TAB . ' ABC')
		);

		$this->assertEquals(
			'123 455 ABC',
			$registration->getAccountNumber()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyAccountNumberSetsEmptyAccountNumber() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_number' => '')
		);

		$this->assertEquals(
			'',
			$registration->getAccountNumber()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingAccountNumberSetsEmptyAccountNumber() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'',
			$registration->getAccountNumber()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyBankCodeSetsBankCode() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_code' => '123 455 ABC')
		);

		$this->assertEquals(
			'123 455 ABC',
			$registration->getBankCode()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromBankCode() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_code' => '123 <em>455</em> ABC')
		);

		$this->assertEquals(
			'123 455 ABC',
			$registration->getBankCode()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInBankCode() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('bank_code' => '123' . CRLF . '455'  . TAB . ' ABC')
		);

		$this->assertEquals(
			'123 455 ABC',
			$registration->getBankCode()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyBankCodeSetsEmptyBankCode() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_code' => '')
		);

		$this->assertEquals(
			'',
			$registration->getBankCode()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingBankCodeSetsEmptyBankCode() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'',
			$registration->getBankCode()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyBankNameSetsBankName() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_name' => 'Swiss Tax Protection')
		);

		$this->assertEquals(
			'Swiss Tax Protection',
			$registration->getBankName()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromBankName() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_name' => 'Swiss <em>Tax</em> Protection')
		);

		$this->assertEquals(
			'Swiss Tax Protection',
			$registration->getBankName()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInBankName() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('bank_name' => 'Swiss' . CRLF . 'Tax'  . TAB . ' Protection')
		);

		$this->assertEquals(
			'Swiss Tax Protection',
			$registration->getBankName()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyBankNameSetsEmptyBankName() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_name' => '')
		);

		$this->assertEquals(
			'',
			$registration->getBankName()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingBankNameSetsEmptyBankName() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'',
			$registration->getBankName()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyAccountOwnerSetsAccountOwner() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_owner' => 'John Doe')
		);

		$this->assertEquals(
			'John Doe',
			$registration->getAccountOwner()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromAccountOwner() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_owner' => 'John <em>Doe</em>')
		);

		$this->assertEquals(
			'John Doe',
			$registration->getAccountOwner()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInAccountOwner() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('account_owner' => 'John' . CRLF . TAB . ' Doe')
		);

		$this->assertEquals(
			'John Doe',
			$registration->getAccountOwner()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyAccountOwnerSetsEmptyAccountOwner() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_owner' => '')
		);

		$this->assertEquals(
			'',
			$registration->getAccountOwner()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingAccountOwnerSetsEmptyAccountOwner() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'',
			$registration->getAccountOwner()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyCompanySetsCompany() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('company' => 'Business Ltd.' . LF . 'Tom, Dick & Harry')
		);

		$this->assertEquals(
			'Business Ltd.' . LF . 'Tom, Dick & Harry',
			$registration->getCompany()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromCompany() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('company' => 'Business <em>Ltd.</em>')
		);

		$this->assertEquals(
			'Business Ltd.',
			$registration->getCompany()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyCompanySetsEmptyCompany() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('company' => '')
		);

		$this->assertEquals(
			'',
			$registration->getCompany()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingCompanySetsEmptyCompany() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			'',
			$registration->getCompany()
		);

		$fixture->__destruct();
	}




	/**
	 * @test
	 */
	public function setRegistrationDataForMaleGenderSetsGender() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('gender' => (string) tx_oelib_Model_FrontEndUser::GENDER_MALE)
		);

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_MALE,
			$registration->getGender()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForFemaleGenderSetsGender() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('gender' => (string) tx_oelib_Model_FrontEndUser::GENDER_FEMALE)
		);

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$registration->getGender()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForInvalidIntegerGenderSetsUnknownGender() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('gender' => '42')
		);

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForInvalidStringGenderSetsUnknownGender() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('gender' => 'Mr. Fantastic')
		);

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyGenderSetsUnknownGender() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration,
			array('gender' => '')
		);

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingGenderSetsUnknownGender() {
		$fixture = tx_oelib_ObjectFactory::make(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);

		$fixture->__destruct();
	}
}
?>