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

/**
 * This class represents an e-mail form that does not change the event's status.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_BackEnd_GeneralEventMailForm extends Tx_Seminars_BackEnd_AbstractEventMailForm {
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
	protected function getSubmitButtonLabel() {
		return $GLOBALS['LANG']->getLL('generalMailForm_sendButton');
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
			$hook->modifyGeneralEmail($registration, $eMail);
		}
	}
}