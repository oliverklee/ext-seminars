<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\Country;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationEmail;
use OliverKlee\Seminars\Hooks\RegistrationEmailHookInterface;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures\RegistrationHookInterface;
use OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures\TestingRegistrationManager;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class RegistrationManagerTest extends TestCase
{
    use LanguageHelper;

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
     * @var \Tx_Oelib_EmailCollector
     */
    private $mailer = null;

    /**
     * @var HeaderCollector
     */
    private $headerCollector = null;

    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $frontEndUserMapper = null;

    /**
     * @var string[]
     */
    private $mockedClassNames = [];

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        \Tx_Seminars_OldModel_Registration::purgeCachedSeminars();
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_TEXT_MAIL);
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin.tx_seminars', new Configuration());
        $configurationRegistry->set('plugin.tx_seminars._LOCAL_LANG.default', new Configuration());
        $configurationRegistry->set('config', new Configuration());
        $configurationRegistry->set('page.config', new Configuration());

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

        TemplateHelper::setCachedConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $headerProxyFactory = HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerCollector = $headerProxyFactory->getHeaderProxy();

        $this->seminar = new TestingEvent($this->seminarUid);
        $this->subject = TestingRegistrationManager::getInstance();

        /** @var \Tx_Seminars_Service_SingleViewLinkBuilder|MockObject $linkBuilder */
        $linkBuilder = $this->createPartialMock(
            \Tx_Seminars_Service_SingleViewLinkBuilder::class,
            ['createAbsoluteUrlForEvent']
        );
        $linkBuilder->method('createAbsoluteUrlForEvent')->willReturn('http://singleview.example.com/');
        $this->subject->injectLinkBuilder($linkBuilder);

        $this->frontEndUserMapper = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        $this->purgeMockedInstances();

        TestingRegistrationManager::purgeInstance();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
    }

    /*
     * Utility functions
     */

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
        $htmlMimeParts = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/html');

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
     * @param string $className
     * @param mixed $instance
     *
     * @return void
     */
    private function addMockedInstance(string $className, $instance)
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
            \Tx_Seminars_FrontEnd_DefaultController::class,
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
            \Tx_Seminars_OldModel_Event::class,
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
    public function mockedInstancesListIsEmptyInitially()
    {
        self::assertEmpty($this->mockedClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsClassnameToList()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);

        $this->addMockedInstance($mockedClassName, $mockedInstance);
        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertCount(1, $this->mockedClassNames);
        self::assertSame($mockedClassName, $this->mockedClassNames[0]);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsInstanceToTypo3InstanceBuffer()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesClassnameFromList()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
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
        $mockedClassName = \get_class($mockedInstance);
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();

        self::assertNotSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
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

    /*
     * Tests for the link to the registration page
     */

    public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkTag()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertContains(
            '<a ',
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserCreatesLinkToLoginPage()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertContains(
            '?id=' . $this->loginPageUid,
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedOutUserContainsRedirectWithEventUid()
    {
        $this->testingFramework->logoutFrontEndUser();
        $this->createFrontEndPages();

        self::assertContains(
            'redirect_url',
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
        self::assertContains(
            '%255Bseminar%255D%3D' . $this->seminarUid,
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkTag()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertContains(
            '<a ',
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserCreatesLinkToRegistrationPageWithEventUid()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertContains(
            '?id=' . $this->registrationPageUid,
            $this->subject->getLinkToRegistrationOrLoginPage(
                $this->pi1,
                $this->seminar
            )
        );
        self::assertContains(
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

        self::assertContains(
            '?id=' . $this->registrationPageUid,
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    public function testGetLinkToRegistrationOrLoginPageWithLoggedInUserDoesNotContainRedirect()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertNotContains(
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

        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
            \sprintf($this->getLanguageService()->getLL('label_onlineRegistrationOnQueue'), 0),
            $this->subject->getLinkToRegistrationOrLoginPage($this->pi1, $this->seminar)
        );
    }

    /*
     * Tests concerning getRegistrationLink
     */

    /**
     * @test
     */
    public function getRegistrationLinkForLoggedInUserAndSeminarWithVacanciesReturnsLinkToRegistrationPage()
    {
        $this->createFrontEndPages();
        $this->createAndLogInFrontEndUser();

        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
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

    /*
     * Tests concerning canRegisterIfLoggedIn
     */

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

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationPossibleCallsCanRegisterForSeminarHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::once())->method('canRegisterForSeminar')->with($this->seminar, $user);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->canRegisterIfLoggedIn($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationNotPossibleNotCallsCanRegisterForSeminarHook()
    {
        $this->seminar->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::never())->method('canRegisterForSeminar');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->canRegisterIfLoggedIn($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationPossibleAndHookReturningTrueReturnsTrue()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::once())->method('canRegisterForSeminar')->willReturn(true);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        self::assertTrue(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInForRegistrationPossibleAndHookReturningFalseReturnsFalse()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::once())->method('canRegisterForSeminar')->willReturn(false);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        self::assertFalse(
            $this->subject->canRegisterIfLoggedIn($this->seminar)
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

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleCallsCanUserRegisterForSeminarMessageHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::once())->method('canRegisterForSeminarMessage')->with($this->seminar, $user);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->canRegisterIfLoggedInMessage($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationNotPossibleNotCallsCanUserRegisterForSeminarMessageHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();
        $this->seminar->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::never())->method('canRegisterForSeminarMessage');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->canRegisterIfLoggedInMessage($this->seminar);
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsStringFromHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::once())->method('canRegisterForSeminarMessage')->willReturn('Hello world!');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        self::assertSame(
            'Hello world!',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsNonEmptyStringFromFirstHook()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass1 = 'OneRegistrationHook' . \uniqid('', false);
        $hook1 = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass1)->getMock();
        $hook1->method('canRegisterForSeminarMessage')->willReturn('message 1');
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass1] = $hookClass1;
        GeneralUtility::addInstance($hookClass1, $hook1);

        $hookClass2 = 'AnotherRegistrationHook' . \uniqid('', false);
        $hook2 = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass2)->getMock();
        $hook2->method('canRegisterForSeminarMessage')->willReturn('message 2');
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass2] = $hookClass2;
        GeneralUtility::addInstance($hookClass2, $hook2);

        self::assertSame(
            'message 1',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * @test
     */
    public function canRegisterIfLoggedInMessageForRegistrationPossibleReturnsFirstNonEmptyStringFromHooks()
    {
        $this->testingFramework->createAndLoginFrontEndUser();

        $hookClass1 = 'OneRegistrationHook' . \uniqid('', false);
        $hook1 = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass1)->getMock();
        $hook1->method('canRegisterForSeminarMessage')->willReturn('');
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass1] = $hookClass1;
        GeneralUtility::addInstance($hookClass1, $hook1);

        $hookClass2 = 'AnotherRegistrationHook' . \uniqid('', false);
        $hook2 = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass2)->getMock();
        $hook2->method('canRegisterForSeminarMessage')->willReturn('message 2');
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass2] = $hookClass2;
        GeneralUtility::addInstance($hookClass2, $hook2);

        self::assertSame(
            'message 2',
            $this->subject->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /*
     * Test concerning userFulfillsRequirements
     */

    public function testUserFulfillsRequirementsForEventWithoutRequirementsReturnsTrue()
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

    public function testUserFulfillsRequirementsForEventWithOneFulfilledRequirementReturnsTrue()
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

    public function testUserFulfillsRequirementsForEventWithOneUnfulfilledRequirementReturnsFalse()
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

    /*
     * Tests concerning getMissingRequiredTopics
     */

    public function testGetMissingRequiredTopicsReturnsSeminarBag()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Bag_Event::class,
            $this->subject->getMissingRequiredTopics($this->seminar)
        );
    }

    public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsOneItem()
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

    public function testGetMissingRequiredTopicsForTopicWithOneNotFulfilledRequirementReturnsRequiredTopic()
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

    public function testGetMissingRequiredTopicsForTopicWithOneTwoNotFulfilledRequirementReturnsTwoItems()
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
            [
                'user' => $userUid,
                'seminar' => $seminarUid,
                'hidden' => 0,
            ]
        );

        $this->subject->removeRegistration($registrationUid, $this->pi1);

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
        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        // We cannot test for the expected parameters because the registration
        // instance does not exist yet at this point.
        $hook->expects(self::once())->method('seminarRegistrationRemoved');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

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

        $this->subject->removeRegistration($registrationUid, $this->pi1);
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

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_attendances',
                'registration_queue = 0 AND uid = ' . $queueRegistrationUid
            )
        );
    }

    /**
     * @test
     */
    public function removeRegistrationWithFittingQueueRegistrationCallsSeminarRegistrationMovedFromQueueHook()
    {
        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        // We cannot test for the expected parameters because the registration
        // instance does not exist yet at this point.
        $hook->expects(self::once())->method('seminarRegistrationMovedFromQueue');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

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
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'user' => $userUid,
                'seminar' => $seminarUid,
                'seats' => 1,
                'registration_queue' => 1,
            ]
        );

        $this->subject->removeRegistration($registrationUid, $this->pi1);
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

    /*
     * Tests concerning notifyAttendee
     */

    /**
     * @test
     */
    public function notifyAttendeeSendsMailToAttendeesMailAddress()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
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

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsModifyThankYouEmailHook()
    {
        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::once())->method('modifyThankYouEmail');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationFalseNotCallsModifyThankYouEmailHook()
    {
        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        $hook->expects(self::never())->method('modifyThankYouEmail');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->setConfigurationValue('sendConfirmation', false);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForPlainTextEmail()
    {
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_TEXT_MAIL);

        /** @var \Tx_Seminars_OldModel_Registration $registrationOld */
        $registrationOld = $this->createRegistration();
        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::once())->method('modifyAttendeeEmail')->with(
            self::isInstanceOf(\Tx_Oelib_Mail::class),
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $controller = new \Tx_Seminars_FrontEnd_DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueAndPlainTextEmailCallsPostProcessAttendeeEmailTextHookOnce()
    {
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_TEXT_MAIL);

        $registration = $this->createRegistration();

        $hookClassName = 'RegistrationEmailHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationEmailHookInterface::class)
            ->setMockClassName($hookClassName)->getMock();
        $hook->expects(self::once())->method('postProcessAttendeeEmailText');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $hook);

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueCallsRegistrationEmailHookMethodsForHtmlEmail()
    {
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);

        /** @var \Tx_Seminars_OldModel_Registration $registrationOld */
        $registrationOld = $this->createRegistration();
        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::once())->method('modifyAttendeeEmail')->with(
            self::isInstanceOf(\Tx_Oelib_Mail::class),
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $controller = new \Tx_Seminars_FrontEnd_DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationTrueAndHtmlEmailCallsPostProcessAttendeeEmailTextHookTwice()
    {
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);

        $registration = $this->createRegistration();

        $hookClassName = 'RegistrationEmailHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationEmailHookInterface::class)
            ->setMockClassName($hookClassName)->getMock();
        $hook->expects(self::exactly(2))->method('postProcessAttendeeEmailText');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $hook);

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeMailSubjectContainsConfirmationSubject()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->getLanguageService()->getLL('email_confirmationSubject'),
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsEventTitle()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $this->assertNotContainsRawLabelKey(
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyNotContainsSpaceBeforeComma()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $result = $this->mailer->getFirstSentEmail()->getBody();

        self::assertNotContains(' ,', $result);
    }

    /**
     * @test
     */
    public function notifyAttendeeMailBodyContainsRegistrationFood()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            'test event',
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsTypo3DefaultFromAddressAsSender()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeSetsOrganizerAsReplyTo()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            ['mail@example.com' => 'test organizer'],
            $this->mailer->getFirstSentEmail()->getReplyTo()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeWithoutTypo3DefaultFromAddressSetsOrganizerAsSender()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            ['mail@example.com' => 'test organizer'],
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailSetHasHtmlBody()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertSame(
            [],
            $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/html')
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForTextMailSetHasNoUnreplacedMarkers()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
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
            $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/html')
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsContainsNameOfUserInBody()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $registration = $this->createRegistration();
        $registration->getFrontEndUser()->setData(
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            \Tx_Seminars_Model_Event::STATUS_CONFIRMED
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForCancelledEventNotHasPlannedDisclaimer()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            \Tx_Seminars_Model_Event::STATUS_CANCELED
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertNotContains(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlannedEventDisplaysPlannedDisclaimer()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $registration = $this->createRegistration();
        $registration->getSeminarObject()->setStatus(
            \Tx_Seminars_Model_Event::STATUS_PLANNED
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeehiddenDisclaimerFieldAndPlannedEventHidesPlannedDisclaimer()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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

        self::assertNotContains(
            $this->getLanguageService()->getLL('label_planned_disclaimer'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForHtmlMailsHasCssStylesFromFile()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1 foo2');
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1' . LF . 'foo2');
        $this->subject->notifyAttendee($registration, $pi1);

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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $registration->setAttendeesNames('foo1' . LF . 'foo2');
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlainTextMailSeparatesPlacesTitleAndAddressWithLinefeed()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . LF . 'address2']
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
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnsWithSpaces()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . CR . 'address2']
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
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesCarriageReturnAndLineFeedWithOneSpace()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . CRLF . 'address2']
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
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleCarriageReturnsWithOneSpace()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . CR . CR . 'address2']
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
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsReplacesMultipleLineFeedsWithSpaces()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . LF . LF . 'address2']
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
            'address1 address2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndHtmlMailsReplacesMultipleLineFeedsWithSpaces()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . LF . LF . 'address2']
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
            'address1 address2',
            $this->getEmailHtmlPart()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressReplacesMultipleLineFeedAndCarriageReturnsWithSpaces()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
            ->setAsInteger('eMailFormatForAttendees', TestingRegistrationManager::SEND_HTML_MAIL);
        $this->subject->setConfigurationValue(
            'cssFileForAttendeeMail',
            'EXT:seminars/Resources/Private/CSS/thankYouMail.css'
        );

        $uid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['address' => 'address1' . LF . 'address2' . CR . CRLF . 'address3']
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
            'address1 address2 address3',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSendsCityOfPlace()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);

        /** @var CountryMapper $mapper */
        $mapper = MapperRegistry::get(CountryMapper::class);
        /** @var Country $country */
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForPlaceAddressAndPlainTextMailsSeparatesAddressAndCityWithNewline()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationProxy::getInstance('seminars')
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
        $this->subject->setConfigurationValue('sendConfirmation', true);

        /** @var CountryMapper $mapper */
        $mapper = MapperRegistry::get(CountryMapper::class);
        /** @var Country $country */
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
    private function assertNotContainsRawLabelKey(string $string)
    {
        self::assertNotContains('_', $string);
        self::assertNotContains('salutation', $string);
        self::assertNotContains('formal', $string);
    }

    /*
     * Tests concerning the iCalendar attachment
     */

    /**
     * @test
     */
    public function notifyAttendeeHasUtf8CalendarAttachment()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        self::assertContains('text/calendar', $attachment->getContentType());
        self::assertContains('charset="utf-8"', $attachment->getContentType());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithWindowsLineEndings()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains(CRLF, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithStartAndEndMarkers()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithPublishMethod()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        self::assertContains('method="publish"', $attachment->getContentType());
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEvent()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        self::assertContains('component="vevent"', $attachment->getContentType());
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains($value, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventTitleAsSummary()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains('SUMMARY:' . $this->seminar->getTitle(), $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventStartDateWithTimeZoneFromEvent()
    {
        $timeZone = 'America/Chicago';
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->seminarUid, ['time_zone' => $timeZone]);
        $this->subject->setConfigurationValue('defaultTimeZone', 'Europe/Berlin');

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getBeginDateAsTimestamp());
        self::assertContains('DTSTART;TZID=/' . $timeZone . ':' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeFoEventWithoutTimeZoneHasAttachmentWithEventStartDateWithTimeZoneDefaultTimeZone()
    {
        $timeZone = 'Europe/Berlin';
        $this->subject->setConfigurationValue('defaultTimeZone', $timeZone);

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getBeginDateAsTimestamp());
        self::assertContains('DTSTART;TZID=/' . $timeZone . ':' . $formattedDate, $content);
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertNotContains('DTEND:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventEndDateTimeZoneFromEvent()
    {
        $timeZone = 'America/Chicago';
        $this->testingFramework->changeRecord('tx_seminars_seminars', $this->seminarUid, ['time_zone' => $timeZone]);
        $this->subject->setConfigurationValue('defaultTimeZone', 'Europe/Berlin');

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getEndDateAsTimestampEvenIfOpenEnded());
        self::assertContains('DTEND;TZID=/' . $timeZone . ':' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutTimeZoneHasCalendarAttachmentWithEndDateDefaultTimeZone()
    {
        $timeZone = 'Europe/Berlin';
        $this->subject->setConfigurationValue('defaultTimeZone', $timeZone);

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $this->seminar->getEndDateAsTimestampEvenIfOpenEnded());
        self::assertContains('DTEND;TZID=/' . $timeZone . ':' . $formattedDate, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithEventSubtitleAsDescription()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains('DESCRIPTION:' . $this->seminar->getSubtitle(), $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeForEventWithoutPlaceHasCalendarAttachmentWithoutLocation()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertNotContains('LOCATION:', $content);
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains('LOCATION:location title, some address', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeReplacesNewlinesInCalendarAttachment()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'location title', 'address' => 'some address' . CRLF . 'more' . LF . 'even more' . LF]
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_place_mm', $this->seminarUid, $siteUid);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['place' => 1]
        );

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains('LOCATION:location title, some address, more, even more' . CRLF, $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithOrganizer()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains('ORGANIZER;CN="test organizer":mailto:mail@example.com', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithUid()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        self::assertContains('UID:', $content);
    }

    /**
     * @test
     */
    public function notifyAttendeeHasCalendarAttachmentWithTimestamp()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);

        $attachments = $this->filterEmailAttachmentsByTitle($this->mailer->getFirstSentEmail(), 'text/calendar');
        self::assertNotEmpty($attachments);
        /** @var \Swift_Mime_Attachment $attachment */
        $attachment = $attachments[0];
        $content = $attachment->getBody();
        $formattedDate = strftime('%Y%m%dT%H%M%S', $GLOBALS['SIM_EXEC_TIME']);
        self::assertContains('DTSTAMP:' . $formattedDate, $content);
    }

    /*
     * Tests concerning the salutation
     */

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationContainsInformalSalutation()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'informal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_informal'),
            $this->mailer->getFirstSentEmail()->getBody()
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com']
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_formal_2'),
            $this->mailer->getFirstSentEmail()->getBody()
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com', 'gender' => 0]
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_formal_0'),
            $this->mailer->getFirstSentEmail()->getBody()
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');
        $registration = $this->createRegistration();
        $this->testingFramework->changeRecord(
            'fe_users',
            $registration->getFrontEndUser()->getUid(),
            ['email' => 'foo@bar.com', 'gender' => 1]
        );
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $this->subject->notifyAttendee($registration, $pi1);

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_formal_1'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationContainsFormalConfirmationText()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL('email_confirmationHello'),
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL('email_confirmationHello_informal'),
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
        $this->subject->setConfigurationValue(
            'sendConfirmationOnUnregistration',
            true
        );
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL(
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
        $this->subject->setConfigurationValue(
            'sendConfirmationOnUnregistration',
            true
        );
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL(
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
        $this->subject->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue',
            true
        );
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL(
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
        $this->subject->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue',
            true
        );
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL(
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
        $this->subject->setConfigurationValue(
            'sendConfirmationOnQueueUpdate',
            true
        );
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL(
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
        $this->subject->setConfigurationValue(
            'sendConfirmationOnQueueUpdate',
            true
        );
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL(
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
        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'informal');
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
            $this->mailer->getFirstSentEmail()->getBody()
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');
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
            $this->mailer->getFirstSentEmail()->getBody()
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');
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
            $this->mailer->getFirstSentEmail()->getBody()
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

        $this->subject->setConfigurationValue('sendConfirmation', true);
        ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndConfirmationNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndConfirmationNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue('sendConfirmation', true);
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndUnregistrationNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue(
            'sendConfirmationOnUnregistration',
            true
        );
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndUnregistrationNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue(
            'sendConfirmationOnUnregistration',
            true
        );
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue',
            true
        );
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueConfirmationNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue',
            true
        );
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForFormalSalutationAndQueueUpdateNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue(
            'sendConfirmationOnQueueUpdate',
            true
        );
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
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyAttendeeForInformalSalutationAndQueueUpdateNotContainsRawTemplateMarkers()
    {
        $this->subject->setConfigurationValue(
            'sendConfirmationOnQueueUpdate',
            true
        );
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
        /** @var TestingRegistrationManager|MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::never())->method('getUnregistrationNotice');

        $subject->setConfigurationValue('sendConfirmationOnUnregistration', true);
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
        TemplateHelper::setCachedConfigurationValue('allowUnregistrationWithEmptyWaitingList', false);

        /** @var TestingRegistrationManager|MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::never())->method('getUnregistrationNotice');
        $subject->setConfigurationValue('sendConfirmation', true);

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
        TemplateHelper::setCachedConfigurationValue('allowUnregistrationWithEmptyWaitingList', true);

        /** @var TestingRegistrationManager|MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::once())->method('getUnregistrationNotice');
        $subject->setConfigurationValue('sendConfirmation', true);

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
        /** @var TestingRegistrationManager|MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::once())->method('getUnregistrationNotice');

        $subject->setConfigurationValue(
            'sendConfirmationOnRegistrationForQueue',
            true
        );
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
        /** @var TestingRegistrationManager|MockObject $subject */
        $subject = $this->getMockBuilder(TestingRegistrationManager::class)
            ->setMethods(['getUnregistrationNotice'])->getMock();
        $subject->expects(self::once())->method('getUnregistrationNotice');

        $subject->setConfigurationValue('sendConfirmationOnQueueUpdate', true);
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
    public function notifyAttendeeForSendConfirmationTrueCallsPostProcessAttendeeEmailHook()
    {
        $hookClassName = 'RegistrationEmailHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationEmailHookInterface::class)
            ->setMockClassName($hookClassName)->getMock();
        $hook->expects(self::once())->method('postProcessAttendeeEmail');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $hook);

        $this->subject->setConfigurationValue('sendConfirmation', true);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationFalseNeverCallsPostProcessAttendeeEmailHook()
    {
        $hookClassName = 'RegistrationEmailHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationEmailHookInterface::class)
            ->setMockClassName($hookClassName)->getMock();
        $hook->expects(self::never())->method('postProcessAttendeeEmail');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $hook);

        $this->subject->setConfigurationValue('sendConfirmation', false);
        $pi1 = new \Tx_Seminars_FrontEnd_DefaultController();
        $pi1->init();

        $registration = $this->createRegistration();
        $this->subject->notifyAttendee($registration, $pi1);
    }

    /**
     * @test
     */
    public function notifyAttendeeForSendConfirmationFalseNeverCallsRegistrationEmailHookMethods()
    {
        /** @var \Tx_Seminars_OldModel_Registration $registrationOld */
        $registrationOld = $this->createRegistration();
        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
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

        $this->subject->setConfigurationValue('sendConfirmation', false);
        $controller = new \Tx_Seminars_FrontEnd_DefaultController();
        $controller->init();

        $this->subject->notifyAttendee($registrationOld, $controller);
    }

    /*
     * Tests regarding the notification of organizers
     */

    /**
     * @test
     */
    public function notifyOrganizersForEventWithEmailsMutedNotSendsEmail()
    {
        $this->subject->setConfigurationValue('sendNotification', true);
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['mute_notification_emails' => 1]
        );

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

        self::assertNull($this->mailer->getFirstSentEmail());
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesTypo3DefaultFromAddressAsFrom()
    {
        $this->subject->setConfigurationValue('sendNotification', true);

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyOrganizers($registration);

        self::assertSame(
            [$defaultMailFromAddress => $defaultMailFromName],
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsReplyTo()
    {
        $this->subject->setConfigurationValue('sendNotification', true);

        $registration = $this->createRegistration();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->notifyOrganizers($registration);

        self::assertSame(
            ['mail@example.com' => 'test organizer'],
            $this->mailer->getFirstSentEmail()->getReplyTo()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersWithoutTypo3DefaultFromAddressUsesOrganizerAsFrom()
    {
        $this->subject->setConfigurationValue('sendNotification', true);

        $registration = $this->createRegistration();

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->notifyOrganizers($registration);

        self::assertSame(
            [$defaultMailFromAddress => $defaultMailFromName],
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersUsesOrganizerAsTo()
    {
        $this->subject->setConfigurationValue('sendNotification', true);

        $registration = $this->createRegistration();
        $this->subject->notifyOrganizers($registration);

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
        $this->subject->setConfigurationValue('sendNotification', true);
        $this->subject->setConfigurationValue(
            'hideFieldsInNotificationMail',
            ''
        );

        $this->subject->notifyOrganizers($registration);

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
        $this->subject->setConfigurationValue('sendNotification', true);
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
            '/' . $this->getLanguageService()->getLL('label_vacancies') . ': 1$/',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithUnlimitedVacanciesShowsVacanciesLabelWithUnlimitedLabel()
    {
        $this->subject->setConfigurationValue('sendNotification', true);
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

        self::assertContains(
            $this->getLanguageService()->getLL('label_vacancies') . ': ' .
            $this->getLanguageService()->getLL('label_unlimited'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsLabelOfCompany()
    {
        $this->subject->setConfigurationValue('sendNotification', true);
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

        self::assertContains(
            $this->getLanguageService()->getLL('label_company'),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForRegistrationWithCompanyShowsCompanyOfRegistration()
    {
        $this->subject->setConfigurationValue('sendNotification', true);
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

        self::assertContains(
            'foo inc.',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function notifyOrganizersForSendNotificationTrueCallsRegistrationEmailHookMethods()
    {
        $this->subject->setConfigurationValue('sendNotification', true);

        /** @var \Tx_Seminars_OldModel_Registration $registrationOld */
        $registrationOld = $this->createRegistration();
        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::never())->method('modifyAttendeeEmail');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyPlainText');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyHtml');
        $hook->expects(self::once())->method('modifyOrganizerEmail')->with(
            self::isInstanceOf(\Tx_Oelib_Mail::class),
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
    public function notifyOrganizersForSendConfirmationTrueCallsPostProcessOrganizerEmailHook()
    {
        $this->subject->setConfigurationValue('sendNotification', true);

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $this->seminarUid, 'user' => $this->testingFramework->createFrontEndUser()]
        );
        $registration = new \Tx_Seminars_OldModel_Registration($registrationUid);

        $hookClassName = 'RegistrationEmailHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationEmailHookInterface::class)
            ->setMockClassName($hookClassName)->getMock();
        $hook->expects(self::once())->method('postProcessOrganizerEmail');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $hook);

        $this->subject->notifyOrganizers($registration);
    }

    /**
     * @test
     */
    public function notifyOrganizersForSendNotificationFalseNeverCallsRegistrationEmailHookMethods()
    {
        $this->subject->setConfigurationValue('sendNotification', false);

        /** @var \Tx_Seminars_OldModel_Registration $registrationOld */
        $registrationOld = $this->createRegistration();
        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
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

    /**
     * @test
     */
    public function notifyOrganizersForSendConfirmationFalseNeverCallsPostProcessOrganizerEmailHook()
    {
        $this->subject->setConfigurationValue('sendNotification', false);

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $this->seminarUid, 'user' => $this->testingFramework->createFrontEndUser()]
        );
        $registration = new \Tx_Seminars_OldModel_Registration($registrationUid);

        $hookClassName = 'RegistrationEmailHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationEmailHookInterface::class)
            ->setMockClassName($hookClassName)->getMock();
        $hook->expects(self::never())->method('postProcessOrganizerEmail');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $hook);

        $this->subject->notifyOrganizers($registration);
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
        $this->subject->sendAdditionalNotification($registration);

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

        $this->subject->sendAdditionalNotification($registration);

        self::assertNull($this->mailer->getFirstSentEmail());
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

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertSame(
            2,
            $this->mailer->getNumberOfSentEmails()
        );
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

        $sentEmails = $this->mailer->getSentEmails();

        self::assertArrayHasKey(
            $defaultMailFromAddress,
            $sentEmails[0]->getFrom()
        );
        self::assertArrayHasKey(
            $defaultMailFromAddress,
            $sentEmails[1]->getFrom()
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

        $sentEmails = $this->mailer->getSentEmails();

        self::assertArrayHasKey(
            'mail@example.com',
            $sentEmails[0]->getReplyTo()
        );
        self::assertArrayHasKey(
            'mail@example.com',
            $sentEmails[1]->getReplyTo()
        );
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

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL('email_additionalNotificationEnoughRegistrationsSubject'),
                $this->seminarUid,
                ''
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

        self::assertNull($this->mailer->getFirstSentEmail());
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

        $firstEmail = $this->mailer->getFirstSentEmail();
        self::assertNotNull($firstEmail);
        self::assertContains(
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

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        $firstEmail = $this->mailer->getFirstSentEmail();
        self::assertNull($firstEmail);
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

        $this->createRegistration();
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        $firstEmail = $this->mailer->getFirstSentEmail();
        self::assertNull($firstEmail);
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

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

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
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertContains(
            sprintf(
                $this->getLanguageService()->getLL('email_additionalNotificationIsFullSubject'),
                $this->seminarUid,
                ''
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
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );
        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertContains(
            $this->getLanguageService()->getLL('email_additionalNotificationIsFull'),
            $this->mailer->getFirstSentEmail()->getBody()
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

        $registration = $this->createRegistration();
        $subject->sendAdditionalNotification($registration);

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
            'tx_seminars_seminars',
            $this->seminarUid,
            [
                'attendees_min' => 1,
                'attendees_max' => 0,
                'needs_registration' => 1,
            ]
        );

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);

        self::assertSame(
            1,
            $this->mailer->getNumberOfSentEmails()
        );
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
            '/' . $this->getLanguageService()->getLL('label_vacancies') . ': 1$/',
            $this->mailer->getFirstSentEmail()->getBody()
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

        self::assertContains(
            $this->getLanguageService()->getLL('label_vacancies') . ': '
            . $this->getLanguageService()->getLL('label_unlimited'),
            $this->mailer->getFirstSentEmail()->getBody()
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

        /** @var \Tx_Seminars_OldModel_Registration $registrationOld */
        $registrationOld = $this->createRegistration();
        /** @var \Tx_Seminars_Mapper_Registration $mapper */
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class);
        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = $mapper->find($registrationOld->getUid());

        $hook = $this->createMock(RegistrationEmail::class);
        $hook->expects(self::never())->method('modifyAttendeeEmail');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyPlainText');
        $hook->expects(self::never())->method('modifyAttendeeEmailBodyHtml');
        $hook->expects(self::never())->method('modifyOrganizerEmail');
        $hook->expects(self::once())->method('modifyAdditionalEmail')->with(
            self::isInstanceOf(\Tx_Oelib_Mail::class),
            $registration,
            'IsFull'
        );

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationEmail::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->sendAdditionalNotification($registrationOld);
    }

    /**
     * @test
     */
    public function sendAdditionalNotificationCallsPostProcessOrganizerEmailHook()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['attendees_max' => 1]
        );
        $hookClassName = 'RegistrationEmailHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationEmailHookInterface::class)
            ->setMockClassName($hookClassName)->getMock();
        $hook->expects(self::once())->method('postProcessAdditionalEmail');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $hook);

        $registration = $this->createRegistration();
        $this->subject->sendAdditionalNotification($registration);
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
            $this->subject->allowsRegistrationByDate($this->seminar)
        );
    }

    /**
     * @test
     */
    public function allowsRegistrationByDateForEventWithoutDateAndRegistrationForEventsWithoutDateNotAllowedIsFalse()
    {
        $this->seminar->setAllowRegistrationForEventsWithoutDate(0);
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

        $plugin = new \Tx_Seminars_FrontEnd_DefaultController();
        $plugin->cObj = $this->getFrontEndController()->cObj;
        /** @var TestingRegistrationManager|MockObject $subject */
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
        /** @var TestingRegistrationManager|MockObject $subject */
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
        $seminarData = $seminarsConnection->select(['*'], 'tx_seminars_seminars', ['uid' => $this->seminarUid])->fetch();

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
        /** @var TestingRegistrationManager|MockObject $subject */
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
        /** @var TestingRegistrationManager|MockObject $subject */
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

    /**
     * @test
     */
    public function createRegistrationCallsSeminarRegistrationCreatedHook()
    {
        $this->createAndLogInFrontEndUser();

        $hookClass = 'RegistrationHook' . \uniqid('', false);
        $hook = $this->getMockBuilder(RegistrationHookInterface::class)->setMockClassName($hookClass)->getMock();
        // We cannot test for the expected parameters because the registration
        // instance does not exist yet at this point.
        $hook->expects(self::once())->method('seminarRegistrationCreated');

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['registration'][$hookClass] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $plugin = new \Tx_Seminars_FrontEnd_DefaultController();
        $plugin->cObj = $this->getFrontEndController()->cObj;
        /** @var TestingRegistrationManager|MockObject $subject */
        $subject = $this->createPartialMock(
            TestingRegistrationManager::class,
            [
                'notifyAttendee',
                'notifyOrganizers',
                'sendAdditionalNotification',
                'setRegistrationData',
            ]
        );

        $subject->createRegistration(
            $this->seminar,
            [],
            $plugin
        );

        $uid = $subject->getRegistration()->getUid();
        $connection = $this->getConnectionForTable('tx_seminars_attendances');
        $connection->delete('tx_seminars_attendances', ['uid' => $uid]);
    }

    /*
     * Tests concerning setRegistrationData()
     */

    /**
     * @test
     */
    public function setRegistrationDataForPositiveSeatsSetsSeats()
    {
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['attendees_names' => 'John Doe' . LF . 'Jane Doe']
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
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event|MockObject $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['account_number' => '123' . CRLF . '455' . TAB . ' ABC']
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['bank_code' => '123' . CRLF . '455' . TAB . ' ABC']
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['bank_name' => 'Swiss' . CRLF . 'Tax' . TAB . ' Protection']
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['account_owner' => 'John' . CRLF . TAB . ' Doe']
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData(
            $registration,
            ['company' => 'Business Ltd.' . LF . 'Tom, Dick & Harry']
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
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['name' => 'John' . CRLF . TAB . ' Doe']);

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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['address' => 'Back Road 42' . LF . '(second door)']);

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
        $subject = new TestingRegistrationManager();

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['zip' => '12345' . CRLF . TAB . ' ABC']);

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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['city' => 'Santiago' . CRLF . TAB . ' de Chile']);

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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, ['country' => 'South' . CRLF . TAB . ' Africa']);

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

        /** @var \Tx_Seminars_Model_Event $event */
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

        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setEvent($event);

        $subject->setRegistrationData($registration, []);

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
        self::assertContains(
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
        self::assertContains(
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

        self::assertContains(
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

        self::assertContains(
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

    /*
     * Tests concerning getPricesAvailableForUser
     */

    /**
     * @test
     */
    public function getPricesAvailableForUserForNoAutomaticPricesAndNoRegistrationsReturnsAllAvailablePrices()
    {
        $this->subject->setConfigurationValue('automaticSpecialPriceForSubsequentRegistrationsBySameUser', false);

        $userUid = $this->testingFramework->createFrontEndUser();
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
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
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
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
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
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
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
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
        /** @var \Tx_Seminars_Model_FrontEndUser $user */
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

    private function getConnectionForTable(string $table): Connection
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $connectionPool->getConnectionForTable($table);
    }
}
