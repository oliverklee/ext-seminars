<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Domain\Repository\Registration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\AttendeesTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\BillingAddressTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\PaymentTrait
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\Registration
 * @covers \OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository
 */
final class RegistrationRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
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

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            $this->subject = GeneralUtility::makeInstance(RegistrationRepository::class);
            $this->eventRepository = GeneralUtility::makeInstance(EventRepository::class);
            $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        } else {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $this->subject = $objectManager->get(RegistrationRepository::class);
            $this->eventRepository = $objectManager->get(EventRepository::class);
            $this->persistenceManager = $objectManager->get(PersistenceManager::class);
        }
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
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationOnPage.xml');

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
}
