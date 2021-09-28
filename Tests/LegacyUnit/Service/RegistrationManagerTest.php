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
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures\TestingRegistrationManager;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \Tx_Seminars_Service_RegistrationManager
 */
final class RegistrationManagerTest extends TestCase
{
    use LanguageHelper;

    use EmailTrait;

    use MakeInstanceTrait;

    /**
     * @var TestingRegistrationManager
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int
     */
    private $seminarUid = 0;

    /**
     * @var TestingEvent
     */
    private $seminar = null;

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
     * @var \Tx_Seminars_FrontEnd_DefaultController a front-end plugin
     */
    private $pi1 = null;

    /**
     * @var TestingEvent
     */
    private $fullyBookedSeminar = null;

    /**
     * @var TestingEvent
     */
    private $cachedSeminar = null;

    /**
     * backed-up extension configuration of the TYPO3 configuration variables
     *
     * @var array
     */
    private $extConfBackup = [];

    /**
     * @var HeaderCollector
     */
    private $headerCollector = null;

    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $frontEndUserMapper = null;

    /**
     * @var array<int, class-string>
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

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];

        $this->testingFramework = new TestingFramework('tx_seminars');
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);

        $this->email = $this->createEmailMock();
        $this->secondEmail = $this->createEmailMock();
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->secondEmail);

        \Tx_Seminars_OldModel_Registration::purgeCachedSeminars();
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

        $this->seminar = new TestingEvent($this->seminarUid);
        $this->subject = TestingRegistrationManager::getInstance();

        /** @var \Tx_Seminars_Service_SingleViewLinkBuilder&MockObject $linkBuilder */
        $linkBuilder = $this->createPartialMock(
            \Tx_Seminars_Service_SingleViewLinkBuilder::class,
            ['createAbsoluteUrlForEvent']
        );
        $linkBuilder->method('createAbsoluteUrlForEvent')->willReturn('http://singleview.example.com/');
        $this->subject->injectLinkBuilder($linkBuilder);

