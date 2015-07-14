<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_RegistrationTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Registration
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixture = new tx_seminars_Model_Registration();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	///////////////////////////////
	// Tests regarding the title.
	///////////////////////////////

	/**
	 * @test
	 */
	public function setTitleWithEmptyTitleThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $title must not be empty.'
		);

		$this->fixture->setTitle('');
	}

	/**
	 * @test
	 */
	public function setTitleSetsTitle() {
		$this->fixture->setTitle('registration for event');

		self::assertEquals(
			'registration for event',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'registration for event'));

		self::assertEquals(
			'registration for event',
			$this->fixture->getTitle()
		);
	}


	////////////////////////////////////////
	// Tests regarding the front-end user.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function setFrontEndUserSetsFrontEndUser() {
		$frontEndUser = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')
			->getNewGhost();
		$this->fixture->setFrontEndUser($frontEndUser);

		self::assertSame(
			$frontEndUser,
			$this->fixture->getFrontEndUser()
		);
	}


	///////////////////////////////
	// Tests regarding the event.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getEventReturnsEvent() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getNewGhost();
		$this->fixture->setData(array('seminar' => $event));

		self::assertSame(
			$event,
			$this->fixture->getEvent()
		);
	}

	/**
	 * @test
	 */
	public function getSeminarReturnsEvent() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getNewGhost();
		$this->fixture->setData(array('seminar' => $event));

		self::assertSame(
			$event,
			$this->fixture->getSeminar()
		);
	}

	/**
	 * @test
	 */
	public function setEventSetsEvent() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getNewGhost();
		$this->fixture->setEvent($event);

		self::assertSame(
			$event,
			$this->fixture->getEvent()
		);
	}

	/**
	 * @test
	 */
	public function setSeminarSetsEvent() {
		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getNewGhost();
		$this->fixture->setSeminar($event);

		self::assertSame(
			$event,
			$this->fixture->getEvent()
		);
	}


	/////////////////////////////////////////////////////////////////////
	// Tests regarding isOnRegistrationQueue and setOnRegistrationQueue
	/////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function isOnRegistrationQueueForRegularRegistrationReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->isOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function isOnRegistrationQueueForQueueRegistrationReturnsTrue() {
		$this->fixture->setData(array('registration_queue' => TRUE));

		self::assertTrue(
			$this->fixture->isOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function setOnRegistrationQueueTrueSetsRegistrationQueuetoToTrue() {
		$this->fixture->setData(array('registration_queue' => FALSE));
		$this->fixture->setOnRegistrationQueue(TRUE);

		self::assertTrue(
			$this->fixture->isOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function setOnRegistrationQueueFalseSetsRegistrationQueuetoToFalse() {
		$this->fixture->setData(array('registration_queue' => TRUE));
		$this->fixture->setOnRegistrationQueue(FALSE);

		self::assertFalse(
			$this->fixture->isOnRegistrationQueue()
		);
	}


	///////////////////////////////
	// Tests regarding the price.
	///////////////////////////////

	/**
	 * @test
	 */
	public function setPriceSetsPrice() {
		$price = 'Price Regular';
		$this->fixture->setPrice($price);

		self::assertEquals(
			$price,
			$this->fixture->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function getPriceWithNonEmptyPriceReturnsPrice() {
		$price = 'Price Regular';
		$this->fixture->setData(array('price' => $price));

		self::assertEquals(
			$price,
			$this->fixture->getPrice()
		);
	}

	/**
	 * @test
	 */
	public function getPriceWithoutPriceReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getPrice()
		);
	}


	///////////////////////////////
	// Tests regarding the seats.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getSeatsWithoutSeatsReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function getSeatsWithNonZeroSeatsReturnsSeats() {
		$this->fixture->setData(array('seats' => 42));

		self::assertEquals(
			42,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setSeatsSetsSeats() {
		$this->fixture->setSeats(42);

		self::assertEquals(
			42,
			$this->fixture->getSeats()
		);
	}

	/**
	 * @test
	 */
	public function setSeatsWithNegativeSeatsThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $seats must be >= 0.'
		);

		$this->fixture->setSeats(-1);
	}


	///////////////////////////////////////////////
	// Tests regarding hasRegisteredThemselves().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasRegisteredThemselvesForThirdPartyRegistrationReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasRegisteredThemselves()
		);
	}

	/**
	 * @test
	 */
	public function hasRegisteredThemselvesForSelfRegistrationReturnsTrue() {
		$this->fixture->setData(array('registered_themselves' => TRUE));

		self::assertTrue(
			$this->fixture->hasRegisteredThemselves()
		);
	}

	/**
	 * @test
	 */
	public function setRegisteredThemselvesSetsRegisteredThemselves() {
		$this->fixture->setRegisteredThemselves(TRUE);

		self::assertTrue(
			$this->fixture->hasRegisteredThemselves()
		);
	}


	/////////////////////////////////////
	// Tests regarding the total price.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getTotalPriceWithoutTotalPriceReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0.00,
			$this->fixture->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function getTotalPriceWithTotalPriceReturnsTotalPrice() {
		$this->fixture->setData(array('total_price' => 42.13));

		self::assertEquals(
			42.13,
			$this->fixture->getTotalPrice()
		);
	}

	/**
	 * @test
	 */
	public function setTotalPriceForNegativePriceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $price must be >= 0.'
		);

		$this->fixture->setTotalPrice(-1);
	}

	/**
	 * @test
	 */
	public function setTotalPriceSetsTotalPrice() {
		$this->fixture->setTotalPrice(42.13);

		self::assertEquals(
			42.13,
			$this->fixture->getTotalPrice()
		);
	}


	/////////////////////////////////////////
	// Tests regarding the attendees names.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAttendeesNamesWithoutAttendeesNamesReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function getAttendeesNamesWithAttendeesNamesReturnsAttendeesNames() {
		$this->fixture->setData(array('attendees_names' => 'John Doe'));

		self::assertEquals(
			'John Doe',
			$this->fixture->getAttendeesNames()
		);
	}

	/**
	 * @test
	 */
	public function setAttendeesNamesSetsAttendeesNames() {
		$this->fixture->setAttendeesNames('John Doe');

		self::assertEquals(
			'John Doe',
			$this->fixture->getAttendeesNames()
		);
	}


	//////////////////////////////
	// Tests regarding isPaid().
	//////////////////////////////

	/**
	 * @test
	 */
	public function isPaidForUnpaidRegistrationReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->isPaid()
		);
	}

	/**
	 * @test
	 */
	public function isPaidForPaidRegistrationReturnsTrue() {
		$this->fixture->setData(array('datepaid' => $GLOBALS['SIM_EXEC_TIME']));

		self::assertTrue(
			$this->fixture->isPaid()
		);
	}


	//////////////////////////////////////
	// Tests regarding the payment date.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentDateAsUnixTimestampWithoutPaymentDateReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getPaymentDateAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentDateAsUnixTimestampWithPaymentDateReturnsPaymentDate() {
		$this->fixture->setData(array('datepaid' => 42));

		self::assertEquals(
			42,
			$this->fixture->getPaymentDateAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function setPaymentDateAsUnixTimestampSetsPaymentDate() {
		$this->fixture->setPaymentDateAsUnixTimestamp(42);

		self::assertEquals(
			42,
			$this->fixture->getPaymentDateAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function setPaymentDateAsUnixTimestampWithNegativeTimestampThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $timestamp must be >= 0.'
		);

		$this->fixture->setPaymentDateAsUnixTimestamp(-1);
	}


	////////////////////////////////////////
	// Tests regarding the payment method.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function setPaymentMethodSetsPaymentMethod() {
		$paymentMethod = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_PaymentMethod'
		)->getNewGhost();
		$this->fixture->setPaymentMethod($paymentMethod);

		self::assertSame(
			$paymentMethod,
			$this->fixture->getPaymentMethod()
		);
	}

	/**
	 * @test
	 */
	public function setPaymentMethodCanSetPaymentMethodToNull() {
		$this->fixture->setPaymentMethod(NULL);

		self::assertNull(
			$this->fixture->getPaymentMethod()
		);
	}


	////////////////////////////////////////
	// Tests regarding the account number.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAccountNumberWithoutAccountNumberReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getAccountNumber()
		);
	}

	/**
	 * @test
	 */
	public function getAccountNumberWithAccountNumberReturnsAccountNumber() {
		$this->fixture->setData(array('account_number' => '1234567'));

		self::assertEquals(
			'1234567',
			$this->fixture->getAccountNumber()
		);
	}

	/**
	 * @test
	 */
	public function setAccountNumberSetsAccountNumber() {
		$this->fixture->setAccountNumber('1234567');

		self::assertEquals(
			'1234567',
			$this->fixture->getAccountNumber()
		);
	}


	///////////////////////////////////
	// Tests regarding the bank code.
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getBankCodeWithoutBankCodeReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getBankCode()
		);
	}

	/**
	 * @test
	 */
	public function getBankCodeWithBankCodeReturnsBankCode() {
		$this->fixture->setData(array('bank_code' => '1234567'));

		self::assertEquals(
			'1234567',
			$this->fixture->getBankCode()
		);
	}

	/**
	 * @test
	 */
	public function setBankCodeSetsBankCode() {
		$this->fixture->setBankCode('1234567');

		self::assertEquals(
			'1234567',
			$this->fixture->getBankCode()
		);
	}


	///////////////////////////////////
	// Tests regarding the bank name.
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getBankNameWithoutBankNameReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getBankName()
		);
	}

	/**
	 * @test
	 */
	public function getBankNameWithBankNameReturnsBankName() {
		$this->fixture->setData(array('bank_name' => 'Cayman Island Bank'));

		self::assertEquals(
			'Cayman Island Bank',
			$this->fixture->getBankName()
		);
	}

	/**
	 * @test
	 */
	public function setBankNameSetsBankName() {
		$this->fixture->setBankName('Cayman Island Bank');

		self::assertEquals(
			'Cayman Island Bank',
			$this->fixture->getBankName()
		);
	}


	///////////////////////////////////////
	// Tests regarding the account owner.
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getAccountOwnerWithoutAccountOwnerReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getAccountOwner()
		);
	}

	/**
	 * @test
	 */
	public function getAccountOwnerWithAccountOwnerReturnsAccountOwner() {
		$this->fixture->setData(array('account_owner' => 'John Doe'));

		self::assertEquals(
			'John Doe',
			$this->fixture->getAccountOwner()
		);
	}

	/**
	 * @test
	 */
	public function setAccountOwnerSetsAccountOwner() {
		$this->fixture->setAccountOwner('John Doe');

		self::assertEquals(
			'John Doe',
			$this->fixture->getAccountOwner()
		);
	}


	/////////////////////////////////
	// Tests regarding the company.
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getCompanyWithoutCompanyReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function getCompanyWithCompanyReturnsCompany() {
		$this->fixture->setData(array('company' => 'Example Inc.'));

		self::assertEquals(
			'Example Inc.',
			$this->fixture->getCompany()
		);
	}

	/**
	 * @test
	 */
	public function setCompanySetsCompany() {
		$this->fixture->setCompany('Example Inc.');

		self::assertEquals(
			'Example Inc.',
			$this->fixture->getCompany()
		);
	}


	//////////////////////////////
	// Tests regarding the name.
	//////////////////////////////

	/**
	 * @test
	 */
	public function getNameWithoutNameReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameWithNameReturnsName() {
		$this->fixture->setData(array('name' => 'John Doe'));

		self::assertEquals(
			'John Doe',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function setNameSetsName() {
		$this->fixture->setName('John Doe');

		self::assertEquals(
			'John Doe',
			$this->fixture->getName()
		);
	}


	////////////////////////////////
	// Tests regarding the gender.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getGenderWithGenderMaleReturnsGenderMale() {
		$this->fixture->setData(array());

		self::assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_MALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderWithGenderFemaleReturnsGenderFemale() {
		$this->fixture->setData(
			array('gender' => tx_oelib_Model_FrontEndUser::GENDER_FEMALE)
		);

		self::assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderWithGenderUnknownReturnsGenderUnknown() {
		$this->fixture->setData(
			array('gender' => tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN)
		);

		self::assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderWithUnsupportedGenderThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $gender must be one of the following: tx_oelib_Model_FrontEndUser::GENDER_MALE, ' .
				'tx_oelib_Model_FrontEndUser::GENDER_FEMALE, tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN'
		);

		$this->fixture->setGender(-1);
	}

	/**
	 * @test
	 */
	public function setGenderWithGenderMaleSetsGender() {
		$this->fixture->setGender(tx_oelib_Model_FrontEndUser::GENDER_MALE);

		self::assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_MALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderWithGenderFemaleSetsGender() {
		$this->fixture->setGender(tx_oelib_Model_FrontEndUser::GENDER_FEMALE);

		self::assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderWithGenderUnknownSetsGender() {
		$this->fixture->setGender(tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN);

		self::assertEquals(
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
			$this->fixture->getGender()
		);
	}


	/////////////////////////////////
	// Tests regarding the address.
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getAddressWithoutAddressReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function getAddressWithAdressReturnsAddress() {
		$this->fixture->setData(array('address' => 'Main Street 123'));

		self::assertEquals(
			'Main Street 123',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setAddressSetsAddress() {
		$this->fixture->setAddress('Main Street 123');

		self::assertEquals(
			'Main Street 123',
			$this->fixture->getAddress()
		);
	}


	//////////////////////////////////
	// Tests regarding the ZIP code.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getZipWithoutZipReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getZip()
		);
	}

	/**
	 * @test
	 */
	public function getZipWithZipReturnsZip() {
		$this->fixture->setData(array('zip' => '12345'));

		self::assertEquals(
			'12345',
			$this->fixture->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setZipSetsZip() {
		$this->fixture->setZip('12345');

		self::assertEquals(
			'12345',
			$this->fixture->getZip()
		);
	}


	//////////////////////////////
	// Tests regarding the city.
	//////////////////////////////

	/**
	 * @test
	 */
	public function getCityWithoutCityReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getCity()
		);
	}

	/**
	 * @test
	 */
	public function getCityWithCityReturnsCity() {
		$this->fixture->setData(array('city' => 'Nowhere Ville'));

		self::assertEquals(
			'Nowhere Ville',
			$this->fixture->getCity()
		);
	}

	/**
	 * @test
	 */
	public function setCitySetsCity() {
		$this->fixture->setCity('Nowhere Ville');

		self::assertEquals(
			'Nowhere Ville',
			$this->fixture->getCity()
		);
	}


	/////////////////////////////////
	// Tests regarding the country.
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getCountryInitiallyReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setCountrySetsCountry() {
		$country = 'Germany';
		$this->fixture->setCountry($country);

		self::assertSame(
			$country,
			$this->fixture->getCountry()
		);
	}


	/*
	 * Tests regarding the phone number.
	 */

	/**
	 * @test
	 */
	public function getPhoneWithoutPhoneReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getPhone()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneWithPhoneReturnsPhone() {
		$this->fixture->setData(array('telephone' => '+49123456789'));

		self::assertEquals(
			'+49123456789',
			$this->fixture->getPhone()
		);
	}

	/**
	 * @test
	 */
	public function setPhoneSetsPhone() {
		$this->fixture->setPhone('+49123456789');

		self::assertEquals(
			'+49123456789',
			$this->fixture->getPhone()
		);
	}


	////////////////////////////////////////
	// Tests regarding the e-mail address.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEMailAddressWithoutEMailAddressReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function getEMailAddressWithEMailAddressReturnsEMailAddress() {
		$this->fixture->setData(array('email' => 'john@doe.com'));

		self::assertEquals(
			'john@doe.com',
			$this->fixture->getEmailAddress()
		);
	}

	/**
	 * @test
	 */
	public function setEMailAddressSetsEMailAddress() {
		$this->fixture->setEnailAddress('john@doe.com');

		self::assertEquals(
			'john@doe.com',
			$this->fixture->getEmailAddress()
		);
	}


	////////////////////////////////////
	// Tests regarding hasAttended().
	////////////////////////////////////

	/**
	 * @test
	 */
	public function hasAttendedWithoutAttendeeHasAttendedReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasAttended()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendedWithAttendeeHasAttendedReturnsTrue() {
		$this->fixture->setData(array('been_there' => TRUE));

		self::assertTrue(
			$this->fixture->hasAttended()
		);
	}


	///////////////////////////////////
	// Tests regarding the interests.
	///////////////////////////////////

	/**
	 * @test
	 */
	public function getInterestsWithoutInterestsReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getInterests()
		);
	}

	/**
	 * @test
	 */
	public function getInterestsWithInterestsReturnsInterests() {
		$this->fixture->setData(array('interests' => 'TYPO3'));

		self::assertEquals(
			'TYPO3',
			$this->fixture->getInterests()
		);
	}

	/**
	 * @test
	 */
	public function setInterestsSetsInterests() {
		$this->fixture->setInterests('TYPO3');

		self::assertEquals(
			'TYPO3',
			$this->fixture->getInterests()
		);
	}


	//////////////////////////////////////
	// Tests regarding the expectations.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getExpectationsWithoutExpectationsReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getExpectations()
		);
	}

	/**
	 * @test
	 */
	public function getExpectationsWithExpectationsReturnsExpectations() {
		$this->fixture->setData(
			array('expectations' => 'It\'s going to be nice.')
		);

		self::assertEquals(
			'It\'s going to be nice.',
			$this->fixture->getExpectations()
		);
	}

	/**
	 * @test
	 */
	public function setExpectationsSetsExpectations() {
		$this->fixture->setExpectations('It\'s going to be nice.');

		self::assertEquals(
			'It\'s going to be nice.',
			$this->fixture->getExpectations()
		);
	}


	//////////////////////////////////////////////
	// Tests regarding the background knowledge.
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getBackgroundKnowledgeWithoutBackgroundKnowledgeReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getBackgroundKnowledge()
		);
	}

	/**
	 * @test
	 */
	public function getBackgroundKnowledgeWithBackgroundKnowledgeReturnsBackgroundKnowledge() {
		$this->fixture->setData(array('background_knowledge' => 'Unit Testing'));

		self::assertEquals(
			'Unit Testing',
			$this->fixture->getBackgroundKnowledge()
		);
	}

	/**
	 * @test
	 */
	public function setBackgroundKnowledgeSetsBackgroundKnowledge() {
		$this->fixture->setBackgroundKnowledge('Unit Testing');

		self::assertEquals(
			'Unit Testing',
			$this->fixture->getBackgroundKnowledge()
		);
	}


	///////////////////////////////////////
	// Tests regarding the accommodation.
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getAccommodationWithoutAccommodationReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getAccommodation()
		);
	}

	/**
	 * @test
	 */
	public function getAccommodationWithAccommodationReturnsAccommodation() {
		$this->fixture->setData(array('accommodation' => 'tent'));

		self::assertEquals(
			'tent',
			$this->fixture->getAccommodation()
		);
	}

	/**
	 * @test
	 */
	public function setAccommodationSetsAccommodation() {
		$this->fixture->setAccommodation('tent');

		self::assertEquals(
			'tent',
			$this->fixture->getAccommodation()
		);
	}


	//////////////////////////////
	// Tests regarding the food.
	//////////////////////////////

	/**
	 * @test
	 */
	public function getFoodWithoutFoodReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getFood()
		);
	}

	/**
	 * @test
	 */
	public function getFoodWithFoodReturnsFood() {
		$this->fixture->setData(array('food' => 'delicious food'));

		self::assertEquals(
			'delicious food',
			$this->fixture->getFood()
		);
	}

	/**
	 * @test
	 */
	public function setFoodSetsFood() {
		$this->fixture->setFood('delicious food');

		self::assertEquals(
			'delicious food',
			$this->fixture->getFood()
		);
	}


	////////////////////////////////////
	// Tests regarding the known from.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getKnownFromWithoutKnownFromReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getKnownFrom()
		);
	}

	/**
	 * @test
	 */
	public function getKnownFromWithKnownFromReturnsKnownFrom() {
		$this->fixture->setData(array('known_from' => 'Google'));

		self::assertEquals(
			'Google',
			$this->fixture->getKnownFrom()
		);
	}

	/**
	 * @test
	 */
	public function setKnownFromSetsKnownFrom() {
		$this->fixture->setKnownFrom('Google');

		self::assertEquals(
			'Google',
			$this->fixture->getKnownFrom()
		);
	}


	///////////////////////////////
	// Tests regarding the notes.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getNotesWithoutNotesReturnsEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesWithNotesReturnsNotes() {
		$this->fixture->setData(array('notes' => 'This is a nice registration.'));

		self::assertEquals(
			'This is a nice registration.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function setNotesSetsNotes() {
		$this->fixture->setNotes('This is a nice registration.');

		self::assertEquals(
			'This is a nice registration.',
			$this->fixture->getNotes()
		);
	}


	//////////////////////////////
	// Tests regarding the kids.
	//////////////////////////////

	/**
	 * @test
	 */
	public function getKidsWithoutKidsReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getKids()
		);
	}

	/**
	 * @test
	 */
	public function getKidsWithKidsReturnsKids() {
		$this->fixture->setData(array('kids' => 3));

		self::assertEquals(
			3,
			$this->fixture->getKids()
		);
	}

	/**
	 * @test
	 */
	public function setKidsWithNegativeKidsThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $kids must be >= 0.'
		);

		$this->fixture->setKids(-1);
	}

	/**
	 * @test
	 */
	public function setKidsWithPositiveKidsSetsKids() {
		$this->fixture->setKids(3);

		self::assertEquals(
			3,
			$this->fixture->getKids()
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning the additional registered persons
	///////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAdditionalPersonsGetsAdditionalPersons() {
		$additionalPersons = new tx_oelib_List();
		$this->fixture->setData(
			array('additional_persons' => $additionalPersons)
		);

		self::assertSame(
			$additionalPersons,
			$this->fixture->getAdditionalPersons()
		);
	}

	/**
	 * @test
	 */
	public function setAdditionalPersonsSetsAdditionalPersons() {
		$additionalPersons = new tx_oelib_List();
		$this->fixture->setAdditionalPersons($additionalPersons);

		self::assertSame(
			$additionalPersons,
			$this->fixture->getAdditionalPersons()
		);
	}
}