<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Mario Rimann (mario@screenteam.com)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'tx_seminars_tests_fixtures_TestingEventMailForm' for the 'seminars' extension.
 *
 * This class represents an implementation of the EventMailForm class.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_tests_fixtures_TestingEventMailForm extends tx_seminars_BackEnd_EventMailForm {
	/**
	 * @var the prefix for all locallang keys for prefilling the form,
	 *      must not be empty
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
	 */
	public function setDateFormat() {
		$this->getEvent()->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
	}
}
?>