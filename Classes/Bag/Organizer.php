<?php

declare(strict_types=1);

use OliverKlee\Seminars\Bag\AbstractBag;

/**
 * This aggregate class holds a bunch of organizer objects and allows iterating over them.
 *
 * @extends AbstractBag<\Tx_Seminars_OldModel_Organizer>
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Bag_Organizer extends AbstractBag
{
    /**
     * @var class-string<\Tx_Seminars_OldModel_Organizer>
     */
    protected static $modelClassName = \Tx_Seminars_OldModel_Organizer::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_organizers';
}
