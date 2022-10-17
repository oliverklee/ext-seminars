<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopic
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 */
final class EventTopicTest extends UnitTestCase
{
    /**
     * @var EventTopic
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EventTopic();
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
    public function implementsEventInterface(): void
    {
        self::assertInstanceOf(EventInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function isEvent(): void
    {
        self::assertInstanceOf(Event::class, $this->subject);
    }

    /**
     * @test
     */
    public function getInternalTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getInternalTitle());
    }

    /**
     * @test
     */
    public function setInternalTitleSetsTitle(): void
    {
        $value = 'TYPO3 extension development';
        $this->subject->setInternalTitle($value);

        self::assertSame($value, $this->subject->getInternalTitle());
    }
}
