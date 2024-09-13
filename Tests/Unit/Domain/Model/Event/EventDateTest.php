<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventStatistics;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Domain\Model\RawDataInterface;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDate
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDateTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopicTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTrait
 * @covers \OliverKlee\Seminars\Domain\Model\RawDataTrait
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
    public function implementsRawDataInterface(): void
    {
        self::assertInstanceOf(RawDataInterface::class, $this->subject);
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
    public function isHiddenInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->isHidden());
    }

    /**
     * @test
     */
    public function setHiddenSetsHidden(): void
    {
        $this->subject->setHidden(true);

        self::assertTrue($this->subject->isHidden());
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
        $venues = $this->subject->getVenues();

        self::assertInstanceOf(ObjectStorage::class, $venues);
        self::assertCount(0, $venues);
    }

    /**
     * @test
     */
    public function setVenuesSetsVenues(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();
        $this->subject->setVenues($venues);

        self::assertSame($venues, $this->subject->getVenues());
    }

    /**
     * @test
     */
    public function hasExactlyOneVenueForNoVenuesReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasExactlyOneVenue());
    }

    /**
     * @test
     */
    public function hasExactlyOneVenueForOneVenueReturnsTrue(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();
        $venues->attach(new Venue());
        $this->subject->setVenues($venues);

        self::assertTrue($this->subject->hasExactlyOneVenue());
    }

    /**
     * @test
     */
    public function hasExactlyOneVenueForTwoVenuesReturnsFalse(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();
        $venues->attach(new Venue());
        $venues->attach(new Venue());
        $this->subject->setVenues($venues);

        self::assertFalse($this->subject->hasExactlyOneVenue());
    }

    /**
     * @test
     */
    public function getFirstVenueForNoVenuesThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1726226635);
        $this->expectExceptionMessage('This event does not have any venues.');

        $this->subject->getFirstVenue();
    }

    /**
     * @test
     */
    public function getFirstVenueForOneVenueReturnsVenue(): void
    {
        $venue = new Venue();
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();
        $venues->attach($venue);
        $this->subject->setVenues($venues);

        self::assertSame($venue, $this->subject->getFirstVenue());
    }

    /**
     * @test
     */
    public function getFirstVenueForTwoVenuesReturnsFirstVenue(): void
    {
        $venue = new Venue();
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();
        $venues->attach($venue);
        $venues->attach(new Venue());
        $this->subject->setVenues($venues);

        self::assertSame($venue, $this->subject->getFirstVenue());
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
    public function getFirstOrganizerWithNoOrganizersThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1724277439);
        $this->expectExceptionMessage('This event does not have any organizers.');

        $this->subject->getFirstOrganizer();
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
    public function hasAdditionalTermsWithoutTopicReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasAdditionalTerms());
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
    public function hasAdditionalTermsWithTopicReturnsValueFromTopic(bool $value): void
    {
        $topic = new EventTopic();
        $topic->setAdditionalTerms($value);
        $this->subject->setTopic($topic);

        self::assertSame($value, $this->subject->hasAdditionalTerms());
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

    /**
     * @test
     */
    public function getStatisticsInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getStatistics());
    }

    /**
     * @test
     */
    public function setStatisticsSetsStatistics(): void
    {
        $model = new EventStatistics(0, 0, 0, 0, 0);
        $this->subject->setStatistics($model);

        self::assertSame($model, $this->subject->getStatistics());
    }

    /**
     * @test
     */
    public function eventFormatsAreDistinct(): void
    {
        $eventFormats = [
            EventDateInterface::EVENT_FORMAT_ON_SITE,
            EventDateInterface::EVENT_FORMAT_HYBRID,
            EventDateInterface::EVENT_FORMAT_ONLINE,
        ];

        self::assertSame(\array_unique($eventFormats), $eventFormats);
    }

    /**
     * @test
     */
    public function getEventFormatInitiallyReturnsOnSite(): void
    {
        self::assertSame(EventDateInterface::EVENT_FORMAT_ON_SITE, $this->subject->getEventFormat());
    }

    /**
     * @test
     */
    public function setEventFormatSetsEventFormat(): void
    {
        $value = EventDateInterface::EVENT_FORMAT_HYBRID;
        $this->subject->setEventFormat($value);

        self::assertSame($value, $this->subject->getEventFormat());
    }

    /**
     * @test
     */
    public function isAtLeastPartiallyOnSiteForOnSiteEventReturnsTrue(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ON_SITE);

        self::assertTrue($this->subject->isAtLeastPartiallyOnSite());
    }

    /**
     * @test
     */
    public function isAtLeastPartiallyOnSiteForHybridEventReturnsTrue(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_HYBRID);

        self::assertTrue($this->subject->isAtLeastPartiallyOnSite());
    }

    /**
     * @test
     */
    public function isAtLeastPartiallyOnSiteForOnlineEventReturnsFalse(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ONLINE);

        self::assertFalse($this->subject->isAtLeastPartiallyOnSite());
    }

    /**
     * @test
     */
    public function isAtLeastPartiallyOnlineForOnSiteEventReturnsFalse(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ON_SITE);

        self::assertFalse($this->subject->isAtLeastPartiallyOnline());
    }

    /**
     * @test
     */
    public function isAtLeastPartiallyOnlineForHybridEventReturnsTrue(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_HYBRID);

        self::assertTrue($this->subject->isAtLeastPartiallyOnline());
    }

    /**
     * @test
     */
    public function isAtLeastPartiallyOnlineForOnlineEventReturnsTrue(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ONLINE);

        self::assertTrue($this->subject->isAtLeastPartiallyOnline());
    }

    /**
     * @test
     */
    public function getWebinarUrlInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getWebinarUrl());
    }

    /**
     * @test
     */
    public function setWebinarUrlSetsWebinarUrl(): void
    {
        $value = 'https://example.com/webinar';
        $this->subject->setWebinarUrl($value);

        self::assertSame($value, $this->subject->getWebinarUrl());
    }

    /**
     * @test
     */
    public function hasUsableWebinarUrlForOnSiteEventWithoutWebinarUrlReturnsFalse(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ON_SITE);
        $this->subject->setWebinarUrl('');

        self::assertFalse($this->subject->hasUsableWebinarUrl());
    }

    /**
     * @test
     */
    public function hasUsableWebinarUrlForOnSiteEventWithWebinarUrlReturnsFalse(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ON_SITE);
        $this->subject->setWebinarUrl('https://example.com/webinar');

        self::assertFalse($this->subject->hasUsableWebinarUrl());
    }

    /**
     * @return array<string, array{0: EventDateInterface::EVENT_FORMAT_*}>
     */
    public function atLeastPartiallyOnlineEventFormatsDataProvider(): array
    {
        return [
            'hybrid' => [EventDateInterface::EVENT_FORMAT_HYBRID],
            'online' => [EventDateInterface::EVENT_FORMAT_ONLINE],
        ];
    }

    /**
     * @test
     * @param EventDateInterface::EVENT_FORMAT_* $eventFormat
     * @dataProvider atLeastPartiallyOnlineEventFormatsDataProvider
     */
    public function hasUsableWebinarUrlForPartiallyOnlineEventWithWebinarUrlReturnsTrue(int $eventFormat): void
    {
        $this->subject->setEventFormat($eventFormat);
        $this->subject->setWebinarUrl('https://example.com/webinar');

        self::assertTrue($this->subject->hasUsableWebinarUrl());
    }

    /**
     * @test
     * @param EventDateInterface::EVENT_FORMAT_* $eventFormat
     * @dataProvider atLeastPartiallyOnlineEventFormatsDataProvider
     */
    public function hasUsableWebinarUrlForPartiallyOnlineEventWithoutWebinarUrlReturnsFalse(int $eventFormat): void
    {
        $this->subject->setEventFormat($eventFormat);
        $this->subject->setWebinarUrl('');

        self::assertFalse($this->subject->hasUsableWebinarUrl());
    }

    /**
     * @test
     */
    public function getAdditionalEmailTextInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getAdditionalEmailText());
    }

    /**
     * @test
     */
    public function setAdditionalEmailTextSetsAdditionalEmailText(): void
    {
        $value = 'Club-Mate';
        $this->subject->setAdditionalEmailText($value);

        self::assertSame($value, $this->subject->getAdditionalEmailText());
    }
}
