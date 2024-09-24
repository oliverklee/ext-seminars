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
    protected static string $modelClassName = LegacyCategory::class;

    /**
     * @var non-empty-string
     */
    protected static string $tableName = 'tx_seminars_categories';
}
