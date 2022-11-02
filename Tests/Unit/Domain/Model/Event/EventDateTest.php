<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\Organizer;
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
        $model = new \DateTime();
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
        $model = new \DateTime();
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
        $model = new \DateTime();
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
        $model = new \DateTime();
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
}
