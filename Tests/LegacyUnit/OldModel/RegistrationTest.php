<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_OldModel_RegistrationTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration
     */
    protected $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var int the UID of a seminar to which the fixture relates
     */
    protected $seminarUid = 0;

    /**
     * @var int the UID of the registration the fixture relates to
     */
    protected $registrationUid = 0;

    /**
     * @var int the UID of the user the registration relates to
     */
    protected $feUserUid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration::purgeCachedSeminars();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        \Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', new \Tx_Oelib_Configuration());

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
        $this->registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'title' => 'test title',
                'seminar' => $this->seminarUid,
                'interests' => 'nothing',
                'expectations' => '',
                'background_knowledge' => 'foo' . LF . 'bar',
                'known_from' => 'foo' . CR . 'bar',
                'user' => $this->feUserUid,
            ]
        );

        $this->subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($this->registrationUid);
        $this->subject->setConfigurationValue(
            'templateFile',
            'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html'
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    /**
     * @test
     */
    public function isOk()
    {
        self::assertTrue(
            $this->subject->isOk()
        );
    }

    /*
     * Utility functions.
     */

    /**
     * Inserts a payment method record into the database and creates a relation
     * to it from the fixture.
     *
     * @param array $paymentMethodData data of the payment method to add, may be empty
     *
     * @return int the UID of the created record, will always be > 0
     */
    private function setPaymentMethodRelation(array $paymentMethodData)
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_payment_methods', $paymentMethodData);

        $this->subject->setPaymentMethod($uid);

        return $uid;
    }

    /*
     * Tests for the utility functions.
     */

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

    /*
     * Tests concerning the payment method in setRegistrationData
     */

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
    public function setRegistrationDataForNoPaymentMethodSetAndPositiveTotalPriceWithSeminarWithOnePaymentMethodSelectsThatPaymentMethod(
    ) {
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('currency', 'EUR');
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

    /*
     * Tests regarding the registration queue.
     */

    /**
     * @test
     */
    public function isOnRegistrationQueue()
    {
        self::assertFalse(
            $this->subject->isOnRegistrationQueue()
        );

        $this->subject->setIsOnRegistrationQueue(1);
        self::assertTrue(
            $this->subject->isOnRegistrationQueue()
        );
    }

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

    /*
     * Tests regarding getting the registration data.
     */

    /**
     * @test
     */
    public function getRegistrationDataIsEmptyForEmptyKey()
    {
        self::assertSame(
            '',
            $this->subject->getRegistrationData('')
        );
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

        self::assertContains(
            $title,
            $this->subject->getRegistrationData('method_of_payment')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForRegisteredThemselvesZeroReturnsLabelNo()
    {
        $this->subject->setRegisteredThemselves(0);

        self::assertSame(
            $this->subject->translate('label_no'),
            $this->subject->getRegistrationData('registered_themselves')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForRegisteredThemselvesOneReturnsLabelYes()
    {
        $this->subject->setRegisteredThemselves(1);

        self::assertSame(
            $this->subject->translate('label_yes'),
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
            ['notes' => 'foo' . CRLF . 'bar']
        );

        self::assertNotContains(
            CRLF,
            $this->subject->getRegistrationData('notes')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForNotesWithCarriageReturnAndLineFeedReturnsNotesWithLinefeedAndNoCarriageReturn(
    ) {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['notes' => 'foo' . CRLF . 'bar']
        );

        self::assertSame(
            'foo' . LF . 'bar',
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
            ['attendees_names' => 'foo' . LF . 'bar']
        );

        self::assertSame(
            '1. foo' . LF . '2. bar',
            $this->subject->getRegistrationData('attendees_names')
        );
    }

    /*
     * Tests concerning dumpAttendanceValues
     */

    /**
     * @test
     */
    public function dumpAttendanceValuesCanContainUid()
    {
        self::assertContains(
            (string)$this->subject->getUid(),
            $this->subject->dumpAttendanceValues('uid')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequested()
    {
        self::assertContains(
            'nothing',
            $this->subject->dumpAttendanceValues('interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceAfterCommaInKeyList()
    {
        self::assertContains(
            'nothing',
            $this->subject->dumpAttendanceValues('email, interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsInterestsIfRequestedEvenForSpaceBeforeCommaInKeyList()
    {
        self::assertContains(
            'nothing',
            $this->subject->dumpAttendanceValues('interests ,email')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelForInterestsIfRequested()
    {
        self::assertContains(
            $this->subject->translate('label_interests'),
            $this->subject->dumpAttendanceValues('interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceAfterCommaInKeyList()
    {
        self::assertContains(
            $this->subject->translate('label_interests'),
            $this->subject->dumpAttendanceValues('interests, expectations')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceBeforeCommaInKeyList()
    {
        self::assertContains(
            $this->subject->translate('label_interests'),
            $this->subject->dumpAttendanceValues('interests ,expectations')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesForDataWithLineFeedStartsDataOnNewLine()
    {
        self::assertContains(
            LF . 'foo' . LF . 'bar',
            $this->subject->dumpAttendanceValues('background_knowledge')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesForDataWithCarriageReturnStartsDataOnNewLine()
    {
        self::assertContains(
            LF . 'foo' . LF . 'bar',
            $this->subject->dumpAttendanceValues('known_from')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesCanContainNonRegisteredField()
    {
        self::assertContains(
            'label_is_dummy_record: 1',
            $this->subject->dumpAttendanceValues('is_dummy_record')
        );
    }

    /*
     * Tests regarding committing registrations to the database.
     */

    /**
     * @test
     */
    public function commitToDbCanCreateNewRecord()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $registration = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration(0);
        $registration->setRegistrationData($seminar, 0, []);
        $registration->enableTestMode();
        $this->testingFramework->markTableAsDirty('tx_seminars_attendances');

        self::assertTrue(
            $registration->isOk()
        );
        self::assertTrue(
            $registration->commitToDb()
        );
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_attendances',
                'uid=' . $registration->getUid()
            ),
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

        $registration = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration(0);
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

        self::assertTrue(
            $registration->isOk()
        );
        self::assertTrue(
            $registration->commitToDb()
        );
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_attendances',
                'uid=' . $registration->getUid()
            ),
            'The registration record cannot be found in the DB.'
        );
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_attendances_lodgings_mm',
                'uid_local=' . $registration->getUid()
                . ' AND uid_foreign=' . $lodgingsUid
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

        $registration = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration(0);
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
            $registration->isOk()
        );
        self::assertTrue(
            $registration->commitToDb()
        );
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_attendances',
                'uid=' . $registration->getUid()
            ),
            'The registration record cannot be found in the DB.'
        );
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_attendances_foods_mm',
                'uid_local=' . $registration->getUid()
                . ' AND uid_foreign=' . $foodsUid
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

        $registration = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration(0);
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
            $registration->isOk()
        );
        self::assertTrue(
            $registration->commitToDb()
        );
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_attendances',
                'uid=' . $registration->getUid()
            ),
            'The registration record cannot be found in the DB.'
        );
        self::assertSame(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_attendances_checkboxes_mm',
                'uid_local=' . $registration->getUid()
                . ' AND uid_foreign=' . $checkboxesUid
            ),
            'The relation record cannot be found in the DB.'
        );
    }

    /*
     * Tests concerning getSeminarObject
     */

    /**
     * @test
     */
    public function getSeminarObjectReturnsSeminarInstance()
    {
        self::assertInstanceOf(
            \Tx_Seminars_OldModel_Event::class,
            $this->subject->getSeminarObject()
        );
    }

    /**
     * @test
     */
    public function getSeminarObjectForRegistrationWithoutSeminarReturnsSeminarInstance()
    {
        $this->testingFramework->changeRecord(
            'tx_seminars_attendances',
            $this->registrationUid,
            [
                'seminar' => 0,
                'user' => 0,
            ]
        );

        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($this->registrationUid);

        self::assertInstanceOf(
            \Tx_Seminars_OldModel_Event::class,
            $subject->getSeminarObject()
        );
    }

    /**
     * @test
     */
    public function getSeminarObjectReturnsSeminarWithUidFromRelation()
    {
        self::assertSame(
            $this->seminarUid,
            $this->subject->getSeminarObject()->getUid()
        );
    }

    /*
     * Tests regarding the cached seminars.
     */

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

        \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration::purgeCachedSeminars();
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertSame(
            'test title 2',
            $subject->getSeminarObject()->getTitle()
        );
    }

    /*
     * Tests for setting and getting the user data
     */

    /**
     * @test
     */
    public function instantiationWithoutLoggedInUserDoesNotThrowException()
    {
        $this->testingFramework->logoutFrontEndUser();

        new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration(
            $this->testingFramework->createRecord(
                'tx_seminars_attendances',
                ['seminar' => $this->seminarUid]
            )
        );
    }

    /**
     * @test
     */
    public function setUserDataThrowsExceptionForEmptyUserData()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            '$userData must not be empty.'
        );

        $this->subject->setUserData([]);
    }

    /**
     * @test
     */
    public function getUserDataIsEmptyForEmptyKey()
    {
        self::assertSame(
            '',
            $this->subject->getUserData('')
        );
    }

    /**
     * @test
     */
    public function getUserDataReturnsEmptyStringForInexistentKeyName()
    {
        $this->subject->setUserData(['name' => 'John Doe']);

        self::assertSame(
            '',
            $this->subject->getUserData('foo')
        );
    }

    /**
     * @test
     */
    public function getUserDataCanReturnWwwSetViaSetUserData()
    {
        $this->subject->setUserData(['www' => 'www.foo.com']);

        self::assertSame(
            'www.foo.com',
            $this->subject->getUserData('www')
        );
    }

    /**
     * @test
     */
    public function getUserDataCanReturnNumericPidAsString()
    {
        $pid = $this->testingFramework->createSystemFolder();
        $this->subject->setUserData(['pid' => $pid]);

        self::assertInternalType(
            'string',
            $this->subject->getUserData('pid')
        );
        self::assertSame(
            (string)$pid,
            $this->subject->getUserData('pid')
        );
    }

    /**
     * @test
     */
    public function getUserDataForUserWithNameReturnsUsersName()
    {
        self::assertSame(
            'foo_user',
            $this->subject->getUserData('name')
        );
    }

    /**
     * @test
     */
    public function getUserDataForUserWithOutNameButFirstNameReturnsFirstName()
    {
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['name' => '', 'first_name' => 'first_foo']
        );

        self::assertSame(
            'first_foo',
            $this->subject->getUserData('name')
        );
    }

    /**
     * @test
     */
    public function getUserDataForUserWithOutNameButLastNameReturnsLastName()
    {
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['name' => '', 'last_name' => 'last_foo']
        );

        self::assertSame(
            'last_foo',
            $this->subject->getUserData('name')
        );
    }

    /**
     * @test
     */
    public function getUserDataForUserWithOutNameButFirstAndLastNameReturnsFirstAndLastName()
    {
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['name' => '', 'first_name' => 'first', 'last_name' => 'last']
        );

        self::assertSame(
            'first last',
            $this->subject->getUserData('name')
        );
    }

    /*
     * Tests concerning dumpUserValues
     */

    /**
     * @test
     */
    public function dumpUserValuesContainsUserNameIfRequested()
    {
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['name' => 'John Doe']
        );

        self::assertContains(
            'John Doe',
            $this->subject->dumpUserValues('name')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsUserNameIfRequestedEvenForSpaceAfterCommaInKeyList()
    {
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['name' => 'John Doe']
        );

        self::assertContains(
            'John Doe',
            $this->subject->dumpUserValues('email, name')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsUserNameIfRequestedEvenForSpaceBeforeCommaInKeyList()
    {
        $this->testingFramework->changeRecord(
            'fe_users',
            $this->feUserUid,
            ['name' => 'John Doe']
        );

        self::assertContains(
            'John Doe',
            $this->subject->dumpUserValues('name ,email')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsLabelForUserNameIfRequested()
    {
        self::assertContains(
            $this->subject->translate('label_name'),
            $this->subject->dumpUserValues('name')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsLabelEvenForSpaceAfterCommaInKeyList()
    {
        self::assertContains(
            $this->subject->translate('label_name'),
            $this->subject->dumpUserValues('email, name')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsLabelEvenForSpaceBeforeCommaInKeyList()
    {
        self::assertContains(
            $this->subject->translate('label_name'),
            $this->subject->dumpUserValues('name ,email')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsPidIfRequested()
    {
        $pid = $this->testingFramework->createSystemFolder();
        $this->subject->setUserData(['pid' => $pid]);

        self::assertInternalType(
            'string',
            $this->subject->getUserData('pid')
        );

        self::assertContains(
            (string)$pid,
            $this->subject->dumpUserValues('pid')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesContainsFieldNameAsLabelForPid()
    {
        $pid = $this->testingFramework->createSystemFolder();
        $this->subject->setUserData(['pid' => $pid]);

        self::assertContains(
            'Pid',
            $this->subject->dumpUserValues('pid')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesDoesNotContainRawLabelNameAsLabelForPid()
    {
        $pid = $this->testingFramework->createSystemFolder();
        $this->subject->setUserData(['pid' => $pid]);

        self::assertNotContains(
            'label_pid',
            $this->subject->dumpUserValues('pid')
        );
    }

    /**
     * @test
     */
    public function dumpUserValuesCanContainNonRegisteredField()
    {
        $this->subject->setUserData(['is_dummy_record' => true]);

        self::assertContains(
            'Is_dummy_record: 1',
            $this->subject->dumpUserValues('is_dummy_record')
        );
    }

    /*
     * Tests for isPaid()
     */

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

    /*
     * Tests regarding hasExistingFrontEndUser().
     */

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
        $this->subject->setFrontEndUserUID(0);

        self::assertFalse(
            $this->subject->hasExistingFrontEndUser()
        );
    }

    /*
     * Tests regarding getFrontEndUser().
     */

    /**
     * @test
     */
    public function getFrontEndUserWithExistingFrontEndUserReturnsFrontEndUser()
    {
        self::assertInstanceOf(\Tx_Oelib_Model_FrontEndUser::class, $this->subject->getFrontEndUser());
    }

    /*
     * Tests concerning setRegistrationData
     */

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

        self::assertInternalType(
            'array',
            $this->subject->getFoodsData()
        );
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
            $this->subject->getFoodsData()
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

        self::assertInternalType(
            'array',
            $this->subject->getFoodsData()
        );
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

        self::assertInternalType(
            'array',
            $this->subject->getLodgingsData()
        );
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
            $this->subject->getLodgingsData()
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

        self::assertInternalType(
            'array',
            $this->subject->getLodgingsData()
        );
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

        self::assertInternalType(
            'array',
            $this->subject->getCheckboxesData()
        );
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
            $this->subject->getCheckboxesData()
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

        self::assertInternalType(
            'array',
            $this->subject->getCheckboxesData()
        );
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
            $this->subject->translate('label_yes'),
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
            ['company' => 'Foo' . LF . 'Bar Inc']
        );

        self::assertSame(
            'Foo' . LF . 'Bar Inc',
            $this->subject->getRegistrationData('company')
        );
    }

    /*
     * Tests regarding the seats.
     */

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
        $this->setExpectedException(
            \InvalidArgumentException::class,
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

    /*
     * Tests regarding the attendees names.
     */

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

    /*
     * Tests regarding the kids.
     */

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
        $this->setExpectedException(
            \InvalidArgumentException::class,
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

    /*
     * Tests regarding the price.
     */

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

    /*
     * Tests regarding the total price.
     */

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
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('currency', 'EUR');
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

    /*
     * Tests regarding the method of payment.
     */

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
        $this->setExpectedException(
            \InvalidArgumentException::class,
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

    /*
     * Tests regarding the billing address.
     */

    /**
     * @test
     */
    public function getBillingAddressWithGenderMaleContainsLabelForGenderMale()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['gender' => '0']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            $subject->translate('label_gender.I.0'),
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithGenderFemaleContainsLabelForGenderFemale()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['gender' => '1']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            $subject->translate('label_gender.I.1'),
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithNameContainsName()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['name' => 'John Doe']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            'John Doe',
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithAddressContainsAddress()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['address' => 'Main Street 123']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            'Main Street 123',
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithZipCodeContainsZipCode()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['zip' => '12345']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            '12345',
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithCityContainsCity()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['city' => 'Big City']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            'Big City',
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithCountryContainsCountry()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['country' => 'Takka-Tukka-Land']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            'Takka-Tukka-Land',
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithTelephoneNumberContainsTelephoneNumber()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['telephone' => '01234-56789']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            '01234-56789',
            $subject->getBillingAddress()
        );
    }

    /**
     * @test
     */
    public function getBillingAddressWithEMailAddressContainsEMailAddress()
    {
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['email' => 'john@doe.com']
        );
        $subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingRegistration($registrationUid);

        self::assertContains(
            'john@doe.com',
            $subject->getBillingAddress()
        );
    }

    /*
     * Tests concerning getEnumeratedAttendeeNames
     */

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUseHtmlSeparatesAttendeesNamesWithListItems()
    {
        $seminar = new \Tx_Seminars_OldModel_Event($this->seminarUid);
        $this->subject->setRegistrationData(
            $seminar,
            0,
            ['attendees_names' => 'foo' . LF . 'bar']
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
            ['attendees_names' => 'foo' . LF . 'bar']
        );

        self::assertSame(
            '1. foo' . LF . '2. bar',
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
        $this->subject->setRegisteredThemselves(1);

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
        $this->subject->setRegisteredThemselves(1);

        self::assertSame(
            '1. foo_user' . LF . '2. foo',
            $this->subject->getEnumeratedAttendeeNames()
        );
    }

    /*
     * Tests concerning hasRegisteredMySelf
     */

    /**
     * @test
     */
    public function hasRegisteredMySelfForRegisteredThemselvesFalseReturnsFalse()
    {
        $this->subject->setRegisteredThemselves(0);

        self::assertFalse(
            $this->subject->hasRegisteredMySelf()
        );
    }

    /**
     * @test
     */
    public function hasRegisteredMySelfForRegisteredThemselvesTrueReturnsTrue()
    {
        $this->subject->setRegisteredThemselves(1);

        self::assertTrue(
            $this->subject->hasRegisteredMySelf()
        );
    }

    /*
     * Tests concerning the food
     */

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

    /*
     * Tests concerning the accommodation
     */

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

    /*
     * Tests concerning the interests
     */

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
