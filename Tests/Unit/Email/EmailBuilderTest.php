<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Email\EmailBuilder;
use OliverKlee\Seminars\Tests\Unit\Email\Fixtures\TestingMailRole;
use Symfony\Component\Mime\Part\DataPart;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * @covers \OliverKlee\Seminars\Email\EmailBuilder
 */
final class EmailBuilderTest extends UnitTestCase
{
    /**
     * @var EmailBuilder
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new EmailBuilder();
    }

    private function runInV9Only(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 10) {
            self::markTestSkipped('This test is intended for V9 only.');
        }
    }

    private function runInV10AndHigherOnly(): void
    {
        if ((new Typo3Version())->getMajorVersion() <= 9) {
            self::markTestSkipped('This test is intended for V10 and higher only.');
        }
    }

    /**
     * @test
     */
    public function buildReturnsMailMessage(): void
    {
        self::assertInstanceOf(MailMessage::class, $this->subject->build());
    }

    /**
     * @test
     */
    public function buildCalledMultipleTimesAlwaysReturnsTheSameInstance(): void
    {
        self::assertSame($this->subject->build(), $this->subject->build());
    }

    /**
     * @test
     */
    public function buildOnDifferentBuilderInstancesReturnDifferentMailMessageInstances(): void
    {
        $builder1 = new EmailBuilder();
        $builder2 = new EmailBuilder();

        self::assertNotSame($builder1->build(), $builder2->build());
    }

