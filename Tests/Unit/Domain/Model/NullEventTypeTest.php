<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\NullEventType;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    /**
     * @test
     */
    public function isEventType(): void
    {
        self::assertInstanceOf(EventType::class, $this->subject);
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
