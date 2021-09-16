<?php

declare(strict_types=1);

use OliverKlee\Seminars\Bag\AbstractBag;

/**
 * This aggregate class holds a bunch of event objects and allows iterating over them.
 *
 * @extends AbstractBag<\Tx_Seminars_OldModel_Event>
 */
class Tx_Seminars_Bag_Event extends AbstractBag
{
    /**
     * @var class-string<\Tx_Seminars_OldModel_Event>
     */
    protected static $modelClassName = \Tx_Seminars_OldModel_Event::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_seminars';
}
