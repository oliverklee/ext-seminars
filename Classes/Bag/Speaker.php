<?php

declare(strict_types=1);

use OliverKlee\Seminars\Bag\AbstractBag;

/**
 * This aggregate class holds a bunch of speaker objects and allows to iterate over them.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Bag_Speaker extends AbstractBag
{
    /**
     * @var string
     */
    protected static $modelClassName = \Tx_Seminars_OldModel_Speaker::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_speakers';
}
