<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for the event list in the BE module.
 */
class ModuleController extends ActionController
{
    use PageUidTrait;

    private ModuleTemplateFactory $moduleTemplateFactory;

    private EventRepository $eventRepository;

    private RegistrationRepository $registrationRepository;

    private EventStatisticsCalculator $eventStatisticsCalculator;

    private Permissions $permissions;

    private PageRenderer $pageRenderer;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        EventRepository $eventRepository,
        RegistrationRepository $registrationRepository,
        EventStatisticsCalculator $eventStatisticsCalculator,
        Permissions $permissions,
        PageRenderer $pageRenderer
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->eventRepository = $eventRepository;
        $this->registrationRepository = $registrationRepository;
        $this->eventStatisticsCalculator = $eventStatisticsCalculator;
        $this->permissions = $permissions;
        $this->pageRenderer = $pageRenderer;
    }

    public function overviewAction(): ResponseInterface
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
            $this->registrationRepository->countRegularRegistrationsByPageUid($pageUid),
        );

        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Seminars/BackEnd/DeleteConfirmation');

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
