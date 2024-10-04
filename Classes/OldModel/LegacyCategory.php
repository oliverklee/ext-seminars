<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

/**
 * This class represents an event category.
 */
class LegacyCategory extends AbstractModel
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static string $tableName = 'tx_seminars_categories';
}
