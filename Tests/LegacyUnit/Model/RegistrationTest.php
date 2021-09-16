<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RegistrationTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Registration
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->subject = new \Tx_Seminars_Model_Registration();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////
    // Tests regarding the title.
    ///////////////////////////////

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $title must not be empty.'
        );

        $this->subject->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('registration for event');

        self::assertEquals(
            'registration for event',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'registration for event']);

        self::assertEquals(
            'registration for event',
            $this->subject->getTitle()
        );
    }

    ////////////////////////////////////////
    // Tests regarding the front-end user.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function setFrontEndUserSetsFrontEndUser()
    {
        /** @var FrontEndUser $frontEndUser */
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)->getNewGhost();
        $this->subject->setFrontEndUser($frontEndUser);

        self::assertSame(
            $frontEndUser,
            $this->subject->getFrontEndUser()
        );
    }

    ///////////////////////////////
    // Tests regarding the event.
    ///////////////////////////////

    /**
     * @test
     */
    public function getEventReturnsEvent()
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getNewGhost();
        $this->subject->setData(['seminar' => $event]);

        self::assertSame(
            $event,
            $this->subject->getEvent()
        );
    }

    /**
     * @test
     */
    public function getSeminarReturnsEvent()
    {
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getNewGhost();
        $this->subject->setData(['seminar' => $event]);

        self::assertSame(
            $event,
            $this->subject->getSeminar()
        );
    }

    /**
     * @test
     */
    public function setEventSetsEvent()
    {
        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getNewGhost();
        $this->subject->setEvent($event);

        self::assertSame(
            $event,
            $this->subject->getEvent()
        );
    }

    /**
     * @test
     */
    public function setSeminarSetsEvent()
    {
        /** @var \Tx_Seminars_Model_Event $event */
        $event = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getNewGhost();
        $this->subject->setSeminar($event);

        self::assertSame(
            $event,
            $this->subject->getEvent()
        );
    }

    /////////////////////////////////////////////////////////////////////
    // Tests regarding isOnRegistrationQueue and setOnRegistrationQueue
    /////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function isOnRegistrationQueueForRegularRegistrationReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->isOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function isOnRegistrationQueueForQueueRegistrationReturnsTrue()
    {
        $this->subject->setData(['registration_queue' => true]);

        self::assertTrue(
            $this->subject->isOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function setOnRegistrationQueueTrueSetsRegistrationQueuetoToTrue()
    {
        $this->subject->setData(['registration_queue' => false]);
        $this->subject->setOnRegistrationQueue(true);

        self::assertTrue(
            $this->subject->isOnRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function setOnRegistrationQueueFalseSetsRegistrationQueuetoToFalse()
    {
        $this->subject->setData(['registration_queue' => true]);
        $this->subject->setOnRegistrationQueue(false);

        self::assertFalse(
            $this->subject->isOnRegistrationQueue()
        );
    }

    ///////////////////////////////
    // Tests regarding the price.
    ///////////////////////////////

    /**
     * @test
     */
    public function setPriceSetsPrice()
    {
        $price = 'Price Regular';
        $this->subject->setPrice($price);

        self::assertEquals(
            $price,
            $this->subject->getPrice()
        );
    }

    /**
     * @test
     */
    public function getPriceWithNonEmptyPriceReturnsPrice()
    {
        $price = 'Price Regular';
        $this->subject->setData(['price' => $price]);

        self::assertEquals(
            $price,
            $this->subject->getPrice()
        );
    }

    /**
     * @test
     */
    public function getPriceWithoutPriceReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getPrice()
        );
    }

    ///////////////////////////////
    // Tests regarding the seats.
    ///////////////////////////////

    /**
     * @test
     */
    public function getSeatsWithoutSeatsReturnsZero()
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getSeats()
        );
    }

    /**
     * @test
     */
    public function getSeatsWithNonZeroSeatsReturnsSeats()
    {
        $this->subject->setData(['seats' => 42]);

        self::assertEquals(
            42,
            $this->subject->getSeats()
        );
    }

    /**
     * @test
     */
    public function setSeatsSetsSeats()
    {
        $this->subject->setSeats(42);

        self::assertEquals(
            42,
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

    ///////////////////////////////////////////////
    // Tests regarding hasRegisteredThemselves().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function hasRegisteredThemselvesForThirdPartyRegistrationReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function hasRegisteredThemselvesForSelfRegistrationReturnsTrue()
    {
        $this->subject->setData(['registered_themselves' => true]);

        self::assertTrue(
            $this->subject->hasRegisteredThemselves()
        );
    }

    /**
     * @test
     */
    public function setRegisteredThemselvesSetsRegisteredThemselves()
    {
        $this->subject->setRegisteredThemselves(true);

        self::assertTrue(
            $this->subject->hasRegisteredThemselves()
        );
    }

    /////////////////////////////////////
    // Tests regarding the total price.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getTotalPriceWithoutTotalPriceReturnsZero()
    {
        $this->subject->setData([]);

        self::assertEquals(
            0.00,
            $this->subject->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function getTotalPriceWithTotalPriceReturnsTotalPrice()
    {
        $this->subject->setData(['total_price' => 42.13]);

        self::assertEquals(
            42.13,
            $this->subject->getTotalPrice()
        );
    }

    /**
     * @test
     */
    public function setTotalPriceForNegativePriceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $price must be >= 0.'
        );

        $this->subject->setTotalPrice(-1);
    }

    /**
     * @test
     */
    public function setTotalPriceSetsTotalPrice()
    {
        $this->subject->setTotalPrice(42.13);

        self::assertEquals(
            42.13,
            $this->subject->getTotalPrice()
        );
    }

    /////////////////////////////////////////
    // Tests regarding the attendees names.
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getAttendeesNamesWithoutAttendeesNamesReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function getAttendeesNamesWithAttendeesNamesReturnsAttendeesNames()
    {
        $this->subject->setData(['attendees_names' => 'John Doe']);

        self::assertEquals(
            'John Doe',
            $this->subject->getAttendeesNames()
        );
    }

    /**
     * @test
     */
    public function setAttendeesNamesSetsAttendeesNames()
    {
        $this->subject->setAttendeesNames('John Doe');

        self::assertEquals(
            'John Doe',
            $this->subject->getAttendeesNames()
        );
    }

    //////////////////////////////
    // Tests regarding isPaid().
    //////////////////////////////

    /**
     * @test
     */
    public function isPaidForUnpaidRegistrationReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->isPaid()
        );
    }

    /**
     * @test
     */
    public function isPaidForPaidRegistrationReturnsTrue()
    {
        $this->subject->setData(['datepaid' => $GLOBALS['SIM_EXEC_TIME']]);

        self::assertTrue(
            $this->subject->isPaid()
        );
    }

    //////////////////////////////////////
    // Tests regarding the payment date.
    //////////////////////////////////////

    /**
     * @test
     */
    public function getPaymentDateAsUnixTimestampWithoutPaymentDateReturnsZero()
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getPaymentDateAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function getPaymentDateAsUnixTimestampWithPaymentDateReturnsPaymentDate()
    {
        $this->subject->setData(['datepaid' => 42]);

        self::assertEquals(
            42,
            $this->subject->getPaymentDateAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function setPaymentDateAsUnixTimestampSetsPaymentDate()
    {
        $this->subject->setPaymentDateAsUnixTimestamp(42);

        self::assertEquals(
            42,
            $this->subject->getPaymentDateAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function setPaymentDateAsUnixTimestampWithNegativeTimestampThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $timestamp must be >= 0.'
        );

        $this->subject->setPaymentDateAsUnixTimestamp(-1);
    }

    ////////////////////////////////////////
    // Tests regarding the payment method.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function setPaymentMethodSetsPaymentMethod()
    {
        /** @var \Tx_Seminars_Model_PaymentMethod $paymentMethod */
        $paymentMethod = MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class)->getNewGhost();
        $this->subject->setPaymentMethod($paymentMethod);

        self::assertSame(
            $paymentMethod,
            $this->subject->getPaymentMethod()
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodCanSetPaymentMethodToNull()
    {
        $this->subject->setPaymentMethod();

        self::assertNull(
            $this->subject->getPaymentMethod()
        );
    }

    ////////////////////////////////////////
    // Tests regarding the account number.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getAccountNumberWithoutAccountNumberReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function getAccountNumberWithAccountNumberReturnsAccountNumber()
    {
        $this->subject->setData(['account_number' => '1234567']);

        self::assertEquals(
            '1234567',
            $this->subject->getAccountNumber()
        );
    }

    /**
     * @test
     */
    public function setAccountNumberSetsAccountNumber()
    {
        $this->subject->setAccountNumber('1234567');

        self::assertEquals(
            '1234567',
            $this->subject->getAccountNumber()
        );
    }

    ///////////////////////////////////
    // Tests regarding the bank code.
    ///////////////////////////////////

    /**
     * @test
     */
    public function getBankCodeWithoutBankCodeReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getBankCode()
        );
    }

    /**
     * @test
     */
    public function getBankCodeWithBankCodeReturnsBankCode()
    {
        $this->subject->setData(['bank_code' => '1234567']);

        self::assertEquals(
            '1234567',
            $this->subject->getBankCode()
        );
    }

    /**
     * @test
     */
    public function setBankCodeSetsBankCode()
    {
        $this->subject->setBankCode('1234567');

        self::assertEquals(
            '1234567',
            $this->subject->getBankCode()
        );
    }

    ///////////////////////////////////
    // Tests regarding the bank name.
    ///////////////////////////////////

    /**
     * @test
     */
    public function getBankNameWithoutBankNameReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getBankName()
        );
    }

    /**
     * @test
     */
    public function getBankNameWithBankNameReturnsBankName()
    {
        $this->subject->setData(['bank_name' => 'Cayman Island Bank']);

        self::assertEquals(
            'Cayman Island Bank',
            $this->subject->getBankName()
        );
    }

    /**
     * @test
     */
    public function setBankNameSetsBankName()
    {
        $this->subject->setBankName('Cayman Island Bank');

        self::assertEquals(
            'Cayman Island Bank',
            $this->subject->getBankName()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the account owner.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getAccountOwnerWithoutAccountOwnerReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function getAccountOwnerWithAccountOwnerReturnsAccountOwner()
    {
        $this->subject->setData(['account_owner' => 'John Doe']);

        self::assertEquals(
            'John Doe',
            $this->subject->getAccountOwner()
        );
    }

    /**
     * @test
     */
    public function setAccountOwnerSetsAccountOwner()
    {
        $this->subject->setAccountOwner('John Doe');

        self::assertEquals(
            'John Doe',
            $this->subject->getAccountOwner()
        );
    }

    /////////////////////////////////
    // Tests regarding the company.
    /////////////////////////////////

    /**
     * @test
     */
    public function getCompanyWithoutCompanyReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getCompany()
        );
    }

    /**
     * @test
     */
    public function getCompanyWithCompanyReturnsCompany()
    {
        $this->subject->setData(['company' => 'Example Inc.']);

        self::assertEquals(
            'Example Inc.',
            $this->subject->getCompany()
        );
    }

    /**
     * @test
     */
    public function setCompanySetsCompany()
    {
        $this->subject->setCompany('Example Inc.');

        self::assertEquals(
            'Example Inc.',
            $this->subject->getCompany()
        );
    }

    //////////////////////////////
    // Tests regarding the name.
    //////////////////////////////

    /**
     * @test
     */
    public function getNameWithoutNameReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameWithNameReturnsName()
    {
        $this->subject->setData(['name' => 'John Doe']);

        self::assertEquals(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function setNameSetsName()
    {
        $this->subject->setName('John Doe');

        self::assertEquals(
            'John Doe',
            $this->subject->getName()
        );
    }

    ////////////////////////////////
    // Tests regarding the gender.
    ////////////////////////////////

    /**
     * @test
     */
    public function getGenderWithGenderMaleReturnsGenderMale()
    {
        $this->subject->setData([]);

        self::assertEquals(
            FrontEndUser::GENDER_MALE,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function getGenderWithGenderFemaleReturnsGenderFemale()
    {
        $this->subject->setData(
            ['gender' => FrontEndUser::GENDER_FEMALE]
        );

        self::assertEquals(
            FrontEndUser::GENDER_FEMALE,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function getGenderWithGenderUnknownReturnsGenderUnknown()
    {
        $this->subject->setData(
            ['gender' => FrontEndUser::GENDER_UNKNOWN]
        );

        self::assertEquals(
            FrontEndUser::GENDER_UNKNOWN,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function setGenderWithUnsupportedGenderThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $gender must be one of the following: FrontEndUser::GENDER_MALE, ' .
            'FrontEndUser::GENDER_FEMALE, FrontEndUser::GENDER_UNKNOWN'
        );

        $this->subject->setGender(-1);
    }

    /**
     * @test
     */
    public function setGenderWithGenderMaleSetsGender()
    {
        $this->subject->setGender(FrontEndUser::GENDER_MALE);

        self::assertEquals(
            FrontEndUser::GENDER_MALE,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function setGenderWithGenderFemaleSetsGender()
    {
        $this->subject->setGender(FrontEndUser::GENDER_FEMALE);

        self::assertEquals(
            FrontEndUser::GENDER_FEMALE,
            $this->subject->getGender()
        );
    }

    /**
     * @test
     */
    public function setGenderWithGenderUnknownSetsGender()
    {
        $this->subject->setGender(FrontEndUser::GENDER_UNKNOWN);

        self::assertEquals(
            FrontEndUser::GENDER_UNKNOWN,
            $this->subject->getGender()
        );
    }

    /////////////////////////////////
    // Tests regarding the address.
    /////////////////////////////////

    /**
     * @test
     */
    public function getAddressWithoutAddressReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function getAddressWithAdressReturnsAddress()
    {
        $this->subject->setData(['address' => 'Main Street 123']);

        self::assertEquals(
            'Main Street 123',
            $this->subject->getAddress()
        );
    }

    /**
     * @test
     */
    public function setAddressSetsAddress()
    {
        $this->subject->setAddress('Main Street 123');

        self::assertEquals(
            'Main Street 123',
            $this->subject->getAddress()
        );
    }

    //////////////////////////////////
    // Tests regarding the ZIP code.
    //////////////////////////////////

    /**
     * @test
     */
    public function getZipWithoutZipReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function getZipWithZipReturnsZip()
    {
        $this->subject->setData(['zip' => '12345']);

        self::assertEquals(
            '12345',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function setZipSetsZip()
    {
        $this->subject->setZip('12345');

        self::assertEquals(
            '12345',
            $this->subject->getZip()
        );
    }

    //////////////////////////////
    // Tests regarding the city.
    //////////////////////////////

    /**
     * @test
     */
    public function getCityWithoutCityReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function getCityWithCityReturnsCity()
    {
        $this->subject->setData(['city' => 'Nowhere Ville']);

        self::assertEquals(
            'Nowhere Ville',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function setCitySetsCity()
    {
        $this->subject->setCity('Nowhere Ville');

        self::assertEquals(
            'Nowhere Ville',
            $this->subject->getCity()
        );
    }

    /////////////////////////////////
    // Tests regarding the country.
    /////////////////////////////////

    /**
     * @test
     */
    public function getCountryInitiallyReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getCountry()
        );
    }

    /**
     * @test
     */
    public function setCountrySetsCountry()
    {
        $country = 'Germany';
        $this->subject->setCountry($country);

        self::assertSame(
            $country,
            $this->subject->getCountry()
        );
    }

    // Tests regarding the phone number.

    /**
     * @test
     */
    public function getPhoneWithoutPhoneReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getPhone()
        );
    }

    /**
     * @test
     */
    public function getPhoneWithPhoneReturnsPhone()
    {
        $this->subject->setData(['telephone' => '+49123456789']);

        self::assertEquals(
            '+49123456789',
            $this->subject->getPhone()
        );
    }

    /**
     * @test
     */
    public function setPhoneSetsPhone()
    {
        $this->subject->setPhone('+49123456789');

        self::assertEquals(
            '+49123456789',
            $this->subject->getPhone()
        );
    }

    ////////////////////////////////////////
    // Tests regarding the e-mail address.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getEmailAddressWithoutEmailAddressReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressWithEmailAddressReturnsEmailAddress()
    {
        $this->subject->setData(['email' => 'john@doe.com']);

        self::assertEquals(
            'john@doe.com',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function setEmailAddressSetsEmailAddress()
    {
        $this->subject->setEnailAddress('john@doe.com');

        self::assertEquals(
            'john@doe.com',
            $this->subject->getEmailAddress()
        );
    }

    ////////////////////////////////////
    // Tests regarding hasAttended().
    ////////////////////////////////////

    /**
     * @test
     */
    public function hasAttendedWithoutAttendeeHasAttendedReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAttended()
        );
    }

    /**
     * @test
     */
    public function hasAttendedWithAttendeeHasAttendedReturnsTrue()
    {
        $this->subject->setData(['been_there' => true]);

        self::assertTrue(
            $this->subject->hasAttended()
        );
    }

    ///////////////////////////////////
    // Tests regarding the interests.
    ///////////////////////////////////

    /**
     * @test
     */
    public function getInterestsWithoutInterestsReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getInterests()
        );
    }

    /**
     * @test
     */
    public function getInterestsWithInterestsReturnsInterests()
    {
        $this->subject->setData(['interests' => 'TYPO3']);

        self::assertEquals(
            'TYPO3',
            $this->subject->getInterests()
        );
    }

    /**
     * @test
     */
    public function setInterestsSetsInterests()
    {
        $this->subject->setInterests('TYPO3');

        self::assertEquals(
            'TYPO3',
            $this->subject->getInterests()
        );
    }

    //////////////////////////////////////
    // Tests regarding the expectations.
    //////////////////////////////////////

    /**
     * @test
     */
    public function getExpectationsWithoutExpectationsReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getExpectations()
        );
    }

    /**
     * @test
     */
    public function getExpectationsWithExpectationsReturnsExpectations()
    {
        $this->subject->setData(
            ['expectations' => 'It\'s going to be nice.']
        );

        self::assertEquals(
            'It\'s going to be nice.',
            $this->subject->getExpectations()
        );
    }

    /**
     * @test
     */
    public function setExpectationsSetsExpectations()
    {
        $this->subject->setExpectations('It\'s going to be nice.');

        self::assertEquals(
            'It\'s going to be nice.',
            $this->subject->getExpectations()
        );
    }

    //////////////////////////////////////////////
    // Tests regarding the background knowledge.
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function getBackgroundKnowledgeWithoutBackgroundKnowledgeReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getBackgroundKnowledge()
        );
    }

    /**
     * @test
     */
    public function getBackgroundKnowledgeWithBackgroundKnowledgeReturnsBackgroundKnowledge()
    {
        $this->subject->setData(['background_knowledge' => 'Unit Testing']);

        self::assertEquals(
            'Unit Testing',
            $this->subject->getBackgroundKnowledge()
        );
    }

    /**
     * @test
     */
    public function setBackgroundKnowledgeSetsBackgroundKnowledge()
    {
        $this->subject->setBackgroundKnowledge('Unit Testing');

        self::assertEquals(
            'Unit Testing',
            $this->subject->getBackgroundKnowledge()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the accommodation.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getAccommodationWithoutAccommodationReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getAccommodation()
        );
    }

    /**
     * @test
     */
    public function getAccommodationWithAccommodationReturnsAccommodation()
    {
        $this->subject->setData(['accommodation' => 'tent']);

        self::assertEquals(
            'tent',
            $this->subject->getAccommodation()
        );
    }

    /**
     * @test
     */
    public function setAccommodationSetsAccommodation()
    {
        $this->subject->setAccommodation('tent');

        self::assertEquals(
            'tent',
            $this->subject->getAccommodation()
        );
    }

    //////////////////////////////
    // Tests regarding the food.
    //////////////////////////////

    /**
     * @test
     */
    public function getFoodWithoutFoodReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getFood()
        );
    }

    /**
     * @test
     */
    public function getFoodWithFoodReturnsFood()
    {
        $this->subject->setData(['food' => 'delicious food']);

        self::assertEquals(
            'delicious food',
            $this->subject->getFood()
        );
    }

    /**
     * @test
     */
    public function setFoodSetsFood()
    {
        $this->subject->setFood('delicious food');

        self::assertEquals(
            'delicious food',
            $this->subject->getFood()
        );
    }

    ////////////////////////////////////
    // Tests regarding the known from.
    ////////////////////////////////////

    /**
     * @test
     */
    public function getKnownFromWithoutKnownFromReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getKnownFrom()
        );
    }

    /**
     * @test
     */
    public function getKnownFromWithKnownFromReturnsKnownFrom()
    {
        $this->subject->setData(['known_from' => 'Google']);

        self::assertEquals(
            'Google',
            $this->subject->getKnownFrom()
        );
    }

    /**
     * @test
     */
    public function setKnownFromSetsKnownFrom()
    {
        $this->subject->setKnownFrom('Google');

        self::assertEquals(
            'Google',
            $this->subject->getKnownFrom()
        );
    }

    ///////////////////////////////
    // Tests regarding the notes.
    ///////////////////////////////

    /**
     * @test
     */
    public function getNotesWithoutNotesReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesWithNotesReturnsNotes()
    {
        $this->subject->setData(['notes' => 'This is a nice registration.']);

        self::assertEquals(
            'This is a nice registration.',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function setNotesSetsNotes()
    {
        $this->subject->setNotes('This is a nice registration.');

        self::assertEquals(
            'This is a nice registration.',
            $this->subject->getNotes()
        );
    }

    //////////////////////////////
    // Tests regarding the kids.
    //////////////////////////////

    /**
     * @test
     */
    public function getKidsWithoutKidsReturnsZero()
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getKids()
        );
    }

    /**
     * @test
     */
    public function getKidsWithKidsReturnsKids()
    {
        $this->subject->setData(['kids' => 3]);

        self::assertEquals(
            3,
            $this->subject->getKids()
        );
    }

    /**
     * @test
     */
    public function setKidsWithNegativeKidsThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $kids must be >= 0.'
        );

        $this->subject->setKids(-1);
    }

    /**
     * @test
     */
    public function setKidsWithPositiveKidsSetsKids()
    {
        $this->subject->setKids(3);

        self::assertEquals(
            3,
            $this->subject->getKids()
        );
    }

    ///////////////////////////////////////////////////////
    // Tests concerning the additional registered persons
    ///////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getAdditionalPersonsGetsAdditionalPersons()
    {
        $additionalPersons = new Collection();
        $this->subject->setData(
            ['additional_persons' => $additionalPersons]
        );

        self::assertSame(
            $additionalPersons,
            $this->subject->getAdditionalPersons()
        );
    }

    /**
     * @test
     */
    public function setAdditionalPersonsSetsAdditionalPersons()
    {
        $additionalPersons = new Collection();
        $this->subject->setAdditionalPersons($additionalPersons);

        self::assertSame(
            $additionalPersons,
            $this->subject->getAdditionalPersons()
        );
    }
}
