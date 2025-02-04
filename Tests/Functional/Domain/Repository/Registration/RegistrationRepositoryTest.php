<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository\Registration;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Tests\Support\BackEndTestsTrait;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\RawDataTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\AttendeesTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\BillingAddressTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\PaymentTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\Registration
 * @covers \OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository
 * @covers \OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository
 */
final class RegistrationRepositoryTest extends FunctionalTestCase
{
    use BackEndTestsTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var RegistrationRepository
     */
    private $subject;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var PersistenceManager
     */
    private $persistenceManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(RegistrationRepository::class);
        $this->eventRepository = $this->get(EventRepository::class);
        $this->persistenceManager = $this->get(PersistenceManager::class);
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
    public function mapsAllModelFieldsFromTheBaseModel(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertSame('some new registration', $result->getTitle());
        self::assertNull($result->getEvent());
        self::assertTrue($result->isOnWaitingList());
        self::assertSame('escapism', $result->getInterests());
        self::assertSame('fast escapes', $result->getExpectations());
        self::assertSame('Looking forward to the event!', $result->getComments());
        self::assertSame('the internet', $result->getKnownFrom());
        self::assertSame('crash course', $result->getBackgroundKnowledge());
        self::assertTrue($result->hasSeparateBillingAddress());
    }

    /**
     * @test
     */
    public function mapsAllModelFieldsFromTheAttendeesTrait(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAllFields.xml');

        $result = $this->subject->findByUid(1);

        self::assertInstanceOf(Registration::class, $result);
        self::assertNull($result->getUser());
        self::assertSame(3, $result->getSeats());
        self::assertTrue($result->hasRegisteredThemselves());
        self::assertSame('Max und Moritz', $result->getAttendeesNames());
    }

    /**
     * @test
     */
    public function mapsAllModelFieldsFromTheBillingAddressTrait(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertSame('ACME Inc.', $result->getBillingCompany());
        self::assertSame('Dan Chase', $result->getBillingFullName());
        self::assertSame('Abenteuerland 5', $result->getBillingStreetAddress());
        self::assertSame('01234', $result->getBillingZipCode());
        self::assertSame('Solar City', $result->getBillingCity());
        self::assertSame('Solar Country', $result->getBillingCountry());
        self::assertSame('+49 0000 1928765', $result->getBillingPhoneNumber());
        self::assertSame('billing@example.com', $result->getBillingEmailAddress());
    }

    /**
     * @test
     */
    public function mapsAllModelFieldsFromThePaymentTrait(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAllFields.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertSame(Price::PRICE_EARLY_BIRD, $result->getPriceCode());
        self::assertSame('Standard price: 200€', $result->getHumanReadablePrice());
        self::assertSame(199.99, $result->getTotalPrice());
        self::assertNull($result->getPaymentMethod());
    }

