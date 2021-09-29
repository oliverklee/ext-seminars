<?php

declare(strict_types=1);

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Oelib\Mapper\LanguageMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;
use OliverKlee\Oelib\Model\Language;
use OliverKlee\Seminars\Model\Interfaces\Titled;
use OliverKlee\Seminars\Model\Traits\EventEmailSenderTrait;

/**
 * This class represents an event.
 */
class Tx_Seminars_Model_Event extends \Tx_Seminars_Model_AbstractTimeSpan implements Titled
{
    use EventEmailSenderTrait;

    /**
     * @var int represents the type for a single event
     */
    const TYPE_COMPLETE = 0;

    /**
     * @var int represents the type for an event topic
     */
    const TYPE_TOPIC = 1;

    /**
     * @var int represents the type for an event date
     */
    const TYPE_DATE = 2;

    /**
     * @var int the status "planned" for an event
     */
    const STATUS_PLANNED = 0;

    /**
     * @var int the status "canceled" for an event
     */
    const STATUS_CANCELED = 1;

    /**
     * @var int the status "confirmed" for an event
     */
    const STATUS_CONFIRMED = 2;

    protected function getConfiguration(): Configuration
    {
        return ConfigurationRegistry::get('plugin.tx_seminars');
    }

    /**
     * Returns whether this event is a single event.
     *
     * @return bool TRUE if this event is a single event, FALSE otherwise
     */
    public function isSingleEvent(): bool
    {
        return $this->getAsInteger('object_type') == self::TYPE_COMPLETE;
    }

    /**
     * Returns whether this event is a valid event date (i.e., a date with an associated topic).
     *
     * @return bool TRUE if this event is an event date, FALSE otherwise
     */
    public function isEventDate(): bool
    {
        return $this->getAsInteger('object_type') === self::TYPE_DATE && $this->getAsModel('topic') !== null;
    }

    /**
     * Returns the record type of this event, which will be one of the following:
     * - \Tx_Seminars_Model_Event::TYPE_COMPLETE
     * - \Tx_Seminars_Model_Event::TYPE_TOPIC
     * - \Tx_Seminars_Model_Event::TYPE_DATE
     *
     * @return int the record type of this event, will be one of the values
     *                 mentioned above, will be >= 0
     */
    public function getRecordType(): int
    {
        return $this->getAsInteger('object_type');
    }

    /**
     * This method may only be called for date records.
     *
     * @throws \BadMethodCallException if this event is no (valid) date
     */
    public function getTopic(): \Tx_Seminars_Model_Event
    {
        if (!$this->isEventDate()) {
            throw new \BadMethodCallException('This function may only be called for date records.', 1333296324);
        }
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = $this->getAsModel('topic');

        return $topic;
    }

    /**
     * @return string our title, will be empty if this event has no title
     */
    public function getTitle(): string
    {
        return $this->isEventDate() ? $this->getTopic()->getTitle() : $this->getRawTitle();
    }

    /**
     * Returns our direct title, i.e. for date records the date's title, not
     * the topic's title.
     *
     * For single events and dates, this function will return the same as
     * getTitle.
     *
     * @return string our title, will be empty if this event has no title
     */
    public function getRawTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $title our title to set, must not be empty
     *
     * @throws \InvalidArgumentException
     */
    public function setTitle(string $title): void
    {
        if ($title === '') {
            throw new \InvalidArgumentException('$title must not be empty.', 1333293446);
        }

        $this->setAsString('title', $title);
    }

    /**
     * @return string our subtitle, will be empty if this event has no subtitle
     */
    public function getSubtitle(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getSubtitle()
            : $this->getAsString('subtitle');
    }

