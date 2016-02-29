<?php
namespace OliverKlee\Seminars\Tests\Unit\Service;

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
use OliverKlee\Seminars\Service\EmailService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EmailServiceTest extends \Tx_Phpunit_TestCase
{
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

    protected function setUp()
    {
        $this->languageBackup = isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : null;
        $languageService = new LanguageService();
        $languageService->init('en');
        $GLOBALS['LANG'] = $languageService;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        $this->organizer = new \Tx_Seminars_Model_Organizer();
        $this->organizer->setData(
            ['title' => 'Brain Gourmets', 'email' => 'organizer@example.com', 'email_footer' => 'Best workshops in town!']
        );
        $organizers = new \Tx_Oelib_List();
        $organizers->add($this->organizer);

        $this->event = new \Tx_Seminars_Model_Event();
        $this->event->setData(['registrations' => new \Tx_Oelib_List(), 'organizers' => $organizers]);

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
    public function sendEmailToAttendeesUsesFirstOrganizerAsSender()
    {
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
    public function sendEmailToAttendeesInsertsUserNameIntoMailTextWithSalutationMarker()
    {
        $this->subject->sendEmailToAttendees($this->event, 'Bonjour!', '%salutation The cake is a lie.');

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
}