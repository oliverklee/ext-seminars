<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Model\Registration;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * This class represents an e-mail form that does not change the event's status.
 */
class GeneralEventMailForm extends AbstractEventMailForm
{
    /**
     * the action of this form
     *
     * @var string
     */
    protected $action = 'sendEmail';

    /**
     * Returns the label for the submit button.
     *
     * @return string label for the submit button, will not be empty
     */
    protected function getSubmitButtonLabel(): string
    {
        return $this->getLanguageService()->getLL('generalMailForm_sendButton');
    }

    /**
     * Calls all registered hooks for modifying the e-mail.
     */
    protected function modifyEmailWithHook(Registration $registration, MailMessage $eMail): void
    {
        foreach ($this->getHooks() as $hook) {
            $hook->modifyGeneralEmail($registration, $eMail);
        }
    }
}
