<?php
namespace OliverKlee\Seminars\Tests\Unit\SchedulerTasks;

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
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;
use OliverKlee\Seminars\SchedulerTasks\MailNotifier;
use OliverKlee\Seminars\Service\EmailService;
use OliverKlee\Seminars\Service\EventStatusService;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MailNotifierTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var MailNotifier|\PHPUnit_Framework_MockObject_MockObject|\Tx_Phpunit_Interface_AccessibleObject
     */
    protected $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    protected $configuration = null;

    /**
     * @var \Tx_Oelib_EmailCollector
     */
    protected $mailer = null;

    /**
     * @var EventStatusService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventStatusService = null;

    /**
     * @var EmailService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailService = null;

    /**
     * @var \Tx_Seminars_Mapper_Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMapper = null;

    /**
     * @var \Tx_Seminars_EmailSalutation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailSalutation = null;

    /**
     * @var LanguageService
     */
    private $languageBackup = null;

    /**
     * @var LanguageService
     */
    private $languageService = null;

    /**
     * @var RegistrationDigest|ObjectProphecy
     */
    private $registrationDigestProphecy = null;

    /**
     * @var RegistrationDigest|ProphecySubjectInterface
     */
    private $registrationDigest = null;

    protected function setUp()
    {
        $this->languageBackup = isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : null;
        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            self::markTestSkipped('This tests needs the scheduler extension.');
        }

        $this->languageService = new LanguageService();
        $this->languageService->init('default');
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $GLOBALS['LANG'] = $this->languageService;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        \Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin', new \Tx_Oelib_Configuration());
        $this->configuration = new \Tx_Oelib_Configuration();
        $this->configuration->setData([
            'sendEventTakesPlaceReminderDaysBeforeBeginDate' => 2,
            'sendCancelationDeadlineReminder' => true,
            'filenameForRegistrationsCsv' => 'registrations.csv',
            'dateFormatYMD' => '%d.%m.%Y',
            'fieldsFromAttendanceForEmailCsv' => 'title',
            'showAttendancesOnRegistrationQueueInEmailCsv' => true,
        ]);
        \Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

        $this->subject = $this->getAccessibleMock(MailNotifier::class, ['dummy'], [], '', false);

        $configurationPageUid = $this->testingFramework->createFrontEndPage();
        $this->subject->setConfigurationPageUid($configurationPageUid);

        $this->eventStatusService = $this->getMock(EventStatusService::class);
        $this->subject->_set('eventStatusService', $this->eventStatusService);

        $this->emailService = $this->getMock(EmailService::class);
        $this->subject->_set('emailService', $this->emailService);

        $this->eventMapper = $this->getMock(\Tx_Seminars_Mapper_Event::class);
        $this->subject->_set('eventMapper', $this->eventMapper);

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();
        $this->subject->_set('mailer', $this->mailer);

        $this->emailSalutation = $this->getMock(\Tx_Seminars_EmailSalutation::class);
        $this->subject->_set('emailSalutation', $this->emailSalutation);

        $this->registrationDigestProphecy = $this->prophesize(RegistrationDigest::class);
        $this->registrationDigest = $this->registrationDigestProphecy->reveal();
        $this->subject->_set('registrationDigest', $this->registrationDigest);
    }

    protected function tearDown()
    {
        if ($this->testingFramework !== null) {
            $this->testingFramework->cleanUp();
        }

        $GLOBALS['LANG'] = $this->languageBackup;
        $this->languageBackup = null;
    }

    /*
     * Utility functions
     */

    /**
     * Creates a seminar record and an organizer record and the relation
     * between them.
     *
     * @param array $additionalSeminarData additional data for the seminar record, may be empty
     *
     * @return int UID of the added event, will be > 0
     */
    private function createSeminarWithOrganizer(array $additionalSeminarData = [])
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
    private function addSpeaker($eventUid)
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
    protected function getFirstEmailAttachment()
    {
        $children = $this->mailer->getFirstSentEmail()->getChildren();
        return $children[0];
    }

    /*
     * Tests for the utility functions
     */

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesSeminarRecord()
    {
        $this->createSeminarWithOrganizer();

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_seminars', '1=1')
        );
    }

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesSeminarRecordWithAdditionalData()
    {
        $this->createSeminarWithOrganizer(['title' => 'foo']);

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_seminars', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesOrganizerRecord()
    {
        $this->createSeminarWithOrganizer();

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_organizers', '1=1')
        );
    }

    /**
     * @test
     */
    public function createSeminarWithOrganizerCreatesRealtionBetweenSeminarAndOrganizer()
    {
        $this->createSeminarWithOrganizer();

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_seminars_organizers_mm', '1=1')
        );
    }

    /**
     * @test
     */
    public function addSpeakerCreatesSpeakerRecord()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer());

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_speakers', '1=1')
        );
    }

    /**
     * @test
     */
    public function addSpeakerCreatesSpeakerRelation()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer());

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_seminars_speakers_mm', '1=1')
        );
    }

    /**
     * @test
     */
    public function addSpeakerSetsNumberOfSpeakersToOneForTheSeminarWithTheProvidedUid()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer());

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_seminars', 'speakers = 1')
        );
    }

    /*
     * Basic tests
     */

    /**
     * @test
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
        $subject = new MailNotifier();

        $result = $subject->execute();

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
        /** @var MailNotifier|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses'],
            [],
            '',
            false
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
        /** @var MailNotifier|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            MailNotifier::class,
            ['sendEventTakesPlaceReminders', 'sendCancellationDeadlineReminders', 'automaticallyChangeEventStatuses'],
            [],
            '',
            false
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

        /** @var MailNotifier|\PHPUnit_Framework_MockObject_MockObject|\Tx_Phpunit_Interface_AccessibleObject $subject */
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

        $this->registrationDigestProphecy->execute()->shouldHaveBeenCalled();
    }

    /*
     * Tests concerning sendEventTakesPlaceReminders
     */

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameSendsReminder()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            1,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceSubject()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = \Tx_Oelib_BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $subject = $this->languageService->getLL('email_eventTakesPlaceReminderSubject');
        $subject = str_replace('%event', '', $subject);
        $subject = str_replace('%days', 2, $subject);

        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            $subject,
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceMessage()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = \Tx_Oelib_BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $message = $this->languageService->getLL('email_eventTakesPlaceReminder');
        $message = str_replace('%event', '', $message);
        $message = str_replace('%organizer', 'Mr. Test', $message);

        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            substr($message, 0, strpos($message, '%') - 1),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForTwoConfirmedEventsWithinConfiguredTimeFrameSendsTwoReminders()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            2,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventWithTwoOrganizersAndWithinConfiguredTimeFrameSendsTwoReminders()
    {
        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
        $this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, ['organizers' => 2]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            2,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersSetsSentFlagInTheDatabaseWhenReminderWasSent()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_seminars', 'event_takes_place_reminder_sent = 1'
            )
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameAndReminderSentFlagTrueSendsNoReminder()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            'event_takes_place_reminder_sent' => 1,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventWithPassedBeginDateSendsNoReminder()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventBeginningLaterThanConfiguredTimeFrameSendsNoReminder()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * \Tx_Oelib_Time::SECONDS_PER_DAY),
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForConfirmedEventAndNoTimeFrameConfiguredSendsNoReminder()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $this->configuration->setAsInteger('sendEventTakesPlaceReminderDaysBeforeBeginDate', 0);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForCanceledEventWithinConfiguredTimeFrameSendsNoReminder()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendEventTakesPlaceRemindersForPlannedEventWithinConfiguredTimeFrameSendsNoReminder()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /*
     * Tests concerning sendCancellationDeadlineReminders
     */

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventAndOptionEnabledSendsReminder()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertSame(
            1,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineSubject()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = \Tx_Oelib_BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $subject = $this->languageService->getLL('email_cancelationDeadlineReminderSubject');
        $subject = str_replace('%event', '', $subject);

        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertContains(
            $subject,
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineMessage()
    {
        /** @var \Tx_Seminars_Model_BackEndUser $user */
        $user = \Tx_Oelib_BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_BackEndUser::class);
        $this->languageService->lang = $user->getLanguage();
        $this->languageService->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        $message = $this->languageService->getLL('email_cancelationDeadlineReminder');
        $message = str_replace('%event', '', $message);
        $message = str_replace('%organizer', 'Mr. Test', $message);

        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertContains(
            substr($message, 0, strpos($message, '%') - 1),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForTwoPlannedEventsAndOptionEnabledSendsTwoReminders()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertSame(
            2,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventWithTwoOrganizersAndOptionEnabledSendsTwoReminders()
    {
        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]);
        $this->addSpeaker($eventUid);
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars', $eventUid, ['organizers' => 2]
        );

        $this->subject->sendCancellationDeadlineReminders();

        self::assertSame(
            2,
            $this->mailer->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersSetsFlagInTheDatabaseWhenReminderWasSent()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertTrue(
            $this->testingFramework->existsRecord('tx_seminars_seminars', 'cancelation_deadline_reminder_sent = 1')
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventAndOptionEnabledAndReminderSentFlagTrueSendsNoReminder()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
            'cancelation_deadline_reminder_sent' => 1,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventWithPassedBeginDateSendsNoReminder()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventWithSpeakersDeadlineNotYetReachedSendsNoReminder()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * \Tx_Oelib_Time::SECONDS_PER_DAY),
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForPlannedEventAndOptionDisabledSendsNoReminder()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));
        $this->configuration->setAsBoolean('sendCancelationDeadlineReminder', false);

        $this->subject->sendCancellationDeadlineReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForCanceledEventAndOptionEnabledSendsNoReminder()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendCancellationDeadlineRemindersForConfirmedEventAndOptionEnabledSendsNoReminder()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertNull(
            $this->mailer->getFirstSentEmail()
        );
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
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            'MrTest@example.com',
            $this->mailer->getFirstSentEmail()->getTo()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsEmailWithOrganizerAsSender()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            'MrTest@example.com',
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithTwoOrganizersSendsEmailWithFirstOrganizerAsSender()
    {
        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'foo', 'email' => 'foo@example.com']
        );
        $this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
        $this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, ['organizers' => 2]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertArrayHasKey(
            'MrTest@example.com',
            $this->mailer->getFirstSentEmail()->getFrom()
        );
    }

    /*
     * * attached CSV
     */

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithNoAttendancesAndAttachCsvFileTrueNotAttachesRegistrationsCsv()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);

        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            [],
            $this->mailer->getFirstSentEmail()->getChildren()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithAttendancesAndAttachCsvFileTrueAttachesRegistrationsCsv()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);

        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

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
        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->sendEventTakesPlaceReminders();

        self::assertSame(
            [],
            $this->mailer->getFirstSentEmail()->getChildren()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsEmailWithCsvFileWhichContainsRegistration()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);
        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            'test registration' . CRLF,
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

        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser('', ['email' => 'foo@bar.com']),
            ]
        );

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            'foo@bar.com',
            $this->getFirstEmailAttachment()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForShowAttendancesOnQueueInEmailCsvSendsEmailWithCsvWithRegistrationsOnQueue()
    {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);
        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'real registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'on queue',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'registration_queue' => 1,
            ]
        );

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            'on queue',
            $this->getFirstEmailAttachment()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForShowAttendancesOnQueueInEmailCsvFalseSendsEmailWithCsvFileWhichDoesNotContainDataOfAttendanceOnQueue(
    ) {
        $this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', true);
        $this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', false);

        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'real registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'on queue',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'registration_queue' => 1,
            ]
        );

        $this->subject->sendEventTakesPlaceReminders();

        self::assertNotContains(
            'on queue',
            $this->getFirstEmailAttachment()->getBody()
        );
    }

    /*
     * * customized subject
     */

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithSubjectWithEventTitle()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            'title' => 'test event',
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            'test event',
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithSubjectWithDaysUntilBeginDate()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            '2',
            $this->mailer->getFirstSentEmail()->getSubject()
        );
    }

    /*
     * * customized message
     */

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithOrganizerName()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            'Mr. Test',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithEventTitle()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            'title' => 'test event',
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            'test event',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithEventUid()
    {
        $uid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            (string)$uid,
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithDaysUntilBeginDate()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            '2',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersSendsReminderWithMessageWithEventsBeginDate()
    {
        $this->addSpeaker($this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED,
        ]));

        $this->subject->sendCancellationDeadlineReminders();

        self::assertContains(
            strftime(
                $this->configuration->getAsString('dateFormatYMD'),
                $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY
            ),
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithNoRegistrationSendsReminderWithMessageWithNumberOfRegistrations()
    {
        $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            '0',
            $this->mailer->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendRemindersToOrganizersForEventWithOneRegistrationsSendsReminderWithMessageWithNumberOfRegistrations()
    {
        $eventUid = $this->createSeminarWithOrganizer([
            'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
        ]);
        $this->testingFramework->createRecord(
            'tx_seminars_attendances', [
                'title' => 'test registration',
                'seminar' => $eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->sendEventTakesPlaceReminders();

        self::assertContains(
            '1',
            $this->mailer->getFirstSentEmail()->getBody()
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
        $events = new \Tx_Oelib_List();
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::never())->method('updateStatusAndSave');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForNoEventsForStatusChangeSendsNoEmail()
    {
        $events = new \Tx_Oelib_List();
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->emailService->expects(self::never())->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventForStatusChangeRequestsStatusChangeWithThatEvent()
    {
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::once())
            ->method('updateStatusAndSave')->with($event)
            ->will(self::returnValue(false));

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithNoStatusChangeNeededNotSendsEmail()
    {
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(false));

        $this->emailService->expects(self::never())->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithConfirmedStatusChangeSendsEmail()
    {
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $event->confirm();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with($event, self::anything(), self::anything());

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithConfirmedStatusChangeSendsEmailWithConfirmSubject()
    {
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $event->confirm();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

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
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $event->confirm();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

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
        $events = new \Tx_Oelib_List();
        $event1 = new \Tx_Seminars_Model_Event();
        $event1->confirm();
        $events->add($event1);
        $event2 = new \Tx_Seminars_Model_Event();
        $event2->confirm();
        $events->add($event2);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

        $this->emailService->expects(self::exactly(2))->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithCanceledStatusChangeSendsEmail()
    {
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $event->cancel();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

        $this->emailService->expects(self::once())->method('sendEmailToAttendees')
            ->with($event, self::anything(), self::anything());

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     */
    public function automaticallyChangeEventStatusesForOneEventWithCanceledStatusChangeSendsEmailWithCancelSubject()
    {
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $event->cancel();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

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
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $event->cancel();
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

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
        $events = new \Tx_Oelib_List();
        $event1 = new \Tx_Seminars_Model_Event();
        $event1->cancel();
        $events->add($event1);
        $event2 = new \Tx_Seminars_Model_Event();
        $event2->cancel();
        $events->add($event2);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

        $this->emailService->expects(self::exactly(2))->method('sendEmailToAttendees');

        $this->subject->automaticallyChangeEventStatuses();
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function automaticallyChangeEventStatusesForOneEventWithPlannedStatusChangeThrowsException()
    {
        $events = new \Tx_Oelib_List();
        $event = new \Tx_Seminars_Model_Event();
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);
        $events->add($event);
        $this->eventMapper->expects(self::once())->method('findForAutomaticStatusChange')->will(self::returnValue($events));

        $this->eventStatusService->expects(self::any())->method('updateStatusAndSave')->will(self::returnValue(true));

        $this->subject->automaticallyChangeEventStatuses();
    }
}
