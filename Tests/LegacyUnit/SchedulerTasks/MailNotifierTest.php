<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\SchedulerTasks;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\Interfaces\AccessibleObject;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminar\Email\Salutation;
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;
use OliverKlee\Seminars\SchedulerTasks\MailNotifier;
use OliverKlee\Seminars\Service\EmailService;
use OliverKlee\Seminars\Service\EventStatusService;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MailNotifierTest extends TestCase
{
    use EmailTrait;

    use MakeInstanceTrait;

    /**
     * @var MailNotifier&MockObject&AccessibleObject
     */
    protected $subject = null;

    /**
     * @var TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var Configuration
     */
    protected $configuration = null;

    /**
     * @var EventStatusService&MockObject
     */
    protected $eventStatusService = null;

    /**
     * @var EmailService&MockObject
     */
    protected $emailService = null;

    /**
     * @var \Tx_Seminars_Mapper_Event&MockObject
     */
    protected $eventMapper = null;

    /**
     * @var Salutation&MockObject
     */
    protected $emailSalutation = null;

    /**
     * @var LanguageService|null
     */
    private $languageBackup = null;

    /**
     * @var LanguageService
     */
    private $languageService = null;

    /**
     * @var ObjectProphecy
     */
    private $registrationDigestProphecy = null;

    /**
     * @var RegistrationDigest
     */
    private $registrationDigest = null;

    /**
     * @var (MockObject&MailMessage)|null
     */
    private $email = null;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->languageBackup = $GLOBALS['LANG'] ?? null;
        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            self::markTestSkipped('This tests needs the scheduler extension.');
        }
        Bootstrap::initializeBackendAuthentication();

        $this->languageService = new LanguageService();
        $this->languageService->init('default');
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $GLOBALS['LANG'] = $this->languageService;

        $this->testingFramework = new TestingFramework('tx_seminars');

        ConfigurationRegistry::getInstance()->set('plugin', new Configuration());
        $this->configuration = new Configuration();
        $this->configuration->setData(
            [
                'sendEventTakesPlaceReminderDaysBeforeBeginDate' => 2,
                'sendCancelationDeadlineReminder' => true,
                'filenameForRegistrationsCsv' => 'registrations.csv',
                'dateFormatYMD' => '%d.%m.%Y',
                'fieldsFromAttendanceForEmailCsv' => 'title',
                'showAttendancesOnRegistrationQueueInEmailCsv' => true,
            ]
        );
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

        /** @var MailNotifier&MockObject&AccessibleObject $subject */
        $subject = $this->getAccessibleMock(MailNotifier::class, ['dummy']);
        $this->subject = $subject;

        $configurationPageUid = $this->testingFramework->createFrontEndPage();
        $subject->setConfigurationPageUid($configurationPageUid);

        /** @var EventStatusService&MockObject $eventStatusService */
        $eventStatusService = $this->createMock(EventStatusService::class);
        $this->eventStatusService = $eventStatusService;
        $subject->_set('eventStatusService', $eventStatusService);

        /** @var EmailService&MockObject $emailService */
        $emailService = $this->createMock(EmailService::class);
        $this->emailService = $emailService;
        $subject->_set('emailService', $emailService);

        /** @var \Tx_Seminars_Mapper_Event&MockObject $eventMapper */
        $eventMapper = $this->createMock(\Tx_Seminars_Mapper_Event::class);
        $this->eventMapper = $eventMapper;
        $subject->_set('eventMapper', $eventMapper);

        /** @var Salutation&MockObject $emailSalutation */
        $emailSalutation = $this->createMock(Salutation::class);
        $this->emailSalutation = $emailSalutation;
        $subject->_set('emailSalutation', $emailSalutation);

        $this->registrationDigestProphecy = $this->prophesize(RegistrationDigest::class);
        $this->registrationDigest = $this->registrationDigestProphecy->reveal();
        $subject->_set('registrationDigest', $this->registrationDigest);

        $this->email = $this->createEmailMock();
    }

    protected function tearDown()
    {
        if ($this->testingFramework !== null) {
            $this->testingFramework->cleanUp();
        }

        $GLOBALS['LANG'] = $this->languageBackup;
        $this->languageBackup = null;
    }

    // Utility functions

    /**
     * Creates a seminar record and an organizer record and the relation
     * between them.
     *
     * @param array $additionalSeminarData additional data for the seminar record, may be empty
     *
     * @return int UID of the added event, will be > 0
     */
    private function createSeminarWithOrganizer(array $additionalSeminarData = []): int
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'Mr. Test', 'email' => 'MrTest@example.com']
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array_merge($additionalSeminarData, ['organizers' => 1])
        );

        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);

        return $eventUid;
    }

    /**
     * Adds a speaker relation to an existing seminar record.
     *
     * Note: This function must only be called once per test.
     *
     * @param int $eventUid event UID, must be > 0
     *
     * @return void
     */
    private function addSpeaker(int $eventUid)
    {
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['cancelation_period' => 2]
        );

        $this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, ['speakers' => 1]);
        $this->testingFramework->createRelation('tx_seminars_seminars_speakers_mm', $eventUid, $speakerUid);
    }

    /**
     * Returns the first e-mail attachment (if there is any).
     *
     * @return \Swift_Mime_Attachment
     */
    protected function getFirstEmailAttachment(): \Swift_Mime_Attachment
    {
        $children = $this->email->getChildren();
        $attachment = $children[0];
        if (!$attachment instanceof \Swift_Mime_Attachment) {
            throw new \UnexpectedValueException('Attachment is no Swift_Mime_Attachment.', 1630771213);
        }

        return $attachment;
    }

    // Tests for the utility functions

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesSeminarRecord()
    {
        $this->createSeminarWithOrganizer();
        $connection = $this->getConnectionForTable('tx_seminars_seminars');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars', [])
        );
    }

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesSeminarRecordWithAdditionalData()
    {
        $this->createSeminarWithOrganizer(['title' => 'foo']);
        $connection = $this->getConnectionForTable('tx_seminars_seminars');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars', ['title' => 'foo'])
        );
    }

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesOrganizerRecord()
    {
        $this->createSeminarWithOrganizer();
        $connection = $this->getConnectionForTable('tx_seminars_organizers');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_organizers', [])
        );
    }

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesRelationBetweenSeminarAndOrganizer()
    {
        $this->createSeminarWithOrganizer();
        $connection = $this->getConnectionForTable('tx_seminars_seminars_organizers_mm');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars_organizers_mm', [])
        );
    }

    /**
     * @test
     */
    public function addSpeakerCreatesSpeakerRecord()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer());
        $connection = $this->getConnectionForTable('tx_seminars_speakers');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_speakers', [])
        );
    }

    /**
     * @test
     */
    public function addSpeakerCreatesSpeakerRelation()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer());
        $connection = $this->getConnectionForTable('tx_seminars_seminars_speakers_mm');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars_speakers_mm', [])
        );
    }

    /**
     * @test
     */
    public function addSpeakerSetsNumberOfSpeakersToOneForTheSeminarWithTheProvidedUid()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer());
        $connection = $this->getConnectionForTable('tx_seminars_seminars');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars', ['speakers' => 1])
        );
    }

    // Basic tests

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function classCanBeInstantiated()
    {
        new MailNotifier();
    }

    /**
     * @test
     */
    public function classIsSchedulerTask()
    {
        self::assertInstanceOf(AbstractTask::class, $this->subject);
    }

    /**
     * @test
     */
    public function setConfigurationPageUidSetsConfigiurationPageUid()
    {
        $uid = 42;
        $this->subject->setConfigurationPageUid($uid);

        $result = $this->subject->getConfigurationPageUid();

        self::assertSame($uid, $result);
    }

    /**
     * @test
     */
    public function executeWithoutPageConfigurationReturnsFalse()
    {
        $result = (new MailNotifier())->execute();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function executeWithZeroPageConfigurationReturnsFalse()
    {
        $subject = new MailNotifier();
        $subject->setConfigurationPageUid(0);

        $result = $subject->execute();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function executeWithPageConfigurationReturnsTrue()
    {
        $subject = new MailNotifier();
        $pageUid = $this->testingFramework->createFrontEndPage();
        $subject->setConfigurationPageUid($pageUid);

        $result = $subject->execute();

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function executeWithPageConfigurationCallsAllSeparateSteps()
    {
        /** @var MailNotifier&MockObject $subject */
        $subject = $this->createPartialMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses']
        );
        $pageUid = $this->testingFramework->createFrontEndPage();
        $subject->setConfigurationPageUid($pageUid);

        $subject->expects(self::once())->method('sendEventTakesPlaceReminders');
        $subject->expects(self::once())->method('sendCancellationDeadlineReminders');
        $subject->expects(self::once())->method('automaticallyChangeEventStatuses');

        $subject->execute();
    }

    /**
     * @test
     */
    public function executeWithoutPageConfigurationNotCallsAnySeparateStep()
    {
        /** @var MailNotifier&MockObject $subject */
        $subject = $this->createPartialMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses']
        );
        $subject->setConfigurationPageUid(0);

        $subject->expects(self::never())->method('sendEventTakesPlaceReminders');
        $subject->expects(self::never())->method('sendCancellationDeadlineReminders');
        $subject->expects(self::never())->method('automaticallyChangeEventStatuses');

        $subject->execute();
    }

    /**
     * @test
     */
    public function executeWithPageConfigurationExecutesRegistrationDigest()
    {

        /** @var MailNotifier&MockObject&AccessibleObject $subject */
        $subject = $this->getAccessibleMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses'],
            [],
            '',
            false
        );

        $pageUid = $this->testingFramework->createFrontEndPage();
        $subject->setConfigurationPageUid($pageUid);
        $subject->_set('registrationDigest', $this->registrationDigest);

        $subject->_call('executeAfterInitialization');

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->registrationDigestProphecy->execute()->shouldHaveBeenCalled();
    }

    // Tests concerning sendEventTakesPlaceReminders

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameSendsReminder()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceSubject()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $subject = $this->languageService->getLL('email_eventTakesPlaceReminderSubject');
        $subject = str_replace(['%event', '%days'], ['', 2], $subject);

        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            $subject,
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceMessage()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $message = $this->languageService->getLL('email_eventTakesPlaceReminder');
        $message = str_replace(['%event', '%organizer'], ['', 'Mr. Test'], $message);

        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            substr($message, 0, strpos($message, '%') - 1),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForTwoConfirmedEventsWithinConfiguredTimeFrameSendsTwoReminders()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedWithTwoOrganizersAndWithinTimeFrameSendsTwoReminders()
    {
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
        $this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, ['organizers' => 2]);

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersSetsSentFlagInTheDatabaseWhenReminderWasSent()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
        $connection = $this->getConnectionForTable('tx_seminars_seminars');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars', ['event_takes_place_reminder_sent' => 1])
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedWithinTimeFrameAndReminderSentFlagTrueSendsNoReminder()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
                'event_takes_place_reminder_sent' => 1,
            ]
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventWithPassedBeginDateSendsNoReminder()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventBeginningLaterThanConfiguredTimeFrameSendsNoReminder()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * Time::SECONDS_PER_DAY),
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventAndNoTimeFrameConfiguredSendsNoReminder()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $this->configuration->setAsInteger('sendEventTakesPlaceReminderDaysBeforeBeginDate', 0);

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForCanceledEventWithinConfiguredTimeFrameSendsNoReminder()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED,
            ]
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForPlannedEventWithinConfiguredTimeFrameSendsNoReminder()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
            ]
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();
    }

    // Tests concerning sendCancellationDeadlineReminders

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventAndOptionEnabledSendsReminder()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineSubject()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $subject = $this->languageService->getLL('email_cancelationDeadlineReminderSubject');
        $subject = str_replace('%event', '', $subject);

        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();

        self::assertContains(
            $subject,
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineMessage()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $message = $this->languageService->getLL('email_cancelationDeadlineReminder');
        $message = str_replace(['%event', '%organizer'], ['', 'Mr. Test'], $message);

        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );
        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();

        self::assertContains(
            substr($message, 0, strpos($message, '%') - 1),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForTwoPlannedEventsAndOptionEnabledSendsTwoReminders()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventWithTwoOrganizersAndOptionEnabledSendsTwoReminders()
    {
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
            ]
        );
        $this->addSpeaker($eventUid);
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $eventUid,
            $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $eventUid,
            ['organizers' => 2]
        );

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersSetsFlagInTheDatabaseWhenReminderWasSent()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
        $connection = $this->getConnectionForTable('tx_seminars_seminars');

        self::assertGreaterThan(
            0,
            $connection->count('*', 'tx_seminars_seminars', ['cancelation_deadline_reminder_sent' => 1])
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedAndOptionEnabledAndReminderSentFlagTrueSendsNoReminder()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                    'cancelation_deadline_reminder_sent' => 1,
                ]
            )
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventWithPassedBeginDateSendsNoReminder()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventWithSpeakersDeadlineNotYetReachedSendsNoReminder()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * Time::SECONDS_PER_DAY),
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventAndOptionDisabledSendsNoReminder()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );
        $this->configuration->setAsBoolean('sendCancelationDeadlineReminder', false);

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForCanceledEventAndOptionEnabledSendsNoReminder()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED,
                ]
            )
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForConfirmedEventAndOptionEnabledSendsNoReminder()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
                ]
            )
        );

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();
    }

    /*
     * Tests concerning the reminders content
     *
     * * sender and recipients
     */

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsEmailWithOrganizerAsRecipient()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            'MrTest@example.com',
            $this->email->getTo()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsEmailWithTypo3DefaultFromAddressAsSender()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            $defaultMailFromAddress,
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithTwoOrganizersSendsEmailWithTypo3DefaultFromAddressAsSender()
    {
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
        $this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, ['organizers' => 2]);

        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            $defaultMailFromAddress,
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithTwoOrganizersSendsEmailWithFirstOrganizerAsReplyTo()
    {
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
        $this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, ['organizers' => 2]);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            ['MrTest@example.com' => 'Mr. Test'],
            $this->email->getReplyTo()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersWithoutTypo3DefaultFromAddressSendsEmailWithOrganizerAsSender()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            'MrTest@example.com',
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithTwoOrganizersWithoutTypo3DefaultFromAddressSendsEmailWithFirstOrganizerAsSender()
    {
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
        $this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, ['organizers' => 2]);

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            'MrTest@example.com',
            $this->email->getFrom()
        );
    }

    // attached CSV

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithNoAttendancesAndAttachCsvFileTrueNotAttachesRegistrationsCsv()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);

        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            [],
            $this->email->getChildren()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithAttendancesAndAttachCsvFileTrueAttachesRegistrationsCsv()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);

        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->email->expects(self::once())->method('send');

        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            'registrations.csv',
            $this->getFirstEmailAttachment()->getFilename()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithAttendancesAndAttachCsvFileFalseNotAttachesRegistrationsCsv()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', false);
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            [],
            $this->email->getChildren()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsEmailWithCsvFileWhichContainsRegistration()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            "test registration\r\n",
            $this->getFirstEmailAttachment()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsEmailWithCsvFileWithOfFrontEndUserData()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);
        $this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'email');

        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser('', ['email' => 'foo@bar.com']),
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            'foo@bar.com',
            $this->getFirstEmailAttachment()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForShowAttendancesOnQueueInEmailCsvSendsEmailWithRegistrationsOnQueue()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'real registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'on queue',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'registration_queue' => 1,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            'on queue',
            $this->getFirstEmailAttachment()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForShowAttendancesOnQueueFalseSendsWithCsvFileWithoutQueueAttendances()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', false);

        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'real registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'on queue',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'registration_queue' => 1,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringNotContainsString(
            'on queue',
            $this->getFirstEmailAttachment()->getBody()
        );
    }

    // customized subject

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithSubjectWithEventTitle()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
                'title' => 'test event',
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            'test event',
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithSubjectWithDaysUntilBeginDate()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            '2',
            $this->email->getSubject()
        );
    }

    // customized message

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithOrganizerName()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            'Mr. Test',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithEventTitle()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
                'title' => 'test event',
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            'test event',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithEventUid()
    {
        $uid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            (string)$uid,
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithDaysUntilBeginDate()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            '2',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithEventsBeginDate()
    {
        $this->addSpeaker(
            $this->createSeminarWithOrganizer(
                [
                    'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                    'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
                ]
            )
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendCancellationDeadlineReminders();

        self::assertContains(
            strftime(
                $this->configuration->getAsString('dateFormatYMD'),
                $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY
            ),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithNoRegistrationSendsReminderWithNumberOfRegistrations()
    {
        $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            '0',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithOneRegistrationsSendsReminderWithNumberOfRegistrations()
    {
        $eventUid = $this->createSeminarWithOrganizer(
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertStringContainsString(
            '1',
            $this->email->getBody()
        );
    }

    /**
     * Tests for the automatic status change
     */

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForNoEventsForStatusChangeNotRequestsStatusChange()
    {
        $events = new Collection();
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->expects(self::never())->method('updateStatusAndSave');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForNoEventsForStatusChangeSendsNoEmail()
    {
        $events = new Collection();
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->emailService->expects(self::never())->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventForStatusChangeRequestsStatusChangeWithThatEvent()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->expects(self::once())
            ->method('updateStatusAndSave')->with($event)
            ->willReturn(false);

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithNoStatusChangeNeededNotSendsEmail()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(false);

        $this->emailService->expects(self::never())->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithConfirmedStatusChangeSendsEmail()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $event->confirm();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with($event, self::anything(), self::anything());

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithConfirmedStatusChangeSendsEmailWithConfirmSubject()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $event->confirm();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $emailSubject = $this->languageService->getLL('email-event-confirmed-subject');
        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with(self::anything(), $emailSubject, self::anything());

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithConfirmedStatusChangeSendsEmailWithConfirmBody()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $event->confirm();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $emailBody = $this->languageService->getLL('email-event-confirmed-body');
        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with(self::anything(), self::anything(), $emailBody);

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForTwoEventsWithConfirmedStatusChangeSendsTwoEmails()
    {
        $events = new Collection();
        $event1 = new \Tx_Seminars_Model_Event();
        $event1->confirm();
        $events->add($event1);
        $event2 = new \Tx_Seminars_Model_Event();
        $event2->confirm();
        $events->add($event2);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $this->emailService->expects(self::exactly(2))->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithCanceledStatusChangeSendsEmail()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $event->cancel();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with($event, self::anything(), self::anything());

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithCanceledStatusChangeSendsEmailWithCancelSubject()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $event->cancel();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $emailSubject = $this->languageService->getLL('email-event-canceled-subject');
        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with(self::anything(), $emailSubject, self::anything());

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithCanceledStatusChangeSendsEmailWithCancelBody()
    {
        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $event->cancel();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $emailBody = $this->languageService->getLL('email-event-canceled-body');
        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with(self::anything(), self::anything(), $emailBody);

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForTwoEventsWithCanceledStatusChangeSendsTwoEmails()
    {
        $events = new Collection();
        $event1 = new \Tx_Seminars_Model_Event();
        $event1->cancel();
        $events->add($event1);
        $event2 = new \Tx_Seminars_Model_Event();
        $event2->cancel();
        $events->add($event2);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $this->emailService->expects(self::exactly(2))->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithPlannedStatusChangeThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $events = new Collection();
        $event = new \Tx_Seminars_Model_Event();
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);
        $events->add($event);
        $this->eventMapper->expects(self::once())->method(
            'findForAutomaticStatusChange'
        )->willReturn($events);

        $this->eventStatusService->method('updateStatusAndSave')->willReturn(true);

        $this->subject->automaticallyChangeEventStatuses();
    }

    private function getConnectionForTable(string $table): Connection
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getConnectionForTable($table);
    }
}
