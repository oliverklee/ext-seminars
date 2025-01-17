<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use OliverKlee\Seminars\Service\RegistrationGuard;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for showing events in the frontend.
 */
class EventController extends ActionController
{
    protected EventRepository $eventRepository;

    protected EventStatisticsCalculator $eventStatisticsCalculator;

    protected RegistrationGuard $registrationGuard;

    public function __construct(
        EventRepository $eventRepository,
        EventStatisticsCalculator $eventStatisticsCalculator,
        RegistrationGuard $registrationGuard
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventStatisticsCalculator = $eventStatisticsCalculator;
        $this->registrationGuard = $registrationGuard;
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
        foreach ($events as $event) {
            $this->eventStatisticsCalculator->enrichWithStatistics($event);
        }
        $this->registrationGuard->setRegistrationPossibleByDateForEvents($events);

        $this->view->assign('events', $events);

        return $this->htmlResponse();
    }

    /**
     * @IgnoreValidation("event")
     */
    public function showAction(Event $event): ResponseInterface
    {
        $this->eventStatisticsCalculator->enrichWithStatistics($event);
        $this->registrationGuard->setRegistrationPossibleByDateForEvents([$event]);

        $this->view->assign('event', $event);

        return $this->htmlResponse();
    }
}
