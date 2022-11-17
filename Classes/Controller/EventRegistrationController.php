<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Service\RegistrationGuard;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for registering for events.
 */
class EventRegistrationController extends ActionController
{
    /**
     * @var RegistrationGuard
     */
    private $registrationGuard;

    public function injectRegistrationGuard(RegistrationGuard $registrationGuard): void
    {
        $this->registrationGuard = $registrationGuard;
    }

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
        if (!$this->registrationGuard->isRegistrationPossibleAtAnyTimeAtAll($event)) {
            $this->forwardToDenyAction('noRegistrationPossibleAtAll');
        }
        \assert($event instanceof EventDateInterface);
        if (!$this->registrationGuard->isRegistrationPossibleByDate($event)) {
            $this->forwardToDenyAction('noRegistrationPossibleAtTheMoment');
        }

        $this->redirect('new', null, null, ['event' => $event]);
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
     * @param non-empty-string $warningMessageKey the key of the message to display,
     *        will automatically get prefixed with `plugin.eventRegistration.error.`
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

    /**
     * Displays the event registration form.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function newAction(Event $event): void
    {
        $this->view->assign('event', $event);
    }

    /**
     * Displays the confirmation page of the event registration form.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function confirmAction(Event $event): void
    {
        $this->view->assign('event', $event);
    }

    /**
     * Creates the registration and redirects to the thank-you action.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function createAction(Event $event): void
    {
        $this->redirect('thankYou', null, null, ['event' => $event]);
    }

    /**
     * Displays the thank-you page.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function thankYouAction(Event $event): void
    {
        $this->view->assign('event', $event);
    }
}
