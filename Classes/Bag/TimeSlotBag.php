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
    protected static $modelClassName = LegacyTimeSlot::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_timeslots';
}
