<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

/**
 * This class represents an e-mail form that does not change the event's status.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
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
    protected function getSubmitButtonLabel()
    {
        return $this->getLanguageService()->getLL('generalMailForm_sendButton');
    }

    /**
     * Calls all registered hooks for modifying the e-mail.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to which the e-mail refers
     * @param \Tx_Oelib_Mail $eMail
     *        the e-mail to be sent
     *
     * @return void
     */
    protected function modifyEmailWithHook(
        \Tx_Seminars_Model_Registration $registration,
        \Tx_Oelib_Mail $eMail
    ) {
        foreach ($this->getHooks() as $hook) {
            $hook->modifyGeneralEmail($registration, $eMail);
        }
    }
}
