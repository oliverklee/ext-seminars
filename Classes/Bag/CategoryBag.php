<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Bag;

use OliverKlee\Seminars\OldModel\LegacyCategory;

/**
 * This aggregate class holds a bunch of category objects and allows iterating over them.
 *
 * @extends AbstractBag<LegacyCategory>
 */
class CategoryBag extends AbstractBag
{
    /**
     * @var class-string<LegacyCategory>
     */
    protected static $modelClassName = LegacyCategory::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_categories';
}