        $frontEndUserMapper = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class);
        $this->frontEndUserMapper = $frontEndUserMapper;
    }

    protected function tearDown()
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
     *
     * @return void
     */
    private function createFrontEndPages()
    {
        $this->loginPageUid = $this->testingFramework->createFrontEndPage();
        $this->registrationPageUid
            = $this->testingFramework->createFrontEndPage();

        $this->pi1 = new \Tx_Seminars_FrontEnd_DefaultController();

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
        $this->fullyBookedSeminar = new TestingEvent(
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
     * @return \Tx_Seminars_OldModel_Registration the created registration
     */
    private function createRegistration(): \Tx_Seminars_OldModel_Registration
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

        return new \Tx_Seminars_OldModel_Registration($registrationUid);
    }

    /**
     * Extracts the HTML body from the first sent e-mail.
     *
     * @return string
     */
    private function getEmailHtmlPart(): string
    {
        $htmlMimeParts = $this->filterEmailAttachmentsByTitle($this->email, 'text/html');

        return $htmlMimeParts[0]->getBody();
    }

    /**
     * Returns the attachments of $email that have a content type that contains $contentType.
     *
     * Example: a $contentType of "text/calendar" will also find attachments that have 'text/calendar; charset="utf-8"'
     * as the content type.
     *
     * @param MailMessage $email
     * @param string $contentType
     *
     * @return \Swift_Mime_MimeEntity[]
     */
    private function filterEmailAttachmentsByTitle(MailMessage $email, string $contentType): array
    {
        $matches = [];

        /** @var \Swift_Mime_MimeEntity $attachment */
        foreach ($email->getChildren() as $attachment) {
            if (strpos($attachment->getContentType(), $contentType) !== false) {
                $matches[] = $attachment;
            }
        }

        return $matches;
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
     *
     * @return void
     */
    private function purgeMockedInstances()
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
    public function createFrontEndPagesCreatesNonZeroLoginPageUid()
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
    public function createFrontEndPagesCreatesNonZeroRegistrationPageUid()
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
    public function createFrontEndPagesCreatesPi1()
    {
        $this->createFrontEndPages();

        self::assertNotNull(
            $this->pi1
        );
        self::assertInstanceOf(
            \Tx_Seminars_FrontEnd_DefaultController::class,
            $this->pi1
        );
    }

    /**
     * @test
     */
    public function createAndLogInFrontEndUserCreatesNonZeroUserUid()
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
    public function createAndLogInFrontEndUserLogsInFrontEndUser()
    {
        $this->createAndLogInFrontEndUser();
        self::assertTrue(
            $this->testingFramework->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createBookedOutSeminarSetsSeminarInstance()
    {
        $this->createBookedOutSeminar();

        self::assertInstanceOf(
            \Tx_Seminars_OldModel_Event::class,
            $this->fullyBookedSeminar
        );
    }

    /**
     * @test
     */
    public function createdBookedOutSeminarHasUidGreaterZero()
    {
        $this->createBookedOutSeminar();

        self::assertTrue(
            $this->fullyBookedSeminar->getUid() > 0
        );
    }

    /**
     * @test
     */
    public function mockedInstancesListInitiallyHasTwoInstances()
    {
        self::assertCount(2, $this->mockedClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsClassnameToList()
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
    public function addMockedInstanceAddsInstanceToTypo3InstanceBuffer()
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
    public function purgeMockedInstancesRemovesClassnameFromList()
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
    public function purgeMockedInstancesRemovesInstanceFromTypo3InstanceBuffer()
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
    public function getInstanceReturnsRegistrationManagerInstance()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Service_RegistrationManager::class,
            TestingRegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceReturnsTestingRegistrationManagerInstance()
    {
        self::assertInstanceOf(
            TestingRegistrationManager::class,
            TestingRegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            TestingRegistrationManager::getInstance(),
            TestingRegistrationManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
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
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkTag()
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
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage()
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
    public function getLinkToRegistrationOrLoginPageWithLoggedOutUserContainsRedirectWithEventUid()
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
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkTag()
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
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid()
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
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=4504
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInAndSeparateDetailsPageCreatesLinkToRegistrationPage()
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
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserDoesNotContainRedirect()
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
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndSeminarWithoutDateHasLinkWithPrebookingLabel()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate(0);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlinePrebooking'),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInSeminarWithoutDateAndNoVacanciesHasRegistrationLabel()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate(0);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_onlineRegistration'),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedInUserAndFullyBookedSeminarWithQueueHasQueueRegistration()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();
        $this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertStringContainsString(
            \sprintf($this->getLanguageService()->getLL('label_onlineRegistrationOnQueue'), 0),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getLinkToRegistrationOrLoginPageWithLoggedOutAndFullyBookedSeminarWithQueueHasQueueRegistration()
    {
        $this->createFrontEndPages();
        $this->seminar->setBeginDate($GLOBALS['EXEC_SIM_TIME'] + 45);
        $this->seminar->setNumberOfAttendances(5);
        $this->seminar->setAttendancesMax(5);
        $this->seminar->setRegistrationQueue(true);

        self::assertStringContainsString(
            \sprintf($this->getLanguageService()->getLL('label_onlineRegistrationOnQueue'), 0),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    // Tests concerning getRegistrationLink

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedInUserAndSeminarWithVacanciesReturnsLinkToRegistrationPage()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertStringContainsString(
            '?id=' . $this->registrationPageUid,
            $this->subject->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedInUserAndSeminarWithVacanciesReturnsLinkWithSeminarUid()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertStringContainsString(
            '%5Bseminar%5D=' . $this->seminarUid,
            $this->subject->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedOutUserAndSeminarWithVacanciesReturnsLoginLink()
    {
        $this->createFrontEndPages();

        self::assertStringContainsString(
            '?id=' . $this->loginPageUid,
            $this->subject->getRegistrationLink($this->pi1, $this->seminar)
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
            $this->subject->getRegistrationLink($this->pi1, $this->fullyBookedSeminar)
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
            $this->subject->getRegistrationLink($this->pi1, $this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForBeginDateBeforeCurrentDateReturnsEmptyString()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'title' => 'test event',
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
                    'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                    'attendees_max' => 10,
                ]
            )
        );

        self::assertSame(
            '',
            $this->subject->getRegistrationLink($this->pi1, $this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForAlreadyEndedRegistrationDeadlineReturnsEmptyString()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'title' => 'test event',
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                    'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                    'attendees_max' => 10,
                    'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
                ]
            )
        );

        self::assertSame(
            '',
            $this->subject->getRegistrationLink($this->pi1, $this->cachedSeminar)
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

        self::assertStringContainsString(
            '%5Bseminar%5D=' . $this->seminarUid,
            $this->subject->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsLoginLink()
    {
        $this->createFrontEndPages();
        $this->seminar->setUnlimitedVacancies();

        self::assertStringContainsString(
            '?id=' . $this->loginPageUid,
            $this->subject->getRegistrationLink($this->pi1, $this->seminar)
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

        self::assertStringContainsString(
            '%5Bseminar%5D=' . $this->seminarUid,
            $this->subject->getRegistrationLink($this->pi1, $this->seminar)
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

        self::assertStringContainsString(
            '?id=' . $this->loginPageUid,
            $this->subject->getRegistrationLink($this->pi1, $this->seminar)
        );
    }

    /**
     * @test
     */
    public function getRegistrationLinkForPriceOnRequestReturnsEmptyString()
    {
        $this->seminar->setPriceOnRequest(true);
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertSame('', $this->subject->getRegistrationLink($this->pi1, $this->seminar));
    }

    // Tests concerning canRegisterIfLoggedIn

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarRegistrationOpenReturnsTrue()
    {
        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForPriceOnRequestReturnsFalse()
    {
        $this->seminar->setPriceOnRequest(true);

        self::assertFalse($this->subject->canRegisterIfLoggedIn($this->seminar));
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInUserAndRegistrationOpenReturnsTrue()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredUserReturnsFalse()
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
    public function canRegisterIfLoggedInForLoggedInButAlreadyRegisteredAndWithMultipleRegistrationsAllowedIsTrue()
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
    public function canRegisterIfLoggedInForLoggedInButBlockedUserReturnsFalse()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        $this->cachedSeminar = new TestingEvent(
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
    public function canRegisterIfLoggedInForLoggedOutUserAndFullyBookedSeminarReturnsFalse()
    {
        $this->createBookedOutSeminar();

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndCanceledSeminarReturnsFalse()
    {
        $this->seminar->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
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
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForLoggedOutUserAndSeminarWithUnlimitedVacanciesReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
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
            $this->subject->canRegisterIfLoggedIn($this->seminar)
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
            $this->subject->canRegisterIfLoggedIn($this->seminar)
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
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    // Tests concerning canRegisterIfLoggedInMessage

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndSeminarRegistrationOpenReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
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
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredUserReturnsAlreadyRegisteredMessage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        self::assertSame(
            $this->getLanguageService()->getLL('message_alreadyRegistered'),
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedInButAlreadyRegisteredAndMultipleRegistrationsAllowedIsEmpty()
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
    public function canRegisterIfLoggedInMessageForLoggedInButBlockedUserReturnsUserIsBlockedMessage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $this->testingFramework->createAndLoginFrontEndUser(),
                'seminar' => $this->seminarUid,
            ]
        );

        $this->cachedSeminar = new TestingEvent(
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
            $this->getLanguageService()->getLL('message_userIsBlocked'),
            $this->subject->canRegisterIfLoggedInMessage($this->cachedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndFullyBookedSeminarReturnsFullyBookedMessage()
    {
        $this->createBookedOutSeminar();

        self::assertSame(
            $this->getLanguageService()->getLL('message_noVacancies'),
            $this->subject->canRegisterIfLoggedInMessage($this->fullyBookedSeminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutUserAndCanceledSeminarReturnsSeminarCancelledMessage()
    {
        $this->seminar->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertSame(
            $this->getLanguageService()->getLL('message_seminarCancelled'),
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForLoggedOutAndWithoutRegistrationReturnsNoRegistrationNeededMessage()
    {
        $this->seminar->setAttendancesMax(0);
        $this->seminar->setNeedsRegistration(false);

        self::assertSame(
            $this->getLanguageService()->getLL('message_noRegistrationNecessary'),
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
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
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
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
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
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
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
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
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    // Test concerning userFulfillsRequirements

    /**
     * @test
     */
    public function userFulfillsRequirementsForEventWithoutRequirementsReturnsTrue()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function userFulfillsRequirementsForEventWithOneFulfilledRequirementReturnsTrue()
    {
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredDateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function userFulfillsRequirementsForEventWithOneUnfulfilledRequirementReturnsFalse()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function getMissingRequiredTopicsReturnsSeminarBag()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Bag_Event::class,
            $this->subject->getMissingRequiredTopics($this->seminar)
        );
    }

    /**
     * @test
     */
    public function getMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsOneItem()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function getMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsRequiredTopic()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid,
            'requirements'
        );

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function getMissingRequiredTopicsForTopicWithOneTwoNotFulfilledRequirementReturnsTwoItems()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid2,
            ]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function getMissingRequiredTopicsForTopicWithTwoRequirementsOneFulfilledOneUnfulfilledReturnsUnfulfilled()
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredDateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function getMissingRequiredTopicsForTopicWithOneFulfilledOneUnfulfilledDoesNotReturnFulfilled()
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        $requiredTopicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $requiredDateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $requiredTopicUid2,
            'requirements'
        );

        $this->cachedSeminar = new TestingEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                [
                    'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
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
    public function removeRegistrationHidesRegistrationOfUser()
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
    public function removeRegistrationWithFittingQueueRegistrationMovesItFromQueue()
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
    public function canRegisterSeatsForFullyBookedEventAndZeroSeatsGivenReturnsFalse()
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
    public function canRegisterSeatsForFullyBookedEventAndOneSeatGivenReturnsFalse()
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
    public function canRegisterSeatsForEventWithOneVacancyAndZeroSeatsGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndOneSeatGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithOneVacancyAndTwoSeatsGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(1);

        self::assertFalse(
            $this->subject->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndOneSeatGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(2);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndTwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setAttendancesMax(2);

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithTwoVacanciesAndThreeSeatsGivenReturnsFalse()
    {
        $this->seminar->setAttendancesMax(2);

        self::assertFalse(
            $this->subject->canRegisterSeats($this->seminar, 3)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndZeroSeatsGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 0)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndOneSeatGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 1)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndTwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 2)
        );
    }

    /**
     * @test
     */
    public function canRegisterSeatsForEventWithUnlimitedVacanciesAndFortytwoSeatsGivenReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->canRegisterSeats($this->seminar, 42)
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
            $this->subject->canRegisterSeats($this->seminar, 0)
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
            $this->subject->canRegisterSeats($this->seminar, 1)
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
            $this->subject->canRegisterSeats($this->seminar, 2)
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
            $this->subject->canRegisterSeats($this->seminar, 42)
        );
    }

    // Tests concerning notifyAttendee

    /**
     * @test
     */
    public function notifyAttendeeSendsMailToAttendeesMailAddress()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertArrayHasKey(
            'foo@bar.com',
            $this->email->getTo()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForAttendeeWithoutMailAddressNotSendsEmail()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $registration = new \Tx_Seminars_OldModel_Registration($registrationUid);

        $this->email->expects(self::never())->method('send');

        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForPlainTextEmail()
    {
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_TEXT_MAIL);
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
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

        $controller = new \Tx_Seminars_FrontEnd_DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForHtmlEmail()
    {
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->configuration->setAsBoolean('sendConfirmation', true);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
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

        $controller = new \Tx_Seminars_FrontEnd_DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeMailSubjectContainsConfirmationSubject()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_confirmationSubject'),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsEventTitle()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'test event',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyNotContainsSpaceBeforeComma()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $result = $this->email->getBody();

        self::assertStringNotContainsString(' ,', $result);
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationFood()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'something nice to eat',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationAccommodation()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'a nice, dry place',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationInterests()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'learning Ruby on Rails',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailSubjectContainsEventTitle()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
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
    public function notifyAttendeeSetsTypo3DefaultFromAddressAsSender()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            [$defaultMailFromAddress => $defaultMailFromName],
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsOrganizerAsReplyTo()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            ['mail@example.com' => 'test organizer'],
            $this->email->getReplyTo()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeWithoutTypo3DefaultFromAddressSetsOrganizerAsSender()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            ['mail@example.com' => 'test organizer'],
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailSetHasHtmlBody()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            '<html',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetDoesNotHaveHtmlBody()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            [],
            $this->filterEmailAttachmentsByTitle($this->email, 'text/html')
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetHasNoUnreplacedMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            '###',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailHasNoUnreplacedMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            '###',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForMailSetToUserModeAndUserSetToHtmlMailsHasHtmlBody()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            '<html',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForMailSetToUserModeAndUserSetToTextMailsNotHasHtmlBody()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            [],
            $this->filterEmailAttachmentsByTitle($this->email, 'text/html')
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsContainsNameOfUserInBody()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'Harry Callagan',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasLinkToSeminarInBody()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);
        $seminarLink = 'http://singleview.example.com/';

        self::assertStringContainsString(
            '<a href="' . $seminarLink,
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeAppendsOrganizersFooterToMailBody()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            "\n-- \norganizer footer",
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForConfirmedEventNotHasPlannedDisclaimer()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            \Tx_Seminars_Model_Event::STATUS_CONFIRMED
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForCancelledEventNotHasPlannedDisclaimer()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            \Tx_Seminars_Model_Event::STATUS_CANCELED
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlannedEventDisplaysPlannedDisclaimer()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            \Tx_Seminars_Model_Event::STATUS_PLANNED
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeehiddenDisclaimerFieldAndPlannedEventHidesPlannedDisclaimer()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->subject->setConfigurationValue(
            'hideFieldsInThankYouMail',
            'planned_disclaimer'
        );
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            \Tx_Seminars_Model_Event::STATUS_PLANNED
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasCssStylesFromFile()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'style=',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyCanContainAttendeesNames()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1 foo2');
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'foo1 foo2',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailEnumeratesAttendeesNames()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames("foo1\nfoo2");
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            "1. foo1\n2. foo2",
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailReturnsAttendeesNamesInOrderedList()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames("foo1\nfoo2");
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertRegExp(
            '/\\<ol>.*<li>foo1<\\/li>.*<li>foo2<\\/li>.*<\\/ol>/s',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceTitleInMailBody()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'foo_place',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeCanSendPlaceAddressInMailBody()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'foo_street',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithNoPlaceSendsWillBeAnnouncedMessage()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailSeparatesPlacesTitleAndAddressWithLinefeed()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            "place_title\nplace_address",
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailSeparatesPlacesTitleAndAddressWithBreaks()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'place_title<br>place_address',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeStripsHtmlTagsFromPlaceAddress()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            "place_title\nplace_address",
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesLineFeedsWithSpaces()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address1 address2',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnsWithSpaces()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address1 address2',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnAndLineFeedWithOneSpace()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address1 address2',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleCarriageReturnsWithOneSpace()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address1 address2',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsReplacesMultipleLineFeedsWithSpaces()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address1 address2',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsReplacesMultipleLineFeedsWithSpaces()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address1 address2',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleLineFeedAndCarriageReturnsWithSpaces()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address1 address2 address3',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCityOfPlace()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'footown',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsZipAndCityOfPlace()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            '12345 footown',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCountryOfPlace()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $country->getLocalShortName(),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSeparatesAddressAndCityWithNewline()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            "address\nfootown",
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsSeparatresAddressAndCityLineWithBreaks()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->extensionConfiguration
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'address<br>footown',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCountryAndCitySeparatesCountryAndCityWithComma()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            'footown, ' . $country->getLocalShortName(),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressWithCityAndNoCountryNotAddsSurplusCommaAfterCity()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringNotContainsString(
            'footown,',
            $this->email->getBody()
        );
    }

    /**
     * Checks that $string does not contain a raw label key.
     *
     * @param string $string
     *
     * @return void
     */
    private function assertNotContainsRawLabelKey(string $string)
    {
        self::assertStringNotContainsString('_', $string);
        self::assertStringNotContainsString('salutation', $string);
        self::assertStringNotContainsString('formal', $string);
    }

    // Tests concerning the iCalendar attachment

    /**
     * @test
     */
    public function notifyAttendeeHasUtf8CalendarAttachment()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        self::assertStringContainsString('text/calendar', $attachment->getContentType());
        self::assertStringContainsString('charset="utf-8"', $attachment->getContentType());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithWindowsLineEndings()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString("\r\n", $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithStartAndEndMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        $attachment = $attachments[0];
        self::assertContains('BEGIN:VCALENDAR', $attachment->getBody());
        self::assertContains('END:VCALENDAR', $attachment->getBody());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithPublishMethod()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        self::assertStringContainsString('method="publish"', $attachment->getContentType());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEvent()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        self::assertStringContainsString('component="vevent"', $attachment->getContentType());
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
    public function notifyAttendeeHasCalendarAttachmentWithImportantFields(string $value)
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString($value, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventTitleAsSummary()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('SUMMARY:' . $this->seminar->getTitle(), $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventStartDateWithTimeZoneFromEvent()
    {
        $timeZone = 'America/Chicago';
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->seminarUid, ['time_zone' => $timeZone]);
        $this->subject->setConfigurationValue('defaultTimeZone', 'Europe/Berlin');

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getBeginDateAsTimestamp());
        self::assertStringContainsString('DTSTART;TZID=/' . $timeZone . ':' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeFoEventWithoutTimeZoneHasAttachmentWithEventStartDateWithTimeZoneDefaultTimeZone()
    {
        $timeZone = 'Europe/Berlin';
        $this->subject->setConfigurationValue('defaultTimeZone', $timeZone);

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getBeginDateAsTimestamp());
        self::assertStringContainsString('DTSTART;TZID=/' . $timeZone . ':' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutEndDateHasCalendarAttachmentWithoutEndDate()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['end_date' => 0]
        );

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringNotContainsString('DTEND:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventEndDateTimeZoneFromEvent()
    {
        $timeZone = 'America/Chicago';
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->seminarUid, ['time_zone' => $timeZone]);
        $this->subject->setConfigurationValue('defaultTimeZone', 'Europe/Berlin');

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getEndDateAsTimestampEvenIfOpenEnded());
        self::assertStringContainsString('DTEND;TZID=/' . $timeZone . ':' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutTimeZoneHasCalendarAttachmentWithEndDateDefaultTimeZone()
    {
        $timeZone = 'Europe/Berlin';
        $this->subject->setConfigurationValue('defaultTimeZone', $timeZone);

        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getEndDateAsTimestampEvenIfOpenEnded());
        self::assertStringContainsString('DTEND;TZID=/' . $timeZone . ':' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventSubtitleAsDescription()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('DESCRIPTION:' . $this->seminar->getSubtitle(), $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutPlaceHasCalendarAttachmentWithoutLocation()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringNotContainsString('LOCATION:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventLocation()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('LOCATION:location title, some address', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeReplacesNewlinesInCalendarAttachment()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString("LOCATION:location title, some address, more, even more\r\n", $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithOrganizer()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('ORGANIZER;CN="test organizer":mailto:mail@example.com', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithUid()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertStringContainsString('UID:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithTimestamp()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->email, 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $GLOBALS['SIM_EXEC_TIME']);
        self::assertStringContainsString('DTSTAMP:' . $formattedDate, $content);
    }

    // Tests concerning the salutation

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationContainsInformalSalutation()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_hello_informal'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderUnknownContainsFormalUnknownSalutation()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_hello_formal_2'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderMaleContainsFormalMaleSalutation()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_hello_formal_0'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderFemaleContainsFormalFemaleSalutation()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_hello_formal_1'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationContainsFormalConfirmationText()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('email_confirmationHello'),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndConfirmationContainsInformalConfirmationText()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('email_confirmationHello_informal'),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndUnregistrationContainsFormalUnregistrationText()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL(
                    'email_confirmationOnUnregistrationHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndUnregistrationContainsInformalUnregistrationText()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL(
                    'email_confirmationOnUnregistrationHello_informal'
                ),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueConfirmationContainsFormalQueueConfirmationText()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL(
                    'email_confirmationOnRegistrationForQueueHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueConfirmationContainsInformalQueueConfirmationText()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL(
                    'email_confirmationOnRegistrationForQueueHello_informal'
                ),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueUpdateContainsFormalQueueUpdateText()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL(
                    'email_confirmationOnQueueUpdateHello'
                ),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueUpdateContainsInformalQueueUpdateText()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL(
                    'email_confirmationOnQueueUpdateHello_informal'
                ),
                $this->seminar->getTitle()
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->configuration->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderUnknownNotContainsRawTemplateMarkers()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderMaleNotContainsRawTemplateMarkers()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndGenderFemaleNotContainsRawTemplateMarkers()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndConfirmationNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmation', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndUnregistrationNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndUnregistrationNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnUnregistration', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnUnregistration'
        );

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnRegistrationForQueue', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnRegistrationForQueue'
        );

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueUpdateNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->subject->setConfigurationValue('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueUpdateNotContainsRawTemplateMarkers()
    {
        $this->configuration->setAsBoolean('sendConfirmationOnQueueUpdate', true);
        $this->subject->setConfigurationValue('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee(
            $registration,
            $pi1,
            'confirmationOnQueueUpdate'
        );

        $this->assertNotContainsRawLabelKey(
            $this->email->getBody()
        );
    }

    // Tests concerning the unregistration notice

    /**
     * @test
     */
    public function notifyAttendeeForUnregistrationMailDoesNotAppendUnregistrationNotice()
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
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
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
    public function notifyAttendeeForRegistrationMailAndNoUnregistrationPossibleNotAddsUnregistrationNotice()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationMailAndUnregistrationPossibleAddsUnregistrationNotice()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForRegistrationOnQueueMailAndUnregistrationPossibleAddsUnregistrationNotice()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
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
    public function notifyAttendeeForQueueUpdateMailAndUnregistrationPossibleAddsUnregistrationNotice()
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

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
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
    public function notifyAttendeeForSendConfirmationFalseNeverCallsRegistrationEmailHookMethods()
    {
        $this->configuration->setAsBoolean('sendConfirmation', false);
        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
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

        $controller = new \Tx_Seminars_FrontEnd_DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    // Tests regarding the notification of organizers

    /**
     * @test
     */
    public function notifyOrganizersForEventWithEmailsMutedNotSendsEmail()
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
    public function notifyOrganizersUsesTypo3DefaultFromAddressAsFrom()
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyOrganizers($registration);

        self::assertSame(
            [$defaultMailFromAddress => $defaultMailFromName],
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsReplyTo()
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->notifyOrganizers($registration);

        self::assertSame(
            ['mail@example.com' => 'test organizer'],
            $this->email->getReplyTo()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersWithoutTypo3DefaultFromAddressUsesOrganizerAsFrom()
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyOrganizers($registration);

        self::assertSame(
            [$defaultMailFromAddress => $defaultMailFromName],
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsTo()
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

        self::assertArrayHasKey(
            'mail@example.com',
            $this->email->getTo()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersIncludesHelloIfNotHidden()
    {
        $registration = $this->createRegistration();
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->subject->setConfigurationValue(
            'hideFieldsInNotificationMail',
            ''
        );

        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString(
            'Hello',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithOneVacancyShowsVacanciesLabelWithVacancyNumber()
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->subject->setConfigurationValue(
            'showSeminarFieldsInNotificationMail',
            'vacancies'
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['needs_registration' => 1, 'attendees_max' => 2]
        );

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

        self::assertRegExp(
            '/' . $this->getLanguageService()->getLL('label_vacancies') . ': 1\\n*$/',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithUnlimitedVacanciesShowsVacanciesLabelWithUnlimitedLabel()
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->subject->setConfigurationValue(
            'showSeminarFieldsInNotificationMail',
            'vacancies'
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['needs_registration' => 1, 'attendees_max' => 0]
        );

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_vacancies') . ': ' .
            $this->getLanguageService()->getLL('label_unlimited'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsLabelOfCompany()
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->subject->setConfigurationValue(
            'showAttendanceFieldsInNotificationMail',
            'company'
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo inc.',
            ]
        );

        $registration = new \Tx_Seminars_OldModel_Registration($registrationUid);
        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_company'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsCompanyOfRegistration()
    {
        $this->configuration->setAsBoolean('sendNotification', true);
        $this->subject->setConfigurationValue(
            'showAttendanceFieldsInNotificationMail',
            'company'
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->seminarUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'company' => 'foo inc.',
            ]
        );

        $registration = new \Tx_Seminars_OldModel_Registration($registrationUid);
        $this->subject->notifyOrganizers($registration);

        self::assertStringContainsString(
            'foo inc.',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForSendNotificationTrueCallsRegistrationEmailHookMethods()
    {
        $this->configuration->setAsBoolean('sendNotification', true);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
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
    public function notifyOrganizersForSendNotificationFalseNeverCallsRegistrationEmailHookMethods()
    {
        $this->configuration->setAsBoolean('sendNotification', false);

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
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
    public function sendAdditionalNotificationCanSendEmailToOneOrganizer()
    {
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertArrayHasKey(
            'mail@example.com',
            $this->email->getTo()
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

        $this->email->expects(self::never())->method('send');

        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationCanSendEmailsToTwoOrganizers()
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

        self::assertArrayHasKey('mail@example.com', $this->email->getTo());
        self::assertArrayHasKey('mail2@example.com', $this->email->getTo());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationUsesTypo3DefaultFromAddressAsSenderIfEmailIsSentToTwoOrganizers()
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

        self::assertArrayHasKey(
            $defaultMailFromAddress,
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationUsesTheFirstOrganizerAsReplyToIfEmailIsSentToTwoOrganizers()
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

        /** @var array<array-key, string> $replyTosOfFirstEmail */
        $replyTosOfFirstEmail = $this->email->getReplyTo();
        self::assertArrayHasKey('mail@example.com', $replyTosOfFirstEmail);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationUsesTheFirstOrganizerAsSenderIfEmailIsSentToTwoOrganizers()
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

        self::assertArrayHasKey(
            'mail@example.com',
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesSendsEnoughAttendancesMail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        unset($this->subject);
        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('email_additionalNotificationEnoughRegistrationsSubject'),
                $this->seminarUid,
                ''
            ),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesNotSendsEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 2, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesNotSetsNotificationFlag()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 2, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new TestingEvent($this->seminarUid);

        self::assertFalse($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithMoreThanEnoughAttendancesSendsEnoughAttendancesMail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        $firstEmail = $this->email;
        self::assertNotNull($firstEmail);
        self::assertStringContainsString(
            sprintf(
                $this->getLanguageService()->getLL('email_additionalNotificationEnoughRegistrationsSubject'),
                $this->seminarUid,
                ''
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
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new TestingEvent($this->seminarUid);

        self::assertTrue($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithMoreThanEnoughAttendancesSetsNotifiedFlag()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        // This makes sure the event is loaded from DB again.
        $event = new TestingEvent($this->seminarUid);

        self::assertTrue($event->haveOrganizersBeenNotifiedAboutEnoughAttendees());
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndOrganizersAlreadyNotifiedNotSendsEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42, 'organizers_notified_about_minimum_reached' => 1]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForMoreThanEnoughAttendancesAndOrganizersAlreadyNotifiedNotSends()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 1, 'attendees_max' => 42, 'organizers_notified_about_minimum_reached' => 1]
        );

        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->email->expects(self::never())->method('send');

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithZeroAttendeesMinDoesNotSendAnyMail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 0, 'attendees_max' => 42]
        );

        unset($this->subject);
        TestingRegistrationManager::purgeInstance();
        $this->subject = TestingRegistrationManager::getInstance();
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutSubject()
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
                $this->getLanguageService()->getLL('email_additionalNotificationIsFullSubject'),
                $this->seminarUid,
                ''
            ),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForBookedOutEventSendsEmailWithBookedOutMessage()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_additionalNotificationIsFull'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithNotEnoughAttendancesAndNotBookedOutSendsNoEmail()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_min' => 5, 'attendees_max' => 5]
        );

        $subject = new TestingRegistrationManager();
        $subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->email->expects(self::never())->method('send');

        $registration = $this->createRegistration();
        $subject->sendAdditionalNotification($registration);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndUnlimitedVacanciesSendsEmail()
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
    public function sendAdditionalNotificationForEventWithEnoughAttendancesAndOneVacancyHasVacancyNumber()
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
        $this->subject->setConfigurationValue(
            'showSeminarFieldsInNotificationMail',
            'vacancies'
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertRegExp(
            '/' . $this->getLanguageService()->getLL('label_vacancies') . ': 1\\n*$/',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationForEnoughAttendancesAndUnlimitedVacanciesHasUnlimitedLabel()
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
        $this->subject->setConfigurationValue(
            'showSeminarFieldsInNotificationMail',
            'vacancies'
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_vacancies') . ': '
            . $this->getLanguageService()->getLL('label_unlimited'),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationCallsRegistrationEmailHookMethods()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );

        $registrationOld = $this->createRegistration();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
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
    public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateAllowedReturnsTrue()
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
    public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateNotAllowedIsFalse()
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
    public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineOverReturnsFalse()
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
    public function allowsRegistrationByDateForBeginDateAndRegistrationDeadlineInFutureReturnsTrue()
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
    public function allowsRegistrationByDateForRegistrationBeginInFutureReturnsFalse()
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
    public function allowsRegistrationByDateForRegistrationBeginInPastReturnsTrue()
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
    public function allowsRegistrationByDateForNoRegistrationBeginReturnsTrue()
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
    public function allowsRegistrationByDateForBeginDateInPastAndRegistrationBeginInPastReturnsFalse()
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
    public function allowsRegistrationBySeatsForEventWithNoVacanciesAndNoQueueReturnsFalse()
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
    public function allowsRegistrationBySeatsForEventWithUnlimitedVacanciesReturnsTrue()
    {
        $this->seminar->setUnlimitedVacancies();

        self::assertTrue(
            $this->subject->allowsRegistrationBySeats($this->seminar)
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
            $this->subject->allowsRegistrationBySeats($this->seminar)
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
            $this->subject->allowsRegistrationBySeats($this->seminar)
        );
    }

    // Tests concerning registrationHasStarted

    /**
     * @test
     */
    public function registrationHasStartedForEventWithoutRegistrationBeginReturnsTrue()
    {
        $this->seminar->setRegistrationBeginDate(0);

        self::assertTrue(
            $this->subject->registrationHasStarted($this->seminar)
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
            $this->subject->registrationHasStarted($this->seminar)
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
    public function createRegistrationSavesRegistration()
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new \Tx_Seminars_FrontEnd_DefaultController();
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
            \Tx_Seminars_OldModel_Registration::class,
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
    public function createRegistrationIncreasesRegistrationCountInEventFromZeroToOne()
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new \Tx_Seminars_FrontEnd_DefaultController();
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
    public function createRegistrationReturnsRegistration()
    {
        $this->createAndLogInFrontEndUser();

        $plugin = new \Tx_Seminars_FrontEnd_DefaultController();
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
            \Tx_Seminars_Model_Registration::class,
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
        // Drops the non-saving mapper so that the registration mapper (once we use it) actually saves the registration.
        MapperRegistry::purgeInstance();
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);
        $this->testingFramework->markTableAsDirty('tx_seminars_seminars');

        $this->createAndLogInFrontEndUser();

        $plugin = new \Tx_Seminars_FrontEnd_DefaultController();
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
    public function setRegistrationDataForPositiveSeatsSetsSeats()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingSeatsSetsOneSeat()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForZeroSeatsSetsOneSeat()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNegativeSeatsSetsOneSeat()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForRegisteredThemselvesOneSetsItToTrue()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForRegisteredThemselvesZeroSetsItToFalse()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForRegisteredThemselvesMissingSetsItToFalse()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForSelectedAvailablePricePutsSelectedPriceCodeToPrice()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12, 'special' => 3]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForSelectedNotAvailablePricePutsFirstPriceCodeToPrice()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNoSelectedPricePutsFirstPriceCodeToPrice()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNoSelectedAndOnlyFreeRegularPriceAvailablePutsRegularPriceCodeToPrice()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 0]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForOneSeatsCalculatesTotalPriceFromSelectedPriceAndSeats()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForTwoSeatsCalculatesTotalPriceFromSelectedPriceAndSeats()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices']);
        $event->setData(['payment_methods' => new Collection()]);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyAttendeesNamesSetsAttendeesNames()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromAttendeesNames()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyAttendeesNamesSetsEmptyAttendeesNames()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingAttendeesNamesSetsEmptyAttendeesNames()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForPositiveKidsSetsNumberOfKids()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingKidsSetsZeroKids()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForZeroKidsSetsZeroKids()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNegativeKidsSetsZeroKids()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneSetsIt()
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromTwoSetsIt()
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod1 = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethod2 = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForSelectedAvailablePaymentMethodFromOneForFreeEventsSetsNoPaymentMethod()
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 0]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['method_of_payment' => $paymentMethod->getUid()]);

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndNoneAvailableSetsNone()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 0]);
        $event->method('getPaymentMethods')
            ->willReturn(new Collection());
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingPaymentMethodAndTwoAvailableSetsNone()
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod1 = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethod2 = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

        self::assertNull(
            $registration->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForMissingPaymentMethodAndOneAvailableSetsIt()
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForUnavailablePaymentMethodAndTwoAvailableSetsNone()
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod1 = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethod2 = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod1);
        $paymentMethods->add($paymentMethod2);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForUnavailablePaymentMethodAndOneAvailableSetsAvailable()
    {
        $subject = new TestingRegistrationManager();

        $paymentMethod = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $paymentMethods = new Collection();
        $paymentMethods->add($paymentMethod);

        /** @var \Tx_Seminars_Model_Event&MockObject $event */
        $event = $this->createPartialMock(\Tx_Seminars_Model_Event::class, ['getAvailablePrices', 'getPaymentMethods']);
        $event->method('getAvailablePrices')
            ->willReturn(['regular' => 12]);
        $event->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyAccountNumberSetsAccountNumber()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromAccountNumber()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInAccountNumber()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyAccountNumberSetsEmptyAccountNumber()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingAccountNumberSetsEmptyAccountNumber()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyBankCodeSetsBankCode()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromBankCode()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInBankCode()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyBankCodeSetsEmptyBankCode()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingBankCodeSetsEmptyBankCode()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyBankNameSetsBankName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromBankName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInBankName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyBankNameSetsEmptyBankName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingBankNameSetsEmptyBankName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyAccountOwnerSetsAccountOwner()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromAccountOwner()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInAccountOwner()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyAccountOwnerSetsEmptyAccountOwner()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingAccountOwnerSetsEmptyAccountOwner()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyCompanySetsCompany()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromCompany()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyCompanySetsEmptyCompany()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingCompanySetsEmptyCompany()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMaleGenderSetsGender()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForFemaleGenderSetsGender()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForInvalidIntegerGenderSetsUnknownGender()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForInvalidStringGenderSetsUnknownGender()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyGenderSetsUnknownGender()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingGenderSetsUnknownGender()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyNameSetsName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyNameSetsEmptyName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingNameSetsEmptyName()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyAddressSetsAddress()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromAddress()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyAddressSetsEmptyAddress()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingAddressSetsEmptyAddress()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyZipSetsZip()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromZip()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInZip()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyZipSetsEmptyZip()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingZipSetsEmptyZip()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyCitySetsCity()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromCity()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInCity()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyCitySetsEmptyCity()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingCitySetsEmptyCity()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForNonEmptyCountrySetsCountry()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataDropsHtmlTagsFromCountry()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataChangesWhitespaceToSpaceInCountry()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForEmptyCountrySetsEmptyCountry()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function setRegistrationDataForMissingCountrySetsEmptyCountry()
    {
        $subject = new TestingRegistrationManager();

        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
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
    public function existsSeminarForZeroUidReturnsFalse()
    {
        self::assertFalse(
            $this->subject->existsSeminar(0)
        );
    }

    /**
     * @test
     */
    public function existsSeminarForInexistentUidReturnsFalse()
    {
        self::assertFalse(
            $this->subject->existsSeminar($this->testingFramework->getAutoIncrement('tx_seminars_seminars'))
        );
    }

    /**
     * @test
     */
    public function existsSeminarForExistingDeleteUidReturnsFalse()
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
    public function existsSeminarForExistingHiddenUidReturnsFalse()
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
    public function existsSeminarForExistingUidReturnsTrue()
    {
        self::assertTrue(
            $this->subject->existsSeminar($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForZeroUidReturnsErrorMessage()
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_missingSeminarNumber'),
            $this->subject->existsSeminarMessage(0)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForZeroUidSendsNotFoundHeader()
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
    public function existsSeminarMessageForInexistentUidReturnsErrorMessage()
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_wrongSeminarNumber'),
            $this->subject->existsSeminarMessage($this->testingFramework->getAutoIncrement('tx_seminars_seminars'))
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForInexistentUidSendsNotFoundHeader()
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
    public function existsSeminarMessageForExistingDeleteUidReturnsErrorMessage()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['deleted' => 1]
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_wrongSeminarNumber'),
            $this->subject->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingHiddenUidReturnsErrorMessage()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['hidden' => 1]
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('message_wrongSeminarNumber'),
            $this->subject->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingUidReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->existsSeminarMessage($this->seminarUid)
        );
    }

    /**
     * @test
     */
    public function existsSeminarMessageForExistingUidNotSendsHttpHeader()
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
    public function getPricesAvailableForUserForNoAutomaticPricesAndNoRegistrationsReturnsAllAvailablePrices()
    {
        $this->subject->setConfigurationValue('automaticSpecialPriceForSubsequentRegistrationsBySameUser', false);

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
        $event = new \Tx_Seminars_OldModel_Event($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['regular', 'regular_board', 'special', 'special_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndOneRegistrationReturnsAllAvailablePrices()
    {
        $this->subject->setConfigurationValue('automaticSpecialPriceForSubsequentRegistrationsBySameUser', false);

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
        $event = new \Tx_Seminars_OldModel_Event($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['regular', 'regular_board', 'special', 'special_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForAutomaticPricesAndNoRegistrationsRemovesSpecialPrices()
    {
        $this->subject->setConfigurationValue('automaticSpecialPriceForSubsequentRegistrationsBySameUser', true);

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
        $event = new \Tx_Seminars_OldModel_Event($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['regular', 'regular_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndOneRegistrationRemovesRegularPrices()
    {
        $this->subject->setConfigurationValue('automaticSpecialPriceForSubsequentRegistrationsBySameUser', true);

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
        $event = new \Tx_Seminars_OldModel_Event($eventUid);

        $prices = $this->subject->getPricesAvailableForUser($event, $user);

        self::assertSame(['special', 'special_board'], array_keys($prices));
    }

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndOneRegistrationAndNoSpecialPriceKeepsRegularPrice()
    {
        $this->subject->setConfigurationValue('automaticSpecialPriceForSubsequentRegistrationsBySameUser', true);

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
        $event = new \Tx_Seminars_OldModel_Event($eventUid);

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
