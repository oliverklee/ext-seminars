<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for the event list in the BE module.
 */
class ModuleController extends ActionController
{
    use EventStatisticsTrait;
    use PageUidTrait;
    use PermissionsTrait;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function overviewAction(): void
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
