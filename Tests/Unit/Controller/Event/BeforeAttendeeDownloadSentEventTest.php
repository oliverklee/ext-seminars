<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\Event;

use OliverKlee\Seminars\Controller\Event\BeforeAttendeeDownloadSentEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\Event\BeforeAttendeeDownloadSentEvent
 */
final class BeforeAttendeeDownloadSentEventTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getRegistrationReturnsRegistrationProvidedToConstructor(): void
    {
        $registration = new Registration();
        $subject = new BeforeAttendeeDownloadSentEvent(
            $registration,
            $this->createStub(ResourceInterface::class),
            $this->createStub(StreamInterface::class),
        );

        self::assertSame($registration, $subject->getRegistration());
    }

    /**
     * @test
     */
    public function getFileResourceReturnsResourceProvidedToConstructor(): void
    {
        $resource = $this->createStub(ResourceInterface::class);
        $subject = new BeforeAttendeeDownloadSentEvent(
            new Registration(),
            $resource,
            $this->createStub(StreamInterface::class),
        );

        self::assertSame($resource, $subject->getFileResource());
    }

    /**
     * @test
     */
    public function getContentStreamByDefaultReturnsStreamProvidedToConstructor(): void
    {
        $stream = $this->createStub(StreamInterface::class);
        $subject = new BeforeAttendeeDownloadSentEvent(
            new Registration(),
            $this->createStub(ResourceInterface::class),
            $stream,
        );

        self::assertSame($stream, $subject->getContentStream());
    }

    /**
     * @test
     */
    public function setContentStreamOverridesContentStream(): void
    {
        $stream1 = $this->createStub(StreamInterface::class);
        $stream2 = $this->createStub(StreamInterface::class);
        $subject = new BeforeAttendeeDownloadSentEvent(
            new Registration(),
            $this->createStub(ResourceInterface::class),
            $stream1,
        );

        self::assertSame($stream1, $subject->getContentStream());

        $subject->setContentStream($stream2);
        self::assertSame($stream2, $subject->getContentStream());
    }
}
