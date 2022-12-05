<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopicInterface;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
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
        $date = new \DateTime();
        $this->subject->setStart($date);

        self::assertSame($date, $this->subject->getStart());
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
        $date = new \DateTime();
        $this->subject->setEnd($date);

        self::assertSame($date, $this->subject->getEnd());
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
        $date = new \DateTime();
        $this->subject->setEarlyBirdDeadline($date);

        self::assertSame($date, $this->subject->getEarlyBirdDeadline());
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
        $date = new \DateTime();
        $this->subject->setRegistrationDeadline($date);

        self::assertSame($date, $this->subject->getRegistrationDeadline());
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
    public function setRegistrationRequiredSetsRegistrationRequired(): void
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
        $date = new \DateTime();
        $this->subject->setRegistrationStart($date);

        self::assertSame($date, $this->subject->getRegistrationStart());
    }

    /**
     * @test
     */
    public function setRegistrationStartCanSetRegistrationStartToNull(): void
    {
        $this->subject->setRegistrationStart(null);

        self::assertNull($this->subject->getRegistrationStart());
    }

    /**
     * @test
     */
    public function hasAdditionalTermsInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasAdditionalTerms());
    }

    /**
     * @test
     */
    public function setAdditionalTermsSetsAdditionalTerms(): void
    {
        $this->subject->setAdditionalTerms(true);

        self::assertTrue($this->subject->hasAdditionalTerms());
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

    /**
     * @test
     */
    public function getAllPricesForNoPricesSetReturnsZeroStandardPrice(): void
    {
        $result = $this->subject->getAllPrices();

        self::assertCount(1, $result);
        $expected = [Price::PRICE_STANDARD => new Price(0.0, 'price.standard', Price::PRICE_STANDARD)];
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getAllPricesForAllPricesZeroReturnsZeroStandardPrice(): void
    {
        $this->subject->setStandardPrice(0.0);
        $this->subject->setEarlyBirdPrice(0.0);
        $this->subject->setSpecialPrice(0.0);
        $this->subject->setSpecialEarlyBirdPrice(0.0);

        $result = $this->subject->getAllPrices();

        self::assertCount(1, $result);
        $expected = [Price::PRICE_STANDARD => new Price(0.0, 'price.standard', Price::PRICE_STANDARD)];
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getAllPricesForZeroStandardPriceAndAllOtherPricesNonZeroReturnsZeroStandardPrice(): void
    {
        $this->subject->setStandardPrice(0.0);
        $this->subject->setEarlyBirdPrice(2.0);
        $this->subject->setSpecialPrice(3.0);
        $this->subject->setSpecialEarlyBirdPrice(4.0);

        $result = $this->subject->getAllPrices();

        self::assertCount(1, $result);
        $expected = [Price::PRICE_STANDARD => new Price(0.0, 'price.standard', Price::PRICE_STANDARD)];
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getAllPricesForForNonZeroStandardPriceAndAllOtherPricesZeroReturnsStandardPrice(): void
    {
        $standardPriceAmount = 200.0;
        $this->subject->setStandardPrice($standardPriceAmount);
        $this->subject->setEarlyBirdPrice(0.0);
        $this->subject->setSpecialPrice(0.0);
        $this->subject->setSpecialEarlyBirdPrice(0.0);

        $result = $this->subject->getAllPrices();

        self::assertCount(1, $result);
        $expected = [Price::PRICE_STANDARD => new Price($standardPriceAmount, 'price.standard', Price::PRICE_STANDARD)];
        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getAllPricesForAllPricesNonZeroReturnsAllPrices(): void
    {
        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $specialPriceAmount = 3.0;
        $specialEarlyBirdPriceAmount = 4.0;
        $this->subject->setStandardPrice($standardPriceAmount);
        $this->subject->setEarlyBirdPrice($earlyBirdPriceAmount);
        $this->subject->setSpecialPrice($specialPriceAmount);
        $this->subject->setSpecialEarlyBirdPrice($specialEarlyBirdPriceAmount);

        $result = $this->subject->getAllPrices();

        self::assertCount(4, $result);
        $expected = [
            Price::PRICE_STANDARD => new Price($standardPriceAmount, 'price.standard', Price::PRICE_STANDARD),
            Price::PRICE_EARLY_BIRD => new Price($earlyBirdPriceAmount, 'price.earlyBird', Price::PRICE_EARLY_BIRD),
            Price::PRICE_SPECIAL => new Price($specialPriceAmount, 'price.special', Price::PRICE_SPECIAL),
            Price::PRICE_SPECIAL_EARLY_BIRD => new Price(
                $specialEarlyBirdPriceAmount,
                'price.specialEarlyBird',
                Price::PRICE_SPECIAL_EARLY_BIRD
            ),
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function getPriceByPriceCodeWithStandardPriceCodeForNoPricesReturnsFreeStandardPrice(): void
    {
        $expectedPrice = new Price(0.0, 'price.standard', Price::PRICE_STANDARD);
        self::assertEquals($expectedPrice, $this->subject->getPriceByPriceCode(Price::PRICE_STANDARD));
    }

    /**
     * @test
     */
    public function getPriceByPriceCodeForExistingNonStandardPriceWithThatCodeReturnsMatchingPrice(): void
    {
        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $this->subject->setStandardPrice($standardPriceAmount);
        $this->subject->setEarlyBirdPrice($earlyBirdPriceAmount);

        $expectedPrice = new Price($earlyBirdPriceAmount, 'price.earlyBird', Price::PRICE_EARLY_BIRD);
        self::assertEquals($expectedPrice, $this->subject->getPriceByPriceCode(Price::PRICE_EARLY_BIRD));
    }

    /**
     * @test
     */
    public function getPriceByPriceCodeForInexistentPriceThrowsException(): void
    {
        $standardPriceAmount = 1.0;
        $this->subject->setStandardPrice($standardPriceAmount);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1668096769);
        $this->expectExceptionMessage(
            'This event does not have a price with the code "' . Price::PRICE_EARLY_BIRD . '".'
        );

        $this->subject->getPriceByPriceCode(Price::PRICE_EARLY_BIRD);
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

    /**
     * @test
     */
    public function getRawDataInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getRawData());
    }

    /**
     * @test
     */
    public function setRawDataSetsRawData(): void
    {
        $rawData = ['uid' => 5, 'title' => 'foo'];

        $this->subject->setRawData($rawData);

        self::assertSame($rawData, $this->subject->getRawData());
    }
}
