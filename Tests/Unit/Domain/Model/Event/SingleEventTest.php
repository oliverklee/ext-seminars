<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventStatistics;
use OliverKlee\Seminars\Domain\Model\Event\EventTopicInterface;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Domain\Model\RawDataInterface;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDateTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopicTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\SingleEvent
 * @covers \OliverKlee\Seminars\Domain\Model\RawDataTrait
 */
final class SingleEventTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private SingleEvent $subject;

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
    public function isMultiDayForNoStartAndNoEndReturnsFalse(): void
    {
        $this->subject->setStart(null);
        $this->subject->setEnd(null);

        self::assertFalse($this->subject->isMultiDay());
    }

    /**
     * @test
     */
    public function isMultiDayForStartAndNoEndReturnsFalse(): void
    {
        $this->subject->setStart(new \DateTime('2035-10-01 10:00'));
        $this->subject->setEnd(null);

        self::assertFalse($this->subject->isMultiDay());
    }

    /**
     * @test
     */
    public function isMultiDayForNoStartButEndReturnsFalse(): void
    {
        $this->subject->setStart(null);
        $this->subject->setEnd(new \DateTime('2035-10-01 18:00'));

        self::assertFalse($this->subject->isMultiDay());
    }

    /**
     * @test
     */
    public function isMultiDayForStartAndEndOnSameDayReturnsFalse(): void
    {
        $this->subject->setStart(new \DateTime('2035-10-01 10:00'));
        $this->subject->setEnd(new \DateTime('2035-10-01 18:00'));

        self::assertFalse($this->subject->isMultiDay());
    }

    /**
     * @test
     */
    public function isMultiDayForStartAndEndOnConsecutiveDaysReturnsTrue(): void
    {
        $this->subject->setStart(new \DateTime('2035-10-01 10:00'));
        $this->subject->setEnd(new \DateTime('2035-10-02 18:00'));

        self::assertTrue($this->subject->isMultiDay());
    }

    /**
     * @test
     */
    public function isMultiDayForStartAndEndTwoDaysApartReturnsTrue(): void
    {
        $this->subject->setStart(new \DateTime('2035-10-01 10:00'));
        $this->subject->setEnd(new \DateTime('2035-10-03 18:00'));

        self::assertTrue($this->subject->isMultiDay());
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
    public function getEffectiveRegistrationDeadlineWithNoRegistrationAndNoDatesSetReturnsNull(): void
    {
        $this->subject->setRegistrationRequired(false);
        $this->subject->setStart(null);
        $this->subject->setRegistrationDeadline(null);

        self::assertNull($this->subject->getEffectiveRegistrationDeadline());
    }

    /**
     * @test
     */
    public function getEffectiveRegistrationDeadlineWithNoRegistrationAndStartAndDeadlineSetReturnsNull(): void
    {
        $this->subject->setRegistrationRequired(false);
        $this->subject->setStart(new \DateTime());
        $this->subject->setRegistrationDeadline(new \DateTime());

        self::assertNull($this->subject->getEffectiveRegistrationDeadline());
    }

    /**
     * @test
     */
    public function getEffectiveRegistrationDeadlineWithRegistrationAndNoDatesSetReturnsNull(): void
    {
        $this->subject->setRegistrationRequired(true);
        $this->subject->setStart(null);
        $this->subject->setRegistrationDeadline(null);

        self::assertNull($this->subject->getEffectiveRegistrationDeadline());
    }

    /**
     * @test
     */
    public function getEffectiveRegistrationDeadlineWithRegistrationAndStartDateSetSetReturnsStartDate(): void
    {
        $startDate = new \DateTime();

        $this->subject->setRegistrationRequired(true);
        $this->subject->setStart($startDate);
        $this->subject->setRegistrationDeadline(null);

        self::assertSame($startDate, $this->subject->getEffectiveRegistrationDeadline());
    }

    /**
     * @test
     */
    public function getEffectiveRegistrationDeadlineWithRegistrationAndStartDateAndDeadlineSetSetReturnsDeadline(): void
    {
        $deadline = new \DateTime('now');
        $startDate = new \DateTime('now +1 day');

        $this->subject->setRegistrationRequired(true);
        $this->subject->setStart($startDate);
        $this->subject->setRegistrationDeadline($deadline);

        self::assertSame($deadline, $this->subject->getEffectiveRegistrationDeadline());
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
     * @return array<string, array{0: float}>
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
    public function hasUnlimitedSeatsForZeroMaxRegistrationsAndRegistrationRequiredReturnsTrue(): void
    {
        $this->subject->setMaximumNumberOfRegistrations(0);
        $this->subject->setRegistrationRequired(true);

        self::assertTrue($this->subject->hasUnlimitedSeats());
    }

    /**
     * @test
     */
    public function hasUnlimitedSeatsForNonZeroMaxRegistrationsAndRegistrationRequiredReturnsFalse(): void
    {
        $this->subject->setMaximumNumberOfRegistrations(10);
        $this->subject->setRegistrationRequired(true);

        self::assertFalse($this->subject->hasUnlimitedSeats());
    }

    /**
     * @test
     */
    public function hasUnlimitedSeatsForZeroMaxRegistrationsAndRegistrationNotRequiredReturnsFalse(): void
    {
        $this->subject->setMaximumNumberOfRegistrations(0);
        $this->subject->setRegistrationRequired(false);

        self::assertFalse($this->subject->hasUnlimitedSeats());
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
        $model = new EventStatistics(0, 0, 0, 0, 0, false);
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
    public function isHybridForOnSiteEventReturnsFalse(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ON_SITE);

        self::assertFalse($this->subject->isHybrid());
    }

    /**
     * @test
     */
    public function isHybridForOnlineEventReturnsFalse(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_ONLINE);

        self::assertFalse($this->subject->isHybrid());
    }

    /**
     * @test
     */
    public function isHybridForHybridEventReturnsTrue(): void
    {
        $this->subject->setEventFormat(EventDateInterface::EVENT_FORMAT_HYBRID);

        self::assertTrue($this->subject->isHybrid());
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

    /**
     * @test
     */
    public function getCityNamesForNoVenuesReturnsEmptyArray(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();
        $this->subject->setVenues($venues);

        $result = $this->subject->getCityNames();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getCityNamesForOneVenueReturnsCityFromVenue(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();

        $venue = new Venue();
        $cityName = 'Berlin';
        $venue->setCity($cityName);
        $venues->attach($venue);

        $this->subject->setVenues($venues);

        $result = $this->subject->getCityNames();

        self::assertSame([$cityName], $result);
    }

    /**
     * @test
     */
    public function getCityNamesForTwoVenuesInTheSameCityReturnsOnlyOneCity(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();
        $cityName = 'Berlin';

        $venue1 = new Venue();
        $venue1->setCity($cityName);
        $venues->attach($venue1);

        $venue2 = new Venue();
        $venue2->setCity($cityName);
        $venues->attach($venue2);

        $this->subject->setVenues($venues);

        $result = $this->subject->getCityNames();

        self::assertSame([$cityName], $result);
    }

    /**
     * @test
     */
    public function getCityNamesForTwoVenuesInDifferentCitiesReturnsBothCities(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();

        $venue1 = new Venue();
        $cityName1 = 'Berlin';
        $venue1->setCity($cityName1);
        $venues->attach($venue1);

        $venue2 = new Venue();
        $cityName2 = 'Bonn';
        $venue2->setCity($cityName2);
        $venues->attach($venue2);

        $this->subject->setVenues($venues);

        $result = $this->subject->getCityNames();

        self::assertSame([$cityName1, $cityName2], $result);
    }

    /**
     * @test
     */
    public function getCityNamesSortsCityNames(): void
    {
        /** @var ObjectStorage<Venue> $venues */
        $venues = new ObjectStorage();

        $venue1 = new Venue();
        $cityName1 = 'Köln';
        $venue1->setCity($cityName1);
        $venues->attach($venue1);

        $venue2 = new Venue();
        $cityName2 = 'Bonn';
        $venue2->setCity($cityName2);
        $venues->attach($venue2);

        $this->subject->setVenues($venues);

        $result = $this->subject->getCityNames();

        self::assertSame([$cityName2, $cityName1], $result);
    }

    /**
     * @test
     */
    public function isRegistrationPossibleByDateInitiallyThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('registrationPossibleByDate has not been set set.');
        $this->expectExceptionCode(1736269500);

        $this->subject->isRegistrationPossibleByDate();
    }

    /**
     * @return array<string, array{0: bool}>
     */
    public static function booleanDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     * @dataProvider booleanDataProvider
     */
    public function setRegistrationPossibleByDateSetsRegistrationPossibleByDate(bool $value): void
    {
        $this->subject->setRegistrationPossibleByDate($value);

        self::assertSame($value, $this->subject->isRegistrationPossibleByDate());
    }

    /**
     * @test
     */
    public function getRoomInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getRoom());
    }

    /**
     * @test
     */
    public function setRoomSetsRoom(): void
    {
        $value = 'room 13 B';
        $this->subject->setRoom($value);

        self::assertSame($value, $this->subject->getRoom());
    }

    /**
     * @test
     */
    public function getDownloadStartDateInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getDownloadStartDate());
    }

    /**
     * @test
     */
    public function setDownloadStartDateSetsDownloadStartDate(): void
    {
        $model = new \DateTime();
        $this->subject->setDownloadStartDate($model);

        self::assertSame($model, $this->subject->getDownloadStartDate());
    }

    /**
     * @test
     */
    public function areDownloadsPossibleByDateForNoDownloadStartDateReturnsTrue(): void
    {
        self::assertTrue($this->subject->areDownloadsPossibleByDate());
    }

    /**
     * @test
     */
    public function areDownloadsPossibleByDateForDownloadStartInPastReturnsTrue(): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('now')));
        $this->subject->setDownloadStartDate(new \DateTime('now -1 day'));

        self::assertTrue($this->subject->areDownloadsPossibleByDate());
    }

    /**
     * @test
     */
    public function areDownloadsPossibleByDateForDownloadStartInFutureReturnsFalse(): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('now')));
        $this->subject->setDownloadStartDate(new \DateTime('now +1 day'));

        self::assertFalse($this->subject->areDownloadsPossibleByDate());
    }
}
