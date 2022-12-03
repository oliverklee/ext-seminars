<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for the event list in the BE module.
 */
class EventController extends ActionController
{
    /**
     * @var Permissions
     */
    private $permissions;

    public function injectPermissions(Permissions $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * This method is only public for unit testing.
     *
     * @return 0|positive-int
     */
    public function getPageUid(): int
    {
        return (int)(GeneralUtility::_GP('id') ?? 0);
    }

    public function indexAction(): void
    {
        $this->view->assign('permissions', $this->permissions);
        $this->view->assign('pageUid', $this->getPageUid());
    }
}
