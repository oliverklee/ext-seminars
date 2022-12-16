<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use PHPUnit\Framework\TestCase;
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
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var int the UID of a seminar to which the fixture relates
     */
    private $seminarUid = 0;

    /**
     * @var int the UID of the user the registration relates to
     */
    private $feUserUid = 0;

    /** @var ConnectionPool */
    private $connectionPool;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        LegacyRegistration::purgeCachedSeminars();

        $this->testingFramework = new TestingFramework('tx_seminars');
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->changeRecord('pages', $rootPageUid, ['slug' => '/home']);
        $this->testingFramework->createFakeFrontEnd($rootPageUid);

        $this->configuration = new DummyConfiguration();
        $this->configuration->setAsString('templateFile', 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html');
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
            $this->translate('label_no'),
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
            $this->translate('label_yes'),
            $this->subject->getRegistrationData('registered_themselves')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForNotesWithCarriageReturnRemovesCarriageReturnFromNotes(): void
    {
        $subject = LegacyRegistration::fromData(['notes' => "foo\r\nbar"]);

        self::assertStringNotContainsString(
            "\r\n",
            $subject->getRegistrationData('notes')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForNotesWithCarriageReturnAndLineFeedReturnsNotesWithLinefeedAndNoCarriageReturn(): void
    {
        $subject = LegacyRegistration::fromData(['notes' => "foo\r\nbar"]);

        self::assertSame(
            "foo\nbar",
            $subject->getRegistrationData('notes')
        );
    }

    /**
     * @test
     */
    public function getRegistrationDataForMultipleAttendeeNamesReturnsAttendeeNamesWithEnumeration(): void
    {
        $subject = LegacyRegistration::fromData(['attendees_names' => "foo\nbar"]);

        self::assertSame(
            "1. foo\n2. bar",
            $subject->getRegistrationData('attendees_names')
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
            $this->translate('label_interests'),
            $this->subject->dumpAttendanceValues('interests')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceAfterCommaInKeyList(): void
    {
        self::assertStringContainsString(
            $this->translate('label_interests'),
            $this->subject->dumpAttendanceValues('interests, expectations')
        );
    }

    /**
     * @test
     */
    public function dumpAttendanceValuesContainsLabelEvenForSpaceBeforeCommaInKeyList(): void
    {
        self::assertStringContainsString(
            $this->translate('label_interests'),
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
        $registration = new LegacyRegistration();
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
        $subject = LegacyRegistration::fromData(['attendees_names' => "foo\nbar"]);

        self::assertSame(
            '<ol><li>foo</li><li>bar</li></ol>',
            $subject->getEnumeratedAttendeeNames(true)
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUseHtmlAndEmptyAttendeesNamesReturnsEmptyString(): void
    {
        $subject = LegacyRegistration::fromData(['attendees_names' => '']);

        self::assertSame(
            '',
            $subject->getEnumeratedAttendeeNames(true)
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUsePlainTextSeparatesAttendeesNamesWithLineFeed(): void
    {
        $subject = LegacyRegistration::fromData(['attendees_names' => "foo\nbar"]);

        self::assertSame(
            "1. foo\n2. bar",
            $subject->getEnumeratedAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesWithUsePlainTextAndEmptyAttendeesNamesReturnsEmptyString(): void
    {
        $subject = LegacyRegistration::fromData(['attendees_names' => '']);

        self::assertSame(
            '',
            $subject->getEnumeratedAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndNoAttendeeNamesReturnsUsersName(): void
    {
        $subject = LegacyRegistration::fromData(['attendees_names' => '']);
        $user = MapperRegistry::get(FrontEndUserMapper::class)->getLoadedTestingModel(['name' => 'foo_user']);
        $subject->setFrontEndUser($user);
        $subject->setRegisteredThemselves(true);

        self::assertSame(
            '1. foo_user',
            $subject->getEnumeratedAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getEnumeratedAttendeeNamesForSelfRegisteredUserAndAttendeeNamesReturnsUserInFirstPosition(): void
    {
        $subject = LegacyRegistration::fromData(['attendees_names' => 'foo']);
        $user = MapperRegistry::get(FrontEndUserMapper::class)->getLoadedTestingModel(['name' => 'foo_user']);
        $subject->setFrontEndUser($user);
        $subject->setRegisteredThemselves(true);

        self::assertSame(
            "1. foo_user\n2. foo",
            $subject->getEnumeratedAttendeeNames()
        );
    }

    // Tests concerning the food

    /**
     * @test
     */
    public function getFoodReturnsFood(): void
    {
        $food = 'a hamburger';
        $subject = LegacyRegistration::fromData(['food' => $food]);

        self::assertSame(
            $food,
            $subject->getFood()
        );
    }

    /**
     * @test
     */
    public function hasFoodForEmptyFoodReturnsFalse(): void
    {
        $subject = LegacyRegistration::fromData(['food' => '']);

        self::assertFalse(
            $subject->hasFood()
        );
    }

    /**
     * @test
     */
    public function hasFoodForNonEmptyFoodReturnsTrue(): void
    {
        $subject = LegacyRegistration::fromData(['food' => 'two donuts']);

        self::assertTrue(
            $subject->hasFood()
        );
    }

    // Tests concerning the accommodation

    /**
     * @test
     */
    public function getAccommodationReturnsAccommodation(): void
    {
        $accommodation = 'a tent in the woods';

        $subject = LegacyRegistration::fromData(['accommodation' => $accommodation]);

        self::assertSame(
            $accommodation,
            $subject->getAccommodation()
        );
    }

    /**
     * @test
     */
    public function hasAccommodationForEmptyAccommodationReturnsFalse(): void
    {
        $subject = LegacyRegistration::fromData(['accommodation' => '']);

        self::assertFalse(
            $subject->hasAccommodation()
        );
    }

    /**
     * @test
     */
    public function hasAccommodationForNonEmptyAccommodationReturnsTrue(): void
    {
        $subject = LegacyRegistration::fromData(['accommodation' => 'a youth hostel']);

        self::assertTrue(
            $subject->hasAccommodation()
        );
    }

    // Tests concerning the interests

    /**
     * @test
     */
    public function getInterestsReturnsInterests(): void
    {
        $interests = 'new experiences';

        $subject = LegacyRegistration::fromData(['interests' => $interests]);

        self::assertSame(
            $interests,
            $subject->getInterests()
        );
    }

    /**
     * @test
     */
    public function hasInterestsForEmptyInterestsReturnsFalse(): void
    {
        $subject = LegacyRegistration::fromData(['interests' => '']);

        self::assertFalse(
            $subject->hasInterests()
        );
    }

    /**
     * @test
     */
    public function hasInterestsForNonEmptyInterestsReturnsTrue(): void
    {
        $subject = LegacyRegistration::fromData(['interests' => 'meeting people']);

        self::assertTrue(
            $subject->hasInterests()
        );
    }
}
