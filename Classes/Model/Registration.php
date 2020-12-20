<?php

declare(strict_types=1);

/**
 * This class represents a registration for an event.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Model_Registration extends \Tx_Oelib_Model implements \Tx_Seminars_Interface_Titled
{
    /**
     * Returns the title of this registration.
     *
     * @return string the title of this registration, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * Sets the title of this registration.
     *
     * @param string $title the title of this registration, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setTitle(string $title)
    {
        if ($title === '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296917);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Returns the front-end user of this registration.
     *
     * @return \Tx_Seminars_Model_FrontEndUser|null
     */
    public function getFrontEndUser()
    {
        /** @var \Tx_Seminars_Model_FrontEndUser|null $user */
        $user = $this->getAsModel('user');

        return $user;
    }

    /**
     * Sets the front-end user of this registration.
     *
     * @param \Tx_Oelib_Model_FrontEndUser $user the front-end user to set for this registration
     *
     * @return void
     */
    public function setFrontEndUser(\Tx_Oelib_Model_FrontEndUser $user)
    {
        $this->set('user', $user);
    }

    /**
     * Returns the event of this registration.
     *
     * @return \Tx_Seminars_Model_Event the event of this registration
     */
    public function getEvent(): \Tx_Seminars_Model_Event
    {
        /** @var \Tx_Seminars_Model_Event $event */
        $event = $this->getAsModel('seminar');

        return $event;
    }

    /**
     * Returns the event of this registration.
     *
     * This is an alias for getEvent necessary for the relation to the event.
     *
     * @return \Tx_Seminars_Model_Event|null
     *
     * @see getEvent
     */
    public function getSeminar(): \Tx_Seminars_Model_Event
    {
        return $this->getEvent();
    }

    /**
     * Sets the event of this registration.
     *
     * @param \Tx_Seminars_Model_Event $event the event to set for this registration
     *
     * @return void
     */
    public function setEvent(\Tx_Seminars_Model_Event $event)
    {
        $this->set('seminar', $event);
    }

    /**
     * Sets the event of this registration.
     *
     * This is an alias for setEvent necessary for the relation to the event.
     *
     * @param \Tx_Seminars_Model_Event $event the event to set for this registration
     *
     * @see setEvent
     *
     * @return void
     */
    public function setSeminar(\Tx_Seminars_Model_Event $event)
    {
        $this->setEvent($event);
    }

    /**
     * Returns whether this registration is on the registration queue.
     *
     * @return bool TRUE if this registration is on the registration queue, FALSE otherwise
     */
    public function isOnRegistrationQueue(): bool
    {
        return $this->getAsBoolean('registration_queue');
    }

    /**
     * Sets whether this registration is on the registration queue.
     *
     * @param bool $isOnQueue whether this registration should be on the registration queue
     *
     * @return void
     */
    public function setOnRegistrationQueue(bool $isOnQueue)
    {
        $this->setAsBoolean('registration_queue', $isOnQueue);
    }

    /**
     * Returns the name of the price of this registration.
     *
     * @return string the name of the price of this registration, e.g. "Price regular", might be empty
     */
    public function getPrice(): string
    {
        return $this->getAsString('price');
    }

    /**
     * Sets the name of the price of this registration.
     *
     * @param string $price the name of the price of this registration to set, e.g. "Price regular", may be empty
     *
     * @return void
     */
    public function setPrice(string $price)
    {
        $this->setAsString('price', $price);
    }

    /**
     * Returns the number of registered seats of this registration.
     *
     * In older versions 0 equals 1 seat, which is deprecated.
     *
     * @return int the number of registered seats of this registration, will be >= 0
     */
    public function getSeats(): int
    {
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
     * @throws \InvalidArgumentException
     */
    public function setSeats(int $seats)
    {
        if ($seats < 0) {
            throw new \InvalidArgumentException('The parameter $seats must be >= 0.', 1333296926);
        }

        $this->setAsInteger('seats', $seats);
    }

    /**
     * Returns whether the front-end user registered themselves.
     *
     * @return bool TRUE if the front-end user registered themselves, FALSE otherwise
     */
    public function hasRegisteredThemselves(): bool
    {
        return $this->getAsBoolean('registered_themselves');
    }

    /**
     * Sets whether the front-end user registered themselves.
     *
     * @param bool $registeredThemselves whether the front-end user registered themselves
     *
     * @return void
     */
    public function setRegisteredThemselves(bool $registeredThemselves)
    {
        $this->setAsBoolean('registered_themselves', $registeredThemselves);
    }

    /**
     * Returns the total price of this registration.
     *
     * @return float the total price of this registration, will be >= 0
     */
    public function getTotalPrice(): float
    {
        return $this->getAsFloat('total_price');
    }

    /**
     * Sets the total price of the registration.
     *
     * @param float $price the total price of to set, must be >= 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setTotalPrice(float $price)
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.', 1333296931);
        }

        $this->setAsFloat('total_price', $price);
    }

    /**
     * Returns the names of the attendees of this registration.
     *
     * @return string the names of the attendees of this registration separated by CRLF, might be empty
     */
    public function getAttendeesNames(): string
    {
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
    public function setAttendeesNames(string $attendeesNames)
    {
        $this->setAsString('attendees_names', $attendeesNames);
    }

    /**
     * Gets the additional persons (FE users) attached to this registration.
     *
     * @return \Tx_Oelib_List additional persons, will be empty if there are none
     */
    public function getAdditionalPersons(): \Tx_Oelib_List
    {
        return $this->getAsList('additional_persons');
    }

    /**
     * Sets the additional persons attached to this registration.
     *
     * @param \Tx_Oelib_List $persons the additional persons (FE users), may be empty
     *
     * @return void
     */
    public function setAdditionalPersons(\Tx_Oelib_List $persons)
    {
        $this->set('additional_persons', $persons);
    }

    /**
     * Returns whether this registration is paid.
     *
     * @return bool TRUE if this registration has a payment date, FALSE otherwise
     */
    public function isPaid(): bool
    {
        return $this->getPaymentDateAsUnixTimestamp() > 0;
    }

    /**
     * Returns the payment date of this registration as a UNIX timestamp.
     *
     * @return int the payment date of this registration as a UNIX timestamp, will be >= 0
     */
    public function getPaymentDateAsUnixTimestamp(): int
    {
        return $this->getAsInteger('datepaid');
    }

    /**
     * Sets the payment date of this registration as a UNIX timestamp.
     *
     * @param int $timestamp the payment date of this registration as a UNIX timestamp, must be >= 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setPaymentDateAsUnixTimestamp(int $timestamp)
    {
        if ($timestamp < 0) {
            throw new \InvalidArgumentException('The parameter $timestamp must be >= 0.', 1333296945);
        }

        $this->setAsInteger('datepaid', $timestamp);
    }

    /**
     * Returns the payment method of this registration.
     *
     * @return \Tx_Seminars_Model_PaymentMethod|null
     */
    public function getPaymentMethod()
    {
        /** @var \Tx_Seminars_Model_PaymentMethod|null $paymentMethod */
        $paymentMethod = $this->getAsModel('method_of_payment');

        return $paymentMethod;
    }

    /**
     * Sets the payment method of this registration.
     *
     * @param \Tx_Seminars_Model_PaymentMethod|null $paymentMethod
     *        the payment method of this registration to set, use NULL to set no payment method
     *
     * @return void
     */
    public function setPaymentMethod(\Tx_Seminars_Model_PaymentMethod $paymentMethod = null)
    {
        $this->set('method_of_payment', $paymentMethod);
    }

    /**
     * Returns the account number of the bank account of this registration.
     *
     * @return string the account number of the bank account of this registration, might be empty
     */
    public function getAccountNumber(): string
    {
        return $this->getAsString('account_number');
    }

    /**
     * Sets the account number of the bank account of this registration.
     *
     * @param string $accountNumber the account number of the bank account of this registration to , may be empty
     *
     * @return void
     */
    public function setAccountNumber(string $accountNumber)
    {
        $this->setAsString('account_number', $accountNumber);
    }

    /**
     * Returns the bank code of the bank account of this registration.
     *
     * @return string the bank code of the bank account of this registration, might be empty
     */
    public function getBankCode(): string
    {
        return $this->getAsString('bank_code');
    }

    /**
     * Sets the bank code of the bank account of this registration.
     *
     * @param string $bankCode *        the bank code of the bank account of this registration, may be empty
     *
     * @return void
     */
    public function setBankCode(string $bankCode)
    {
        $this->setAsString('bank_code', $bankCode);
    }

    /**
     * Returns the bank name of the bank account of this registration.
     *
     * @return string the bank name of the bank account of this registration, might be empty
     */
    public function getBankName(): string
    {
        return $this->getAsString('bank_name');
    }

    /**
     * Sets the bank name of the bank account of this registration.
     *
     * @param string $bankName the bank name of the bank account of this registration to set, may be empty
     *
     * @return void
     */
    public function setBankName(string $bankName)
    {
        $this->setAsString('bank_name', $bankName);
    }

    /**
     * Returns the name of the owner of the bank account of this registration.
     *
     * @return string the name of the owner of the bank account of this registration, might be empty
     */
    public function getAccountOwner(): string
    {
        return $this->getAsString('account_owner');
    }

    /**
     * Sets the name of the owner of the bank account of this registration.
     *
     * @param string $accountOwner the name of the owner of the bank account of this registration, may be empty
     *
     * @return void
     */
    public function setAccountOwner(string $accountOwner)
    {
        $this->setAsString('account_owner', $accountOwner);
    }

    /**
     * Returns the name of the company of the billing address of this registration.
     *
     * @return string the name of the company of this registration, might be empty
     */
    public function getCompany(): string
    {
        return $this->getAsString('company');
    }

    /**
     * Sets the name of the company of the billing address of this registration.
     *
     * @param string $company the name of the company of this registration, may be empty
     *
     * @return void
     */
    public function setCompany(string $company)
    {
        $this->setAsString('company', $company);
    }

    /**
     * Returns the name of the billing address of this registration.
     *
     * @return string the name of this registration, might be empty
     */
    public function getName(): string
    {
        return $this->getAsString('name');
    }

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->setAsString('name', $name);
    }

    /**
     * Returns the gender of the billing address of this registration.
     *
     * @return int the gender of this registration, will be one of the
     *                 following:
     *                 - \Tx_Oelib_Model_FrontEndUser::GENDER_MALE
     *                 - \Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE
     *                 - \Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN
     */
    public function getGender(): int
    {
        return $this->getAsInteger('gender');
    }

    /**
     * Sets the gender of the billing address of this registration.
     *
     * @param int $gender
     *        the gender of this registration, must be one of the following:
     *        - \Tx_Oelib_Model_FrontEndUser::GENDER_MALE
     *        - \Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE
     *        - \Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setGender(int $gender)
    {
        $allowedGenders = [
            \Tx_Oelib_Model_FrontEndUser::GENDER_MALE,
            \Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE,
            \Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN,
        ];

        if (!in_array($gender, $allowedGenders, true)) {
            throw new \InvalidArgumentException(
                'The parameter $gender must be one of the following: \\Tx_Oelib_Model_FrontEndUser::GENDER_MALE, ' .
                'Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE, \\Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN',
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
    public function getAddress(): string
    {
        return $this->getAsString('address');
    }

    /**
     * Sets the address (usually only the street) of the billing address of this registration.
     *
     * @param string $address the address of this registration to set, may be empty
     *
     * @return void
     */
    public function setAddress(string $address)
    {
        $this->setAsString('address', $address);
    }

    /**
     * Returns the ZIP code of the billing address of this registration.
     *
     * @return string the ZIP code of this registration, will be empty
     */
    public function getZip(): string
    {
        return $this->getAsString('zip');
    }

    /**
     * Sets the ZIP code of the billing address of this registration.
     *
     * @param string $zip the ZIP code of this registration to set, may be empty
     *
     * @return void
     */
    public function setZip(string $zip)
    {
        $this->setAsString('zip', $zip);
    }

    /**
     * Returns the city of the billing address of this registration.
     *
     * @return string the city of this registration, will be empty
     */
    public function getCity(): string
    {
        return $this->getAsString('city');
    }

    /**
     * Sets the city of the billing address of this registration.
     *
     * @param string $city the city of this registration to set, may be empty
     *
     * @return void
     */
    public function setCity(string $city)
    {
        $this->setAsString('city', $city);
    }

    /**
     * Returns the country name of the billing address of this registration.
     *
     * @return string the country name of this registration, will be empty
     */
    public function getCountry(): string
    {
        return $this->getAsString('country');
    }

    /**
     * Sets the country name of the billing address of this registration.
     *
     * @param string $country the country name of this registration to set
     *
     * @return void
     */
    public function setCountry(string $country)
    {
        $this->setAsString('country', $country);
    }

    /**
     * Returns the phone number of the billing address of this registration.
     *
     * @return string the phone number of this registration, will be empty
     */
    public function getPhone(): string
    {
        return $this->getAsString('telephone');
    }

    /**
     * Sets the phone number of the billing address of this registration.
     *
     * @param string $phone the phone number of this registration, may be empty
     *
     * @return void
     */
    public function setPhone(string $phone)
    {
        $this->setAsString('telephone', $phone);
    }

    /**
     * Returns the e-mail address of the billing address of this registration.
     *
     * @return string the e-mail address of this registration, will be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * Sets the e-mail address of the billing address of this registration.
     *
     * @param string $email the e-mail address of this registration, may be empty
     *
     * @return void
     */
    public function setEnailAddress(string $email)
    {
        $this->setAsString('email', $email);
    }

    /**
     * Returns whether the attendees of this registration have attended the event.
     *
     * @return bool TRUE if the attendees of this registration have attended the event, FALSE otherwise
     */
    public function hasAttended(): bool
    {
        return $this->getAsBoolean('been_there');
    }

    /**
     * Returns the interests of this registration.
     *
     * @return string the interests of this registration, will be empty
     */
    public function getInterests(): string
    {
        return $this->getAsString('interests');
    }

    /**
     * Sets the interests of this registration.
     *
     * @param string $interests the interests of this registration to set, may be empty
     *
     * @return void
     */
    public function setInterests(string $interests)
    {
        $this->setAsString('interests', $interests);
    }

    /**
     * Returns the expectations of this registration.
     *
     * @return string the expectations of this registration, will be empty
     */
    public function getExpectations(): string
    {
        return $this->getAsString('expectations');
    }

    /**
     * Sets the expectations of this registration.
     *
     * @param string $expectations the expectations of this registration, may be empty
     *
     * @return void
     */
    public function setExpectations(string $expectations)
    {
        $this->setAsString('expectations', $expectations);
    }

    /**
     * Returns the background knowledge of this registration.
     *
     * @return string the background knowledge of this registration, will be empty
     */
    public function getBackgroundKnowledge(): string
    {
        return $this->getAsString('background_knowledge');
    }

    /**
     * Sets the background knowledge of this registration.
     *
     * @param string $backgroundKnowledge the background knowledge of this registration to set, may be empty
     *
     * @return void
     */
    public function setBackgroundKnowledge(string $backgroundKnowledge)
    {
        $this->setAsString('background_knowledge', $backgroundKnowledge);
    }

    /**
     * Returns the accommodation of this registration.
     *
     * @return string the accommodation of this registration, will be empty
     */
    public function getAccommodation(): string
    {
        return $this->getAsString('accommodation');
    }

    /**
     * Sets the accommodation of this registration.
     *
     * @param string $accommodation the accommodation of this registration to set, may be empty
     *
     * @return void
     */
    public function setAccommodation(string $accommodation)
    {
        $this->setAsString('accommodation', $accommodation);
    }

    /**
     * Returns the lodgings of this registration.
     *
     * @return \Tx_Oelib_List the lodgings of this registration
     */
    public function getLodgings(): \Tx_Oelib_List
    {
        return $this->getAsList('lodgings');
    }

    /**
     * Returns the food of this registration.
     *
     * @return string the food of this registration, will be empty
     */
    public function getFood(): string
    {
        return $this->getAsString('food');
    }

    /**
     * Sets the food of this registration.
     *
     * @param string $food the food of this registration to set, may be empty
     *
     * @return void
     */
    public function setFood(string $food)
    {
        $this->setAsString('food', $food);
    }

    /**
     * Returns the foods of this registration.
     *
     * @return \Tx_Oelib_List the foods of this registration
     */
    public function getFoods(): \Tx_Oelib_List
    {
        return $this->getAsList('foods');
    }

    /**
     * Returns where the attendee has heard of the event of this registration.
     *
     * @return string where the attendee has heard of the event of this registration, will be empty
     */
    public function getKnownFrom(): string
    {
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
    public function setKnownFrom(string $knownFrom)
    {
        $this->setAsString('known_from', $knownFrom);
    }

    /**
     * Returns the notes of this registration.
     *
     * @return string the notes of this registration, will be empty
     */
    public function getNotes(): string
    {
        return $this->getAsString('notes');
    }

    /**
     * Sets the notes of this registration.
     *
     * @param string $notes the notes of this registration, may be empty
     *
     * @return void
     */
    public function setNotes(string $notes)
    {
        $this->setAsString('notes', $notes);
    }

    /**
     * Returns the number of kids of this registration.
     *
     * @return int the number of kids of this registration, will be >= 0
     */
    public function getKids(): int
    {
        return $this->getAsInteger('kids');
    }

    /**
     * Sets the number of kids of this registration.
     *
     * @param int $kids the number of kids of this registration to set, must be >= 0
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setKids(int $kids)
    {
        if ($kids < 0) {
            throw new \InvalidArgumentException('The parameter $kids must be >= 0.', 1333296998);
        }

        $this->setAsString('kids', $kids);
    }

    /**
     * Returns the checkboxes of this registration.
     *
     * @return \Tx_Oelib_List the checkboxes of this registration
     */
    public function getCheckboxes(): \Tx_Oelib_List
    {
        return $this->getAsList('checkboxes');
    }
}
