<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\EventTypeInterface;
use OliverKlee\Seminars\Domain\Model\NullEventType;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\NullEventType
 */
final class NullEventTypeTest extends UnitTestCase
{
    /**
     * @var NullEventType
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new NullEventType();
    }

    /**
     * @test
     */
    public function implementsEventTypeInterface(): void
    {
        self::assertInstanceOf(EventTypeInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function getUidReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getUid());
    }

    /**
     * @test
     */
    public function getTitleReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }
}
