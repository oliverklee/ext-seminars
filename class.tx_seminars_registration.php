<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
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

	/**
	 * The constructor.
	 *
	 * @param	object		the seminar object (that's the seminar we would like to register for), must not be null
	 * @param	integer		the UID of the feuser who wants to sign up
	 * @param	array		associative array with the registration data the user has just entered
	 * @param	object		content object (must not be null)
	 *
	 * @access	public
	 */
	function tx_seminars_registration(&$seminar, $userUid, $registrationData, &$cObj) {
		$this->init();
		$this->tableName = $this->tableAttendances;
		$this->cObj =& $cObj;

		$this->seminar =& $seminar;

		$this->recordData = array();

		$this->recordData['seminar'] = $seminar->getUid();
		$this->recordData['user'] = $userUid;

		$this->recordData['seats'] = $registrationData['seats'];

		$this->recordData['interests'] = $registrationData['interests'];
		$this->recordData['expectations'] = $registrationData['expectations'];
		$this->recordData['background_knowledge'] = $registrationData['background_knowledge'];
		$this->recordData['known_from'] = $registrationData['known_from'];
		$this->recordData['notes'] = $registrationData['notes'];

		$this->recordData['pid'] = $this->getConfValue('attendancesPID');

		$this->createTitle();

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
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'name',
				'fe_users',
				'uid='.$this->getUser(),
				'',
				'',
				'');
		if ($dbResult) {
			$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$result = $dbResultAssoc['name'];
		} else {
			$result = '';
		}

		return $result;
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
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'email',
				'fe_users',
				'uid='.$this->getUser(),
				'',
				'',
				'');
		if ($dbResult) {
			$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$result = $dbResultAssoc['email'];
		} else {
			$result = '';
		}

		return $result;
	}

	/**
	 * Gets the attendee's name and e-mail address in the format
	 * "John Doe <john.doe@example.com>".
	 *
	 * @return	string		the attendee's name and e-mail address
	 *
	 * @access	private
	 */
	function getUserNameAndEmail() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'name, email',
				'fe_users',
				'uid='.$this->getUser(),
				'',
				'',
				'');
		if ($dbResult) {
			$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$result = $dbResultAssoc['name'].' <'.$dbResultAssoc['email'].'>';
		} else {
			$result = '';
		}

		return $result;
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
		$this->readSubpartsToHide($this->getConfValue('hideFieldsInThankYouMail'), 'field_wrapper');

		$this->setMarkerContent('hello', sprintf($this->pi_getLL('email_confirmationHello'), $this->getUserName()));
		$this->setMarkerContent('type', $this->seminar->getType());
		$this->setMarkerContent('title', $this->seminar->getTitle());

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

		if ($this->getConfValue('generalPriceInMail')) {
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
		$this->readSubpartsToHide($this->getConfValue('hideGeneralFieldsInNotificationMail'), 'field_wrapper');

		$this->setMarkerContent('hello', $this->pi_getLL('email_notificationHello'));
		$this->setMarkerContent('summary', $this->getTitle());

		$showSeminarFields = $this->getConfValue('showSeminarFieldsInNotificationMail');
		if (!empty($showSeminarFields)) {
			$this->setMarkerContent('seminardata', $this->seminar->dumpSeminarValues($showSeminarFields));
		} else {
			$this->readSubpartsToHide('seminardata', 'field_wrapper');
		}

		$showFeUserFields = $this->getConfValue('showFeUserFieldsInNotificationMail');
		if (!empty($showFeUserFields)) {
			$this->setMarkerContent('feuserdata', $this->dumpUserValues($showFeUserFields));
		} else {
			$this->readSubpartsToHide('feuserdata', 'field_wrapper');
		}

		$showAttendanceFields = $this->getConfValue('showAttendanceFieldsInNotificationMail');
		if (!empty($showAttendanceFields)) {
			$this->setMarkerContent('attendancedata', $this->dumpAttendanceValues($showAttendanceFields));
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

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				'fe_users',
				'uid='.$this->getUser(),
				'',
				'',
				'');
		if ($dbResult) {
			$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);

			foreach ($keys as $currentKey) {
				if (array_key_exists($currentKey, $dbResultAssoc)) {
					$value = $dbResultAssoc[$currentKey];
				} else {
					$value = '';
				}
				$result .= str_pad($currentKey.': ', $maxLength + 2, ' ').$value.chr(10);
			}
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
