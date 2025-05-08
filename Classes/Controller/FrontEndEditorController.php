<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\EventTypeRepository;
use OliverKlee\Seminars\Domain\Repository\OrganizerRepository;
use OliverKlee\Seminars\Domain\Repository\SpeakerRepository;
use OliverKlee\Seminars\Domain\Repository\VenueRepository;
use OliverKlee\Seminars\Seo\SlugGenerator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for editing single events in the FE.
 */
class FrontEndEditorController extends ActionController
{
    private EventRepository $eventRepository;

    private EventTypeRepository $eventTypeRepository;

    private OrganizerRepository $organizerRepository;

    private SpeakerRepository $speakerRepository;

    private VenueRepository $venueRepository;

    private SlugGenerator $slugGenerator;

    public function __construct(
        EventRepository $eventRepository,
        EventTypeRepository $eventTypeRepository,
        OrganizerRepository $organizerRepository,
        SpeakerRepository $speakerRepository,
        VenueRepository $venueRepository,
        SlugGenerator $slugGenerator
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventTypeRepository = $eventTypeRepository;
        $this->organizerRepository = $organizerRepository;
        $this->speakerRepository = $speakerRepository;
        $this->venueRepository = $venueRepository;
        $this->slugGenerator = $slugGenerator;
    }

    private function getLoggedInUserUid(): int
    {
        return (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'id');
    }

    public function indexAction(): ResponseInterface
    {
        $events = $this->eventRepository->findSingleEventsByOwnerUid($this->getLoggedInUserUid());
        $this->view->assign('events', $events);

        return $this->htmlResponse();
    }

    /**
     * Checks if the logged-in FE user is the owner of the provided event, and throws an exception otherwise.
     *
     * This should only happen if someone manipulates the request.
     *
     * Note: This cannot go into an `initialize*Action()` method because the event is not available there.
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
     * @IgnoreValidation("event")
     */
    public function editSingleEventAction(SingleEvent $event): ResponseInterface
    {
        $this->checkEventOwner($event);

        $this->view->assign('event', $event);
        $this->assignAuxiliaryRecordsToView();

        return $this->htmlResponse();
    }

    private function assignAuxiliaryRecordsToView(): void
    {
        $this->view->assign('eventTypes', $this->eventTypeRepository->findAllPlusNullEventType());
        $this->view->assign('organizers', $this->organizerRepository->findAll());
        $this->view->assign('speakers', $this->speakerRepository->findAll());
        $this->view->assign('venues', $this->venueRepository->findAll());
    }

    public function updateSingleEventAction(SingleEvent $event): ResponseInterface
    {
        $this->checkEventOwner($event);

        $uid = $event->getUid();
        \assert(\is_int($uid) && $uid > 0);
        $recordData = ['uid' => $uid, 'title' => $event->getInternalTitle()];
        $event->setSlug($this->slugGenerator->generateSlug(['record' => $recordData]));

        $this->eventRepository->update($event);
        $this->eventRepository->persistAll();

        return $this->redirect('index');
    }

    public function newSingleEventAction(): ResponseInterface
    {
        $eventToCreate = GeneralUtility::makeInstance(SingleEvent::class);
        $this->view->assign('event', $eventToCreate);
        $this->assignAuxiliaryRecordsToView();

        return $this->htmlResponse();
    }

    public function createSingleEventAction(SingleEvent $event): ResponseInterface
    {
        $event->setOwnerUid($this->getLoggedInUserUid());
        $folderSettings = $this->settings['folderForCreatedEvents'] ?? null;
        $folderUid = \is_string($folderSettings) ? (int)$folderSettings : 0;
        $event->setPid($folderUid);

        // We first need to persist the event to get a UID for it, so we can generate a slug.
        $this->eventRepository->add($event);
        $this->eventRepository->persistAll();

        $uid = $event->getUid();
        \assert(\is_int($uid) && $uid > 0);
        $recordData = ['uid' => $uid, 'title' => $event->getInternalTitle()];
        $event->setSlug($this->slugGenerator->generateSlug(['record' => $recordData]));

        $this->eventRepository->update($event);
        $this->eventRepository->persistAll();

        return $this->redirect('index');
    }
}
