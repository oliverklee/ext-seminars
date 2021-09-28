<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

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
     * the prefix for all locallang keys for prefilling the form
     *
     * @var string
     */
    protected $formFieldPrefix = 'generalMailForm_prefillField_';

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
    protected function modifyEmailWithHook(\Tx_Seminars_Model_Registration $registration, MailMessage $eMail): void
    {
        foreach ($this->getHooks() as $hook) {
            $hook->modifyGeneralEmail($registration, $eMail);
        }
    }
}
