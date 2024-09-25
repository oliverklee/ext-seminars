<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Trait for BE permissions.
 *
 * @phpstan-require-extends ActionController
 */
trait PermissionsTrait
{
    private Permissions $permissions;

    public function injectPermissions(Permissions $permissions): void
    {
        $this->permissions = $permissions;
    }
}
