<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
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

    public function injectEventRepository(EventRepository $repository): void
    {
        $this->eventRepository = $repository;
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
    }

    public function updateAction(SingleEvent $event): void
    {
        $this->checkEventOwner($event);

        $this->eventRepository->update($event);
        $this->eventRepository->persistAll();

        $this->redirect('index');
    }
}
