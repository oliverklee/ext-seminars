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
 * Class 'tx_seminars_BackEnd_EventMailForm' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
abstract class tx_seminars_BackEnd_EventMailForm {
	/**
	 * @var tx_seminars_seminar the event which we want to list/show
	 */
	private $event = null;

	/**
	 * @var boolean whether the form is complete
	 */
	private $isComplete = TRUE;

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
	 * @var string the prefix for all locallang keys for prefilling the form,
	 *             must not be empty
	 */
	protected $formFieldPrefix;

	/**
	 * @var integer the status to set when submitting the form
	 */
	protected $statusToSet = tx_seminars_seminar::STATUS_PLANNED;

	/**
	 * The constructor of this class. Instantiates an event object.
	 *
	 * @throws Exception if event could not be instantiated
	 *
	 * @param integer UID of an event, must be > 0
	 */
	public function __construct($eventUid) {
		$this->event = tx_oelib_ObjectFactory::make(
			'tx_seminars_seminar', $eventUid
		);

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
	 * @return string HTML for the whole form, will be empty if the user has
	 *                insufficient permissions
	 */
	public function render() {
		if (!$this->checkAccess()) {
			return '';
		}

		if ($this->isSubmitted() && $this->validateFormData()) {
			$this->setEventStatus();
			$this->sendEmailToAttendees();
			$this->redirectToListView();
		}

		return '<fieldset id="EventMailForm"><form action="index.php?id=' .
			tx_oelib_PageFinder::getInstance()->getPageUid() .
			'&amp;subModule=1" method="post">' .
			$this->createSenderFormElement() .
			$this->createSubjectFormElement() .
			$this->createMessageBodyFormElement() .
			$this->createBackButton() .
			$this->createSubmitButton() .
			'<p><input type="hidden" name="action" value="' . $this->action .
			'" /><input type="hidden" name="eventUid" value="' .
			$this->event->getUid() . '" /><input type="hidden" ' .
			'name="isSubmitted" value="1" /></p></form></fieldset>';
	}

	/**
	 * Checks whether the form was already submitted by the user.
	 *
	 * @return boolean TRUE if the form was submitted by the user, FALSE otherwise
	 */
	protected function isSubmitted() {
		return $this->getPostData('isSubmitted') == '1';
	}

	/**
	 * Validates the input that comes via POST data. If a field contains invalid
	 * data, an error message for this field is stored in $this->errorMessages.
	 *
	 * The following fields are tested for being non-empty:
	 * - subject
	 * - messageBody
	 *
	 * @return boolean TRUE if the form data is valid, FALSE otherwise
	 */
	private function validateFormData() {
		if ($this->getPostData('subject') == '') {
			$this->markAsIncomplete();
			$this->setErrorMessage(
				'subject',
				$GLOBALS['LANG']->getLL('eventMailForm_error_subjectMustNotBeEmpty')
			);
		}

		if ($this->getPostData('messageBody') == '') {
			$this->markAsIncomplete();
			$this->setErrorMessage(
				'messageBody',
				$GLOBALS['LANG']->getLL('eventMailForm_error_messageBodyMustNotBeEmpty')
			);
		}

		return $this->isComplete;
	}

	/**
	 * Marks the form as incomplete (i.e. some fields were empty or not filled
	 * with valid data). This will hinder the later process to really send the
	 * mail and do any further processing with the event.
	 *
	 * This method is public for testing only.
	 */
	public function markAsIncomplete() {
		$this->isComplete = FALSE;
	}

	/**
	 * Checks whether the current back-end user has the needed permissions to
	 * access this form.
	 *
	 * @return boolean TRUE if the user is allowed to see/use the form, FALSE otherwise
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

		$organizers = $this->event->getOrganizerBag();
		$multipleOrganizers = $organizers->count() > 1;

		if ($multipleOrganizers) {
			$result .= '<select id="sender" name="sender">';
			$openingOptionTag = '<option value="';
			$bracketClose = '">';
			$closingOptionTag = '</option>';
		} else {
			$result .= '<input type="hidden" id="sender" name="sender" value="';
			$openingOptionTag = '';
			$bracketClose = '" />';
			$closingOptionTag = '';
		}

		foreach ($organizers as $currentOrganizer) {
			$result .=  $openingOptionTag . $currentOrganizer->getUid() .
				$bracketClose . htmlspecialchars(
					'"' . $currentOrganizer->getName() . '"' .
					' <' . $currentOrganizer->getEMailAddress() . '>'
				) . $closingOptionTag;
		}

		$organizers->__destruct();

		return $result . ($multipleOrganizers ? '</select>' : '') . '</p>';
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
		$classMarker = ($this->hasErrorMessage('subject'))
			? 'class="error" ' : '';

		return '<p><label for="subject">' .
			$GLOBALS['LANG']->getLL('eventMailForm_subject') . '</label>' .
			'<input type="text" id="subject" name="subject" value="' .
			htmlspecialchars($this->fillFormElement('subject'), ENT_QUOTES, 'utf-8') . '" ' .
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
		$messageBody = $this->fillFormElement('messageBody');
		$classMarker = ($this->hasErrorMessage('messageBody')) ? ', error' : '';

		return '<p><label for="messageBody">' .
			$GLOBALS['LANG']->getLL('eventMailForm_message') . '</label>' .
			'<textarea cols="50" rows="20" class="eventMailForm_message' .
			$classMarker . '" id="messageBody" name="messageBody">' .
			htmlspecialchars($messageBody) . '</textarea>' .
			$this->getErrorMessage('messageBody') . '</p>';
	}

	/**
	 * Returns the HTML for the back button.
	 *
	 * @return string HTML for the back button, will not be empty
	 */
	protected function createBackButton() {
		return '<p><input type="button" value="' .
			$GLOBALS['LANG']->getLL('eventMailForm_backButton') .
			'" class="backButton" onclick="window.location=window.location" />' .
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
	protected function getErrorMessage($fieldName) {
		if ($fieldName == '') {
			throw new Exception('$fieldName must not be empty.');
		}

		$result = '';

		if ($this->hasErrorMessage($fieldName)) {
			$result = '<span class="EventMailForm_error">' .
				$this->errorMessages[$fieldName] . '</span>';
		}

		return $result;
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
	 * @return string either the data from POST array or a default value for
	 *                this field
	 */
	protected function fillFormElement($fieldName) {
		if ($this->isSubmitted()) {
			$result = $this->getPostData($fieldName);
		} else {
			$result = $this->getInitialValue($fieldName);
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
	 * @return boolen TRUE if the stored POST data contains an entry, FALSE otherwise
	 */
	protected function hasPostData($key) {
		if ($key == '') {
			throw new Exception('$key must not be empty.');
		}

		return isset($this->postData[$key]);
	}

	/**
	 * Sends an e-mail to the attendees to inform about the changed event state.
	 */
	private function sendEmailToAttendees() {
		$organizer = tx_oelib_ObjectFactory::make(
			'tx_seminars_organizer', intval($this->getPostData('sender'))
		);

		$registrationBagBuilder
			= tx_oelib_ObjectFactory::make('tx_seminars_registrationBagBuilder');
		$registrationBagBuilder->limitToEvent($this->getEvent()->getUid());
		$registrations = $registrationBagBuilder->build();

		foreach ($registrations as $registration) {
			if (!$registration->getFrontEndUser()->hasEMailAddress()) {
				continue;
			}
			$eMail = tx_oelib_ObjectFactory::make('tx_oelib_Mail');
			$eMail->setSender($organizer);
			$eMail->setSubject($this->getPostData('subject'));
			$eMail->addRecipient($registration->getFrontEndUser());
			$eMail->setMessage(
				$this->createMessageBody(
					$registration->getFrontEndUser(), $organizer
				)
			);

			tx_oelib_mailerFactory::getInstance()->getMailer()->send($eMail);
			$eMail->__destruct();
		}

		$organizer->__destruct();
		$registrations->__destruct();
	}

	/**
	 * Marks an event according to the status to set and commits the change to
	 * the database.
	 */
	private function setEventStatus() {
		$this->getEvent()->setStatus($this->statusToSet);
		$this->getEvent()->commitToDb();
	}

	/**
	 * Redirects to the list view.
	 */
	private function redirectToListView() {
		tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->addHeader(
			'Location: ' . t3lib_div::locationHeaderUrl(
				'/typo3conf/ext/seminars/BackEnd/index.php?id=' .
				tx_oelib_PageFinder::getInstance()->getPageUid()
		));
	}

	/**
	 * Returns the HTML for the submit button.
	 *
	 * @return string HTML for the submit button, will not be empty
	 */
	protected function createSubmitButton() {
		return '<p><button class="submitButton '. $this->action . '">' .
			'<p>' . $this->getSubmitButtonLabel() . '</p></button></p>';
	}

	/**
	 * Returns the label for the submit button.
	 *
	 * @return string label for the submit button, will not be empty
	 */
	abstract protected function getSubmitButtonLabel();

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
	protected function getInitialValue($fieldName) {
		switch ($fieldName) {
			case 'subject':
				$result = $this->appendTitleAndDate($this->formFieldPrefix);
				break;
			case 'messageBody':
				$result = $this->getMessageBodyFormContent();
				break;
			default:
				throw new Exception('There is no initial value for the field "' .
					$fieldName . '" defined.'
				);
		}

		return $result;

	}

	/**
	 * Appends the title and the date to the subject.
	 *
	 * @param string $prefix
	 *        the prefix for the locallang key of the subject, must be either
	 *        "cancelMailForm_prefillField_" or "confirmMailForm_prefillField_"
	 *        and always have a trailing underscore
	 *
	 * @return string the subject for the mail form suffixed with the event
	 *                title and date, will be empty if no locallang label
	 *                could be found for the given prefix
	 */
	private function appendTitleAndDate($prefix) {
		return $GLOBALS['LANG']->getLL($prefix . 'subject') . ' ' .
			$this->getEvent()->getTitleAndDate();
	}

	/**
	 * Replaces the string placeholder "%s" with a localized placeholder for
	 * the salutation.
	 *
	 * @param string $prefix
	 *        the prefix for the locallang key of the messageBody, must be
	 *        either "cancelMailForm_prefillField_" or
	 *        "confirmMailForm_prefillField_" and always have a trailing
	 *        underscore
	 *
	 * @return string the content for the prefilled messageBody field with the
	 *                replaced placeholders, will be empty if no locallang label
	 *                for the given prefix could be found
	 */
	protected function localizeSalutationPlaceholder($prefix) {
		$salutation = tx_oelib_ObjectFactory::make(
			'tx_seminars_EmailSalutation'
		);
		$eventDetails = $salutation->createIntroduction(
			'"%s"',
			$this->getEvent()
		);
		$introduction = sprintf(
			$GLOBALS['LANG']->getLL($prefix . 'introduction'),
			$eventDetails
		);
		$salutation->__destruct();

		return '%' . $GLOBALS['LANG']->getLL('mailForm_salutation') . LF . LF .
			$introduction . LF . $GLOBALS['LANG']->getLL($prefix . 'messageBody');
	}

	/**
	 * Creates the message body for the e-mail.
	 *
	 * @param tx_seminars_Model_FrontEndUser $user the recipient of the e-mail
	 * @param tx_seminars_organizer $organizer
	 *                              the organizer which is selected as sender
	 *
	 * @return string the messsage with the salutation replaced by the user's
	 *                name, will be empty if no message has been set in the POST
	 *                data
	 */
	private function createMessageBody(
		tx_seminars_Model_FrontEndUser $user, tx_seminars_organizer $organizer
	) {
		$salutation = tx_oelib_ObjectFactory::make('tx_seminars_EmailSalutation');
		$messageText = str_replace(
			'%' . $GLOBALS['LANG']->getLL('mailForm_salutation'),
			$salutation->getSalutation($user),
			$this->getPostData('messageBody')
		);
		$salutation->__destruct();
		$organizerFooter = $organizer->getEmailFooter();
		$messageFooter = ($organizerFooter != '') ? LF . '-- ' . LF .
			$organizerFooter : '';

		return $messageText . $messageFooter;
	}

	/**
	 * Gets the content of the message body for the e-mail.
	 *
	 * @return string the content for the message body, will not be empty
	 */
	abstract protected function getMessageBodyFormContent();

	/**
	 * Sets an error message.
	 *
	 * @param string $fieldName
	 *        the field name to set the error message for, must be "messageBody"
	 *        or "subject"
	 * @param string $message the error message to set, may be empty
	 */
	protected function setErrorMessage($fieldName, $message) {
		if ($this->hasErrorMessage($fieldName)) {
			$this->errorMessages[$fieldName] .= '<br />' . $message;
		} else {
			$this->errorMessages[$fieldName] = $message;
		}
	}

	/**
	 * Checks whether an error message has been set for the given fieldname.
	 *
	 * @param string $fieldName
	 *        the field to check the error message for, must not be empty
	 *
	 * @return boolean TRUE if an error message has been stored for the given
	 *                 fieldname, FALSE otherwise
	 */
	private function hasErrorMessage($fieldName) {
		return isset($this->errorMessages[$fieldName]);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_EventMailForm.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BackEnd/class.tx_seminars_BackEnd_EventMailForm.php']);
}
?>