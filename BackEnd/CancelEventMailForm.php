<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Mario Rimann (mario@screenteam.com)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This class creates back-end e-mail form for canceling an event.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEnd_CancelEventMailForm extends tx_seminars_BackEnd_AbstractEventMailForm  {
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
	 * @var tx_seminars_Service_SingleViewLinkBuilder
	 */
	private $linkBuilder = NULL;

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->linkBuilder);

		parent::__destruct();
	}

	/**
	 * Returns the label for the submit button.
	 *
	 * @return string label for the submit button, will not be empty
	 */
	protected function getSubmitButtonLabel() {
		return $GLOBALS['LANG']->getLL('cancelMailForm_sendButton');
	}

	/**
	 * Gets the content of the message body for the e-mail.
	 *
	 * @return string the content for the message body, will not be empty
	 */
	protected function getMessageBodyFormContent() {
		$result = $this->localizeSalutationPlaceholder($this->formFieldPrefix);

		if (!$this->getEvent()->isEventDate()) {
			return $result;
		}

		$builder = t3lib_div::makeInstance('tx_seminars_BagBuilder_Event');
		$builder->limitToEarliestBeginDate($GLOBALS['SIM_EXEC_TIME']);
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
	private function getSingleViewUrl() {
		if ($this->linkBuilder == NULL) {
			$this->injectLinkBuilder(t3lib_div::makeInstance(
				'tx_seminars_Service_SingleViewLinkBuilder'
			));
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
	protected function setEventStatus() {
		$this->getEvent()->setStatus(tx_seminars_Model_Event::STATUS_CANCELED);
		tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->save($this->getEvent());

		$message = t3lib_div::makeInstance(
			't3lib_FlashMessage',
			$GLOBALS['LANG']->getLL('message_eventCanceled'),
			'',
			t3lib_FlashMessage::OK,
			TRUE
		);
		t3lib_FlashMessageQueue::addMessage($message);
	}

	/**
	 * Injects a link builder.
	 *
	 * @param tx_seminars_Service_SingleViewLinkBuilder $linkBuilder
	 *        the link builder instance to use
	 *
	 * @return void
	 */
	public function injectLinkBuilder(
		tx_seminars_Service_SingleViewLinkBuilder $linkBuilder
	) {
		$this->linkBuilder = $linkBuilder;
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
			$hook->modifyCancelEmail($registration, $eMail);
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/CancelEventMailForm.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/CancelEventMailForm.php']);
}