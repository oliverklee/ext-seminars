<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyRegistration
 */
final class LegacyRegistrationTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var LegacyRegistration
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int the UID of a seminar to which the fixture relates
     */
    private $seminarUid = 0;

    /**
     * @var int the UID of the user the registration relates to
     */
    private $feUserUid = 0;

    /** @var ConnectionPool */
    private $connectionPool = null;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        LegacyRegistration::purgeCachedSeminars();

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->configuration = new DummyConfiguration();
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            [
                'title' => 'test organizer',
                'email' => 'mail@example.com',
            ]
        );

        $this->seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers' => 1, 'title' => 'foo_event']
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $this->seminarUid,
            $organizerUid
        );

        $this->feUserUid = $this->testingFramework->createFrontEndUser(
            '',
            [
                'name' => 'foo_user',
                'email' => 'foo@bar.com',
            ]
        );
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'test title',
                'seminar' => $this->seminarUid,
                'interests' => 'nothing',
                'expectations' => '',
                'background_knowledge' => "foo\nbar",
                'known_from' => "foo\rbar",
                'user' => $this->feUserUid,
            ]
        );

        $this->subject = new LegacyRegistration($registrationUid);
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        RegistrationManager::purgeInstance();
    }

    // Utility functions.

    /**
     * Inserts a payment method record into the database and creates a relation
     * to it from the fixture.
     *
     * @param array $paymentMethodData data of the payment method to add, may be empty
     *
     * @return int the UID of the created record, will always be > 0
     */
    private function setPaymentMethodRelation(array $paymentMethodData): int
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_payment_methods', $paymentMethodData);

        $this->subject->setMethodOfPaymentUid($uid);

        return $uid;
    }

    // Tests for the utility functions.

    /**
     * @test
     */
    public function setPaymentMethodRelationReturnsUid(): void
    {
        self::assertTrue(
            $this->setPaymentMethodRelation([]) > 0
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodRelationCreatesNewUid(): void
    {
        self::assertNotEquals(
            $this->setPaymentMethodRelation([]),
            $this->setPaymentMethodRelation([])
        );
    }

    /**
     * @test
     */
    public function isOk(): void
    {
        self::assertTrue($this->subject->isOk());
    }

    // Tests concerning the payment method in setRegistrationData

    /**
     * @test
     */
    public function setRegistrationDataUsesPaymentMethodUidFromSetRegistrationData(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['method_of_payment' => 42]
        );

        self::assertSame(
            42,
            $this->subject->getMethodOfPaymentUid()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataForNoPaymentMethodSetAndPositiveTotalPriceWithSeminarWithOnePaymentMethodSelectsThatPaymentMethod(): void
    {
        $this->configuration->setAsString('currency', 'EUR');
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $this->seminarUid,
            ['price_regular' => 31.42]
        );
        $paymentMethodUid = $this->testingFramework->createRecord(
            'tx_seminars_payment_methods'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->seminarUid,
            $paymentMethodUid,
            'payment_methods'
        );

        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, 0, []);

        self::assertSame(
            $paymentMethodUid,
            $this->subject->getMethodOfPaymentUid()
        );
    }

    // Tests regarding the registration queue.

    /**
     * @test
     */
    public function statusIsInitiallyRegular(): void
    {
        self::assertSame(
            'regular',
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function statusIsRegularIfNotOnQueue(): void
    {
        $this->subject->setIsOnRegistrationQueue(false);

        self::assertSame(
            'regular',
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function statusIsWaitingListIfOnQueue(): void
    {
        $this->subject->setIsOnRegistrationQueue(true);

        self::assertSame(
            'waiting list',
            $this->subject->getStatus()
        );
    }

    // Tests regarding getting the registration data.

    /**
     * @test
     */
    public function getRegistrationDataForEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // @phpstan-ignore-next-line We are explicitly testing a contract violation here.
        $this->subject->getRegistrationData('');
    }

    /**
     * @test
     */
    public function getRegistrationDataCanGetUid(): void
    {
        self::assertSame(
            (string)$this->subject->getUid(),
            $this->subject->getRegistrationData('uid')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataWithKeyMethodOfPaymentReturnsMethodOfPayment(): void
    {
        $title = 'Test payment method';
        $this->setPaymentMethodRelation(['title' => $title]);

        self::assertStringContainsString(
            $title,
            $this->subject->getRegistrationData('method_of_payment')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForRegisteredThemselvesFalseReturnsLabelNo(): void
    {
        $this->subject->setRegisteredThemselves(false);

        self::assertSame(
            $this->getLanguageService()->getLL('label_no'),
            $this->subject->getRegistrationData('registered_themselves')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForRegisteredThemselvesTrueReturnsLabelYes(): void
    {
        $this->subject->setRegisteredThemselves(true);

        self::assertSame(
            $this->getLanguageService()->getLL('label_yes'),
            $this->subject->getRegistrationData('registered_themselves')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForNotesWithCarriageReturnRemovesCarriageReturnFromNotes(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['notes' => "foo\r\nbar"]
        );

        self::assertStringNotContainsString(
            "\r\n",
            $this->subject->getRegistrationData('notes')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForNotesWithCarriageReturnAndLineFeedReturnsNotesWithLinefeedAndNoCarriageReturn(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['notes' => "foo\r\nbar"]
        );

        self::assertSame(
            "foo\nbar",
            $this->subject->getRegistrationData('notes')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForMultipleAttendeeNamesReturnsAttendeeNamesWithEnumeration(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['attendees_names' => "foo\nbar"]
        );

        self::assertSame(
            "1. foo\n2. bar",
            $this->subject->getRegistrationData('attendees_names')
        );
    }

    // Tests concerning dumpAttendanceValues

    /**
     * @test
     */
    public function dumpAttendanceValuesCanContainUid(): void
    {
        self::assertStringContainsString(
            (string)$this->subject->getUid(),
            $this->subject->dumpAttendanceValues('uid')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequested(): void
    {
        self::assertStringContainsString(
            'nothing',
            $this->subject->dumpAttendanceValues('interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceAfterCommaInKeyList(): void
    {
        self::assertStringContainsString(
            'nothing',
            $this->subject->dumpAttendanceValues('email, interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceBeforeCommaInKeyList(): void
    {
        self::assertStringContainsString(
            'nothing',
            $this->subject->dumpAttendanceValues('interests ,email')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelForInterestsIfRequested(): void
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_interests'),
            $this->subject->dumpAttendanceValues('interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceAfterCommaInKeyList(): void
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_interests'),
            $this->subject->dumpAttendanceValues('interests, expectations')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceBeforeCommaInKeyList(): void
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_interests'),
            $this->subject->dumpAttendanceValues('interests ,expectations')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesForDataWithLineFeedStartsDataOnNewLine(): void
    {
        self::assertStringContainsString(
            "\nfoo\nbar",
            $this->subject->dumpAttendanceValues('background_knowledge')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesForDataWithCarriageReturnStartsDataOnNewLine(): void
    {
        self::assertStringContainsString(
            "\nfoo\nbar",
            $this->subject->dumpAttendanceValues('known_from')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesCanContainNonRegisteredField(): void
    {
        self::assertStringContainsString(
            'label_is_dummy_record: 1',
            $this->subject->dumpAttendanceValues('is_dummy_record')
        );
    }

    /**
     * @return string[][]
     */
    public function dumpableRegistrationFieldsDataProvider(): array
    {
        $fields = [
            'uid',
            'interests',
            'expectations',
            'background_knowledge',
            'lodgings',
            'accommodation',
            'foods',
            'food',
            'known_from',
            'notes',
            'checkboxes',
            'price',
            'seats',
            'total_price',
            'attendees_names',
            'kids',
            'method_of_payment',
            'company',
            'gender',
            'name',
            'address',
            'zip',
            'city',
            'country',
            'telephone',
            'email',
        ];

        $result = [];
        foreach ($fields as $field) {
            $result[$field] = [$field];
        }

        return $result;
    }

    /**
     * @test
     *
     * @param string $fieldName
     *
     * @dataProvider dumpableRegistrationFieldsDataProvider
     */
    public function dumpAttendanceValuesCreatesNoDoubleColonsAfterLabel(string $fieldName): void
    {
        $subject = LegacyRegistration::fromData([$fieldName => '1234 some value']);

        $result = $subject->dumpAttendanceValues($fieldName);

        self::assertStringNotContainsString('::', $result);
    }

    // Tests regarding committing registrations to the database.

    /**
     * @test
     */
    public function commitToDbCanCreateNewRecord(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $registration = new LegacyRegistration();
        $registration->setRegistrationData($seminar, 0, []);
        $registration->enableTestMode();
        $this->testingFramework->markTableAsDirty('tx_seminars_attendances');
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_attendances');

        self::assertTrue(
            $registration->commitToDatabase()
        );
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_attendances', ['uid' => $registration->getUid()]),
            'The registration record cannot be found in the DB.'
        );
    }

    /**
     * @test
     */
    public function commitToDbCanCreateLodgingsRelation(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $lodgingsUid = $this->testingFramework->createRecord(
            'tx_seminars_lodgings'
        );

        $registration = new LegacyRegistration();
        $registration->setRegistrationData(
            $seminar,
            0,
            ['lodgings' => [$lodgingsUid]]
        );
        $registration->enableTestMode();
        $this->testingFramework->markTableAsDirty('tx_seminars_attendances');
        $this->testingFramework->markTableAsDirty(
            'tx_seminars_attendances_lodgings_mm'
        );

        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_attendances');

        self::assertTrue(
            $registration->commitToDatabase()
        );
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_attendances', ['uid' => $registration->getUid()]),
            'The registration record cannot be found in the DB.'
        );

        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_attendances_lodgings_mm');

        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_attendances_lodgings_mm',
                ['uid_local' => $registration->getUid(), 'uid_foreign' => $lodgingsUid]
            ),
            'The relation record cannot be found in the DB.'
        );
    }

    /**
     * @test
     */
    public function commitToDbCanCreateFoodsRelation(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $foodsUid = $this->testingFramework->createRecord(
            'tx_seminars_foods'
        );

        $registration = new LegacyRegistration();
        $registration->setRegistrationData(
            $seminar,
            0,
            ['foods' => [$foodsUid]]
        );
        $registration->enableTestMode();
        $this->testingFramework->markTableAsDirty('tx_seminars_attendances');
        $this->testingFramework->markTableAsDirty(
            'tx_seminars_attendances_foods_mm'
        );

        self::assertTrue(
            $registration->commitToDatabase()
        );
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_attendances');

        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_attendances', ['uid' => $registration->getUid()]),
            'The registration record cannot be found in the DB.'
        );
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_attendances_foods_mm');
        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_attendances_foods_mm',
                ['uid_local' => $registration->getUid(), 'uid_foreign' => $foodsUid]
            ),
            'The relation record cannot be found in the DB.'
        );
    }

    /**
     * @test
     */
    public function commitToDbCanCreateCheckboxesRelation(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $checkboxesUid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes'
        );

        $registration = new LegacyRegistration();
        $registration->setRegistrationData(
            $seminar,
            0,
            ['checkboxes' => [$checkboxesUid]]
        );
        $registration->enableTestMode();
        $this->testingFramework->markTableAsDirty('tx_seminars_attendances');
        $this->testingFramework->markTableAsDirty(
            'tx_seminars_attendances_checkboxes_mm'
        );

        self::assertTrue(
            $registration->commitToDatabase()
        );
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_attendances');
        self::assertSame(
            1,
            $connection->count('*', 'tx_seminars_attendances', ['uid' => $registration->getUid()]),
            'The registration record cannot be found in the DB.'
        );
        $connection = $this->connectionPool->getConnectionForTable('tx_seminars_attendances_checkboxes_mm');
        self::assertSame(
            1,
            $connection->count(
                '*',
                'tx_seminars_attendances_checkboxes_mm',
                ['uid_local' => $registration->getUid(), 'uid_foreign' => $checkboxesUid]
            ),
            'The relation record cannot be found in the DB.'
        );
    }

    // Tests regarding the cached seminars.

    /**
     * @test
     */
    public function purgeCachedSeminarsResultsInDifferentDataForSameSeminarUid(): void
    {
        $seminarUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'test title 1']
        );

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $seminarUid]
        );

        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $seminarUid,
            ['title' => 'test title 2']
        );

        LegacyRegistration::purgeCachedSeminars();
        $subject = new LegacyRegistration($registrationUid);

        self::assertSame(
            'test title 2',
            $subject->getSeminarObject()->getTitle()
        );
    }

    // Tests for setting and getting the user data

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function instantiationWithoutLoggedInUserDoesNotThrowException(): void
    {
        $this->testingFramework->logoutFrontEndUser();

        new LegacyRegistration(
            $this->testingFramework->createRecord(
                'tx_seminars_attendances',
                ['seminar' => $this->seminarUid]
            )
        );
    }

    // Tests for isPaid()

    /**
     * @test
     */
    public function isPaidInitiallyReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->isPaid()
        );
    }

    /**
     * @test
     */
    public function isPaidForPaidRegistrationReturnsTrue(): void
    {
        $this->subject->setPaymentDateAsUnixTimestamp($GLOBALS['SIM_EXEC_TIME']);

        self::assertTrue(
            $this->subject->isPaid()
        );
    }

    /**
     * @test
     */
    public function isPaidForUnpaidRegistrationReturnsFalse(): void
    {
        $this->subject->setPaymentDateAsUnixTimestamp(0);

        self::assertFalse(
            $this->subject->isPaid()
        );
    }

    // Tests regarding hasExistingFrontEndUser().

    /**
     * @test
     */
    public function hasExistingFrontEndUserWithExistingFrontEndUserReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->hasExistingFrontEndUser()
        );
    }

    /**
     * @test
     */
    public function hasExistingFrontEndUserWithInexistentFrontEndUserReturnsFalse(): void
    {
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->subject->getUser(),
            ['deleted' => 1]
        );

        self::assertFalse(
            $this->subject->hasExistingFrontEndUser()
        );
    }

    /**
     * @test
     */
    public function hasExistingFrontEndUserWithZeroFrontEndUserUIDReturnsFalse(): void
    {
        $this->subject->setFrontEndUserUid(0);

        self::assertFalse(
            $this->subject->hasExistingFrontEndUser()
        );
    }

    // Tests concerning setRegistrationData

    /**
     * @test
     */
    public function setRegistrationDataWithNoFoodOptionsInitializesFoodOptionsAsArray(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            []
        );

        self::assertIsArray($this->subject->getFoodsUids());
    }

    /**
     * @test
     */
    public function setRegistrationDataForFoodOptionsStoresFoodOptionsInFoodsVariable(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $foods = ['foo' => 'foo', 'bar' => 'bar'];
        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['foods' => $foods]
        );

        self::assertSame(
            $foods,
            $this->subject->getFoodsUids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataWithEmptyFoodOptionsInitializesFoodOptionsAsArray(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['foods' => '']
        );

        self::assertIsArray($this->subject->getFoodsUids());
    }

    /**
     * @test
     */
    public function setRegistrationDataWithNoLodgingOptionsInitializesLodgingOptionsAsArray(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            []
        );

        self::assertIsArray($this->subject->getLodgingsUids());
    }

    /**
     * @test
     */
    public function setRegistrationDataWithLodgingOptionsStoresLodgingOptionsInLodgingVariable(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $lodgings = ['foo' => 'foo', 'bar' => 'bar'];
        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['lodgings' => $lodgings]
        );

        self::assertSame(
            $lodgings,
            $this->subject->getLodgingsUids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataWithEmptyLodgingOptionsInitializesLodgingOptionsAsArray(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['lodgings' => '']
        );

        self::assertIsArray($this->subject->getLodgingsUids());
    }

    /**
     * @test
     */
    public function setRegistrationDataWithNoCheckboxOptionsInitializesCheckboxOptionsAsArray(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            []
        );

        self::assertIsArray($this->subject->getCheckboxesUids());
    }

    /**
     * @test
     */
    public function setRegistrationDataWithCheckboxOptionsStoresCheckboxOptionsInCheckboxVariable(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $checkboxes = ['foo' => 'foo', 'bar' => 'bar'];
        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['checkboxes' => $checkboxes]
        );

        self::assertSame(
            $checkboxes,
            $this->subject->getCheckboxesUids()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataWithEmptyCheckboxOptionsInitializesCheckboxOptionsAsArray(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();

        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['checkboxes' => '']
        );

        self::assertIsArray($this->subject->getCheckboxesUids());
    }

    /**
     * @test
     */
    public function setRegistrationDataWithRegisteredThemselvesGivenStoresRegisteredThemselvesIntoTheObject(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['registered_themselves' => 1]
        );

        self::assertSame(
            $this->getLanguageService()->getLL('label_yes'),
            $this->subject->getRegistrationData('registered_themselves')
        );
    }

    /**
     * @test
     */
    public function setRegistrationDataWithCompanyGivenStoresCompanyIntoTheObject(): void
    {
        $userUid = $this->testingFramework->createAndLoginFrontEndUser();
        $this->subject->setRegistrationData(
            $this->subject->getSeminarObject(),
            $userUid,
            ['company' => "Foo\nBar Inc"]
        );

        self::assertSame(
            "Foo\nBar Inc",
            $this->subject->getRegistrationData('company')
        );
    }

    // Tests regarding the seats.

    /**
     * @test
     */
    public function getSeatsWithoutSeatsReturnsOne(): void
    {
        self::assertSame(
            1,
            $this->subject->getSeats()
        );
    }

    /**
     * @test
     */
    public function setSeatsWithNegativeSeatsThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $seats must be >= 0.'
        );

        $this->subject->setSeats(-1);
    }

    /**
     * @test
     */
    public function setSeatsWithZeroSeatsSetsSeats(): void
    {
        $this->subject->setSeats(0);

        self::assertSame(
            1,
            $this->subject->getSeats()
        );
    }

    /**
     * @test
     */
    public function setSeatsWithPositiveSeatsSetsSeats(): void
    {
        $this->subject->setSeats(42);

        self::assertSame(
            42,
            $this->subject->getSeats()
        );
    }

    /**
     * @test
     */
    public function hasSeatsWithoutSeatsReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasSeats()
        );
    }

    /**
     * @test
     */
    public function hasSeatsWithSeatsReturnsTrue(): void
    {
        $this->subject->setSeats(42);

        self::assertTrue(
            $this->subject->hasSeats()
        );
    }

    // Tests regarding the attendees names.

    /**
     * @test
     */
    public function getAttendeesNamesWithoutAttendeesNamesReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setAttendeesNamesWithAttendeesNamesSetsAttendeesNames(): void
    {
        $this->subject->setAttendeesNames('John Doe');

        self::assertSame(
            'John Doe',
            $this->subject->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function hasAttendeesNamesWithoutAttendeesNamesReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function hasAttendeesNamesWithAttendeesNamesReturnsTrue(): void
    {
        $this->subject->setAttendeesNames('John Doe');

        self::assertTrue(
            $this->subject->hasAttendeesNames()
        );
    }

    // Tests regarding the kids.

    /**
     * @test
     */
    public function getNumberOfKidsWithoutKidsReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfKids()
        );
    }

    /**
     * @test
     */
    public function setNumberOfKidsWithNegativeNumberOfKidsThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $numberOfKids must be >= 0.'
        );

        $this->subject->setNumberOfKids(-1);
    }

    /**
     * @test
     */
    public function setNumberOfKidsWithZeroNumberOfKidsSetsNumberOfKids(): void
    {
        $this->subject->setNumberOfKids(0);

        self::assertSame(
            0,
            $this->subject->getNumberOfKids()
        );
    }

    /**
     * @test
     */
    public function setNumberOfKidsWithPositiveNumberOfKidsSetsNumberOfKids(): void
    {
        $this->subject->setNumberOfKids(42);

        self::assertSame(
            42,
            $this->subject->getNumberOfKids()
        );
    }

    /**
     * @test
     */
    public function hasKidsWithoutKidsReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasKids()
        );
    }

    /**
     * @test
     */
    public function hasKidsWithKidsReturnsTrue(): void
    {
        $this->subject->setNumberOfKids(42);

        self::assertTrue(
            $this->subject->hasKids()
        );
    }

    // Tests regarding the price.

    /**
     * @test
     */
    public function getPriceWithoutPriceReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getPrice()
        );
    }

    /**
     * @test
     */
    public function setPriceWithPriceSetsPrice(): void
    {
        $this->subject->setPrice('Regular price: 42.42');

        self::assertSame(
            'Regular price: 42.42',
            $this->subject->getPrice()
        );
    }

    /**
     * @test
     */
    public function hasPriceWithoutPriceReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasPrice()
        );
    }

    /**
     * @test
     */
    public function hasPriceWithPriceReturnsTrue(): void
    {
        $this->subject->setPrice('Regular price: 42.42');

        self::assertTrue(
            $this->subject->hasPrice()
        );
    }

    // Tests regarding the total price.

    /**
     * @test
     */
    public function getTotalPriceWithoutTotalPriceReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function setTotalPriceWithTotalPriceSetsTotalPrice(): void
    {
        $this->configuration->setAsString('currency', 'EUR');
        $this->subject->setTotalPrice('42.42');

        self::assertSame(
            '€ 42,42',
            $this->subject->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function hasTotalPriceWithoutTotalPriceReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasTotalPrice()
        );
    }

    /**
     * @test
     */
    public function hasTotalPriceWithTotalPriceReturnsTrue(): void
    {
        $this->subject->setTotalPrice('42.42');

        self::assertTrue(
            $this->subject->hasTotalPrice()
        );
    }

    // Tests regarding the method of payment.

    /**
     * @test
     */
    public function getMethodOfPaymentUidWithoutMethodOfPaymentReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->getMethodOfPaymentUid()
        );
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidWithNegativeUidThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $uid must be >= 0.'
        );

        $this->subject->setMethodOfPaymentUid(-1);
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidWithZeroUidSetsMethodOfPaymentUid(): void
    {
        $this->subject->setMethodOfPaymentUid(0);

        self::assertSame(
            0,
            $this->subject->getMethodOfPaymentUid()
        );
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidWithPositiveUidSetsMethodOfPaymentUid(): void
    {
        $this->subject->setMethodOfPaymentUid(42);

        self::assertSame(
            42,
            $this->subject->getMethodOfPaymentUid()
        );
    }

    /**
     * @test
     */
    public function hasMethodOfPaymentWithoutMethodOfPaymentReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasMethodOfPayment()
        );
    }

    /**
     * @test
     */
    public function hasMethodOfPaymentWithMethodOfPaymentReturnsTrue(): void
    {
        $this->subject->setMethodOfPaymentUid(42);

        self::assertTrue(
            $this->subject->hasMethodOfPayment()
        );
    }

    // Tests concerning getEnumeratedAttendeeNames

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUseHtmlSeparatesAttendeesNamesWithListItems(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['attendees_names' => "foo\nbar"]
        );

        self::assertSame(
            '<ol><li>foo</li><li>bar</li></ol>',
            $this->subject->getEnumeratedAttendeeNames(true)
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUseHtmlAndEmptyAttendeesNamesReturnsEmptyString(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['attendees_names' => '']
        );

        self::assertSame(
            '',
            $this->subject->getEnumeratedAttendeeNames(true)
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUsePlainTextSeparatesAttendeesNamesWithLineFeed(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['attendees_names' => "foo\nbar"]
        );

        self::assertSame(
            "1. foo\n2. bar",
            $this->subject->getEnumeratedAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUsePlainTextAndEmptyAttendeesNamesReturnsEmptyString(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['attendees_names' => '']
        );

        self::assertSame(
            '',
            $this->subject->getEnumeratedAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndNoAttendeeNamesReturnsUsersName(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            $this->feUserUid,
            ['attendees_names' => '']
        );
        $this->subject->setRegisteredThemselves(true);

        self::assertSame(
            '1. foo_user',
            $this->subject->getEnumeratedAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndAttendeeNamesReturnsUserInFirstPosition(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            $this->feUserUid,
            ['attendees_names' => 'foo']
        );
        $this->subject->setRegisteredThemselves(true);

        self::assertSame(
            "1. foo_user\n2. foo",
            $this->subject->getEnumeratedAttendeeNames()
        );
    }

    // Tests concerning the food

    /**
     * @test
     */
    public function getFoodReturnsFood(): void
    {
        $food = 'a hamburger';

        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['food' => $food]);

        self::assertSame(
            $food,
            $this->subject->getFood()
        );
    }

    /**
     * @test
     */
    public function hasFoodForEmptyFoodReturnsFalse(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['food' => '']);

        self::assertFalse(
            $this->subject->hasFood()
        );
    }

    /**
     * @test
     */
    public function hasFoodForNonEmptyFoodReturnsTrue(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['food' => 'two donuts']);

        self::assertTrue(
            $this->subject->hasFood()
        );
    }

    // Tests concerning the accommodation

    /**
     * @test
     */
    public function getAccommodationReturnsAccommodation(): void
    {
        $accommodation = 'a tent in the woods';

        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['accommodation' => $accommodation]);

        self::assertSame(
            $accommodation,
            $this->subject->getAccommodation()
        );
    }

    /**
     * @test
     */
    public function hasAccommodationForEmptyAccommodationReturnsFalse(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['accommodation' => '']);

        self::assertFalse(
            $this->subject->hasAccommodation()
        );
    }

    /**
     * @test
     */
    public function hasAccommodationForNonEmptyAccommodationReturnsTrue(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['accommodation' => 'a youth hostel']);

        self::assertTrue(
            $this->subject->hasAccommodation()
        );
    }

    // Tests concerning the interests

    /**
     * @test
     */
    public function getInterestsReturnsInterests(): void
    {
        $interests = 'new experiences';

        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['interests' => $interests]);

        self::assertSame(
            $interests,
            $this->subject->getInterests()
        );
    }

    /**
     * @test
     */
    public function hasInterestsForEmptyInterestsReturnsFalse(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['interests' => '']);

        self::assertFalse(
            $this->subject->hasInterests()
        );
    }

    /**
     * @test
     */
    public function hasInterestsForNonEmptyInterestsReturnsTrue(): void
    {
        $seminar = new LegacyEvent($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['interests' => 'meeting people']);

        self::assertTrue(
            $this->subject->hasInterests()
        );
    }
}
