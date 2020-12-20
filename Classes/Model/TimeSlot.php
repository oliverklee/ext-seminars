<?php

declare(strict_types=1);

/**
 * This class represents a time-slot.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_TimeSlot extends \Tx_Seminars_Model_AbstractTimeSpan
{
    /**
     * Returns our entry date as UNIX time-stamp.
     *
     * @return int our entry date as UNIX time-stamp, will be >= 0,
     *                 0 means "no entry date"
     */
    public function getEntryDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('entry_date');
    }

    /**
     * Sets our entry date as UNIX time-stamp.
     *
     * @param int $entryDate our entry date as UNIX time-stamp, will be >= 0, 0 means "no entry date"
     *
     * @return void
     */
    public function setEntryDateAsUnixTimeStamp(int $entryDate)
    {
        if ($entryDate < 0) {
            throw new \InvalidArgumentException('The parameter $entryDate must be >= 0.', 1333297074);
        }

        $this->setAsInteger('entry_date', $entryDate);
    }

    /**
     * Returns whether this time-slot has an entry date.
     *
     * @return bool TRUE if this time-slot has an entry date, FALSE otherwise
     */
    public function hasEntryDate(): bool
    {
        return $this->hasInteger('entry_date');
    }

    /**
     * Returns our place.
     *
     * @return \Tx_Seminars_Model_Place|null
     */
    public function getPlace()
    {
        /** @var \Tx_Seminars_Model_Place|null $model */
        $model = $this->getAsModel('place');

        return $model;
    }

    /**
     * Returns the seminar/event this time-slot belongs to.
     *
     * @return \Tx_Seminars_Model_Event|null
     */
    public function getSeminar()
    {
        /** @var \Tx_Seminars_Model_Event|null $model */
        $model = $this->getAsModel('seminar');

        return $model;
    }

    /**
     * Sets the seminar/event this time-slot belongs to.
     *
     * @param \Tx_Seminars_Model_Event $seminar
     *
     * @return void
     */
    public function setSeminar(\Tx_Seminars_Model_Event $seminar)
    {
        $this->set('seminar', $seminar);
    }
}