    /**
     * @test
     */
    public function findsRecordOnPages(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.csv');

        $result = $this->subject->findAll();

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function canPersistModel(): void
    {
        $registration = new Registration();

        $this->subject->add($registration);
        $this->persistenceManager->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_attendances');
        $query = 'SELECT * FROM tx_seminars_attendances WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => $registration->getUid()]);
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
    public function persistAllPersistsAddedModels(): void
    {
        $registration = new Registration();

        $this->subject->add($registration);
        $this->subject->persistAll();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_attendances');
        $query = 'SELECT * FROM tx_seminars_attendances WHERE uid = :uid';
        $result = $connection->executeQuery($query, ['uid' => $registration->getUid()]);
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
    public function mapsEventAssociationWithSingleEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithSingleEvent.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertInstanceOf(SingleEvent::class, $result->getEvent());
    }

    /**
     * @test
     */
    public function mapsDeletedEventAssociationAsNull(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/RegistrationWithDeletedEvent.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertNull($result->getEvent());
    }

    /**
     * @test
     */
    public function mapsEventAssociationWithEventDate(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventDate.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertInstanceOf(EventDate::class, $result->getEvent());
    }

    /**
     * @test
     *
     * Note: This case usually should not happen. It is only possible if there are already registrations for a single
     * event of event topic, and the event then gets changed to an event topic.
     */
    public function mapsEventAssociationWithEventTopic(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventTopic.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertInstanceOf(EventTopic::class, $result->getEvent());
    }

    /**
     * @test
     */
    public function mapsUserAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithUser.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertInstanceOf(FrontendUser::class, $result->getUser());
    }

    /**
     * @test
     */
    public function mapsDeletedUserAssociationAsNull(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/RegistrationWithDeletedUser.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertNull($result->getUser());
    }

    /**
     * @test
     */
    public function mapsAdditionalPersonsAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAdditionalPerson.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        $association = $result->getAdditionalPersons();
        self::assertInstanceOf(ObjectStorage::class, $association);
        self::assertCount(1, $association);
        self::assertInstanceOf(FrontendUser::class, $association->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsAccommodationOptionsAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAccommodationOption.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        $association = $result->getAccommodationOptions();
        self::assertInstanceOf(ObjectStorage::class, $association);
        self::assertCount(1, $association);
        self::assertInstanceOf(AccommodationOption::class, $association->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsFoodOptionsAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithFoodOption.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        $association = $result->getFoodOptions();
        self::assertInstanceOf(ObjectStorage::class, $association);
        self::assertCount(1, $association);
        self::assertInstanceOf(FoodOption::class, $association->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsRegistrationCheckboxesAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithRegistrationCheckbox.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        $association = $result->getRegistrationCheckboxes();
        self::assertInstanceOf(ObjectStorage::class, $association);
        self::assertCount(1, $association);
        self::assertInstanceOf(RegistrationCheckbox::class, $association->toArray()[0]);
    }

    /**
     * @test
     */
    public function mapsPaymentMethodAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithPaymentMethod.xml');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertInstanceOf(PaymentMethod::class, $result->getPaymentMethod());
    }

    /**
     * @test
     */
    public function mapsDeletedPaymentMethodAssociationAsNull(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/propertyMapping/RegistrationWithDeletedPaymentMethod.csv');

        $result = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $result);

        self::assertNull($result->getPaymentMethod());
    }

    /**
     * @test
     */
    public function existsRegistrationForEventAndUserForZeroUserUidReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');
        $event = $this->eventRepository->findByUid(1);

        self::assertFalse($this->subject->existsRegistrationForEventAndUser($event, 0));
    }

    /**
     * @test
     */
    public function existsRegistrationForEventAndOtherUserUidReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');
        $event = $this->eventRepository->findByUid(1);

