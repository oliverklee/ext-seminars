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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates back-end e-mail form for canceling an event.
 *
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
     * a link builder instance
     *
     * @var Tx_Seminars_Service_SingleViewLinkBuilder
     */
    private $linkBuilder = null;

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset($this->linkBuilder);

        parent::__destruct();
    }

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
     * Gets the content of the message body for the e-mail.
     *
     * @return string the content for the message body, will not be empty
     */
    protected function getMessageBodyFormContent()
    {
        $result = $this->localizeSalutationPlaceholder($this->formFieldPrefix);

        if (!$this->getEvent()->isEventDate()) {
            return $result;
        }

        /** @var Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(Tx_Seminars_BagBuilder_Event::class);
        $builder->limitToEarliestBeginOrEndDate($GLOBALS['SIM_EXEC_TIME']);
        $builder->limitToOtherDatesForTopic($this->getOldEvent());

        if (!$builder->build()->isEmpty()) {
            $result .= LF . LF .
                $GLOBALS['LANG']->getLL('cancelMailForm_alternativeDate') .
                ' <' . $this->getSingleViewUrl() . '>';
        }

        return $result;
    }

    /**
     * Gets the full URL to the single view of the current event.
     *
     * @return string the URL to the single view of the given event, will be
     *                empty if no single view URL could be determined
     */
    private function getSingleViewUrl()
    {
        if ($this->linkBuilder == null) {
            /** @var Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder */
            $linkBuilder = GeneralUtility::makeInstance(Tx_Seminars_Service_SingleViewLinkBuilder::class);
            $this->injectLinkBuilder($linkBuilder);
        }
        $result = $this->linkBuilder->createAbsoluteUrlForEvent($this->getEvent());

        if ($result == '') {
            $this->setErrorMessage(
                'messageBody',
                $GLOBALS['LANG']->getLL('eventMailForm_error_noDetailsPageFound')
            );
        }

        return $result;
    }

    /**
     * Marks an event according to the status to set and commits the change to
     * the database.
     *
     * @return void
     */
    protected function setEventStatus()
    {
        $this->getEvent()->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);
        /** @var Tx_Seminars_Mapper_Event $mapper */
        $mapper = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class);
        $mapper->save($this->getEvent());

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
     * Injects a link builder.
     *
     * @param Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder
     *        the link builder instance to use
     *
     * @return void
     */
    public function injectLinkBuilder(
        Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder
    ) {
        $this->linkBuilder = $linkBuilder;
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
