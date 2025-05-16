<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository\Event;

use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Category;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventStatistics;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Tests\Support\BackEndTestsTrait;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\Event
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDate
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventDateTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopic
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTopicTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\EventTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Event\SingleEvent
 * @covers \OliverKlee\Seminars\Domain\Model\RawDataTrait
 * @covers \OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository
 * @covers \OliverKlee\Seminars\Domain\Repository\Event\EventRepository
 */
final class EventRepositoryTest extends FunctionalTestCase
{
    use BackEndTestsTrait;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    private EventRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(EventRepository::class);
    }

    private function initializeBackEndUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/BackEndUser.csv');
        $this->setUpBackendUser(1);
        $this->unifyBackEndLanguage();
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }

    /**
     * @test
     */
    public function isRawDataCapableRepository(): void
    {
        self::assertInstanceOf(AbstractRawDataCapableRepository::class, $this->subject);
    }

    /**
     * @test
     */
    public function mapsSingleEventWithAllFields(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/SingleEventWithAllFields.csv');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(SingleEvent::class, $result);
        self::assertFalse($result->isHidden());
        self::assertSame('Jousting', $result->getInternalTitle());
        self::assertSame('Jousting', $result->getDisplayTitle());
        self::assertSame('There is no glory in prevention.', $result->getDescription());
        self::assertEquals(new \DateTime('2022-04-02 10:00'), $result->getStart());
        self::assertEquals(new \DateTime('2022-04-03 18:00'), $result->getEnd());
        self::assertEquals(new \DateTime('2022-01-01 00:00'), $result->getRegistrationStart());
        self::assertEquals(new \DateTime('2022-03-02 10:00'), $result->getEarlyBirdDeadline());
        self::assertEquals(new \DateTime('2022-04-01 10:00'), $result->getRegistrationDeadline());
        self::assertEquals(new \DateTime('2023-04-01 10:00'), $result->getDownloadStartDate());
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
        self::assertSame(EventDateInterface::EVENT_FORMAT_ONLINE, $result->getEventFormat());
        self::assertSame('https://webinar.example.com/', $result->getWebinarUrl());
        self::assertSame('Some more text for the attendees', $result->getAdditionalEmailText());
        self::assertSame('conference room 2', $result->getRoom());
        self::assertSame('jousting/1', $result->getSlug());
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
        $databaseRow = $result->fetchAssociative();

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
        $databaseRow = $result->fetchAssociative();

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
        $databaseRow = $result->fetchAssociative();

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
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
        self::assertSame(EventInterface::TYPE_EVENT_DATE, $databaseRow['object_type']);
    }

    /**
     * @test
     */
    public function canPersistsSingleEventWithStatistics(): void
    {
        $event = new SingleEvent();
        $event->setStatistics(new EventStatistics(0, 0, 0, 0, 0, false));
        $this->subject->add($event);
        $this->subject->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_seminars WHERE uid = :uid', ['uid' => $event->getUid()]);
        $databaseRow = $result->fetchAssociative();

        self::assertIsArray($databaseRow);
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/SingleEventWithAllFields.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        self::assertNull($result->getEventType());
    }

    /**
     * @test
     */
    public function mapsDeletedEventTypeAssociationForSingleEventAsNull(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/SingleEventWithDeletedEventType.csv');

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
    public function mapsDownloadsForAttendeesAssociationForSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/SingleEventWithOneDownloadForAttendees.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getDownloadsForAttendees();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(FileReference::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsDownloadsForAttendeesAssociationForEventDate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/EventDateWithOneDownloadForAttendees.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventDate::class, $result);

        $associatedModels = $result->getDownloadsForAttendees();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(FileReference::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsCategoriesAssociationForSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/SingleEventWithCategory.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $result);

        $associatedModels = $result->getCategories();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Category::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsCategoriesAssociationForEventTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/EventTopicWithCategory.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(EventTopic::class, $result);

        $associatedModels = $result->getCategories();
        self::assertCount(1, $associatedModels);
        self::assertInstanceOf(Category::class, $associatedModels->toArray()[0]);
    }

    /**
     * @test
     */
    public function findOneByUidForBackendCanFindVisibleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findOneByUidForBackend(1);

        self::assertInstanceOf(SingleEvent::class, $result);
    }

    /**
     * @test
     */
    public function findOneByUidForBackendIgnoresDeletedEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');

        $result = $this->subject->findOneByUidForBackend(1);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function findOneByUidForBackendCanFindHiddenEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $result = $this->subject->findOneByUidForBackend(1);

        self::assertInstanceOf(SingleEvent::class, $result);
        self::assertTrue($result->isHidden());
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidFindsSingleEventWithTheProvidedOwnerUid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findSingleEventsByOwnerUid/SingleEventWithOwner.csv');

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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findSingleEventsByOwnerUid/SingleEventWithoutOwner.csv');

        $result = $this->subject->findSingleEventsByOwnerUid(0);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidFindsSingleEventWithTheProvidedOwnerUidOnAnyPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findSingleEventsByOwnerUid/SingleEventWithOwnerOnPage.csv');

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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findSingleEventsByOwnerUid/SingleEventWithOwner.csv');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidIgnoresEventDatesWithTheProvidedOwnerUid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findSingleEventsByOwnerUid/EventDateWithOwner.csv');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidIgnoresEventTopicsWithTheProvidedOwnerUid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findSingleEventsByOwnerUid/EventTopicWithOwner.csv');

        $result = $this->subject->findSingleEventsByOwnerUid(5);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findSingleEventsByOwnerUidSortsEventByInternalTitleInAscendingOrder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findSingleEventsByOwnerUid/TwoSingleEventsWithOwner.csv');

        $result = $this->subject->findSingleEventsByOwnerUid(42);

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function updateRegistrationCounterCacheForNoRegistrationsSetsCounterCacheToZero(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/SingleEventWithAllFields.csv');
        $event = $this->subject->findByUid(1);

        $this->subject->updateRegistrationCounterCache($event);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars');
        $query = 'SELECT * FROM tx_seminars_seminars WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => 1]);
        $databaseRow = $result->fetchAssociative();
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
        $databaseRow = $result->fetchAssociative();
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
        $databaseRow = $result->fetchAssociative();
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
        $databaseRow = $result->fetchAssociative();
        self::assertIsArray($databaseRow);

        self::assertSame(0, (int)$databaseRow['registrations']);
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeForNoEventsReturnsEmptyArray(): void
    {
        $result = $this->subject->findByPageUidInBackEndMode(0);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeFindsSingleEventOnGivenPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeFindsEventDateOnGivenPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventDateOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventDate::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeFindsEventTopicOnGivenPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventTopicOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeIgnoresSingleEventOnOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(2);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeIgnoresDeletedEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeCanFindHiddenEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeCanFindTimedEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TimedSingleEventOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(1);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidInBackEndModeSortsEventByBeginDateInDescendingOrder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TwoSingleEventsOnPage.csv');

        $result = $this->subject->findByPageUidInBackEndMode(1);

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @return array<non-empty-string, array{0: string}>
     */
    public static function emptySearchTermDataProvider(): array
    {
        return [
            'empty string' => [''],
            'whitespace only' => [" \t\n\r"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermForNoEventsReturnsEmptyArray(
        string $searchTerm
    ): void {
        $result = $this->subject->findBySearchTermInBackEndMode(0, $searchTerm);

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermFindsSingleEventOnGivenPage(
        string $searchTerm
    ): void {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermFindsEventDateOnGivenPage(string $searchTerm): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventDateOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventDate::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermFindsEventTopicOnGivenPage(string $searchTerm): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventTopicOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermIgnoresSingleEventOnOtherPage(
        string $searchTerm
    ): void {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(2, $searchTerm);

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermIgnoresDeletedEvent(string $searchTerm): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertSame([], $result);
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermCanFindHiddenEvent(string $searchTerm): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermCanFindTimedEvent(string $searchTerm): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TimedSingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     *
     * @dataProvider emptySearchTermDataProvider
     */
    public function findBySearchTermInBackEndModeWithEmptySearchTermSortsEventByBeginDateInDescendingOrder(
        string $searchTerm
    ): void {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TwoSingleEventsOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleSingleEventWithMatchingUidOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '1');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleEventDateWithMatchingUidOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventDateOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '1');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventDate::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleEventTopicWithMatchingUidOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventTopicOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '1');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleEventWithMatchingUidMinusWhitespaceOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, ' 1 ');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsHiddenEventWithMatchingUidOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '1');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsTimedEventWithMatchingUidOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TimedSingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '1');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresDeletedEventWithMatchingUidOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '1');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresVisibleEventWithMatchingUidOnOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(2, '1');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresVisibleEventWithNonMatchingUidOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '15');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresVisibleEventWithUidOnlyInTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithIntegerTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '9');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresVisibleEventWithSubstringUidOnlyInTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithWhitespaceIntegerTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, '9');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleSingleEventWithExactlyMatchingTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'single event');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleSingleEventWithLeftSubstringMatch(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'sing');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleSingleEventWithRightSubstringMatch(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'vent');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleSingleEventWithMiddleStringMatch(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'gle ev');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleSingleEventWithSingleCharacterSubstringMatch(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'e');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string}>
     */
    public static function sqlCharactersDataProvider(): array
    {
        return [
            ';' => [';'],
            ',' => [','],
            '(' => ['('],
            ')' => [')'],
            'double quote' => ['"'],
            'single quote' => ["'"],
            '%' => ['%'],
            '-' => ['-'],
            '_' => ['_'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $searchTerm
     *
     * @dataProvider sqlCharactersDataProvider
     */
    public function findBySearchTermInBackEndModeFindsVisibleSingleEventWithSqlCharacterMatch(string $searchTerm): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithSqlCharactersTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, $searchTerm);

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeSortsEventByBeginDateInDescendingOrder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TwoSingleEventsOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'single event');

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleEventDateWithMatchingTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventDateWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'event date');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventDate::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleEventTopicWithMatchingTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventTopicWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'event topic');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsVisibleEventWithTitleMatchMinusWhitespaceOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, ' single event ');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsHiddenEventWithMatchingTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'Hidden single event on page');

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeFindsTimedEventWithMatchingTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/TimedSingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(
            1,
            'Timed single event with title on page'
        );

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresDeletedEventWithMatchingTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(
            1,
            'Deleted single event with title on page'
        );

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresVisibleEventWithMatchingTitleOnOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(2, 'single event');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresVisibleEventWithCompletelyDifferentTitleOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'no dice');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findBySearchTermInBackEndModeIgnoresVisibleEventWithTwoSearchWordsInReverseOrder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventWithTitleOnPage.csv');

        $result = $this->subject->findBySearchTermInBackEndMode(1, 'event single');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function enrichWithRawDataCanBeCalledWithEmptyArray(): void
    {
        $events = [];

        $this->subject->enrichWithRawData($events);

        self::assertSame([], $events);
    }

    /**
     * @test
     */
    public function enrichWithRawDataAddsRawDataToEvents(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/SingleEventWithAllFields.csv');
        $event = $this->subject->findByUid(1);
        self::assertInstanceOf(SingleEvent::class, $event);
        $events = [$event];

        $this->subject->enrichWithRawData($events);

        $rawData = $event->getRawData();
        self::assertIsArray($rawData);
        self::assertSame(1, $rawData['uid']);
        self::assertSame('Jousting', $rawData['title']);
    }

    /**
     * @test
     */
    public function enrichWithRawDataCanEnrichHiddenEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventWithTitleOnPage.csv');
        $events = $this->subject->findByPageUidInBackEndMode(1);
        $this->subject->findByUid(1);
        self::assertCount(1, $events);
        $event = $events[0];
        self::assertInstanceOf(SingleEvent::class, $event);

        $this->subject->enrichWithRawData($events);

        $rawData = $event->getRawData();
        self::assertIsArray($rawData);
        self::assertSame(1, $rawData['uid']);
        self::assertSame('Hidden single event on page', $rawData['title']);
    }

    /**
     * @test
     */
    public function hideViaDataHandlerWithUidOfExistingVisibleEventMarksEventAsHidden(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $this->subject->hideViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function hideViaDataHandlerWithUidOfInexistentEventKeepsOtherVisibleEventsUnchanged(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $this->subject->hideViaDataHandler(2);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function hideViaDataHandlerWithUidOfExistingHiddenEventKeepsEventHidden(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $this->subject->hideViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function hideViaDataHandlerWithUidOfDeletedVisibleEventKeepsDeletedEventVisible(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');

        $this->subject->hideViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function unhideViaDataHandlerWithUidOfExistingHiddenEventMarksEventAsVisible(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $this->subject->unhideViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function unhideViaDataHandlerWithUidOfInexistentEventKeepsOtherHiddenEventsUnchanged(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $this->subject->unhideViaDataHandler(2);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function unhideViaDataHandlerWithUidOfExistingVisibleEventKeepsEventVisible(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $this->subject->unhideViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function unhideViaDataHandlerWithUidOfDeletedHiddenEventKeepsDeletedEventHidden(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedHiddenSingleEventOnPage.csv');

        $this->subject->unhideViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedHiddenSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfExistingVisibleEventMarksEventAsDeleted(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $this->subject->deleteViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfExistingHiddenEventMarksEventAsDeleted(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenSingleEventOnPage.csv');

        $this->subject->deleteViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedHiddenSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfInexistentEventKeepsOtherVisibleEventsUnchanged(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');

        $this->subject->deleteViaDataHandler(2);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/SingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfExistingDeletedEventKeepsEventDeleted(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');

        $this->subject->deleteViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedSingleEventOnPage.csv');
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerCreatesCopyOnSamePage(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventOnPage.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventAndDuplicateOnPage.csv');
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerMakesCopyHidden(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/VisibleSingleEvent.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/VisibleSingleEventAndDuplicate.csv');
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerCanCreateCopyOfHiddenEvent(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/HiddenSingleEvent.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/HiddenSingleEventAndDuplicate.csv');
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerCopiesScalarData(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithScalarData.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithScalarDataAndDuplicate.csv'
        );
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerCopiesToOneRelations(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithToOneRelations.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithToOneRelationsAndDuplicate.csv'
        );
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerKeepsTopicForDate(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/EventDate.csv');

        $this->subject->duplicateViaDataHandler(2);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/EventDateAndDuplicate.csv');
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerCopiesToManyRelations(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithOrganizer.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithOrganizerAndDuplicate.csv');
    }

    /**
     * @test
     */
    public function duplicateViaDataHandlerSetsSomeFieldsToDefaultValues(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithDataToReset.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventAndDuplicateWithResetData.csv');
    }

    /**
     * @test
     *
     * @deprecated #1324 will be removed in seminars 6.0
     */
    public function duplicateViaDataHandlerForEventWithRegistrationsResetsRegistrationsCounterToZero(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithOneRegistration.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithOneRegistrationAndDuplicateWithoutRegistrations.csv'
        );
    }

    /**
     * @test
     *
     * @deprecated #1324 will be removed in seminars 6.0
     */
    public function duplicateViaDataHandlerForEventWithRegistrationsDoesNotDuplicateRegistrations(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithOneRegistration.csv');

        $this->subject->duplicateViaDataHandler(1);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/duplicateViaDataHandler/SingleEventWithOneRegistrationAndDuplicateWithRegistrations.csv'
        );
    }

    /**
     * @test
     */
    public function findInPastForNoEventsReturnsEmptyResult(): void
    {
        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastFindsPlannedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/PlannedSingleEventInPast.csv');

        $result = $this->subject->findInPast();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findInPastIgnoresHiddenPlannedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/HiddenPlannedSingleEventInPast.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresDeletedPlannedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/DeletedPlannedSingleEventInPast.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastFindsConfirmedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/ConfirmedSingleEventInPast.csv');

        $result = $this->subject->findInPast();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findInPastIgnoresCanceledSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/CanceledSingleEventInPast.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresPlannedSingleEventWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/PlannedSingleEventInFuture.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresConfirmedSingleEventWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/ConfirmedSingleEventInFuture.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresCanceledSingleEventWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/CanceledSingleEventInFuture.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresCurrentlyRunningSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/PlannedRunningSingleEvent.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresPlannedSingleEventWithoutStartAndWithoutEnd(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/PlannedSingleEventWithoutStartAndWithoutEnd.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresSingleEventWithPastStartAndWithoutEnd(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/PlannedSingleEventWithPastStartAndWithoutEnd.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastIgnoresSingleEventWithoutStartAndWithPastEnd(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/PlannedSingleEventWithoutStartAndWithPastEnd.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastFindsPlannedEventDateWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/PlannedEventDateInPast.csv');

        $result = $this->subject->findInPast();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventDate::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findInPastIgnoresEventTopicWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/EventTopicInPast.csv');

        $result = $this->subject->findInPast();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findInPastOrdersSubsequentEventsFromLatestToOldest(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/TwoSubsequentPlannedSingleEventsInPast.csv');

        $result = $this->subject->findInPast();

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findInPastOrdersOverlappingEventsFromLatestToOldestStartDate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findInPast/TwoOverlappingPlannedSingleEventsInPast.csv');

        $result = $this->subject->findInPast();

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findUpcomingForNoEventsReturnsEmptyResult(): void
    {
        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresPlannedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/PlannedSingleEventInPast.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresHiddenPlannedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/HiddenPlannedSingleEventInPast.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresDeletedPlannedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/DeletedPlannedSingleEventInPast.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresConfirmedSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/ConfirmedSingleEventInPast.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresCanceledSingleEventWithStartAndEndInPast(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/CanceledSingleEventInPast.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingFindsPlannedSingleEventWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/PlannedSingleEventInFuture.csv');

        $result = $this->subject->findUpcoming();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findUpcomingFindsConfirmedSingleEventWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/ConfirmedSingleEventInFuture.csv');

        $result = $this->subject->findUpcoming();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresCanceledSingleEventWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/CanceledSingleEventInFuture.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingFindsCurrentlyRunningSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/PlannedRunningSingleEvent.csv');

        $result = $this->subject->findUpcoming();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findUpcomingFindsPlannedSingleEventWithoutStartAndWithoutEnd(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/PlannedSingleEventWithoutStartAndWithoutEnd.csv');

        $result = $this->subject->findUpcoming();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(1, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresSingleEventWithPastStartAndWithoutEnd(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/PlannedSingleEventWithPastStartAndWithoutEnd.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresSingleEventWithOutStartAndWithEndInFuture(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/findUpcoming/PlannedSingleEventWithoutStartAndWithEndInFuture.csv'
        );

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresSingleEventWithoutStartAndWithPastEnd(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/PlannedSingleEventWithoutStartAndWithPastEnd.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingFindsPlannedEventDateWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/PlannedEventDateInFuture.csv');

        $result = $this->subject->findUpcoming();

        self::assertCount(1, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(EventDate::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findUpcomingIgnoresEventTopicWithStartAndEndInFuture(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/EventTopicInFuture.csv');

        $result = $this->subject->findUpcoming();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findUpcomingOrdersSubsequentEventsFromOldestLatest(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/TwoSubsequentPlannedSingleEventsInFuture.csv');

        $result = $this->subject->findUpcoming();

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findUpcomingOrdersOverlappingEventsFromLatestToOldestStartDate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findUpcoming/TwoOverlappingPlannedSingleEventsInFuture.csv');

        $result = $this->subject->findUpcoming();

        self::assertCount(2, $result);
        $firstMatch = $result[0];
        self::assertInstanceOf(SingleEvent::class, $firstMatch);
        self::assertSame(2, $firstMatch->getUid());
    }

    /**
     * @test
     */
    public function findAllTopicsFindsEventTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findAllTopics/EventTopic.csv');

        $result = $this->subject->findAllTopics();

        self::assertCount(1, $result);
        $firstMatch = $result->toArray()[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
    }

    /**
     * @test
     */
    public function findAllTopicsIgnoresEventDate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findAllTopics/EventDate.csv');

        $result = $this->subject->findAllTopics();

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findAllTopicsIgnoresSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findAllTopics/SingleEvent.csv');

        $result = $this->subject->findAllTopics();

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findAllTopicsFindsTopicOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findAllTopics/EventTopicOnPage.csv');

        $result = $this->subject->findAllTopics();

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function findAllTopicsIgnoresHiddenTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findAllTopics/HiddenEventTopic.csv');

        $result = $this->subject->findAllTopics();

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findAllTopicsIgnoresDeletedTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findAllTopics/DeletedEventTopic.csv');

        $result = $this->subject->findAllTopics();

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findAllTopicsOrdersByInternalTitleInAscendingOrder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findAllTopics/TwoEventTopicsInReverseOrder.csv');

        $result = $this->subject->findAllTopics();

        self::assertCount(2, $result);
        $firstMatch = $result->toArray()[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
        self::assertSame('acrobatics', $firstMatch->getInternalTitle());
    }

    /**
     * @test
     */
    public function findTopicsByUidsFindsEventTopicWithMatchingOnlyUid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/EventTopic.csv');

        $result = $this->subject->findTopicsByUids([1]);

        self::assertCount(1, $result);
        $firstMatch = $result->toArray()[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
    }

    /**
     * @test
     */
    public function findTopicsByUidsFindsEventTopicWithMatchingFirstUidOfTwo(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/EventTopic.csv');

        $result = $this->subject->findTopicsByUids([1, 2]);

        self::assertCount(1, $result);
        $firstMatch = $result->toArray()[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
    }

    /**
     * @test
     */
    public function findTopicsByUidsFindsEventTopicWithMatchingLastUidOfTwo(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/EventTopic.csv');

        $result = $this->subject->findTopicsByUids([2, 1]);

        self::assertCount(1, $result);
        $firstMatch = $result->toArray()[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
    }

    /**
     * @test
     */
    public function findTopicsByUidsIgnoresEventTopicWithNonMatchingUid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/EventTopic.csv');

        $result = $this->subject->findTopicsByUids([2]);

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findTopicsByUidsIgnoresEventDateWithMatchingUid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/EventDate.csv');

        $result = $this->subject->findTopicsByUids([1]);

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findTopicsByUidsIgnoresSingleEventWithMatchingUid(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/SingleEvent.csv');

        $result = $this->subject->findTopicsByUids([1]);

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findTopicsByUidsFindsTopicOnPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/EventTopicOnPage.csv');

        $result = $this->subject->findTopicsByUids([1]);

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function findTopicsByUidsIgnoresHiddenTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/HiddenEventTopic.csv');

        $result = $this->subject->findTopicsByUids([1]);

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findTopicsByUidsIgnoresDeletedTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/DeletedEventTopic.csv');

        $result = $this->subject->findTopicsByUids([1]);

        self::assertCount(0, $result);
    }

    /**
     * @test
     */
    public function findTopicsByUidsOrdersByInternalTitleInAscendingOrder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/findTopicsByUids/TwoEventTopicsInReverseOrder.csv');

        $result = $this->subject->findTopicsByUids([1, 2]);

        self::assertCount(2, $result);
        $firstMatch = $result->toArray()[0];
        self::assertInstanceOf(EventTopic::class, $firstMatch);
        self::assertSame('acrobatics', $firstMatch->getInternalTitle());
    }
}
