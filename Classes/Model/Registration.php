<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a registration for an event.
 */
class Registration extends AbstractModel implements Titled
{
    /**
     * @return string the title of this registration, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $title the title of this registration, must not be empty
     *
     * @throws \InvalidArgumentException
     */
    public function setTitle(string $title): void
    {
        if ($title === '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333296917);
        }

        $this->setAsString('title', $title);
    }

    public function getFrontEndUser(): ?FrontEndUser
    {
        /** @var FrontEndUser|null $user */
        $user = $this->getAsModel('user');

        return $user;
    }

    public function setFrontEndUser(FrontEndUser $user): void
    {
        $this->set('user', $user);
    }

    public function getEvent(): Event
    {
        /** @var Event $event */
        $event = $this->getAsModel('seminar');

        return $event;
    }

    /**
     * Returns the event of this registration.
     *
     * This is an alias for `getEvent` necessary for the relation to the event.
     *
     * @see getEvent
     */
    public function getSeminar(): ?Event
    {
        return $this->getEvent();
    }

    public function setEvent(Event $event): void
    {
        $this->set('seminar', $event);
    }

    /**
     * Sets the event of this registration.
     *
     * This is an alias for setEvent necessary for the relation to the event.
     *
     * @see setEvent
     */
    public function setSeminar(Event $event): void
    {
        $this->setEvent($event);
    }

    public function isOnRegistrationQueue(): bool
    {
        return $this->getAsBoolean('registration_queue');
    }

    public function setOnRegistrationQueue(bool $isOnQueue): void
    {
        $this->setAsBoolean('registration_queue', $isOnQueue);
    }

    /**
     * @return string the name of the price of this registration, e.g. "Price regular", might be empty
     */
    public function getPrice(): string
    {
        return $this->getAsString('price');
    }

    /**
     * @param string $price the name of the price of this registration to set, e.g. "Price regular", may be empty
     */
    public function setPrice(string $price): void
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
     * @throws \InvalidArgumentException
     */
    public function setSeats(int $seats): void
    {
        if ($seats < 0) {
            throw new \InvalidArgumentException('The parameter $seats must be >= 0.', 1333296926);
        }

        $this->setAsInteger('seats', $seats);
    }

    /**
     * Returns whether the front-end user registered themselves.
     */
    public function hasRegisteredThemselves(): bool
    {
        return $this->getAsBoolean('registered_themselves');
    }

    /**
     * Sets whether the front-end user registered themselves.
     */
    public function setRegisteredThemselves(bool $registeredThemselves): void
    {
        $this->setAsBoolean('registered_themselves', $registeredThemselves);
    }

    /**
     * @return float the total price of this registration, will be >= 0
     */
    public function getTotalPrice(): float
    {
        return $this->getAsFloat('total_price');
    }

    /**
     * @param float $price the total price of to set, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setTotalPrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.', 1333296931);
        }

        $this->setAsFloat('total_price', $price);
    }

    /**
     * @return string the names of the attendees of this registration separated by CRLF, might be empty
     */
    public function getAttendeesNames(): string
    {
        return $this->getAsString('attendees_names');
    }

    /**
     * @param string $attendeesNames the names of the attendees of this registration to set separated
     *        by CRLF, may be empty
     */
    public function setAttendeesNames(string $attendeesNames): void
    {
        $this->setAsString('attendees_names', $attendeesNames);
    }

    /**
     * Gets the additional persons (FE users) attached to this registration.
     *
     * @return Collection<FrontEndUser>
     */
    public function getAdditionalPersons(): Collection
    {
        /** @var Collection<FrontEndUser> $additionalPersons */
        $additionalPersons = $this->getAsCollection('additional_persons');

        return $additionalPersons;
    }

    /**
     * Sets the additional persons attached to this registration.
     *
     * @param Collection<FrontEndUser> $persons
     */
    public function setAdditionalPersons(Collection $persons): void
    {
        $this->set('additional_persons', $persons);
    }

    public function isPaid(): bool
    {
        return $this->getPaymentDateAsUnixTimestamp() > 0;
    }

