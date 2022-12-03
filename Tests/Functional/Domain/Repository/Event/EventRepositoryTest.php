<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository\Event;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
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
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

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
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(SingleEvent::class, $result);
        self::assertSame('Jousting', $result->getInternalTitle());
        self::assertSame('Jousting', $result->getDisplayTitle());
        self::assertSame('There is no glory in prevention.', $result->getDescription());
        self::assertEquals(new \DateTime('2022-04-02 10:00'), $result->getStart());
        self::assertEquals(new \DateTime('2022-04-03 18:00'), $result->getEnd());
        self::assertEquals(new \DateTime('2022-01-01 00:00'), $result->getRegistrationStart());
        self::assertEquals(new \DateTime('2022-03-02 10:00'), $result->getEarlyBirdDeadline());
        self::assertEquals(new \DateTime('2022-04-01 10:00'), $result->getRegistrationDeadline());
        self::assertTrue($result->isRegistrationRequired());
        self::assertTrue($result->hasWaitingList());
        self::assertSame(5, $result->getMinimumNumberOfRegistrations());
        self::assertSame(20, $result->getMaximumNumberOfRegistrations());
        self::assertEqualsWithDelta(150.0, $result->getStandardPrice(), 0.0001);
        self::assertEqualsWithDelta(125.0, $result->getEarlyBirdPrice(), 0.0001);
        self::assertSame(15, $result->getOwnerUid());
        self::assertNull($result->getEventType());
        self::assertTrue($result->hasAdditionalTerms());
        self::assertTrue($result->isMultipleRegistrationPossible());
        self::assertSame(5, $result->getNumberOfOfflineRegistrations());
        self::assertSame(EventInterface::STATUS_CONFIRMED, $result->getStatus());
        self::assertEqualsWithDelta(100.0, $result->getSpecialPrice(), 0.0001);
        self::assertEqualsWithDelta(75.0, $result->getSpecialEarlyBirdPrice(), 0.0001);
    }

    /**
     * @test
     */
    public function mapsNotSetDateTimesForSingleEventAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithoutData.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/EventTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(EventTopic::class, $result);
        self::assertSame('Jousting topic', $result->getInternalTitle());
        self::assertSame('Jousting topic', $result->getDisplayTitle());
        self::assertSame('There is no glory in prevention.', $result->getDescription());
        self::assertEqualsWithDelta(150.0, $result->getStandardPrice(), 0.0001);
        self::assertEqualsWithDelta(125.0, $result->getEarlyBirdPrice(), 0.0001);
        self::assertSame(15, $result->getOwnerUid());
        self::assertNull($result->getEventType());
        self::assertTrue($result->hasAdditionalTerms());
        self::assertTrue($result->isMultipleRegistrationPossible());
        self::assertEqualsWithDelta(100.0, $result->getSpecialPrice(), 0.0001);
        self::assertEqualsWithDelta(75.0, $result->getSpecialEarlyBirdPrice(), 0.0001);
    }

    /**
     * @test
     */
    public function mapsEventDateWithAllFields(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateAndTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(EventDate::class, $result);
        self::assertSame('Jousting date', $result->getInternalTitle());
        self::assertSame('Jousting topic', $result->getDisplayTitle());
        self::assertSame('There is no glory in prevention.', $result->getDescription());
        self::assertEquals(new \DateTime('2022-04-02 10:00'), $result->getStart());
        self::assertEquals(new \DateTime('2022-04-03 18:00'), $result->getEnd());
        self::assertEquals(new \DateTime('2022-01-01 00:00'), $result->getRegistrationStart());
        self::assertEquals(new \DateTime('2022-03-02 10:00'), $result->getEarlyBirdDeadline());
        self::assertEquals(new \DateTime('2022-04-01 10:00'), $result->getRegistrationDeadline());
        self::assertTrue($result->isRegistrationRequired());
        self::assertTrue($result->hasWaitingList());
        self::assertSame(5, $result->getMinimumNumberOfRegistrations());
        self::assertSame(20, $result->getMaximumNumberOfRegistrations());
        self::assertSame(15, $result->getOwnerUid());
        self::assertSame(5, $result->getNumberOfOfflineRegistrations());
        self::assertSame(EventInterface::STATUS_CONFIRMED, $result->getStatus());
    }

    /**
     * @test
     */
    public function mapsNotSetDateTimesForEventDateAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithoutData.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateAndTopicWithAllFields.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        self::assertNull($result->getEventType());
    }

    /**
     * @test
     */
    public function mapsEmptyEventTypeAssociationForEventTopicAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventTopic::class, $result);

        self::assertNull($result->getEventType());
    }

    /**
     * @test
     */
    public function mapsEmptyEventTypeAssociationForEventDateAsNull(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateAndTopicWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        self::assertNull($result->getEventType());
    }

    /**
     * @test
     */
    public function mapsEventTypeAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithEventType.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/EventTopicWithEventType.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateAndTopicWithEventType.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithVenue.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getVenues();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Venue::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsVenuesAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithVenue.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getVenues();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Venue::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsSpeakersAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithSpeaker.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getSpeakers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Speaker::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsSpeakersAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithSpeaker.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getSpeakers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Speaker::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsOrganizersAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithOrganizer.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getOrganizers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Organizer::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsOrganizersAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithOrganizer.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getOrganizers();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Organizer::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsAccommodationOptionsAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithAccommodationOption.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getAccommodationOptions();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(AccommodationOption::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsAccommodationOptionsAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithAccommodationOption.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getAccommodationOptions();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(AccommodationOption::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsFoodOptionsAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithFoodOption.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getFoodOptions();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(FoodOption::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsFoodOptionsAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithFoodOption.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getFoodOptions();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(FoodOption::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsRegistrationCheckboxesAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithRegistrationCheckbox.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getRegistrationCheckboxes();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(RegistrationCheckbox::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsRegistrationCheckboxesAssociationForEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithRegistrationCheckbox.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getRegistrationCheckboxes();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(RegistrationCheckbox::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsPaymentMethodsAssociationForSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithPaymentMethod.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getPaymentMethods();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(PaymentMethod::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsPaymentMethodsAssociationForEventTopic(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTopicWithPaymentMethod.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventTopic::class, $result);

        $associatedModels = $result->getPaymentMethods();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(PaymentMethod::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidFindsSingleEventWithTheProvidedOwnerUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithOwner.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithoutOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(0);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidFindsSingleEventWithTheProvidedOwnerUidOnAnyPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithOwnerOnPage.xml');

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
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidIgnoresEventDatesWithTheProvidedOwnerUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidIgnoresEventTopicsWithTheProvidedOwnerUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTopicWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidSortsEventByInternalTitleInAscendingOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoSingleEventsWithOwner.xml');

        $result = $this->subject->findSingleEventsByOwnerUid(42);

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function updateRegistrationCounterCacheForNoRegistrationsSetsCounterCacheAtZero(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithAllFields.xml');
        $event = $this->subject->findByUid(1);

        $this->subject->updateRegistrationCounterCache($event);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $query = 'SELECT * FROM tx_seminars_seminars WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => 1]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }
        self::assertIsArray($databaseRow);

        self::assertSame(0, (int)$databaseRow['registrations']);
    }

    /**
     * @test
     */
    public function updateRegistrationCounterCacheForRegistrationsSetsCounterCacheToRegistrationsCount(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithTwoRegistrationsWithZeroCounterCache.xml');
        $event = $this->subject->findByUid(1);

        $this->subject->updateRegistrationCounterCache($event);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $query = 'SELECT * FROM tx_seminars_seminars WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => 1]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }
        self::assertIsArray($databaseRow);

        self::assertSame(2, (int)$databaseRow['registrations']);
    }

    /**
     * @test
     */
    public function updateRegistrationCounterCacheIgnoresHiddenRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithHiddenRegistrationWithZeroCounterCache.xml');
        $event = $this->subject->findByUid(1);

        $this->subject->updateRegistrationCounterCache($event);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $query = 'SELECT * FROM tx_seminars_seminars WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => 1]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }
        self::assertIsArray($databaseRow);

        self::assertSame(0, (int)$databaseRow['registrations']);
    }

    /**
     * @test
     */
    public function updateRegistrationCounterCacheIgnoresDeletedRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventWithHiddenRegistrationWithZeroCounterCache.xml');
        $event = $this->subject->findByUid(1);

        $this->subject->updateRegistrationCounterCache($event);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $query = 'SELECT * FROM tx_seminars_seminars WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => 1]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }
        self::assertIsArray($databaseRow);

        self::assertSame(0, (int)$databaseRow['registrations']);
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeForNoEventsReturnsEmptyArray(): void
    {
        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(0);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeFindsSingleEventOnGivenPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.xml');

        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeFindsEventDateOnGivenPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventDateOnPage.xml');

        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventDate::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeIgnoresEventTopicOnOtherPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventTopicOnPage.xml');

        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeIgnoresSingleEventOnOtherPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.xml');

        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(2);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeIgnoresDeletedEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.xml');

        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeCanFindHiddenEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.xml');

        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBookableEventsByPageUidInBackEndModeSortsEventByBeginDateInDescendingOrder(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoSingleEventsOnPage.xml');

        $result = $this->subject->findBookableEventsByPageUidInBackEndMode(1);

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }
}
