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
    protected static string $modelClassName = LegacyRegistration::class;

    /**
     * @var non-empty-string
     */
    protected static string $tableName = 'tx_seminars_attendances';
}
