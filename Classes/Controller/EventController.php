<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for showing events in the frontend.
 */
class EventController extends ActionController
{
    protected EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Shows a list of past events.
     */
    public function archiveAction(): ResponseInterface
    {
        $events = $this->eventRepository->findInPast();

        $this->view->assign('events', $events);

        return $this->htmlResponse();
    }
}
