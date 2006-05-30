<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2006 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars_registration' for the 'seminars' extension.
 *
 * This class represents a registration/attendance.
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_objectfromdb.php');

class tx_seminars_registration extends tx_seminars_objectfromdb {
	/** Same as class name */
	var $prefixId = 'tx_seminars_registration';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_registration.php';

	/** our seminar (object) */
	var $seminar = null;

	/** whether we have already initialized the templates (which is done lazily) */
	var $isTemplateInitialized = false;

	/** This variable stores the data of the user as an array and makes it available without further database queries. It will get filled with data in the constructor. */
	var $userData;

	/**
	 * The constructor.
	 *
	 * @param	object		content object (must not be null)
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object. If this parameter is not provided or null, setRegistrationData() needs to be called directly after construction or this object will not be usable.
	 *
	 * @access	public
	 */
	function tx_seminars_registration(&$cObj, $dbResult = null) {
		$this->cObj =& $cObj;
		$this->init();
		$this->tableName = $this->tableAttendances;

	 	if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->getDataFromDbResult($GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult));

			if ($this->isOk()) {
				/** Name of the seminar class in case someone subclasses it. */
				$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
				$this->seminar =& new $seminarClassname($this->recordData['seminar']);

				// Store the user data in $this->userData.
				$this->retrieveUserData();
			}
	 	}
	}

	/**
	 * Sets this registration's data if this registration is newly created instead of from a DB query.
	 * This function must be called directly after construction or this object will not be usable.
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	integer		the UID of the feuser who wants to sign up
	 * @param	array		associative array with the registration data the user has just entered
	 *
	 * @access	public
	 */
	function setRegistrationData(&$seminar, $userUid, $registrationData) {
		$this->seminar =& $seminar;

		$this->recordData = array();

		$this->recordData['seminar'] = $seminar->getUid();
		$this->recordData['user'] = $userUid;

		$this->recordData['seats'] = $registrationData['seats'];

		$this->recordData['interests'] = $registrationData['interests'];
		$this->recordData['expectations'] = $registrationData['expectations'];
		$this->recordData['background_knowledge'] = $registrationData['background_knowledge'];
		$this->recordData['accommodation'] = $registrationData['accommodation'];
		$this->recordData['food'] = $registrationData['food'];
		$this->recordData['known_from'] = $registrationData['known_from'];
		$this->recordData['notes'] = $registrationData['notes'];

		$this->recordData['pid'] = $this->getConfValueInteger('attendancesPID');

		if ($this->isOk()) {
			// Store the user data in $this->userData.
			$this->retrieveUserData();
			$this->createTitle();
		}

		return;
	}

	/**
	 * Gets our title, containing:
	 *  the attendee's full name,
	 *  the seminar title,
	 *  the seminar date
	 *
	 * @return	string		the attendance title
	 *
	 * @access	public
	 */
	function getTitle() {
		return $this->getRecordPropertyString('title');
	}

	/**
	 * Creates our title and writes it to $this->title.
	 *
	 * The title is constructed like this:
	 *   Name of Attendee / Title of Seminar seminardate
	 *
	 * @access	private
	 */
	function createTitle() {
		$this->recordData['title'] = $this->getUserName().' / '.$this->seminar->getTitle().', '.$this->seminar->getDate('-');

		return;
	}

 	/**
	 * Gets the complete FE user data as an array.
	 * The attendee's user data (from fe_users) will be written to $this->userData.
	 * $this->userData will be null if retrieving the user data fails.
	 *
	 * @access	private
	 */
	function retrieveUserData() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'fe_users',
				'uid='.$this->getUser());

		if ($dbResult) {
			$this->userData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		} else {
			$this->userData = null;
		}

		return;
	}

	/**
	 * Returns a value out of the userData array. The return value may be an empty string if the key is not defined in the userData array.
	 *
	 * @param	string		the key to retrieve
	 *
	 * @return	string		the value retrieved from $this->userData, may be empty
	 *
	 * @access	protected
	 */
	function getUserData($key) {
		$result = '';
		if (is_array($this->userData) && !empty($key)) {
			if (array_key_exists($key, $this->userData)) {
				$result = $this->userData[$key];
			}
		}

		return $result;
	}

	/**
	 * Gets the attendee's uid.
	 *
	 * @return	integer		the attendee's feuser uid
	 *
	 * @access	public
	 */
	function getUser() {
		return $this->getRecordPropertyInteger('user');
	}

	/**
	 * Gets the attendee's (real) name
	 *
	 * @return	string		the attendee's name
	 *
	 * @access	private
	 */
	function getUserName() {
		return $this->getUserData('name');
	}

	/**
	 * Gets the attendee's e-mail address in the format
	 * "john.doe@example.com".
	 *
	 * @return	string		the attendee's e-mail address
	 *
	 * @access	private
	 */
	function getUserEmail() {
		return $this->getUserData('email');
	}

	/**
	 * Gets the attendee's name and e-mail address in the format
	 * '"John Doe" <john.doe@example.com>'.
	 *
	 * @return	string		the attendee's name and e-mail address
	 *
	 * @access	private
	 */
	function getUserNameAndEmail() {
		return '"'.$this->getUserData('name').'" <'.$this->getUserData('email').'>';
	}

	/**
	 * Gets the seminar's uid.
	 *
	 * @return	integer		the seminar's uid
	 *
	 * @access	public
	 */
	function getSeminar() {
		return $this->getRecordPropertyInteger('seminar');
	}

	/**
	 * Gets whether this attendance has already been paid for.
	 *
	 * @return	boolean		whether this attendance has already been paid for
	 *
	 * @access	public
	 */
	function getIsPaid() {
		trigger_error('Member function tx_seminars_registration->getIsPaid not implemented yet.');
	}

	/**
	 * Gets the date at which the user has paid for this attendance.
	 *
	 * @return	integer		the date at which the user has paid for this attendance
	 *
	 * @access	public
	 */
	function getDatePaid() {
		trigger_error('Member function tx_seminars_registration->getDatePaid not implemented yet.');
	}

	/**
	 * Gets the method of payment.
	 *
	 * @return	integer		the uid of the method of payment (may be 0 if none is given)
	 *
	 * @access	public
	 */
	function getMethodOfPayment() {
		trigger_error('Member function tx_seminars_registration->getMethodOfPayment not implemented yet.');
	}

	/**
	 * Gets whether the attendee has been at the seminar.
	 *
	 * @return	boolean		whether the attendee has attended the seminar
	 *
	 * @access	public
	 */
	function getHasBeenThere() {
		trigger_error('Member function tx_seminars_registration->getHasBeenThere not implemented yet.');
	}

	/**
	 * Gets the attendee's special interests in the subject.
	 *
	 * @return	string		a description of the attendee's special interests (may be empty)
	 *
	 * @access	public
	 */
	function getInterests() {
		return $this->getRecordPropertyString('interests');
	}

	/**
	 * Gets the attendee's expectations for the seminar.
	 *
	 * @return	string		a description of the attendee's expectations for the seminar (may be empty)
	 *
	 * @access	public
	 */
	function getExpectations() {
		return $this->getRecordPropertyString('expectations');
	}

	/**
	 * Gets the attendee's background knowledge on the subject.
	 *
	 * @return	string		a description of the attendee's background knowledge (may be empty)
	 *
	 * @access	public
	 */
	function getKnowledge() {
		return $this->getRecordPropertyString('background_knowledge');
	}

	/**
	 * Gets where the attendee has heard about this seminar.
	 *
	 * @return	string		a description of where the attendee has heard about this seminar (may be empty)
	 *
	 * @access	public
	 */
	function getKnownFrom() {
		return $this->getRecordPropertyString('known_from');
	}

	/**
	 * Gets text from the "additional notes" field the attendee could fill at online registration.
	 *
	 * @return	string		additional notes on registration (may be empty)
	 *
	 * @access	public
	 */
	function getNotes() {
		return $this->getRecordPropertyString('notes');
	}

	/**
	 * Send an e-mail to the attendee, thanking him/her for registering for an event.
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page, must not be null)
	 *
	 * @access	public
	 */
	function notifyAttendee(&$plugin) {
		$this->initializeTemplate();
		$this->readSubpartsToHide($this->getConfValueString('hideFieldsInThankYouMail'), 'field_wrapper');

		$this->setMarkerContent('hello', sprintf($this->pi_getLL('email_confirmationHello'), $this->getUserName()));
		$this->setMarkerContent('type', $this->seminar->getType());
		$this->setMarkerContent('title', $this->seminar->getTitle());
		$this->setMarkerContent('uid', $this->seminar->getUid());

		if ($this->hasRecordPropertyInteger('seats')) {
			$this->setMarkerContent('seats', $this->getRecordPropertyInteger('seats'));
		} else {
			$this->readSubpartsToHide('seats', 'field_wrapper');
		}

		if ($this->seminar->hasAccreditationNumber()) {
			$this->setMarkerContent('accreditation_number', $this->seminar->getAccreditationNumber());
		} else {
			$this->readSubpartsToHide('accreditation_number', 'field_wrapper');
		}

		if ($this->seminar->hasCreditPoints()) {
			$this->setMarkerContent('credit_points', $this->seminar->getCreditPoints());
		} else {
			$this->readSubpartsToHide('credit_points', 'field_wrapper');
		}

		$this->setMarkerContent('date', $this->seminar->getDate('-'));
		$this->setMarkerContent('time', $this->seminar->getTime('-'));
		$this->setMarkerContent('place', $this->seminar->getPlaceShort());

		if ($this->seminar->hasRoom()) {
			$this->setMarkerContent('room', $this->seminar->getRoom());
		} else {
			$this->readSubpartsToHide('room', 'field_wrapper');
		}

		if ($this->getConfValueBoolean('generalPriceInMail')) {
			$this->setMarkerContent('label_price_regular', $this->pi_getLL('label_price_general'));
		}
		$this->setMarkerContent('price_regular', $this->seminar->getPriceRegular(' '));

		if ($this->seminar->hasPriceSpecial()) {
			$this->setMarkerContent('price_special', $this->seminar->getPriceSpecial(' '));
		} else {
			$this->readSubpartsToHide('price_special', 'field_wrapper');
		}

		if ($this->seminar->hasPaymentMethods()) {
			$this->setMarkerContent('message_paymentmethods', $this->pi_getLL('email_confirmationPayment'));
			$this->setMarkerContent('paymentmethods', $this->seminar->getPaymentMethodsPlain());
		} else {
			$this->readSubpartsToHide('paymentmethods', 'field_wrapper');
		}

		$this->setMarkerContent('url', $this->seminar->getDetailedViewUrl($plugin));

		$footers = $this->seminar->getOrganizersFooter();
		$this->setMarkerContent('footer', $footers[0]);

		$content = $this->substituteMarkerArrayCached('MAIL_THANKYOU');
		$froms = $this->seminar->getOrganizersNameAndEmail();

		// We use just the user's e-mail address as e-mail recipient
		// as some SMTP servers cannot handle the format
		// "John Doe <john.doe@example.com>".
		t3lib_div::plainMailEncoded(
			$this->getUserEmail(),
			$this->pi_getLL('email_confirmationSubject').': '.$this->seminar->getTitleAndDate('-'),
			$content,
			// We just use the first organizer as sender
			'From: '.$froms[0],
			'8bit'
		);

		return;
	}

	/**
	 * Send an e-mail to all organizers, notifying them of the registration
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page, must not be null)
	 *
	 * @access	public
	 */
	function notifyOrganizers(&$plugin) {
		$this->initializeTemplate();
		$this->readSubpartsToHide($this->getConfValueString('hideGeneralFieldsInNotificationMail'), 'field_wrapper');

		$this->setMarkerContent('hello', $this->pi_getLL('email_notificationHello'));
		$this->setMarkerContent('summary', $this->getTitle());

		if ($this->hasConfValueString('showSeminarFieldsInNotificationMail')) {
			$this->setMarkerContent('seminardata', $this->seminar->dumpSeminarValues($this->getConfValueString('showSeminarFieldsInNotificationMail')));
		} else {
			$this->readSubpartsToHide('seminardata', 'field_wrapper');
		}

		if ($this->hasConfValueString('showFeUserFieldsInNotificationMail')) {
			$this->setMarkerContent('feuserdata', $this->dumpUserValues($this->getConfValueString('showFeUserFieldsInNotificationMail')));
		} else {
			$this->readSubpartsToHide('feuserdata', 'field_wrapper');
		}

		if ($this->hasConfValueString('showAttendanceFieldsInNotificationMail')) {
			$this->setMarkerContent('attendancedata', $this->dumpAttendanceValues($this->getConfValueString('showAttendanceFieldsInNotificationMail')));
		} else {
			$this->readSubpartsToHide('attendancedata', 'field_wrapper');
		}

		$content = $this->substituteMarkerArrayCached('MAIL_NOTIFICATION');

		// We use just the organizer's e-mail address as e-mail recipient
		// as some SMTP servers cannot handle the format
		// "John Doe <john.doe@example.com>".
		$organizers = $this->seminar->getOrganizersEmail();
		foreach ($organizers as $currentOrganizerEmail) {
			t3lib_div::plainMailEncoded(
				$currentOrganizerEmail,
				$this->pi_getLL('email_notificationSubject').': '.$this->getTitle(),
				$content,
				// We use the attendee's e-mail as sender
				'From: '.$this->getUserNameAndEmail(),
				'8bit'
			);
		}

		return;
	}

	/**
	 * Checks if additional notifications to the organizers are necessary. In that case, the notification e-mails will be sent to all organizers.
	 *
	 * Additional notifications mails will be sent out upon the following events:
	 * - an event now has enough registrations
	 * - an event is fully booked
	 * If both things happen at the same time (minimum and maximum count of attendees are the same), only the "event is full" message will be sent.
	 *
	 * @param	object		a tx_seminars_templatehelper object (for a live page, must not be null)
	 *
	 * @access	public
	 */
	function sendAdditionalNotification(&$plugin) {
		$whichEmailToSend = '';
		$whichEmailSubject = '';

		if ($this->seminar->isFull()) {
			$whichEmailToSend = 'email_additionalNotificationIsFull';
			$whichEmailSubject = 'email_additionalNotificationIsFullSubject';
		}
		// The second check ensures that only one set of e-mails is sent to the organizers.
		elseif ($this->seminar->getMinimumAttendees() == $this->seminar->getAttendances()) {
			$whichEmailToSend = 'email_additionalNotificationEnoughRegistrations';
			$whichEmailSubject = 'email_additionalNotificationEnoughRegistrationsSubject';
		}

		// Only send an e-mail if there's a reason for it.
		if (!empty($whichEmailToSend)) {
			$this->setMarkerContent('message', $this->pi_getLL($whichEmailToSend));

			$showSeminarFields = $this->getConfValueString('showSeminarFieldsInNotificationMail');
			if (!empty($showSeminarFields)) {
				$this->setMarkerContent('seminardata', $this->seminar->dumpSeminarValues($showSeminarFields));
			} else {
				$this->readSubpartsToHide('seminardata', 'field_wrapper');
			}

			$content = $this->substituteMarkerArrayCached('MAIL_ADDITIONALNOTIFICATION');
			$subject = sprintf($this->pi_getLL($whichEmailSubject), $this->seminar->getUid(), $this->seminar->getTitleAndDate());

			// We use just the organizer's e-mail address as e-mail recipient
			// as some SMTP servers cannot handle the format
			// "John Doe <john.doe@example.com>".
			$organizers = $this->seminar->getOrganizersEmail();
			$froms = $this->seminar->getOrganizersNameAndEmail();
			foreach ($organizers as $currentOrganizerEmail) {
				t3lib_div::plainMailEncoded(
					$currentOrganizerEmail,
					$subject,
					$content,
					// We use the first organizer's e-mail as sender.
					'From: '.$froms[0],
					'8bit'
				);
			}
		}
		return;
	}

	/**
	 * Reads and initializes the templates.
	 * If this has already been called for this instance, this function does nothing.
	 *
	 * @access	private
	 */
	function initializeTemplate() {
		if (!$this->isTemplateInitialized) {
			$this->getTemplateCode();
			$this->setLabels();

			$this->isTemplateInitialized = true;
		}

		return;
	}

	/**
	 * Gets a plain text list of feuser property values (if they exist),
	 * formatted as strings (and nicely lined up) in the following format:
	 *
	 * key1: value1
	 *
	 * @param	string		comma-separated list of key names
	 *
	 * @return	string		formatted output (may be empty)
	 *
	 * @access	public
	 */
	function dumpUserValues($keysList) {
		$keys = explode(',', $keysList);

		$maxLength = 0;
		foreach ($keys as $index => $currentKey) {
			$currentKeyTrimmed = strtolower(trim($currentKey));
			// write the trimmed key back so that we don't have to trim again
			$keys[$index] = $currentKeyTrimmed;
			$maxLength = max($maxLength, strlen($currentKeyTrimmed));
		}

		$result = '';
		foreach ($keys as $currentKey) {
			$result .= str_pad($currentKey.': ', $maxLength + 2, ' ').$this->getUserData($currentKey).chr(10);
		}

		return $result;
	}

	/**
	 * Gets a plain text list of attendance (registration) property values (if they exist),
	 * formatted as strings (and nicely lined up) in the following format:
	 *
	 * key1: value1
	 *
	 * @param	string		comma-separated list of key names
	 *
	 * @return	string		formatted output (may be empty)
	 *
	 * @access	public
	 */
	function dumpAttendanceValues($keysList) {
		$keys = explode(',', $keysList);

		$maxLength = 0;
		foreach ($keys as $index => $currentKey) {
			$currentKeyTrimmed = strtolower(trim($currentKey));
			// write the trimmed key back so that we don't have to trim again
			$keys[$index] = $currentKeyTrimmed;
			$maxLength = max($maxLength, strlen($currentKeyTrimmed));
		}

		$result = '';

		foreach ($keys as $currentKey) {
			$value = $this->getRecordPropertyString($currentKey);
			$result .= str_pad($currentKey.': ', $maxLength + 2, ' ').$value.chr(10);
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']);
}

?>
