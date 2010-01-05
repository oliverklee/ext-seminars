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
 * Class 'tx_seminars_BackEnd_ConfirmEventMailForm' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_BackEnd_ConfirmEventMailForm extends tx_seminars_BackEnd_EventMailForm  {
	/**
	 * @var string the action of this form
	 */
	protected $action = 'confirmEvent';

	/**
	 * @var integer the status to set when submitting the form
	 */
	protected $statusToSet = tx_seminars_seminar::STATUS_CONFIRMED;

	/**
	 * Returns the label for the submit button.
	 *
	 * @return string label for the submit button, will not be empty
	 */
	protected function getSubmitButtonLabel() {
		return $GLOBALS['LANG']->getLL('confirmMailForm_sendButton');
	}

	/**
	 * Returns the initial value for a certain field.
	 *
	 * @param string the field name, must not be empty
	 *
	 * @return string the initial value of the field, will be empty if no
	 *                initial value is defined
	 */
	protected function getInitialValue($fieldName) {
		$result = $GLOBALS['LANG']->getLL(
			'confirmMailForm_prefillField_' . $fieldName
		);
		if ($fieldName == 'subject') {
			$result .= ' ' . $this->getEvent()->getTitleAndDate();
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_ConfirmEventMailForm.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_ConfirmEventMailForm.php']);
}
?>