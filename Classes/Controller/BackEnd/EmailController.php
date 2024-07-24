<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\GeneralEventMailForm;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for sending emails to the attendees of an event.
 */
class EmailController extends ActionController
{
    use PermissionsTrait;

    /**
     * Action for the displaying the email form.
     *
     * @param positive-int $pageUid
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function composeAction(Event $event, int $pageUid, string $subject = '', string $body = ''): void
    {
        $this->checkPermissions();

        $this->view->assign('event', $event);
        $this->view->assign('pageUid', $pageUid);
        $this->view->assign('subject', $subject);
        $this->view->assign('body', $body);
    }

    /**
     * Action for the sending the email.
     *
     * @param positive-int $pageUid
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function sendAction(Event $event, int $pageUid, string $subject, string $body): void
    {
        $this->checkPermissions();

        $eventUid = $event->getUid();
        $_POST['subject'] = $subject;
        $_POST['emailBody'] = $body;

        $emailService = GeneralUtility::makeInstance(GeneralEventMailForm::class, $eventUid);
        $emailService->setPostData(['subject' => $subject, 'messageBody' => $body]);
        $emailService->sendEmailToAttendees();

        $this->redirect('overview', 'BackEnd\\Module');
    }

    /**
     * Checks the that the user has read permissions for events and registrations.
     *
     * As this will not happen under normal circumstances (as there will be no UI to get to the email controller without
     * having the necessary permissions), this method will throw an exception instead of creating a nice flash message.
     */
    private function checkPermissions(): void
    {
        if (!$this->permissions->hasReadAccessToEvents()) {
            throw new \RuntimeException('Missing read permissions for events.', 1671020157);
        }
        if (!$this->permissions->hasReadAccessToRegistrations()) {
            throw new \RuntimeException('Missing read permissions for registrations.', 1671020198);
        }
    }
}
