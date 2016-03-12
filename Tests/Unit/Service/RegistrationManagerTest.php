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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Service_RegistrationManagerTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Service_RegistrationManager
     */
    protected $fixture = null;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var int
     */
    protected $seminarUid = 0;

    /**
     * @var tx_seminars_seminarchild
     */
    protected $seminar = null;

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
     * @var Tx_Seminars_FrontEnd_DefaultController a front-end plugin
     */
    protected $pi1 = null;

    /**
     * @var tx_seminars_seminarchild a fully booked seminar
     */
    protected $fullyBookedSeminar = null;

    /**
     * @var tx_seminars_seminarchild a seminar
     */
    protected $cachedSeminar = null;

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
     * @var Tx_Seminars_Service_SingleViewLinkBuilder
     */
    protected $linkBuilder = null;

    /**
     * @var Tx_Oelib_EmailCollector
     */
    protected $mailer = null;

    /**
     * @var Tx_Oelib_HeaderCollector
     */
    protected $headerCollector = null;

    protected function setUp()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        /** @var Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration::purgeCachedSeminars();
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', Tx_Seminars_Service_RegistrationManager::SEND_TEXT_MAIL);
        $configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin.tx_seminars', new Tx_Oelib_Configuration());
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new Tx_Oelib_Configuration());
        $configurationRegistry->set('config', new Tx_Oelib_Configuration());
        $configurationRegistry->set('page.config', new Tx_Oelib_Configuration());

        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            array(
                'title' => 'test organizer',
                'email' => 'mail@example.com',
                'email_footer' => 'organizer footer',
            )
        );
        $this->seminarUid = $this->testingFramework->createRecord(
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
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $this->seminarUid, $organizerUid);

        Tx_Oelib_TemplateHelper::setCachedConfigurationValue(
            'templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $headerProxyFactory = Tx_Oelib_HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerCollector = $headerProxyFactory->getHeaderProxy();

        $this->seminar = new tx_seminars_seminarchild($this->seminarUid);
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();

        $this->linkBuilder = $this->getMock(Tx_Seminars_Service_SingleViewLinkBuilder::class, array('createAbsoluteUrlForEvent'));
        $this->linkBuilder->expects(self::any())
            ->method('createAbsoluteUrlForEvent')
            ->will(self::returnValue('http://singleview.example.com/'));
        $this->fixture->injectLinkBuilder($this->linkBuilder);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        Tx_Seminars_Service_RegistrationManager::purgeInstance();

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
    private function createFrontEndPages()
    {
        $this->loginPageUid = $this->testingFramework->createFrontEndPage();
        $this->registrationPageUid
            = $this->testingFramework->createFrontEndPage();

        $this->pi1 = new Tx_Seminars_FrontEnd_DefaultController();

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
    private function createAndLogInFrontEndUser()
    {
        $this->frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
    }

    /**
     * Creates a seminar which is booked out.
     *
     * @return void
     */
    private function createBookedOutSeminar()
    {
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
     * @return Tx_Seminars_OldModel_Registration the created registration
     */
    private function createRegistration()
    {
        $frontEndUserUid = $this->testingFramework->createFrontEndUser(
            '',
            array(
                'name' => 'Harry Callagan',
                'email' => 'foo@bar.com',
            )
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'seminar' => $this->seminarUid,
                'user' => $frontEndUserUid,
                'food' => 'something nice to eat',
                'accommodation' => 'a nice, dry place',
                'interests' => 'learning Ruby on Rails',
            )
        );

        return new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);
    }

    /**
     * Creates a subclass of the fixture class that makes protected methods
     * public where necessary.
     *
     * @return string the class name of the subclass, will not be empty
     */
    private function createAccessibleProxyClass()
    {
        $testingClassName = Tx_Seminars_Service_RegistrationManager::class . uniqid();

        if (!class_exists($testingClassName, false)) {
            eval(
                'class ' . $testingClassName .
                    ' extends Tx_Seminars_Service_RegistrationManager {' .
                'public function setRegistrationData(' .
                '  Tx_Seminars_Model_Registration $registration, array $formData' .
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
    protected function getEmailHtmlPart()
    {
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
    public function createFrontEndPagesCreatesNonZeroLoginPageUid()
    {
        $this->createFrontEndPages();

        self::assertGreaterThan(
            0,
            $this->loginPageUid
        );
    }

    public function testCreateFrontEndPagesCreatesNonZeroRegistrationPageUid()
    {
        $this->createFrontEndPages();

        self::assertGreaterThan(
            0,
            $this->registrationPageUid
        );
    }

    public function testCreateFrontEndPagesCreatesPi1()
    {
        $this->createFrontEndPages();

        self::assertNotNull(
            $this->pi1
        );
        self::assertInstanceOf(
            Tx_Seminars_FrontEnd_DefaultController::class,
            $this->pi1
        );
    }

    public function testCreateAndLogInFrontEndUserCreatesNonZeroUserUid()
    {
        $this->createAndLogInFrontEndUser();

        self::assertGreaterThan(
            0,
            $this->frontEndUserUid
        );
    }

    public function testCreateAndLogInFrontEndUserLogsInFrontEndUser()
    {
        $this->createAndLogInFrontEndUser();
        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    public function testCreateBookedOutSeminarSetsSeminarInstance()
    {
        $this->createBookedOutSeminar();

        self::assertInstanceOf(
            Tx_Seminars_OldModel_Event::class,
            $this->fullyBookedSeminar
        );
    }

    public function testCreatedBookedOutSeminarHasUidGreaterZero()
    {
        $this->createBookedOutSeminar();

        self::assertTrue(
            $this->fullyBookedSeminar->getUid() > 0
        );
    }

    /**
     * @test
     */
    public function createAccessibleProxyClassCreatesFixtureSubclass()
    {
        $className = $this->createAccessibleProxyClass();
        $instance = new $className();

        self::assertInstanceOf(
            Tx_Seminars_Service_RegistrationManager::class,
            $instance
        );
    }

    /*
     * Tests regarding the Singleton property.
     */

    /**
     * @test
     */
    public function getInstanceReturnsRegistrationManagerInstance()
    {
        self::assertInstanceOf(
            Tx_Seminars_Service_RegistrationManager::class,
            Tx_Seminars_Service_RegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            Tx_Seminars_Service_RegistrationManager::getInstance(),
            Tx_Seminars_Service_RegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = Tx_Seminars_Service_RegistrationManager::getInstance();
        Tx_Seminars_Service_RegistrationManager::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            Tx_Seminars_Service_RegistrationManager::getInstance()
        );
    }

    /*
     * Tests for the link to the registration page
     */

    public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkTag()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertContains(
            '<a ',
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertContains(
            '?id=' . $this->loginPageUid,
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserContainsRedirectWithEventUid()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertContains(
            'redirect_url',
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
        self::assertContains(
            '%255Bseminar%255D%3D' . $this->seminarUid,
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkTag()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertContains(
            '<a ',
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertContains(
            '?id=' . $this->registrationPageUid,
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar
            )
        );
        self::assertContains(
            '[seminar]=' . $this->seminarUid,
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=4504
     */
    public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserAndSeparateDetailsPageCreatesLinkToRegistrationPage()
    {
        $this->createFrontEndPages();

        $detailsPageUid = $this->testingFramework->createFrontEndPage();
        $this->seminar->setDetailsPage($detailsPageUid);

        $this->createAndLogInFrontEndUser();

        self::assertContains(
            '?id=' . $this->registrationPageUid,
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserDoesNotContainRedirect()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertNotContains(
            'redirect_url',
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndSeminarWithoutDateHasLinkWithPrebookingLabel()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate(0);

        self::assertContains(
            $this->pi1->translate('label_onlinePrebooking'),
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserSeminarWithoutDateAndNoVacanciesContainsRegistrationLabel()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate(0);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);

        self::assertContains(
            $this->pi1->translate('label_onlineRegistration'),
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndFullyBookedSeminarWithQueueContainsQueueRegistrationLabel()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertContains(
            sprintf($this->pi1->translate('label_onlineRegistrationOnQueue'), 0),
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserAndFullyBookedSeminarWithQueueContainsQueueRegistrationLabel()
    {
        $this->createFrontEndPages();
        $this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertContains(
            sprintf($this->pi1->translate('label_onlineRegistrationOnQueue'), 0),
            $this->fixture->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /*
     * Tests for the getRegistrationLink function
     */

    public function testGetRegistrationLinkForLoggedInUserAndSeminarWithVacanciesReturnsLinkToRegistrationPage()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertContains(
            '?id=' . $this->registrationPageUid,
            $this->fixture->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    public function testGetRegistrationLinkForLoggedInUserAndSeminarWithVacanciesReturnsLinkWithSeminarUid()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertContains(
            '[seminar]=' . $this->seminarUid,
            $this->fixture->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    public function testGetRegistrationLinkForLoggedOutUserAndSeminarWithVacanciesReturnsLoginLink()
    {
        $this->createFrontEndPages();

        self::assertContains(
            '?id=' . $this->loginPageUid,
            $this->fixture->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedInUserAndFullyBookedSeminarReturnsEmptyString()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        $this->createBookedOutSeminar();

        self::assertSame(
            '',
            $this->fixture->getRegistrationLink($this->pi1, $this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedOutUserAndFullyBookedSeminarReturnsEmptyString()
    {
        $this->createFrontEndPages();

        $this->createBookedOutSeminar();

        self::assertSame(
            '',
            $this->fixture->getRegistrationLink($this->pi1, $this->fullyBookedSeminar)
        );
    }

    public function testGetRegistrationLinkForBeginDateBeforeCurrentDateReturnsEmptyString()
    {
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

        self::assertSame(
            '',
            $this->fixture->getRegistrationLink($this->pi1, $this->cachedSeminar)
        );
    }

    public function testGetRegistrationLinkForAlreadyEndedRegistrationDeadlineReturnsEmptyString()
    {
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

        self::assertSame(
            '',
            $this->fixture->getRegistrationLink($this->pi1, $this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsLinkWithSeminarUid()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setUnlimitedVacancies();

        self::assertContains(
            '[seminar]=' . $this->seminarUid,
            $this->fixture->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsLoginLink()
    {
        $this->createFrontEndPages();
        $this->seminar->setUnlimitedVacancies();

        self::assertContains(
            '?id=' . $this->loginPageUid,
            $this->fixture->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedInUserAndFullyBookedSeminarWithQueueEnabledReturnsLinkWithSeminarUid()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setNeedsRegistration(true);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertContains(
            '[seminar]=' . $this->seminarUid,
            $this->fixture->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedOutUserAndFullyBookedSeminarWithQueueEnabledReturnsLoginLink()
    {
        $this->createFrontEndPages();
        $this->seminar->setNeedsRegistration(true);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertContains(
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
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarRegistrationOpenReturnsTrue()
    {
        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInUserAndRegistrationOpenReturnsTrue()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserReturnsFalse()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            )
        );

        self::assertFalse(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserAndSeminarWithMultipleRegistrationsAllowedReturnsTrue()
    {
        $this->seminar->setAllowsMultipleRegistrations(true);

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            )
        );

        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInButBlockedUserReturnsFalse()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
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

        self::assertFalse(
            $this->fixture->canRegisterIfLoggedIn($this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarReturnsFalse()
    {
        $this->createBookedOutSeminar();

        self::assertFalse(
            $this->fixture->canRegisterIfLoggedIn($this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndCanceledSeminarReturnsFalse()
    {
        $this->seminar->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarWithoutRegistrationReturnsFalse()
    {
        $this->seminar->setAttendancesMax(0);
        $this->seminar->setNeedsRegistration(false);

        self::assertFalse(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarWithQueueReturnsTrue()
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInUserAndFullyBookedSeminarWithQueueReturnsTrue()
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationPossibleCallsCanRegisterForSeminarHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $user = Tx_Oelib_FrontEndLoginManager::getInstance()->getLoggedInUser(Tx_Seminars_Mapper_FrontEndUser::class);

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
        $hook->expects(self::once())->method('canRegisterForSeminar')->with($this->seminar, $user);

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
            [$hookClass] = $hookClass;

        $this->fixture->canRegisterIfLoggedIn($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationNotPossibleNotCallsCanRegisterForSeminarHook()
    {
        $this->seminar->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
        $hook->expects(self::never())->method('canRegisterForSeminar');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        $this->fixture->canRegisterIfLoggedIn($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationPossibleAndHookReturningTrueReturnsTrue()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
        $hook->expects(self::once())->method('canRegisterForSeminar')->will(self::returnValue(true));

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        self::assertTrue(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationPossibleAndHookReturningFalseReturnsFalse()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('canRegisterForSeminar'));
        $hook->expects(self::once())->method('canRegisterForSeminar')->will(self::returnValue(false));

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        self::assertFalse(
            $this->fixture->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /*
     * Tests concerning canRegisterIfLoggedInMessage
     */

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarRegistrationOpenReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInUserAndRegistrationOpenReturnsEmptyString()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredUserReturnsAlreadyRegisteredMessage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            )
        );

        self::assertSame(
            $this->fixture->translate('message_alreadyRegistered'),
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredUserAndSeminarWithMultipleRegistrationsAllowedReturnsEmptyString()
    {
        $this->seminar->setAllowsMultipleRegistrations(true);

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            )
        );

        self::assertSame(
            '',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButBlockedUserReturnsUserIsBlockedMessage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
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

        self::assertSame(
            $this->fixture->translate('message_userIsBlocked'),
            $this->fixture->canRegisterIfLoggedInMessage($this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarReturnsFullyBookedMessage()
    {
        $this->createBookedOutSeminar();

        self::assertSame(
            'message_noVacancies',
            $this->fixture->canRegisterIfLoggedInMessage($this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndCanceledSeminarReturnsSeminarCancelledMessage()
    {
        $this->seminar->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertSame(
            'message_seminarCancelled',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarWithoutRegistrationReturnsNoRegistrationNeededMessage()
    {
        $this->seminar->setAttendancesMax(0);
        $this->seminar->setNeedsRegistration(false);

        self::assertSame(
            'message_noRegistrationNecessary',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsEmptyString()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertSame(
            '',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsEmptyString()
    {
        $this->seminar->setUnlimitedVacancies();
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarWithQueueReturnsEmptyString()
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertSame(
            '',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInUserAndFullyBookedSeminarWithQueueReturnsEmptyString()
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleCallsCanUserRegisterForSeminarMessageHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $user = Tx_Oelib_FrontEndLoginManager::getInstance()->getLoggedInUser(Tx_Seminars_Mapper_FrontEndUser::class);

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
        $hook->expects(self::once())->method('canRegisterForSeminarMessage')->with($this->seminar, $user);

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
            [$hookClass] = $hookClass;

        $this->fixture->canRegisterIfLoggedInMessage($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationNotPossibleNotCallsCanUserRegisterForSeminarMessageHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->seminar->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
        $hook->expects(self::never())->method('canRegisterForSeminarMessage');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        $this->fixture->canRegisterIfLoggedInMessage($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsStringFromHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('canRegisterForSeminarMessage'));
        $hook->expects(self::once())->method('canRegisterForSeminarMessage')->will(self::returnValue('Hello world!'));

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        self::assertSame(
            'Hello world!',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsNonEmptyStringFromFirstHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass1 = uniqid('tx_registrationHook');
        $hook1 = $this->getMock($hookClass1, array('canRegisterForSeminarMessage'));
        $hook1->expects(self::any())->method('canRegisterForSeminarMessage')->will(self::returnValue('message 1'));
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass1] = $hook1;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass1] = $hookClass1;

        $hookClass2 = uniqid('tx_registrationHook');
        $hook2 = $this->getMock($hookClass2, array('canRegisterForSeminarMessage'));
        $hook2->expects(self::any())->method('canRegisterForSeminarMessage')->will(self::returnValue('message 2'));
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass2] = $hook2;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass2] = $hookClass2;

        self::assertSame(
            'message 1',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsFirstNonEmptyStringFromHooks()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass1 = uniqid('tx_registrationHook');
        $hook1 = $this->getMock($hookClass1, array('canRegisterForSeminarMessage'));
        $hook1->expects(self::any())->method('canRegisterForSeminarMessage')->will(self::returnValue(''));
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass1] = $hook1;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass1] = $hookClass1;

        $hookClass2 = uniqid('tx_registrationHook');
        $hook2 = $this->getMock($hookClass2, array('canRegisterForSeminarMessage'));
        $hook2->expects(self::any())->method('canRegisterForSeminarMessage')->will(self::returnValue('message 2'));
        $GLOBALS['T3_VAR']['getUserObj'][$hookClass2] = $hook2;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass2] = $hookClass2;

        self::assertSame(
            'message 2',
            $this->fixture->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /*
     * Test concerning userFulfillsRequirements
     */

    public function testUserFulfillsRequirementsForEventWithoutRequirementsReturnsTrue()
    {
        $this->testingFramework->createAndLogInFrontEndUser();

        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->cachedSeminar = new tx_seminars_seminarchild(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                array(
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );

        self::assertTrue(
            $this->fixture->userFulfillsRequirements($this->cachedSeminar)
        );
    }

    public function testUserFulfillsRequirementsForEventWithOneFulfilledRequirementReturnsTrue()
    {
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $requiredDateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
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
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid, $requiredTopicUid, 'requirements'
        );

        $this->cachedSeminar = new tx_seminars_seminarchild(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                array(
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );

        self::assertTrue(
            $this->fixture->userFulfillsRequirements($this->cachedSeminar)
        );
    }

    public function testUserFulfillsRequirementsForEventWithOneUnfulfilledRequirementReturnsFalse()
    {
        $this->testingFramework->createAndLogInFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            )
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid, $requiredTopicUid, 'requirements'
        );

        $this->cachedSeminar = new tx_seminars_seminarchild(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                array(
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );

        self::assertFalse(
            $this->fixture->userFulfillsRequirements($this->cachedSeminar)
        );
    }

    /*
     * Tests concerning getMissingRequiredTopics
     */

    public function testGetMissingRequiredTopicsReturnsSeminarBag()
    {
        self::assertInstanceOf(
            Tx_Seminars_Bag_Event::class,
            $this->fixture->getMissingRequiredTopics($this->seminar)
        );
    }

    public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsOneItem()
    {
        $this->testingFramework->createAndLogInFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            )
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid, $requiredTopicUid, 'requirements'
        );

        $this->cachedSeminar = new tx_seminars_seminarchild(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                array(
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );
        $missingTopics = $this->fixture->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            1,
            $missingTopics->count()
        );
    }

    public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsRequiredTopic()
    {
        $this->testingFramework->createAndLogInFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            )
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid, $requiredTopicUid, 'requirements'
        );

        $this->cachedSeminar = new tx_seminars_seminarchild(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                array(
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );
        $missingTopics = $this->fixture->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            $requiredTopicUid,
            $missingTopics->current()->getUid()
        );
    }

    public function testGetMissingRequiredTopicsForTopicWithOneTwoNotFulfilledRequirementReturnsTwoItems()
    {
        $this->testingFramework->createAndLogInFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid1,
            )
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid, $requiredTopicUid1, 'requirements'
        );

        $requiredTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
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
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );
        $missingTopics = $this->fixture->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            2,
            $missingTopics->count()
        );
    }

    public function testGetMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledReturnsUnfulfilledTopic()
    {
        $userUid = $this->testingFramework->createAndLogInFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $requiredDateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
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
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid, $requiredTopicUid2, 'requirements'
        );

        $this->cachedSeminar = new tx_seminars_seminarchild(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                array(
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );
        $missingTopics = $this->fixture->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            $requiredTopicUid2,
            $missingTopics->current()->getUid()
        );
    }

    public function testGetMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledDoesNotReturnFulfilledTopic()
    {
        $userUid = $this->testingFramework->createAndLogInFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $requiredDateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
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
            array('object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC)
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid, $requiredTopicUid2, 'requirements'
        );

        $this->cachedSeminar = new tx_seminars_seminarchild(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                array(
                    'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                    'topic' => $topicUid,
                )
            )
        );
        $missingTopics = $this->fixture->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
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
    public function removeRegistrationHidesRegistrationOfUser()
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $seminarUid = $this->seminarUid;
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

        self::assertTrue(
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
    public function removeRegistrationCallsSeminarRegistrationRemovedHook()
    {
        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('seminarRegistrationRemoved'));
        // We cannot test for the expected parameters because the registration
        // instance does not exist yet at this point.
        $hook->expects(self::once())->method('seminarRegistrationRemoved');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
            [$hookClass] = $hookClass;

        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $seminarUid = $this->seminarUid;
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
    public function removeRegistrationWithFittingQueueRegistrationMovesItFromQueue()
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $seminarUid = $this->seminarUid;
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
    public function removeRegistrationWithFittingQueueRegistrationCallsSeminarRegistrationMovedFromQueueHook()
    {
        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('seminarRegistrationMovedFromQueue'));
        // We cannot test for the expected parameters because the registration
        // instance does not exist yet at this point.
        $hook->expects(self::once())->method('seminarRegistrationMovedFromQueue');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
            [$hookClass] = $hookClass;

        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $seminarUid = $this->seminarUid;
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
    public function canRegisterSeatsForFullyBookedEventAndZeroSeatsGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);

        self::assertFalse(
            $this->fixture->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventAndOneSeatGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);

        self::assertFalse(
            $this->fixture->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventAndEmptyStringGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);

        self::assertFalse(
            $this->fixture->canRegisterSeats($this->seminar, '')
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventAndInvalidStringGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);

        self::assertFalse(
            $this->fixture->canRegisterSeats($this->seminar, 'foo')
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndZeroSeatsGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndOneSeatGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndTwoSeatsGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertFalse(
            $this->fixture->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndEmptyStringGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, '')
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndInvalidStringGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertFalse(
            $this->fixture->canRegisterSeats($this->seminar, 'foo')
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndOneSeatGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(2);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndTwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(2);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndThreeSeatsGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(2);

        self::assertFalse(
            $this->fixture->canRegisterSeats($this->seminar, 3)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndZeroSeatsGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndOneSeatGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndTwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndFortytwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 42)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventWithQueueAndZeroSeatsGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventWithQueueAndOneSeatGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventWithQueueAndTwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventWithQueueAndEmptyStringGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, '')
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndWithQueueAndFortytwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(2);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->fixture->canRegisterSeats($this->seminar, 42)
        );
    }

    /*
     * Tests concerning notifyAttendee
     */

    /**
     * @test
     */
    public function notifyAttendeeSendsMailToAttendeesMailAddress()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertArrayHasKey(
            'foo@bar.com',
            $this->mailer->getFirstSentEmail()->getTo()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForAttendeeWithoutMailAddressNotSendsEmail()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            )
        );
        $registration = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsModifyThankYouEmailHook()
    {
        $hookClass = uniqid('tx_seminars_registrationHook');
        $hook = $this->getMock($hookClass, array('modifyThankYouEmail'));
        $hook->expects(self::once())->method('modifyThankYouEmail');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationFalseNotCallsModifyThankYouEmailHook()
    {
        $hookClass = uniqid('tx_seminars_registrationHook');
        $hook = $this->getMock($hookClass, array('modifyThankYouEmail'));
        $hook->expects(self::never())->method('modifyThankYouEmail');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        $this->fixture->setConfigurationValue('sendConfirmation', false);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueAndPlainTextEmailCallsModifyAttendeeEmailTextHookOnce()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', Tx_Seminars_Service_RegistrationManager::SEND_TEXT_MAIL);

        $registration = $this->createRegistration();

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_Registration::class, array());
        $hookClass = get_class($hook);
        $hook->expects(self::once())->method('modifyAttendeeEmailText')->with($registration, self::anything());

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueAndHtmlEmailCallsModifyAttendeeEmailTextHookTwice()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL);

        $registration = $this->createRegistration();

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_Registration::class, array());
        $hookClass = get_class($hook);
        $hook->expects(self::exactly(2))->method('modifyAttendeeEmailText')->with($registration, self::anything());

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeMailSubjectContainsConfirmationSubject()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->fixture->translate('email_confirmationSubject'),
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsEventTitle()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'test event',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationFood()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'something nice to eat',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationAccommodation()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'a nice, dry place',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationInterests()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'learning Ruby on Rails',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailSubjectContainsEventTitle()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'test event',
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsOrganizerAsSender()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertSame(
            array('mail@example.com' => 'test organizer'),
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailSetHasHtmlBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
            );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            '<html',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetDoesNotHaveHtmlBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertSame(
            array(),
            $this->mailer->getFirstSentEmail()->getChildren()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetHasNoUnreplacedMarkers()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            '###',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailHasNoUnreplacedMarkers()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
            );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            '###',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForMailSetToUserModeAndUserSetToHtmlMailsHasHtmlBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_USER_MAIL
            );
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            array(
                'module_sys_dmail_html' => true,
                'email' => 'foo@bar.com',
            )
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            '<html',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForMailSetToUserModeAndUserSetToTextMailsNotHasHtmlBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_USER_MAIL
            );
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            array(
                'module_sys_dmail_html' => false,
                'email' => 'foo@bar.com',
            )
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertSame(
            array(),
            $this->mailer->getFirstSentEmail()->getChildren()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsContainsNameOfUserInBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
            );
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'Harry Callagan',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasLinkToSeminarInBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
            );
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);
        $seminarLink = 'http://singleview.example.com/';

        self::assertContains(
            '<a href="' . $seminarLink,
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeAppendsOrganizersFooterToMailBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            LF . '-- ' . LF . 'organizer footer',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForConfirmedEventNotHasPlannedDisclaimer()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Tx_Seminars_Model_Event::STATUS_CONFIRMED
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            $this->fixture->translate('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForCancelledEventNotHasPlannedDisclaimer()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Tx_Seminars_Model_Event::STATUS_CANCELED
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            $this->fixture->translate('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlannedEventDisplaysPlannedDisclaimer()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Tx_Seminars_Model_Event::STATUS_PLANNED
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->fixture->translate('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeehiddenDisclaimerFieldAndPlannedEventHidesPlannedDisclaimer()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $this->fixture->setConfigurationValue(
            'hideFieldsInThankYouMail', 'planned_disclaimer'
        );
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Tx_Seminars_Model_Event::STATUS_PLANNED
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            $this->fixture->translate('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasCssStylesFromFile()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
            );
        $this->fixture->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'style=',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyCanContainAttendeesNames()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1 foo2');
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'foo1 foo2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailEnumeratesAttendeesNames()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1' . LF . 'foo2');
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            '1. foo1' . LF . '2. foo2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailReturnsAttendeesNamesInOrderedList()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
            );
        $this->fixture->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1' . LF . 'foo2');
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertRegExp(
            '/\<ol>.*<li>foo1<\/li>.*<li>foo2<\/li>.*<\/ol>/s',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceTitleInMailBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites', array('title' => 'foo_place')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'foo_place',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceAddressInMailBody()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites', array('address' => 'foo_street')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'foo_street',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithNoPlaceSendsWillBeAnnouncedMessage()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->fixture->translate('message_willBeAnnounced'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailSeparatesPlacesTitleAndAddressWithLinefeed()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('title' => 'place_title', 'address' => 'place_address')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'place_title' . LF . 'place_address',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailSeparatesPlacesTitleAndAddressWithBreaks()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
            );
        $this->fixture->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('title' => 'place_title', 'address' => 'place_address')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'place_title<br>place_address',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeStripsHtmlTagsFromPlaceAddress()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('title' => 'place_title', 'address' => 'place<h2>_address</h2>')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'place_title' . LF . 'place_address',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesLineFeedsWithSpaces()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('address' => 'address1' . LF . 'address2')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnsWithSpaces()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('address' => 'address1' . CR . 'address2')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnAndLineFeedWithOneSpace()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('address' => 'address1' . CRLF . 'address2')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleCarriageReturnsWithOneSpace()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('address' => 'address1' . CR . CR . 'address2')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsReplacesMultipleLineFeedsWithSpaces()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('address' => 'address1' . LF . LF . 'address2')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsReplacesMultipleLineFeedsWithSpaces()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
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
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address1 address2',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleLineFeedAndCarriageReturnsWithSpaces()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
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
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address1 address2 address3',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCityOfPlace()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites', array('city' => 'footown')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'footown',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsZipAndCityOfPlace()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites', array('zip' => '12345', 'city' => 'footown')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            '12345 footown',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCountryOfPlace()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);

        /** @var Tx_Oelib_Mapper_Country $mapper */
        $mapper = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class);
        /** @var Tx_Oelib_Model_Country $country */
        $country = $mapper->find(54);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('city' => 'footown', 'country' => $country->getIsoAlpha2Code())
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            $country->getLocalShortName(),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSeparatesAddressAndCityWithNewline()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('address' => 'address', 'city' => 'footown')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address' . LF . 'footown',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsSeparatresAddressAndCityLineWithBreaks()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')
            ->setAsInteger(
                'eMailFormatForAttendees',
                Tx_Seminars_Service_RegistrationManager::SEND_HTML_MAIL
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
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'address<br>footown',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCountryAndCitySeparatesCountryAndCityWithComma()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);

        /** @var Tx_Oelib_Mapper_Country $mapper */
        $mapper = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class);
        /** @var Tx_Oelib_Model_Country $country */
        $country = $mapper->find(54);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array(
                'address' => 'address',
                'city' => 'footown',
                'country' => $country->getIsoAlpha2Code(),
            )
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            'footown, ' . $country->getLocalShortName(),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCityAndNoCountryNotAddsSurplusCommaAfterCity()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            array('address' => 'address', 'city' => 'footown')
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars', $this->seminarUid, $uid, 'place'
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            'footown,',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * Checks that $string does not contain a raw label key.
     *
     * @param string $string
     *
     * @return void
     */
    private function assertNotContainsRawLabelKey($string)
    {
        self::assertNotContains('_', $string);
        self::assertNotContains('salutation', $string);
        self::assertNotContains('formal', $string);
    }

    /*
     * Tests concerning the salutation
     */

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationContainsInformalSalutation()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_informal'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderUnknownContainsFormalUnknownSalutation()
    {
        if (Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is no FrontEndUser.gender field.');
        }

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_formal_2'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderMaleContainsFormalMaleSalutation()
    {
        if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com', 'gender' => 0)
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_formal_0'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderFemaleContainsFormalFemaleSalutation()
    {
        if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com', 'gender' => 1)
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_formal_1'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationContainsFormalConfirmationText()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
            sprintf(
                $this->fixture->translate('email_confirmationHello'),
                $this->seminar->getTitle()
            ),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndConfirmationContainsInformalConfirmationText()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        self::assertContains(
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
    public function notifyAttendeeForFormalSalutationAndUnregistrationContainsFormalUnregistrationText()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnUnregistration', true
        );
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnUnregistration'
        );

        self::assertContains(
            sprintf(
                $this->fixture->translate(
                    'email_confirmationOnUnregistrationHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndUnregistrationContainsInformalUnregistrationText()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnUnregistration', true
        );
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnUnregistration'
        );

        self::assertContains(
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
    public function notifyAttendeeForFormalSalutationAndQueueConfirmationContainsFormalQueueConfirmationText()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue', true
        );
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnRegistrationForQueue'
        );

        self::assertContains(
            sprintf(
                $this->fixture->translate(
                    'email_confirmationOnRegistrationForQueueHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueConfirmationContainsInformalQueueConfirmationText()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue', true
        );
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnRegistrationForQueue'
        );

        self::assertContains(
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
    public function notifyAttendeeForFormalSalutationAndQueueUpdateContainsFormalQueueUpdateText()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnQueueUpdate', true
        );
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnQueueUpdate'
        );

        self::assertContains(
            sprintf(
                $this->fixture->translate(
                    'email_confirmationOnQueueUpdateHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueUpdateContainsInformalQueueUpdateText()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnQueueUpdate', true
        );
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnQueueUpdate'
        );

        self::assertContains(
            sprintf(
                $this->fixture->translate(
                    'email_confirmationOnQueueUpdateHello_informal'
                ),
                $this->seminar->getTitle()
            ),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderUnknownNotContainsRawTemplateMarkers()
    {
        if (Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is no FrontEndUser.gender field.');
        }

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderMaleNotContainsRawTemplateMarkers()
    {
        if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com', 'gender' => 0)
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderFemaleNotContainsRawTemplateMarkers()
    {
        if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->fixture->setConfigurationValue('sendConfirmation', true);
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com', 'gender' => 1)
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndConfirmationNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue('sendConfirmation', true);
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndUnregistrationNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnUnregistration', true
        );
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnUnregistration'
        );

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndUnregistrationNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnUnregistration', true
        );
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnUnregistration'
        );

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue', true
        );
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnRegistrationForQueue'
        );

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue', true
        );
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnRegistrationForQueue'
        );

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueUpdateNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnQueueUpdate', true
        );
        $this->fixture->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnQueueUpdate'
        );

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueUpdateNotContainsRawTemplateMarkers()
    {
        $this->fixture->setConfigurationValue(
            'sendConfirmationOnQueueUpdate', true
        );
        $this->fixture->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnQueueUpdate'
        );

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /*
     * Tests concerning the unregistration notice
     */

    /**
     * @test
     */
    public function notifyAttendeeForUnregistrationMailDoesNotAppendUnregistrationNotice()
    {
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class, array('getUnregistrationNotice')
        );
        $fixture->expects(self::never())->method('getUnregistrationNotice');

        $fixture->setConfigurationValue('sendConfirmationOnUnregistration', true);
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array(
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_DAY,
            )
        );
        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnUnregistration'
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationMailAndNoUnregistrationPossibleNotAddsUnregistrationNotice()
    {
        Tx_Oelib_TemplateHelper::setCachedConfigurationValue(
            'allowUnregistrationWithEmptyWaitingList', false
        );

        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class, array('getUnregistrationNotice')
        );
        $fixture->expects(self::never())->method('getUnregistrationNotice');
        $fixture->setConfigurationValue('sendConfirmation', true);

        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array(
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_DAY,
            )
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $fixture->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationMailAndUnregistrationPossibleAddsUnregistrationNotice()
    {
        Tx_Oelib_TemplateHelper::setCachedConfigurationValue(
            'allowUnregistrationWithEmptyWaitingList', true
        );

        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class, array('getUnregistrationNotice')
        );
        $fixture->expects(self::once())->method('getUnregistrationNotice');
        $fixture->setConfigurationValue('sendConfirmation', true);

        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users', $registration->getFrontEndUser()->getUid(),
            array('email' => 'foo@bar.com')
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array(
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_DAY,
            )
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $fixture->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationOnQueueMailAndUnregistrationPossibleAddsUnregistrationNotice()
    {
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class, array('getUnregistrationNotice')
        );
        $fixture->expects(self::once())->method('getUnregistrationNotice');

        $fixture->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue', true
        );
        $registration = $this->createRegistration();
        $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array(
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_DAY,
                'queue_size' => 1,
            )
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances', $registration->getUid(),
            array('registration_queue' => 1)
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $fixture->notifyAttendee(
            $registration, $pi1, 'confirmationOnRegistrationForQueue'
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForQueueUpdateMailAndUnregistrationPossibleAddsUnregistrationNotice()
    {
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class, array('getUnregistrationNotice')
        );
        $fixture->expects(self::once())->method('getUnregistrationNotice');

        $fixture->setConfigurationValue('sendConfirmationOnQueueUpdate', true);
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array(
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_DAY,
                'queue_size' => 1,
            )
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances', $registration->getUid(),
            array('registration_queue' => 1)
        );

        $pi1 = new Tx_Seminars_FrontEnd_DefaultController();
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
    public function notifyOrganizersForEventWithEmailsMutedNotSendsEmail()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->seminarUid, ['mute_notification_emails' => 1]);

        $registration = $this->createRegistration();
        $this->fixture->notifyOrganizers($registration);

        self::assertNull($this->mailer->getFirstSentEmail());
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsFrom()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);

        $registration = $this->createRegistration();
        $this->fixture->notifyOrganizers($registration);

        self::assertSame(
            array('mail@example.com' => 'test organizer'),
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsTo()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);

        $registration = $this->createRegistration();
        $this->fixture->notifyOrganizers($registration);

        self::assertArrayHasKey(
            'mail@example.com',
            $this->mailer->getFirstSentEmail()->getTo()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersIncludesHelloIfNotHidden()
    {
        $registration = $this->createRegistration();
        $this->fixture->setConfigurationValue('sendNotification', true);
        $this->fixture->setConfigurationValue(
            'hideFieldsInNotificationMail', ''
        );

        $this->fixture->notifyOrganizers($registration);

        self::assertContains(
            'Hello',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithOneVacancyShowsVacanciesLabelWithVacancyNumber()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);
        $this->fixture->setConfigurationValue(
            'showSeminarFieldsInNotificationMail', 'vacancies'
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('needs_registration' => 1, 'attendees_max' => 2)
        );

        $registration = $this->createRegistration();
        $this->fixture->notifyOrganizers($registration);

        self::assertRegExp(
            '/' . $this->fixture->translate('label_vacancies') . ': 1$/',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithUnlimitedVacanciesShowsVacanciesLabelWithUnlimtedLabel()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);
        $this->fixture->setConfigurationValue(
            'showSeminarFieldsInNotificationMail', 'vacancies'
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('needs_registration' => 1, 'attendees_max' => 0)
        );

        $registration = $this->createRegistration();
        $this->fixture->notifyOrganizers($registration);

        self::assertContains(
            $this->fixture->translate('label_vacancies') . ': ' .
                $this->fixture->translate('label_unlimited'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsLabelOfCompany()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);
        $this->fixture->setConfigurationValue(
            'showAttendanceFieldsInNotificationMail', 'company'
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo inc.',
            )
        );

        $registration = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);
        $this->fixture->notifyOrganizers($registration);

        self::assertContains(
            $this->fixture->translate('label_company'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsCompanyOfRegistration()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);
        $this->fixture->setConfigurationValue(
            'showAttendanceFieldsInNotificationMail', 'company'
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array(
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo inc.',
            )
        );

        $registration = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);
        $this->fixture->notifyOrganizers($registration);

        self::assertContains(
            'foo inc.',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersCallsModifyOrganizerNotificationEmailHookWithRegistration()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array('seminar' => $this->seminarUid, 'user' => $this->testingFramework->createFrontEndUser())
        );
        $registration = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_Registration::class);
        $hookClassName = get_class($hook);
        $hook->expects(self::once())->method('modifyOrganizerNotificationEmail')->with($registration, self::anything());

        $GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;

        $this->fixture->notifyOrganizers($registration);
    }

    /**
     * @test
     */
    public function notifyOrganizersCallsModifyOrganizerNotificationEmailHookWithTemplate()
    {
        $this->fixture->setConfigurationValue('sendNotification', true);

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            array('seminar' => $this->seminarUid, 'user' => $this->testingFramework->createFrontEndUser())
        );
        $registration = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        $hook = $this->getMock(Tx_Seminars_Interface_Hook_Registration::class);
        $hookClassName = get_class($hook);
        $hook->expects(self::once())->method('modifyOrganizerNotificationEmail')
            ->with(self::anything(), self::isInstanceOf(Tx_Oelib_Template::class));

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
    public function sendAdditionalNotificationCanSendEmailToOneOrganizer()
    {
        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertArrayHasKey(
            'mail@example.com',
            $this->mailer->getFirstSentEmail()->getTo()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEmailMutedNotSendsEmail()
    {
        $registration = $this->createRegistration();
        $event = $registration->getSeminarObject();
        $event->muteNotificationEmails();

        $this->fixture->sendAdditionalNotification($registration);

        self::assertNull($this->mailer->getFirstSentEmail());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationCanSendEmailsToTwoOrganizers()
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            array(
                'title' => 'test organizer 2',
                'email' => 'mail2@example.com',
            )
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm', $this->seminarUid,
            $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('organizers' => 2)
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertSame(
            2,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationUsesTheFirstOrganizerAsSenderIfEmailIsSentToTwoOrganizers()
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            array(
                'title' => 'test organizer 2',
                'email' => 'mail2@example.com',
            )
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm', $this->seminarUid,
            $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('organizers' => 2)
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        $sentEmails = $this->mailer->getSentEmails();

        self::assertArrayHasKey(
            'mail@example.com',
            $sentEmails[0]->getFrom()
        );
        self::assertArrayHasKey(
            'mail@example.com',
            $sentEmails[1]->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesSendsEnoughAttendancesMail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('attendees_min' => 1, 'attendees_max' => 42)
        );

        unset($this->fixture);
        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertContains(
            sprintf(
                $this->fixture->translate('email_additionalNotificationEnoughRegistrationsSubject'),
                $this->seminarUid, ''
            ),
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesNotSendsEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            ['attendees_min' => 2, 'attendees_max' => 42]
        );

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertNull($this->mailer->getFirstSentEmail());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesNotSetsNotificationFlag()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            ['attendees_min' => 2, 'attendees_max' => 42]
        );

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new tx_seminars_seminarchild($this->seminarUid);

        self::assertFalse($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithMoreThanEnoughAttendancesSendsEnoughAttendancesMail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        $firstEmail = $this->mailer->getFirstSentEmail();
        self::assertNotNull($firstEmail);
        self::assertContains(
            sprintf(
                $this->fixture->translate('email_additionalNotificationEnoughRegistrationsSubject'),
                $this->seminarUid, ''
            ),
            $firstEmail->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesSetsNotifiedFlag()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new tx_seminars_seminarchild($this->seminarUid);

        self::assertTrue($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithMoreThanEnoughAttendancesSetsNotifiedFlag()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new tx_seminars_seminarchild($this->seminarUid);

        self::assertTrue($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndOrganizersAlreadyNotifiedNotSendsEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42, 'organizers_notified_about_minimum_reached' => 1]
        );

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        $firstEmail = $this->mailer->getFirstSentEmail();
        self::assertNull($firstEmail);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithMoreThanEnoughAttendancesAndOrganizersAlreadyNotifiedNotSendsEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42, 'organizers_notified_about_minimum_reached' => 1]
        );

        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        $firstEmail = $this->mailer->getFirstSentEmail();
        self::assertNull($firstEmail);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithZeroAttendeesMinDoesNotSendAnyMail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('attendees_min' => 0, 'attendees_max' => 42)
        );

        unset($this->fixture);
        Tx_Seminars_Service_RegistrationManager::purgeInstance();
        $this->fixture = Tx_Seminars_Service_RegistrationManager::getInstance();
        $this->fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutSubject()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('attendees_max' => 1)
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertContains(
            sprintf(
                $this->fixture->translate('email_additionalNotificationIsFullSubject'),
                $this->seminarUid, ''
            ),
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutMessage()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('attendees_max' => 1)
        );
        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertContains(
            $this->fixture->translate('email_additionalNotificationIsFull'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesAndNotBookedOutSendsNoEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array('attendees_min' => 5, 'attendees_max' => 5)
        );

        $fixture = new Tx_Seminars_Service_RegistrationManager();
        $fixture->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $fixture->sendAdditionalNotification($registration);

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndUnlimitedVacanciesSendsEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
            array(
                'attendees_min' => 1,
                'attendees_max' => 0,
                'needs_registration' => 1
            )
        );

        $registration = $this->createRegistration();
        $this->fixture->sendAdditionalNotification($registration);

        self::assertSame(
            1,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndOneVacancyShowsVacanciesLabelWithVacancyNumber()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
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

        self::assertRegExp(
            '/' . $this->fixture->translate('label_vacancies') . ': 1$/',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndUnlimitedVacanciesShowsVacanciesLabelWithUnlimitedLabel()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid,
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

        self::assertContains(
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
    public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateAllowedReturnsTrue()
    {
        $this->seminar->setAllowRegistrationForEventsWithoutDate(1);
        $this->seminar->setBeginDate(0);

        self::assertTrue(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateNotAllowedReturnsFalse()
    {
        $this->seminar->setAllowRegistrationForEventsWithoutDate(0);
        $this->seminar->setBeginDate(0);

        self::assertFalse(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineOverReturnsFalse()
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] - 42);

        self::assertFalse(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineInFutureReturnsTrue()
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertTrue(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForRegistrationBeginInFutureReturnsFalse()
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] + 10);

        self::assertFalse(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForRegistrationBeginInPastReturnsTrue()
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);

        self::assertTrue(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForNoRegistrationBeginReturnsTrue()
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationBeginDate(0);

        self::assertTrue(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForBeginDateInPastAndRegistrationBeginInPastReturnsFalse()
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);
        $this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 50);

        self::assertFalse(
            $this->fixture->allowsRegistrationByDate($this->seminar)
        );
    }

    /*
     * Tests concerning allowsRegistrationBySeats
     */

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithNoVacanciesAndNoQueueReturnsFalse()
    {
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setRegistrationQueue(false);

        self::assertFalse(
            $this->fixture->allowsRegistrationBySeats($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithUnlimitedVacanciesReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->fixture->allowsRegistrationBySeats($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithRegistrationQueueReturnsTrue()
    {
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->fixture->allowsRegistrationBySeats($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithVacanciesReturnsTrue()
    {
        $this->seminar->setNumberOfAttendances(0);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setRegistrationQueue(false);

        self::assertTrue(
            $this->fixture->allowsRegistrationBySeats($this->seminar)
        );
    }

    /*
     * Tests concerning registrationHasStarted
     */

    /**
     * @test
     */
    public function registrationHasStartedForEventWithoutRegistrationBeginReturnsTrue()
    {
        $this->seminar->setRegistrationBeginDate(0);

        self::assertTrue(
            $this->fixture->registrationHasStarted($this->seminar)
        );
    }

    /**
     * @test
     */
    public function registrationHasStartedForEventWithRegistrationBeginInPastReturnsTrue()
    {
        $this->seminar->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] - 42
        );

        self::assertTrue(
            $this->fixture->registrationHasStarted($this->seminar)
        );
    }

    /**
     * @test
     */
    public function registrationHasStartedForEventWithRegistrationBeginInFutureReturnsFalse()
    {
        $this->seminar->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] + 42
        );

        self::assertFalse(
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
    public function createRegistrationSavesRegistration()
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new Tx_Seminars_FrontEnd_DefaultController();
        $plugin->cObj = $GLOBALS['TSFE']->cObj;
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class,
            array(
                'notifyAttendee', 'notifyOrganizers',
                'sendAdditionalNotification', 'setRegistrationData'
            )
        );

        $fixture->createRegistration($this->seminar, array(), $plugin);

        self::assertInstanceOf(
            Tx_Seminars_OldModel_Registration::class,
            $fixture->getRegistration()
        );
        $uid = $fixture->getRegistration()->getUid();
        self::assertTrue(
            // We're not using the testing framework here because the record
            // is not marked as dummy record.
            Tx_Oelib_Db::existsRecordWithUid(
                'tx_seminars_attendances', $uid
            )
        );

        Tx_Oelib_Db::delete('tx_seminars_attendances', 'uid = ' . $uid);
    }

    /**
     * @test
     */
    public function createRegistrationIncreasesRegistrationCountInEventFromZeroToOne()
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new Tx_Seminars_FrontEnd_DefaultController();
        $plugin->cObj = $GLOBALS['TSFE']->cObj;
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class,
            array(
                'notifyAttendee', 'notifyOrganizers',
                'sendAdditionalNotification', 'setRegistrationData'
            )
        );

        $fixture->createRegistration($this->seminar, array(), $plugin);

        $seminarData = Tx_Oelib_Db::selectSingle('*', 'tx_seminars_seminars', 'uid = ' . $this->seminarUid);

        $registrationUid = $fixture->getRegistration()->getUid();
        Tx_Oelib_Db::delete('tx_seminars_attendances', 'uid = ' . $registrationUid);

        self::assertSame(1, (int)$seminarData['registrations']);
    }

    /**
     * @test
     */
    public function createRegistrationReturnsRegistration()
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new Tx_Seminars_FrontEnd_DefaultController();
        $plugin->cObj = $GLOBALS['TSFE']->cObj;
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class,
            array(
                'notifyAttendee', 'notifyOrganizers',
                'sendAdditionalNotification', 'setRegistrationData'
            )
        );

        $registration = $fixture->createRegistration($this->seminar, array(), $plugin);

        $uid = $fixture->getRegistration()->getUid();
        // @TODO: This line needs to be removed once createRegistration uses
        // the data mapper to save the registration.
        Tx_Oelib_Db::delete('tx_seminars_attendances', 'uid = ' . $uid);

        self::assertInstanceOf(
            Tx_Seminars_Model_Registration::class,
            $registration
        );
    }

    /**
     * @TODO: This is just a transitional test that can be removed once
     * createRegistration does not use the old registration model anymore.
     *
     * @test
     */
    public function createRegistrationCreatesOldAndNewRegistrationModelForTheSameUid()
    {
        // Drops the non-saving mapper so that the registration mapper (once we
        // use it) actually saves the registration.
        Tx_Oelib_MapperRegistry::purgeInstance();
        Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode(
            $this->testingFramework
        );
        $this->testingFramework->markTableAsDirty('tx_seminars_seminars');

        $this->createAndLogInFrontEndUser();

        $plugin = new Tx_Seminars_FrontEnd_DefaultController();
        $plugin->cObj = $GLOBALS['TSFE']->cObj;
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class,
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
        Tx_Oelib_Db::delete('tx_seminars_attendances', 'uid = ' . $uid);

        self::assertSame(
            $registration->getUid(),
            $fixture->getRegistration()->getUid()
        );
    }

    /**
     * @test
     */
    public function createRegistrationCallsSeminarRegistrationCreatedHook()
    {
        $this->createAndLoginFrontEndUser();

        $hookClass = uniqid('tx_registrationHook');
        $hook = $this->getMock($hookClass, array('seminarRegistrationCreated'));
        // We cannot test for the expected parameters because the registration
        // instance does not exist yet at this point.
        $hook->expects(self::once())->method('seminarRegistrationCreated');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration']
            [$hookClass] = $hookClass;

        $plugin = new Tx_Seminars_FrontEnd_DefaultController();
        $plugin->cObj = $GLOBALS['TSFE']->cObj;
        $fixture = $this->getMock(
            Tx_Seminars_Service_RegistrationManager::class,
            array(
                'notifyAttendee', 'notifyOrganizers',
                'sendAdditionalNotification', 'setRegistrationData'
            )
        );

        $fixture->createRegistration(
            $this->seminar, array(), $plugin
        );

        $uid = $fixture->getRegistration()->getUid();

        Tx_Oelib_Db::delete('tx_seminars_attendances', 'uid = ' . $uid);
    }

    /*
     * Tests concerning setRegistrationData()
     */

    /**
     * @test
     */
    public function setRegistrationDataForPositiveSeatsSetsSeats()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** $event Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('seats' => '3')
        );

        self::assertSame(
            3,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingSeatsSetsOneSeat()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array()
        );

        self::assertSame(
            1,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForZeroSeatsSetsOneSeat()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('seats' => '0')
        );

        self::assertSame(
            1,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNegativeSeatsSetsOneSeat()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('seats' => '-1')
        );

        self::assertSame(
            1,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForRegisteredThemselvesOneSetsItToTrue()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('registered_themselves' => '1')
        );

        self::assertTrue(
            $registration->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForRegisteredThemselvesZeroSetsItToFalse()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('registered_themselves' => '0')
        );

        self::assertFalse(
            $registration->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForRegisteredThemselvesMissingSetsItToFalse()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array()
        );

        self::assertFalse(
            $registration->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePricePutsSelectedPriceCodeToPrice()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices'));
        $event->setData(array('payment_methods' => new Tx_Oelib_List()));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12, 'special' => 3)));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('price' => 'special')
        );

        self::assertSame(
            'special',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedNotAvailablePricePutsFirstPriceCodeToPrice()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices'));
        $event->setData(array('payment_methods' => new Tx_Oelib_List()));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('price' => 'early_bird_regular')
        );

        self::assertSame(
            'regular',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNoSelectedPricePutsFirstPriceCodeToPrice()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices'));
        $event->setData(array('payment_methods' => new Tx_Oelib_List()));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array()
        );

        self::assertSame(
            'regular',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNoSelectedAndOnlyFreeRegularPriceAvailablePutsRegularPriceCodeToPrice()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices'));
        $event->setData(array('payment_methods' => new Tx_Oelib_List()));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 0)));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array()
        );

        self::assertSame(
            'regular',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForOneSeatsCalculatesTotalPriceFromSelectedPriceAndSeats()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices'));
        $event->setData(array('payment_methods' => new Tx_Oelib_List()));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('price' => 'regular', 'seats' => '1')
        );

        self::assertSame(
            12.0,
            $registration->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForTwoSeatsCalculatesTotalPriceFromSelectedPriceAndSeats()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices'));
        $event->setData(array('payment_methods' => new Tx_Oelib_List()));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('price' => 'regular', 'seats' => '2')
        );

        self::assertSame(
            24.0,
            $registration->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAttendeesNamesSetsAttendeesNames()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('attendees_names' => 'John Doe' . LF . 'Jane Doe')
        );

        self::assertSame(
            'John Doe' . LF . 'Jane Doe',
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAttendeesNames()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('attendees_names' => 'John <em>Doe</em>')
        );

        self::assertSame(
            'John Doe',
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAttendeesNamesSetsEmptyAttendeesNames()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('attendees_names' => '')
        );

        self::assertSame(
            '',
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAttendeesNamesSetsEmptyAttendeesNames()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array()
        );

        self::assertSame(
            '',
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForPositiveKidsSetsNumberOfKids()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('kids' => '3')
        );

        self::assertSame(
            3,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingKidsSetsZeroKids()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array()
        );

        self::assertSame(
            0,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForZeroKidsSetsZeroKids()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('kids' => '0')
        );

        self::assertSame(
            0,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNegativeKidsSetsZeroKids()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('kids' => '-1')
        );

        self::assertSame(
            0,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneSetsIt()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $paymentMethod = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Tx_Oelib_List();
        $paymentMethods->add($paymentMethod);

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue($paymentMethods));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod->getUid()));

        self::assertSame(
            $paymentMethod,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromTwoSetsIt()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $paymentMethod1 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethod2 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Tx_Oelib_List();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue($paymentMethods));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod2->getUid()));

        self::assertSame(
            $paymentMethod2,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneForFreeEventsSetsNoPaymentMethod()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $paymentMethod = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Tx_Oelib_List();
        $paymentMethods->add($paymentMethod);

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 0)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue($paymentMethods));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod->getUid()));

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndNoneAvailableSetsNone()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 0)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue(new Tx_Oelib_List()));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array()
        );

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndTwoAvailableSetsNone()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $paymentMethod1 = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethod2 = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Tx_Oelib_List();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue($paymentMethods));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndOneAvailableSetsIt()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $paymentMethod = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Tx_Oelib_List();
        $paymentMethods->add($paymentMethod);

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue($paymentMethods));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            $paymentMethod,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForUnavailablePaymentMethodAndTwoAvailableSetsNone()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $paymentMethod1 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethod2 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Tx_Oelib_List();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue($paymentMethods));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('method_of_payment' => max($paymentMethod1->getUid(), $paymentMethod2->getUid()) + 1)
        );

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForUnavailablePaymentMethodAndOneAvailableSetsAvailable()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        $paymentMethod = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Tx_Oelib_List();
        $paymentMethods->add($paymentMethod);

        $event = $this->getMock(Tx_Seminars_Model_Event::class, array('getAvailablePrices', 'getPaymentMethods'));
        $event->expects(self::any())->method('getAvailablePrices')
            ->will(self::returnValue(array('regular' => 12)));
        $event->expects(self::any())->method('getPaymentMethods')
            ->will(self::returnValue($paymentMethods));
        $registration = new Tx_Seminars_Model_Registration();
        /** @var Tx_Seminars_Model_Event $event */
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('method_of_payment' => $paymentMethod->getUid() + 1));

        self::assertSame(
            $paymentMethod,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAccountNumberSetsAccountNumber()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('account_number' => '123 455 ABC'));

        self::assertSame(
            '123 455 ABC',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAccountNumber()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('account_number' => '123 <em>455</em> ABC'));

        self::assertSame(
            '123 455 ABC',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInAccountNumber()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('account_number' => '123' . CRLF . '455'  . TAB . ' ABC')
        );

        self::assertSame(
            '123 455 ABC',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAccountNumberSetsEmptyAccountNumber()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('account_number' => ''));

        self::assertSame(
            '',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAccountNumberSetsEmptyAccountNumber()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyBankCodeSetsBankCode()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('bank_code' => '123 455 ABC'));

        self::assertSame(
            '123 455 ABC',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromBankCode()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('bank_code' => '123 <em>455</em> ABC'));

        self::assertSame(
            '123 455 ABC',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInBankCode()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('bank_code' => '123' . CRLF . '455'  . TAB . ' ABC')
        );

        self::assertSame(
            '123 455 ABC',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyBankCodeSetsEmptyBankCode()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('bank_code' => ''));

        self::assertSame(
            '',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingBankCodeSetsEmptyBankCode()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyBankNameSetsBankName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('bank_name' => 'Swiss Tax Protection'));

        self::assertSame(
            'Swiss Tax Protection',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromBankName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('bank_name' => 'Swiss <em>Tax</em> Protection'));

        self::assertSame(
            'Swiss Tax Protection',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInBankName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('bank_name' => 'Swiss' . CRLF . 'Tax'  . TAB . ' Protection')
        );

        self::assertSame(
            'Swiss Tax Protection',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyBankNameSetsEmptyBankName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('bank_name' => ''));

        self::assertSame(
            '',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingBankNameSetsEmptyBankName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAccountOwnerSetsAccountOwner()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('account_owner' => 'John Doe'));

        self::assertSame(
            'John Doe',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAccountOwner()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('account_owner' => 'John <em>Doe</em>'));

        self::assertSame(
            'John Doe',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInAccountOwner()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('account_owner' => 'John' . CRLF . TAB . ' Doe')
        );

        self::assertSame(
            'John Doe',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAccountOwnerSetsEmptyAccountOwner()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('account_owner' => ''));

        self::assertSame(
            '',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAccountOwnerSetsEmptyAccountOwner()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyCompanySetsCompany()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('company' => 'Business Ltd.' . LF . 'Tom, Dick & Harry')
        );

        self::assertSame(
            'Business Ltd.' . LF . 'Tom, Dick & Harry',
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromCompany()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('company' => 'Business <em>Ltd.</em>'));

        self::assertSame(
            'Business Ltd.',
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyCompanySetsEmptyCompany()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('company' => ''));

        self::assertSame(
            '',
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingCompanySetsEmptyCompany()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMaleGenderSetsGender()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('gender' => (string) Tx_Oelib_Model_FrontEndUser::GENDER_MALE)
        );

        self::assertSame(
            Tx_Oelib_Model_FrontEndUser::GENDER_MALE,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForFemaleGenderSetsGender()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData(
            $registration, array('gender' => (string) Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE)
        );

        self::assertSame(
            Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForInvalidIntegerGenderSetsUnknownGender()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('gender' => '42'));

        self::assertSame(
            Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForInvalidStringGenderSetsUnknownGender()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('gender' => 'Mr. Fantastic'));

        self::assertSame(
            Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyGenderSetsUnknownGender()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('gender' => ''));

        self::assertSame(
            Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingGenderSetsUnknownGender()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyNameSetsName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('name' => 'John Doe'));

        self::assertSame(
            'John Doe',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('name' => 'John <em>Doe</em>'));

        self::assertSame(
            'John Doe',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('name' => 'John' . CRLF . TAB . ' Doe'));

        self::assertSame(
            'John Doe',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyNameSetsEmptyName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('name' => ''));

        self::assertSame(
            '',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingNameSetsEmptyName()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAddressSetsAddress()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('address' => 'Back Road 42' . LF . '(second door)'));

        self::assertSame(
            'Back Road 42' . LF . '(second door)',
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAddress()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('address' => 'Back <em>Road</em> 42'));

        self::assertSame(
            'Back Road 42',
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAddressSetsEmptyAddress()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('address' => ''));

        self::assertSame(
            '',
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAddressSetsEmptyAddress()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyZipSetsZip()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('zip' => '12345 ABC'));

        self::assertSame(
            '12345 ABC',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromZip()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('zip' => '12345 <em>ABC</em>'));

        self::assertSame(
            '12345 ABC',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInZip()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('zip' => '12345' . CRLF . TAB . ' ABC'));

        self::assertSame(
            '12345 ABC',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyZipSetsEmptyZip()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('zip' => ''));

        self::assertSame(
            '',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingZipSetsEmptyZip()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyCitySetsCity()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('city' => 'Elmshorn'));

        self::assertSame(
            'Elmshorn',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromCity()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('city' => 'Santiago de <em>Chile</em>'));

        self::assertSame(
            'Santiago de Chile',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInCity()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('city' => 'Santiago' . CRLF . TAB . ' de Chile'));

        self::assertSame(
            'Santiago de Chile',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyCitySetsEmptyCity()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('city' => ''));

        self::assertSame(
            '',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingCitySetsEmptyCity()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyCountrySetsCountry()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('country' => 'Brazil'));

        self::assertSame(
            'Brazil',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromCountry()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('country' => 'South <em>Africa</em>'));

        self::assertSame(
            'South Africa',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInCountry()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('country' => 'South' . CRLF . TAB . ' Africa'));

        self::assertSame(
            'South Africa',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyCountrySetsEmptyCountry()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array('country' => ''));

        self::assertSame(
            '',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingCountrySetsEmptyCountry()
    {
        $className = $this->createAccessibleProxyClass();
        /** @var Tx_Seminars_Service_RegistrationManager $fixture */
        $fixture = new $className();

        /** @var Tx_Seminars_Model_Event $event */
        $event = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel(array());
        $registration = new Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $fixture->setRegistrationData($registration, array());

        self::assertSame(
            '',
            $registration->getCountry()
        );
    }

    /*
     *  Tests concerning existsSeminar and existsSeminarMessage
     */

    /**
     * @test
     */
    public function existsSeminarForZeroUidReturnsFalse()
    {
        self::assertFalse(
            $this->fixture->existsSeminar(0)
        );
    }

    /**
     * @test
     */
    public function existsSeminarForInvalidStringUidReturnsFalse()
    {
        self::assertFalse(
            $this->fixture->existsSeminar('Hello world!')
        );
    }

    /**
     * @test
     */
    public function existsSeminarForInexistentUidReturnsFalse()
    {
        self::assertFalse(
            $this->fixture->existsSeminar($this->testingFramework->getAutoIncrement('tx_seminars_seminars'))
        );
    }

    /**
     * @test
     */
    public function existsSeminarForExistingDeleteUidReturnsFalse()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array('deleted' => 1)
        );

        self::assertFalse(
            $this->fixture->existsSeminar($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarForExistingHiddenUidReturnsFalse()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array('hidden' => 1)
        );

        self::assertFalse(
            $this->fixture->existsSeminar($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarForExistingUidReturnsTrue()
    {
        self::assertTrue(
            $this->fixture->existsSeminar($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForZeroUidReturnsErrorMessage()
    {
        self::assertContains(
            $this->fixture->translate('message_missingSeminarNumber'),
            $this->fixture->existsSeminarMessage(0)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForZeroUidSendsNotFoundHeader()
    {
        $this->fixture->existsSeminarMessage(0);

        self::assertSame(
            'Status: 404 Not Found',
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForInvalidStringUidReturnsErrorMessage()
    {
        self::assertContains(
            $this->fixture->translate('message_missingSeminarNumber'),
            $this->fixture->existsSeminarMessage('Hello world!')
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForInexistentUidReturnsErrorMessage()
    {
        self::assertContains(
            $this->fixture->translate('message_wrongSeminarNumber'),
            $this->fixture->existsSeminarMessage($this->testingFramework->getAutoIncrement('tx_seminars_seminars'))
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForInexistentUidSendsNotFoundHeader()
    {
        $this->fixture->existsSeminarMessage($this->testingFramework->getAutoIncrement('tx_seminars_seminars'));

        self::assertSame(
            'Status: 404 Not Found',
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingDeleteUidReturnsErrorMessage()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array('deleted' => 1)
        );

        self::assertContains(
            $this->fixture->translate('message_wrongSeminarNumber'),
            $this->fixture->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingHiddenUidReturnsErrorMessage()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $this->seminarUid, array('hidden' => 1)
        );

        self::assertContains(
            $this->fixture->translate('message_wrongSeminarNumber'),
            $this->fixture->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingUidReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->fixture->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingUidNotSendsHttpHeader()
    {
        $this->fixture->existsSeminarMessage($this->seminarUid);

        self::assertSame(
            array(),
            $this->headerCollector->getAllAddedHeaders()
        );
    }
}
