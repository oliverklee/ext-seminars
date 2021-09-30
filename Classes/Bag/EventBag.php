<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Bag;

use OliverKlee\Seminars\OldModel\LegacyEvent;

/**
 * This aggregate class holds a bunch of event objects and allows iterating over them.
 *
 * @extends AbstractBag<LegacyEvent>
 */
class EventBag extends AbstractBag
{
    /**
     * @var class-string<LegacyEvent>
     */
    protected static $modelClassName = LegacyEvent::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_seminars';
}
