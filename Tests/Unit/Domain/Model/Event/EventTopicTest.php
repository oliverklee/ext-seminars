<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\EventTopicInterface;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopic
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopicTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTrait
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

    /**
     * @test
     */
    public function setEventTypeCanSetEventTypeToNull(): void
    {
        $this->subject->setEventType(null);

        self::assertNull($this->subject->getEventType());
    }

    /**
     * @test
     */
    public function getOwnerUidInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getOwnerUid());
    }

    /**
     * @test
     */
    public function setOwnerUidSetsOwnerUid(): void
    {
        $value = 123456;
        $this->subject->setOwnerUid($value);

        self::assertSame($value, $this->subject->getOwnerUid());
    }

    /**
     * @test
     */
    public function hasAdditionalTermsAndConditionsInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasAdditionalTermsAndConditions());
    }

    /**
     * @test
     */
    public function setAdditionalTermsAndConditionsSetsAdditionalTermsAndConditions(): void
    {
        $this->subject->setAdditionalTermsAndConditions(true);

        self::assertTrue($this->subject->hasAdditionalTermsAndConditions());
    }

    /**
     * @test
     */
    public function isMultipleRegistrationPossibleInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->isMultipleRegistrationPossible());
    }

    /**
     * @test
     */
    public function setMultipleRegistrationPossibleSetsMultipleRegistrationPossible(): void
    {
        $this->subject->setMultipleRegistrationPossible(true);

        self::assertTrue($this->subject->isMultipleRegistrationPossible());
    }

    /**
     * @test
     */
    public function getSpecialPriceInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getSpecialPrice());
    }

    /**
     * @test
     * @dataProvider validPriceDataProvider
     */
    public function setSpecialPriceSetsSpecialPrice(float $price): void
    {
        $this->subject->setSpecialPrice($price);

        self::assertSame($price, $this->subject->getSpecialPrice());
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getSpecialEarlyBirdPrice());
    }

    /**
     * @test
     * @dataProvider validPriceDataProvider
     */
    public function setSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice(float $price): void
    {
        $this->subject->setSpecialEarlyBirdPrice($price);

        self::assertSame($price, $this->subject->getSpecialEarlyBirdPrice());
    }

    /**
     * @test
     */
    public function getPaymentMethodsInitiallyReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getPaymentMethods();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function setPaymentMethodsSetsCheckboxes(): void
    {
        /** @var ObjectStorage<PaymentMethod> $associatedModels */
        $associatedModels = new ObjectStorage();
        $this->subject->setPaymentMethods($associatedModels);

        self::assertSame($associatedModels, $this->subject->getPaymentMethods());
    }

    /**
     * @test
     */
    public function isFreeOfChargeForNoPricesSetReturnsTrue(): void
    {
        self::assertTrue($this->subject->isFreeOfCharge());
    }

    /**
     * @test
     */
    public function isFreeOfChargeForAllPricesSetToZeroReturnsTrue(): void
    {
        $this->subject->setStandardPrice(0.0);
        $this->subject->setEarlyBirdPrice(0.0);
        $this->subject->setSpecialPrice(0.0);
        $this->subject->setSpecialEarlyBirdPrice(0.0);

        self::assertTrue($this->subject->isFreeOfCharge());
    }

    /**
     * @test
     */
    public function isFreeOfChargeForNonZeroStandardPriceAndAllOtherPricesZeroReturnsFalse(): void
    {
        $this->subject->setStandardPrice(42.0);
        $this->subject->setEarlyBirdPrice(0.0);
        $this->subject->setSpecialPrice(0.0);
        $this->subject->setSpecialEarlyBirdPrice(0.0);

        self::assertFalse($this->subject->isFreeOfCharge());
    }

    /**
     * @test
     */
    public function isFreeOfChargeForAllPricesSetToNonZeroReturnsFalse(): void
    {
        $this->subject->setStandardPrice(1.0);
        $this->subject->setEarlyBirdPrice(2.0);
        $this->subject->setSpecialPrice(3.0);
        $this->subject->setSpecialEarlyBirdPrice(4.0);

        self::assertFalse($this->subject->isFreeOfCharge());
    }

    /**
     * @test
     */
    public function isFreeOfChargeForZeroStandardPriceAndAllOtherPricesNonZeroReturnsTrue(): void
    {
        $this->subject->setStandardPrice(0.0);
        $this->subject->setEarlyBirdPrice(2.0);
        $this->subject->setSpecialPrice(3.0);
        $this->subject->setSpecialEarlyBirdPrice(4.0);

        self::assertTrue($this->subject->isFreeOfCharge());
    }
}
