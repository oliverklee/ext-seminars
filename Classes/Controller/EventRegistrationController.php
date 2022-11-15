<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for registering for events.
 */
class EventRegistrationController extends ActionController
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    public function injectEventRepository(EventRepository $repository): void
    {
        $this->eventRepository = $repository;
    }

    /**
     * @Extbase\IgnoreValidation("event")
     */
    public function checkPrerequisitesAction(?Event $event = null): void
    {
        if (!$event instanceof Event) {
            $this->redirectToPageForNoEvent();
            return;
        }
    }

    private function redirectToPageForNoEvent(): void
    {
        $pageUid = (int)($this->settings['pageForMissingEvent'] ?? 0);
        $this->redirect(null, null, null, [], $pageUid);
    }
}
