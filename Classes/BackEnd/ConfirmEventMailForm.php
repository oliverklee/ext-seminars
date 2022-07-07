<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Model\Registration;
use OliverKlee\Seminars\Service\EventStatusService;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates back-end e-mail form for confirming an event.
 */
class ConfirmEventMailForm extends AbstractEventMailForm
{
    /**
     * @var string the action of this form
     */
    protected $action = 'confirmEvent';

    /**
     * the prefix for all locallang keys for prefilling the form, must not be empty
     *
     * @var string
     */
    protected $formFieldPrefix = 'confirmMailForm_prefillField_';

    /**
     * Returns the label for the submit button.
     *
     * @return string label for the submit button, will not be empty
     */
    protected function getSubmitButtonLabel(): string
    {
        return $this->getLanguageService()->getLL('confirmMailForm_sendButton');
    }

    /**
     * Calls all registered hooks for modifying the e-mail.
     */
    protected function modifyEmailWithHook(Registration $registration, MailMessage $eMail): void
    {
        foreach ($this->getHooks() as $hook) {
            $hook->modifyConfirmEmail($registration, $eMail);
        }
    }

    /**
     * Marks an event according to the status to set and commits the change to the database.
     */
    protected function setEventStatus(): void
    {
        GeneralUtility::makeInstance(EventStatusService::class)->confirmAndSave($this->getEvent());

        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->getLanguageService()->getLL('message_eventConfirmed'),
            '',
            FlashMessage::OK,
            true
        );
        $this->addFlashMessage($message);
    }
}
