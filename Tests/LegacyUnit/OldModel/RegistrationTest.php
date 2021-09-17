<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class RegistrationTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Seminars_OldModel_Registration
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

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        \Tx_Seminars_OldModel_Registration::purgeCachedSeminars();

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

        $this->subject = new \Tx_Seminars_OldModel_Registration($registrationUid);
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );

        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
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
    public function setPaymentMethodRelationReturnsUid()
    {
        self::assertTrue(
            $this->setPaymentMethodRelation([]) > 0
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodRelationCreatesNewUid()
    {
        self::assertNotEquals(
            $this->setPaymentMethodRelation([]),
            $this->setPaymentMethodRelation([])
        );
    }

    /**
     * @test
     */
    public function isOk()
    {
        self::assertTrue($this->subject->isOk());
    }

    // Tests concerning the payment method in setRegistrationData

    /**
     * @test
     */
    public function setRegistrationDataUsesPaymentMethodUidFromSetRegistrationData()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function setRegistrationDataForNoPaymentMethodSetAndPositiveTotalPriceWithSeminarWithOnePaymentMethodSelectsThatPaymentMethod()
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

        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function statusIsInitiallyRegular()
    {
        self::assertSame(
            'regular',
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function statusIsRegularIfNotOnQueue()
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
    public function statusIsWaitingListIfOnQueue()
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
    public function getRegistrationDataForEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->getRegistrationData('');
    }

    /**
     * @test
     */
    public function getRegistrationDataCanGetUid()
    {
        self::assertSame(
            (string)$this->subject->getUid(),
            $this->subject->getRegistrationData('uid')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataWithKeyMethodOfPaymentReturnsMethodOfPayment()
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
    public function getRegistrationDataForRegisteredThemselvesFalseReturnsLabelNo()
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
    public function getRegistrationDataForRegisteredThemselvesTrueReturnsLabelYes()
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
    public function getRegistrationDataForNotesWithCarriageReturnRemovesCarriageReturnFromNotes()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getRegistrationDataForNotesWithCarriageReturnAndLineFeedReturnsNotesWithLinefeedAndNoCarriageReturn()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getRegistrationDataForMultipleAttendeeNamesReturnsAttendeeNamesWithEnumeration()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function dumpAttendanceValuesCanContainUid()
    {
        self::assertStringContainsString(
            (string)$this->subject->getUid(),
            $this->subject->dumpAttendanceValues('uid')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequested()
    {
        self::assertStringContainsString(
            'nothing',
            $this->subject->dumpAttendanceValues('interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceAfterCommaInKeyList()
    {
        self::assertStringContainsString(
            'nothing',
            $this->subject->dumpAttendanceValues('email, interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceBeforeCommaInKeyList()
    {
        self::assertStringContainsString(
            'nothing',
            $this->subject->dumpAttendanceValues('interests ,email')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelForInterestsIfRequested()
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_interests'),
            $this->subject->dumpAttendanceValues('interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceAfterCommaInKeyList()
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_interests'),
            $this->subject->dumpAttendanceValues('interests, expectations')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceBeforeCommaInKeyList()
    {
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_interests'),
            $this->subject->dumpAttendanceValues('interests ,expectations')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesForDataWithLineFeedStartsDataOnNewLine()
    {
        self::assertStringContainsString(
            "\nfoo\nbar",
            $this->subject->dumpAttendanceValues('background_knowledge')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesForDataWithCarriageReturnStartsDataOnNewLine()
    {
        self::assertStringContainsString(
            "\nfoo\nbar",
            $this->subject->dumpAttendanceValues('known_from')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesCanContainNonRegisteredField()
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
    public function dumpAttendanceValuesCreatesNoDoubleColonsAfterLabel(string $fieldName)
    {
        $subject = \Tx_Seminars_OldModel_Registration::fromData([$fieldName => '1234 some value']);

        $result = $subject->dumpAttendanceValues($fieldName);

        self::assertStringNotContainsString('::', $result);
    }

    // Tests regarding committing registrations to the database.

    /**
     * @test
     */
    public function commitToDbCanCreateNewRecord()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $registration = new \Tx_Seminars_OldModel_Registration();
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
    public function commitToDbCanCreateLodgingsRelation()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $lodgingsUid = $this->testingFramework->createRecord(
            'tx_seminars_lodgings'
        );

        $registration = new \Tx_Seminars_OldModel_Registration();
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
    public function commitToDbCanCreateFoodsRelation()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $foodsUid = $this->testingFramework->createRecord(
            'tx_seminars_foods'
        );

        $registration = new \Tx_Seminars_OldModel_Registration();
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
    public function commitToDbCanCreateCheckboxesRelation()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $checkboxesUid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes'
        );

        $registration = new \Tx_Seminars_OldModel_Registration();
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
    public function purgeCachedSeminarsResultsInDifferentDataForSameSeminarUid()
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

        \Tx_Seminars_OldModel_Registration::purgeCachedSeminars();
        $subject = new \Tx_Seminars_OldModel_Registration($registrationUid);

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
    public function instantiationWithoutLoggedInUserDoesNotThrowException()
    {
        $this->testingFramework->logoutFrontEndUser();

        new \Tx_Seminars_OldModel_Registration(
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
    public function isPaidInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isPaid()
        );
    }

    /**
     * @test
     */
    public function isPaidForPaidRegistrationReturnsTrue()
    {
        $this->subject->setPaymentDateAsUnixTimestamp($GLOBALS['SIM_EXEC_TIME']);

        self::assertTrue(
            $this->subject->isPaid()
        );
    }

    /**
     * @test
     */
    public function isPaidForUnpaidRegistrationReturnsFalse()
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
    public function hasExistingFrontEndUserWithExistingFrontEndUserReturnsTrue()
    {
        self::assertTrue(
            $this->subject->hasExistingFrontEndUser()
        );
    }

    /**
     * @test
     */
    public function hasExistingFrontEndUserWithInexistentFrontEndUserReturnsFalse()
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
    public function hasExistingFrontEndUserWithZeroFrontEndUserUIDReturnsFalse()
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
    public function setRegistrationDataWithNoFoodOptionsInitializesFoodOptionsAsArray()
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
    public function setRegistrationDataForFoodOptionsStoresFoodOptionsInFoodsVariable()
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
    public function setRegistrationDataWithEmptyFoodOptionsInitializesFoodOptionsAsArray()
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
    public function setRegistrationDataWithNoLodgingOptionsInitializesLodgingOptionsAsArray()
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
    public function setRegistrationDataWithLodgingOptionsStoresLodgingOptionsInLodgingVariable()
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
    public function setRegistrationDataWithEmptyLodgingOptionsInitializesLodgingOptionsAsArray()
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
    public function setRegistrationDataWithNoCheckboxOptionsInitializesCheckboxOptionsAsArray()
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
    public function setRegistrationDataWithCheckboxOptionsStoresCheckboxOptionsInCheckboxVariable()
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
    public function setRegistrationDataWithEmptyCheckboxOptionsInitializesCheckboxOptionsAsArray()
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
    public function setRegistrationDataWithRegisteredThemselvesGivenStoresRegisteredThemselvesIntoTheObject()
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
    public function setRegistrationDataWithCompanyGivenStoresCompanyIntoTheObject()
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
    public function getSeatsWithoutSeatsReturnsOne()
    {
        self::assertSame(
            1,
            $this->subject->getSeats()
        );
    }

    /**
     * @test
     */
    public function setSeatsWithNegativeSeatsThrowsException()
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
    public function setSeatsWithZeroSeatsSetsSeats()
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
    public function setSeatsWithPositiveSeatsSetsSeats()
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
    public function hasSeatsWithoutSeatsReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasSeats()
        );
    }

    /**
     * @test
     */
    public function hasSeatsWithSeatsReturnsTrue()
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
    public function getAttendeesNamesWithoutAttendeesNamesReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setAttendeesNamesWithAttendeesNamesSetsAttendeesNames()
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
    public function hasAttendeesNamesWithoutAttendeesNamesReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function hasAttendeesNamesWithAttendeesNamesReturnsTrue()
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
    public function getNumberOfKidsWithoutKidsReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfKids()
        );
    }

    /**
     * @test
     */
    public function setNumberOfKidsWithNegativeNumberOfKidsThrowsException()
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
    public function setNumberOfKidsWithZeroNumberOfKidsSetsNumberOfKids()
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
    public function setNumberOfKidsWithPositiveNumberOfKidsSetsNumberOfKids()
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
    public function hasKidsWithoutKidsReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasKids()
        );
    }

    /**
     * @test
     */
    public function hasKidsWithKidsReturnsTrue()
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
    public function getPriceWithoutPriceReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getPrice()
        );
    }

    /**
     * @test
     */
    public function setPriceWithPriceSetsPrice()
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
    public function hasPriceWithoutPriceReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasPrice()
        );
    }

    /**
     * @test
     */
    public function hasPriceWithPriceReturnsTrue()
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
    public function getTotalPriceWithoutTotalPriceReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function setTotalPriceWithTotalPriceSetsTotalPrice()
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
    public function hasTotalPriceWithoutTotalPriceReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasTotalPrice()
        );
    }

    /**
     * @test
     */
    public function hasTotalPriceWithTotalPriceReturnsTrue()
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
    public function getMethodOfPaymentUidWithoutMethodOfPaymentReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getMethodOfPaymentUid()
        );
    }

    /**
     * @test
     */
    public function setMethodOfPaymentUidWithNegativeUidThrowsException()
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
    public function setMethodOfPaymentUidWithZeroUidSetsMethodOfPaymentUid()
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
    public function setMethodOfPaymentUidWithPositiveUidSetsMethodOfPaymentUid()
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
    public function hasMethodOfPaymentWithoutMethodOfPaymentReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasMethodOfPayment()
        );
    }

    /**
     * @test
     */
    public function hasMethodOfPaymentWithMethodOfPaymentReturnsTrue()
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
    public function getEnumeratedAttendeeNamesWithUseHtmlSeparatesAttendeesNamesWithListItems()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getEnumeratedAttendeeNamesWithUseHtmlAndEmptyAttendeesNamesReturnsEmptyString()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getEnumeratedAttendeeNamesWithUsePlainTextSeparatesAttendeesNamesWithLineFeed()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getEnumeratedAttendeeNamesWithUsePlainTextAndEmptyAttendeesNamesReturnsEmptyString()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndNoAttendeeNamesReturnsUsersName()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndAttendeeNamesReturnsUserInFirstPosition()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
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
    public function getFoodReturnsFood()
    {
        $food = 'a hamburger';

        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['food' => $food]);

        self::assertSame(
            $food,
            $this->subject->getFood()
        );
    }

    /**
     * @test
     */
    public function hasFoodForEmptyFoodReturnsFalse()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['food' => '']);

        self::assertFalse(
            $this->subject->hasFood()
        );
    }

    /**
     * @test
     */
    public function hasFoodForNonEmptyFoodReturnsTrue()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['food' => 'two donuts']);

        self::assertTrue(
            $this->subject->hasFood()
        );
    }

    // Tests concerning the accommodation

    /**
     * @test
     */
    public function getAccommodationReturnsAccommodation()
    {
        $accommodation = 'a tent in the woods';

        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['accommodation' => $accommodation]);

        self::assertSame(
            $accommodation,
            $this->subject->getAccommodation()
        );
    }

    /**
     * @test
     */
    public function hasAccommodationForEmptyAccommodationReturnsFalse()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['accommodation' => '']);

        self::assertFalse(
            $this->subject->hasAccommodation()
        );
    }

    /**
     * @test
     */
    public function hasAccommodationForNonEmptyAccommodationReturnsTrue()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['accommodation' => 'a youth hostel']);

        self::assertTrue(
            $this->subject->hasAccommodation()
        );
    }

    // Tests concerning the interests

    /**
     * @test
     */
    public function getInterestsReturnsInterests()
    {
        $interests = 'new experiences';

        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['interests' => $interests]);

        self::assertSame(
            $interests,
            $this->subject->getInterests()
        );
    }

    /**
     * @test
     */
    public function hasInterestsForEmptyInterestsReturnsFalse()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['interests' => '']);

        self::assertFalse(
            $this->subject->hasInterests()
        );
    }

    /**
     * @test
     */
    public function hasInterestsForNonEmptyInterestsReturnsTrue()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData($seminar, $this->feUserUid, ['interests' => 'meeting people']);

        self::assertTrue(
            $this->subject->hasInterests()
        );
    }
}
