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
 * Class 'tx_seminars_mod2_EventMailForm' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
abstract class tx_seminars_mod2_EventMailForm {
	/**
	 * @var tx_seminars_seminar the event which we want to list/show
	 */
	private $event = null;

	/**
	 * @var boolean whether the form is complete
	 */
	private $isComplete = true;

	/**
	 * @var array the array of error messages
	 */
	private $errorMessages = array();

	/**
	 * @var array the array of POST data
	 */
	private $postData = array();

	/**
	 * @var string the action of this form
	 */
	protected $action;

	/**
	 * The constructor of this class. Instantiates an event object.
	 *
	 * @throws Exception if event could not be instantiated
	 *
	 * @param integer UID of an event, must be > 0
	 */
	public function __construct($eventUid) {
		$eventClassName = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
		$this->event = new $eventClassName($eventUid);

		if (!$this->event->isOk()) {
			throw new Exception('There is no event with this UID.');
		}
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->event) {
			$this->event->__destruct();
			unset($this->event);
		}
	}

	/**
	 * Returns the HTML needed to show the form. If the current user has not
	 * the necessary permissions, an empty string is returned.
	 *
	 * @return string HTML for the whole form, will be empty if user hat not
	 *                enough permissions
	 */
	public function render() {
		if (!$this->checkAccess()) {
			return '';
		}

		if ($this->isSubmitted()) {
			$this->validateFormData();
		}

		return '<fieldset id="EventMailForm"><form action="index.php?id=' .
			$pageData['uid'] .
			'&amp;subModule=1" method="post">' .
			$this->createSenderFormElement() .
			$this->createSubjectFormElement() .
			$this->createMessageBodyFormElement() .
			$this->createCancelButton() .
			$this->createSubmitButton() .
			'<p><input type="hidden" name="action" value="' . $this->action .
			'" /><input type="hidden" name="eventUid" value="' .
			$this->event->getUid() . '" /><input type="hidden" ' .
			'name="isSubmitted" value="true" /></p></form></fieldset>';
	}

	/**
	 * Checks whether the form was already submitted by the user.
	 *
	 * @return boolean true if the form was submitted by the user, false otherwise
	 */
	protected function isSubmitted() {
		return $this->getPostData('isSubmitted') == 'true';
	}

	/**
	 * Validates the input that comes via POST data. If a field contains invalid
	 * data, an error message for this field is stored in $this->errorMessages.
	 *
	 * The following fields are tested for being non-empty:
	 * - subject
	 * - messageBody
	 */
	public function validateFormData() {
		if ($this->getPostData('subject') == '') {
			$this->markAsIncomplete();
			$this->errorMessages['subject'] = $GLOBALS['LANG']->getLL(
				'eventMailForm_error_subjectMustNotBeEmpty'
			);
		}

		if ($this->getPostData('messageBody') == '') {
			$this->markAsIncomplete();
			$this->errorMessages['messageBody'] = $GLOBALS['LANG']->getLL(
				'eventMailForm_error_messageBodyMustNotBeEmpty'
			);
		}
	}

	/**
	 * Marks the form as incomplete (i.e. some fields were empty or not filled
	 * with valid data). This will hinder the later process to really send the
	 * mail and do any further processing with the event.
	 *
	 * This method is for testing only.
	 */
	public function markAsIncomplete() {
		$this->isComplete = false;
	}

	/**
	 * Checks whether the current back-end user has the needed permissions to
	 * access this form.
	 *
	 * @return boolean true if the user is allowed to see/use the form, false otherwise
	 */
	public function checkAccess() {
		return $GLOBALS['BE_USER']->check('tables_select', SEMINARS_TABLE_SEMINARS);
	}

	/**
	 * Returns the HTML for the sender field of the form. If the event has more
	 * then one organizer, a drop-down menu is returned - a hidden field otherwise.
	 *
	 * @return string the HTML for rendering the sender field of the form, will
	 *                not be empty
	 */
	protected function createSenderFormElement() {
		$result = '<p><label for="sender">' .
			$GLOBALS['LANG']->getLL('eventMailForm_sender') . '</label>';
		$organizers = $this->event->getOrganizersNameAndEmail();

		if (count($organizers) > 1) {
			$result .= '<select id="sender" name="sender">';
			foreach ($organizers as $currentOrganizer) {
				$currentOrganizer = htmlspecialchars($currentOrganizer);
				$result .= '<option value="' . $currentOrganizer . '">' .
					$currentOrganizer . '</option>';
			}
			$result .= '</select>';
		} else {
			$currentOrganizer = htmlspecialchars($organizers[0]);
			$result .= '<input type="hidden" id="sender" name="sender" value="' .
				$currentOrganizer . '" />';
			$result .= $currentOrganizer;
		}
		$result .= '</p>';

		return $result;
	}

	/**
	 * Returns the HTML for the subject field of the form. It gets pre-filled
	 * depending on the implementation of this abstract class. Shows an error
	 * message next to the field if required after validation of this field.
	 *
	 * @return string HTML for the subject field, optionally with an error
	 *                message, will not be empty
	 */
	protected function createSubjectFormElement() {
		$classMarker = (isset(
			$this->errorMessages['subject'])
		) ? 'class="error" ' : '';

		return '<p><label for="subject">' .
			$GLOBALS['LANG']->getLL('eventMailForm_subject') . '</label>' .
			'<input type="text" id="subject" name="subject" value="' .
			$this->fillFormElement('subject') . '" ' .
			$classMarker . '/>' . $this->getErrorMessage('subject') . '</p>';
	}

	/**
	 * Returns the HTML for the message body field of the form. It gets pre-filled
	 * depending on the implementation of this abstract class. Shows an error
	 * message next to the field if required after validation of this field.
	 *
	 * @return string HTML for the subject field, optionally with an error message
	 */
	protected function createMessageBodyFormElement() {
		$classMarker = (isset(
			$this->errorMessages['messageBody'])
		) ? ', error' : '';

		return '<p><label for="messageBody">' .
			$GLOBALS['LANG']->getLL('eventMailForm_message') . '</label>' .
			'<textarea cols="50" rows="20" class="eventMailForm_message' .
			$classMarker . '" id="messageBody" name="messageBody">' .
			htmlspecialchars($this->fillFormElement('messageBody')) . '</textarea>' .
			$this->getErrorMessage('messageBody') . '</p>';
	}

	/**
	 * Returns the HTML for the cancel button.
	 *
	 * @return string HTML for the cancel button, will not be empty
	 */
	protected function createCancelButton() {
		return '<p><input type="button" value="' .
			$GLOBALS['LANG']->getLL('eventMailForm_cancelButton') .
			'" class="cancelButton" onclick="window.location=window.location" />' .
			'</p>';
	}

	/**
	 * Returns the event object.
	 *
	 * @return tx_seminars_seminar the event object
	 */
	protected function getEvent() {
		return $this->event;
	}

	/**
	 * Returns error messages from $this->errorMessages depending on a certain
	 * field name.
	 *
	 * @throws Exception if $fieldName is empty
	 *
	 * @param string the field name for which the error message should be returned, must not be empty
	 *
	 * @return string the error message for the field, will be empty if there's no error message for this field
	 */
	protected function getErrorMessage($fieldName) {
		if ($fieldName == '') {
			throw new Exception('$fieldName must not be empty.');
		}

		$result = '';

		if (!$this->isComplete && isset($this->errorMessages[$fieldName])) {
			$result = '<span class="EventMailForm_error">' .
				$this->errorMessages[$fieldName] . '</span>';
		}

		return $result;
	}

	/**
	 * Sets the POST data.
	 *
	 * @param array associative array with the POST data, may be empty
	 */
	public function setPostData(array $postData) {
		$this->postData = $postData;
	}

	/**
	 * Returns an entry from the stored POST data or an empty string if that
	 * key is not set.
	 *
	 * @param string the key of the field to return, must not be empty
	 *
	 * @return string the value of the field, may be empty
	 */
	protected function getPostData($key) {
		if (!$this->hasPostData($key)) {
			return '';
		}

		return $this->postData[$key];
	}

	/**
	 * Checks whether the stored POST data contains data for a certain field.
	 *
	 * @throws Exception if key is empty
	 *
	 * @param string the key of the field to check for, must not be empty
	 *
	 * @return boolen true if the stored POST data contains an entry, false otherwise
	 */
	protected function hasPostData($key) {
		if ($key == '') {
			throw new Exception('$key must not be empty.');
		}

		return isset($this->postData[$key]);
	}

	/**
	 * Returns the HTML for the submit button.
	 *
	 * @return string HTML for the submit button, will not be empty
	 */
	abstract protected function createSubmitButton();

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
	abstract protected function fillFormElement($fieldName);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_EventMailForm.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/class.tx_seminars_mod2_EventMailForm.php']);
}
?>