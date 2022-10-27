<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository\Event;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDate
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDateTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopic
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopicTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\SingleEvent
 * @covers \OliverKlee\Seminars\Domain\Repository\Event\EventRepository
 */
final class EventRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var EventRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(EventRepository::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(EventRepository::class);
        }
    }

    /**
     * @test
     */
    public function mapsSingleEventWithAllFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(SingleEvent::class, $result);
        self::assertSame('Jousting', $result->getInternalTitle());
        self::assertSame('Jousting', $result->getDisplayTitle());
        self::assertSame('There is no glory in prevention.', $result->getDescription());
        self::assertEquals(new \DateTime('2022-04-02 10:00'), $result->getStart());
        self::assertEquals(new \DateTime('2022-04-03 18:00'), $result->getEnd());
        self::assertEquals(new \DateTime('2022-03-02 10:00'), $result->getEarlyBirdDeadline());
        self::assertEquals(new \DateTime('2022-04-01 10:00'), $result->getRegistrationDeadline());
        self::assertTrue($result->isRegistrationRequired());
        self::assertTrue($result->hasWaitingList());
        self::assertSame(5, $result->getMinimumNumberOfRegistrations());
        self::assertSame(20, $result->getMaximumNumberOfRegistrations());
        self::assertEqualsWithDelta(150.0, $result->getStandardPrice(), 0.0001);
        self::assertEqualsWithDelta(125.0, $result->getEarlyBirdPrice(), 0.0001);
        self::assertSame(15, $result->getOwnerUid());
    }

    /**
     * @test
     */
    public function mapsNotSetDateTimesForSingleEventAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithoutData.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(SingleEvent::class, $result);
        self::assertNull($result->getStart());
        self::assertNull($result->getEnd());
        self::assertNull($result->getEarlyBirdDeadline());
        self::assertNull($result->getRegistrationDeadline());
    }

    /**
     * @test
     */
    public function mapsEventTopicWithAllFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(EventTopic::class, $result);
        self::assertSame('Jousting topic', $result->getInternalTitle());
        self::assertSame('Jousting topic', $result->getDisplayTitle());
        self::assertSame('There is no glory in prevention.', $result->getDescription());
        self::assertEqualsWithDelta(150.0, $result->getStandardPrice(), 0.0001);
        self::assertEqualsWithDelta(125.0, $result->getEarlyBirdPrice(), 0.0001);
        self::assertSame(15, $result->getOwnerUid());
    }

    /**
     * @test
     */
    public function mapsEventDateWithAllFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateAndTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(EventDate::class, $result);
        self::assertSame('Jousting date', $result->getInternalTitle());
        self::assertSame('Jousting topic', $result->getDisplayTitle());
        self::assertSame('There is no glory in prevention.', $result->getDescription());
        self::assertEquals(new \DateTime('2022-04-02 10:00'), $result->getStart());
        self::assertEquals(new \DateTime('2022-04-03 18:00'), $result->getEnd());
        self::assertEquals(new \DateTime('2022-03-02 10:00'), $result->getEarlyBirdDeadline());
        self::assertEquals(new \DateTime('2022-04-01 10:00'), $result->getRegistrationDeadline());
        self::assertTrue($result->isRegistrationRequired());
        self::assertTrue($result->hasWaitingList());
        self::assertSame(5, $result->getMinimumNumberOfRegistrations());
        self::assertSame(20, $result->getMaximumNumberOfRegistrations());
        self::assertSame(15, $result->getOwnerUid());
    }

    /**
     * @test
     */
    public function mapsNotSetDateTimesForEventDateAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithoutData.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(SingleEvent::class, $result);
        self::assertNull($result->getStart());
        self::assertNull($result->getEnd());
        self::assertNull($result->getEarlyBirdDeadline());
        self::assertNull($result->getRegistrationDeadline());
    }

    /**
     * @test
     */
    public function persistAllPersistsAddedModels(): void
    {
        $event = new SingleEvent();

        $this->subject->add($event);
        $this->subject->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => $event->getUid()]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
    }

    /**
     * @test
     */
    public function persistsSingleEventWithSingleEventRecordType(): void
    {
        $event = new SingleEvent();
        $this->subject->add($event);
        $this->subject->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => $event->getUid()]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
        self::assertSame(EventInterface::TYPE_SINGLE_EVENT, $databaseRow['object_type']);
    }

    /**
     * @test
     */
    public function persistsEventTopicWithEventTopicType(): void
    {
        $event = new EventTopic();
        $this->subject->add($event);
        $this->subject->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => $event->getUid()]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
        self::assertSame(EventInterface::TYPE_EVENT_TOPIC, $databaseRow['object_type']);
    }

    /**
     * @test
     */
    public function persistsEventDateWithEventDateType(): void
    {
        $event = new EventDate();
        $this->subject->add($event);
        $this->subject->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => $event->getUid()]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
        self::assertSame(EventInterface::TYPE_EVENT_DATE, $databaseRow['object_type']);
    }

    /**
     * @test
     */
    public function mapsEventTopicAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateAndTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $topic = $result->getTopic();
        self::assertInstanceOf(EventTopic::class, $topic);
        self::assertSame(2, $topic->getUid());
        self::assertSame('Jousting topic', $topic->getInternalTitle());
    }

    /**
     * @test
     */
    public function mapsEmptyEventTypeAssociationForSingleEventAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        self::assertNull($result->getEventType());
    }

    /**
     * @test
     */
    public function mapsEmptyEventTypeAssociationForEventTopicAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventTopic::class, $result);

        self::assertNull($result->getEventType());
    }

    /**
     * @test
     */
    public function mapsEmptyEventTypeAssociationForEventDateAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateAndTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        self::assertNull($result->getEventType());
    }

    /**
     * @test
     */
    public function mapsEventTypeAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithEventType.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $eventType = $result->getEventType();
        self::assertInstanceOf(EventType::class, $eventType);
        self::assertSame(1, $eventType->getUid());
    }

    /**
     * @test
     */
    public function mapsEventTypeAssociationForEventTopic(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventTopicWithEventType.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventTopic::class, $result);

        $eventType = $result->getEventType();
        self::assertInstanceOf(EventType::class, $eventType);
        self::assertSame(1, $eventType->getUid());
    }

    /**
     * @test
     */
    public function mapsEventTypeAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateAndTopicWithEventType.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $eventType = $result->getEventType();
        self::assertInstanceOf(EventType::class, $eventType);
        self::assertSame(1, $eventType->getUid());
    }

    /**
     * @test
     */
    public function mapsVenuesAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithVenue.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getVenues();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Venue::class, $associatedModels->current());
    }

    /**
     * @test
     */
    public function mapsVenuesAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateWithVenue.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getVenues();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Venue::class, $associatedModels->current());
    }

    /**
     * @test
     */
    public function mapsSpeakersAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithSpeaker.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getSpeakers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Speaker::class, $associatedModels->current());
    }

    /**
     * @test
     */
    public function mapsSpeakersAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateWithSpeaker.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getSpeakers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Speaker::class, $associatedModels->current());
    }

    /**
     * @test
     */
    public function mapsOrganizersAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithOrganizer.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getOrganizers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Organizer::class, $associatedModels->current());
    }

    /**
     * @test
     */
    public function mapsOrganizersAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateWithOrganizer.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getOrganizers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Organizer::class, $associatedModels->current());
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidFindsSingleEventWithTheProvidedOwnerUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(42);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidForUidZeroIgnoresEventWithoutOwner(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithoutOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(0);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidFindsSingleEventWithTheProvidedOwnerUidOnAnyPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithOwnerOnPage.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(42);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidIgnoresSingleEventWithOtherOwnerUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/SingleEventWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidIgnoresEventDatesWithTheProvidedOwnerUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventDateWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidIgnoresEventTopicsWithTheProvidedOwnerUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/EventTopicWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidSortsEventByInternalTitleInAscendingOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventRepository/TwoSingleEventsWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(42);

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertSame(2, $firstMatch->getUid());
    }
}
