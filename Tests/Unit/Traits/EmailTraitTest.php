<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Email\EmailBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mime\Part\DataPart;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * @covers \OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait
 */
final class EmailTraitTest extends UnitTestCase
{
    use EmailTrait;

    /**
     * @test
     */
    public function createEmailMockCreatesMock(): void
    {
        $mock = $this->createEmailMock();

        self::assertInstanceOf(MockObject::class, $mock);
    }

    /**
     * @test
     */
    public function createEmailMockCreatesMailMessage(): void
    {
        $mock = $this->createEmailMock();

        self::assertInstanceOf(MailMessage::class, $mock);
    }

    /**
     * @test
     */
    public function mocksTheSendMethod(): void
    {
        $mock = $this->createEmailMock();
        $mock->expects(self::once())->method('send');

        $mock->send();
    }

    /**
     * @test
     */
    public function mockRemembersTo(): void
    {
        $mock = $this->createEmailMock();
        $mock->setTo('max@example.com', 'Max');

        self::assertSame(['max@example.com' => 'Max'], $this->getToOfEmail($mock));
    }

    /**
     * @test
     */
    public function mockRemembersFrom(): void
    {
        $mock = $this->createEmailMock();
        $mock->setFrom('max@example.com', 'Max');

        self::assertSame(['max@example.com' => 'Max'], $this->getFromOfEmail($mock));
    }

    /**
     * @test
     */
    public function mockRemembersReplyTo(): void
    {
        $mock = $this->createEmailMock();
        $mock->setReplyTo('max@example.com', 'Max');

        self::assertSame(['max@example.com' => 'Max'], $this->getReplyToOfEmail($mock));
    }

    /**
     * @test
     */
    public function mockRemembersSubject(): void
    {
        $subject = 'What is love?';
        $mock = $this->createEmailMock();
        $mock->setSubject($subject);

        self::assertSame($subject, $mock->getSubject());
    }

    /**
     * @test
     */
    public function mockRemembersTextBodyInV10(): void
    {
        $textBody = 'What is love?';
        $mock = $this->createEmailMock();
        $mock->text($textBody);

        self::assertSame($textBody, $mock->getTextBody());
    }

    /**
     * @test
     */
    public function getHtmlBodyOfEmailForEmailWithoutAnyTextOrHtmlBodyReturnsEmptyString(): void
    {
        $email = (new EmailBuilder())->build();

        self::assertSame('', (string)$email->getHtmlBody());
    }

    /**
     * @test
     */
    public function getHtmlBodyOfEmailForEmailWithTextOnlyBodyReturnsEmptyString(): void
    {
        $email = (new EmailBuilder())->text('There is only text.')->build();

        self::assertSame('', (string)$email->getHtmlBody());
    }

    /**
     * @test
     */
    public function getHtmlBodyOfEmailForEmailWithTextBodyAndEmptyHtmlBodyReturnsEmptyString(): void
    {
        $email = (new EmailBuilder())->text('There is only text.')->html('')->build();

        self::assertSame('', (string)$email->getHtmlBody());
    }

    /**
     * @test
     */
    public function getHtmlBodyOfEmailForEmailWithNonEmptyTextAndHtmlBodyReturnsHtmlBody(): void
    {
        $htmlBody = '<p>There also is HTML.</p>';
        $email = (new EmailBuilder())->text('There is some text.')->html($htmlBody)->build();

        self::assertSame($htmlBody, (string)$email->getHtmlBody());
    }

    /**
     * @test
     */
    public function getHtmlBodyOfEmailForEmailWithHtmlBodyOnlyReturnsHtmlBody(): void
    {
        $htmlBody = '<p>There also is HTML.</p>';
        $email = (new EmailBuilder())->html($htmlBody)->build();

        self::assertSame($htmlBody, (string)$email->getHtmlBody());
    }

    /**
     * @test
     */
    public function filterEmailAttachmentsByTypeForNoAttachmentReturnsEmptyArray(): void
    {
        $email = (new EmailBuilder())->build();

        self::assertSame([], $this->filterEmailAttachmentsByType($email, 'text/calendar'));
    }

    /**
     * @test
     */
    public function filterEmailAttachmentsByTypeIgnoresNonMatchingAttachment(): void
    {
        $emailBuilder = new EmailBuilder();
        $emailBuilder->attach('CSV data', 'text/csv', 'registrations.csv');
        $email = $emailBuilder->build();

        self::assertSame([], $this->filterEmailAttachmentsByType($email, 'text/calendar'));
    }

    /**
     * @test
     */
    public function filterEmailAttachmentsByTypeReturnsMatchingAttachmentWithExactMatch(): void
    {
        $body = 'Event data';
        $contentType = 'text/calendar';
        $fileName = 'event.ical';

        $emailBuilder = new EmailBuilder();
        $emailBuilder->attach($body, $contentType, $fileName);
        $email = $emailBuilder->build();

        $matches = $this->filterEmailAttachmentsByType($email, $contentType);
        self::assertContainsOnlyInstancesOf(DataPart::class, $matches);
        self::assertCount(1, $matches);
        $firstMatch = $matches[0];
        self::assertSame($body, $firstMatch->getBody());
        self::assertSame($contentType, $this->getContentTypeForDataPart($firstMatch));
        self::assertStringContainsString($fileName, $firstMatch->getPreparedHeaders()->toString());
    }

    /**
     * @test
     */
    public function filterEmailAttachmentsByTypeReturnsMatchingAttachmentUsingSubstringMatching(): void
    {
        $body = 'Event data';
        $contentType = 'text/calendar; charset="utf-8"; component="vevent"; method="publish"';
        $fileName = 'event.ical';

        $emailBuilder = new EmailBuilder();
        $emailBuilder->attach($body, $contentType, $fileName);
        $email = $emailBuilder->build();

        $matches = $this->filterEmailAttachmentsByType($email, 'text/calendar');
        self::assertContainsOnlyInstancesOf(DataPart::class, $matches);
        self::assertCount(1, $matches);
        $firstMatch = $matches[0];
        self::assertSame($body, $firstMatch->getBody());
        self::assertSame($contentType, $this->getContentTypeForDataPart($firstMatch));
        self::assertStringContainsString($fileName, $firstMatch->getPreparedHeaders()->toString());
    }
}
