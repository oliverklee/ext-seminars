<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel\Fixtures;

use OliverKlee\Seminars\OldModel\LegacyTimeSlot;

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 */
final class TestingLegacyTimeSlot extends LegacyTimeSlot
{
    /**
     * Sets the place field of the time slot.
     *
     * @param int $place the UID of the place (has to be > 0)
     */
    public function setPlace(int $place): void
    {
        $this->setRecordPropertyInteger('place', $place);
    }

    /**
     * Sets the entry date.
     *
     * @param int $entryDate the entry date as a UNIX timestamp (has to be >= 0, 0 will unset the entry date)
     */
    public function setEntryDate(int $entryDate): void
    {
        $this->setRecordPropertyInteger('entry_date', $entryDate);
    }

    /**
     * Sets the begin date and time.
     *
     * @param int $beginDate the begin date as a UNIX timestamp (has to be >= 0, 0 will unset the begin date)
     */
    public function setBeginDate(int $beginDate): void
    {
        $this->setRecordPropertyInteger('begin_date', $beginDate);
    }
}
