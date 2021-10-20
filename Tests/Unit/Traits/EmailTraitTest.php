<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\System\Typo3Version;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * @covers \OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait
 */
final class EmailTraitTest extends UnitTestCase
{
    use EmailTrait;

    private function runInV9Only(): void
    {
        if (Typo3Version::isAtLeast(10)) {
            self::markTestSkipped('This test is intended for V9 only.');
        }
    }

    private function runInV10AndHigherOnly(): void
    {
        if (Typo3Version::isNotHigherThan(9)) {
            self::markTestSkipped('This test is intended for V10 and higher only.');
        }
    }

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
    public function mockRemembersTextBodyInV9(): void
    {
        $this->runInV9Only();

        $textBody = 'What is love?';
        $mock = $this->createEmailMock();
        // @phpstan-ignore-next-line This line is V9-specific, and we are running PHPStan with V10.
        $mock->setBody($textBody);

        self::assertSame($textBody, $this->getTextBodyOfEmail($mock));
    }

    /**
     * @test
     */
    public function mockRemembersTextBodyInV10(): void
    {
        $this->runInV10AndHigherOnly();

        $textBody = 'What is love?';
        $mock = $this->createEmailMock();
        $mock->text($textBody);

        self::assertSame($textBody, $this->getTextBodyOfEmail($mock));
    }
}
