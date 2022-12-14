<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Email\EmailBuilder;
use OliverKlee\Seminars\Tests\Unit\Email\Fixtures\TestingMailRole;
use Symfony\Component\Mime\Part\DataPart;
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
    public function subjectSetsSubject(): void
    {
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
    public function textSetsPlainTextBody(): void
    {
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
    public function htmlSetsHtmlBody(): void
    {
        $body = 'Good news!';
        $this->subject->html($body);

        $email = $this->subject->build();

        self::assertSame($body, $email->getHtmlBody());
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
    public function toCanSetOneRecipient(): void
    {
        $to = new TestingMailRole('max', 'max@example.com');
        $this->subject->to($to);

        $email = $this->subject->build();

        self::assertSame($to->getEmailAddress(), $email->getTo()[0]->getAddress());
        self::assertSame($to->getName(), $email->getTo()[0]->getName());
    }

    /**
     * @test
     */
    public function toCanSetTwoRecipients(): void
    {
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
    public function fromCanSetFrom(): void
    {
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
    public function replyToCanSetOneRecipient(): void
    {
        $replyTo = new TestingMailRole('max', 'max@example.com');
        $this->subject->replyTo($replyTo);

        $email = $this->subject->build();

        self::assertSame($replyTo->getEmailAddress(), $email->getReplyTo()[0]->getAddress());
        self::assertSame($replyTo->getName(), $email->getReplyTo()[0]->getName());
    }

    /**
     * @test
     */
    public function replyToCanSetTwoRecipients(): void
    {
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
    public function emailInitiallyHasNoAttachments(): void
    {
        $email = $this->subject->build();

        self::assertSame([], $email->getAttachments());
    }

    /**
     * @test
     */
    public function attachCanAddOneAttachment(): void
    {
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
    public function attachUsesApplicationOctetStreamAsDefaultForContentType(): void
    {
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
    public function attachCanAddTwoAttachments(): void
    {
        $this->subject->attach('The cake is a lie');
        $this->subject->attach('There is no spoon.');

        $email = $this->subject->build();

        $attachments = $email->getAttachments();
        self::assertCount(2, $attachments);
    }
}
