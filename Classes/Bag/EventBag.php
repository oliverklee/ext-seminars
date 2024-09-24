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
    protected static string $modelClassName = LegacyEvent::class;

    /**
     * @var non-empty-string
     */
    protected static string $tableName = 'tx_seminars_seminars';
}
