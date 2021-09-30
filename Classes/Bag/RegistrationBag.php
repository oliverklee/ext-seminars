<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Bag;

use OliverKlee\Seminars\OldModel\LegacyRegistration;

/**
 * This aggregate class holds a bunch of registration objects and allows iterating over them.
 *
 * @extends AbstractBag<LegacyRegistration>
 */
class RegistrationBag extends AbstractBag
{
    /**
     * @var class-string<LegacyRegistration>
     */
    protected static $modelClassName = LegacyRegistration::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_attendances';
}
