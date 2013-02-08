<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class represents an e-mail form that does not change the event's status.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BackEnd_GeneralEventMailForm extends tx_seminars_BackEnd_AbstractEventMailForm {
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
			$hook->modifyGeneralEmail($registration, $eMail);
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/GeneralEventMailForm.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/GeneralEventMailForm.php']);
}
?>