<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Bag;

use OliverKlee\Seminars\OldModel\LegacyTimeSlot;

/**
 * This aggregate class holds a bunch of TimeSlot objects and allows iterating over them.
 *
 * @extends AbstractBag<LegacyTimeSlot>
 */
class TimeSlotBag extends AbstractBag
{
    /**
     * @var class-string<LegacyTimeSlot>
     */
    protected static string $modelClassName = LegacyTimeSlot::class;

    /**
     * @var non-empty-string
     */
    protected static string $tableName = 'tx_seminars_timeslots';
}
