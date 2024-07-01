<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingEventMailForm;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * @covers \OliverKlee\Seminars\BackEnd\AbstractEventMailForm
 */
final class AbstractEventMailFormTest extends FunctionalTestCase
{
    use LanguageHelper;
    use EmailTrait;
    use MakeInstanceTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var array<string, array<string, non-empty-string>>
     */
    protected $configurationToUseInTestInstance = [
        'MAIL' => [
            'defaultMailFromAddress' => 'system-foo@example.com',
            'defaultMailFromName' => 'Mr. Default',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        $this->email = $this->createEmailMock();
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

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $subject->sendEmailToAttendees();
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesSendsEmailWithNameOfRegisteredUserInSalutationMarker(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $subject = new TestingEventMailForm(1);

        $messageBody = '%salutation';
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );
        $subject->sendEmailToAttendees();

        self::assertStringContainsString('Joe Johnson', $this->email->getTextBody());
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesTypo3DefaultFromAddressAsSender(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
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
        $subject->sendEmailToAttendees();

        self::assertArrayHasKey('system-foo@example.com', $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesUsesFirstOrganizerAsSender(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL'] = [];

        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
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
        $subject->sendEmailToAttendees();

        self::assertArrayHasKey('oliver@example.com', $this->getFromOfEmail($this->email));
    }

    /**
     * @test
     */
    public function sendEmailToAttendeesEmailUsesFirstOrganizerAsReplyTo(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
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
        $subject->sendEmailToAttendees();

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

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->sendEmailToAttendees();

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
        $subject = new TestingEventMailForm(1);
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => $emailSubject,
                'messageBody' => 'Hello!',
            ]
        );
        $subject->sendEmailToAttendees();

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

        $subject = new TestingEventMailForm(4);
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'Hello!',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->sendEmailToAttendees();
    }
}
