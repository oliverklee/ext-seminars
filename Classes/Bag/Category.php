<?php

declare(strict_types=1);

use OliverKlee\Seminars\Bag\AbstractBag;

/**
 * This aggregate class holds a bunch of category objects and allows iterating over them.
 *
 * @extends AbstractBag<\Tx_Seminars_OldModel_Category>
 */
class Tx_Seminars_Bag_Category extends AbstractBag
{
    /**
     * @var class-string<\Tx_Seminars_OldModel_Category>
     */
    protected static $modelClassName = \Tx_Seminars_OldModel_Category::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_categories';
}
