<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
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

    /**
     * Shows a list of upcoming events.
     */
    public function outlookAction(): ResponseInterface
    {
        $events = $this->eventRepository->findUpcoming();

        $this->view->assign('events', $events);

        return $this->htmlResponse();
    }

    /**
     * @IgnoreValidation("event")
     */
    public function showAction(Event $event): ResponseInterface
    {
        $this->view->assign('event', $event);

        return $this->htmlResponse();
    }
}
