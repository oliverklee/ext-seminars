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
 */
class Tx_seminars_Service_RegistrationManagerTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_seminars_registrationmanager
	 */
	protected $fixture = NULL;

	/**
	 * @var tx_oelib_testingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var tx_seminars_seminarchild a seminar to which the fixture relates
	 */
	protected $seminar = NULL;

	/**
	 * @var int the UID of a fake front-end user
	 */
	protected $frontEndUserUid = 0;

	/**
	 * @var int UID of a fake login page
	 */
	protected $loginPageUid = 0;

	/**
	 * @var int UID of a fake registration page
	 */
	protected $registrationPageUid = 0;

	/**
	 * @var tx_seminars_FrontEnd_DefaultController a front-end plugin
	 */
	protected $pi1 = NULL;

	/**
	 * @var tx_seminars_seminarchild a fully booked seminar
	 */
	protected $fullyBookedSeminar = NULL;

	/**
	 * @var tx_seminars_seminarchild a seminar
	 */
	protected $cachedSeminar = NULL;

	/**
	 * backed-up extension configuration of the TYPO3 configuration variables
	 *
	 * @var array
	 */
	protected $extConfBackup = array();

	/**
	 * backed-up T3_VAR configuration
	 *
	 * @var array
	 */
	protected $t3VarBackup = array();

	/**
	 * @var tx_seminars_Service_SingleViewLinkBuilder
	 */
	protected $linkBuilder = NULL;

	/**
	 * @var Tx_Oelib_EmailCollector
	 */
	protected $mailer = NULL;

	protected function setUp() {
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		/** @var Tx_Oelib_MailerFactory $mailerFactory */
		$mailerFactory = t3lib_div::makeInstance('Tx_Oelib_MailerFactory');
		$mailerFactory->enableTestMode();
		$this->mailer = $mailerFactory->getMailer();

		tx_seminars_registrationchild::purgeCachedSeminars();
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger('eMailFormatForAttendees', tx_seminars_registrationmanager::SEND_TEXT_MAIL);
		$configurationRegistry = tx_oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('plugin.tx_seminars', new tx_oelib_Configuration());
		$configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.de', new tx_oelib_Configuration());
		$configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new tx_oelib_Configuration());
		$configurationRegistry->set('config', new tx_oelib_Configuration());

		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'test organizer',
				'email' => 'mail@example.com',
				'email_footer' => 'organizer footer',
			)
		);

		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
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

		$this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $seminarUid, $organizerUid);

		tx_oelib_templateHelper::setCachedConfigurationValue(
			'templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);

		$this->seminar = new tx_seminars_seminarchild($seminarUid);
		$this->fixture = tx_seminars_registrationmanager::getInstance();

		$this->linkBuilder = $this->getMock('tx_seminars_Service_SingleViewLinkBuilder', array('createAbsoluteUrlForEvent'));
		$this->linkBuilder->expects($this->any())
			->method('createAbsoluteUrlForEvent')
			->will($this->returnValue('http://singleview.example.com/'));
		$this->fixture->injectLinkBuilder($this->linkBuilder);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
	}


	/*
	 * Utility functions
	 */

	/**
	 * Creates a dummy login page and registration page and stores their UIDs
	 * in $this->loginPageUid and $this->registrationPageUid.
	 *
	 * In addition, it provides the fixture's configuration with the UIDs.
	 *
	 * @return void
	 */
	private function createFrontEndPages() {
		$this->loginPageUid = $this->testingFramework->createFrontEndPage();
		$this->registrationPageUid
			= $this->testingFramework->createFrontEndPage();

		$this->pi1 = new tx_seminars_FrontEnd_DefaultController();

		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'loginPID' => $this->loginPageUid,
				'registerPID' => $this->registrationPageUid,
			)
		);
	}

	/**
	 * Creates a FE user, stores it UID in $this->frontEndUserUid and logs it in.
	 *
	 * @return void
	 */
	private function createAndLogInFrontEndUser() {
		$this->frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
	}

	/**
	 * Creates a seminar which is booked out.
	 *
	 * @return void
	 */
	private function createBookedOutSeminar() {
		$this->fullyBookedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
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
	 * A new front-end user will be created and the event in $this->seminar will be used.
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
				'food' => 'something nice to eat',
				'accommodation' => 'a nice, dry place',
				'interests' => 'learning Ruby on Rails',
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

		if (!class_exists($testingClassName, FALSE)) {
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

	/**
	 * Extracts the HTML body from the first sent e-mail.
	 *
	 * @return string
	 */
	protected function getEmailHtmlPart() {
		$children = $this->mailer->getFirstSentEmail()->getChildren();
		/** @var Swift_Mime_MimeEntity $firstChild */
		$firstChild = $children[0];

		return $firstChild->getBody();
	}


	/*
	 * Tests for the utility functions
	 */

	/**
	 * @test
	 */
	public function createFrontEndPagesCreatesNonZeroLoginPageUid() {
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
		$this->assertInstanceOf(
			'tx_seminars_FrontEnd_DefaultController',
			$this->pi1
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

		$this->assertInstanceOf(
			'tx_seminars_seminar',
			$this->fullyBookedSeminar
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

		$this->assertInstanceOf(
			'tx_seminars_registrationmanager',
			$instance
		);
	}


	/*
	 * Tests regarding the Singleton property.
	 */

	/**
	 * @test
	 */
	public function getInstanceReturnsRegistrationManagerInstance() {
		$this->assertInstanceOf(
			'tx_seminars_registrationmanager',
			tx_seminars_registrationmanager::getInstance()
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


	/*
	 * Tests for the link to the registration page
	 */

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkTag() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'<a ',
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserContainsRedirectWithEventUid() {
		$this->testingFramework->logoutFrontEndUser();
		$this->createFrontEndPages();

		$this->assertContains(
			'redirect_url',
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
		$this->assertContains(
			'%255Bseminar%255D%3D' . $this->seminar->getUid(),
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkTag() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'<a ',
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'?id=' . $this->registrationPageUid,
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar
			)
		);
		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	/**
	 * @test
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=4504
	 */
	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserAndSeparateDetailsPageCreatesLinkToRegistrationPage() {
		$this->createFrontEndPages();

		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$this->seminar->setDetailsPage($detailsPageUid);

		$this->createAndLogInFrontEndUser();

		$this->assertContains(
			'?id=' . $this->registrationPageUid,
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserDoesNotContainRedirect() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->assertNotContains(
			'redirect_url',
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndSeminarWithoutDateHasLinkWithPrebookingLabel() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setBeginDate(0);

		$this->assertContains(
			$this->pi1->translate('label_onlinePrebooking'),
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function getLinkToRegistrationOrLoginPageWithLoggedInUserSeminarWithoutDateAndNoVacanciesContainsRegistrationLabel() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setBeginDate(0);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setAttendancesMax(5);

		$this->assertContains(
			$this->pi1->translate('label_onlineRegistration'),
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndFullyBookedSeminarWithQueueContainsQueueRegistrationLabel() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertContains(
			sprintf($this->pi1->translate('label_onlineRegistrationOnQueue'), 0),
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function getLinkToRegistrationOrLoginPageWithLoggedOutUserAndFullyBookedSeminarWithQueueContainsQueueRegistrationLabel() {
		$this->createFrontEndPages();
		$this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
		$this->seminar->setNumberOfAttendances(5);
		$this->seminar->setAttendancesMax(5);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertContains(
			sprintf($this->pi1->translate('label_onlineRegistrationOnQueue'), 0),
			$this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
		);
	}


	/*
	 * Tests for the getRegistrationLink function
	 */

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

	/**
	 * @test
	 */
	public function getRegistrationLinkForLoggedInUserAndFullyBookedSeminarReturnsEmptyString() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->createBookedOutSeminar();

		$this->assertSame(
			'',
			$this->fixture->getRegistrationLink($this->pi1, $this->fullyBookedSeminar)
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationLinkForLoggedOutUserAndFullyBookedSeminarReturnsEmptyString() {
		$this->createFrontEndPages();

		$this->createBookedOutSeminar();

		$this->assertSame(
			'',
			$this->fixture->getRegistrationLink($this->pi1, $this->fullyBookedSeminar)
		);
	}

	public function testGetRegistrationLinkForBeginDateBeforeCurrentDateReturnsEmptyString() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'title' => 'test event',
					'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
					'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
					'attendees_max' => 10
				)
			)
		);

		$this->assertSame(
			'',
			$this->fixture->getRegistrationLink($this->pi1, $this->cachedSeminar)
		);

	}

	public function testGetRegistrationLinkForAlreadyEndedRegistrationDeadlineReturnsEmptyString() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'title' => 'test event',
					'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
					'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
					'attendees_max' => 10,
					'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
				)
			)
		);

		$this->assertSame(
			'',
			$this->fixture->getRegistrationLink($this->pi1, $this->cachedSeminar)
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationLinkForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsLinkWithSeminarUid() {
		$this->createFrontEndPages();
		$this->createAndLogInFrontEndUser();
		$this->seminar->setUnlimitedVacancies();

		$this->assertContains(
			'[seminar]=' . $this->seminar->getUid(),
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationLinkForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsLoginLink() {
		$this->createFrontEndPages();
		$this->seminar->setUnlimitedVacancies();

		$this->assertContains(
			'?id=' . $this->loginPageUid,
			$this->fixture->getRegistrationLink($this->pi1, $this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationLinkForLoggedInUserAndFullyBookedSeminarWithQueueEnabledReturnsLinkWithSeminarUid() {
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

	/**
	 * @test
	 */
	public function getRegistrationLinkForLoggedOutUserAndFullyBookedSeminarWithQueueEnabledReturnsLoginLink() {
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


	/*
	 * Tests concerning canRegisterIfLoggedIn
	 */

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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
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
		$user = tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
		$hook->expects($this->once())->method('canRegisterForSeminar')->with($this->seminar, $user);

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
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->fixture->canRegisterIfLoggedIn($this->seminar);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInForRegistrationPossibleAndHookReturningTrueReturnsTrue() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
		$hook->expects($this->once())->method('canRegisterForSeminar')->will($this->returnValue(TRUE));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

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
		$hook->expects($this->once())->method('canRegisterForSeminar')->will($this->returnValue(FALSE));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->assertFalse(
			$this->fixture->canRegisterIfLoggedIn($this->seminar)
		);
	}


	/*
	 * Tests concerning canRegisterIfLoggedInMessage
	 */

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarRegistrationOpenReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInUserAndRegistrationOpenReturnsEmptyString() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertSame(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredUserReturnsAlreadyRegisteredMessage() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->assertSame(
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
			'tx_seminars_attendances',
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->assertSame(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedInButBlockedUserReturnsUserIsBlockedMessage() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'user' => $this->testingFramework->createAndLoginFrontEndUser(),
				'seminar' => $this->seminar->getUid(),
			)
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
					'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
					'attendees_max' => 10,
					'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				)
			)
		);

		$this->assertSame(
			$this->fixture->translate('message_userIsBlocked'),
			$this->fixture->canRegisterIfLoggedInMessage($this->cachedSeminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarReturnsFullyBookedMessage() {
		$this->createBookedOutSeminar();

		$this->assertSame(
			'message_noVacancies',
			$this->fixture->canRegisterIfLoggedInMessage($this->fullyBookedSeminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndCanceledSeminarReturnsSeminarCancelledMessage() {
		$this->seminar->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		$this->assertSame(
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

		$this->assertSame(
			'message_noRegistrationNecessary',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsEmptyString() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertSame(
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

		$this->assertSame(
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

		$this->assertSame(
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

		$this->assertSame(
			'',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForRegistrationPossibleCallsCanUserRegisterForSeminarMessageHook() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$user = tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
		$hook->expects($this->once())->method('canRegisterForSeminarMessage')->with($this->seminar, $user);

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
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->fixture->canRegisterIfLoggedInMessage($this->seminar);
	}

	/**
	 * @test
	 */
	public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsStringFromHook() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$hookClass = uniqid('tx_registrationHook');
		$hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
		$hook->expects($this->once())->method('canRegisterForSeminarMessage')->will($this->returnValue('Hello world!'));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->assertSame(
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
		$hook1->expects($this->any())->method('canRegisterForSeminarMessage')->will($this->returnValue('message 1'));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass1] = $hook1;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass1] = $hookClass1;

		$hookClass2 = uniqid('tx_registrationHook');
		$hook2 = $this->getMock($hookClass2, array('canRegisterForSeminarMessage'));
		$hook2->expects($this->any())->method('canRegisterForSeminarMessage')->will($this->returnValue('message 2'));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass2] = $hook2;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass2] = $hookClass2;

		$this->assertSame(
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
		$hook1->expects($this->any())->method('canRegisterForSeminarMessage')->will($this->returnValue(''));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass1] = $hook1;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass1] = $hookClass1;

		$hookClass2 = uniqid('tx_registrationHook');
		$hook2 = $this->getMock($hookClass2, array('canRegisterForSeminarMessage'));
		$hook2->expects($this->any())->method('canRegisterForSeminarMessage')->will($this->returnValue('message 2'));
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass2] = $hook2;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass2] = $hookClass2;

		$this->assertSame(
			'message 2',
			$this->fixture->canRegisterIfLoggedInMessage($this->seminar)
		);
	}


	/*
	 * Test concerning userFulfillsRequirements
	 */

	public function testUserFulfillsRequirementsForEventWithoutRequirementsReturnsTrue() {
		$this->testingFramework->createAndLogInFrontEndUser();

		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredDateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredTopicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $requiredDateUid,
				'user' => $this->testingFramework->createAndLogInFrontEndUser(),
			)
		);

		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$this->assertFalse(
			$this->fixture->userFulfillsRequirements($this->cachedSeminar)
		);
	}


	/*
	 * Tests concerning getMissingRequiredTopics
	 */

	public function testGetMissingRequiredTopicsReturnsSeminarBag() {
		$this->assertInstanceOf(
			'tx_seminars_Bag_Event',
			$this->fixture->getMissingRequiredTopics($this->seminar)
		);
	}

	public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsOneItem() {
		$this->testingFramework->createAndLogInFrontEndUser();
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
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertSame(
			1,
			$missingTopics->count()
		);
	}

	public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsRequiredTopic() {
		$this->testingFramework->createAndLogInFrontEndUser();
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
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertSame(
			$requiredTopicUid,
			$missingTopics->current()->getUid()
		);
	}

	public function testGetMissingRequiredTopicsForTopicWithOneTwoNotFulfilledRequirementReturnsTwoItems() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$requiredTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredTopicUid1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid1, 'requirements'
		);

		$requiredTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredTopicUid2,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid2, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertSame(
			2,
			$missingTopics->count()
		);
	}

	public function testGetMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledReturnsUnfulfilledTopic() {
		$userUid = $this->testingFramework->createAndLogInFrontEndUser();
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$requiredTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredDateUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredTopicUid1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $requiredDateUid1, 'user' => $userUid)
		);

		$requiredTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid2, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertSame(
			$requiredTopicUid2,
			$missingTopics->current()->getUid()
		);
	}

	public function testGetMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledDoesNotReturnFulfilledTopic() {
		$userUid = $this->testingFramework->createAndLogInFrontEndUser();
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$requiredTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredDateUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $requiredTopicUid1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $requiredDateUid1, 'user' => $userUid)
		);

		$requiredTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid2, 'requirements'
		);

		$this->cachedSeminar = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);
		$missingTopics = $this->fixture->getMissingRequiredTopics(
			$this->cachedSeminar
		);

		$this->assertSame(
			1,
			$missingTopics->count()
		);
	}


	/*
	 * Tests concerning removeRegistration
	 */

	/**
	 * @test
	 */
	public function removeRegistrationHidesRegistrationOfUser() {
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();
		$seminarUid = $this->seminar->getUid();
		$this->createFrontEndPages();

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
				'hidden' => 0,
			)
		);

		$this->fixture->removeRegistration($registrationUid, $this->pi1);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
			)
		);
		$queueRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
				'seats' => 1,
				'registration_queue' => 1,
			)
		);

		$this->fixture->removeRegistration($registrationUid, $this->pi1);

		$this->testingFramework->existsRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'user' => $userUid,
				'seminar' => $seminarUid,
				'seats' => 1,
				'registration_queue' => 1,
			)
		);

		$this->fixture->removeRegistration($registrationUid, $this->pi1);
	}


	/*
	 * Tests concerning canRegisterSeats
	 */

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventAndZeroSeatsGivenReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventAndOneSeatGivenReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventAndEmptyStringGivenReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, '')
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventAndInvalidStringGivenReturnsFalse() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 'foo')
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithOneVacancyAndZeroSeatsGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(1);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}


	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithOneVacancyAndOneSeatGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(1);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithOneVacancyAndTwoSeatsGivenReturnsFalse() {
		$this->seminar->setAttendancesMax(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithOneVacancyAndEmptyStringGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(1);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, '')
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithOneVacancyAndInvalidStringGivenReturnsFalse() {
		$this->seminar->setAttendancesMax(1);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 'foo')
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithTwoVacanciesAndOneSeatGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(2);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithTwoVacanciesAndTwoSeatsGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(2);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithTwoVacanciesAndThreeSeatsGivenReturnsFalse() {
		$this->seminar->setAttendancesMax(2);

		$this->assertFalse(
			$this->fixture->canRegisterSeats($this->seminar, 3)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithUnlimitedVacanciesAndZeroSeatsGivenReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithUnlimitedVacanciesAndOneSeatGivenReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithUnlimitedVacanciesAndTwoSeatsGivenReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithUnlimitedVacanciesAndFortytwoSeatsGivenReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 42)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventWithQueueAndZeroSeatsGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 0)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventWithQueueAndOneSeatGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 1)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventWithQueueAndTwoSeatsGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 2)
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForFullyBookedEventWithQueueAndEmptyStringGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, '')
		);
	}

	/**
	 * @test
	 */
	public function canRegisterSeatsForEventWithTwoVacanciesAndWithQueueAndFortytwoSeatsGivenReturnsTrue() {
		$this->seminar->setAttendancesMax(2);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->canRegisterSeats($this->seminar, 42)
		);
	}


	/*
	 * Tests concerning notifyAttendee
	 */

	/**
	 * @test
	 */
	public function notifyAttendeeSendsMailToAttendeesMailAddress() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertArrayHasKey(
			'foo@bar.com',
			$this->mailer->getFirstSentEmail()->getTo()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForAttendeeWithoutMailAddressNotSendsEmail() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
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

		$this->assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForSendConfirmationTrueCallsModifyThankYouEmailHook() {
		$hookClass = uniqid('tx_seminars_registrationHook');
		$hook = $this->getMock($hookClass, array('modifyThankYouEmail'));
		$hook->expects($this->once())->method('modifyThankYouEmail');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForSendConfirmationFalseNotCallsModifyThankYouEmailHook() {
		$hookClass = uniqid('tx_seminars_registrationHook');
		$hook = $this->getMock($hookClass, array('modifyThankYouEmail'));
		$hook->expects($this->never())->method('modifyThankYouEmail');

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->fixture->setConfigurationValue('sendConfirmation', FALSE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForSendConfirmationTrueAndPlainTextEmailCallsModifyAttendeeEmailTextHookOnce() {
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger('eMailFormatForAttendees', tx_seminars_registrationmanager::SEND_TEXT_MAIL);

		$registration = $this->createRegistration();

		$hook = $this->getMock('tx_seminars_Interface_Hook_Registration', array());
		$hookClass = get_class($hook);
		$hook->expects($this->once())->method('modifyAttendeeEmailText')->with($registration, $this->anything());

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForSendConfirmationTrueAndHtmlEmailCallsModifyAttendeeEmailTextHookTwice() {
		tx_oelib_configurationProxy::getInstance('seminars')
			->setAsInteger('eMailFormatForAttendees', tx_seminars_registrationmanager::SEND_HTML_MAIL);

		$registration = $this->createRegistration();

		$hook = $this->getMock('tx_seminars_Interface_Hook_Registration', array());
		$hookClass = get_class($hook);
		$hook->expects($this->exactly(2))->method('modifyAttendeeEmailText')->with($registration, $this->anything());;

		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailSubjectContainsConfirmationSubject() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			$this->fixture->translate('email_confirmationSubject'),
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailBodyContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'test event',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailBodyContainsRegistrationFood() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'something nice to eat',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailBodyContainsRegistrationAccommodation() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'a nice, dry place',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailBodyContainsRegistrationInterests() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'learning Ruby on Rails',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailSubjectContainsEventTitle() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'test event',
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeSetsOrganizerAsSender() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertSame(
			array('mail@example.com' => 'test organizer'),
			$this->mailer->getFirstSentEmail()->getFrom()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'<html',
			$this->getEmailHtmlPart()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForTextMailSetDoesNotHaveHtmlBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertSame(
			array(),
			$this->mailer->getFirstSentEmail()->getChildren()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForTextMailSetHasNoUnreplacedMarkers() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertNotContains(
			'###',
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertNotContains(
			'###',
			$this->getEmailHtmlPart()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'<html',
			$this->getEmailHtmlPart()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertSame(
			array(),
			$this->mailer->getFirstSentEmail()->getChildren()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'foo_user',
			$this->getEmailHtmlPart()
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
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);
		$seminarLink = 'http://singleview.example.com/';

		$this->assertContains(
			'<a href="' . $seminarLink,
			$this->getEmailHtmlPart()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeAppendsOrganizersFooterToMailBody() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			LF . '-- ' . LF . 'organizer footer',
			$this->mailer->getFirstSentEmail()->getBody()
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

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertNotContains(
			$this->fixture->translate('label_planned_disclaimer'),
			$this->mailer->getFirstSentEmail()->getBody()
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

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertNotContains(
			$this->fixture->translate('label_planned_disclaimer'),
			$this->mailer->getFirstSentEmail()->getBody()
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

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			$this->fixture->translate('label_planned_disclaimer'),
			$this->mailer->getFirstSentEmail()->getBody()
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

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertNotContains(
			$this->fixture->translate('label_planned_disclaimer'),
			$this->mailer->getFirstSentEmail()->getBody()
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

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'style=',
			$this->getEmailHtmlPart()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeMailBodyCanContainAttendeesNames() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$registration->setAttendeesNames('foo1 foo2');
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'foo1 foo2',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForPlainTextMailEnumeratesAttendeesNames() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$registration->setAttendeesNames('foo1' . LF . 'foo2');
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'1. foo1' . LF . '2. foo2',
			$this->mailer->getFirstSentEmail()->getBody()
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

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$registration->setAttendeesNames('foo1' . LF . 'foo2');
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertRegExp(
			'/\<ol>.*<li>foo1<\/li>.*<li>foo2<\/li>.*<\/ol>/s',
			$this->getEmailHtmlPart()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'foo_place',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'foo_street',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForEventWithNoPlaceSendsWillBeAnnouncedMessage() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'place_title' . LF . 'place_address',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'place_title<br>place_address',
			$this->getEmailHtmlPart()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'place_title' . LF . 'place_address',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address1 address2',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address1 address2',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address1 address2',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address1 address2',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address1 address2',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address1 address2',
			$this->getEmailHtmlPart()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address1 address2 address3',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'footown',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'12345 footown',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			$country->getLocalShortName(),
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address' . LF . 'footown',
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'address<br>footown',
			$this->getEmailHtmlPart()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			'footown, ' . $country->getLocalShortName(),
			$this->mailer->getFirstSentEmail()->getBody()
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
			'tx_seminars_seminars', $this->seminar->getUid(), $uid, 'place'
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$registration = $this->createRegistration();
		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertNotContains(
			'footown,',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}


	/*
	 * Tests concerning the salutation
	 */

	/**
	 * @test
	 */
	public function notifyAttendeeForInformalSalutationContainsInformalSalutation() {
		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('salutation', 'informal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_informal'),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndGenderUnknownContainsFormalUnknownSalutation() {
		if (Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if there is no FrontEndUser.gender field.');
		}

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com')
		);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_formal_2'),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndGenderMaleContainsFormalMaleSalutation() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
		}

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com', 'gender' => 0)
		);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_formal_0'),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyAttendeeForFormalSalutationAndGenderFemaleContainsFormalFemaleSalutation() {
		if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
			$this->markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
		}

		$this->fixture->setConfigurationValue('sendConfirmation', TRUE);
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->setAsString('salutation', 'formal');
		$registration = $this->createRegistration();
		$this->testingFramework->changeRecord(
			'fe_users', $registration->getFrontEndUser()->getUid(),
			array('email' => 'foo@bar.com', 'gender' => 1)
		);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			tx_oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_formal_1'),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_confirmationHello_formal'),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee($registration, $pi1);

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_confirmationHello_informal'),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnUnregistration'
		);

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnUnregistrationHello_formal'
				),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnUnregistration'
		);

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnUnregistrationHello_informal'
				),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnRegistrationForQueue'
		);

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnRegistrationForQueueHello_formal'
				),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnRegistrationForQueue'
		);

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnRegistrationForQueueHello_informal'
				),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnQueueUpdate'
		);

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnQueueUpdateHello_formal'
				),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
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
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$this->fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnQueueUpdate'
		);

		$this->assertContains(
			sprintf(
				$this->fixture->translate(
					'email_confirmationOnQueueUpdateHello_informal'
				),
				$this->seminar->getTitle()
			),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}


	/*
	 * Tests concerning the unregistration notice
	 */

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
			'tx_seminars_seminars', $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			)
		);
		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnUnregistration'
		);
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
			'tx_seminars_seminars', $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			)
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$fixture->notifyAttendee($registration, $pi1);
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
			'tx_seminars_seminars', $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			)
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$fixture->notifyAttendee($registration, $pi1);
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
			'tx_seminars_seminars', $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'queue_size' => 1,
			)
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_attendances', $registration->getUid(),
			array('registration_queue' => 1)
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnRegistrationForQueue'
		);
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
			'tx_seminars_seminars', $this->seminar->getUid(), array(
				'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
				'queue_size' => 1,
			)
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_attendances', $registration->getUid(),
			array('registration_queue' => 1)
		);

		$pi1 = new tx_seminars_FrontEnd_DefaultController();
		$pi1->init();

		$fixture->notifyAttendee(
			$registration, $pi1, 'confirmationOnQueueUpdate'
		);
	}


	/*
	 * Tests regarding the notification of organizers
	 */

	/**
	 * @test
	 */
	public function notifyOrganizersUsesOrganizerAsFrom() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);

		$this->assertSame(
			array('mail@example.com' => 'test organizer'),
			$this->mailer->getFirstSentEmail()->getFrom()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersUsesOrganizerAsTo() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);

		$this->assertArrayHasKey(
			'mail@example.com',
			$this->mailer->getFirstSentEmail()->getTo()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersIncludesHelloIfNotHidden() {
		$registration = $this->createRegistration();
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
		$this->fixture->setConfigurationValue(
			'hideFieldsInNotificationMail', ''
		);

		$this->fixture->notifyOrganizers($registration);

		$this->assertContains(
			'Hello',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersForEventWithOneVacancyShowsVacanciesLabelWithVacancyNumber() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
		$this->fixture->setConfigurationValue(
			'showSeminarFieldsInNotificationMail', 'vacancies'
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
			array('needs_registration' => 1, 'attendees_max' => 2)
		);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);

		$this->assertRegExp(
			'/' . $this->fixture->translate('label_vacancies') . ': 1$/',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersForEventWithUnlimitedVacanciesShowsVacanciesLabelWithUnlimtedLabel() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);
		$this->fixture->setConfigurationValue(
			'showSeminarFieldsInNotificationMail', 'vacancies'
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
			array('needs_registration' => 1, 'attendees_max' => 0)
		);

		$registration = $this->createRegistration();
		$this->fixture->notifyOrganizers($registration);

		$this->assertContains(
			$this->fixture->translate('label_vacancies') . ': ' .
				$this->fixture->translate('label_unlimited'),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersForRegistrationWithCompanyShowsLabelOfCompany() {
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

		$registration = new tx_seminars_registrationchild($registrationUid);
		$this->fixture->notifyOrganizers($registration);

		$this->assertContains(
			$this->fixture->translate('label_company'),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersForRegistrationWithCompanyShowsCompanyOfRegistration() {
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

		$registration = new tx_seminars_registrationchild($registrationUid);
		$this->fixture->notifyOrganizers($registration);

		$this->assertContains(
			'foo inc.',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersCallsModifyOrganizerNotificationEmailHookWithRegistration() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $this->seminar->getUid(), 'user' => $this->testingFramework->createFrontEndUser())
		);
		$registration = new tx_seminars_registrationchild($registrationUid);

		$hook = $this->getMock('tx_seminars_Interface_Hook_Registration');
		$hookClassName = get_class($hook);
		$hook->expects($this->once())->method('modifyOrganizerNotificationEmail')->with($registration, $this->anything());

		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;

		$this->fixture->notifyOrganizers($registration);
	}

	/**
	 * @test
	 */
	public function notifyOrganizersCallsModifyOrganizerNotificationEmailHookWithTemplate() {
		$this->fixture->setConfigurationValue('sendNotification', TRUE);

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $this->seminar->getUid(), 'user' => $this->testingFramework->createFrontEndUser())
		);
		$registration = new tx_seminars_registrationchild($registrationUid);

		$hook = $this->getMock('tx_seminars_Interface_Hook_Registration');
		$hookClassName = get_class($hook);
		$hook->expects($this->once())->method('modifyOrganizerNotificationEmail')
			->with($this->anything(), $this->isInstanceOf('Tx_Oelib_Template'));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;

		$this->fixture->notifyOrganizers($registration);
	}


	/*
	 * Tests concerning sendAdditionalNotification
	 */

	/**
	 * @test
	 */
	public function sendAdditionalNotificationCanSendEmailToOneOrganizer() {
		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);

		$this->assertArrayHasKey(
			'mail@example.com',
			$this->mailer->getFirstSentEmail()->getTo()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationCanSendEmailsToTwoOrganizers() {
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
			'tx_seminars_seminars', $this->seminar->getUid(),
			array('organizers' => 2)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);

		$this->assertSame(
			2,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationUsesTheFirstOrganizerAsSenderIfEmailIsSentToTwoOrganizers() {
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
			'tx_seminars_seminars', $this->seminar->getUid(),
			array('organizers' => 2)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);

		$sentEmails = $this->mailer->getSentEmails();

		$this->assertArrayHasKey(
			'mail@example.com',
			$sentEmails[0]->getFrom()
		);
		$this->assertArrayHasKey(
			'mail@example.com',
			$sentEmails[1]->getFrom()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForEventWithEnoughAttendancesSendsEnoughAttendancesMail() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
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

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_additionalNotificationEnoughRegistrationsSubject'),
				$this->seminar->getUid(), ''
			),
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForEventWithZeroAttendeesMinDoesNotSendAnyMail() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
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

		$this->assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutSubject() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
			array('attendees_max' => 1)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);

		$this->assertContains(
			sprintf(
				$this->fixture->translate('email_additionalNotificationIsFullSubject'),
				$this->seminar->getUid(), ''
			),
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutMessage() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
			array('attendees_max' => 1)
		);
		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);

		$this->assertContains(
			$this->fixture->translate('email_additionalNotificationIsFull'),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForEventWithNotEnoughAttendancesAndNotBookedOutSendsNoEmail() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
			array('attendees_min' => 5, 'attendees_max' => 5)
		);


		$fixture = new tx_seminars_registrationmanager();
		$fixture->setConfigurationValue(
			'templateFile',
			'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
		);

		$registration = $this->createRegistration();
		$fixture->sendAdditionalNotification($registration);

		$this->assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForEventWithEnoughAttendancesAndUnlimitedVacanciesSendsEmail() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
			array(
				'attendees_min' => 1,
				'attendees_max' => 0,
				'needs_registration' => 1
			)
		);

		$registration = $this->createRegistration();
		$this->fixture->sendAdditionalNotification($registration);

		$this->assertSame(
			1,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForEventWithEnoughAttendancesAndOneVacancyShowsVacanciesLabelWithVacancyNumber() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
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

		$this->assertRegExp(
			'/' . $this->fixture->translate('label_vacancies') . ': 1$/',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendAdditionalNotificationForEventWithEnoughAttendancesAndUnlimitedVacanciesShowsVacanciesLabelWithUnlimitedLabel() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->seminar->getUid(),
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

		$this->assertContains(
			$this->fixture->translate('label_vacancies') . ': ' . $this->fixture->translate('label_unlimited'),
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}


	/*
	 * Tests concerning allowsRegistrationByDate
	 */

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateAllowedReturnsTrue() {
		$this->seminar->setAllowRegistrationForEventsWithoutDate(1);
		$this->seminar->setBeginDate(0);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateNotAllowedReturnsFalse() {
		$this->seminar->setAllowRegistrationForEventsWithoutDate(0);
		$this->seminar->setBeginDate(0);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineOverReturnsFalse() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] - 42);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineInFutureReturnsTrue() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] + 42);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForRegistrationBeginInFutureReturnsFalse() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] + 10);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForRegistrationBeginInPastReturnsTrue() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForNoRegistrationBeginReturnsTrue() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
		$this->seminar->setRegistrationBeginDate(0);

		$this->assertTrue(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationByDateForBeginDateInPastAndRegistrationBeginInPastReturnsFalse() {
		$this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);
		$this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 50);

		$this->assertFalse(
			$this->fixture->allowsRegistrationByDate($this->seminar)
		);
	}


	/*
	 * Tests concerning allowsRegistrationBySeats
	 */

	/**
	 * @test
	 */
	public function allowsRegistrationBySeatsForEventWithNoVacanciesAndNoQueueReturnsFalse() {
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setRegistrationQueue(FALSE);

		$this->assertFalse(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationBySeatsForEventWithUnlimitedVacanciesReturnsTrue() {
		$this->seminar->setUnlimitedVacancies();

		$this->assertTrue(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationBySeatsForEventWithRegistrationQueueReturnsTrue() {
		$this->seminar->setNumberOfAttendances(1);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setRegistrationQueue(TRUE);

		$this->assertTrue(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function allowsRegistrationBySeatsForEventWithVacanciesReturnsTrue() {
		$this->seminar->setNumberOfAttendances(0);
		$this->seminar->setAttendancesMax(1);
		$this->seminar->setRegistrationQueue(FALSE);

		$this->assertTrue(
			$this->fixture->allowsRegistrationBySeats($this->seminar)
		);
	}


	/*
	 * Tests concerning registrationHasStarted
	 */

	/**
	 * @test
	 */
	public function registrationHasStartedForEventWithoutRegistrationBeginReturnsTrue() {
		$this->seminar->setRegistrationBeginDate(0);

		$this->assertTrue(
			$this->fixture->registrationHasStarted($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function registrationHasStartedForEventWithRegistrationBeginInPastReturnsTrue() {
		$this->seminar->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] - 42
		);

		$this->assertTrue(
			$this->fixture->registrationHasStarted($this->seminar)
		);
	}

	/**
	 * @test
	 */
	public function registrationHasStartedForEventWithRegistrationBeginInFutureReturnsFalse() {
		$this->seminar->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] + 42
		);

		$this->assertFalse(
			$this->fixture->registrationHasStarted($this->seminar)
		);
	}


	/*
	 * Tests concerning createRegistration
	 */

	/**
	 * @TODO: This is just a transitional test that needs to be removed once
	 * createRegistration uses the data mapper to save the registration.
	 *
	 * @test
	 */
	public function createRegistrationSavesRegistration() {
		$this->createAndLogInFrontEndUser();

		$plugin = new tx_seminars_FrontEnd_DefaultController();
		$plugin->cObj = $GLOBALS['TSFE']->cObj;
		$fixture = $this->getMock(
			'tx_seminars_registrationmanager',
			array(
				'notifyAttendee', 'notifyOrganizers',
				'sendAdditionalNotification', 'setRegistrationData'
			)
		);

		$fixture->createRegistration($this->seminar, array(), $plugin);

		$this->assertInstanceOf(
			'tx_seminars_registration',
			$fixture->getRegistration()
		);
		$uid = $fixture->getRegistration()->getUid();
		$this->assertTrue(
			// We're not using the testing framework here because the record
			// is not marked as dummy record.
			tx_oelib_db::existsRecordWithUid(
				'tx_seminars_attendances', $uid
			)
		);

		tx_oelib_db::delete('tx_seminars_attendances', 'uid = ' . $uid);
	}

	/**
	 * @test
	 */
	public function createRegistrationReturnsRegistration() {
		$this->createAndLogInFrontEndUser();

		$plugin = new tx_seminars_FrontEnd_DefaultController();
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

		$this->assertInstanceOf(
			'tx_seminars_Model_Registration',
			$registration
		);
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

		$plugin = new tx_seminars_FrontEnd_DefaultController();
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

		$this->assertSame(
			$registration->getUid(),
			$fixture->getRegistration()->getUid()
		);
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

		$plugin = new tx_seminars_FrontEnd_DefaultController();
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

		tx_oelib_db::delete('tx_seminars_attendances', 'uid = ' . $uid);
	}


	/*
	 * Tests concerning setRegistrationData()
	 */

	/**
	 * @test
	 */
	public function setRegistrationDataForPositiveSeatsSetsSeats() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('seats' => '3')
		);

		$this->assertSame(
			3,
			$registration->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingSeatsSetsOneSeat() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertSame(
			1,
			$registration->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForZeroSeatsSetsOneSeat() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('seats' => '0')
		);

		$this->assertSame(
			1,
			$registration->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNegativeSeatsSetsOneSeat() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('seats' => '-1')
		);

		$this->assertSame(
			1,
			$registration->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForRegisteredThemselvesOneSetsItToTrue() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('registered_themselves' => '1')
		);

		$this->assertTrue(
			$registration->hasRegisteredThemselves()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForRegisteredThemselvesZeroSetsItToFalse() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('registered_themselves' => '0')
		);

		$this->assertFalse(
			$registration->hasRegisteredThemselves()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForRegisteredThemselvesMissingSetsItToFalse() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertFalse(
			$registration->hasRegisteredThemselves()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePricePutsSelectedPriceCodeToPrice() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices'));
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12, 'special' => 3)));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'special')
		);

		$this->assertSame(
			'special',
			$registration->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedNotAvailablePricePutsFirstPriceCodeToPrice() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices'));
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'early_bird_regular')
		);

		$this->assertSame(
			'regular',
			$registration->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNoSelectedPricePutsFirstPriceCodeToPrice() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices'));
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertSame(
			'regular',
			$registration->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNoSelectedAndOnlyFreeRegularPriceAvailablePutsRegularPriceCodeToPrice() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices'));
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 0)));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertSame(
			'regular',
			$registration->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForOneSeatsCalculatesTotalPriceFromSelectedPriceAndSeats() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices'));
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'regular', 'seats' => '1')
		);

		$this->assertSame(
			12.0,
			$registration->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForTwoSeatsCalculatesTotalPriceFromSelectedPriceAndSeats() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices'));
		$event->setData(array('payment_methods' => new tx_oelib_List()));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('price' => 'regular', 'seats' => '2')
		);

		$this->assertSame(
			24.0,
			$registration->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyAttendeesNamesSetsAttendeesNames() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('attendees_names' => 'John Doe' . LF . 'Jane Doe')
		);

		$this->assertSame(
			'John Doe' . LF . 'Jane Doe',
			$registration->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromAttendeesNames() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('attendees_names' => 'John <em>Doe</em>')
		);

		$this->assertSame(
			'John Doe',
			$registration->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyAttendeesNamesSetsEmptyAttendeesNames() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('attendees_names' => '')
		);

		$this->assertSame(
			'',
			$registration->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingAttendeesNamesSetsEmptyAttendeesNames() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertSame(
			'',
			$registration->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForPositiveKidsSetsNumberOfKids() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('kids' => '3')
		);

		$this->assertSame(
			3,
			$registration->getKids()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingKidsSetsZeroKids() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertSame(
			0,
			$registration->getKids()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForZeroKidsSetsZeroKids() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('kids' => '0')
		);

		$this->assertSame(
			0,
			$registration->getKids()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNegativeKidsSetsZeroKids() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('kids' => '-1')
		);

		$this->assertSame(
			0,
			$registration->getKids()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneSetsIt() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod->getUid()));

		$this->assertSame(
			$paymentMethod,
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePaymentMethodFromTwoSetsIt() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod1 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethod2 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod1);
		$paymentMethods->add($paymentMethod2);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod2->getUid()));

		$this->assertSame(
			$paymentMethod2,
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneForFreeEventsSetsNoPaymentMethod() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 0)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod->getUid()));

		$this->assertNull(
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingPaymentMethodAndNoneAvailableSetsNone() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 0)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue(new tx_oelib_List()));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array()
		);

		$this->assertNull(
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingPaymentMethodAndTwoAvailableSetsNone() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod1 = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethod2 = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod1);
		$paymentMethods->add($paymentMethod2);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertNull(
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingPaymentMethodAndOneAvailableSetsIt() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			$paymentMethod,
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForUnavailablePaymentMethodAndTwoAvailableSetsNone() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod1 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethod2 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod1);
		$paymentMethods->add($paymentMethod2);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('method_of_payment' => max($paymentMethod1->getUid(), $paymentMethod2->getUid()) + 1)
		);

		$this->assertNull(
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForUnavailablePaymentMethodAndOneAvailableSetsAvailable() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$paymentMethod = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_PaymentMethod')->getNewGhost();
		$paymentMethods = new tx_oelib_List();
		$paymentMethods->add($paymentMethod);

		$event = $this->getMock('tx_seminars_Model_Event', array('getAvailablePrices', 'getPaymentMethods'));
		$event->expects($this->any())->method('getAvailablePrices')
			->will($this->returnValue(array('regular' => 12)));
		$event->expects($this->any())->method('getPaymentMethods')
			->will($this->returnValue($paymentMethods));
		$registration = new tx_seminars_Model_Registration();
		/** @var $event tx_seminars_Model_Event */
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod->getUid() + 1));

		$this->assertSame(
			$paymentMethod,
			$registration->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyAccountNumberSetsAccountNumber() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('account_number' => '123 455 ABC'));

		$this->assertSame(
			'123 455 ABC',
			$registration->getAccountNumber()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromAccountNumber() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('account_number' => '123 <em>455</em> ABC'));

		$this->assertSame(
			'123 455 ABC',
			$registration->getAccountNumber()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInAccountNumber() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_number' => '123' . CRLF . '455'  . TAB . ' ABC')
		);

		$this->assertSame(
			'123 455 ABC',
			$registration->getAccountNumber()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyAccountNumberSetsEmptyAccountNumber() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('account_number' => ''));

		$this->assertSame(
			'',
			$registration->getAccountNumber()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingAccountNumberSetsEmptyAccountNumber() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getAccountNumber()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyBankCodeSetsBankCode() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('bank_code' => '123 455 ABC'));

		$this->assertSame(
			'123 455 ABC',
			$registration->getBankCode()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromBankCode() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('bank_code' => '123 <em>455</em> ABC'));

		$this->assertSame(
			'123 455 ABC',
			$registration->getBankCode()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInBankCode() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_code' => '123' . CRLF . '455'  . TAB . ' ABC')
		);

		$this->assertSame(
			'123 455 ABC',
			$registration->getBankCode()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyBankCodeSetsEmptyBankCode() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('bank_code' => ''));

		$this->assertSame(
			'',
			$registration->getBankCode()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingBankCodeSetsEmptyBankCode() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getBankCode()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyBankNameSetsBankName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('bank_name' => 'Swiss Tax Protection'));

		$this->assertSame(
			'Swiss Tax Protection',
			$registration->getBankName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromBankName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('bank_name' => 'Swiss <em>Tax</em> Protection'));

		$this->assertSame(
			'Swiss Tax Protection',
			$registration->getBankName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInBankName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('bank_name' => 'Swiss' . CRLF . 'Tax'  . TAB . ' Protection')
		);

		$this->assertSame(
			'Swiss Tax Protection',
			$registration->getBankName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyBankNameSetsEmptyBankName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event') ->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('bank_name' => ''));

		$this->assertSame(
			'',
			$registration->getBankName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingBankNameSetsEmptyBankName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getBankName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyAccountOwnerSetsAccountOwner() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('account_owner' => 'John Doe'));

		$this->assertSame(
			'John Doe',
			$registration->getAccountOwner()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromAccountOwner() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('account_owner' => 'John <em>Doe</em>'));

		$this->assertSame(
			'John Doe',
			$registration->getAccountOwner()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInAccountOwner() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('account_owner' => 'John' . CRLF . TAB . ' Doe')
		);

		$this->assertSame(
			'John Doe',
			$registration->getAccountOwner()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyAccountOwnerSetsEmptyAccountOwner() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('account_owner' => ''));

		$this->assertSame(
			'',
			$registration->getAccountOwner()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingAccountOwnerSetsEmptyAccountOwner() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getAccountOwner()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyCompanySetsCompany() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('company' => 'Business Ltd.' . LF . 'Tom, Dick & Harry')
		);

		$this->assertSame(
			'Business Ltd.' . LF . 'Tom, Dick & Harry',
			$registration->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromCompany() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('company' => 'Business <em>Ltd.</em>'));

		$this->assertSame(
			'Business Ltd.',
			$registration->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyCompanySetsEmptyCompany() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('company' => ''));

		$this->assertSame(
			'',
			$registration->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingCompanySetsEmptyCompany() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMaleGenderSetsGender() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('gender' => (string) tx_oelib_Model_FrontEndUser::GENDER_MALE)
		);

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_MALE,
			$registration->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForFemaleGenderSetsGender() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData(
			$registration, array('gender' => (string) tx_oelib_Model_FrontEndUser::GENDER_FEMALE)
		);

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$registration->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForInvalidIntegerGenderSetsUnknownGender() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('gender' => '42'));

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForInvalidStringGenderSetsUnknownGender() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('gender' => 'Mr. Fantastic'));

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyGenderSetsUnknownGender() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('gender' => ''));

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingGenderSetsUnknownGender() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$registration->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyNameSetsName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('name' => 'John Doe'));

		$this->assertSame(
			'John Doe',
			$registration->getName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('name' => 'John <em>Doe</em>'));

		$this->assertSame(
			'John Doe',
			$registration->getName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('name' => 'John' . CRLF . TAB . ' Doe'));

		$this->assertSame(
			'John Doe',
			$registration->getName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyNameSetsEmptyName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('name' => ''));

		$this->assertSame(
			'',
			$registration->getName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingNameSetsEmptyName() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getName()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyAddressSetsAddress() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('address' => 'Back Road 42' . LF . '(second door)'));

		$this->assertSame(
			'Back Road 42' . LF . '(second door)',
			$registration->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromAddress() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('address' => 'Back <em>Road</em> 42'));

		$this->assertSame(
			'Back Road 42',
			$registration->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyAddressSetsEmptyAddress() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('address' => ''));

		$this->assertSame(
			'',
			$registration->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingAddressSetsEmptyAddress() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyZipSetsZip() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('zip' => '12345 ABC'));

		$this->assertSame(
			'12345 ABC',
			$registration->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromZip() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('zip' => '12345 <em>ABC</em>'));

		$this->assertSame(
			'12345 ABC',
			$registration->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInZip() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('zip' => '12345' . CRLF . TAB . ' ABC'));

		$this->assertSame(
			'12345 ABC',
			$registration->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyZipSetsEmptyZip() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('zip' => ''));

		$this->assertSame(
			'',
			$registration->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingZipSetsEmptyZip() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyCitySetsCity() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('city' => 'Elmshorn'));

		$this->assertSame(
			'Elmshorn',
			$registration->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromCity() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('city' => 'Santiago de <em>Chile</em>'));

		$this->assertSame(
			'Santiago de Chile',
			$registration->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInCity() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('city' => 'Santiago' . CRLF . TAB . ' de Chile'));

		$this->assertSame(
			'Santiago de Chile',
			$registration->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyCitySetsEmptyCity() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('city' => ''));

		$this->assertSame(
			'',
			$registration->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingCitySetsEmptyCity() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForNonEmptyCountrySetsCountry() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('country' => 'Brazil'));

		$this->assertSame(
			'Brazil',
			$registration->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataDropsHtmlTagsFromCountry() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('country' => 'South <em>Africa</em>'));

		$this->assertSame(
			'South Africa',
			$registration->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataChangesWhitespaceToSpaceInCountry() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('country' => 'South' . CRLF . TAB . ' Africa'));

		$this->assertSame(
			'South Africa',
			$registration->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForEmptyCountrySetsEmptyCountry() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array('country' => ''));

		$this->assertSame(
			'',
			$registration->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDataForMissingCountrySetsEmptyCountry() {
		$fixture = t3lib_div::makeInstance(
			$this->createAccessibleProxyClass()
		);

		/** @var $event tx_seminars_Model_Event */
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$registration = new tx_seminars_Model_Registration();
		$registration->setEvent($event);

		$fixture->setRegistrationData($registration, array());

		$this->assertSame(
			'',
			$registration->getCountry()
		);
	}
}