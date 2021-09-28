<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
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

        self::assertSame(['max@example.com' => 'Max'], $mock->getTo());
    }

    /**
     * @test
     */
    public function mockRemembersFrom(): void
    {
        $mock = $this->createEmailMock();
        $mock->setFrom('max@example.com', 'Max');

        self::assertSame(['max@example.com' => 'Max'], $mock->getFrom());
    }

    /**
     * @test
     */
    public function mockRemembersReplyTo(): void
    {
        $mock = $this->createEmailMock();
        $mock->setReplyTo('max@example.com', 'Max');

        self::assertSame(['max@example.com' => 'Max'], $mock->getReplyTo());
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
    public function mockRemembersBody(): void
    {
        $body = 'What is love?';
        $mock = $this->createEmailMock();
        $mock->setBody($body);

        self::assertSame($body, $mock->getBody());
    }
}
