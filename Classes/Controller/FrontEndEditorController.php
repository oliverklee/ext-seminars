<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\EventTypeRepository;
use OliverKlee\Seminars\Domain\Repository\OrganizerRepository;
use OliverKlee\Seminars\Domain\Repository\SpeakerRepository;
use OliverKlee\Seminars\Domain\Repository\VenueRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for editing single events in the FE.
 */
class FrontEndEditorController extends ActionController
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventTypeRepository
     */
    private $eventTypeRepository;

    /**
     * @var OrganizerRepository
     */
    private $organizerRepository;

    /**
     * @var SpeakerRepository
     */
    private $speakerRepository;

    /**
     * @var VenueRepository
     */
    private $venueRepository;

    public function injectEventRepository(EventRepository $repository): void
    {
        $this->eventRepository = $repository;
    }

    public function injectEventTypeRepository(EventTypeRepository $repository): void
    {
        $this->eventTypeRepository = $repository;
    }

    public function injectOrganizerRepository(OrganizerRepository $repository): void
    {
        $this->organizerRepository = $repository;
    }

    public function injectSpeakerRepository(SpeakerRepository $repository): void
    {
        $this->speakerRepository = $repository;
    }

    public function injectVenueRepository(VenueRepository $repository): void
    {
        $this->venueRepository = $repository;
    }

    private function getLoggedInUserUid(): int
    {
        return (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'id');
    }

    public function indexAction(): void
    {
        $events = $this->eventRepository->findSingleEventsByOwnerUid($this->getLoggedInUserUid());
        $this->view->assign('events', $events);
    }

    /**
     * Checks if the logged-in FE user is the owner of the provided event, and throws an exception otherwise.
     *
     * This should only happen if someone manipulates the request.
     *
     * Note: This cannot go into an `intialize*Action()` method because the event is not available there.
     *
     * @throws \RuntimeException
     */
    private function checkEventOwner(SingleEvent $event): void
    {
        if ($event->getOwnerUid() !== $this->getLoggedInUserUid()) {
            throw new \RuntimeException('You do not have permission to edit this event.', 1666954310);
        }
    }

    /**
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("event")
     */
    public function editAction(SingleEvent $event): void
    {
        $this->checkEventOwner($event);

        $this->view->assign('event', $event);
        $this->assignAuxiliaryRecordsToView();
    }

    private function assignAuxiliaryRecordsToView(): void
    {
        $this->view->assign('eventTypes', $this->eventTypeRepository->findAllPlusNullEventType());
        $this->view->assign('organizers', $this->organizerRepository->findAll());
        $this->view->assign('speakers', $this->speakerRepository->findAll());
        $this->view->assign('venues', $this->venueRepository->findAll());
    }

    public function updateAction(SingleEvent $event): void
    {
        $this->checkEventOwner($event);

        $this->eventRepository->update($event);
        $this->eventRepository->persistAll();

        $this->redirect('index');
    }
}
