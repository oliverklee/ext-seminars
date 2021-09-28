<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Service\EventStatusService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates back-end e-mail form for canceling an event.
 */
class CancelEventMailForm extends AbstractEventMailForm
{
    /**
     * @var string the action of this form
     */
    protected $action = 'cancelEvent';

    /**
     * the prefix for all locallang keys for prefilling the form, must not be empty
     *
     * @var string
     */
    protected $formFieldPrefix = 'cancelMailForm_prefillField_';

    /**
     * Returns the label for the submit button.
     *
     * @return string label for the submit button, will not be empty
     */
    protected function getSubmitButtonLabel(): string
    {
        return $this->getLanguageService()->getLL('cancelMailForm_sendButton');
    }

    /**
     * Marks an event according to the status to set and commits the change to the database.
     */
    protected function setEventStatus(): void
    {
        /** @var EventStatusService $eventStatusService */
        $eventStatusService = GeneralUtility::makeInstance(EventStatusService::class);
        $eventStatusService->cancelAndSave($this->getEvent());

        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->getLanguageService()->getLL('message_eventCanceled'),
            '',
            FlashMessage::OK,
            true
        );
        $this->addFlashMessage($message);
    }

    /**
     * Calls all registered hooks for modifying the e-mail.
     */
    protected function modifyEmailWithHook(\Tx_Seminars_Model_Registration $registration, MailMessage $eMail): void
    {
        foreach ($this->getHooks() as $hook) {
            $hook->modifyCancelEmail($registration, $eMail);
        }
    }
}
