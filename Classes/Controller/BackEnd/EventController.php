<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
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

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventStatisticsCalculator
     */
    private $eventStatisticsCalculator;

    public function injectPermissions(Permissions $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function injectEventRepository(EventRepository $repository): void
    {
        $this->eventRepository = $repository;
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

    public function indexAction(): void
    {
        $pageUid = $this->getPageUid();

        $this->view->assign('permissions', $this->permissions);
        $this->view->assign('pageUid', $pageUid);

        $events = $this->eventRepository->findByPageUidInBackEndMode($pageUid);
        $this->eventRepository->enrichWithRawData($events);
        foreach ($events as $event) {
            $this->eventStatisticsCalculator->enrichWithStatistics($event);
        }
        $this->view->assign('events', $events);
    }
}
