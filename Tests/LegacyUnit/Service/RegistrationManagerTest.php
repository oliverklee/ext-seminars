<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\PaymentMethodMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\Registration;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Service\SingleViewLinkBuilder;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingLegacyEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures\TestingRegistrationManager;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationManager
 */
final class RegistrationManagerTest extends TestCase
{
    use LanguageHelper;
    use EmailTrait;
    use MakeInstanceTrait;

    /**
     * @var TestingRegistrationManager
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var int
     */
    private $seminarUid = 0;

    /**
     * @var TestingLegacyEvent
     */
    private $seminar;

    /**
     * @var int the UID of a fake front-end user
     */
    private $frontEndUserUid = 0;

    /**
     * @var int UID of a fake login page
     */
    private $loginPageUid = 0;

    /**
     * @var int UID of a fake registration page
     */
    private $registrationPageUid = 0;

    /**
     * @var DefaultController a front-end plugin
     */
    private $pi1;

    /**
     * @var TestingLegacyEvent
     */
    private $fullyBookedSeminar;

    /**
     * @var TestingLegacyEvent
     */
    private $cachedSeminar;

    /**
     * backed-up extension configuration of the TYPO3 configuration variables
     *
     * @var array
     */
    private $extConfBackup = [];

    /**
     * @var HeaderCollector
     */
    private $headerCollector;

    /**
     * @var FrontEndUserMapper
     */
    private $frontEndUserMapper;

    /**
     * @var array<int, class-string<MockObject>>
     */
    private $mockedClassNames = [];

