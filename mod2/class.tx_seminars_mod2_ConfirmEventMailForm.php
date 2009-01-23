<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Mario Rimann (mario@screenteam.com)
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
 * Class 'tx_seminars_mod2_ConfirmEventMailForm' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_mod2_ConfirmEventMailForm extends tx_seminars_mod2_EventMailForm  {

	/**
	 * @var string the action of this form
	 */
	protected $action = 'confirmEvent';

	/**
	 * Returns the HTML for the submit button.
	 *
	 * @return string HTML for the submit button, will not be empty
	 */
	protected function createSubmitButton() {
		return '<p><input type="submit" value="' .
			$GLOBALS['LANG']->getLL('eventMailForm_confirmButton') .
			'" class="confirmButton" /></p>';
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
			'eventMailForm_prefillFieldForConfirmation_' . $fieldName
		);
		if ($fieldName == 'subject') {
			$result .= ' ' . $this->getEvent()->getTitleAndDate();
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_ConfirmEventMailForm.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_ConfirmEventMailForm.php']);
}
?>