<?php

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingTimeSlot extends \Tx_Seminars_OldModel_TimeSlot
{
    /**
     * Sets the place field of the time slot.
     *
     * @param int $place the UID of the place (has to be > 0)
     *
     * @return void
     */
    public function setPlace($place)
    {
        $this->setRecordPropertyInteger('place', $place);
    }

    /**
     * Sets the entry date.
     *
     * @param int $entryDate the entry date as a UNIX timestamp (has to be >= 0, 0 will unset the entry date)
     *
     * @return void
     */
    public function setEntryDate($entryDate)
    {
        $this->setRecordPropertyInteger('entry_date', $entryDate);
    }

    /**
     * Sets the begin date and time.
     *
     * @param int $beginDate the begin date as a UNIX timestamp (has to be >= 0, 0 will unset the begin date)
     *
     * @return void
     */
    public function setBeginDate($beginDate)
    {
        $this->setRecordPropertyInteger('begin_date', $beginDate);
    }
}
