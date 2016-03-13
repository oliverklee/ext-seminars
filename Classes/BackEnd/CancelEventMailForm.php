<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use OliverKlee\Seminars\Service\EventStatusService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates back-end e-mail form for canceling an event.
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_BackEnd_CancelEventMailForm extends Tx_Seminars_BackEnd_AbstractEventMailForm
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
    protected function getSubmitButtonLabel()
    {
        return $GLOBALS['LANG']->getLL('cancelMailForm_sendButton');
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
        $eventStatusService->cancelAndSave($this->getEvent());

        /** @var FlashMessage $message */
        $message = GeneralUtility::makeInstance(
            FlashMessage::class,
            $GLOBALS['LANG']->getLL('message_eventCanceled'),
            '',
            FlashMessage::OK,
            true
        );
        $this->addFlashMessage($message);
    }

    /**
     * Calls all registered hooks for modifying the e-mail.
     *
     * @param Tx_Seminars_Model_Registration $registration
     *        the registration to which the e-mail refers
     * @param Tx_Oelib_Mail $eMail
     *        the e-mail to be sent
     *
     * @return void
     */
    protected function modifyEmailWithHook(
        Tx_Seminars_Model_Registration $registration, Tx_Oelib_Mail $eMail
    ) {
        foreach ($this->getHooks() as $hook) {
            $hook->modifyCancelEmail($registration, $eMail);
        }
    }
}
