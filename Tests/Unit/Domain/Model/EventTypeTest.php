<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\EventTypeInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\EventType
 */
final class EventTypeTest extends UnitTestCase
{
    /**
     * @var EventType
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EventType();
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
    public function implementsEventTypeInterface(): void
    {
        self::assertInstanceOf(EventTypeInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $value = 'workshop';
        $this->subject->setTitle($value);

        self::assertSame($value, $this->subject->getTitle());
    }
}
