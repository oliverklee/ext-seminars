<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Base class for BE module controllers to avoid code duplication.
 */
abstract class AbstractController extends ActionController
{
    /**
     * @var Permissions
     */
    protected $permissions;

    /**
     * @var EventStatisticsCalculator
     */
    protected $eventStatisticsCalculator;

    public function injectPermissions(Permissions $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function injectEventStatisticsCalculator(EventStatisticsCalculator $calculator): void
    {
        $this->eventStatisticsCalculator = $calculator;
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
}
