<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDate
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDateTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopicTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTrait
 */
final class EventDateTest extends UnitTestCase
{
    /**
     * @var EventDate
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EventDate();
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
    public function getTopicInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getTopic());
    }

    /**
     * @test
     */
    public function setTopicSetsTopic(): void
    {
        $model = new EventTopic();
        $this->subject->setTopic($model);

        self::assertSame($model, $this->subject->getTopic());
    }

    /**
     * @test
     */
    public function getDisplayTitleWithoutTopicReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getDisplayTitle());
    }

    /**
     * @test
     */
    public function getDisplayTitleWithTopicReturnsDisplayTitleFromTopic(): void
    {
        $topic = new EventTopic();
        $value = 'TYPO3 extension development';
        $topic->setInternalTitle($value);
        $this->subject->setTopic($topic);

        self::assertSame($value, $this->subject->getDisplayTitle());
    }

    /**
     * @test
     */
    public function getDescriptionWithoutTopicReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function getDescriptionWithTopicReturnsDescriptionFromTopic(): void
    {
        $topic = new EventTopic();
        $value = 'TYPO3 extension development';
        $topic->setDescription($value);
        $this->subject->setTopic($topic);

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
        $model = new \DateTimeImmutable();
        $this->subject->setStart($model);

        self::assertSame($model, $this->subject->getStart());
    }

    /**
     * @test
     */
    public function setStartCanSetStartToNull(): void
    {
        $this->subject->setStart(null);

        self::assertNull($this->subject->getStart());
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
        $model = new \DateTimeImmutable();
        $this->subject->setEnd($model);

        self::assertSame($model, $this->subject->getEnd());
    }

    /**
     * @test
     */
    public function setEndCanSetEndToNull(): void
    {
        $this->subject->setEnd(null);

        self::assertNull($this->subject->getEnd());
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
        $model = new \DateTimeImmutable();
        $this->subject->setEarlyBirdDeadline($model);

        self::assertSame($model, $this->subject->getEarlyBirdDeadline());
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineCanSetEarlyBirdDeadlineToNull(): void
    {
        $this->subject->setEarlyBirdDeadline(null);

        self::assertNull($this->subject->getEarlyBirdDeadline());
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
        $model = new \DateTimeImmutable();
        $this->subject->setRegistrationDeadline($model);

        self::assertSame($model, $this->subject->getRegistrationDeadline());
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineCanSetRegistrationDeadlineToNull(): void
    {
        $this->subject->setRegistrationDeadline(null);

        self::assertNull($this->subject->getRegistrationDeadline());
    }

    /**
     * @test
     */
    public function isRegistrationRequiredInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->isRegistrationRequired());
    }

    /**
     * @test
     */
    public function isRegistrationRequiredSetsRegistrationRequired(): void
    {
        $this->subject->setRegistrationRequired(true);

        self::assertTrue($this->subject->isRegistrationRequired());
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
    public function setWaitingListSetsHasWaitingList(): void
    {
        $this->subject->setWaitingList(true);

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
    public function getStandardPriceWithoutTopicReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getStandardPrice());
    }

    /**
     * @test
     */
    public function getStandardPriceWithTopicReturnsStandardPriceFromTopic(): void
    {
        $topic = new EventTopic();
        $value = 500.0;
        $topic->setStandardPrice($value);
        $this->subject->setTopic($topic);

        self::assertEqualsWithDelta($value, $this->subject->getStandardPrice(), 0.0001);
    }

    /**
     * @test
     */
    public function getEarlyBirdPriceWithoutTopicReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getEarlyBirdPrice());
    }

    /**
     * @test
     */
    public function getEarlyBirdPriceWithTopicReturnsEarlyBirdPriceFromTopic(): void
    {
        $topic = new EventTopic();
        $value = 500.0;
        $topic->setEarlyBirdPrice($value);
        $this->subject->setTopic($topic);

        self::assertEqualsWithDelta($value, $this->subject->getEarlyBirdPrice(), 0.0001);
    }

    /**
     * @test
     */
    public function getEventTypeWithoutTopicReturnsNull(): void
    {
        self::assertNull($this->subject->getEventType());
    }

    /**
     * @test
     */
    public function getEventTypeWithTopicReturnsEventTypeFromTopic(): void
    {
        $topic = new EventTopic();
        $eventType = new EventType();
        $topic->setEventType($eventType);
        $this->subject->setTopic($topic);

        self::assertSame($eventType, $this->subject->getEventType());
    }

    /**
     * @test
     */
    public function getVenuesInitiallyReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getVenues();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function setVenuesSetsVenues(): void
    {
        /** @var ObjectStorage<Venue> $associatedModels */
        $associatedModels = new ObjectStorage();
        $this->subject->setVenues($associatedModels);

        self::assertSame($associatedModels, $this->subject->getVenues());
    }

    /**
     * @test
     */
    public function getSpeakersInitiallyReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getSpeakers();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function setSpeakersSetsSpeakers(): void
    {
        /** @var ObjectStorage<Speaker> $associatedModels */
        $associatedModels = new ObjectStorage();
        $this->subject->setSpeakers($associatedModels);

        self::assertSame($associatedModels, $this->subject->getSpeakers());
    }

    /**
     * @test
     */
    public function getOrganizersInitiallyReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getOrganizers();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function setOrganizersSetsOrganizers(): void
    {
        /** @var ObjectStorage<Organizer> $associatedModels */
        $associatedModels = new ObjectStorage();
        $this->subject->setOrganizers($associatedModels);

        self::assertSame($associatedModels, $this->subject->getOrganizers());
    }

    /**
     * @test
     */
    public function getFirstOrganizerWithNoOrganizersReturnsNull(): void
    {
        self::assertNull($this->subject->getFirstOrganizer());
    }

    /**
     * @test
     */
    public function getFirstOrganizerWithTwoOrganizersReturnsFirstOrganizer(): void
    {
        /** @var ObjectStorage<Organizer> $organizers */
        $organizers = new ObjectStorage();
        $organizer1 = new Organizer();
        $organizers->attach($organizer1);
        $organizer2 = new Organizer();
        $organizers->attach($organizer2);
        $this->subject->setOrganizers($organizers);

        self::assertSame($organizer1, $this->subject->getFirstOrganizer());
    }

    /**
     * @test
     */
    public function getFirstOrganizerWithTwoOrganizersNotRewoundReturnsFirstOrganizer(): void
    {
        /** @var ObjectStorage<Organizer> $organizers */
        $organizers = new ObjectStorage();
        $organizer1 = new Organizer();
        $organizers->attach($organizer1);
        $organizer2 = new Organizer();
        $organizers->attach($organizer2);
        $organizers->rewind();
        $organizers->next();
        $this->subject->setOrganizers($organizers);

        self::assertSame($organizer1, $this->subject->getFirstOrganizer());
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
    public function getRegistrationStartInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getRegistrationStart());
    }

    /**
     * @test
     */
    public function setRegistrationStartSetsRegistrationStart(): void
    {
        $model = new \DateTimeImmutable();
        $this->subject->setRegistrationStart($model);

        self::assertSame($model, $this->subject->getRegistrationStart());
    }

    /**
     * @test
     */
    public function hasAdditionalTermsAndConditionsWithoutTopicReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasAdditionalTermsAndConditions());
    }

    /**
     * @return array<string, array{0: bool}>
     */
    public function boolDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     * @dataProvider boolDataProvider
     */
    public function hasAdditionalTermsAndConditionsWithTopicReturnsValueFromTopic(bool $value): void
    {
        $topic = new EventTopic();
        $topic->setAdditionalTermsAndConditions($value);
        $this->subject->setTopic($topic);

        self::assertSame($value, $this->subject->hasAdditionalTermsAndConditions());
    }

    /**
     * @test
     */
    public function isMultipleRegistrationPossibleWithoutTopicReturnsFalse(): void
    {
        self::assertFalse($this->subject->isMultipleRegistrationPossible());
    }

    /**
     * @test
     * @dataProvider boolDataProvider
     */
    public function isMultipleRegistrationPossibleWithTopicReturnsValueFromTopic(bool $value): void
    {
        $topic = new EventTopic();
        $topic->setMultipleRegistrationPossible($value);
        $this->subject->setTopic($topic);

        self::assertSame($value, $this->subject->isMultipleRegistrationPossible());
    }

    /**
     * @test
     */
    public function getNumberOfOfflineRegistrationsInitiallyReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getNumberOfOfflineRegistrations());
    }

    /**
     * @test
     */
    public function setNumberOfOfflineRegistrationsSetsNumberOfOfflineRegistrations(): void
    {
        $value = 123456;
        $this->subject->setNumberOfOfflineRegistrations($value);

        self::assertSame($value, $this->subject->getNumberOfOfflineRegistrations());
    }

    /**
     * @test
     */
    public function getStatusByDefaultReturnsPlanned(): void
    {
        self::assertSame(EventInterface::STATUS_PLANNED, $this->subject->getStatus());
    }

    /**
     * @return array<string, array{0: EventInterface::STATUS_*}>
     */
    public function statusDataProvider(): array
    {
        return [
            'planned' => [EventInterface::STATUS_PLANNED],
            'confirmed' => [EventInterface::STATUS_CONFIRMED],
            'canceled' => [EventInterface::STATUS_CANCELED],
        ];
    }

    /**
     * @test
     * @param EventInterface::STATUS_* $status
     * @dataProvider statusDataProvider
     */
    public function setStatusSetsStatus(int $status): void
    {
        $this->subject->setStatus($status);

        self::assertSame($status, $this->subject->getStatus());
    }

    /**
     * @return array<string, array{0: EventInterface::STATUS_*}>
     */
    public function nonCanceledStatusDataProvider(): array
    {
        return [
            'planned' => [EventInterface::STATUS_PLANNED],
            'confirmed' => [EventInterface::STATUS_CONFIRMED],
        ];
    }

    /**
     * @test
     * @param EventInterface::STATUS_* $status
     * @dataProvider nonCanceledStatusDataProvider
     */
    public function isCanceledForNonCanceledStatusReturnsFalse(int $status): void
    {
        $this->subject->setStatus($status);

        self::assertFalse($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function isCanceledForCanceledStatusReturnsTrue(): void
    {
        $this->subject->setStatus(EventInterface::STATUS_CANCELED);

        self::assertTrue($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function getSpecialPriceWithoutTopicReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getSpecialPrice());
    }

    /**
     * @test
     */
    public function getSpecialPriceWithTopicReturnsSpecialPriceFromTopic(): void
    {
        $topic = new EventTopic();
        $value = 500.0;
        $topic->setSpecialPrice($value);
        $this->subject->setTopic($topic);

        self::assertEqualsWithDelta($value, $this->subject->getSpecialPrice(), 0.0001);
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceWithoutTopicReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getSpecialEarlyBirdPrice());
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceWithTopicReturnsSpecialEarlyBirdPriceFromTopic(): void
    {
        $topic = new EventTopic();
        $value = 500.0;
        $topic->setSpecialEarlyBirdPrice($value);
        $this->subject->setTopic($topic);

        self::assertEqualsWithDelta($value, $this->subject->getSpecialEarlyBirdPrice(), 0.0001);
    }

    /**
     * @test
     */
    public function getAccommodationOptionsInitiallyReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getAccommodationOptions();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function setAccommodationOptionsSetsAccommodationOptions(): void
    {
        /** @var ObjectStorage<AccommodationOption> $associatedModels */
        $associatedModels = new ObjectStorage();
        $this->subject->setAccommodationOptions($associatedModels);

        self::assertSame($associatedModels, $this->subject->getAccommodationOptions());
    }

    /**
     * @test
     */
    public function getFoodOptionsInitiallyReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getFoodOptions();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function setFoodOptionsSetsFoodOptions(): void
    {
        /** @var ObjectStorage<FoodOption> $associatedModels */
        $associatedModels = new ObjectStorage();
        $this->subject->setFoodOptions($associatedModels);

        self::assertSame($associatedModels, $this->subject->getFoodOptions());
    }

    /**
     * @test
     */
    public function getRegistrationCheckboxesInitiallyReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getRegistrationCheckboxes();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function setRegistrationCheckboxesSetsCheckboxes(): void
    {
        /** @var ObjectStorage<RegistrationCheckbox> $associatedModels */
        $associatedModels = new ObjectStorage();
        $this->subject->setRegistrationCheckboxes($associatedModels);

        self::assertSame($associatedModels, $this->subject->getRegistrationCheckboxes());
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithoutTopicReturnsEmptyStorage(): void
    {
        $associatedModels = $this->subject->getPaymentMethods();

        self::assertInstanceOf(ObjectStorage::class, $associatedModels);
        self::assertCount(0, $associatedModels);
    }

    /**
     * @test
     */
    public function getPaymentMethodsWithTopicReturnsPaymentMethodsFromTopic(): void
    {
        $topic = new EventTopic();
        /** @var ObjectStorage<PaymentMethod> $paymentMethods */
        $paymentMethods = new ObjectStorage();
        $topic->setPaymentMethods($paymentMethods);
        $this->subject->setTopic($topic);

        self::assertSame($paymentMethods, $this->subject->getPaymentMethods());
    }

    /**
     * @test
     */
    public function isFreeOfChargeForNoTopicReturnsTrue(): void
    {
        self::assertTrue($this->subject->isFreeOfCharge());
    }

    /**
     * @test
     * @dataProvider boolDataProvider
     */
    public function isFreeOfChargeWithTopicReturnsValueFromTopic(bool $freeOfCharge): void
    {
        $topic = $this->createMock(EventTopic::class);
        $topic->method('isFreeOfCharge')->willReturn($freeOfCharge);
        $this->subject->setTopic($topic);

        self::assertSame($freeOfCharge, $this->subject->isFreeOfCharge());
    }

    /**
     * @test
     */
    public function getAllPricesForNoTopicReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getAllPrices());
    }

    /**
     * @test
     */
    public function getAllPricesWithTopicReturnsPricesFromTopic(): void
    {
        $prices = [Price::PRICE_STANDARD => new Price(0.0, 'price.standard', Price::PRICE_STANDARD)];
        $topic = $this->createMock(EventTopic::class);
        $topic->method('getAllPrices')->willReturn($prices);
        $this->subject->setTopic($topic);

        self::assertSame($prices, $this->subject->getAllPrices());
    }

    /**
     * @test
     */
    public function getPricesByPriceCodeForNoTopicThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1668096905);
        $this->expectExceptionMessage('This event date does not have a topic.');

        $this->subject->getPriceByPriceCode(Price::PRICE_EARLY_BIRD);
    }

    /**
     * @test
     */
    public function getPricesByPriceCodeWithTopicReturnsPriceFromTopic(): void
    {
        $price = new Price(0.0, 'price.standard', Price::PRICE_STANDARD);
        $topic = $this->createMock(EventTopic::class);
        $topic->method('getPriceByPriceCode')->with(Price::PRICE_STANDARD)->willReturn($price);
        $this->subject->setTopic($topic);

        self::assertSame($price, $this->subject->getPriceByPriceCode(Price::PRICE_STANDARD));
    }

    /**
     * @test
     */
    public function allowsUnlimitedRegistrationsForZeroMaxRegistrationsAndRegistrationRequiredReturnsTrue(): void
    {
        $this->subject->setMaximumNumberOfRegistrations(0);
        $this->subject->setRegistrationRequired(true);

        self::assertTrue($this->subject->allowsUnlimitedRegistrations());
    }

    /**
     * @test
     */
    public function allowsUnlimitedRegistrationsForNonZeroMaxRegistrationsAndRegistrationRequiredReturnsFalse(): void
    {
        $this->subject->setMaximumNumberOfRegistrations(10);
        $this->subject->setRegistrationRequired(true);

        self::assertFalse($this->subject->allowsUnlimitedRegistrations());
    }

    /**
     * @test
     */
    public function allowsUnlimitedRegistrationsForZeroMaxRegistrationsAndRegistrationNotRequiredReturnsFalse(): void
    {
        $this->subject->setMaximumNumberOfRegistrations(0);
        $this->subject->setRegistrationRequired(false);

        self::assertFalse($this->subject->allowsUnlimitedRegistrations());
    }
}
