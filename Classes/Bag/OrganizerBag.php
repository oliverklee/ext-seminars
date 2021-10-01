<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Bag;

use OliverKlee\Seminars\OldModel\LegacyOrganizer;

/**
 * This aggregate class holds a bunch of organizer objects and allows iterating over them.
 *
 * @extends AbstractBag<LegacyOrganizer>
 */
class OrganizerBag extends AbstractBag
{
    /**
     * @var class-string<LegacyOrganizer>
     */
    protected static $modelClassName = LegacyOrganizer::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_organizers';
}
