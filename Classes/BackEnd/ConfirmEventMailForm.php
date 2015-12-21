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
 * This class creates back-end e-mail form for confirming an event.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEnd_ConfirmEventMailForm extends tx_seminars_BackEnd_AbstractEventMailForm  {
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
	protected function getSubmitButtonLabel() {
		return $GLOBALS['LANG']->getLL('confirmMailForm_sendButton');
	}

	/**
	 * Gets the content of the message body for the e-mail.
	 *
	 * @return string the content for the message body, will not be empty
	 */
	protected function getMessageBodyFormContent() {
		return $this->localizeSalutationPlaceholder($this->formFieldPrefix);
	}

	/**
	 * Calls all registered hooks for modifying the e-mail.
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to which the e-mail refers
	 * @param tx_oelib_Mail $eMail
	 *        the e-mail to be sent
	 *
	 * @return void
	 */
	protected function modifyEmailWithHook(
		tx_seminars_Model_Registration $registration, tx_oelib_Mail $eMail
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
	protected function setEventStatus() {
		$this->getEvent()->setStatus(tx_seminars_Model_Event::STATUS_CONFIRMED);
		/** @var tx_seminars_Mapper_Event $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event');
		$mapper->save($this->getEvent());

		/** @var FlashMessage $message */
		$message = GeneralUtility::makeInstance(
			FlashMessage::class,
			$GLOBALS['LANG']->getLL('message_eventConfirmed'),
			'',
			FlashMessage::OK,
			TRUE
		);
		$this->addFlashMessage($message);
	}
}