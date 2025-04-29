<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures;

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
}
