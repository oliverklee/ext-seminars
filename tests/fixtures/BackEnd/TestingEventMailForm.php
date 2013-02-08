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
 * Class tx_seminars_tests_fixtures_BackEnd_TestingEventMailForm for the
 * "seminars" extension.
 *
 * This class represents a testing implementation of the AbstractEventMailForm
 * class.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_tests_fixtures_BackEnd_TestingEventMailForm extends tx_seminars_BackEnd_AbstractEventMailForm {
	/**
	 * the prefix for all locallang keys for prefilling the form, must not be empty
	 *
	 * @var string
	 */
	protected $formFieldPrefix = 'testForm_prefillField_';

	/**
	 * Returns the label for the submit button.
	 *
	 * @return string label for the submit button, will not be empty
	 */
	protected function getSubmitButtonLabel() {
		return $GLOBALS['LANG']->getLL('eventMailForm_confirmButton');
	}

	/**
	 * Returns the initial value for a certain field.
	 *
	 * @param string $fieldName
	 *        the name of the field for which to get the initial value, must be
	 *        either 'subject' or 'messageBody'
	 *
	 * @return string the initial value of the field, will be empty if no
	 *                initial value is defined
	 */
	public function getInitialValue($fieldName) {
		return parent::getInitialValue($fieldName);
	}

	/**
	 * Sets the date format for the event.
	 *
	 * @return void
	 */
	public function setDateFormat() {
		$this->getOldEvent()->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
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
	 * Sets an error message.
	 *
	 * @param string $fieldName
	 *        the field name to set the error message for, must be "messageBody"
	 *        or "subject"
	 * @param string $message the error message to set, may be empty
	 *
	 * @return void
	 */
	public function setErrorMessage($fieldName, $message) {
		parent::setErrorMessage($fieldName, $message);
	}

	/**
	 * Returns all error messages set via setErrorMessage for the given field
	 * name.
	 *
	 * @param string $fieldName
	 *        the field name for which the error message should be returned,
	 *        must not be empty
	 *
	 * @return string the error message for the field, will be empty if there's
	 *                no error message for this field
	 */
	public function getErrorMessage($fieldName) {
		return parent::getErrorMessage($fieldName);
	}
}
?>