    /**
     * @param string $subtitle our subtitle to set, may be empty
     */
    public function setSubtitle(string $subtitle): void
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setSubtitle($subtitle);
        } else {
            $this->setAsString('subtitle', $subtitle);
        }
    }

    public function hasSubtitle(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasSubtitle()
            : $this->hasString('subtitle');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Category>
     */
    public function getCategories(): Collection
    {
        if ($this->isEventDate()) {
            return $this->getTopic()->getCategories();
        }

        /** @var Collection<\Tx_Seminars_Model_Category> $categories */
        $categories = $this->getAsCollection('categories');

        return $categories;
    }

    /**
     * @return string our teaser, might be empty
     */
    public function getTeaser(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getTeaser()
            : $this->getAsString('teaser');
    }

    /**
     * @param string $teaser our teaser, may be empty
     */
    public function setTeaser(string $teaser): void
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setTeaser($teaser);
        } else {
            $this->setAsString('teaser', $teaser);
        }
    }

    public function hasTeaser(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasTeaser()
            : $this->hasString('teaser');
    }

    /**
     * @return string our description, might be empty
     */
    public function getDescription(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getDescription()
            : $this->getAsString('description');
    }

    /**
     * @param string $description our description, may be empty
     */
    public function setDescription(string $description): void
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setDescription($description);
        } else {
            $this->setAsString('description', $description);
        }
    }

    public function hasDescription(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasDescription()
            : $this->hasString('description');
    }

    public function getEventType(): ?\Tx_Seminars_Model_EventType
    {
        /** @var Tx_Seminars_Model_EventType|null $type */
        $type = $this->isEventDate() ? $this->getTopic()->getEventType() : $this->getAsModel('event_type');

        return $type;
    }

    public function getTimeZone(): string
    {
        return $this->getAsString('time_zone');
    }

    public function setTimeZone(string $timeZone): void
    {
        $this->setAsString('time_zone', $timeZone);
    }

    /**
     * @return string our accreditation number, will be empty if this event has
     *                no accreditation number
     */
    public function getAccreditationNumber(): string
    {
        return $this->getAsString('accreditation_number');
    }

    /**
     * @param string $accreditationNumber our accreditation number, may be empty
     */
    public function setAccreditationNumber(string $accreditationNumber): void
    {
        $this->setAsString('accreditation_number', $accreditationNumber);
    }

    public function hasAccreditationNumber(): bool
    {
        return $this->hasString('accreditation_number');
    }

    /**
     * @return int our credit points, will be 0 if this event has no credit
     *                 points, will be >= 0
     */
    public function getCreditPoints(): int
    {
        return $this->isEventDate()
            ? $this->getTopic()->getCreditPoints()
            : $this->getAsInteger('credit_points');
    }

    /**
     * @param int $creditPoints our credit points, must be >= 0
     */
    public function setCreditPoints(int $creditPoints): void
    {
        if ($creditPoints < 0) {
            throw new \InvalidArgumentException('The parameter $creditPoints must be >= 0.', 1333296336);
        }

        if ($this->isEventDate()) {
            $this->getTopic()->setCreditPoints($creditPoints);
        } else {
            $this->setAsInteger('credit_points', $creditPoints);
        }
    }

    public function hasCreditPoints(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasCreditPoints()
            : $this->hasInteger('credit_points');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_TimeSlot>
     */
    public function getTimeSlots(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_TimeSlot> $timeSlots */
        $timeSlots = $this->getAsCollection('timeslots');

        return $timeSlots;
    }

    /**
     * @return int our registration deadline as UNIX time-stamp, will be 0 if this event has no registration deadline
     */
    public function getRegistrationDeadlineAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('deadline_registration');
    }

    /**
     * @param int $registrationDeadline our registration deadline as UNIX time-stamp, must be >= 0,
     *        0 unsets the registration deadline
     */
    public function setRegistrationDeadlineAsUnixTimeStamp(int $registrationDeadline): void
    {
        if ($registrationDeadline < 0) {
            throw new \InvalidArgumentException('The parameter $registrationDeadline must be >= 0.', 1333296347);
        }

        $this->setAsInteger('deadline_registration', $registrationDeadline);
    }

    public function hasRegistrationDeadline(): bool
    {
        return $this->hasInteger('deadline_registration');
    }

    /**
     * Returns the latest date/time to register for a seminar.
     * This is either the registration deadline (if set) or the begin date of an
     * event.
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStamp(): int
    {
        if ($this->hasRegistrationDeadline()) {
            return $this->getRegistrationDeadlineAsUnixTimeStamp();
        }
        if ($this->hasEndDate() && $this->getConfiguration()->getAsBoolean('allowRegistrationForStartedEvents')) {
            return $this->getEndDateAsUnixTimeStamp();
        }

        return $this->getBeginDateAsUnixTimeStamp();
    }

    /**
     * Returns our early bird deadline as UNIX time-stamp.
     *
     * @return int our early bird deadline as UNIX time-stamp, will be 0
     *                 if this event has no early bird deadline, will be >= 0
     */
    public function getEarlyBirdDeadlineAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('deadline_early_bird');
    }

    /**
     * @param int $earlyBirdDeadline our early bird deadline as UNIX time-stamp, must be >= 0,
     *        0 unsets the early bird deadline
     */
    public function setEarlyBirdDeadlineAsUnixTimeStamp(int $earlyBirdDeadline): void
    {
        if ($earlyBirdDeadline < 0) {
            throw new \InvalidArgumentException('The parameter $earlyBirdDeadline must be >= 0.', 1333296359);
        }

        $this->setAsInteger('deadline_early_bird', $earlyBirdDeadline);
    }

    public function hasEarlyBirdDeadline(): bool
    {
        return $this->hasInteger('deadline_early_bird');
    }

    /**
     * Returns our unregistration deadline as UNIX time-stamp.
     *
     * @return int our unregistration deadline as UNIX time-stamp, will be
     *                 0 if this event has no unregistration deadline, will be >= 0
     */
    public function getUnregistrationDeadlineAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('deadline_unregistration');
    }

    /**
     * @param int $unregistrationDeadline our unregistration deadline as UNIX time-stamp, must be >= 0,
     *        0 unsets the unregistration deadline
     */
    public function setUnregistrationDeadlineAsUnixTimeStamp(int $unregistrationDeadline): void
    {
        if ($unregistrationDeadline < 0) {
            throw new \InvalidArgumentException('The parameter $unregistrationDeadline must be >= 0.', 1333296369);
        }

        $this->setAsInteger('deadline_unregistration', $unregistrationDeadline);
    }

    public function hasUnregistrationDeadline(): bool
    {
        return $this->hasInteger('deadline_unregistration');
    }

    /**
     * @return int our expiry as UNIX time-stamp, will be 0 if this event
     *                 has no expiry, will be >= 0
     */
    public function getExpiryAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('expiry');
    }

    /**
     * @param int $expiry our expiry as UNIX time-stamp, must be >= 0, 0 unsets the expiry
     */
    public function setExpiryAsUnixTimeStamp(int $expiry): void
    {
        if ($expiry < 0) {
            throw new \InvalidArgumentException('The parameter $expiry must be >= 0.', 1333296380);
        }

        $this->setAsInteger('expiry', $expiry);
    }

    public function hasExpiry(): bool
    {
        return $this->hasInteger('expiry');
    }

    /**
     * @return string our separate details page, will be empty if this event has no separate details page
     */
    public function getDetailsPage(): string
    {
        return $this->getAsString('details_page');
    }

    public function setDetailsPage(string $detailsPage): void
    {
        $this->setAsString('details_page', $detailsPage);
    }

    public function hasDetailsPage(): bool
    {
        return $this->hasString('details_page');
    }

    /**
     * Gets a separate single view page UID (or full URL) for this event,
     * combined from the event itself, the event type and the categories.
     *
     * Note: This function does not take the TS setup configuration or flexform
     * settings into account.
     *
     * @return string the single view page UID/URL, will be an empty string if none has been set
     */
    public function getCombinedSingleViewPage(): string
    {
        $result = '';

        if ($this->hasDetailsPage()) {
            $result = $this->getDetailsPage();
        } elseif ($this->hasSingleViewPageUidFromEventType()) {
            $result = (string)$this->getSingleViewPageUidFromEventType();
        } elseif ($this->hasSingleViewPageUidFromCategories()) {
            $result = (string)$this->getSingleViewPageUidFromCategories();
        }

        return $result;
    }

    public function hasCombinedSingleViewPage(): bool
    {
        return $this->getCombinedSingleViewPage() != '';
    }

    /**
     * @return int the single view page UID from the event type, will be > 0 if
     *         this event has an event type and a that type has a single view
     *         page UID, will be 0 otherwise
     */
    protected function getSingleViewPageUidFromEventType(): int
    {
        if (!$this->hasSingleViewPageUidFromEventType()) {
            return 0;
        }

        return $this->getEventType()->getSingleViewPageUid();
    }

    protected function hasSingleViewPageUidFromEventType(): bool
    {
        return ($this->getEventType() !== null)
            && $this->getEventType()->hasSingleViewPageUid();
    }

    /**
     * This function returns the first found UID from the event categories.
     *
     * @return int the single view page UID from the categories, will be > 0 if
     *         this event has at least one category with a single view page
     *         UID, will be 0 otherwise
     */
    protected function getSingleViewPageUidFromCategories(): int
    {
        $result = 0;

        /** @var \Tx_Seminars_Model_Category $category */
        foreach ($this->getCategories() as $category) {
            if ($category->hasSingleViewPageUid()) {
                $result = $category->getSingleViewPageUid();
                break;
            }
        }

        return $result;
    }

    protected function hasSingleViewPageUidFromCategories(): bool
    {
        return $this->getSingleViewPageUidFromCategories() > 0;
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Place>
     */
    public function getPlaces(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Place> $places */
        $places = $this->getAsCollection('place');

        return $places;
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
     * @return Collection<\Tx_Seminars_Model_Food>
     */
    public function getFoods(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Food> $foods */
        $foods = $this->getAsCollection('foods');

        return $foods;
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Speaker>
     */
    public function getPartners(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Speaker> $partners */
        $partners = $this->getAsCollection('partners');

        return $partners;
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Speaker>
     */
    public function getTutors(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Speaker> $tutors */
        $tutors = $this->getAsCollection('tutors');

        return $tutors;
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Speaker>
     */
    public function getLeaders(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Speaker> $leaders */
        $leaders = $this->getAsCollection('leaders');

        return $leaders;
    }

    public function getLanguage(): ?Language
    {
        if (!$this->hasLanguage()) {
            return null;
        }

        $mapper = MapperRegistry::get(LanguageMapper::class);
        return $mapper->findByIsoAlpha2Code($this->getAsString('language'));
    }

    public function setLanguage(Language $language): void
    {
        $this->setAsString('language', $language->getIsoAlpha2Code());
    }

    public function hasLanguage(): bool
    {
        return $this->hasString('language');
    }

    public function getPriceOnRequest(): bool
    {
        return $this->isEventDate() ? $this->getTopic()->getPriceOnRequest() : $this->getAsBoolean('price_on_request');
    }

    /**
     * @return float our regular price, will be 0.00 if this event has no regular price, will be >= 0.00
     */
    public function getRegularPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getRegularPrice()
            : $this->getAsFloat('price_regular');
    }

    /**
     * @param float $price our regular price, must be >= 0.00
     */
    public function setRegularPrice(float $price): void
    {
        if ($price < 0.00) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.00.', 1333296391);
        }

        if ($this->isEventDate()) {
            $this->getTopic()->setRegularPrice($price);
        } else {
            $this->setAsFloat('price_regular', $price);
        }
    }

    public function hasRegularPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasRegularPrice()
            : $this->hasFloat('price_regular');
    }

    /**
     * @return float our regular early bird price, will be 0.00 if this event has
     *               no regular early bird price, will be >= 0.00
     */
    public function getRegularEarlyBirdPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getRegularEarlyBirdPrice()
            : $this->getAsFloat('price_regular_early');
    }

    /**
     * @param float $price our regular early bird price, must be >= 0.00
     */
    public function setRegularEarlyBirdPrice(float $price): void
    {
        if ($price < 0.00) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.00.', 1333296479);
        }

        if ($this->isEventDate()) {
            $this->getTopic()->setRegularEarlyBirdPrice($price);
        } else {
            $this->setAsFloat('price_regular_early', $price);
        }
    }

    public function hasRegularEarlyBirdPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasRegularEarlyBirdPrice()
            : $this->hasFloat('price_regular_early');
    }

    /**
     * @return float our regular board price, will be 0.00 if this event has no
     *               regular board price, will be >= 0.00
     */
    public function getRegularBoardPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getRegularBoardPrice()
            : $this->getAsFloat('price_regular_board');
    }

    /**
     * @param float $price our regular board price, must be >= 0.00
     */
    public function setRegularBoardPrice(float $price): void
    {
        if ($price < 0.00) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.00.', 1333296604);
        }

        if ($this->isEventDate()) {
            $this->getTopic()->setRegularBoardPrice($price);
        } else {
            $this->setAsFloat('price_regular_board', $price);
        }
    }

    public function hasRegularBoardPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasRegularBoardPrice()
            : $this->hasFloat('price_regular_board');
    }

    /**
     * @return float our special price, will be 0.00 if this event has no special price, will be >= 0.00
     */
    public function getSpecialPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getSpecialPrice()
            : $this->getAsFloat('price_special');
    }

    /**
     * @param float $price our special price, must be >= 0.00
     */
    public function setSpecialPrice(float $price): void
    {
        if ($price < 0.00) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.00.', 1333296667);
        }

        if ($this->isEventDate()) {
            $this->getTopic()->setSpecialPrice($price);
        } else {
            $this->setAsFloat('price_special', $price);
        }
    }

    public function hasSpecialPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasSpecialPrice()
            : $this->hasFloat('price_special');
    }

    /**
     * @return float our special early bird price, will be 0.00 if this event has
     *               no special early bird price, will be >= 0.00
     */
    public function getSpecialEarlyBirdPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getSpecialEarlyBirdPrice()
            : $this->getAsFloat('price_special_early');
    }

    /**
     * @param float $price our special early bird price, must be >= 0.00
     */
    public function setSpecialEarlyBirdPrice(float $price): void
    {
        if ($price < 0.00) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.00.', 1333296677);
        }

        if ($this->isEventDate()) {
            $this->getTopic()->setSpecialEarlyBirdPrice($price);
        } else {
            $this->setAsFloat('price_special_early', $price);
        }
    }

    public function hasSpecialEarlyBirdPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasSpecialEarlyBirdPrice()
            : $this->hasFloat('price_special_early');
    }

    /**
     * @return float our special board price, will be 0.00 if this event has no
     *               special board price, will be >= 0.00
     */
    public function getSpecialBoardPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getSpecialBoardPrice()
            : $this->getAsFloat('price_special_board');
    }

    /**
     * @param float $price our special board price, must be >= 0.00
     */
    public function setSpecialBoardPrice(float $price): void
    {
        if ($price < 0.00) {
            throw new \InvalidArgumentException('The parameter $price must be >= 0.00.', 1333296688);
        }

        if ($this->isEventDate()) {
            $this->getTopic()->setSpecialBoardPrice($price);
        } else {
            $this->setAsFloat('price_special_board', $price);
        }
    }

    public function hasSpecialBoardPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasSpecialBoardPrice()
            : $this->hasFloat('price_special_board');
    }

    /**
     * Checks whether this event is sold with early bird prices.
     *
     * This will return TRUE if the event has a deadline and a price defined
     * for early-bird registrations. If the special price (e.g. for students)
     * is not used, then the student's early bird price is not checked.
     *
     * Attention: Both prices (standard and special) need to have an early bird
     * version for this function to return TRUE (if there is a regular special
     * price).
     *
     * @return bool TRUE if an early bird deadline and early bird prices
     *                 are set, FALSE otherwise
     */
    public function hasEarlyBirdPrice(): bool
    {
        // whether the event has regular prices set (a normal one and an early bird)
        $priceRegularIsOk = $this->hasRegularPrice()
            && $this->hasRegularEarlyBirdPrice();

        // whether no special price is set, or both special prices
        // (normal and early bird) are set
        $priceSpecialIsOk = !$this->hasSpecialPrice()
            || ($this->hasSpecialPrice() && $this->hasSpecialEarlyBirdPrice());

        return $this->hasEarlyBirdDeadline()
            && $priceRegularIsOk
            && $priceSpecialIsOk;
    }

    /**
     * Checks whether the latest possibility to register with early bird rebate
     * for this event is over.
     *
     * The latest moment is just before a set early bird deadline.
     *
     * @return bool TRUE if the deadline has passed, FALSE otherwise
     */
    public function isEarlyBirdDeadlineOver(): bool
    {
        return $GLOBALS['SIM_EXEC_TIME']
            >= $this->getEarlyBirdDeadlineAsUnixTimeStamp();
    }

    /**
     * @return bool TRUE if this event has an early bird deadline set and
     *                 this deadline is not over yet, FALSE otherwise
     */
    public function earlyBirdApplies(): bool
    {
        return $this->hasEarlyBirdPrice() && !$this->isEarlyBirdDeadlineOver();
    }

    /**
     * Gets the list of available prices for this event at this particular time.
     *
     * If there is an early-bird price available and the early-bird deadline has
     * not passed yet, the early-bird price is used.
     *
     * The possible keys of the return value are:
     * regular, regular_early, regular_board,
     * special, special_early, special_board
     *
     * @return array<string, float> the available prices as an associative array, will not be empty
     */
    public function getAvailablePrices(): array
    {
        $result = [];

        $earlyBirdApplies = $this->earlyBirdApplies();

        if ($earlyBirdApplies && $this->hasRegularEarlyBirdPrice()) {
            $result['regular_early'] = $this->getRegularEarlyBirdPrice();
        } else {
            $result['regular'] = $this->getRegularPrice();
        }

        if ($this->hasSpecialPrice()) {
            if ($earlyBirdApplies && $this->hasSpecialEarlyBirdPrice()) {
                $result['special_early'] = $this->getSpecialEarlyBirdPrice();
            } else {
                $result['special'] = $this->getSpecialPrice();
            }
        }

        if ($this->hasRegularBoardPrice()) {
            $result['regular_board'] = $this->getRegularBoardPrice();
        }
        if ($this->hasSpecialBoardPrice()) {
            $result['special_board'] = $this->getSpecialBoardPrice();
        }

        return $result;
    }

    /**
     * @return string our additional information, will be empty if this event
     *                has no additional information
     */
    public function getAdditionalInformation(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getAdditionalInformation()
            : $this->getAsString('additional_information');
    }

    /**
     * @param string $additionalInformation our additional information, may be empty
     */
    public function setAdditionalInformation(string $additionalInformation): void
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setAdditionalInformation($additionalInformation);
        } else {
            $this->setAsString('additional_information', $additionalInformation);
        }
    }

    public function hasAdditionalInformation(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasAdditionalInformation()
            : $this->hasString('additional_information');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_PaymentMethod>
     */
    public function getPaymentMethods(): Collection
    {
        if ($this->isEventDate()) {
            return $this->getTopic()->getPaymentMethods();
        }

        /** @var Collection<\Tx_Seminars_Model_PaymentMethod> $paymentMethods */
        $paymentMethods = $this->getAsCollection('payment_methods');

        return $paymentMethods;
    }

    /**
     * Note: This function should only be called on topic or single event records, not on event dates.
     *
     * @param Collection<\Tx_Seminars_Model_PaymentMethod> $paymentMethods
     */
    public function setPaymentMethods(Collection $paymentMethods): void
    {
        if ($this->isEventDate()) {
            throw new \BadMethodCallException(
                'setPaymentMethods may only be called on single events and ' .
                'event topics, but not on event dates.'
            );
        }

        $this->set('payment_methods', $paymentMethods);
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Organizer>
     */
    public function getOrganizers(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Organizer> $organizers */
        $organizers = $this->getAsCollection('organizers');

        return $organizers;
    }

    public function getFirstOrganizer(): ?\Tx_Seminars_Model_Organizer
    {
        /** @var \Tx_Seminars_Model_Organizer|null $organizer */
        $organizer = $this->getOrganizers()->first();

        return $organizer;
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Organizer>
     */
    public function getOrganizingPartners(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Organizer> $partners */
        $partners = $this->getAsCollection('organizing_partners');

        return $partners;
    }

    public function eventTakesPlaceReminderHasBeenSent(): bool
    {
        return $this->getAsBoolean('event_takes_place_reminder_sent');
    }

    public function cancelationDeadlineReminderHasBeenSent(): bool
    {
        return $this->getAsBoolean('cancelation_deadline_reminder_sent');
    }

    public function needsRegistration(): bool
    {
        return $this->getAsBoolean('needs_registration');
    }

    public function allowsMultipleRegistrations(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->allowsMultipleRegistrations()
            : $this->getAsBoolean('allows_multiple_registrations');
    }

    /**
     * @return int our minimum attendees, will be 0 if this event has no
     *                 minimum attendees, will be >= 0
     */
    public function getMinimumAttendees(): int
    {
        return $this->getAsInteger('attendees_min');
    }

    /**
     * @param int $minimumAttendees our minimum attendees, must be >= 0
     */
    public function setMinimumAttendees(int $minimumAttendees): void
    {
        if ($minimumAttendees < 0) {
            throw new \InvalidArgumentException('The parameter $minimumAttendees must be >= 0.', 1333296697);
        }

        $this->setAsInteger('attendees_min', $minimumAttendees);
    }

    public function hasMinimumAttendees(): bool
    {
        return $this->hasInteger('attendees_min');
    }

    /**
     * @return int our maximum attendees, will be 0 if this event has no
     *                 maximum attendees and allows unlimited number of attendees,
     *                 will be >= 0
     */
    public function getMaximumAttendees(): int
    {
        return $this->getAsInteger('attendees_max');
    }

    /**
     * @param int $maximumAttendees our maximum attendees, must be >= 0, 0 means an unlimited number of attendees
     */
    public function setMaximumAttendees(int $maximumAttendees): void
    {
        if ($maximumAttendees < 0) {
            throw new \InvalidArgumentException('The parameter $maximumAttendees must be >= 0.', 1333296708);
        }

        $this->setAsInteger('attendees_max', $maximumAttendees);
    }

    public function hasMaximumAttendees(): bool
    {
        return $this->hasInteger('attendees_max');
    }

    public function hasUnlimitedVacancies(): bool
    {
        return !$this->hasMaximumAttendees();
    }

    public function hasRegistrationQueue(): bool
    {
        return $this->getAsBoolean('queue_size');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_TargetGroup>
     */
    public function getTargetGroups(): Collection
    {
        if ($this->isEventDate()) {
            return $this->getTopic()->getTargetGroups();
        }

        /** @var Collection<\Tx_Seminars_Model_TargetGroup> $targetGroups */
        $targetGroups = $this->getAsCollection('target_groups');

        return $targetGroups;
    }

    /**
     * Returns whether the collision check should be skipped for this event.
     */
    public function shouldSkipCollisionCheck(): bool
    {
        return $this->getAsBoolean('skip_collision_check');
    }

    /**
     * @return int our status, will be one of STATUS_PLANNED, STATUS_CANCELED or STATUS_CONFIRMED
     */
    public function getStatus(): int
    {
        return $this->getAsInteger('cancelled');
    }

    /**
     * @param int $status our status, must be one of STATUS_PLANNED, STATUS_CANCELED, STATUS_CONFIRMED
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus(int $status): void
    {
        if (!in_array($status, [self::STATUS_PLANNED, self::STATUS_CANCELED, self::STATUS_CONFIRMED], true)) {
            throw new \InvalidArgumentException(
                '$status must be either STATUS_PLANNED, STATUS_CANCELED or STATUS_CONFIRMED.',
                1333296722
            );
        }

        $this->setAsInteger('cancelled', $status);
    }

    /**
     * Checks whether this event still has the "planned" status.
     *
     * @return bool
     */
    public function isPlanned(): bool
    {
        return $this->getStatus() === self::STATUS_PLANNED;
    }

    public function isCanceled(): bool
    {
        return $this->getStatus() === self::STATUS_CANCELED;
    }

    public function isConfirmed(): bool
    {
        return $this->getStatus() === self::STATUS_CONFIRMED;
    }

    /**
     * Marks this event as canceled.
     *
     * If this event already is canceled, this method is a no-op.
     */
    public function cancel(): void
    {
        $this->setStatus(self::STATUS_CANCELED);
    }

    /**
     * Marks this event as confirmed.
     *
     * If this event already is confirmed, this method is a no-op.
     */
    public function confirm(): void
    {
        $this->setStatus(self::STATUS_CONFIRMED);
    }

    public function getOwner(): ?OelibFrontEndUser
    {
        /** @var OelibFrontEndUser|null $owner */
        $owner = $this->getAsModel('owner_feuser');

        return $owner;
    }

    /**
     * @return Collection<OelibFrontEndUser>
     */
    public function getEventManagers(): Collection
    {
        /** @var Collection<OelibFrontEndUser> $managers */
        $managers = $this->getAsCollection('vips');

        return $managers;
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Checkbox>
     */
    public function getCheckboxes(): Collection
    {
        if ($this->isEventDate()) {
            return $this->getTopic()->getCheckboxes();
        }

        /** @var Collection<\Tx_Seminars_Model_Checkbox> $checkboxes */
        $checkboxes = $this->getAsCollection('checkboxes');

        return $checkboxes;
    }

    /**
     * Returns whether this event makes use of the second terms & conditions.
     */
    public function usesTerms2(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->usesTerms2()
            : $this->getAsBoolean('use_terms_2');
    }

    /**
     * @return string our notes, will be empty if this event has no notes
     */
    public function getNotes(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getNotes()
            : $this->getAsString('notes');
    }

    /**
     * @param string $notes our notes, may be empty
     */
    public function setNotes(string $notes): void
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setNotes($notes);
        } else {
            $this->setAsString('notes', $notes);
        }
    }

    public function hasNotes(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasNotes()
            : $this->hasString('notes');
    }

    /**
     * The returned array will be sorted like the files are sorted in the back-end form.
     *
     * @return array<int, string> our attached file names relative to the seminars upload directory,
     *         will be empty if this event has no attached files
     */
    public function getAttachedFiles(): array
    {
        return $this->getAsTrimmedArray('attached_files');
    }

    /**
     * @param array<array-key, string> $attachedFiles our attached file names,
     *        file names must be relative to the seminars upload directory, may be empty
     */
    public function setAttachedFiles(array $attachedFiles): void
    {
        $this->setAsArray('attached_files', $attachedFiles);
    }

    public function hasAttachedFiles(): bool
    {
        return $this->hasString('attached_files');
    }

    /**
     * @return string our image file name relative to the seminars upload directory,
     *         will be empty if this event has no image
     */
    public function getImage(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getImage()
            : $this->getAsString('image');
    }

    /**
     * @param string $image our image file name, must be relative to the seminars upload directory, may be empty
     */
    public function setImage(string $image): void
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setImage($image);
        } else {
            $this->setAsString('image', $image);
        }
    }

    public function hasImage(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasImage()
            : $this->hasString('image');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Event>
     */
    public function getRequirements(): Collection
    {
        if ($this->isEventDate()) {
            return
                $this->getTopic()->getRequirements();
        }

        /** @var Collection<\Tx_Seminars_Model_Event> $requirements */
        $requirements = $this->getAsCollection('requirements');

        return $requirements;
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Event>
     */
    public function getDependencies(): Collection
    {
        if ($this->isEventDate()) {
            return $this->getTopic()->getDependencies();
        }

        /** @var Collection<\Tx_Seminars_Model_Event> $dependencies */
        $dependencies = $this->getAsCollection('dependencies');

        return $dependencies;
    }

    /**
     * Checks whether this event has a begin date for the registration.
     */
    public function hasRegistrationBegin(): bool
    {
        return $this->hasInteger('begin_date_registration');
    }

    /**
     * @return int the begin date for the registration of this event as UNIX
     *                 time-stamp, will be 0 if no begin date for the
     *                 registration is set
     */
    public function getRegistrationBeginAsUnixTimestamp(): int
    {
        return $this->getAsInteger('begin_date_registration');
    }

    /**
     * @return string the publication hash of this event, will be empty if this
     *                event has no publication hash set
     */
    public function getPublicationHash(): string
    {
        return $this->getAsString('publication_hash');
    }

    public function hasPublicationHash(): bool
    {
        return $this->hasString('publication_hash');
    }

    /**
     * @param string $hash the publication hash, use a non-empty string to mark an event as
     *        "not published yet" and an empty string to mark an event as
     *        published
     */
    public function setPublicationHash(string $hash): void
    {
        $this->setAsString('publication_hash', $hash);
    }

    /**
     * Purges the publication hash of this event.
     */
    public function purgePublicationHash(): void
    {
        $this->setPublicationHash('');
    }

    /**
     * Checks whether this event has been published.
     *
     * Note: The publication status of an event is not related to whether it is
     * hidden or not.
     *
     * @return bool TRUE if this event has been published, FALSE otherwise
     */
    public function isPublished(): bool
    {
        return !$this->hasPublicationHash();
    }

    public function hasOfflineRegistrations(): bool
    {
        return $this->hasInteger('offline_attendees');
    }

    /**
     * @return int the number of offline registrations for this event, will
     *                 be 0 if this event has no offline registrations
     */
    public function getOfflineRegistrations(): int
    {
        return $this->getAsInteger('offline_attendees');
    }

    /**
     * Sets the number of offline registrations.
     */
    public function setOfflineRegistrations(int $numberOfRegistrations): void
    {
        $this->setAsInteger('offline_attendees', $numberOfRegistrations);
    }

    /**
     * Checks whether the organizers have already been informed that the event has enough registrations.
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendees(): bool
    {
        return $this->getAsBoolean('organizers_notified_about_minimum_reached');
    }

    /**
     * Sets that the organizers have already been informed that the event has enough registrations.
     */
    public function setOrganizersBeenNotifiedAboutEnoughAttendees(): void
    {
        $this->setAsBoolean('organizers_notified_about_minimum_reached', true);
    }

    /**
     * Checks whether notification e-mail to the organizers are muted.
     */
    public function shouldMuteNotificationEmails(): bool
    {
        return $this->getAsBoolean('mute_notification_emails');
    }

    /**
     * Makes sure that notification e-mail to the organizers are muted.
     */
    public function muteNotificationEmails(): void
    {
        $this->setAsBoolean('mute_notification_emails', true);
    }

    /**
     * Checks whether automatic confirmation/cancelation for this event is enabled.
     */
    public function shouldAutomaticallyConfirmOrCancel(): bool
    {
        return $this->getAsBoolean('automatic_confirmation_cancelation');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Registration> the registrations for this event (both regular and
     *                       on the waiting list), will be empty if this event
     *                       has no registrations
     */
    public function getRegistrations(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Registration> $registrations */
        $registrations = $this->getAsCollection('registrations');

        return $registrations;
    }

    /**
     * @param Collection<\Tx_Seminars_Model_Registration> $registrations the registrations for this event
     *        (both regular and on the waiting list), may be empty
     */
    public function setRegistrations(Collection $registrations): void
    {
        $this->set('registrations', $registrations);
    }

    public function attachRegistration(\Tx_Seminars_Model_Registration $registration): void
    {
        $registration->setEvent($this);
        $this->getRegistrations()->add($registration);
    }

    /**
     * Gets the regular registrations for this event, i.e., the registrations
     * that are not on the waiting list.
     *
     * @return Collection<\Tx_Seminars_Model_Registration> the regular registrations for this event, will be
     *                       will be empty if this event no regular
     *                       registrations
     */
    public function getRegularRegistrations(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Registration> $regularRegistrations */
        $regularRegistrations = new Collection();

        /** @var \Tx_Seminars_Model_Registration $registration */
        foreach ($this->getRegistrations() as $registration) {
            if (!$registration->isOnRegistrationQueue()) {
                $regularRegistrations->add($registration);
            }
        }

        return $regularRegistrations;
    }

    /**
     * Gets the queue registrations for this event, i.e., the registrations
     * that are no regular registrations (yet).
     *
     * @return Collection<\Tx_Seminars_Model_Registration> the queue registrations for this event, will be
     *                       will be empty if this event no queue registrations
     */
    public function getQueueRegistrations(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Registration> $queueRegistrations */
        $queueRegistrations = new Collection();

        /** @var \Tx_Seminars_Model_Registration $registration */
        foreach ($this->getRegistrations() as $registration) {
            if ($registration->isOnRegistrationQueue()) {
                $queueRegistrations->add($registration);
            }
        }

        return $queueRegistrations;
    }

    /**
     * Checks whether this event has any registrations on its registration
     * queue (i.e., on the waiting list).
     */
    public function hasQueueRegistrations(): bool
    {
        return !$this->getQueueRegistrations()->isEmpty();
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Registration>
     */
    public function getRegistrationsAfterLastDigest(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Registration> $newerRegistrations */
        $newerRegistrations = new Collection();
        $dateOfLastDigest = $this->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp();

        /** @var \Tx_Seminars_Model_Registration $registration */
        foreach ($this->getRegistrations() as $registration) {
            if ($registration->getCreationDateAsUnixTimeStamp() > $dateOfLastDigest) {
                $newerRegistrations->add($registration);
            }
        }

        return $newerRegistrations;
    }

    /**
     * Returns the number of regularly registered seats for this event.
     *
     * This functions counts the number of registered seats from regular
     * registrations (but not from queue registrations) and the number of
     * offline registrations.
     *
     * @return int the number of registered seats for this event, will be >= 0
     */
    public function getRegisteredSeats(): int
    {
        $registeredSeats = $this->getOfflineRegistrations();

        /** @var \Tx_Seminars_Model_Registration $registration */
        foreach ($this->getRegularRegistrations() as $registration) {
            $registeredSeats += $registration->getSeats();
        }

        return $registeredSeats;
    }

    /**
     * Checks whether this event has enough regular registrations to take place.
     *
     * If this event has zero as the minimum number of registrations, this
     * function will always return TRUE.
     */
    public function hasEnoughRegistrations(): bool
    {
        return $this->getRegisteredSeats() >= $this->getMinimumAttendees();
    }

    /**
     * Returns the number of vacancies for this event.
     *
     * If this event has an unlimited number of possible registrations, this
     * function will always return zero.
     *
     * @return int the number of vacancies for this event, will be >= 0
     */
    public function getVacancies(): int
    {
        return max(
            0,
            $this->getMaximumAttendees() - $this->getRegisteredSeats()
        );
    }

    /**
     * Checks whether this event has at least one vacancy.
     *
     * If this event has an unlimited number of possible registrations, this
     * function will always return TRUE.
     */
    public function hasVacancies(): bool
    {
        if ($this->hasUnlimitedVacancies()) {
            return true;
        }

        return $this->getVacancies() > 0;
    }

    /**
     * Checks whether this event is fully booked.
     *
     * If this event has an unlimited number of possible registrations, this
     * function will always return FALSE.
     */
    public function isFull(): bool
    {
        return !$this->hasVacancies();
    }

    /**
     * Returns the names of all registered attendees (including additional attendees and queue registrations).
     *
     * @return array<int, string> attendee names: ['Jane Doe', 'John Doe']
     */
    public function getAttendeeNames(): array
    {
        return $this->extractNamesFromRegistrations($this->getRegistrations());
    }

    /**
     * Returns the names of registered attendees (including additional attendees and queue registrations),
     * but only those that have registered after the last registration digest email.
     *
     * @return array<int, string> attendee names: ['Jane Doe', 'John Doe']
     */
    public function getAttendeeNamesAfterLastDigest(): array
    {
        return $this->extractNamesFromRegistrations($this->getRegistrationsAfterLastDigest());
    }

    /**
     * @param Collection<\Tx_Seminars_Model_Registration> $registrations
     *
     * @return array<int, string> attendee names: ['Jane Doe', 'John Doe']
     */
    private function extractNamesFromRegistrations(Collection $registrations): array
    {
        $names = [];

        /** @var \Tx_Seminars_Model_Registration $registration */
        foreach ($registrations as $registration) {
            if ($registration->hasRegisteredThemselves()) {
                $names[] = $registration->getFrontEndUser()->getName();
            }

            $hasAdditionalPersons = false;
            /** @var \Tx_Seminars_Model_FrontEndUser $person */
            foreach ($registration->getAdditionalPersons() as $person) {
                $names[] = $person->getName();
                $hasAdditionalPersons = true;
            }

            if (!$hasAdditionalPersons) {
                foreach (explode("\r\n", $registration->getAttendeesNames()) as $name) {
                    $trimmedName = trim($name);
                    if ($trimmedName !== '') {
                        $names[] = $trimmedName;
                    }
                }
            }
        }
        sort($names, SORT_STRING);

        return $names;
    }

    /**
     * @return int the date as UNIX time-stamp, will be 0 if this no digest has been sent yet
     */
    public function getDateOfLastRegistrationDigestEmailAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('date_of_last_registration_digest');
    }

    /**
     * @param int $date the date as UNIX time-stamp, must be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(int $date): void
    {
        if ($date < 0) {
            throw new \InvalidArgumentException('$date must be >= 0, but was: ' . $date, 1508946114880);
        }

        $this->setAsInteger('date_of_last_registration_digest', $date);
    }
}
