<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Service\EmailService;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EmailServiceTest extends TestCase
{
    use EmailTrait;

    use MakeInstanceTrait;

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
     * @var TestingFramework
     */
    private $testingFramework;

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
     * @var (MockObject&MailMessage)|null
     */
    private $email = null;

    protected function setUp()
    {
        Bootstrap::initializeBackendAuthentication();
        $this->languageBackup = $GLOBALS['LANG'] ?? null;
        $languageService = new LanguageService();
        $languageService->init('default');
        $GLOBALS['LANG'] = $languageService;

        $this->testingFramework = new TestingFramework('tx_seminars');

        $configuration = new Configuration();
        $configuration->setAsString('dateFormatYMD', self::DATE_FORMAT_YMD);
        $configuration->setAsString('dateFormatY', self::DATE_FORMAT_Y);
        $configuration->setAsString('dateFormatM', self::DATE_FORMAT_M);
        $configuration->setAsString('dateFormatMD', self::DATE_FORMAT_MD);
        $configuration->setAsString('dateFormatD', self::DATE_FORMAT_D);

        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        $this->email = $this->createEmailMock();

        $this->organizer = new \Tx_Seminars_Model_Organizer();
        $this->organizer->setData(
            [
                'title' => 'Brain Gourmets',
                'email' => 'organizer@example.com',
                'email_footer' => 'Best workshops in town!',
            ]
        );
        $organizers = new Collection();
        $organizers->add($this->organizer);

        $this->event = new \Tx_Seminars_Model_Event();
        $this->event->setData(
            [
                'title' => 'A nice event',
                'begin_date' => mktime(10, 0, 0, 4, 2, 2016),
                'end_date' => mktime(18, 30, 0, 4, 3, 2016),
                'registrations' => new Collection(),
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
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    // Tests for sendEmailToAttendees

    /**
     * @test
     */
    public function sendEmailToAttendeesForEventWithoutRegistrationsNotSendsMail()
    {
        $this->event->setRegistrations(new Collection());

        $this->email->expects(self::exactly(0))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');
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

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertArrayHasKey(
            $defaultMailFromAddress,
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesFirstOrganizerAsReplyTo()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'system-foo@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = 'Mr. Default';

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertSame(
            $this->organizer->getEMailAddress(),
            \key($this->email->getReplyTo())
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesWithoutTypo3DefaultFromAddressUsesFirstOrganizerAsSender()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertArrayHasKey(
            $this->organizer->getEMailAddress(),
            $this->email->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithProvidedSubject()
    {
        $subject = 'Bonjour!';

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, $subject, 'Hello!');

        self::assertSame(
            $subject,
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesReplacesEventTitleInSubject()
    {
        $subjectPrefix = 'Event title goes here: ';

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, $subjectPrefix . '%eventTitle', 'Hello!');

        self::assertSame(
            $subjectPrefix . $this->event->getTitle(),
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesReplacesEventDateInSubject()
    {
        $subjectPrefix = 'Event date goes here: ';

        $formattedDate = (new \Tx_Seminars_ViewHelper_DateRange())->render($this->event, '-');

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, $subjectPrefix . '%eventDate', 'Hello!');

        self::assertSame(
            $subjectPrefix . $formattedDate,
            $this->email->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithProvidedBody()
    {
        $body = 'Life is good.';

        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->email->expects(self::once())->method('send');
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', $body);

        self::assertStringContainsString(
            $body,
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsToFirstAttendee()
    {
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->email->expects(self::once())->method('send');
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertSame(
            [$this->user->getEmailAddress() => $this->user->getName()],
            $this->email->getTo()
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

        $this->email
            ->expects(self::exactly(2))
            ->method('send')
            ->willReturn(2);
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForRegistrationWithoutUserNotSendsMail()
    {
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData([]);
        $registrations = new Collection();
        $registrations->add($registration);
        $this->event->setRegistrations($registrations);

        $this->email->expects(self::exactly(0))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForAttendeeWithoutEMailAddressNotSendsMail()
    {
        $this->user->setEmailAddress('');

        $this->email->expects(self::exactly(0))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsSalutationIntoMailTextWithSalutationMarker()
    {
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->email->expects(self::once())->method('send');
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', '%salutation (This was the salutation)');

        self::assertStringContainsString(
            $this->user->getName(),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsUserNameIntoMailTextWithUserNameMarker()
    {
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->email->expects(self::once())->method('send');
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello %userName!');

        self::assertStringContainsString(
            'Hello ' . $this->user->getName() . '!',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsEventTitleIntoMailTextWithEventTitleMarker()
    {
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->email->expects(self::once())->method('send');
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Event: %eventTitle');

        self::assertStringContainsString(
            'Event: ' . $this->event->getTitle(),
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesInsertsEventDateIntoMailTextWithEventDateMarker()
    {
        $formattedDate = (new \Tx_Seminars_ViewHelper_DateRange())->render($this->event, '-');

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Date: %eventDate');

        self::assertStringContainsString(
            'Date: ' . $formattedDate,
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForOrganizerWithoutFooterNotAppendsFooterSeparator()
    {
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->organizer->setEMailFooter('');

        $this->email->expects(self::once())->method('send');
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertStringNotContainsString(
            '-- ',
            $this->email->getBody()
        );
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForOrganizerWithFooterAppendsFooter()
    {
        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', 'Hello!');

        self::assertStringContainsString(
            "\n-- \n" . $this->organizer->getEMailFooter(),
            $this->email->getBody()
        );
    }
}
