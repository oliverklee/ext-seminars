<?php

declare(strict_types=1);

/**
 * This class represents a time-slot.
 */
class Tx_Seminars_Model_TimeSlot extends \Tx_Seminars_Model_AbstractTimeSpan
{
    /**
     * @return int our entry date as UNIX time-stamp, will be >= 0, 0 means "no entry date"
     */
    public function getEntryDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('entry_date');
    }

    /**
     * @param int $entryDate our entry date as UNIX time-stamp, will be >= 0, 0 means "no entry date"
     */
    public function setEntryDateAsUnixTimeStamp(int $entryDate): void
    {
        if ($entryDate < 0) {
            throw new \InvalidArgumentException('The parameter $entryDate must be >= 0.', 1333297074);
        }

        $this->setAsInteger('entry_date', $entryDate);
    }

    public function hasEntryDate(): bool
    {
        return $this->hasInteger('entry_date');
    }

    public function getPlace(): ?\Tx_Seminars_Model_Place
    {
        /** @var \Tx_Seminars_Model_Place|null $model */
        $model = $this->getAsModel('place');

        return $model;
    }

    /**
     * Returns the seminar/event this time-slot belongs to.
     */
    public function getSeminar(): ?\Tx_Seminars_Model_Event
    {
        /** @var \Tx_Seminars_Model_Event|null $model */
        $model = $this->getAsModel('seminar');

        return $model;
    }

    /**
     * Sets the seminar/event this time-slot belongs to.
     */
    public function setSeminar(\Tx_Seminars_Model_Event $seminar): void
    {
        $this->set('seminar', $seminar);
    }
}
