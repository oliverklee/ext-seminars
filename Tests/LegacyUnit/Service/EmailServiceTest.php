<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Hooks\Interfaces\AlternativeEmailProcessor;
use OliverKlee\Seminars\Service\EmailService;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EmailServiceTest extends TestCase
{
    /**
     * @var string
     */
    const DATE_FORMAT_YMD = '%d.%m.%Y';

    /**
     * @var string
     */
    const DATE_FORMAT_Y = '%Y';

    /**
     * @var string
     */
    const DATE_FORMAT_M = '%m.';

    /**
     * @var string
     */
    const DATE_FORMAT_MD = '%d.%m.';

    /**
     * @var string
     */
    const DATE_FORMAT_D = '%d.';

    /**
     * @var EmailService
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Oelib_EmailCollector
     */
    private $mailer = null;

    /**
     * @var \Tx_Seminars_Model_Event
     */
    private $event = null;

    /**
     * @var \Tx_Seminars_Model_FrontEndUser
     */
    private $user = null;

    /**
     * @var \Tx_Seminars_Model_Organizer
     */
    private $organizer = null;

    /**
     * @var LanguageService
     */
    private $languageBackup;

    /**
     * @var string[]
     */
    private $mockedClassNames = [];

    protected function setUp()
    {
        Bootstrap::getInstance()->initializeBackendAuthentication();
        $this->languageBackup = $GLOBALS['LANG'] ?? null;
        $languageService = new LanguageService();
        $languageService->init('default');
        $GLOBALS['LANG'] = $languageService;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $configuration = new \Tx_Oelib_Configuration();
        $configuration->setAsString('dateFormatYMD', self::DATE_FORMAT_YMD);
        $configuration->setAsString('dateFormatY', self::DATE_FORMAT_Y);
        $configuration->setAsString('dateFormatM', self::DATE_FORMAT_M);
        $configuration->setAsString('dateFormatMD', self::DATE_FORMAT_MD);
        $configuration->setAsString('dateFormatD', self::DATE_FORMAT_D);

        \Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        $this->organizer = new \Tx_Seminars_Model_Organizer();
        $this->organizer->setData(
            [
                'title' => 'Brain Gourmets',
                'email' => 'organizer@example.com',
                'email_footer' => 'Best workshops in town!',
            ]
        );
        $organizers = new \Tx_Oelib_List();
        $organizers->add($this->organizer);

        $this->event = new \Tx_Seminars_Model_Event();
        $this->event->setData(
            [
                'title' => 'A nice event',
                'begin_date' => mktime(10, 0, 0, 4, 2, 2016),
                'end_date' => mktime(18, 30, 0, 4, 3, 2016),
                'registrations' => new \Tx_Oelib_List(),
                'organizers' => $organizers,
            ]
        );

        $this->user = new \Tx_Seminars_Model_FrontEndUser();
        $this->user->setData(['name' => 'John Doe', 'email' => 'john.doe@example.com']);
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData([]);
        $registration->setFrontEndUser($this->user);
        $this->event->attachRegistration($registration);

        $this->subject = new EmailService();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $GLOBALS['LANG'] = $this->languageBackup;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][AlternativeEmailProcessor::class] = [];
        $this->purgeMockedInstances();
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

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /*
     * Tests for sendEmailToAttendees
     */

    /**
     * @test
     */
    public function sendEmailToAttendeesForEventWithoutRegistrationsNotSendsMail()
    {
        $this->event->setRegistrations(new \Tx_Oelib_List());

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNull($email);
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesTypo3DefaultFromAddressAsSender()
    {
        $defaultMailFromAddress = 'system-foo@example.com';
        $defaultMailFromName = 'Mr. Default';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromName;

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertArrayHasKey(
            $defaultMailFromAddress,
            $email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesFirstOrganizerAsReplyTo()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertSame(
            $this->organizer->getEMailAddress(),
            \key($email->getReplyTo())
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesWithoutTypo3DefaultFromAddressUsesFirstOrganizerAsSender()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertArrayHasKey(
            $this->organizer->getEMailAddress(),
            $email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithProvidedSubject()
    {
        $subject = 'Bonjour!';

        $this->subject->sendEmailToAttendees($this->event, $subject, 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertSame(
            $subject,
            $email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesReplacesEventTitleInSubject()
    {
        $subjectPrefix = 'Event title goes here: ';

        $this->subject->sendEmailToAttendees($this->event, $subjectPrefix . '%eventTitle', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertSame(
            $subjectPrefix . $this->event->getTitle(),
            $email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesReplacesEventDateInSubject()
    {
        $subjectPrefix = 'Event date goes here: ';

        $formattedDate = (new \Tx_Seminars_ViewHelper_DateRange())->render($this->event, '-');

        $this->subject->sendEmailToAttendees($this->event, $subjectPrefix . '%eventDate', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertSame(
            $subjectPrefix . $formattedDate,
            $email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithProvidedBody()
    {
        $body = 'Life is good.';

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', $body);

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertContains(
            $body,
            $email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsToFirstAttendee()
    {
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertSame(
            [$this->user->getEmailAddress() => $this->user->getName()],
            $email->getTo()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForTwoRegistrationsSendsTwoEmails()
    {
        $secondUser = new \Tx_Seminars_Model_FrontEndUser();
        $secondUser->setData(['email' => 'jane@example.com', 'name' => 'Jane Doe']);
        $secondRegistration = new \Tx_Seminars_Model_Registration();
        $secondRegistration->setData([]);
        $secondRegistration->setFrontEndUser($secondUser);
        $this->event->attachRegistration($secondRegistration);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertCount(2, $this->mailer->getSentEmails());
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForRegistrationWithoutUserNotSendsMail()
    {
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData([]);
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);
        $this->event->setRegistrations($registrations);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNull($email);
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForAttendeeWithoutEMailAddressNotSendsMail()
    {
        $this->user->setEmailAddress('');

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNull($email);
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsSalutationIntoMailTextWithSalutationMarker()
    {
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', '%salutation (This was the salutation)');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertContains(
            $this->user->getName(),
            $email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsUserNameIntoMailTextWithUserNameMarker()
    {
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello %userName!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertContains(
            'Hello ' . $this->user->getName() . '!',
            $email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsEventTitleIntoMailTextWithEventTitleMarker()
    {
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Event: %eventTitle');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertContains(
            'Event: ' . $this->event->getTitle(),
            $email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsEventDateIntoMailTextWithEventDateMarker()
    {
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Date: %eventDate');

        $formattedDate = (new \Tx_Seminars_ViewHelper_DateRange())->render($this->event, '-');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertContains(
            'Date: ' . $formattedDate,
            $email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForOrganizerWithoutFooterNotAppendsFooterSeparator()
    {
        $this->organizer->setEMailFooter('');

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertNotContains(
            '-- ',
            $email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForOrganizerWithFooterAppendsFooter()
    {
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        $email = $this->mailer->getFirstSentEmail();
        self::assertNotNull($email);
        self::assertContains(
            LF . '-- ' . LF . $this->organizer->getEMailFooter(),
            $email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesCallsAlternativeEmailProcessorHookWhenRegistered()
    {
        $hook = $this->createMock(AlternativeEmailProcessor::class);
        $hook->expects(self::once())->method('processAttendeeEmail')->with(
            self::isInstanceOf(\Tx_Oelib_Mail::class),
            self::isInstanceOf(\Tx_Seminars_Model_Registration::class)
        );
        $hook->expects(self::never())->method('processOrganizerEmail');
        $hook->expects(self::never())->method('processReminderEmail');
        $hook->expects(self::never())->method('processReviewerEmail');
        $hook->expects(self::never())->method('processAdditionalReviewerEmail');
        $hook->expects(self::never())->method('processAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][AlternativeEmailProcessor::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForTwoRegistrationsCallsAlternativeEmailProcessorHookTwiceWhenRegistered()
    {
        $hook = $this->createMock(AlternativeEmailProcessor::class);
        $hook->expects(self::exactly(2))->method('processAttendeeEmail')->with(
            self::isInstanceOf(\Tx_Oelib_Mail::class),
            self::isInstanceOf(\Tx_Seminars_Model_Registration::class)
        );
        $hook->expects(self::never())->method('processOrganizerEmail');
        $hook->expects(self::never())->method('processReminderEmail');
        $hook->expects(self::never())->method('processReviewerEmail');
        $hook->expects(self::never())->method('processAdditionalReviewerEmail');
        $hook->expects(self::never())->method('processAdditionalEmail');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][AlternativeEmailProcessor::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $secondUser = new \Tx_Seminars_Model_FrontEndUser();
        $secondUser->setData(['email' => 'jane@example.com', 'name' => 'Jane Doe']);
        $secondRegistration = new \Tx_Seminars_Model_Registration();
        $secondRegistration->setData([]);
        $secondRegistration->setFrontEndUser($secondUser);
        $this->event->attachRegistration($secondRegistration);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesDoesntCallAlternativeEmailProcessorHookWhenNotRegistered()
    {
        $hook = $this->createMock(AlternativeEmailProcessor::class);
        $hook->expects(self::never())->method('processAttendeeEmail');
        $hook->expects(self::never())->method('processOrganizerEmail');
        $hook->expects(self::never())->method('processReminderEmail');
        $hook->expects(self::never())->method('processReviewerEmail');
        $hook->expects(self::never())->method('processAdditionalReviewerEmail');
        $hook->expects(self::never())->method('processAdditionalEmail');

        $hookClass = \get_class($hook);
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesDoesNotSendMailViaDefaultMailerWhenAlternativeEmailProcessorHookIsRegistered()
    {
        $hook = $this->createMock(AlternativeEmailProcessor::class);
        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][AlternativeEmailProcessor::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertEquals(0, $this->mailer->getNumberOfSentEmails());
    }
}
