<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;

/**
 * Trait for BE permissions.
 */
trait PermissionsTrait
{
    /**
     * @var Permissions
     */
    private $permissions;

    public function injectPermissions(Permissions $permissions): void
    {
        $this->permissions = $permissions;
    }
}