        self::assertFalse($this->subject->existsRegistrationForEventAndUser($event, 2));
    }

    /**
     * @test
     */
    public function existsRegistrationForOtherEventAndThisUserUidReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUserAndAdditionalEvent.xml');
        $event = $this->eventRepository->findByUid(2);

        self::assertFalse($this->subject->existsRegistrationForEventAndUser($event, 1));
    }

    /**
     * @test
     */
    public function existsRegistrationForHiddenMatchingRegistrationReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenRegistrationWithEventAndUser.xml');
        $event = $this->eventRepository->findByUid(1);

        self::assertFalse($this->subject->existsRegistrationForEventAndUser($event, 1));
    }

    /**
     * @test
     */
    public function existsRegistrationForDeletedMatchingRegistrationReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedRegistrationWithEventAndUser.xml');
        $event = $this->eventRepository->findByUid(1);

        self::assertFalse($this->subject->existsRegistrationForEventAndUser($event, 1));
    }

    /**
     * @test
     */
    public function existsRegistrationMatchingRegistrationReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');
        $event = $this->eventRepository->findByUid(1);

        self::assertTrue($this->subject->existsRegistrationForEventAndUser($event, 1));
    }

    /**
     * @test
     */
    public function existsRegistrationTwoMatchingRegistrationsReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoRegistrationsWithSameEventAndUser.xml');
        $event = $this->eventRepository->findByUid(1);

        self::assertTrue($this->subject->existsRegistrationForEventAndUser($event, 1));
    }

    /**
     * @test
     */
    public function countRegularRegistrationsByPageUidForNoRegistrationsInDatabaseReturnsZero(): void
    {
        self::assertSame(0, $this->subject->countRegularRegistrationsByPageUid(1));
    }

    /**
     * @test
     */
    public function countRegularRegistrationsByPageUidCountsRegularRegistrationsOnTheGivenPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.csv');

        self::assertSame(1, $this->subject->countRegularRegistrationsByPageUid(1));
    }

    /**
     * @test
     */
    public function countRegularRegistrationsByPageUidIgnoresRegistrationsOnOtherPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.csv');

        self::assertSame(0, $this->subject->countRegularRegistrationsByPageUid(2));
    }

    /**
     * @test
     */
    public function countRegularRegistrationsByPageUidIgnoresWaitingListRegistrationsOnTheGivenPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/WaitingListRegistrationOnPage.xml');

        self::assertSame(0, $this->subject->countRegularRegistrationsByPageUid(1));
    }

    /**
     * @test
     */
    public function countRegularRegistrationsByPageUidIgnoresHiddenRegistrationsOnTheGivenPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenRegistrationOnPage.csv');

        self::assertSame(0, $this->subject->countRegularRegistrationsByPageUid(1));
    }

    /**
     * @test
     */
    public function countRegularRegistrationsByPageUidIgnoresDeletedRegistrationsOnTheGivenPage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedRegistrationOnPage.csv');

        self::assertSame(0, $this->subject->countRegularRegistrationsByPageUid(1));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventForNonExistentEventUidReturnsZero(): void
    {
        self::assertSame(0, $this->subject->countRegularSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventIgnoresRegistrationsWithZeroSeats(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');

        self::assertSame(0, $this->subject->countRegularSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventSumsUpSingleSeatRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoRegistrationsWithSameEventAndUser.xml');

        self::assertSame(2, $this->subject->countRegularSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventIgnoresSeatFromOtherEvents(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndAdditionalEvent.xml');

        self::assertSame(0, $this->subject->countRegularSeatsByEvent(2));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventSumsUpMultiSeatRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoMultiSeatRegistrationsWithSameEvent.xml');

        self::assertSame(5, $this->subject->countRegularSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventIgnoresHiddenRegistration(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenRegistrationWithEvent.xml');

        self::assertSame(0, $this->subject->countRegularSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventIgnoresDeletedRegistration(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedRegistrationWithEvent.xml');

        self::assertSame(0, $this->subject->countRegularSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countRegularSeatsByEventIgnoresRegistrationOnWaitingList(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/WaitingListRegistrationWithEvent.xml');

        self::assertSame(0, $this->subject->countRegularSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventForNonExistentEventUidReturnsZero(): void
    {
        self::assertSame(0, $this->subject->countWaitingListSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventIgnoresRegistrationsWithZeroSeats(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/WaitingListRegistrationWithZeroSeats.xml');

        self::assertSame(0, $this->subject->countWaitingListSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventSumsUpSingleSeatRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoWaitingListRegistrationsWithSameEventAndUser.xml');

        self::assertSame(2, $this->subject->countWaitingListSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventIgnoresSeatFromOtherEvents(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoWaitingListRegistrationsWithSameEventAndUser.xml');

        self::assertSame(0, $this->subject->countWaitingListSeatsByEvent(2));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventSumsUpMultiSeatRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoMultiSeatWaitingListRegistrationsWithSameEvent.xml');

        self::assertSame(5, $this->subject->countWaitingListSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventIgnoresHiddenRegistration(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenWaitingListRegistrationWithEvent.xml');

        self::assertSame(0, $this->subject->countWaitingListSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventIgnoresDeletedRegistration(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedWaitingListRegistrationWithEvent.xml');

        self::assertSame(0, $this->subject->countWaitingListSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function countWaitingListSeatsByEventIgnoresRegularOnWaitingList(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');

        self::assertSame(0, $this->subject->countWaitingListSeatsByEvent(1));
    }

    /**
     * @test
     */
    public function findRegularRegistrationsByEventForNoDataReturnsEmptyArray(): void
    {
        $result = $this->subject->findRegularRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findRegularRegistrationsByEventFindsRegularRegistrationsForTheGivenEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');

        $result = $this->subject->findRegularRegistrationsByEvent(1);

        self::assertCount(1, $result);
        $firstRegistration = $result[0];
        self::assertInstanceOf(Registration::class, $firstRegistration);
    }

    /**
     * @test
     */
    public function findRegularRegistrationsByEventIgnoresWaitingListRegistrationsForTheGivenEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/WaitingListRegistrationWithEvent.xml');

        $result = $this->subject->findRegularRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findRegularRegistrationsByEventFindsRegistrationsOnAnyPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUserOnPage.xml');

        $result = $this->subject->findRegularRegistrationsByEvent(1);

        self::assertCount(1, $result);
        $firstRegistration = $result[0];
        self::assertInstanceOf(Registration::class, $firstRegistration);
    }

    /**
     * @test
     */
    public function findRegularRegistrationsByEventIgnoresRegistrationsForDifferentEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');

        $result = $this->subject->findRegularRegistrationsByEvent(2);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findRegularRegistrationsByEventIgnoresHiddenRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenRegistrationWithEventAndUser.xml');

        $result = $this->subject->findRegularRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findRegularRegistrationsByEventIgnoresDeletedRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedRegistrationWithEventAndUser.xml');

        $result = $this->subject->findRegularRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findRegularRegistrationsOrdersByCreationDateNewestFirst(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoRegistrationsWithSameEventAndUser.xml');

        $result = $this->subject->findRegularRegistrationsByEvent(1);

        $firstRegistration = $result[0];
        self::assertInstanceOf(Registration::class, $firstRegistration);
        self::assertSame(2, $firstRegistration->getUid());
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventForNoDataReturnsEmptyArray(): void
    {
        $result = $this->subject->findWaitingListRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventFindsWaitingListRegistrationsForTheGivenEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/WaitingListRegistrationWithEvent.xml');

        $result = $this->subject->findWaitingListRegistrationsByEvent(1);

        self::assertCount(1, $result);
        $firstRegistration = $result[0];
        self::assertInstanceOf(Registration::class, $firstRegistration);
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventIgnoresRegularRegistrationsForTheGivenEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithEventAndUser.xml');

        $result = $this->subject->findWaitingListRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventFindsRegistrationsOnAnyPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/WaitingListRegistrationWithEventAndUserOnPage.xml');

        $result = $this->subject->findWaitingListRegistrationsByEvent(1);

        self::assertCount(1, $result);
        $firstRegistration = $result[0];
        self::assertInstanceOf(Registration::class, $firstRegistration);
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventIgnoresRegistrationsForDifferentEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/WaitingListRegistrationWithEvent.xml');

        $result = $this->subject->findWaitingListRegistrationsByEvent(2);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventIgnoresHiddenRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/HiddenWaitingListRegistrationWithEventAndUser.xml');

        $result = $this->subject->findWaitingListRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventIgnoresDeletedRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/DeletedWaitingListRegistrationWithEvent.xml');

        $result = $this->subject->findWaitingListRegistrationsByEvent(1);

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function findWaitingListRegistrationsByEventOrdersByCreationDateNewestFirst(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/TwoWaitingListRegistrationsWithSameEventAndUser.xml');

        $result = $this->subject->findWaitingListRegistrationsByEvent(1);

        $firstRegistration = $result[0];
        self::assertInstanceOf(Registration::class, $firstRegistration);
        self::assertSame(2, $firstRegistration->getUid());
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
    public function enrichWithRawDataAddsRawDataToRegistrations(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithAllFields.xml');
        $registration = $this->subject->findByUid(1);
        self::assertInstanceOf(Registration::class, $registration);
        $registrations = [$registration];

        $this->subject->enrichWithRawData($registrations);

        $rawData = $registration->getRawData();
        self::assertIsArray($rawData);
        self::assertSame(1, $rawData['uid']);
        self::assertSame('some new registration', $rawData['title']);
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfExistingVisibleRegistrationMarksRegistrationAsDeleted(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.csv');

        $this->subject->deleteViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedRegistrationOnPage.csv');
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfExistingHiddenRegistrationMarksRegistrationAsDeleted(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/HiddenRegistrationOnPage.csv');

        $this->subject->deleteViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedHiddenRegistrationOnPage.csv');
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfInexistentRegistrationKeepsOtherVisibleRegistrationsUnchanged(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.csv');

        $this->subject->deleteViaDataHandler(2);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.csv');
    }

    /**
     * @test
     */
    public function deleteViaDataHandlerWithUidOfExistingDeletedRegistrationKeepsRegistrationDeleted(): void
    {
        $this->initializeBackEndUser();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DeletedRegistrationOnPage.csv');

        $this->subject->deleteViaDataHandler(1);

        $this->assertCSVDataSet(__DIR__ . '/Fixtures/DeletedRegistrationOnPage.csv');
    }
}