    public function getPaymentDateAsUnixTimestamp(): int
    {
        return $this->getAsInteger('datepaid');
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function setPaymentDateAsUnixTimestamp(int $timestamp): void
    {
        if ($timestamp < 0) {
            throw new \InvalidArgumentException('The parameter $timestamp must be >= 0.', 1333296945);
        }

        $this->setAsInteger('datepaid', $timestamp);
    }

    public function getPaymentMethod(): ?\Tx_Seminars_Model_PaymentMethod
    {
        /** @var \Tx_Seminars_Model_PaymentMethod|null $paymentMethod */
        $paymentMethod = $this->getAsModel('method_of_payment');

        return $paymentMethod;
    }

    public function setPaymentMethod(?\Tx_Seminars_Model_PaymentMethod $paymentMethod = null): void
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
     */
    public function setAccountNumber(string $accountNumber): void
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
     * @param string $bankCode the bank code of the bank account of this registration, may be empty
     */
    public function setBankCode(string $bankCode): void
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
     */
    public function setBankName(string $bankName): void
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
     */
    public function setAccountOwner(string $accountOwner): void
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
     */
    public function setCompany(string $company): void
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

    public function setName(string $name): void
    {
        $this->setAsString('name', $name);
    }

    /**
     * Returns the gender of the billing address of this registration.
     *
     * @return int the gender of this registration, will be one of the
     *                 following:
     *                 - FrontEndUser::GENDER_MALE
     *                 - FrontEndUser::GENDER_FEMALE
     *                 - FrontEndUser::GENDER_UNKNOWN
     */
    public function getGender(): int
    {
        return $this->getAsInteger('gender');
    }

    /**
     * Sets the gender of the billing address of this registration.
     *
     * @param int $gender the gender of this registration, must be one of the following:
     *        - FrontEndUser::GENDER_MALE
     *        - FrontEndUser::GENDER_FEMALE
     *        - FrontEndUser::GENDER_UNKNOWN
     *
     * @throws \InvalidArgumentException
     */
    public function setGender(int $gender): void
    {
        $allowedGenders = [
            OelibFrontEndUser::GENDER_MALE,
            OelibFrontEndUser::GENDER_FEMALE,
            OelibFrontEndUser::GENDER_UNKNOWN,
        ];

        if (!in_array($gender, $allowedGenders, true)) {
            throw new \InvalidArgumentException(
                'The parameter $gender must be one of the following: FrontEndUser::GENDER_MALE, ' .
                'FrontEndUser::GENDER_FEMALE, FrontEndUser::GENDER_UNKNOWN',
                1333296957
            );
        }

        $this->setAsInteger('gender', $gender);
    }

    /**
     * Returns the address (usually only the street) of the billing address of this registration.
     *
     * @return string the address of this registration, might be empty
     */
    public function getAddress(): string
    {
        return $this->getAsString('address');
    }

    /**
     * Sets the address (usually only the street) of the billing address of this registration.
     *
     * @param string $address the address of this registration to set, may be empty
     */
    public function setAddress(string $address): void
    {
        $this->setAsString('address', $address);
    }

    /**
     * Returns the ZIP code of the billing address of this registration.
     *
     * @return string the ZIP code of this registration, might be empty
     */
    public function getZip(): string
    {
        return $this->getAsString('zip');
    }

    /**
     * Sets the ZIP code of the billing address of this registration.
     *
     * @param string $zip the ZIP code of this registration to set, may be empty
     */
    public function setZip(string $zip): void
    {
        $this->setAsString('zip', $zip);
    }

    /**
     * Returns the city of the billing address of this registration.
     *
     * @return string the city of this registration, might be empty
     */
    public function getCity(): string
    {
        return $this->getAsString('city');
    }

    /**
     * Sets the city of the billing address of this registration.
     *
     * @param string $city the city of this registration to set, may be empty
     */
    public function setCity(string $city): void
    {
        $this->setAsString('city', $city);
    }

    /**
     * Returns the country name of the billing address of this registration.
     *
     * @return string the country name of this registration, might be empty
     */
    public function getCountry(): string
    {
        return $this->getAsString('country');
    }

    /**
     * Sets the country name of the billing address of this registration.
     *
     * @param string $country the country name of this registration to set
     */
    public function setCountry(string $country): void
    {
        $this->setAsString('country', $country);
    }

    /**
     * Returns the phone number of the billing address of this registration.
     *
     * @return string the phone number of this registration, might be empty
     */
    public function getPhone(): string
    {
        return $this->getAsString('telephone');
    }

    /**
     * Sets the phone number of the billing address of this registration.
     *
     * @param string $phone the phone number of this registration, may be empty
     */
    public function setPhone(string $phone): void
    {
        $this->setAsString('telephone', $phone);
    }

    /**
     * Returns the e-mail address of the billing address of this registration.
     *
     * @return string the e-mail address of this registration, might be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * Sets the e-mail address of the billing address of this registration.
     *
     * @param string $email the e-mail address of this registration, may be empty
     */
    public function setEnailAddress(string $email): void
    {
        $this->setAsString('email', $email);
    }

    /**
     * Returns whether the attendees of this registration have attended the event.
     */
    public function hasAttended(): bool
    {
        return $this->getAsBoolean('been_there');
    }

    /**
     * @return string the interests of this registration, might be empty
     */
    public function getInterests(): string
    {
        return $this->getAsString('interests');
    }

    /**
     * @param string $interests the interests of this registration to set, may be empty
     */
    public function setInterests(string $interests): void
    {
        $this->setAsString('interests', $interests);
    }

    /**
     * @return string the expectations of this registration, might be empty
     */
    public function getExpectations(): string
    {
        return $this->getAsString('expectations');
    }

    /**
     * @param string $expectations the expectations of this registration, may be empty
     */
    public function setExpectations(string $expectations): void
    {
        $this->setAsString('expectations', $expectations);
    }

    /**
     * @return string the background knowledge of this registration, might be empty
     */
    public function getBackgroundKnowledge(): string
    {
        return $this->getAsString('background_knowledge');
    }

    /**
     * @param string $backgroundKnowledge the background knowledge of this registration to set, may be empty
     */
    public function setBackgroundKnowledge(string $backgroundKnowledge): void
    {
        $this->setAsString('background_knowledge', $backgroundKnowledge);
    }

    /**
     * @return string the accommodation of this registration, might be empty
     */
    public function getAccommodation(): string
    {
        return $this->getAsString('accommodation');
    }

    /**
     * @param string $accommodation the accommodation of this registration to set, may be empty
     */
    public function setAccommodation(string $accommodation): void
    {
        $this->setAsString('accommodation', $accommodation);
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Lodging>
     */
    public function getLodgings(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Lodging> $lodgings */
        $lodgings = $this->getAsCollection('lodgings');

        return $lodgings;
    }

    /**
     * @return string the food of this registration, might be empty
     */
    public function getFood(): string
    {
        return $this->getAsString('food');
    }

    /**
     * @param string $food the food of this registration to set, may be empty
     */
    public function setFood(string $food): void
    {
        $this->setAsString('food', $food);
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Food>
     */
    public function getFoods(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Food> $foods */
        $foods = $this->getAsCollection('foods');

        return $foods;
    }

    /**
     * @return string where the attendee has heard of the event of this registration, might be empty
     */
    public function getKnownFrom(): string
    {
        return $this->getAsString('known_from');
    }

    /**
     * @param string $knownFrom where the attendee has heard of the event of this registration to set, may be empty
     */
    public function setKnownFrom(string $knownFrom): void
    {
        $this->setAsString('known_from', $knownFrom);
    }

    /**
     * @return string the notes of this registration, might be empty
     */
    public function getNotes(): string
    {
        return $this->getAsString('notes');
    }

    /**
     * @param string $notes the notes of this registration, may be empty
     */
    public function setNotes(string $notes): void
    {
        $this->setAsString('notes', $notes);
    }

    /**
     * @return int the number of kids of this registration, will be >= 0
     */
    public function getKids(): int
    {
        return $this->getAsInteger('kids');
    }

    /**
     * @param int $kids the number of kids of this registration to set, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setKids(int $kids): void
    {
        if ($kids < 0) {
            throw new \InvalidArgumentException('The parameter $kids must be >= 0.', 1333296998);
        }

        $this->setAsString('kids', $kids);
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Checkbox>
     */
    public function getCheckboxes(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Checkbox> $checkboxes */
        $checkboxes = $this->getAsCollection('checkboxes');

        return $checkboxes;
    }
}
