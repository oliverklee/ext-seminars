<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopicInterface;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventType;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDateTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopicTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\SingleEvent
 */
final class SingleEventTest extends UnitTestCase
{
    /**
     * @var \OliverKlee\Seminars\Domain\Model\Event\SingleEvent
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SingleEvent();
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
    public function implementsEventDateInterface(): void
    {
        self::assertInstanceOf(EventDateInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsEventTopicInterface(): void
    {
        self::assertInstanceOf(EventTopicInterface::class, $this->subject);
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

    /**
     * @test
     */
    public function getDisplayTitleReturnsInternalTitle(): void
    {
        $value = 'TYPO3 extension development';
        $this->subject->setInternalTitle($value);

        self::assertSame($value, $this->subject->getDisplayTitle());
    }

    /**
     * @test
     */
    public function getDescriptionInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription(): void
    {
        $value = 'Club-Mate';
        $this->subject->setDescription($value);

        self::assertSame($value, $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function getStartInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getStart());
    }

    /**
     * @test
     */
    public function setStartSetsStart(): void
    {
        $model = new \DateTime();
        $this->subject->setStart($model);

        self::assertSame($model, $this->subject->getStart());
    }

    /**
     * @test
     */
    public function getEndInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getEnd());
    }

    /**
     * @test
     */
    public function setEndSetsEnd(): void
    {
        $model = new \DateTime();
        $this->subject->setEnd($model);

        self::assertSame($model, $this->subject->getEnd());
    }

    /**
     * @test
     */
    public function getEarlyBirdDeadlineInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getEarlyBirdDeadline());
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineSetsEarlyBirdDeadline(): void
    {
        $model = new \DateTime();
        $this->subject->setEarlyBirdDeadline($model);

        self::assertSame($model, $this->subject->getEarlyBirdDeadline());
    }

    /**
     * @test
     */
    public function getRegistrationDeadlineInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getRegistrationDeadline());
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineSetsRegistrationDeadline(): void
    {
        $model = new \DateTime();
        $this->subject->setRegistrationDeadline($model);

        self::assertSame($model, $this->subject->getRegistrationDeadline());
    }

    /**
     * @test
     */
    public function requiresRegistrationInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->requiresRegistration());
    }

    /**
     * @test
     */
    public function setRequiresRegistrationSetsRequiresRegistration(): void
    {
        $this->subject->setRequiresRegistration(true);

        self::assertTrue($this->subject->requiresRegistration());
    }

    /**
     * @test
     */
    public function hasWaitingListInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasWaitingList());
    }

    /**
     * @test
     */
    public function setHasWaitingListSetsHasWaitingList(): void
    {
        $this->subject->setHasWaitingList(true);

        self::assertTrue($this->subject->hasWaitingList());
    }

    /**
     * @test
     */
    public function getMinimumNumberOfRegistrationsInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getMinimumNumberOfRegistrations());
    }

    /**
     * @test
     */
    public function setMinimumNumberOfRegistrationsSetsMinimumNumberOfRegistrations(): void
    {
        $value = 123456;
        $this->subject->setMinimumNumberOfRegistrations($value);

        self::assertSame($value, $this->subject->getMinimumNumberOfRegistrations());
    }

    /**
     * @test
     */
    public function getMaximumNumberOfRegistrationsInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getMaximumNumberOfRegistrations());
    }

    /**
     * @test
     */
    public function setMaximumNumberOfRegistrationsSetsMaximumNumberOfRegistrations(): void
    {
        $value = 123456;
        $this->subject->setMaximumNumberOfRegistrations($value);

        self::assertSame($value, $this->subject->getMaximumNumberOfRegistrations());
    }

    /**
     * @test
     */
    public function getStandardPriceInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getStandardPrice());
    }

    /**
     * @return array<string, array<int, float>>
     */
    public function validPriceDataProvider(): array
    {
        return [
            'zero' => [0.0],
            'positive' => [1234.56],
        ];
    }

    /**
     * @test
     * @dataProvider validPriceDataProvider
     */
    public function setStandardPriceSetsStandardPrice(float $price): void
    {
        $this->subject->setStandardPrice($price);

        self::assertSame($price, $this->subject->getStandardPrice());
    }

    /**
     * @test
     */
    public function setStandardPriceWithNegativeNumberThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1666112500);
        $this->expectExceptionMessage('The price must be >= 0.0.');

        $this->subject->setStandardPrice(-1.0);
    }

    /**
     * @test
     */
    public function getEarlyBirdPriceInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getEarlyBirdPrice());
    }

    /**
     * @test
     * @dataProvider validPriceDataProvider
     */
    public function setEarlyBirdPriceSetsEarlyBirdPrice(float $price): void
    {
        $this->subject->setEarlyBirdPrice($price);

        self::assertSame($price, $this->subject->getEarlyBirdPrice());
    }

    /**
     * @test
     */
    public function setEarlyBildPriceWithNegativeNumberThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1666112478);
        $this->expectExceptionMessage('The price must be >= 0.0.');

        $this->subject->setEarlyBirdPrice(-1.0);
    }

    /**
     * @test
     */
    public function getEventTypeInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getEventType());
    }

    /**
     * @test
     */
    public function setEventTypeSetsEventType(): void
    {
        $model = new EventType();
        $this->subject->setEventType($model);

        self::assertSame($model, $this->subject->getEventType());
    }
}
