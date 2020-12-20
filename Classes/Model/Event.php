<?php

declare(strict_types=1);

use OliverKlee\Seminars\Model\Traits\EventEmailSenderTrait;

/**
 * This class represents an event.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Model_Event extends \Tx_Seminars_Model_AbstractTimeSpan implements \Tx_Seminars_Interface_Titled
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

    /**
     * @return \Tx_Oelib_Configuration
     */
    protected function getConfiguration(): \Tx_Oelib_Configuration
    {
        return \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars');
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
     * Returns our topic.
     *
     * This method may only be called for date records.
     *
     * @return \Tx_Seminars_Model_Event our topic
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
     * Returns our title.
     *
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
     * Sets our title.
     *
     * @param string $title our title to set, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setTitle(string $title)
    {
        if ($title === '') {
            throw new \InvalidArgumentException('$title must not be empty.', 1333293446);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Returns our subtitle.
     *
     * @return string our subtitle, will be empty if this event has no subtitle
     */
    public function getSubtitle(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getSubtitle()
            : $this->getAsString('subtitle');
    }

    /**
     * Sets our subtitle.
     *
     * @param string $subtitle our subtitle to set, may be empty
     *
     * @return void
     */
    public function setSubtitle(string $subtitle)
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setSubtitle($subtitle);
        } else {
            $this->setAsString('subtitle', $subtitle);
        }
    }

    /**
     * Returns whether this event has a subtitle.
     *
     * @return bool TRUE if this event has a subtitle, FALSE otherwise
     */
    public function hasSubtitle(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasSubtitle()
            : $this->hasString('subtitle');
    }

    /**
     * Returns our categories.
     *
     * @return \Tx_Oelib_List our categories
     */
    public function getCategories(): \Tx_Oelib_List
    {
        return $this->isEventDate()
            ? $this->getTopic()->getCategories()
            : $this->getAsList('categories');
    }

    /**
     * Returns our teaser.
     *
     * @return string our teaser, might be empty
     */
    public function getTeaser(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getTeaser()
            : $this->getAsString('teaser');
    }

    /**
     * Sets our teaser.
     *
     * @param string $teaser our teaser, may be empty
     *
     * @return void
     */
    public function setTeaser(string $teaser)
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setTeaser($teaser);
        } else {
            $this->setAsString('teaser', $teaser);
        }
    }

    /**
     * Returns whether this event has a teaser.
     *
     * @return bool TRUE if this event has a teaser, FALSE otherwise
     */
    public function hasTeaser(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasTeaser()
            : $this->hasString('teaser');
    }

    /**
     * Returns our description.
     *
     * @return string our description, might be empty
     */
    public function getDescription(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getDescription()
            : $this->getAsString('description');
    }

    /**
     * Sets our description.
     *
     * @param string $description our description, may be empty
     *
     * @return void
     */
    public function setDescription(string $description)
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setDescription($description);
        } else {
            $this->setAsString('description', $description);
        }
    }

    /**
     * Returns whether this event has a description.
     *
     * @return bool
     */
    public function hasDescription(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasDescription()
            : $this->hasString('description');
    }

    /**
     * Returns our event type.
     *
     * @return \Tx_Seminars_Model_EventType|null our event type, will be null if this event has no event type
     */
    public function getEventType()
    {
        /** @var Tx_Seminars_Model_EventType|null $type */
        $type = $this->isEventDate() ? $this->getTopic()->getEventType() : $this->getAsModel('event_type');

        return $type;
    }

    /**
     * @return string
     */
    public function getTimeZone(): string
    {
        return $this->getAsString('time_zone');
    }

    /**
     * @param string $timeZone
     *
     * @return void
     */
    public function setTimeZone(string $timeZone)
    {
        $this->setAsString('time_zone', $timeZone);
    }

    /**
     * Returns our accreditation number.
     *
     * @return string our accreditation number, will be empty if this event has
     *                no accreditation number
     */
    public function getAccreditationNumber(): string
    {
        return $this->getAsString('accreditation_number');
    }

    /**
     * Sets our accreditation number.
     *
     * @param string $accreditationNumber our accreditation number, may be empty
     *
     * @return void
     */
    public function setAccreditationNumber(string $accreditationNumber)
    {
        $this->setAsString('accreditation_number', $accreditationNumber);
    }

    /**
     * Returns whether this event has an accreditation number.
     *
     * @return bool TRUE if this event has an accreditation number, FALSE
     *                 otherwise
     */
    public function hasAccreditationNumber(): bool
    {
        return $this->hasString('accreditation_number');
    }

    /**
     * Returns our credit points.
     *
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
     * Sets our credit points.
     *
     * @param int $creditPoints our credit points, must be >= 0
     *
     * @return void
     */
    public function setCreditPoints(int $creditPoints)
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

    /**
     * Returns whether this event has credit points.
     *
     * @return bool TRUE if this event has credit points, FALSE otherwise
     */
    public function hasCreditPoints(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasCreditPoints()
            : $this->hasInteger('credit_points');
    }

    /**
     * Returns our time-slots.
     *
     * @return \Tx_Oelib_List our time-slots, will be empty if this event has no
     *                       time-slots
     */
    public function getTimeSlots(): \Tx_Oelib_List
    {
        return $this->getAsList('timeslots');
    }

    /**
     * Returns our registration deadline as UNIX time-stamp.
     *
     * @return int our registration deadline as UNIX time-stamp, will be
     *                 0 if this event has no registration deadline, will be
     *                 >= 0
     */
    public function getRegistrationDeadlineAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('deadline_registration');
    }

    /**
     * Sets our registration deadline as UNIX time-stamp.
     *
     * @param int $registrationDeadline
     *        our registration deadline as UNIX time-stamp, must be >= 0, 0 unsets the registration deadline
     *
     * @return void
     */
    public function setRegistrationDeadlineAsUnixTimeStamp(int $registrationDeadline)
    {
        if ($registrationDeadline < 0) {
            throw new \InvalidArgumentException('The parameter $registrationDeadline must be >= 0.', 1333296347);
        }

        $this->setAsInteger('deadline_registration', $registrationDeadline);
    }

    /**
     * Returns whether this event has a registration deadline set.
     *
     * @return bool TRUE if this event has a registration deadline set, FALSE
     *                 otherwise
     */
    public function hasRegistrationDeadline(): bool
    {
        return $this->hasInteger('deadline_registration');
    }

    /**
     * Returns the latest date/time to register for a seminar.
     * This is either the registration deadline (if set) or the begin date of an
     * event.
     *
     * @return int the latest possible moment to register for this event
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
     * Sets our early bird deadline as UNIX time-stamp.
     *
     * @param int $earlyBirdDeadline
     *        our early bird deadline as UNIX time-stamp, must be >= 0, 0 unsets the early bird deadline
     *
     * @return void
     */
    public function setEarlyBirdDeadlineAsUnixTimeStamp(int $earlyBirdDeadline)
    {
        if ($earlyBirdDeadline < 0) {
            throw new \InvalidArgumentException('The parameter $earlyBirdDeadline must be >= 0.', 1333296359);
        }

        $this->setAsInteger('deadline_early_bird', $earlyBirdDeadline);
    }

    /**
     * Returns whether this event has an early bird deadline.
     *
     * @return bool TRUE if this event has an early bird deadline, FALSE
     *                 otherwise
     */
    public function hasEarlyBirdDeadline(): bool
    {
        return $this->hasInteger('deadline_early_bird');
    }

    /**
     * Returns our unregistration deadline as UNIX time-stamp.
     *
     * @return int our unregistration deadline as UNIX time-stamp, will be
     *                 0 if this event has no unregistration deadline, will be
     *                 >= 0
     */
    public function getUnregistrationDeadlineAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('deadline_unregistration');
    }

    /**
     * Sets our unregistration deadline as UNIX time-stamp.
     *
     * @param int $unregistrationDeadline
     *        our unregistration deadline as UNIX time-stamp, must be >= 0, 0 unsets the unregistration deadline
     *
     * @return void
     */
    public function setUnregistrationDeadlineAsUnixTimeStamp(int $unregistrationDeadline)
    {
        if ($unregistrationDeadline < 0) {
            throw new \InvalidArgumentException('The parameter $unregistrationDeadline must be >= 0.', 1333296369);
        }

        $this->setAsInteger('deadline_unregistration', $unregistrationDeadline);
    }

    /**
     * Returns whether this event has an unregistration deadline.
     *
     * @return bool
     */
    public function hasUnregistrationDeadline(): bool
    {
        return $this->hasInteger('deadline_unregistration');
    }

    /**
     * Returns our expiry as UNIX time-stamp.
     *
     * @return int our expiry as UNIX time-stamp, will be 0 if this event
     *                 has no expiry, will be >= 0
     */
    public function getExpiryAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('expiry');
    }

    /**
     * Sets our expiry as UNIX time-stamp.
     *
     * @param int $expiry our expiry as UNIX time-stamp, must be >= 0, 0 unsets the expiry
     *
     * @return void
     */
    public function setExpiryAsUnixTimeStamp(int $expiry)
    {
        if ($expiry < 0) {
            throw new \InvalidArgumentException('The parameter $expiry must be >= 0.', 1333296380);
        }

        $this->setAsInteger('expiry', $expiry);
    }

    /**
     * Returns whether this event has an expiry.
     *
     * @return bool TRUE if this event has an expiry, FALSE otherwise
     */
    public function hasExpiry(): bool
    {
        return $this->hasInteger('expiry');
    }

    /**
     * Returns our details page.
     *
     * @return string our separate details page, will be empty if this event has no separate details page
     */
    public function getDetailsPage(): string
    {
        return $this->getAsString('details_page');
    }

    /**
     * Sets our separate details page.
     *
     * @param string $detailsPage our separate details page
     *
     * @return void
     */
    public function setDetailsPage(string $detailsPage)
    {
        $this->setAsString('details_page', $detailsPage);
    }

    /**
     * Returns whether this event has a separate details page.
     *
     * @return bool TRUE if this event has a separate details page, FALSE
     *                 otherwise
     */
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
     * @return string
     *         the single view page UID/URL, will be an empty string if none
     *         has been set
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

    /**
     * Checks whether this event has a separate single view page (combined
     * from the event itself, the event type and the categories).
     *
     * @return bool
     *         TRUE if this event has a single view page set, FALSE otherwise
     */
    public function hasCombinedSingleViewPage(): bool
    {
        return $this->getCombinedSingleViewPage() != '';
    }

    /**
     * Gets the single view page from the event type.
     *
     * @return int
     *         the single view page UID from the event type, will be > 0 if
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

    /**
     * Checks whether this event has an event type with a non-zero single view
     * page UID.
     *
     * @return bool
     *         TRUE if this event has an event type and if that event type has
     *         a non-zero single view page, FALSE otherwise
     */
    protected function hasSingleViewPageUidFromEventType(): bool
    {
        return ($this->getEventType() !== null)
            && $this->getEventType()->hasSingleViewPageUid();
    }

    /**
     * Gets the single view page UID from the categories.
     *
     * This function returns the first found UID from the event categories.
     *
     * @return int
     *         the single view page UID from the categories, will be > 0 if
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

    /**
     * Checks whether this event has at least one category with a single view
     * page UID.
     *
     * @return bool
     *         TRUE if this event has at least one category with a single view
     *         page UID, FALSE otherwise
     */
    protected function hasSingleViewPageUidFromCategories(): bool
    {
        return $this->getSingleViewPageUidFromCategories() > 0;
    }

    /**
     * Returns our places.
     *
     * @return \Tx_Oelib_List our places, will be empty if this event has no
     *                       places
     */
    public function getPlaces(): \Tx_Oelib_List
    {
        return $this->getAsList('place');
    }

    /**
     * Returns our lodgings.
     *
     * @return \Tx_Oelib_List our lodgings, will be empty if this event has no
     *                       lodgings
     */
    public function getLodgings(): \Tx_Oelib_List
    {
        return $this->getAsList('lodgings');
    }

    /**
     * Returns our foods.
     *
     * @return \Tx_Oelib_List our foods, will be empty if this event has no
     *                       foods
     */
    public function getFoods(): \Tx_Oelib_List
    {
        return $this->getAsList('foods');
    }

    /**
     * Returns our partners.
     *
     * @return \Tx_Oelib_List our partners, will be empty if this event has no
     *                       partners
     */
    public function getPartners(): \Tx_Oelib_List
    {
        return $this->getAsList('partners');
    }

    /**
     * Returns our tutors.
     *
     * @return \Tx_Oelib_List our tutors, will be empty if this event has no
     *                       tutors
     */
    public function getTutors(): \Tx_Oelib_List
    {
        return $this->getAsList('tutors');
    }

    /**
     * Returns our leaders.
     *
     * @return \Tx_Oelib_List our leaders, will be empty if this event has no
     *                       leaders
     */
    public function getLeaders(): \Tx_Oelib_List
    {
        return $this->getAsList('leaders');
    }

    /**
     * Returns our language.
     *
     * @return \Tx_Oelib_Model_Language|null
     */
    public function getLanguage()
    {
        if (!$this->hasLanguage()) {
            return null;
        }

        /** @var \Tx_Oelib_Mapper_Language $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Language::class);
        return $mapper->findByIsoAlpha2Code($this->getAsString('language'));
    }

    /**
     * Sets our language.
     *
     * @param \Tx_Oelib_Model_Language $language our language
     *
     * @return void
     */
    public function setLanguage(\Tx_Oelib_Model_Language $language)
    {
        $this->setAsString('language', $language->getIsoAlpha2Code());
    }

    /**
     * Returns whether this event has a language.
     *
     * @return bool TRUE if this event has a language, FALSE otherwise
     */
    public function hasLanguage(): bool
    {
        return $this->hasString('language');
    }

    /**
     * @return bool
     */
    public function getPriceOnRequest(): bool
    {
        return $this->isEventDate() ? $this->getTopic()->getPriceOnRequest() : $this->getAsBoolean('price_on_request');
    }

    /**
     * Returns our regular price.
     *
     * @return float our regular price, will be 0.00 if this event has no regular
     *               price, will be >= 0.00
     */
    public function getRegularPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getRegularPrice()
            : $this->getAsFloat('price_regular');
    }

    /**
     * Sets our regular price.
     *
     * @param float $price our regular price, must be >= 0.00
     *
     * @return void
     */
    public function setRegularPrice(float $price)
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

    /**
     * Returns whether this event has a regular price.
     *
     * @return bool TRUE if this event has a regular price, FALSE otherwise
     */
    public function hasRegularPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasRegularPrice()
            : $this->hasFloat('price_regular');
    }

    /**
     * Returns our regular early bird price.
     *
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
     * Sets our regular early bird price.
     *
     * @param float $price our regular early bird price, must be >= 0.00
     *
     * @return void
     */
    public function setRegularEarlyBirdPrice(float $price)
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

    /**
     * Returns whether this event has a regular early bird price.
     *
     * @return bool TRUE if this event has a regular early bird price, FALSE
     *                 otherwise
     */
    public function hasRegularEarlyBirdPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasRegularEarlyBirdPrice()
            : $this->hasFloat('price_regular_early');
    }

    /**
     * Returns our regular board price.
     *
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
     * Sets our regular board price.
     *
     * @param float $price our regular board price, must be >= 0.00
     *
     * @return void
     */
    public function setRegularBoardPrice(float $price)
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

    /**
     * Returns whether this event has a regular board price.
     *
     * @return bool
     */
    public function hasRegularBoardPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasRegularBoardPrice()
            : $this->hasFloat('price_regular_board');
    }

    /**
     * Returns our special price.
     *
     * @return float our special price, will be 0.00 if this event has no special price, will be >= 0.00
     */
    public function getSpecialPrice(): float
    {
        return $this->isEventDate()
            ? $this->getTopic()->getSpecialPrice()
            : $this->getAsFloat('price_special');
    }

    /**
     * Sets our special price.
     *
     * @param float $price our special price, must be >= 0.00
     *
     * @return void
     */
    public function setSpecialPrice(float $price)
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

    /**
     * Returns whether this event has a special price.
     *
     * @return bool TRUE if this event has a special price, FALSE otherwise
     */
    public function hasSpecialPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasSpecialPrice()
            : $this->hasFloat('price_special');
    }

    /**
     * Returns our special early bird price.
     *
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
     * Sets our special early bird price.
     *
     * @param float $price our special early bird price, must be >= 0.00
     *
     * @return void
     */
    public function setSpecialEarlyBirdPrice(float $price)
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

    /**
     * Return whether this event has a special early bird price.
     *
     * @return bool TRUE if this event has a special early bird price, FALSE
     *                 otherwise
     */
    public function hasSpecialEarlyBirdPrice(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasSpecialEarlyBirdPrice()
            : $this->hasFloat('price_special_early');
    }

    /**
     * Returns our special board price.
     *
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
     * Sets our special board price.
     *
     * @param float $price our special board price, must be >= 0.00
     *
     * @return void
     */
    public function setSpecialBoardPrice(float $price)
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

    /**
     * Returns whether this event has a special board price.
     *
     * @return bool TRUE if this event has a special board price, FALSE
     *                 otherwise
     */
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
     * Returns whether an early bird price applies.
     *
     * @return bool TRUE if this event has an early bird dealine set and
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
     * @return float[] the available prices as an associative array, will not be empty
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
     * Returns our additional information.
     *
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
     * Sets our additional information.
     *
     * @param string $additionalInformation our additional information, may be empty
     *
     * @return void
     */
    public function setAdditionalInformation(string $additionalInformation)
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setAdditionalInformation($additionalInformation);
        } else {
            $this->setAsString('additional_information', $additionalInformation);
        }
    }

    /**
     * Returns whether this event has additional information.
     *
     * @return bool TRUE if this event has additional information, FALSE
     *                 otherwise
     */
    public function hasAdditionalInformation(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasAdditionalInformation()
            : $this->hasString('additional_information');
    }

    /**
     * Returns our payment methods.
     *
     * @return \Tx_Oelib_List our payment methods, will be empty if this event
     *                       has no payment methods
     */
    public function getPaymentMethods(): \Tx_Oelib_List
    {
        return $this->isEventDate()
            ? $this->getTopic()->getPaymentMethods()
            : $this->getAsList('payment_methods');
    }

    /**
     * Sets our payment methods.
     *
     * Note: This function should only be called on topic or single event
     * records, not on event dates.
     *
     * @param \Tx_Oelib_List $paymentMethods
     *        our payment methods, can be empty
     *
     * @return void
     */
    public function setPaymentMethods(\Tx_Oelib_List $paymentMethods)
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
     * Returns our organizers.
     *
     * @return \Tx_Oelib_List our organizers, will be empty if this event has no
     *                       organizers
     */
    public function getOrganizers(): \Tx_Oelib_List
    {
        return $this->getAsList('organizers');
    }

    /**
     * Retrieves the first organizer.
     *
     * @return \Tx_Seminars_Model_Organizer|null
     */
    public function getFirstOrganizer()
    {
        /** @var \Tx_Seminars_Model_Organizer|null $organizer */
        $organizer = $this->getOrganizers()->first();

        return $organizer;
    }

    /**
     * Returns our organinzing partners.
     *
     * @return \Tx_Oelib_List our organizing partners, will be empty if this event
     *                       has no organizing partners
     */
    public function getOrganizingPartners(): \Tx_Oelib_List
    {
        return $this->getAsList('organizing_partners');
    }

    /**
     * Returns whether the "event takes place reminder" has been sent.
     *
     * @return bool TRUE if the "event takes place reminder" has been sent,
     *                 FALSE otherwise
     */
    public function eventTakesPlaceReminderHasBeenSent(): bool
    {
        return $this->getAsBoolean('event_takes_place_reminder_sent');
    }

    /**
     * Returns whether the "cancelation deadline reminder" has been sent.
     *
     * @return bool TRUE if the "cancelation deadline reminder" has been sent,
     *                 FALSE otherwise
     */
    public function cancelationDeadlineReminderHasBeenSent(): bool
    {
        return $this->getAsBoolean('cancelation_deadline_reminder_sent');
    }

    /**
     * Returns whether this event needs a registration.
     *
     * @return bool TRUE if this event needs a registration, FALSE otherwise
     */
    public function needsRegistration(): bool
    {
        return $this->getAsBoolean('needs_registration');
    }

    /**
     * Returns whether this event allows multiple registration.
     *
     * @return bool TRUE if this event allows multiple registration, FALSE
     *                 otherwise
     */
    public function allowsMultipleRegistrations(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->allowsMultipleRegistrations()
            : $this->getAsBoolean('allows_multiple_registrations');
    }

    /**
     * Returns our minimum attendees.
     *
     * @return int our minimum attendees, will be 0 if this event has no
     *                 minimum attendees, will be >= 0
     */
    public function getMinimumAttendees(): int
    {
        return $this->getAsInteger('attendees_min');
    }

    /**
     * Sets our minimum attendees.
     *
     * @param int $minimumAttendees our minimum attendees, must be >= 0
     *
     * @return void
     */
    public function setMinimumAttendees(int $minimumAttendees)
    {
        if ($minimumAttendees < 0) {
            throw new \InvalidArgumentException('The parameter $minimumAttendees must be >= 0.', 1333296697);
        }

        $this->setAsInteger('attendees_min', $minimumAttendees);
    }

    /**
     * Returns whether this event has minimum attendees.
     *
     * @return bool TRUE if this event has minimum attendees, FALSE otherwise
     */
    public function hasMinimumAttendees(): bool
    {
        return $this->hasInteger('attendees_min');
    }

    /**
     * Returns our maximum attendees.
     *
     * @return int our maximum attendees, will be 0 if this event has no
     *                 maximum attendees and allows unlimited number of attendees,
     *                 will be >= 0
     */
    public function getMaximumAttendees(): int
    {
        return $this->getAsInteger('attendees_max');
    }

    /**
     * Sets our maximum attendees.
     *
     * @param int $maximumAttendees our maximum attendees, must be >= 0, 0 means an unlimited number of attendees
     *
     * @return void
     */
    public function setMaximumAttendees(int $maximumAttendees)
    {
        if ($maximumAttendees < 0) {
            throw new \InvalidArgumentException('The parameter $maximumAttendees must be >= 0.', 1333296708);
        }

        $this->setAsInteger('attendees_max', $maximumAttendees);
    }

    /**
     * Returns whether this event has maximum attendees.
     *
     * @return bool TRUE if this event has maximum attendees, FALSE otherwise
     *                 (allowing an unlimited number of attendees)
     */
    public function hasMaximumAttendees(): bool
    {
        return $this->hasInteger('attendees_max');
    }

    /**
     * Checks whether this event has unlimited vacancies.
     *
     * @return bool TRUE if this event has unlimited vacancies, FALSE
     *                 otherwise
     */
    public function hasUnlimitedVacancies(): bool
    {
        return !$this->hasMaximumAttendees();
    }

    /**
     * Returns whether this event has a registration queue.
     *
     * @return bool TRUE if this event has a registration queue, FALSE
     *                 otherwise
     */
    public function hasRegistrationQueue(): bool
    {
        return $this->getAsBoolean('queue_size');
    }

    /**
     * Returns our target groups.
     *
     * @return \Tx_Oelib_List our target groups, will be empty if this event has
     *                       no target groups
     */
    public function getTargetGroups(): \Tx_Oelib_List
    {
        return $this->isEventDate()
            ? $this->getTopic()->getTargetGroups()
            : $this->getAsList('target_groups');
    }

    /**
     * Returns whether the collision check should be skipped for this event.
     *
     * @return bool TRUE if the collision check should be skipped for this
     *                 event, FALSE otherwise
     */
    public function shouldSkipCollisionCheck(): bool
    {
        return $this->getAsBoolean('skip_collision_check');
    }

    /**
     * Returns our status.
     *
     * @return int our status, will be one of STATUS_PLANNED,
     *                 STATUS_CANCELED or STATUS_CONFIRMED
     */
    public function getStatus(): int
    {
        return $this->getAsInteger('cancelled');
    }

    /**
     * Sets our status.
     *
     * @param int $status our status, must be one of STATUS_PLANNED, STATUS_CANCELED, STATUS_CONFIRMED
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus(int $status)
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

    /**
     * Checks whether this event has been canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->getStatus() === self::STATUS_CANCELED;
    }

    /**
     * Checks whether this event has been confirmed.
     *
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->getStatus() === self::STATUS_CONFIRMED;
    }

    /**
     * Marks this event as canceled.
     *
     * If this event already is canceled, this method is a no-op.
     *
     * @return void
     */
    public function cancel()
    {
        $this->setStatus(self::STATUS_CANCELED);
    }

    /**
     * Marks this event as confirmed.
     *
     * If this event already is confirmed, this method is a no-op.
     *
     * @return void
     */
    public function confirm()
    {
        $this->setStatus(self::STATUS_CONFIRMED);
    }

    /**
     * Returns our owner.
     *
     * @return \Tx_Oelib_Model_FrontEndUser|null
     */
    public function getOwner()
    {
        /** @var \Tx_Oelib_Model_FrontEndUser|null $owner */
        $owner = $this->getAsModel('owner_feuser');

        return $owner;
    }

    /**
     * Returns our event managers.
     *
     * @return \Tx_Oelib_List our event managers, will be empty if this event has
     *                       no event managers
     */
    public function getEventManagers(): \Tx_Oelib_List
    {
        return $this->getAsList('vips');
    }

    /**
     * Returns our checkboxes.
     *
     * @return \Tx_Oelib_List our checkboxes, will be empty if this event has no
     *                       checkboxes
     */
    public function getCheckboxes(): \Tx_Oelib_List
    {
        return $this->isEventDate()
            ? $this->getTopic()->getCheckboxes()
            : $this->getAsList('checkboxes');
    }

    /**
     * Returns whether this event makes use of the second terms & conditions.
     *
     * @return bool TRUE if this event makes use of the second terms &
     *                 conditions, FALSE otherwise
     */
    public function usesTerms2(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->usesTerms2()
            : $this->getAsBoolean('use_terms_2');
    }

    /**
     * Returns our notes.
     *
     * @return string our notes, will be empty if this event has no notes
     */
    public function getNotes(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getNotes()
            : $this->getAsString('notes');
    }

    /**
     * Sets our notes.
     *
     * @param string $notes our notes, may be empty
     *
     * @return void
     */
    public function setNotes(string $notes)
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setNotes($notes);
        } else {
            $this->setAsString('notes', $notes);
        }
    }

    /**
     * Returns whether this event has notes.
     *
     * @return bool TRUE if this event has notes, FALSE otherwise
     */
    public function hasNotes(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasNotes()
            : $this->hasString('notes');
    }

    /**
     * Returns our attached files.
     *
     * The returned array will be sorted like the files are sorted in the back-
     * end form.
     *
     * @return string[] our attached file names relative to the seminars upload
     *               directory, will be empty if this event has no attached files
     */
    public function getAttachedFiles(): array
    {
        return $this->getAsTrimmedArray('attached_files');
    }

    /**
     * Sets our attached files.
     *
     * @param string[] $attachedFiles
     *        our attached file names, file names must be relative to the seminars upload directory, may be empty
     *
     * @return void
     */
    public function setAttachedFiles(array $attachedFiles)
    {
        $this->setAsArray('attached_files', $attachedFiles);
    }

    /**
     * Returns whether this event has attached files.
     *
     * @return bool TRUE if this event has attached files, FALSE otherwise
     */
    public function hasAttachedFiles(): bool
    {
        return $this->hasString('attached_files');
    }

    /**
     * Returns our image.
     *
     * @return string our image file name relative to the seminars upload
     *                directory, will be empty if this event has no image
     */
    public function getImage(): string
    {
        return $this->isEventDate()
            ? $this->getTopic()->getImage()
            : $this->getAsString('image');
    }

    /**
     * Sets our image.
     *
     * @param string $image our image file name, must be relative to the seminars upload directory, may be empty
     *
     * @return void
     */
    public function setImage(string $image)
    {
        if ($this->isEventDate()) {
            $this->getTopic()->setImage($image);
        } else {
            $this->setAsString('image', $image);
        }
    }

    /**
     * Returns whether this event has an image.
     *
     * @return bool TRUE if this event has an image, FALSE otherwise
     */
    public function hasImage(): bool
    {
        return $this->isEventDate()
            ? $this->getTopic()->hasImage()
            : $this->hasString('image');
    }

    /**
     * Returns our requirements.
     *
     * @return \Tx_Oelib_List our requirements, will be empty if this event has
     *                       no requirements
     */
    public function getRequirements(): \Tx_Oelib_List
    {
        return $this->isEventDate()
            ? $this->getTopic()->getRequirements()
            : $this->getAsList('requirements');
    }

    /**
     * Returns our dependencies.
     *
     * @return \Tx_Oelib_List our dependencies, will be empty if this event has
     *                       no dependencies
     */
    public function getDependencies(): \Tx_Oelib_List
    {
        return $this->isEventDate()
            ? $this->getTopic()->getDependencies()
            : $this->getAsList('dependencies');
    }

    /**
     * Checks whether this event has a begin date for the registration.
     *
     * @return bool TRUE if this event has a begin date for the registration,
     *                 FALSE otherwise
     */
    public function hasRegistrationBegin(): bool
    {
        return $this->hasInteger('begin_date_registration');
    }

    /**
     * Returns the begin date for the registration of this event as UNIX
     * time-stamp.
     *
     * @return int the begin date for the registration of this event as UNIX
     *                 time-stamp, will be 0 if no begin date for the
     *                 registration is set
     */
    public function getRegistrationBeginAsUnixTimestamp(): int
    {
        return $this->getAsInteger('begin_date_registration');
    }

    /**
     * Returns the publication hash of this event.
     *
     * @return string the publication hash of this event, will be empty if this
     *                event has no publication hash set
     */
    public function getPublicationHash(): string
    {
        return $this->getAsString('publication_hash');
    }

    /**
     * Checks whether this event has a publication hash.
     *
     * @return bool TRUE if this event has a publication hash, FALSE
     *                 otherwise
     */
    public function hasPublicationHash(): bool
    {
        return $this->hasString('publication_hash');
    }

    /**
     * Sets this event's publication hash.
     *
     * @param string $hash
     *        the publication hash, use a non-empty string to mark an event as
     *        "not published yet" and an empty string to mark an event as
     *        published
     *
     * @return void
     */
    public function setPublicationHash(string $hash)
    {
        $this->setAsString('publication_hash', $hash);
    }

    /**
     * Purges the publication hash of this event.
     *
     * @return void
     */
    public function purgePublicationHash()
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

    /**
     * Checks whether this event has any offline registrations.
     *
     * @return bool TRUE if this event has at least one offline registration,
     *                 FALSE otherwise
     */
    public function hasOfflineRegistrations(): bool
    {
        return $this->hasInteger('offline_attendees');
    }

    /**
     * Returns the number of offline registrations for this event.
     *
     * @return int the number of offline registrations for this event, will
     *                 be 0 if this event has no offline registrations
     */
    public function getOfflineRegistrations(): int
    {
        return $this->getAsInteger('offline_attendees');
    }

    /**
     * Sets the number of offline registrations.
     *
     * @param int $numberOfRegistrations
     *
     * @return void
     */
    public function setOfflineRegistrations(int $numberOfRegistrations)
    {
        $this->setAsInteger('offline_attendees', $numberOfRegistrations);
    }

    /**
     * Checks whether the organizers have already been informed that the event has enough registrations.
     *
     * @return bool
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendees(): bool
    {
        return $this->getAsBoolean('organizers_notified_about_minimum_reached');
    }

    /**
     * Sets that the organizers have already been informed that the event has enough registrations.
     *
     * @return void
     */
    public function setOrganizersBeenNotifiedAboutEnoughAttendees()
    {
        $this->setAsBoolean('organizers_notified_about_minimum_reached', true);
    }

    /**
     * Checks whether notification e-mail to the organizers are muted.
     *
     * @return bool
     */
    public function shouldMuteNotificationEmails(): bool
    {
        return $this->getAsBoolean('mute_notification_emails');
    }

    /**
     * Makes sure that notification e-mail to the organizers are muted.
     *
     * @return void
     */
    public function muteNotificationEmails()
    {
        $this->setAsBoolean('mute_notification_emails', true);
    }

    /**
     * Checks whether automatic confirmation/cancelation for this event is enabled.
     *
     * @return bool
     */
    public function shouldAutomaticallyConfirmOrCancel(): bool
    {
        return $this->getAsBoolean('automatic_confirmation_cancelation');
    }

    /**
     * Gets the registrations for this event.
     *
     * @return \Tx_Oelib_List the registrations for this event (both regular and
     *                       on the waiting list), will be empty if this event
     *                       has no registrations
     */
    public function getRegistrations(): \Tx_Oelib_List
    {
        return $this->getAsList('registrations');
    }

    /**
     * Sets the registrations for this event.
     *
     * @param \Tx_Oelib_List $registrations
     *       the registrations for this event (both regular and on the waiting
     *       list), may be empty
     *
     * @return void
     */
    public function setRegistrations(\Tx_Oelib_List $registrations)
    {
        $this->set('registrations', $registrations);
    }

    /**
     * Attaches a registration to this event.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to attach
     *
     * @return void
     */
    public function attachRegistration(
        \Tx_Seminars_Model_Registration $registration
    ) {
        $registration->setEvent($this);
        $this->getRegistrations()->add($registration);
    }

    /**
     * Gets the regular registrations for this event, ie. the registrations
     * that are not on the waiting list.
     *
     * @return \Tx_Oelib_List the regular registrations for this event, will be
     *                       will be empty if this event no regular
     *                       registrations
     */
    public function getRegularRegistrations(): \Tx_Oelib_List
    {
        $regularRegistrations = new \Tx_Oelib_List();

        /** @var \Tx_Seminars_Model_Registration $registration */
        foreach ($this->getRegistrations() as $registration) {
            if (!$registration->isOnRegistrationQueue()) {
                $regularRegistrations->add($registration);
            }
        }

        return $regularRegistrations;
    }

    /**
     * Gets the queue registrations for this event, ie. the registrations
     * that are no regular registrations (yet).
     *
     * @return \Tx_Oelib_List the queue registrations for this event, will be
     *                       will be empty if this event no queue registrations
     */
    public function getQueueRegistrations(): \Tx_Oelib_List
    {
        $queueRegistrations = new \Tx_Oelib_List();

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
     * queue (ie. on the waiting list).
     *
     * @return bool TRUE if there is at least one registration on the queue,
     *                 FALSE otherwise
     */
    public function hasQueueRegistrations(): bool
    {
        return !$this->getQueueRegistrations()->isEmpty();
    }

    /**
     * @return \Tx_Oelib_List \Tx_Oelib_List<\Tx_Seminars_Model_Registration>
     */
    public function getRegistrationsAfterLastDigest(): \Tx_Oelib_List
    {
        $newerRegistrations = new \Tx_Oelib_List();
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
     *
     * @return bool TRUE if this event has enough regular registrations to
     *                 to take place, FALSE otherwise
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
     *
     * @return bool TRUE if this event has at least one vacancy
     *                 FALSE otherwise
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
     *
     * @return bool TRUE if this event is fully booked, FALSE otherwise
     */
    public function isFull(): bool
    {
        return !$this->hasVacancies();
    }

    /**
     * Returns the names of all registered attendees (including additional attendees and queue registrations).
     *
     * @return string[] attendee names: ['Jane Doe', 'John Doe']
     */
    public function getAttendeeNames(): array
    {
        return $this->extractNamesFromRegistrations($this->getRegistrations());
    }

    /**
     * Returns the names of registered attendees (including additional attendees and queue registrations),
     * but only those that have registered after the last registration digest email.
     *
     * @return string[] attendee names: ['Jane Doe', 'John Doe']
     */
    public function getAttendeeNamesAfterLastDigest(): array
    {
        return $this->extractNamesFromRegistrations($this->getRegistrationsAfterLastDigest());
    }

    /**
     * @param \Tx_Oelib_List $registrations \Tx_Oelib_List<\Tx_Seminars_Model_Registration>
     *
     * @return string[] attendee names: ['Jane Doe', 'John Doe']
     */
    private function extractNamesFromRegistrations(\Tx_Oelib_List $registrations): array
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
                foreach (explode(CRLF, $registration->getAttendeesNames()) as $name) {
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
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(int $date)
    {
        if ($date < 0) {
            throw new \InvalidArgumentException('$date must be >= 0, but was: ' . $date, 1508946114880);
        }

        $this->setAsInteger('date_of_last_registration_digest', $date);
    }
}
