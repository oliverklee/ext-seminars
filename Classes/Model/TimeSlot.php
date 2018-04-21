<?php

/**
 * This class represents a time-slot.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Model_TimeSlot extends Tx_Seminars_Model_AbstractTimeSpan
{
    /**
     * Returns our entry date as UNIX time-stamp.
     *
     * @return int our entry date as UNIX time-stamp, will be >= 0,
     *                 0 means "no entry date"
     */
    public function getEntryDateAsUnixTimeStamp()
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
    public function setEntryDateAsUnixTimeStamp($entryDate)
    {
        if ($entryDate < 0) {
            throw new InvalidArgumentException('The parameter $entryDate must be >= 0.', 1333297074);
        }

        $this->setAsInteger('entry_date', $entryDate);
    }

    /**
     * Returns whether this time-slot has an entry date.
     *
     * @return bool TRUE if this time-slot has an entry date, FALSE otherwise
     */
    public function hasEntryDate()
    {
        return $this->hasInteger('entry_date');
    }

    /**
     * Returns our place.
     *
     * @return Tx_Seminars_Model_Place|null our place, will be null if this time-slot has no place
     */
    public function getPlace()
    {
        return $this->getAsModel('place');
    }

    /**
     * Returns the seminar/event this time-slot belongs to.
     *
     * @return Tx_Seminars_Model_Event
     */
    public function getSeminar()
    {
        return $this->getAsModel('seminar');
    }

    /**
     * Sets the seminar/event this time-slot belongs to.
     *
     * @param Tx_Seminars_Model_Event $seminar
     *
     * @return void
     */
    public function setSeminar(Tx_Seminars_Model_Event $seminar)
    {
        $this->set('seminar', $seminar);
    }
}
