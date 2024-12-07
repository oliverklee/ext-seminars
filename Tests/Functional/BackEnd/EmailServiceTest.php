<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use OliverKlee\Seminars\BackEnd\EmailService;
use OliverKlee\Seminars\Tests\Support\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\BackEnd\EmailService
 */
final class EmailServiceTest extends FunctionalTestCase
{
    use LanguageHelper;
    use EmailTrait;
    use MakeInstanceTrait;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected array $configurationToUseInTestInstance = [
        'MAIL' => [
            'defaultMailFromAddress' => 'system-foo@example.com',
            'defaultMailFromName' => 'Mr. Default',
        ],
    ];

    private EmailService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/BackEndUser.csv');
        $this->setUpBackendUser(1);
        $this->initializeBackEndLanguage();

        $this->email = $this->createEmailMock();

        $this->subject = new EmailService();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesForTwoRegistrationsSendsTwoEmails(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $this->subject->sendEmailToAttendees(2);
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithNameOfRegisteredUserInSalutationMarker(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $messageBody = '%salutation';
        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );
        $this->subject->sendEmailToAttendees(1);

        self::assertStringContainsString('Joe Johnson', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesTypo3DefaultFromAddressAsSender(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->subject->sendEmailToAttendees(2);

        self::assertArrayHasKey('system-foo@example.com', $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesFirstOrganizerAsSender(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL'] = [];

        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->subject->sendEmailToAttendees(2);

        self::assertArrayHasKey('oliver@example.com', $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesEmailUsesFirstOrganizerAsReplyTo(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->subject->sendEmailToAttendees(2);

        self::assertArrayHasKey('oliver@example.com', $this->getReplyToOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesEmailAppendsFirstOrganizerFooterToMessageBody(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $this->subject->sendEmailToAttendees(2);

        self::assertStringContainsString("\n-- \nThe one and only", $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesProvidedEmailSubject(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $emailSubject = 'Thank you for your registration.';
        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => $emailSubject,
                'messageBody' => 'Hello!',
            ]
        );
        $this->subject->sendEmailToAttendees(1);

        self::assertSame($emailSubject, $this->email->getSubject());
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesNotSendsEmailToUserWithoutEmailAddress(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $this->subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'Hello!',
                'messageBody' => 'Hello!',
            ]
        );
        $this->subject->sendEmailToAttendees(4);
    }
}
