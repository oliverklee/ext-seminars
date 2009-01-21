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
 * Class 'tx_seminars_mod2_TestingEventMailForm' for the 'seminars' extension.
 *
 * This class represents an implementation of the EventMailForm class.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_tests_fixtures_TestingEventMailForm extends tx_seminars_mod2_EventMailForm {
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
	 * Returns either a default value or the value that was sent via POST data
	 * for a given field.
	 *
	 * For the subject field, we fill in the event's title and date after the
	 * default subject for confirming an event.
	 *
	 * @param string the field name, must not be empty
	 *
	 * @return string either the data from POST array or a default value for this
	 *                field, not htmlspecialchars'ed yet
	 */
	protected function fillFormElement($fieldName) {
		if ($this->isSubmitted()) {
			$result = $this->getPostData($fieldName);
		} else {
			$result = $GLOBALS['LANG']->getLL(
				'eventMailForm_prefillFieldForConfirmation_' . $fieldName
			);
			if ($fieldName == 'subject') {
				$this->getEvent()->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
				$result .= ' ' . $this->getEvent()->getTitleAndDate('-');
			}
		}

		return $result;
	}
}
?>