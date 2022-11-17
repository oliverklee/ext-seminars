<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for registering for events.
 */
class EventRegistrationController extends ActionController
{
    /**
     * Checks that the user can register for the provided event, and redirects or forwards to the corresponding next
     * action.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function checkPrerequisitesAction(?Event $event = null): void
    {
        if (!$event instanceof Event) {
            $this->redirectToPageForNoEvent();
        }

        $this->forwardToDenyAction('plugin.eventRegistration.heading.sorry');
    }

    /**
     * @return never
     */
    private function redirectToPageForNoEvent(): void
    {
        $pageUid = (int)($this->settings['pageForMissingEvent'] ?? 0);
        $this->redirect(null, null, null, [], $pageUid);
    }

    /**
     * This is a convenience method to simplify multiple calls.
     *
     * @return never
     */
    private function forwardToDenyAction(string $warningMessageKey): void
    {
        $this->forward('denyRegistration', null, null, ['warningMessageKey' => $warningMessageKey]);
    }

    public function denyRegistrationAction(string $warningMessageKey): void
    {
        $this->view->assign('warningMessageKey', $warningMessageKey);
    }
}
