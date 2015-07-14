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
 * This class represents a registration for an event.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_Registration extends tx_oelib_Model implements tx_seminars_Interface_Titled {
	/**
	 * Returns the title of this registration.
	 *
	 * @return string the title of this registration, will not be empty
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Sets the title of this registration.
	 *
	 * @param string $title the title of this registration, must not be empty
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setTitle($title) {
		if ($title === '') {
			throw new InvalidArgumentException('The parameter $title must not be empty.', 1333296917);
		}

		$this->setAsString('title', $title);
	}

	/**
	 * Returns the front-end user of this registration.
	 *
	 * @return tx_seminars_Model_FrontEndUser the front-end user of this registration
	 */
	public function getFrontEndUser() {
		return $this->getAsModel('user');
	}

	/**
	 * Sets the front-end user of this registration.
	 *
	 * @param tx_oelib_Model_FrontEndUser $user the front-end user to set for this registration
	 *
	 * @return void
	 */
	public function setFrontEndUser(tx_oelib_Model_FrontEndUser $user) {
		$this->set('user', $user);
	}

	/**
	 * Returns the event of this registration.
	 *
	 * @return tx_seminars_Model_Event the event of this registration
	 */
	public function getEvent() {
		return $this->getAsModel('seminar');
	}

	/**
	 * Returns the event of this registration.
	 *
	 * This is an alias for getEvent necessary for the relation to the event.
	 *
	 * @return tx_seminars_Model_Event the event of this registration
	 *
	 * @see getEvent
	 */
	public function getSeminar() {
		return $this->getEvent();
	}

	/**
	 * Sets the event of this registration.
	 *
	 * @param tx_seminars_Model_Event $event the event to set for this registration
	 *
	 * @return void
	 */
	public function setEvent(tx_seminars_Model_Event $event) {
		$this->set('seminar', $event);
	}

	/**
	 * Sets the event of this registration.
	 *
	 * This is an alias for setEvent necessary for the relation to the event.
	 *
	 * @param tx_seminars_Model_Event $event the event to set for this registration
	 *
	 * @see setEvent
	 *
	 * @return void
	 */
	public function setSeminar(tx_seminars_Model_Event $event) {
		$this->setEvent($event);
	}

	/**
	 * Returns whether this registration is on the registration queue.
	 *
	 * @return bool TRUE if this registration is on the registration queue, FALSE otherwise
	 */
	public function isOnRegistrationQueue() {
		return $this->getAsBoolean('registration_queue');
	}

	/**
	 * Sets whether this registration is on the registration queue.
	 *
	 * @param bool $isOnQueue whether this registration should be on the registration queue
	 *
	 * @return void
	 */
	public function setOnRegistrationQueue($isOnQueue) {
		$this->setAsBoolean('registration_queue', $isOnQueue);
	}

	/**
	 * Returns the name of the price of this registration.
	 *
	 * @return string the name of the price of this registration, e.g. "Price regular", might be empty
	 */
	public function getPrice() {
		return $this->getAsString('price');
	}

	/**
	 * Sets the name of the price of this registration.
	 *
	 * @param string $price the name of the price of this registration to set, e.g. "Price regular", may be empty
	 *
	 * @return void
	 */
	public function setPrice($price) {
		$this->setAsString('price', $price);
	}

	/**
	 * Returns the number of registered seats of this registration.
	 *
	 * In older versions 0 equals 1 seat, which is deprecated.
	 *
	 * @return int the number of registered seats of this registration, will be >= 0
	 */
	public function getSeats() {
		return $this->getAsInteger('seats');
	}

	/**
	 * Sets the number of registered seats of this registration.
	 *
	 * In older versions 0 equals 1 seat, which is deprecated.
	 *
	 * @param int $seats the number of registered seats of this registration, must be >= 0
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setSeats($seats) {
		if ($seats < 0) {
			throw new InvalidArgumentException('The parameter $seats must be >= 0.', 1333296926);
		}

		$this->setAsInteger('seats', $seats);
	}

	/**
	 * Returns whether the front-end user registered themselves.
	 *
	 * @return bool TRUE if the front-end user registered themselves, FALSE otherwise
	 */
	public function hasRegisteredThemselves() {
		return $this->getAsBoolean('registered_themselves');
	}

	/**
	 * Sets whether the front-end user registered themselves.
	 *
	 * @param bool $registeredThemselves whether the front-end user registered themselves
	 *
	 * @return void
	 */
	public function setRegisteredThemselves($registeredThemselves) {
		$this->setAsBoolean('registered_themselves', $registeredThemselves);
	}

	/**
	 * Returns the total price of this registration.
	 *
	 * @return float the total price of this registration, will be >= 0
	 */
	public function getTotalPrice() {
		return $this->getAsFloat('total_price');
	}

	/**
	 * Sets the total price of the registration.
	 *
	 * @param float $price the total price of to set, must be >= 0
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setTotalPrice($price) {
		if ($price < 0) {
			throw new InvalidArgumentException('The parameter $price must be >= 0.', 1333296931);
		}

		$this->setAsFloat('total_price', $price);
	}

	/**
	 * Returns the names of the attendees of this registration.
	 *
	 * @return string the names of the attendees of this registration separated by CRLF, might be empty
	 */
	public function getAttendeesNames() {
		return $this->getAsString('attendees_names');
	}

	/**
	 * Sets the names of the attendees of this registration.
	 *
	 * @param string $attendeesNames
	 *        the names of the attendees of this registration to set separated
	 *        by CRLF, may be empty
	 *
	 * @return void
	 */
	public function setAttendeesNames($attendeesNames) {
		$this->setAsString('attendees_names', $attendeesNames);
	}

	/**
	 * Gets the additional persons (FE users) attached to this registration.
	 *
	 * @return tx_oelib_List additional persons, will be empty if there are none
	 */
	public function getAdditionalPersons() {
		return $this->getAsList('additional_persons');
	}

	/**
	 * Sets the additional persons attached to this registration.
	 *
	 * @param tx_oelib_List $persons the additional persons (FE users), may be empty
	 *
	 * @return void
	 */
	public function setAdditionalPersons(tx_oelib_List $persons) {
		$this->set('additional_persons', $persons);
	}

	/**
	 * Returns whether this registration is paid.
	 *
	 * @return bool TRUE if this registration has a payment date, FALSE otherwise
	 */
	public function isPaid() {
		return ($this->getPaymentDateAsUnixTimestamp() > 0);
	}

	/**
	 * Returns the payment date of this registration as a UNIX timestamp.
	 *
	 * @return int the payment date of this registration as a UNIX timestamp, will be >= 0
	 */
	public function getPaymentDateAsUnixTimestamp() {
		return $this->getAsInteger('datepaid');
	}

	/**
	 * Sets the payment date of this registration as a UNIX timestamp.
	 *
	 * @param int $timestamp the payment date of this registration as a UNIX timestamp, must be >= 0
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setPaymentDateAsUnixTimestamp($timestamp) {
		if ($timestamp < 0) {
			throw new InvalidArgumentException('The parameter $timestamp must be >= 0.', 1333296945);
		}

		$this->setAsInteger('datepaid', $timestamp);
	}

	/**
	 * Returns the payment method of this registration.
	 *
	 * @return tx_seminars_Model_PaymentMethod the payment method of this registration
	 */
	public function getPaymentMethod() {
		return $this->getAsModel('method_of_payment');
	}

	/**
	 * Sets the payment method of this registration.
	 *
	 * @param tx_seminars_Model_PaymentMethod $paymentMethod
	 *        the payment method of this registration to set, use NULL to set no payment method
	 *
	 * @return void
	 */
	public function setPaymentMethod(tx_seminars_Model_PaymentMethod $paymentMethod = NULL) {
		$this->set('method_of_payment', $paymentMethod);
	}

	/**
	 * Returns the account number of the bank account of this registration.
	 *
	 * @return string the account number of the bank account of this registration, might be empty
	 */
	public function getAccountNumber() {
		return $this->getAsString('account_number');
	}

	/**
	 * Sets the account number of the bank account of this registration.
	 *
	 * @param string $accountNumber the account number of the bank account of this registration to , may be empty
	 *
	 * @return void
	 */
	public function setAccountNumber($accountNumber) {
		$this->setAsString('account_number', $accountNumber);
	}

	/**
	 * Returns the bank code of the bank account of this registration.
	 *
	 * @return string the bank code of the bank account of this registration, might be empty
	 */
	public function getBankCode() {
		return $this->getAsString('bank_code');
	}

	/**
	 * Sets the bank code of the bank account of this registration.
	 *
	 * @param string $bankCode *        the bank code of the bank account of this registration, may be empty
	 *
	 * @return void
	 */
	public function setBankCode($bankCode) {
		$this->setAsString('bank_code', $bankCode);
	}

	/**
	 * Returns the bank name of the bank account of this registration.
	 *
	 * @return string the bank name of the bank account of this registration, might be empty
	 */
	public function getBankName() {
		return $this->getAsString('bank_name');
	}

	/**
	 * Sets the bank name of the bank account of this registration.
	 *
	 * @param string $bankName the bank name of the bank account of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setBankName($bankName) {
		$this->setAsString('bank_name', $bankName);
	}

	/**
	 * Returns the name of the owner of the bank account of this registration.
	 *
	 * @return string the name of the owner of the bank account of this registration, might be empty
	 */
	public function getAccountOwner() {
		return $this->getAsString('account_owner');
	}

	/**
	 * Sets the name of the owner of the bank account of this registration.
	 *
	 * @param string $accountOwner the name of the owner of the bank account of this registration, may be empty
	 *
	 * @return void
	 */
	public function setAccountOwner($accountOwner) {
		$this->setAsString('account_owner', $accountOwner);
	}

	/**
	 * Returns the name of the company of the billing address of this registration.
	 *
	 * @return string the name of the company of this registration, might be empty
	 */
	public function getCompany() {
		return $this->getAsString('company');
	}

	/**
	 * Sets the name of the company of the billing address of this registration.
	 *
	 * @param string $company the name of the company of this registration, may be empty
	 *
	 * @return void
	 */
	public function setCompany($company) {
		$this->setAsString('company', $company);
	}

	/**
	 * Returns the name of the billing address of this registration.
	 *
	 * @return string the name of this registration, might be empty
	 */
	public function getName() {
		return $this->getAsString('name');
	}

	/**
	 * Sets the name.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function setName($name) {
		$this->setAsString('name', $name);
	}

	/**
	 * Returns the gender of the billing address of this registration.
	 *
	 * @return int the gender of this registration, will be one of the
	 *                 following:
	 *                 - tx_oelib_Model_FrontEndUser::GENDER_MALE
	 *                 - tx_oelib_Model_FrontEndUser::GENDER_FEMALE
	 *                 - tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN
	 */
	public function getGender() {
		return $this->getAsInteger('gender');
	}

	/**
	 * Sets the gender of the billing address of this registration.
	 *
	 * @param int $gender
	 *        the gender of this registration, must be one of the following:
	 *        - tx_oelib_Model_FrontEndUser::GENDER_MALE
	 *        - tx_oelib_Model_FrontEndUser::GENDER_FEMALE
	 *        - tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setGender($gender) {
		$allowedGenders = array(
			tx_oelib_Model_FrontEndUser::GENDER_MALE,
			tx_oelib_Model_FrontEndUser::GENDER_FEMALE,
			tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN,
		);

		if (!in_array($gender, $allowedGenders)) {
			throw new InvalidArgumentException(
				'The parameter $gender must be one of the following: tx_oelib_Model_FrontEndUser::GENDER_MALE, ' .
					'tx_oelib_Model_FrontEndUser::GENDER_FEMALE, tx_oelib_Model_FrontEndUser::GENDER_UNKNOWN',
				1333296957
			);
		}

		$this->setAsInteger('gender', $gender);
	}

	/**
	 * Returns the address (usually only the street) of the billing address of this registration.
	 *
	 * @return string the address of this registration, will be empty
	 */
	public function getAddress() {
		return $this->getAsString('address');
	}

	/**
	 * Sets the address (usually only the street) of the billing address of this registration.
	 *
	 * @param string $address the address of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setAddress($address) {
		$this->setAsString('address', $address);
	}

	/**
	 * Returns the ZIP code of the billing address of this registration.
	 *
	 * @return string the ZIP code of this registration, will be empty
	 */
	public function getZip() {
		return $this->getAsString('zip');
	}

	/**
	 * Sets the ZIP code of the billing address of this registration.
	 *
	 * @param string $zip the ZIP code of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setZip($zip) {
		$this->setAsString('zip', $zip);
	}

	/**
	 * Returns the city of the billing address of this registration.
	 *
	 * @return string the city of this registration, will be empty
	 */
	public function getCity() {
		return $this->getAsString('city');
	}

	/**
	 * Sets the city of the billing address of this registration.
	 *
	 * @param string $city the city of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setCity($city) {
		$this->setAsString('city', $city);
	}

	/**
	 * Returns the country name of the billing address of this registration.
	 *
	 * @return string the country name of this registration, will be empty
	 */
	public function getCountry() {
		return $this->getAsString('country');
	}

	/**
	 * Sets the country name of the billing address of this registration.
	 *
	 * @param string $country the country name of this registration to set
	 *
	 * @return void
	 */
	public function setCountry($country) {
		$this->setAsString('country', $country);
	}

	/**
	 * Returns the phone number of the billing address of this registration.
	 *
	 * @return string the phone number of this registration, will be empty
	 */
	public function getPhone() {
		return $this->getAsString('telephone');
	}

	/**
	 * Sets the phone number of the billing address of this registration.
	 *
	 * @param string $phone the phone number of this registration, may be empty
	 *
	 * @return void
	 */
	public function setPhone($phone) {
		$this->setAsString('telephone', $phone);
	}

	/**
	 * Returns the e-mail address of the billing address of this registration.
	 *
	 * @return string the e-mail address of this registration, will be empty
	 */
	public function getEmailAddress() {
		return $this->getAsString('email');
	}

	/**
	 * Sets the e-mail address of the billing address of this registration.
	 *
	 * @param string $email the e-mail address of this registration, may be empty
	 *
	 * @return void
	 */
	public function setEnailAddress($email) {
		$this->setAsString('email', $email);
	}

	/**
	 * Returns whether the attendees of this registration have attended the event.
	 *
	 * @return bool TRUE if the attendees of this registration have attended the event, FALSE otherwise
	 */
	public function hasAttended() {
		return $this->getAsBoolean('been_there');
	}

	/**
	 * Returns the interests of this registration.
	 *
	 * @return string the interests of this registration, will be empty
	 */
	public function getInterests() {
		return $this->getAsString('interests');
	}

	/**
	 * Sets the interests of this registration.
	 *
	 * @param string $interests the interests of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setInterests($interests) {
		$this->setAsString('interests', $interests);
	}

	/**
	 * Returns the expectations of this registration.
	 *
	 * @return string the expectations of this registration, will be empty
	 */
	public function getExpectations() {
		return $this->getAsString('expectations');
	}

	/**
	 * Sets the expectations of this registration.
	 *
	 * @param string $expectations the expectations of this registration, may be empty
	 *
	 * @return void
	 */
	public function setExpectations($expectations) {
		$this->setAsString('expectations', $expectations);
	}

	/**
	 * Returns the background knowledge of this registration.
	 *
	 * @return string the background knowledge of this registration, will be empty
	 */
	public function getBackgroundKnowledge() {
		return $this->getAsString('background_knowledge');
	}

	/**
	 * Sets the background knowledge of this registration.
	 *
	 * @param string $backgroundKnowledge the background knowledge of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setBackgroundKnowledge($backgroundKnowledge) {
		$this->setAsString('background_knowledge', $backgroundKnowledge);
	}

	/**
	 * Returns the accommodation of this registration.
	 *
	 * @return string the accommodation of this registration, will be empty
	 */
	public function getAccommodation() {
		return $this->getAsString('accommodation');
	}

	/**
	 * Sets the accommodation of this registration.
	 *
	 * @param string $accommodation the accommodation of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setAccommodation($accommodation) {
		$this->setAsString('accommodation', $accommodation);
	}

	/**
	 * Returns the lodgings of this registration.
	 *
	 * @return tx_oelib_List the lodgings of this registration
	 */
	public function getLodgings() {
		return $this->getAsList('lodgings');
	}

	/**
	 * Returns the food of this registration.
	 *
	 * @return string the food of this registration, will be empty
	 */
	public function getFood() {
		return $this->getAsString('food');
	}

	/**
	 * Sets the food of this registration.
	 *
	 * @param string $food the food of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setFood($food) {
		$this->setAsString('food', $food);
	}

	/**
	 * Returns the foods of this registration.
	 *
	 * @return tx_oelib_List the foods of this registration
	 */
	public function getFoods() {
		return $this->getAsList('foods');
	}

	/**
	 * Returns where the attendee has heard of the event of this registration.
	 *
	 * @return string where the attendee has heard of the event of this registration, will be empty
	 */
	public function getKnownFrom() {
		return $this->getAsString('known_from');
	}

	/**
	 * Sets where the attendee has heard of the event of this registration.
	 *
	 * @param string $knownFrom
	 *        where the attendee has heard of the event of this registration to set, may be empty
	 *
	 * @return void
	 */
	public function setKnownFrom($knownFrom) {
		$this->setAsString('known_from', $knownFrom);
	}

	/**
	 * Returns the notes of this registration.
	 *
	 * @return string the notes of this registration, will be empty
	 */
	public function getNotes() {
		return $this->getAsString('notes');
	}

	/**
	 * Sets the notes of this registration.
	 *
	 * @param string $notes the notes of this registration, may be empty
	 *
	 * @return void
	 */
	public function setNotes($notes) {
		$this->setAsString('notes', $notes);
	}

	/**
	 * Returns the number of kids of this registration.
	 *
	 * @return int the number of kids of this registration, will be >= 0
	 */
	public function getKids() {
		return $this->getAsInteger('kids');
	}

	/**
	 * Sets the number of kids of this registration.
	 *
	 * @param int $kids the number of kids of this registration to set, must be >= 0
	 *
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	public function setKids($kids) {
		if ($kids < 0) {
			throw new InvalidArgumentException('The parameter $kids must be >= 0.', 1333296998);
		}

		$this->setAsString('kids', $kids);
	}

	/**
	 * Returns the checkboxes of this registration.
	 *
	 * @return tx_oelib_List the checkboxes of this registration
	 */
	public function getCheckboxes() {
		return $this->getAsList('checkboxes');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/Registration.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/Registration.php']);
}
