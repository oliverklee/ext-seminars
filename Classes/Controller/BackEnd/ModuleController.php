<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use TYPO3\CMS\Core\Page\PageRenderer;
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

    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var PageRenderer
     */
    private $pageRenderer;

    public function __construct(
        EventRepository $eventRepository,
        RegistrationRepository $registrationRepository,
        PageRenderer $pageRenderer
    ) {
        $this->eventRepository = $eventRepository;
        $this->registrationRepository = $registrationRepository;
        $this->pageRenderer = $pageRenderer;
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
        $this->view->assign(
            'numberOfRegistrations',
            $this->registrationRepository->countRegularRegistrationsByPageUid($pageUid)
        );

        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Seminars/BackEnd/DeleteConfirmation');
    }
}