    /**
     * @test
     */
    public function subjectUsesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->subject('heyho'));
    }

    /**
     * @test
     */
    public function subjectSetsSubjectInV9(): void
    {
        $this->runInV9Only();

        $emailSubject = 'Good news!';
        $this->subject->subject($emailSubject);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        self::assertSame($emailSubject, $email->getSubject());
    }

    /**
     * @test
     */
    public function subjectSetsSubjectInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $emailSubject = 'Good news!';
        $this->subject->subject($emailSubject);

        $email = $this->subject->build();

        self::assertSame($emailSubject, $email->getSubject());
    }

    /**
     * @test
     */
    public function textUsesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->text('heyho'));
    }

    /**
     * @test
     */
    public function textSetsPlainTextBodyInV9(): void
    {
        $this->runInV9Only();

        $body = 'Good news!';
        $this->subject->text($body);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        self::assertSame($body, $email->getBody());
    }

    /**
     * @test
     */
    public function textSetsPlainTextBodyInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $body = 'Good news!';
        $this->subject->text($body);

        $email = $this->subject->build();

        self::assertSame($body, $email->getTextBody());
    }

    /**
     * @test
     */
    public function htmlUsesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->html('heyho'));
    }

    /**
     * @test
     */
    public function htmlSetsHtmlBodyInV9(): void
    {
        $this->runInV9Only();

        $body = 'Good news!';
        $this->subject->html($body);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        $htmlBody = $this->filterSwiftMailerEmailAttachmentsByType($email, 'text/html')[0]->getBody();
        self::assertSame($body, $htmlBody);
    }

    /**
     * @test
     */
    public function htmlSetsHtmlBodyInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $body = 'Good news!';
        $this->subject->html($body);

        $email = $this->subject->build();

        self::assertSame($body, $email->getHtmlBody());
    }

    /**
     * Returns the attachments of $email that have a content type that contains $contentType.
     *
     * Example: a $contentType of "text/calendar" will also find attachments that have 'text/calendar; charset="utf-8"'
     * as the content type.
     *
     * @return array<int, \Swift_Mime_MimeEntity>
     */
    private function filterSwiftMailerEmailAttachmentsByType(\Swift_Message $email, string $contentType): array
    {
        $matches = [];

        foreach ($this->getAttachmentsForSwiftMailerMessage($email) as $attachment) {
            if (\strpos($attachment->getContentType(), $contentType) !== false) {
                $matches[] = $attachment;
            }
        }

        return $matches;
    }

    /**
     * @return array<int, \Swift_Mime_MimeEntity>
     */
    private function getAttachmentsForSwiftMailerMessage(\Swift_Message $email): array
    {
        return $email->getChildren();
    }

    /**
     * @test
     */
    public function toUsesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->to(new TestingMailRole('max', 'max@example.com')));
    }

    /**
     * @test
     */
    public function toCanSetOneRecipientInV9(): void
    {
        $this->runInV9Only();

        $to = new TestingMailRole('max', 'max@example.com');
        $this->subject->to($to);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        self::assertSame([$to->getEmailAddress() => $to->getName()], $email->getTo());
    }

    /**
     * @test
     */
    public function toCanSetOneRecipientInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $to = new TestingMailRole('max', 'max@example.com');
        $this->subject->to($to);

        $email = $this->subject->build();

        self::assertSame($to->getEmailAddress(), $email->getTo()[0]->getAddress());
        self::assertSame($to->getName(), $email->getTo()[0]->getName());
    }

    /**
     * @test
     */
    public function toCanSetTwoRecipientsInV9(): void
    {
        $this->runInV9Only();

        $to1 = new TestingMailRole('max', 'max@example.com');
        $to2 = new TestingMailRole('ben', 'ben@example.com');
        $this->subject->to($to1, $to2);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        self::assertSame(
            [
                $to1->getEmailAddress() => $to1->getName(),
                $to2->getEmailAddress() => $to2->getName(),
            ],
            $email->getTo()
        );
    }

    /**
     * @test
     */
    public function toCanSetTwoRecipientsInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $to1 = new TestingMailRole('max', 'max@example.com');
        $to2 = new TestingMailRole('ben', 'ben@example.com');
        $this->subject->to($to1, $to2);

        $email = $this->subject->build();

        self::assertSame($to1->getEmailAddress(), $email->getTo()[0]->getAddress());
        self::assertSame($to1->getName(), $email->getTo()[0]->getName());
        self::assertSame($to2->getEmailAddress(), $email->getTo()[1]->getAddress());
        self::assertSame($to2->getName(), $email->getTo()[1]->getName());
    }

    /**
     * @test
     */
    public function fromUsesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->from(new TestingMailRole('max', 'max@example.com')));
    }

    /**
     * @test
     */
    public function fromCanSetFromInV9(): void
    {
        $this->runInV9Only();

        $from = new TestingMailRole('max', 'max@example.com');
        $this->subject->from($from);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        self::assertSame([$from->getEmailAddress() => $from->getName()], $email->getFrom());
    }

    /**
     * @test
     */
    public function fromCanSetFromInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $from = new TestingMailRole('max', 'max@example.com');
        $this->subject->from($from);

        $email = $this->subject->build();

        self::assertSame($from->getEmailAddress(), $email->getFrom()[0]->getAddress());
        self::assertSame($from->getName(), $email->getFrom()[0]->getName());
    }

    /**
     * @test
     */
    public function replyToUsesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->replyTo(new TestingMailRole('max', 'max@example.com')));
    }

    /**
     * @test
     */
    public function replyToCanSetOneRecipientInV9(): void
    {
        $this->runInV9Only();

        $replyTo = new TestingMailRole('max', 'max@example.com');
        $this->subject->replyTo($replyTo);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        /** @var array<string, string> $actualReplyTo */
        $actualReplyTo = $email->getReplyTo();
        self::assertSame([$replyTo->getEmailAddress() => $replyTo->getName()], $actualReplyTo);
    }

    /**
     * @test
     */
    public function replyToCanSetOneRecipientInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $replyTo = new TestingMailRole('max', 'max@example.com');
        $this->subject->replyTo($replyTo);

        $email = $this->subject->build();

        self::assertSame($replyTo->getEmailAddress(), $email->getReplyTo()[0]->getAddress());
        self::assertSame($replyTo->getName(), $email->getReplyTo()[0]->getName());
    }

    /**
     * @test
     */
    public function replyToCanSetTwoRecipientsInV9(): void
    {
        $this->runInV9Only();

        $replyTo1 = new TestingMailRole('max', 'max@example.com');
        $replyTo2 = new TestingMailRole('ben', 'ben@example.com');
        $this->subject->replyTo($replyTo1, $replyTo2);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        /** @var array<string, string> $actualReplyTo */
        $actualReplyTo = $email->getReplyTo();
        self::assertSame(
            [
                $replyTo1->getEmailAddress() => $replyTo1->getName(),
                $replyTo2->getEmailAddress() => $replyTo2->getName(),
            ],
            $actualReplyTo
        );
    }

    /**
     * @test
     */
    public function replyToCanSetTwoRecipientsInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $replyTo1 = new TestingMailRole('max', 'max@example.com');
        $replyTo2 = new TestingMailRole('ben', 'ben@example.com');
        $this->subject->replyTo($replyTo1, $replyTo2);

        $email = $this->subject->build();

        self::assertSame($replyTo1->getEmailAddress(), $email->getReplyTo()[0]->getAddress());
        self::assertSame($replyTo1->getName(), $email->getReplyTo()[0]->getName());
        self::assertSame($replyTo2->getEmailAddress(), $email->getReplyTo()[1]->getAddress());
        self::assertSame($replyTo2->getName(), $email->getReplyTo()[1]->getName());
    }

    /**
     * @test
     */
    public function attachUsesFluentInterface(): void
    {
        self::assertSame($this->subject, $this->subject->attach('There is no spoon.', 'text/plain'));
    }

    /**
     * @test
     */
    public function emailInitiallyHasNoAttachmentsInV9(): void
    {
        $this->runInV9Only();

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        self::assertSame([], $this->getAttachmentsForSwiftMailerMessage($email));
    }

    /**
     * @test
     */
    public function emailInitiallyHasNoAttachmentsInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $email = $this->subject->build();

        self::assertSame([], $email->getAttachments());
    }

    /**
     * @test
     */
    public function attachCanAddOneAttachmentInV9(): void
    {
        $this->runInV9Only();

        $body = 'The cake is a lie';
        $contentType = 'text/plain';
        $fileName = 'message.txt';
        $this->subject->attach($body, $contentType, $fileName);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        $attachments = $this->getAttachmentsForSwiftMailerMessage($email);
        self::assertContainsOnlyInstancesOf(\Swift_Mime_MimeEntity::class, $attachments);
        $firstAttachment = $attachments[0];
        self::assertSame($body, $firstAttachment->getBody());
        self::assertSame($contentType, $firstAttachment->getContentType());
    }

    /**
     * @test
     */
    public function attachUsesApplicationOctetStreamAsDefaultForContentTypeInV9(): void
    {
        $this->runInV9Only();

        $body = 'The cake is a lie';
        $this->subject->attach($body);

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        $attachments = $this->getAttachmentsForSwiftMailerMessage($email);
        self::assertSame('application/octet-stream', $attachments[0]->getContentType());
    }

    /**
     * @test
     */
    public function attachCanAddOneAttachmentInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $body = 'The cake is a lie';
        $contentType = 'text/plain';
        $fileName = 'message.txt';
        $this->subject->attach($body, $contentType, $fileName);

        $email = $this->subject->build();

        $attachments = $email->getAttachments();
        self::assertContainsOnlyInstancesOf(DataPart::class, $attachments);
        $firstAttachment = $attachments[0];
        self::assertSame($body, $firstAttachment->getBody());
        self::assertSame('text', $firstAttachment->getMediaType());
        self::assertSame('plain', $firstAttachment->getMediaSubtype());
        self::assertStringContainsString(
            $fileName,
            $firstAttachment->getPreparedHeaders()->get('content-disposition')->toString()
        );
    }

    /**
     * @test
     */
    public function attachUsesApplicationOctetStreamAsDefaultForContentTypeInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $body = 'The cake is a lie';
        $this->subject->attach($body);

        $email = $this->subject->build();

        $attachments = $email->getAttachments();
        self::assertContainsOnlyInstancesOf(DataPart::class, $attachments);
        $firstAttachment = $attachments[0];
        self::assertSame('application', $firstAttachment->getMediaType());
        self::assertSame('octet-stream', $firstAttachment->getMediaSubtype());
    }

    /**
     * @test
     */
    public function attachCanAddTwoAttachmentsInV9(): void
    {
        $this->runInV9Only();

        $this->subject->attach('The cake is a lie');
        $this->subject->attach('There is no spoon.');

        /** @var \Swift_Message $email */
        $email = $this->subject->build();

        $attachments = $this->getAttachmentsForSwiftMailerMessage($email);
        self::assertCount(2, $attachments);
    }

    /**
     * @test
     */
    public function attachCanAddTwoAttachmentsInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $this->subject->attach('The cake is a lie');
        $this->subject->attach('There is no spoon.');

        $email = $this->subject->build();

        $attachments = $email->getAttachments();
        self::assertCount(2, $attachments);
    }
}
