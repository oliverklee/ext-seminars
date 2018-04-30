<?php
namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Service\EventStatusService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates back-end e-mail form for confirming an event.
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
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
    protected function getSubmitButtonLabel()
    {
        return $this->getLanguageService()->getLL('confirmMailForm_sendButton');
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
            $hook->modifyConfirmEmail($registration, $eMail);
        }
    }

    /**
     * Marks an event according to the status to set and commits the change to
     * the database.
     *
     * @return void
     */
    protected function setEventStatus()
    {
        /** @var EventStatusService $eventStatusService */
        $eventStatusService = GeneralUtility::makeInstance(EventStatusService::class);
        $eventStatusService->confirmAndSave($this->getEvent());

        /** @var FlashMessage $message */
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