    /**
     * @var (MailMessage&MockObject)|null
     */
    private $secondEmail = null;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * @var DummyConfiguration
     */
    private $extensionConfiguration;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];

        $this->testingFramework = new TestingFramework('tx_seminars');
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->changeRecord('pages', $rootPageUid, ['slug' => '/home']);
        $this->testingFramework->createFakeFrontEnd($rootPageUid);

        $this->email = $this->createEmailMock();
        $this->secondEmail = $this->createEmailMock();
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->secondEmail);

        LegacyRegistration::purgeCachedSeminars();
        $this->extensionConfiguration = new DummyConfiguration(
            ['eMailFormatForAttendees' => TestingRegistrationManager::SEND_TEXT_MAIL]
        );
        ConfigurationProxy::setInstance('seminars', $this->extensionConfiguration);
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $this->configuration = new DummyConfiguration(
            [
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html',
            ]
        );
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new DummyConfiguration());
        $configurationRegistry->set('config', new DummyConfiguration());
        $configurationRegistry->set('page.config', new DummyConfiguration());

        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'test organizer',
                'email' => 'mail@example.com',
                'email_footer' => 'organizer footer',
            ]
        );
        $this->seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'test event',
                'subtitle' => 'juggling with burning chainsaws',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'attendees_min' => 1,
                'attendees_max' => 10,
                'needs_registration' => 1,
                'organizers' => 1,
            ]
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $this->seminarUid, $organizerUid);

        $headerProxyFactory = HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerCollector = $headerProxyFactory->getHeaderCollector();

        $this->seminar = new TestingLegacyEvent($this->seminarUid);
        $this->subject = TestingRegistrationManager::getInstance();

        /** @var SingleViewLinkBuilder&MockObject $linkBuilder */
        $linkBuilder = $this->createPartialMock(
            SingleViewLinkBuilder::class,
            ['createAbsoluteUrlForEvent']
        );
        $linkBuilder->method('createAbsoluteUrlForEvent')->willReturn('https://singleview.example.com/');
        $this->subject->injectLinkBuilder($linkBuilder);

        $frontEndUserMapper = MapperRegistry::get(FrontEndUserMapper::class);
        $this->frontEndUserMapper = $frontEndUserMapper;
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        $this->purgeMockedInstances();

        ConfigurationProxy::purgeInstances();
        TestingRegistrationManager::purgeInstance();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
    }

    // Utility functions

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Creates a dummy login page and registration page and stores their UIDs
     * in $this->loginPageUid and $this->registrationPageUid.
     *
     * In addition, it provides the fixture's configuration with the UIDs.
     */
    private function createFrontEndPages(): void
    {
        $this->loginPageUid = $this->testingFramework->createFrontEndPage();
        $this->registrationPageUid
            = $this->testingFramework->createFrontEndPage();

        $this->pi1 = new DefaultController();

        $this->pi1->init(
            [
                'isStaticTemplateLoaded' => 1,
                'loginPID' => $this->loginPageUid,
                'registerPID' => $this->registrationPageUid,
            ]
        );
    }

    /**
     * Creates a FE user, stores it UID in $this->frontEndUserUid and logs it in.
     */
    private function createAndLogInFrontEndUser(): void
    {
        $this->frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
    }

    /**
     * Creates a seminar which is booked out.
     */
    private function createBookedOutSeminar(): void
    {
        $this->fullyBookedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'title' => 'test event',
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                    'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                    'attendees_max' => 10,
                    'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                    'needs_registration' => 1,
                    'queue_size' => 0,
                ]
            )
        );
        $this->fullyBookedSeminar->setNumberOfAttendances(10);
    }

    /**
     * Returns and creates a registration.
     *
     * A new front-end user will be created and the event in $this->seminar will be used.
     *
     * @return LegacyRegistration the created registration
     */
    private function createRegistration(): LegacyRegistration
    {
        $frontEndUserUid = $this->testingFramework->createFrontEndUser(
            '',
            [
                'name' => 'Harry Callagan',
                'email' => 'foo@bar.com',
            ]
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $frontEndUserUid,
                'food' => 'something nice to eat',
                'accommodation' => 'a nice, dry place',
                'interests' => 'learning Ruby on Rails',
            ]
        );

        return new LegacyRegistration($registrationUid);
    }

    /**
     * Adds an instance to the Typo3 instance FIFO buffer used by `GeneralUtility::makeInstance()`
     * and registers it for purging in `tearDown()`.
     *
     * In case of a failing test or an exception in the test before the instance is taken
     * from the FIFO buffer, the instance would stay in the buffer and make following tests
     * fail. This function adds it to the list of instances to purge in `tearDown()` in addition
     * to `GeneralUtility::addInstance()`.
     *
     * @param class-string $className
     */
    private function addMockedInstance(string $className, object $instance): void
    {
        GeneralUtility::addInstance($className, $instance);
        $this->mockedClassNames[] = $className;
    }

    /**
     * Purges possibly leftover instances from the Typo3 instance FIFO buffer used by
     * `GeneralUtility::makeInstance()`.
     */
    private function purgeMockedInstances(): void
    {
        foreach ($this->mockedClassNames as $className) {
            GeneralUtility::makeInstance($className);
        }

        $this->mockedClassNames = [];
    }

    // Tests for the utility functions

    /**
     * @test
     */
    public function createFrontEndPagesCreatesNonZeroLoginPageUid(): void
    {
        $this->createFrontEndPages();

        self::assertGreaterThan(
            0,
            $this->loginPageUid
        );
    }

    /**
     * @test
     */
    public function createFrontEndPagesCreatesNonZeroRegistrationPageUid(): void
    {
        $this->createFrontEndPages();

        self::assertGreaterThan(
            0,
            $this->registrationPageUid
        );
    }

    /**
     * @test
     */
    public function createFrontEndPagesCreatesPi1(): void
    {
        $this->createFrontEndPages();

        self::assertNotNull(
            $this->pi1
        );
        self::assertInstanceOf(
            DefaultController::class,
            $this->pi1
        );
    }

    /**
     * @test
     */
    public function createAndLogInFrontEndUserCreatesNonZeroUserUid(): void
    {
        $this->createAndLogInFrontEndUser();

        self::assertGreaterThan(
            0,
            $this->frontEndUserUid
        );
    }

    /**
     * @test
     */
    public function createAndLogInFrontEndUserLogsInFrontEndUser(): void
    {
        $this->createAndLogInFrontEndUser();
        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createBookedOutSeminarSetsSeminarInstance(): void
    {
        $this->createBookedOutSeminar();

        self::assertInstanceOf(
            LegacyEvent::class,
            $this->fullyBookedSeminar
        );
    }

    /**
     * @test
     */
    public function createdBookedOutSeminarHasUidGreaterZero(): void
    {
        $this->createBookedOutSeminar();

        self::assertTrue(
            $this->fullyBookedSeminar->getUid() > 0
        );
    }

    /**
     * @test
     */
    public function mockedInstancesListInitiallyHasTwoInstances(): void
    {
        self::assertCount(2, $this->mockedClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsClassnameToList(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        /** @var class-string<\stdClass&MockObject> $mockedClassName */
        $mockedClassName = \get_class($mockedInstance);

        $this->addMockedInstance($mockedClassName, $mockedInstance);
        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertCount(3, $this->mockedClassNames);
        self::assertSame($mockedClassName, $this->mockedClassNames[2]);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsInstanceToTypo3InstanceBuffer(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        /** @var class-string<\stdClass&MockObject> $mockedClassName */
        $mockedClassName = \get_class($mockedInstance);

        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested yet
        GeneralUtility::makeInstance($mockedClassName);

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesClassnameFromList(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        /** @var class-string<\stdClass&MockObject> $mockedClassName */
        $mockedClassName = \get_class($mockedInstance);
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();
        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested for that yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertEmpty($this->mockedClassNames);
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesInstanceFromTypo3InstanceBuffer(): void
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        /** @var class-string<\stdClass&MockObject> $mockedClassName */
        $mockedClassName = \get_class($mockedInstance);
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();

        self::assertNotSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }

    // Tests regarding the Singleton property.

    /**
     * @test
     */
    public function getInstanceReturnsRegistrationManagerInstance(): void
    {
        self::assertInstanceOf(
            RegistrationManager::class,
            TestingRegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceReturnsTestingRegistrationManagerInstance(): void
    {
        self::assertInstanceOf(
            TestingRegistrationManager::class,
            TestingRegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            TestingRegistrationManager::getInstance(),
            TestingRegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        $firstInstance = TestingRegistrationManager::getInstance();
        TestingRegistrationManager::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            TestingRegistrationManager::getInstance()
        );
    }

    // Tests for the link to the registration page

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkTag(): void
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertStringContainsString(
            '<a ',
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage(): void
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertStringContainsString(
            '?id=' . $this->loginPageUid,
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserContainsRedirectWithEventUid(): void
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertStringContainsString(
            'redirect_url',
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
        self::assertStringContainsString(
            '%255Bseminar%255D%3D' . $this->seminarUid,
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkTag(): void
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertStringContainsString(
            '<a ',
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid(): void
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertStringContainsString(
            '?id=' . $this->registrationPageUid,
            $this->subject->getLinkToRegistrationOrLoginPage(
                $this->pi1,
                $this->seminar
            )
        );
        self::assertStringContainsString(
            '%5Bseminar%5D=' . $this->seminarUid,
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInAndSeparateDetailsPageCreatesLinkToRegistrationPage(): void
    {
        $this->createFrontEndPages();

        $detailsPageUid = $this->testingFramework->createFrontEndPage();
        $this->seminar->setDetailsPage($detailsPageUid);

        $this->createAndLogInFrontEndUser();

        self::assertStringContainsString(
            '?id=' . $this->registrationPageUid,
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserDoesNotContainRedirect(): void
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertStringNotContainsString(
            'redirect_url',
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndSeminarWithoutDateHasLinkWithPrebookingLabel(): void
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate(0);

        self::assertStringContainsString(
            $this->translate('label_onlinePrebooking'),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInSeminarWithoutDateAndNoVacanciesHasRegistrationLabel(): void
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate(0);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);

        self::assertStringContainsString(
            $this->translate('label_onlineRegistration'),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndFullyBookedSeminarWithQueueHasQueueRegistration(): void
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertStringContainsString(
            \sprintf($this->translate('label_onlineRegistrationOnQueue'), 0),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutAndFullyBookedSeminarWithQueueHasQueueRegistration(): void
    {
        $this->createFrontEndPages();
        $this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertStringContainsString(
            \sprintf($this->translate('label_onlineRegistrationOnQueue'), 0),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    // Tests concerning canRegisterIfLoggedIn

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarRegistrationOpenReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForPriceOnRequestReturnsFalse(): void
    {
        $this->seminar->setPriceOnRequest(true);

        self::assertFalse($this->subject->canRegisterIfLoggedIn($this->seminar));
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInUserAndRegistrationOpenReturnsTrue(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserReturnsFalse(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredAndWithMultipleRegistrationsAllowedIsTrue(): void
    {
        $this->seminar->setAllowsMultipleRegistrations(true);

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInButBlockedUserReturnsFalse(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                    'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                    'attendees_max' => 10,
                    'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                ]
            )
        );

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarReturnsFalse(): void
    {
        $this->createBookedOutSeminar();

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndCanceledSeminarReturnsFalse(): void
    {
        $this->seminar->setStatus(Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarWithoutRegistrationReturnsFalse(): void
    {
        $this->seminar->setAttendancesMax(0);
        $this->seminar->setNeedsRegistration(false);

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsTrue(): void
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsTrue(): void
    {
        $this->seminar->setUnlimitedVacancies();
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarWithQueueReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInUserAndFullyBookedSeminarWithQueueReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    // Tests concerning canRegisterIfLoggedInMessage

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarRegistrationOpenReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInUserAndRegistrationOpenReturnsEmptyString(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredUserReturnsAlreadyRegisteredMessage(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        self::assertSame(
            $this->translate('message_alreadyRegistered'),
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredAndMultipleRegistrationsAllowedIsEmpty(): void
    {
        $this->seminar->setAllowsMultipleRegistrations(true);

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButBlockedUserReturnsUserIsBlockedMessage(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                    'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                    'attendees_max' => 10,
                    'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                ]
            )
        );

        self::assertSame(
            $this->translate('message_userIsBlocked'),
            $this->subject->canRegisterIfLoggedInMessage($this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarReturnsFullyBookedMessage(): void
    {
        $this->createBookedOutSeminar();

        self::assertSame(
            $this->translate('message_noVacancies'),
            $this->subject->canRegisterIfLoggedInMessage($this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndCanceledSeminarReturnsSeminarCancelledMessage(): void
    {
        $this->seminar->setStatus(Event::STATUS_CANCELED);

        self::assertSame(
            $this->translate('message_seminarCancelled'),
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutAndWithoutRegistrationReturnsNoRegistrationNeededMessage(): void
    {
        $this->seminar->setAttendancesMax(0);
        $this->seminar->setNeedsRegistration(false);

        self::assertSame(
            $this->translate('message_noRegistrationNecessary'),
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsEmptyString(): void
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInUserAndSeminarWithUnlimitedVacanciesReturnsEmptyString(): void
    {
        $this->seminar->setUnlimitedVacancies();
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarWithQueueReturnsEmptyString(): void
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInUserAndFullyBookedSeminarWithQueueReturnsEmptyString(): void
    {
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setRegistrationQueue(true);
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    // Test concerning userFulfillsRequirements

    /**
     * @test
     */
    public function userFulfillsRequirementsForEventWithoutRequirementsReturnsTrue(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        self::assertTrue(
            $this->subject->userFulfillsRequirements($this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function userFulfillsRequirementsForEventWithOneFulfilledRequirementReturnsTrue(): void
    {
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $requiredDateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $requiredDateUid,
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
            ]
        );

        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        self::assertTrue(
            $this->subject->userFulfillsRequirements($this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function userFulfillsRequirementsForEventWithOneUnfulfilledRequirementReturnsFalse(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );

        self::assertFalse(
            $this->subject->userFulfillsRequirements($this->cachedSeminar)
        );
    }

    // Tests concerning getMissingRequiredTopics

    /**
     * @test
     */
    public function getMissingRequiredTopicsReturnsSeminarBag(): void
    {
        self::assertInstanceOf(
            EventBag::class,
            $this->subject->getMissingRequiredTopics($this->seminar)
        );
    }

    /**
     * @test
     */
    public function getMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsOneItem(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );
        $missingTopics = $this->subject->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            1,
            $missingTopics->count()
        );
    }

    /**
     * @test
     */
    public function getMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsRequiredTopic(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );
        $missingTopics = $this->subject->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            $requiredTopicUid,
            $missingTopics->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function getMissingRequiredTopicsForTopicWithOneTwoNotFulfilledRequirementReturnsTwoItems(): void
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid1,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid1,
            'requirements'
        );

        $requiredTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid2,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );
        $missingTopics = $this->subject->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            2,
            $missingTopics->count()
        );
    }

    /**
     * @test
     */
    public function getMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledReturnsUnfulfilled(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $requiredDateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid1,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid1,
            'requirements'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $requiredDateUid1, 'user' => $userUid]
        );

        $requiredTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );
        $missingTopics = $this->subject->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            $requiredTopicUid2,
            $missingTopics->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function getMissingRequiredTopicsForTopicWithOneFulfilledOneUnfulfilledDoesNotReturnFulfilled(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $requiredDateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $requiredTopicUid1,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid1,
            'requirements'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $requiredDateUid1, 'user' => $userUid]
        );

        $requiredTopicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );

        $this->cachedSeminar = new TestingLegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => Event::TYPE_DATE,
                    'topic' => $topicUid,
                ]
            )
        );
        $missingTopics = $this->subject->getMissingRequiredTopics(
            $this->cachedSeminar
        );

        self::assertSame(
            1,
            $missingTopics->count()
        );
    }

    // Tests concerning removeRegistration

    /**
     * @test
     */
    public function removeRegistrationHidesRegistrationOfUser(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $seminarUid = $this->seminarUid;
        $this->createFrontEndPages();

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $userUid,
                'seminar' => $seminarUid,
                'hidden' => 0,
            ]
        );

        $this->subject->removeRegistration($registrationUid, $this->pi1);
        $query = $this->getConnectionPool()->getQueryBuilderForTable('tx_seminars_attendances');
        $query->getRestrictions()->removeAll();
        $numberOfRows = $query
            ->count('*')
            ->from('tx_seminars_attendances')
            ->where(
                $query->expr()->eq('user', $query->createNamedParameter($userUid, \PDO::PARAM_INT)),
                $query->expr()->eq('seminar', $query->createNamedParameter($seminarUid, \PDO::PARAM_INT)),
                $query->expr()->eq('hidden', $query->createNamedParameter(1, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchColumn(0);

        self::assertSame(
            1,
            $numberOfRows
        );
    }

    /**
     * @test
     */
    public function removeRegistrationWithFittingQueueRegistrationMovesItFromQueue(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $seminarUid = $this->seminarUid;
        $this->createFrontEndPages();

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $userUid,
                'seminar' => $seminarUid,
            ]
        );
        $queueRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $userUid,
                'seminar' => $seminarUid,
                'seats' => 1,
                'registration_queue' => 1,
            ]
        );

        $this->subject->removeRegistration($registrationUid, $this->pi1);
        $connection = $this->getConnectionForTable('tx_seminars_attendances');

        self::assertGreaterThan(
            0,
            $connection->count(
                '*',
                'tx_seminars_attendances',
                ['registration_queue' => 0, 'uid' => $queueRegistrationUid]
            )
        );
    }

    // Tests concerning canRegisterSeats

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventAndZeroSeatsGivenReturnsFalse(): void
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);

        self::assertFalse(
            $this->subject->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventAndOneSeatGivenReturnsFalse(): void
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);

        self::assertFalse(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndZeroSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(1);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndOneSeatGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(1);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndTwoSeatsGivenReturnsFalse(): void
    {
        $this->seminar->setAttendancesMax(1);

        self::assertFalse(
            $this->subject->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndOneSeatGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(2);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndTwoSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(2);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndThreeSeatsGivenReturnsFalse(): void
    {
        $this->seminar->setAttendancesMax(2);

        self::assertFalse(
            $this->subject->canRegisterSeats($this->seminar, 3)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndZeroSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndOneSeatGivenReturnsTrue(): void
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndTwoSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndFortytwoSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 42)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventWithQueueAndZeroSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventWithQueueAndOneSeatGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForFullyBookedEventWithQueueAndTwoSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndWithQueueAndFortytwoSeatsGivenReturnsTrue(): void
    {
        $this->seminar->setAttendancesMax(2);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 42)
        );
    }

    // Tests concerning notifyAttendee

    /**
     * @test
     */
    public function notifyAttendeeSendsMailToAttendeesMailAddress(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertArrayHasKey('foo@bar.com', $this->getToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForAttendeeWithoutMailAddressNotSendsEmail(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $registration = new LegacyRegistration($registrationUid);

        $this->email->expects(self::never())->method('send');

        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForPlainTextEmail(): void
    {
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_TEXT_MAIL);
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(RegistrationMapper::class);
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::once())->method('modifyAttendeeEmail')->with(
            self::isInstanceOf(MailMessage::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::once())->method('modifyAttendeeEmailBodyPlainText')->with(
            self::isInstanceOf(Template::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyHtml');
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::never())->method('modifyAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForHtmlEmail(): void
    {
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(RegistrationMapper::class);
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::once())->method('modifyAttendeeEmail')->with(
            self::isInstanceOf(MailMessage::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::once())->method('modifyAttendeeEmailBodyPlainText')->with(
            self::isInstanceOf(Template::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::once())->method('modifyAttendeeEmailBodyHtml')->with(
            self::isInstanceOf(Template::class),
            $registration,
            'confirmation'
        );
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::never())->method('modifyAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeMailSubjectContainsConfirmationSubject(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->translate('email_confirmationSubject'),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsEventTitle(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('test event', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyNotContainsSpaceBeforeComma(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(' ,', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationFood(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('something nice to eat', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationAccommodation(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('a nice, dry place', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationInterests(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('learning Ruby on Rails', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeMailSubjectContainsEventTitle(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'test event',
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsTypo3DefaultFromAddressAsSender(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame([$defaultMailFromAddress => $defaultMailFromName], $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsOrganizerAsReplyTo(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(['mail@example.com' => 'test organizer'], $this->getReplyToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeWithoutTypo3DefaultFromAddressSetsOrganizerAsSender(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(['mail@example.com' => 'test organizer'], $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailSetHasHtmlBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('<html', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetDoesNotHaveHtmlBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame('', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetHasNoUnreplacedMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString('###', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailHasNoUnreplacedMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString('###', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForMailSetToUserModeAndUserSetToHtmlMailsHasHtmlBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_USER_MAIL);
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            [
                'module_sys_dmail_html' => true,
                'email' => 'foo@bar.com',
            ]
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('<html', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForMailSetToUserModeAndUserSetToTextMailsNotHasHtmlBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_USER_MAIL);
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            [
                'module_sys_dmail_html' => false,
                'email' => 'foo@bar.com',
            ]
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame('', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsContainsNameOfUserInBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('Harry Callagan', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasLinkToSeminarInBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);
        $seminarLink = 'https://singleview.example.com/';

        self::assertStringContainsString('<a href="' . $seminarLink, $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeAppendsOrganizersFooterToMailBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString("\n-- \norganizer footer", $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForConfirmedEventNotHasPlannedDisclaimer(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Event::STATUS_CONFIRMED
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForCancelledEventNotHasPlannedDisclaimer(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Event::STATUS_CANCELED
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlannedEventDisplaysPlannedDisclaimer(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Event::STATUS_PLANNED
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeWithHiddenDisclaimerFieldAndPlannedEventHidesPlannedDisclaimer(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('hideFieldsInThankYouMail', 'planned_disclaimer');
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            Event::STATUS_PLANNED
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            $this->translate('label_planned_disclaimer'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasCssStylesFromFile(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('style=', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyCanContainAttendeesNames(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1 foo2');
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('foo1 foo2', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailEnumeratesAttendeesNames(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames("foo1\nfoo2");
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString("1. foo1\n2. foo2", $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailReturnsAttendeesNamesInOrderedList(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames("foo1\nfoo2");
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertRegExp(
            '/\\<ol>.*<li>foo1<\\/li>.*<li>foo2<\\/li>.*<\\/ol>/s',
            $this->getHtmlBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceTitleInMailBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'foo_place']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('foo_place', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceAddressInMailBody(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'foo_street']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('foo_street', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithNoPlaceSendsWillBeAnnouncedMessage(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->translate('message_willBeAnnounced'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailSeparatesPlacesTitleAndAddressWithLinefeed(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'place_title', 'address' => 'place_address']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            "place_title\nplace_address",
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailSeparatesPlacesTitleAndAddressWithBreaks(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'place_title', 'address' => 'place_address']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('place_title<br>place_address', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeStripsHtmlTagsFromPlaceAddress(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'place_title', 'address' => 'place<h2>_address</h2>']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString("place_title\nplace_address", $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesLineFeedsWithSpaces(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address1 address2', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnsWithSpaces(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . "\r" . 'address2']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address1 address2', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnAndLineFeedWithOneSpace(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\r\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address1 address2', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleCarriageReturnsWithOneSpace(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . "\r" . "\r" . 'address2']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address1 address2', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsReplacesMultipleLineFeedsWithSpaces(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\n\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address1 address2', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsReplacesMultipleLineFeedsWithSpaces(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\n\naddress2"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address1 address2', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleLineFeedAndCarriageReturnsWithSpaces(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => "address1\naddress2\r\r\naddress3"]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address1 address2 address3', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCityOfPlace(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('footown', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsZipAndCityOfPlace(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['zip' => '12345', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('12345 footown', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCountryOfPlace(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'footown', 'country' => $country->getIsoAlpha2Code()]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString($country->getLocalShortName(), $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSeparatesAddressAndCityWithNewline(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString("address\nfootown", $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsSeparatresAddressAndCityLineWithBreaks(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsString(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString('address<br>footown', $this->getHtmlBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCountryAndCitySeparatesCountryAndCityWithComma(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $mapper = MapperRegistry::get(CountryMapper::class);
        $country = $mapper->find(54);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            [
                'address' => 'address',
                'city' => 'footown',
                'country' => $country->getIsoAlpha2Code(),
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'footown, ' . $country->getLocalShortName(),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCityAndNoCountryNotAddsSurplusCommaAfterCity(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address', 'city' => 'footown']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $uid,
            'place'
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString('footown,', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * Checks that $string does not contain a raw label key.
     *
     * @param string $string
     */
    private function assertNotContainsRawLabelKey(string $string): void
    {
        self::assertStringNotContainsString('_', $string);
        self::assertStringNotContainsString('salutation', $string);
        self::assertStringNotContainsString('formal', $string);
    }

    // Tests concerning the iCalendar attachment

    /**
     * @test
     */
    public function notifyAttendeeHasUtf8CalendarAttachment(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        self::assertSame('text', $attachment->getMediaType());
        self::assertStringContainsString('calendar', $attachment->getMediaSubtype());
        self::assertStringContainsString('charset="utf-8"', $attachment->getMediaSubtype());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithWindowsLineEndings(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString("\r\n", $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithStartAndEndMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        self::assertStringContainsString('BEGIN:VCALENDAR', $attachment->getBody());
        self::assertStringContainsString('END:VCALENDAR', $attachment->getBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithPublishMethod(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        self::assertStringContainsString('method="publish"', $attachment->getMediaSubtype());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEvent(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        self::assertStringContainsString('component="vevent"', $attachment->getMediaSubtype());
    }

    /**
     * @return string[][]
     */
    public function iCalDataProvider(): array
    {
        return [
            'calendar begin' => ['BEGIN:VCALENDAR'],
            'calendar end' => ['END:VCALENDAR'],
            'publish method' => ['METHOD:PUBLISH'],
            'event begin' => ['BEGIN:VEVENT'],
            'event end' => ['END:VEVENT'],
            'version' => ['VERSION:2.0'],
            'product ID' => ['PRODID:TYPO3 CMS'],
        ];
    }

    /**
     * @test
     *
     * @param string $value
     *
     * @dataProvider iCalDataProvider
     */
    public function notifyAttendeeHasCalendarAttachmentWithImportantFields(string $value): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString($value, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventTitleAsSummary(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('SUMMARY:' . $this->seminar->getTitle(), $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventStartDateFromEvent(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getBeginDateAsTimestamp());
        self::assertStringContainsString('DTSTART:' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutEndDateHasCalendarAttachmentWithoutEndDate(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['end_date' => 0]
        );

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringNotContainsString('DTEND:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventEndDateFromEvent(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getEndDateAsTimestampEvenIfOpenEnded());
        self::assertStringContainsString('DTEND:' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventSubtitleAsDescription(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('DESCRIPTION:' . $this->seminar->getSubtitle(), $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutPlaceHasCalendarAttachmentWithoutLocation(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringNotContainsString('LOCATION:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventLocation(): void
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'location title', 'address' => 'some address']
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $this->seminarUid, $siteUid);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['place' => 1]
        );

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('LOCATION:location title, some address', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeReplacesNewlinesInCalendarAttachment(): void
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'location title', 'address' => "some address\r\nmore\neven more\n"]
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $this->seminarUid, $siteUid);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['place' => 1]
        );

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString("LOCATION:location title, some address, more, even more\r\n", $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithOrganizer(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('ORGANIZER;CN="test organizer":mailto:mail@example.com', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithUid(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('UID:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithTimestamp(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByType($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $GLOBALS['SIM_EXEC_TIME']);
        self::assertStringContainsString('DTSTAMP:' . $formattedDate, $content);
    }

    // Tests concerning the salutation

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationContainsInformalSalutation(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->translate('email_hello_informal'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderUnknownContainsFormalUnknownSalutation(): void
    {
        if (OelibFrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is no FrontEndUser.gender field.');
        }

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->translate('email_hello_formal_2'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderMaleContainsFormalMaleSalutation(): void
    {
        if (!OelibFrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com', 'gender' => 0]
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->translate('email_hello_formal_0'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderFemaleContainsFormalFemaleSalutation(): void
    {
        if (!OelibFrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com', 'gender' => 1]
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->translate('email_hello_formal_1'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationContainsFormalConfirmationText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            sprintf(
                $this->translate('email_confirmationHello'),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndConfirmationContainsInformalConfirmationText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            sprintf(
                $this->translate('email_confirmationHello_informal'),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndUnregistrationContainsFormalUnregistrationText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        self::assertStringContainsString(
            sprintf(
                $this->translate(
                    'email_confirmationOnUnregistrationHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndUnregistrationContainsInformalUnregistrationText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        self::assertStringContainsString(
            sprintf(
                $this->translate(
                    'email_confirmationOnUnregistrationHello_informal'
                ),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueConfirmationContainsFormalQueueConfirmationText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        self::assertStringContainsString(
            sprintf(
                $this->translate(
                    'email_confirmationOnRegistrationForQueueHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueConfirmationContainsInformalQueueConfirmationText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        self::assertStringContainsString(
            sprintf(
                $this->translate(
                    'email_confirmationOnRegistrationForQueueHello_informal'
                ),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueUpdateContainsFormalQueueUpdateText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        self::assertStringContainsString(
            sprintf(
                $this->translate(
                    'email_confirmationOnQueueUpdateHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueUpdateContainsInformalQueueUpdateText(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        self::assertStringContainsString(
            sprintf(
                $this->translate(
                    'email_confirmationOnQueueUpdateHello_informal'
                ),
                $this->seminar->getTitle()
            ),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderUnknownNotContainsRawTemplateMarkers(): void
    {
        if (OelibFrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is no FrontEndUser.gender field.');
        }

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderMaleNotContainsRawTemplateMarkers(): void
    {
        if (!OelibFrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com', 'gender' => 0]
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderFemaleNotContainsRawTemplateMarkers(): void
    {
        if (!OelibFrontEndUser::hasGenderField()) {
            self::markTestSkipped('This test is only applicable if there is a FrontEndUser.gender field.');
        }

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com', 'gender' => 1]
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndConfirmationNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndUnregistrationNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndUnregistrationNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueUpdateNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->configuration->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueUpdateNotContainsRawTemplateMarkers(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        $this->assertNotContainsRawLabelKey($this->getTextBodyOfEmail($this->email));
    }

    // Tests concerning the unregistration notice

    /**
     * @test
     */
    public function notifyAttendeeForUnregistrationMailDoesNotAppendUnregistrationNotice(): void
    {
        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::never())->method('getUnregistrationNotice');

        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );
        $pi1 = new DefaultController();
        $pi1->init();

        $subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationMailAndNoUnregistrationPossibleNotAddsUnregistrationNotice(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', false);

        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::never())->method('getUnregistrationNotice');
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationMailAndUnregistrationPossibleAddsUnregistrationNotice(): void
    {
        $this->configuration->setAsBoolean('allowUnregistrationWithEmptyWaitingList', true);

        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::once())->method('getUnregistrationNotice');
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationOnQueueMailAndUnregistrationPossibleAddsUnregistrationNotice(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);

        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::once())->method('getUnregistrationNotice');

        $registration = $this->createRegistration();
        $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'queue_size' => 1,
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances',
            $registration->getUid(),
            ['registration_queue' => 1]
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForQueueUpdateMailAndUnregistrationPossibleAddsUnregistrationNotice(): void
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);

        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::once())->method('getUnregistrationNotice');

        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'deadline_unregistration' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'queue_size' => 1,
            ]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances',
            $registration->getUid(),
            ['registration_queue' => 1]
        );

        $pi1 = new DefaultController();
        $pi1->init();

        $subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationFalseNeverCallsRegistrationEmailHookMethods(): void
    {
        $this->configuration->setAsBoolean('sendConfirmation', false);
        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(RegistrationMapper::class);
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::never())->method('modifyAttendeeEmail');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyPlainText');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyHtml');
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::never())->method('modifyAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $controller = new DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    // Tests regarding the notification of organizers

    /**
     * @test
     */
    public function notifyOrganizersForEventWithEmailsMutedNotSendsEmail(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['mute_notification_emails' => 1]
        );

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesTypo3DefaultFromAddressAsFrom(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyOrganizers($registration);

        self::assertSame([$defaultMailFromAddress => $defaultMailFromName], $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsReplyTo(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->notifyOrganizers($registration);

        self::assertSame(['mail@example.com' => 'test organizer'], $this->getReplyToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyOrganizersWithoutTypo3DefaultFromAddressUsesOrganizerAsFrom(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyOrganizers($registration);

        self::assertSame([$defaultMailFromAddress => $defaultMailFromName], $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsTo(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

        self::assertArrayHasKey('mail@example.com', $this->getToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyOrganizersIncludesHelloIfNotHidden(): void
    {
        $registration = $this->createRegistration();
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->configuration->setAsString('hideFieldsInNotificationMail', '');

        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString('Hello', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithOneVacancyShowsVacanciesLabelWithVacancyNumber(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->configuration->setAsString('showSeminarFieldsInNotificationMail', 'vacancies');
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['needs_registration' => 1, 'attendees_max' => 2]
        );

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

        self::assertRegExp(
            '/' . $this->translate('label_vacancies') . ': 1\\n*$/',
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithUnlimitedVacanciesShowsVacanciesLabelWithUnlimitedLabel(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->configuration->setAsString('showSeminarFieldsInNotificationMail', 'vacancies');
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['needs_registration' => 1, 'attendees_max' => 0]
        );

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString(
            $this->translate('label_vacancies') . ': ' .
            $this->translate('label_unlimited'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsLabelOfCompany(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->configuration->setAsString('showAttendanceFieldsInNotificationMail', 'company');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo inc.',
            ]
        );

        $registration = new LegacyRegistration($registrationUid);
        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString(
            $this->translate('label_company'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsCompanyOfRegistration(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->configuration->setAsString('showAttendanceFieldsInNotificationMail', 'company');

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo inc.',
            ]
        );

        $registration = new LegacyRegistration($registrationUid);
        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString('foo inc.', $this->getTextBodyOfEmail($this->email));
    }

    /**
     * @test
     */
    public function notifyOrganizersForSendNotificationTrueCallsRegistrationEmailHookMethods(): void
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(RegistrationMapper::class);
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::never())->method('modifyAttendeeEmail');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyPlainText');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyHtml');
        $hook->expects(self::once())->method('modifyOrganizerEmail')->with(
            self::isInstanceOf(MailMessage::class),
            $registration,
            'notification'
        );
        $hook->expects(self::never())->method('modifyAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->notifyOrganizers($registrationOld);
    }

    /**
     * @test
     */
    public function notifyOrganizersForSendNotificationFalseNeverCallsRegistrationEmailHookMethods(): void
    {
        $this->configuration->setAsBoolean('sendNotification', false);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(RegistrationMapper::class);
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::never())->method('modifyAttendeeEmail');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyPlainText');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyHtml');
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::never())->method('modifyAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->notifyOrganizers($registrationOld);
    }

    // Tests concerning sendAdditionalNotification

    /**
     * @test
     */
    public function sendAdditionalNotificationCanSendEmailToOneOrganizer(): void
    {
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertArrayHasKey('mail@example.com', $this->getToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEmailMutedNotSendsEmail(): void
    {
        $registration = $this->createRegistration();
        $event = $registration->getSeminarObject();
        $event->muteNotificationEmails();

        $this->email->expects(self::never())->method('send');

        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationCanSendEmailsToTwoOrganizers(): void
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'test organizer 2',
                'email' => 'mail2@example.com',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->seminarUid,
            $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['organizers' => 2]
        );

        $this->email->expects(self::once())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertArrayHasKey('mail@example.com', $this->getToOfEmail($this->email));
        self::assertArrayHasKey('mail2@example.com', $this->getToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationUsesTypo3DefaultFromAddressAsSenderIfEmailIsSentToTwoOrganizers(): void
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'test organizer 2',
                'email' => 'mail2@example.com',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->seminarUid,
            $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['organizers' => 2]
        );

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->sendAdditionalNotification($registration);

        self::assertArrayHasKey($defaultMailFromAddress, $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationUsesTheFirstOrganizerAsReplyToIfEmailIsSentToTwoOrganizers(): void
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'test organizer 2',
                'email' => 'mail2@example.com',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->seminarUid,
            $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['organizers' => 2]
        );

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->sendAdditionalNotification($registration);

        self::assertArrayHasKey('mail@example.com', $this->getReplyToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationUsesTheFirstOrganizerAsSenderIfEmailIsSentToTwoOrganizers(): void
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'test organizer 2',
                'email' => 'mail2@example.com',
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->seminarUid,
            $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['organizers' => 2]
        );

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->subject->sendAdditionalNotification($registration);

        self::assertArrayHasKey('mail@example.com', $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesSendsEnoughAttendancesMail(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        unset($this->subject);
        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertStringContainsString(
            sprintf(
                $this->translate('email_additionalNotificationEnoughRegistrationsSubject'),
                $this->seminarUid,
                ''
            ),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesNotSendsEmail(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 2, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesNotSetsNotificationFlag(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 2, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new TestingLegacyEvent($this->seminarUid);

        self::assertFalse($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithMoreThanEnoughAttendancesSendsEnoughAttendancesMail(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        $firstEmail = $this->email;
        self::assertNotNull($firstEmail);
        self::assertStringContainsString(
            sprintf(
                $this->translate('email_additionalNotificationEnoughRegistrationsSubject'),
                $this->seminarUid,
                ''
            ),
            $firstEmail->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesSetsNotifiedFlag(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new TestingLegacyEvent($this->seminarUid);

        self::assertTrue($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithMoreThanEnoughAttendancesSetsNotifiedFlag(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new TestingLegacyEvent($this->seminarUid);

        self::assertTrue($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndOrganizersAlreadyNotifiedNotSendsEmail(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42, 'organizers_notified_about_minimum_reached' => 1]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForMoreThanEnoughAttendancesAndOrganizersAlreadyNotifiedNotSends(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42, 'organizers_notified_about_minimum_reached' => 1]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $this->email->expects(self::never())->method('send');

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithZeroAttendeesMinDoesNotSendAnyMail(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 0, 'attendees_max' => 42]
        );

        unset($this->subject);
        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutSubject(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertStringContainsString(
            sprintf(
                $this->translate('email_additionalNotificationIsFullSubject'),
                $this->seminarUid,
                ''
            ),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutMessage(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertStringContainsString(
            $this->translate('email_additionalNotificationIsFull'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesAndNotBookedOutSendsNoEmail(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 5, 'attendees_max' => 5]
        );

        $subject = new TestingRegistrationManager();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndUnlimitedVacanciesSendsEmail(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'attendees_min' => 1,
                'attendees_max' => 0,
                'needs_registration' => 1,
            ]
        );

        $this->email->expects(self::once())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndOneVacancyHasVacancyNumber(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'attendees_min' => 1,
                'attendees_max' => 2,
                'needs_registration' => 1,
            ]
        );
        $this->configuration->setAsString('showSeminarFieldsInNotificationMail', 'vacancies');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertRegExp(
            '/' . $this->translate('label_vacancies') . ': 1\\n*$/',
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEnoughAttendancesAndUnlimitedVacanciesHasUnlimitedLabel(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'attendees_min' => 1,
                'attendees_max' => 0,
                'needs_registration' => 1,
            ]
        );
        $this->configuration->setAsString('showSeminarFieldsInNotificationMail', 'vacancies');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertStringContainsString(
            $this->translate('label_vacancies') . ': '
            . $this->translate('label_unlimited'),
            $this->getTextBodyOfEmail($this->email)
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationCallsRegistrationEmailHookMethods(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(RegistrationMapper::class);
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::never())->method('modifyAttendeeEmail');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyPlainText');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyHtml');
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::once())->method('modifyAdditionalEmail')->with(
            self::isInstanceOf(MailMessage::class),
            $registration,
            'IsFull'
        );

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->sendAdditionalNotification($registrationOld);
    }

    // Tests concerning allowsRegistrationByDate

    /**
     * @test
     */
    public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateAllowedReturnsTrue(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', true);

        $this->seminar->setBeginDate(0);

        self::assertTrue(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateNotAllowedIsFalse(): void
    {
        $this->configuration->setAsBoolean('allowRegistrationForEventsWithoutDate', false);

        $this->seminar->setBeginDate(0);

        self::assertFalse(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineOverReturnsFalse(): void
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] - 42);

        self::assertFalse(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineInFutureReturnsTrue(): void
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationDeadline($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertTrue(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForRegistrationBeginInFutureReturnsFalse(): void
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] + 10);

        self::assertFalse(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForRegistrationBeginInPastReturnsTrue(): void
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);

        self::assertTrue(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForNoRegistrationBeginReturnsTrue(): void
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 42);
        $this->seminar->setRegistrationBeginDate(0);

        self::assertTrue(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForBeginDateInPastAndRegistrationBeginInPastReturnsFalse(): void
    {
        $this->seminar->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 42);
        $this->seminar->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME'] - 50);

        self::assertFalse(
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    // Tests concerning allowsRegistrationBySeats

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithNoVacanciesAndNoQueueReturnsFalse(): void
    {
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setRegistrationQueue(false);

        self::assertFalse(
            $this->subject->allowsRegistrationBySeats($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithUnlimitedVacanciesReturnsTrue(): void
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->allowsRegistrationBySeats($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithRegistrationQueueReturnsTrue(): void
    {
        $this->seminar->setNumberOfAttendances(1);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setRegistrationQueue(true);

        self::assertTrue(
            $this->subject->allowsRegistrationBySeats($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationBySeatsForEventWithVacanciesReturnsTrue(): void
    {
        $this->seminar->setNumberOfAttendances(0);
        $this->seminar->setAttendancesMax(1);
        $this->seminar->setRegistrationQueue(false);

        self::assertTrue(
            $this->subject->allowsRegistrationBySeats($this->seminar)
        );
    }

    // Tests concerning registrationHasStarted

    /**
     * @test
     */
    public function registrationHasStartedForEventWithoutRegistrationBeginReturnsTrue(): void
    {
        $this->seminar->setRegistrationBeginDate(0);

        self::assertTrue(
            $this->subject->registrationHasStarted($this->seminar)
        );
    }

    /**
     * @test
     */
    public function registrationHasStartedForEventWithRegistrationBeginInPastReturnsTrue(): void
    {
        $this->seminar->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] - 42
        );

        self::assertTrue(
            $this->subject->registrationHasStarted($this->seminar)
        );
    }

    /**
     * @test
     */
    public function registrationHasStartedForEventWithRegistrationBeginInFutureReturnsFalse(): void
    {
        $this->seminar->setRegistrationBeginDate(
            $GLOBALS['SIM_EXEC_TIME'] + 42
        );

        self::assertFalse(
            $this->subject->registrationHasStarted($this->seminar)
        );
    }

    // Tests concerning createRegistration

    /**
     * @TODO: This is just a transitional test that needs to be removed once
     * createRegistration uses the data mapper to save the registration.
     *
     * @test
     */
    public function createRegistrationSavesRegistration(): void
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new DefaultController();
        $plugin->cObj = $this->getFrontEndController()->cObj;
        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingRegistrationManager::class,
            [
                'notifyAttendee',
                'notifyOrganizers',
                'sendAdditionalNotification',
                'setRegistrationData',
            ]
        );

        $subject->createRegistration($this->seminar, [], $plugin);

        self::assertInstanceOf(
            LegacyRegistration::class,
            $subject->getRegistration()
        );
        $uid = $subject->getRegistration()->getUid();
        $connection = $this->getConnectionForTable('tx_seminars_attendances');
        // We're not using the testing framework here because the record
        // is not marked as dummy record.
        $result = $connection->count('*', 'tx_seminars_attendances', ['uid' => $uid]);
        self::assertSame(1, $result);

        $connection->delete('tx_seminars_attendances', ['uid' => $uid]);
    }

    /**
     * @test
     */
    public function createRegistrationIncreasesRegistrationCountInEventFromZeroToOne(): void
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new DefaultController();
        $plugin->cObj = $this->getFrontEndController()->cObj;
        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingRegistrationManager::class,
            [
                'notifyAttendee',
                'notifyOrganizers',
                'sendAdditionalNotification',
                'setRegistrationData',
            ]
        );

        $subject->createRegistration($this->seminar, [], $plugin);
        $seminarsConnection = $this->getConnectionForTable('tx_seminars_seminars');
        $seminarData = $seminarsConnection->select(['*'], 'tx_seminars_seminars', ['uid' => $this->seminarUid])
            ->fetch();

        $registrationUid = $subject->getRegistration()->getUid();

        $attendancesConnection = $this->getConnectionForTable('tx_seminars_attendances');
        $attendancesConnection->delete('tx_seminars_attendances', ['uid' => $registrationUid]);

        self::assertSame(1, (int)$seminarData['registrations']);
    }

    /**
     * @test
     */
    public function createRegistrationReturnsRegistration(): void
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new DefaultController();
        $plugin->cObj = $this->getFrontEndController()->cObj;
        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingRegistrationManager::class,
            [
                'notifyAttendee',
                'notifyOrganizers',
                'sendAdditionalNotification',
                'setRegistrationData',
            ]
        );

        $registration = $subject->createRegistration($this->seminar, [], $plugin);

        $uid = $subject->getRegistration()->getUid();
        // @TODO: This line needs to be removed once createRegistration uses
        // the data mapper to save the registration.
        $connection = $this->getConnectionForTable('tx_seminars_attendances');
        $connection->delete('tx_seminars_attendances', ['uid' => $uid]);

        self::assertInstanceOf(
            Registration::class,
            $registration
        );
    }

    /**
     * @TODO: This is just a transitional test that can be removed once
     * createRegistration does not use the old registration model anymore.
     *
     * @test
     */
    public function createRegistrationCreatesOldAndNewRegistrationModelForTheSameUid(): void
    {
        // Drops the non-saving mapper so that the registration mapper (once we use it) actually saves the registration.
        MapperRegistry::purgeInstance();
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);
        $this->testingFramework->markTableAsDirty('tx_seminars_seminars');

        $this->createAndLogInFrontEndUser();

        $plugin = new DefaultController();
        $plugin->cObj = $this->getFrontEndController()->cObj;
        /** @var TestingRegistrationManager&MockObject $subject */
        $subject = $this->createPartialMock(
            TestingRegistrationManager::class,
            [
                'notifyAttendee',
                'notifyOrganizers',
                'sendAdditionalNotification',
                'setRegistrationData',
            ]
        );

        $registration = $subject->createRegistration(
            $this->seminar,
            [],
            $plugin
        );

        $uid = $subject->getRegistration()->getUid();
        // @TODO: This line needs to be removed once createRegistration uses
        // the data mapper to save the registration.
        $connection = $this->getConnectionForTable('tx_seminars_attendances');
        $connection->delete('tx_seminars_attendances', ['uid' => $uid]);

        self::assertSame(
            $registration->getUid(),
            $subject->getRegistration()->getUid()
        );
    }

    // Tests concerning setRegistrationData()

    /**
     * @test
     */
    public function setRegistrationDataForPositiveSeatsSetsSeats(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['seats' => '3']
        );

        self::assertSame(
            3,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingSeatsSetsOneSeat(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            []
        );

        self::assertSame(
            1,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForZeroSeatsSetsOneSeat(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['seats' => '0']
        );

        self::assertSame(
            1,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNegativeSeatsSetsOneSeat(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['seats' => '-1']
        );

        self::assertSame(
            1,
            $registration->getSeats()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForRegisteredThemselvesOneSetsItToTrue(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['registered_themselves' => '1']
        );

        self::assertTrue(
            $registration->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForRegisteredThemselvesZeroSetsItToFalse(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['registered_themselves' => '0']
        );

        self::assertFalse(
            $registration->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForRegisteredThemselvesMissingSetsItToFalse(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            []
        );

        self::assertFalse(
            $registration->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePricePutsSelectedPriceCodeToPrice(): void
    {
        $subject = new TestingRegistrationManager();

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12, 'special' => 3]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['price' => 'special']
        );

        self::assertSame(
            'special',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedNotAvailablePricePutsFirstPriceCodeToPrice(): void
    {
        $subject = new TestingRegistrationManager();

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['price' => 'early_bird_regular']
        );

        self::assertSame(
            'regular',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNoSelectedPricePutsFirstPriceCodeToPrice(): void
    {
        $subject = new TestingRegistrationManager();

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            []
        );

        self::assertSame(
            'regular',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNoSelectedAndOnlyFreeRegularPriceAvailablePutsRegularPriceCodeToPrice(): void
    {
        $subject = new TestingRegistrationManager();

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 0]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            []
        );

        self::assertSame(
            'regular',
            $registration->getPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForOneSeatsCalculatesTotalPriceFromSelectedPriceAndSeats(): void
    {
        $subject = new TestingRegistrationManager();

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['price' => 'regular', 'seats' => '1']
        );

        self::assertSame(
            12.0,
            $registration->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForTwoSeatsCalculatesTotalPriceFromSelectedPriceAndSeats(): void
    {
        $subject = new TestingRegistrationManager();

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['price' => 'regular', 'seats' => '2']
        );

        self::assertSame(
            24.0,
            $registration->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAttendeesNamesSetsAttendeesNames(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['attendees_names' => "John Doe\nJane Doe"]
        );

        self::assertSame(
            "John Doe\nJane Doe",
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAttendeesNames(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['attendees_names' => 'John <em>Doe</em>']
        );

        self::assertSame(
            'John Doe',
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAttendeesNamesSetsEmptyAttendeesNames(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['attendees_names' => '']
        );

        self::assertSame(
            '',
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAttendeesNamesSetsEmptyAttendeesNames(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            []
        );

        self::assertSame(
            '',
            $registration->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForPositiveKidsSetsNumberOfKids(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['kids' => '3']
        );

        self::assertSame(
            3,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingKidsSetsZeroKids(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            []
        );

        self::assertSame(
            0,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForZeroKidsSetsZeroKids(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['kids' => '0']
        );

        self::assertSame(
            0,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNegativeKidsSetsZeroKids(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['kids' => '-1']
        );

        self::assertSame(
            0,
            $registration->getKids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneSetsIt(): void
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['method_of_payment' => $paymentMethod->getUid()]);

        self::assertSame(
            $paymentMethod,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromTwoSetsIt(): void
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod1 = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethod2 = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['method_of_payment' => $paymentMethod2->getUid()]);

        self::assertSame(
            $paymentMethod2,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneForFreeEventsSetsNoPaymentMethod(): void
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 0]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['method_of_payment' => $paymentMethod->getUid()]);

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndNoneAvailableSetsNone(): void
    {
        $subject = new TestingRegistrationManager();

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 0]);
        $event->method('getPaymentMethods')
            ->willReturn(new Collection());
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            []
        );

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndTwoAvailableSetsNone(): void
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod1 = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethod2 = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndOneAvailableSetsIt(): void
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            $paymentMethod,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForUnavailablePaymentMethodAndTwoAvailableSetsNone(): void
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod1 = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethod2 = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['method_of_payment' => max($paymentMethod1->getUid(), $paymentMethod2->getUid()) + 1]
        );

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForUnavailablePaymentMethodAndOneAvailableSetsAvailable(): void
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(PaymentMethodMapper::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var Event&MockObject $event */
        $event = $this->createPartialMock(Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['method_of_payment' => $paymentMethod->getUid() + 1]);

        self::assertSame(
            $paymentMethod,
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAccountNumberSetsAccountNumber(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['account_number' => '123 455 ABC']);

        self::assertSame(
            '123 455 ABC',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAccountNumber(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['account_number' => '123 <em>455</em> ABC']);

        self::assertSame(
            '123 455 ABC',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInAccountNumber(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['account_number' => "123\r\n455\t ABC"]
        );

        self::assertSame(
            '123 455 ABC',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAccountNumberSetsEmptyAccountNumber(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['account_number' => '']);

        self::assertSame(
            '',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAccountNumberSetsEmptyAccountNumber(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyBankCodeSetsBankCode(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['bank_code' => '123 455 ABC']);

        self::assertSame(
            '123 455 ABC',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromBankCode(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['bank_code' => '123 <em>455</em> ABC']);

        self::assertSame(
            '123 455 ABC',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInBankCode(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['bank_code' => "123\r\n455\t ABC"]
        );

        self::assertSame(
            '123 455 ABC',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyBankCodeSetsEmptyBankCode(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['bank_code' => '']);

        self::assertSame(
            '',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingBankCodeSetsEmptyBankCode(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyBankNameSetsBankName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['bank_name' => 'Swiss Tax Protection']);

        self::assertSame(
            'Swiss Tax Protection',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromBankName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['bank_name' => 'Swiss <em>Tax</em> Protection']);

        self::assertSame(
            'Swiss Tax Protection',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInBankName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['bank_name' => "Swiss\r\nTax\t Protection"]
        );

        self::assertSame(
            'Swiss Tax Protection',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyBankNameSetsEmptyBankName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['bank_name' => '']);

        self::assertSame(
            '',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingBankNameSetsEmptyBankName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getBankName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAccountOwnerSetsAccountOwner(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['account_owner' => 'John Doe']);

        self::assertSame(
            'John Doe',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAccountOwner(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['account_owner' => 'John <em>Doe</em>']);

        self::assertSame(
            'John Doe',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInAccountOwner(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['account_owner' => "John\r\n\t Doe"]
        );

        self::assertSame(
            'John Doe',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAccountOwnerSetsEmptyAccountOwner(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['account_owner' => '']);

        self::assertSame(
            '',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAccountOwnerSetsEmptyAccountOwner(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyCompanySetsCompany(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['company' => "Business Ltd.\nTom, Dick & Harry"]
        );

        self::assertSame(
            "Business Ltd.\nTom, Dick & Harry",
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromCompany(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['company' => 'Business <em>Ltd.</em>']);

        self::assertSame(
            'Business Ltd.',
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyCompanySetsEmptyCompany(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['company' => '']);

        self::assertSame(
            '',
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingCompanySetsEmptyCompany(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getCompany()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMaleGenderSetsGender(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['gender' => (string)OelibFrontEndUser::GENDER_MALE]
        );

        self::assertSame(
            OelibFrontEndUser::GENDER_MALE,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForFemaleGenderSetsGender(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['gender' => (string)OelibFrontEndUser::GENDER_FEMALE]
        );

        self::assertSame(
            OelibFrontEndUser::GENDER_FEMALE,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForInvalidIntegerGenderSetsUnknownGender(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['gender' => '42']);

        self::assertSame(
            OelibFrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForInvalidStringGenderSetsUnknownGender(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['gender' => 'Mr. Fantastic']);

        self::assertSame(
            OelibFrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyGenderSetsUnknownGender(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['gender' => '']);

        self::assertSame(
            OelibFrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingGenderSetsUnknownGender(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            OelibFrontEndUser::GENDER_UNKNOWN,
            $registration->getGender()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyNameSetsName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['name' => 'John Doe']);

        self::assertSame(
            'John Doe',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['name' => 'John <em>Doe</em>']);

        self::assertSame(
            'John Doe',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['name' => "John\r\n\t Doe"]);

        self::assertSame(
            'John Doe',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyNameSetsEmptyName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['name' => '']);

        self::assertSame(
            '',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingNameSetsEmptyName(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getName()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyAddressSetsAddress(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['address' => "Back Road 42\n(second door)"]);

        self::assertSame(
            "Back Road 42\n(second door)",
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromAddress(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['address' => 'Back <em>Road</em> 42']);

        self::assertSame(
            'Back Road 42',
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyAddressSetsEmptyAddress(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['address' => '']);

        self::assertSame(
            '',
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingAddressSetsEmptyAddress(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getAddress()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyZipSetsZip(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['zip' => '12345 ABC']);

        self::assertSame(
            '12345 ABC',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromZip(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['zip' => '12345 <em>ABC</em>']);

        self::assertSame(
            '12345 ABC',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInZip(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['zip' => "12345\r\n\t ABC"]);

        self::assertSame(
            '12345 ABC',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyZipSetsEmptyZip(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['zip' => '']);

        self::assertSame(
            '',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingZipSetsEmptyZip(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getZip()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyCitySetsCity(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['city' => 'Elmshorn']);

        self::assertSame(
            'Elmshorn',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromCity(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['city' => 'Santiago de <em>Chile</em>']);

        self::assertSame(
            'Santiago de Chile',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInCity(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['city' => "Santiago\r\n\t de Chile"]);

        self::assertSame(
            'Santiago de Chile',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyCitySetsEmptyCity(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['city' => '']);

        self::assertSame(
            '',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingCitySetsEmptyCity(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getCity()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNonEmptyCountrySetsCountry(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['country' => 'Brazil']);

        self::assertSame(
            'Brazil',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataDropsHtmlTagsFromCountry(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['country' => 'South <em>Africa</em>']);

        self::assertSame(
            'South Africa',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataChangesWhitespaceToSpaceInCountry(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['country' => "South\r\n\t Africa"]);

        self::assertSame(
            'South Africa',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForEmptyCountrySetsEmptyCountry(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['country' => '']);

        self::assertSame(
            '',
            $registration->getCountry()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingCountrySetsEmptyCountry(): void
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $registration = new Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertSame(
            '',
            $registration->getCountry()
        );
    }

    // Tests concerning existsSeminar and existsSeminarMessage

    /**
     * @test
     */
    public function existsSeminarForZeroUidReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->existsSeminar(0)
        );
    }

    /**
     * @test
     */
    public function existsSeminarForInexistentUidReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->existsSeminar($this->testingFramework->getAutoIncrement('tx_seminars_seminars'))
        );
    }

    /**
     * @test
     */
    public function existsSeminarForExistingDeleteUidReturnsFalse(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['deleted' => 1]
        );

        self::assertFalse(
            $this->subject->existsSeminar($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarForExistingHiddenUidReturnsFalse(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['hidden' => 1]
        );

        self::assertFalse(
            $this->subject->existsSeminar($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarForExistingUidReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->existsSeminar($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForZeroUidReturnsErrorMessage(): void
    {
        self::assertStringContainsString(
            $this->translate('message_missingSeminarNumber'),
            $this->subject->existsSeminarMessage(0)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForZeroUidSendsNotFoundHeader(): void
    {
        $this->subject->existsSeminarMessage(0);

        self::assertSame(
            'Status: 404 Not Found',
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForInexistentUidReturnsErrorMessage(): void
    {
        self::assertStringContainsString(
            $this->translate('message_wrongSeminarNumber'),
            $this->subject->existsSeminarMessage($this->testingFramework->getAutoIncrement('tx_seminars_seminars'))
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForInexistentUidSendsNotFoundHeader(): void
    {
        $this->subject->existsSeminarMessage($this->testingFramework->getAutoIncrement('tx_seminars_seminars'));

        self::assertSame(
            'Status: 404 Not Found',
            $this->headerCollector->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingDeleteUidReturnsErrorMessage(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['deleted' => 1]
        );

        self::assertStringContainsString(
            $this->translate('message_wrongSeminarNumber'),
            $this->subject->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingHiddenUidReturnsErrorMessage(): void
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['hidden' => 1]
        );

        self::assertStringContainsString(
            $this->translate('message_wrongSeminarNumber'),
            $this->subject->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingUidReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingUidNotSendsHttpHeader(): void
    {
        $this->subject->existsSeminarMessage($this->seminarUid);

        self::assertSame(
            [],
            $this->headerCollector->getAllAddedHeaders()
        );
    }

    // Tests concerning getPricesAvailableForUser

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndNoRegistrationsReturnsAllAvailablePrices(): void
    {
        $this->configuration->setAsBoolean('automaticSpecialPriceForSubsequentRegistrationsBySameUser', false);

        $userUid = $this->testingFramework->createFrontEndUser();
        $user = $this->frontEndUserMapper->find($userUid);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => '100.00',
                'price_regular_early' => '90.00',
                'price_regular_board' => '150.00',
                'price_special' => '50.00',
                'price_special_early' => '45.00',
                'price_special_board' => '75.00',
            ]
        );
        $event = new LegacyEvent($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['regular', 'regular_board', 'special', 'special_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndOneRegistrationReturnsAllAvailablePrices(): void
    {
        $this->configuration->setAsBoolean('automaticSpecialPriceForSubsequentRegistrationsBySameUser', false);

        $userUid = $this->testingFramework->createFrontEndUser();
        $user = $this->frontEndUserMapper->find($userUid);

        $this->testingFramework->createRecord('tx_seminars_attendances', ['user' => $userUid]);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => '100.00',
                'price_regular_early' => '90.00',
                'price_regular_board' => '150.00',
                'price_special' => '50.00',
                'price_special_early' => '45.00',
                'price_special_board' => '75.00',
            ]
        );
        $event = new LegacyEvent($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['regular', 'regular_board', 'special', 'special_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForAutomaticPricesAndNoRegistrationsRemovesSpecialPrices(): void
    {
        $this->configuration->setAsBoolean('automaticSpecialPriceForSubsequentRegistrationsBySameUser', true);

        $userUid = $this->testingFramework->createFrontEndUser();
        $user = $this->frontEndUserMapper->find($userUid);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => '100.00',
                'price_regular_early' => '90.00',
                'price_regular_board' => '150.00',
                'price_special' => '50.00',
                'price_special_early' => '45.00',
                'price_special_board' => '75.00',
            ]
        );
        $event = new LegacyEvent($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['regular', 'regular_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndOneRegistrationRemovesRegularPrices(): void
    {
        $this->configuration->setAsBoolean('automaticSpecialPriceForSubsequentRegistrationsBySameUser', true);

        $userUid = $this->testingFramework->createFrontEndUser();
        $user = $this->frontEndUserMapper->find($userUid);

        $this->testingFramework->createRecord('tx_seminars_attendances', ['user' => $userUid]);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => '100.00',
                'price_regular_early' => '90.00',
                'price_regular_board' => '150.00',
                'price_special' => '50.00',
                'price_special_early' => '45.00',
                'price_special_board' => '75.00',
            ]
        );
        $event = new LegacyEvent($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['special', 'special_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndOneRegistrationAndNoSpecialPriceKeepsRegularPrice(): void
    {
        $this->configuration->setAsBoolean('automaticSpecialPriceForSubsequentRegistrationsBySameUser', true);

        $userUid = $this->testingFramework->createFrontEndUser();
        $user = $this->frontEndUserMapper->find($userUid);

        $this->testingFramework->createRecord('tx_seminars_attendances', ['user' => $userUid]);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => '100.00',
                'price_regular_early' => '90.00',
                'price_regular_board' => '150.00',
            ]
        );
        $event = new LegacyEvent($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['regular', 'regular_board'], array_keys($prices));
    }

    private function getConnectionPool(): ConnectionPool
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $connectionPool;
    }

    private function getConnectionForTable(string $table): Connection
    {
        return $this->getConnectionPool()->getConnectionForTable($table);
    }
}
