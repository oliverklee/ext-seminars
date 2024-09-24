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
    protected static string $modelClassName = LegacyOrganizer::class;

    /**
     * @var non-empty-string
     */
    protected static string $tableName = 'tx_seminars_organizers';
}
