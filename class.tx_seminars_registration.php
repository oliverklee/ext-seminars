<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

if ((float) $GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] >= 4.0) {
	require_once(PATH_t3lib.'class.t3lib_befunc.php');
	require_once(PATH_t3lib.'class.t3lib_refindex.php');
}

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
	 * This variable stores the data of the user as an array and makes it
	 * available without further database queries. It will get filled with data
	 * in the constructor.
	 */
	var $userData;

	/**
	 * An array of UIDs of lodging options associated with this record.
	 */
	var $lodgings = array();

	/**
	 * An array of UIDs of food options associated with this record.
	 */
	var $foods = array();

	/**
	 * An array of UIDs of option checkboxes associated with this record.
	 */
	var $checkboxes = array();

	/**
	 * The constructor.
	 *
	 * @param	object		content object (must not be null)
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object. If this parameter is not provided or null, setRegistrationData() needs to be called directly after construction or this object will not be usable.
	 *
	 * @access	public
	 */
	function tx_seminars_registration(&$cObj, $dbResult = null) {
		static $cachedSeminars = array();

		$this->cObj =& $cObj;
		$this->init();
		$this->tableName = $this->tableAttendances;

	 	if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->getDataFromDbResult($GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult));

			if ($this->isOk()) {
				$seminarUid = $this->recordData['seminar'];
				if (isset($cachedSeminars[$seminarUid])) {
					$this->seminar =& $cachedSeminars[$seminarUid];
				} else {
					/** Name of the seminar class in case someone subclasses it. */
					$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
					$this->seminar =& new $seminarClassname($seminarUid);
					$cachedSeminars[$seminarUid] =& $this->seminar;
				}

				// Store the user data in $this->userData.
				$this->retrieveUserData();
			}
	 	}
	}

	/**
	 * Sets this registration's data if this registration is newly created
	 * instead of from a DB query.
	 * This function must be called directly after construction or this object
	 * will not be usable.
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

		$seats = intval($registrationData['seats']);
		if ($seats < 1) {
			$seats = 1;
		}
		$this->recordData['seats'] = $seats;

		$availablePrices = $seminar->getAvailablePrices();
		// If no (available) price is selected, use the first price by default.
		$selectedPrice = (isset($registrationData['price'])
			&& $seminar->isPriceAvailable($registrationData['price']))
			? $registrationData['price'] : key($availablePrices);
		$this->recordData['price'] = $availablePrices[$selectedPrice]['caption'];

		$this->recordData['total_price'] =
			$seats * $availablePrices[$selectedPrice]['amount'];

		$this->recordData['attendees_names'] = $registrationData['attendees_names'];

		$this->recordData['kids'] = $registrationData['kids'];

		$methodOfPayment = $registrationData['method_of_payment'];
		// Auto-select the only payment method if no payment method has been
		// selected, there actually is anything to pay and only one payment
		// method is provided.
		if (!$methodOfPayment && ($this->recordData['total_price'] > 0.00)
			&& ($seminar->getNumberOfPaymentMethods() == 1)) {
				$availablePaymentMethods = explode(
					',',
					$seminar->getPaymentMethodsUids()
				);
				$methodOfPayment = $availablePaymentMethods[0];
		}
		$this->recordData['method_of_payment'] = $methodOfPayment;

		$this->recordData['account_number'] = $registrationData['account_number'];
		$this->recordData['bank_code'] = $registrationData['bank_code'];
		$this->recordData['bank_name'] = $registrationData['bank_name'];
		$this->recordData['account_owner'] = $registrationData['account_owner'];

		$this->recordData['gender'] = $registrationData['gender'];
		$this->recordData['name'] = $registrationData['name'];
		$this->recordData['address'] = $registrationData['address'];
		$this->recordData['zip'] = $registrationData['zip'];
		$this->recordData['city'] = $registrationData['city'];
		$this->recordData['country'] = $registrationData['country'];
		$this->recordData['telephone'] = $registrationData['telephone'];
		$this->recordData['email'] = $registrationData['email'];

		$this->lodgings = isset($registrationData['lodgings'])
			? $registrationData['lodgings'] : array();
		$this->recordData['lodgings'] = count($this->lodgings);

		$this->foods = isset($registrationData['foods'])
			? $registrationData['foods'] : array();
		$this->recordData['foods'] = count($this->foods);

		$this->checkboxes = isset($registrationData['checkboxes'])
			? $registrationData['checkboxes'] : array();
		$this->recordData['checkboxes'] = count($this->checkboxes);

		$this->recordData['interests'] = $registrationData['interests'];
		$this->recordData['expectations'] = $registrationData['expectations'];
		$this->recordData['background_knowledge'] = $registrationData['background_knowledge'];
		$this->recordData['accommodation'] = $registrationData['accommodation'];
		$this->recordData['food'] = $registrationData['food'];
		$this->recordData['known_from'] = $registrationData['known_from'];
		$this->recordData['notes'] = $registrationData['notes'];

		$this->recordData['pid'] = $this->seminar->hasAttendancesPid()
			? $this->seminar->getAttendancesPid()
			: $this->getConfValueInteger('attendancesPID');

		if ($this->isOk()) {
			// Store the user data in $this->userData.
			$this->retrieveUserData();
			$this->createTitle();
		}

		return;
	}

	/**
	 * Gets the number of seats that are registered with this registration.
	 * If no value is saved in the record, 1 will be returned.
	 *
	 * @return	integer		the number of seats
	 *
	 * @access	public
	 */
	function getSeats() {
		if ($this->hasRecordPropertyInteger('seats')) {
			$seats = $this->getRecordPropertyInteger('seats');
		} else {
			$seats = 1;
		}

		return $seats;
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
	 * The attendee's user data (from fe_users) will be written to
	 * $this->userData.
	 *
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
	 * Retrieves a value from this record. The return value will be an empty
	 * string if the key is not defined in $this->recordData or if it has not
	 * been filled in.
	 *
	 * If the data needs to be decoded to be readable (eg. the method of
	 * payment or the gender), this function will already return the clear text
	 * version.
	 *
	 * @param	string		the key of the data to retrieve (the key doesn't need to be trimmed)
	 *
	 * @return	string		the trimmed value retrieved from $this->recordData, may be empty
	 *
	 * @access	public
	 */
	function getRegistrationData($key) {
		$result = '';
		$trimmedKey = trim($key);

		switch ($trimmedKey) {
			case 'crdate':
				// The fallthrough is intended.
			case 'tstamp':
				$result = strftime(
					$this->getConfValueString('dateFormatYMD').' '
						.$this->getConfValueString('timeFormat'),
					$this->getRecordPropertyInteger($trimmedKey)
				);
				break;
			case 'price':
				$result = $this->getPrice();
				break;
			case 'total_price':
				$result = $this->getTotalPrice(' ');
				break;
			case 'paid':
				// The fallthrough is intended.
			case 'been_there':
				$result = ($this->getRecordPropertyBoolean($trimmedKey))
					? $this->pi_getLL('label_yes')
					: $this->pi_getLL('label_no');
				break;
			case 'datepaid':
				$result = strftime(
					$this->getConfValueString('dateFormatYMD'),
					$this->getRecordPropertyInteger($trimmedKey)
				);
				break;
			case 'method_of_payment':
				$result = $this->seminar->getSinglePaymentMethodShort(
					$this->getRecordPropertyInteger($trimmedKey)
				);
				break;
			case 'gender':
				$result = $this->getGender();
				break;
			case 'seats':
				$result = $this->getSeats();
				break;
			case 'lodgings':
				$result = $this->getLodgings();
				break;
			case 'foods':
				$result = $this->getFoods();
				break;
			case 'checkboxes':
				$result = $this->getCheckboxes();
				break;
			default:
				$result = $this->getRecordPropertyString($trimmedKey);
				break;
		}

		return $result;
	}

	/**
	 * Retrieves a value out of the userData array. The return value will be an
	 * empty string if the key is not defined in the $this->userData array.
	 *
	 * If the data needs to be decoded to be readable (eg. the gender, the date
	 * of birth or the status), this function will already return the clear text
	 * version.
	 *
	 * @param	string		the key of the data to retrieve (the key doesn't need to be trimmed)
	 *
	 * @return	string		the trimmed value retrieved from $this->userData, may be empty
	 *
	 * @access	public
	 */
	function getUserData($key) {
		$result = '';
		$trimmedKey = trim($key);

		if (is_array($this->userData) && !empty($trimmedKey)) {
			if (array_key_exists($trimmedKey, $this->userData)) {
				$rawData = trim($this->userData[$trimmedKey]);

				// deal with special cases
				switch ($trimmedKey) {
					case 'gender':
						$result = $this->pi_getLL('label_gender.I.'.$rawData);
						break;
					case 'status':
						if ($rawData) {
							$result = $this->pi_getLL('label_status.I.'.$rawData);
						}
						break;
					case 'wheelchair':
						$result = ($rawData)
							? $this->pi_getLL('label_yes')
							: $this->pi_getLL('label_no');
						break;
					case 'crdate':
						// The fallthrough is intended.
					case 'tstamp':
						$result = strftime(
							$this->getConfValueString('dateFormatYMD').' '
								.$this->getConfValueString('timeFormat'),
							$rawData
						);
						break;
					case 'date_of_birth':
						$result = strftime(
							$this->getConfValueString('dateFormatYMD'),
							$rawData
						);
						break;
					default:
						$result = $rawData;
						break;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns values out of the userData array, nicely formatted as safe
	 * HTML and with the e-mail address as a mailto: link.
	 * If more than one key is provided, the return values are separated
	 * by a comma and a space.
	 * Empty values will be removed from the output.
	 *
	 * @param	string		comma-separated list of keys to retrieve
	 * @param	object		a tx_seminars_templatehelper object (for a live page, must not be null)
	 *
	 * @return	string		the values retrieved from $this->userData, may be empty
	 *
	 * @access	public
	 */
	function getUserDataAsHtml($keys, &$plugin) {
		$singleKeys = explode(',', $keys);
		$singleValues = array();

		foreach ($singleKeys as $currentKey) {
			$rawValue = $this->getUserData($currentKey);
			if (!empty($rawValue)) {
				switch (trim($currentKey)) {
					case 'email':
						$singleValues[$currentKey] = $plugin->cObj->mailto_makelinks('mailto:'.$rawValue, array());
						break;
					default:
						$singleValues[$currentKey] = htmlspecialchars($rawValue);
						break;
				}
			}
		}

		// And now: Everthing separated by a comma and a space!
		return implode(', ', $singleValues);
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
	 * Gets the seminar to which this registration belongs.
	 *
	 * @return	object		the seminar to which this registration belongs
	 *
	 * @access	public
	 */
	function getSeminarObject() {
		return $this->seminar;
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
	 * Gets the saved price category name and its single price, all in one long
	 * string.
	 *
	 * @return	string		the saved price category name and its single price or an empty string if no price had been saved
	 *
	 * @access	public
	 */
	function getPrice() {
		return $this->getRecordPropertyString('price');
	}

	/**
	 * Gets the saved total price and the currency.
	 * An empty string will be returned if no total price could be calculated.
	 *
	 * @param	string		character(s) used to separate the price from the currency
	 *
	 * @return	string		the total price and the currency or an empty string if no total price could be calculated
	 *
	 * @access	public
	 */
	function getTotalPrice($space = '&nbsp;') {
		$result = '';
		$totalPrice = $this->getRecordPropertyDecimal('total_price');
		$currency = $this->getConfValueString('currency');
		if ($totalPrice != '0.00') {
			$result = $this->seminar->formatPrice($totalPrice).$space.$currency;
		}

		return $result;
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
		$this->setMarkerContent('event_type', $this->seminar->getEventType());
		$this->setMarkerContent('title', $this->seminar->getTitle());
		$this->setMarkerContent('uid', $this->seminar->getUid());

		if ($this->hasRecordPropertyInteger('seats')) {
			$this->setMarkerContent('seats', $this->getRecordPropertyInteger('seats'));
		} else {
			$this->readSubpartsToHide('seats', 'field_wrapper');
		}

		if ($this->hasRecordPropertyString('attendees_names')) {
			$this->setMarkerContent('attendees_names', $this->getRecordPropertyString('attendees_names'));
		} else {
			$this->readSubpartsToHide('attendees_names', 'field_wrapper');
		}

		if ($this->hasLodgings()) {
			$this->setMarkerContent('lodgings', $this->getLodgings());
		} else {
			$this->readSubpartsToHide('lodgings', 'field_wrapper');
		}

		if ($this->hasFoods()) {
			$this->setMarkerContent('foods', $this->getFoods());
		} else {
			$this->readSubpartsToHide('foods', 'field_wrapper');
		}

		if ($this->hasCheckboxes()) {
			$this->setMarkerContent('checkboxes', $this->getCheckboxes());
		} else {
			$this->readSubpartsToHide('checkboxes', 'field_wrapper');
		}

		if ($this->hasRecordPropertyInteger('kids')) {
			$this->setMarkerContent('kids', $this->getRecordPropertyInteger('kids'));
		} else {
			$this->readSubpartsToHide('kids', 'field_wrapper');
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

		if ($this->seminar->hasAdditionalTimesAndPlaces()) {
			$this->setMarkerContent(
				'additional_times_places',
				$this->seminar->getAdditionalTimesAndPlacesRaw()
			);
		} else {
			$this->readSubpartsToHide('additional_times_places', 'field_wrapper');
		}

		if ($this->hasRecordPropertyString('price')) {
			$this->setMarkerContent('price', $this->getPrice());
		} else {
			$this->readSubpartsToHide('price', 'field_wrapper');
		}

		if ($this->hasRecordPropertyDecimal('total_price')) {
			$this->setMarkerContent('total_price', $this->getTotalPrice(' '));
		} else {
			$this->readSubpartsToHide('total_price', 'field_wrapper');
		}

		// We don't need to check $this->seminar->hasPaymentMethods() here as
		// method_of_payment can only be set (using the registration form) if
		// the event has at least one payment method.
		if ($this->hasRecordPropertyInteger('method_of_payment')) {
			$this->setMarkerContent(
				'paymentmethod',
				$this->seminar->getSinglePaymentMethodPlain(
					$this->getRecordPropertyInteger('method_of_payment')
				)
			);
		} else {
			$this->readSubpartsToHide('paymentmethod', 'field_wrapper');
		}

		$this->setMarkerContent('billing_address', $this->getBillingAddress());

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
			'quoted-printable',
			$this->getConfValueString('charsetForEMails')
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
				// We use the attendee's e-mail as sender.
				'From: '.$this->getUserNameAndEmail(),
				'quoted-printable',
				$this->getConfValueString('charsetForEMails')
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
			$subject = sprintf($this->pi_getLL($whichEmailSubject), $this->seminar->getUid(), $this->seminar->getTitleAndDate('-'));

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
					'quoted-printable',
					$this->getConfValueString('charsetForEMails')
				);
			}
		}
		return;
	}

	/**
	 * Reads and initializes the templates.
	 * If this has already been called for this instance, this function does nothing.
	 *
	 * This function will read the template file as it is set in the TypoScript
	 * setup. If there is a template file set in the flexform of pi1, this will
	 * be ignored!
	 *
	 * @access	private
	 */
	function initializeTemplate() {
		if (!$this->isTemplateInitialized) {
			$this->getTemplateCode(true);
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
			$result .= str_pad($currentKey.': ', $maxLength + 2, ' ').$this->getUserData($currentKey).LF;
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
			$value = $this->getRegistrationData($currentKey);
			$result .= str_pad($currentKey.': ', $maxLength + 2, ' ').$value.LF;
		}

		return $result;
	}

	/**
	 * Gets the billing address, formatted as plain text.
	 *
	 * @return	string		the billing address
	 *
	 * @access	protected
	 */
	function getBillingAddress() {
		/**
		 * the keys of the corresponding fields and whether to add a LF after
		 * the entry (instead of just a space)
		 */
		$billingAddressFields = array(
			'gender' => false,
			'name' => true,
			'address' => true,
			'zip' => false,
			'city' => true,
			'country' => true,
			'telephone' => true,
			'email' => true
		);

		$result = '';

		foreach ($billingAddressFields as $key => $useLf) {
			if ($this->hasRecordPropertyString($key)) {
				// Add labels before the phone number and the e-mail address.
				if (($key == 'telephone') || ($key == 'email')) {
					$result .= $this->pi_getLL('label_'.$key).': ';
				}
				$result .= $this->getRegistrationData($key);
				if ($useLf) {
					$result .= LF;
				} else {
					$result .= ' ';
				}
			}
		}

		return $result;
	}

	/**
	 * Retrieves the localized string corresponding to the key in the "gender"
	 * field.
	 *
	 * @return	string		the localized gender as entered for the billing address (Mr. or Mrs.)
	 *
	 * @access	public
	 */
	function getGender() {
		return $this->pi_getLL('label_gender.I.'
			.$this->getRecordPropertyInteger('gender'));
	}

	/**
	 * Checks whether there are any lodging options referenced by this record.
	 *
	 * @return	boolean		true if at least one lodging option is referenced by this record, false otherwise
	 *
	 * @access	public
	 */
	function hasLodgings() {
		return $this->hasRecordPropertyInteger('lodgings');
	}

	/**
	 * Gets the selected lodging options separated by CRLF. If there is no
	 * lodging option selected, this function will return an empty string.
	 *
	 * @return	string		the titles of the selected loding options separated by CRLF or an empty string if no lodging option is selected
	 *
	 * @access	public
	 */
	function getLodgings() {
		$result = '';

		if ($this->hasLodgings()) {
			$result = $this->getMmRecords(
				$this->tableLodgings,
				$this->tableAttendancesLodgingsMM
			);
		}

		return $result;
	}

	/**
	 * Checks whether there are any food options referenced by this record.
	 *
	 * @return	boolean		true if at least one food option is referenced by this record, false otherwise
	 *
	 * @access	public
	 */
	function hasFoods() {
		return $this->hasRecordPropertyInteger('foods');
	}

	/**
	 * Gets the selected food options separated by CRLF. If there is no
	 * food option selected, this function will return an empty string.
	 *
	 * @return	string		the titles of the selected loding options separated by CRLF or an empty string if no food option is selected
	 *
	 * @access	public
	 */
	function getFoods() {
		$result = '';

		if ($this->hasFoods()) {
			$result = $this->getMmRecords(
				$this->tableFoods,
				$this->tableAttendancesFoodsMM
			);
		}

		return $result;
	}

	/**
	 * Checks whether any option checkboxes are referenced by this record.
	 *
	 * @return	boolean		true if at least one option checkbox is referenced by this record, false otherwise
	 *
	 * @access	public
	 */
	function hasCheckboxes() {
		return $this->hasRecordPropertyInteger('checkboxes');
	}

	/**
	 * Gets the selected option checkboxes separated by CRLF. If no option
	 * checkbox is selected, this function will return an empty string.
	 *
	 * @return	string		the titles of the selected option checkboxes separated by CRLF or an empty string if no option checkbox is selected
	 *
	 * @access	public
	 */
	function getCheckboxes() {
		$result = '';

		if ($this->hasCheckboxes()) {
			$result = $this->getMmRecords(
				$this->tableCheckboxes,
				$this->tableAttendancesCheckboxesMM
			);
		}

		return $result;
	}

	/**
	 * Gets a CRLF-separated list of the titles of records referenced by this
	 * record.
	 *
	 * @param	string		the name of the foreign table (must not be empty), must have the fields uid and title
	 * @param	string		the name of the m:m table, having the fields uid_local, uid_foreign and sorting, must not be empty
	 *
	 * @return	string		the titles of the referenced records separated by CRLF, might be empty if no records are referenced
	 *
	 * @access	private
	 */
	function getMmRecords($foreignTable, $mmTable) {
		$result = '';

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, sorting',
			$foreignTable.', '.$mmTable,
			'uid_local='.$this->getUid().' AND uid_foreign=uid'
				.$this->enableFields($foreignTable),
			'',
			'sorting'
		);

		if ($dbResult) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				if (!empty($result)) {
					$result .= CRLF;
				}
				$result .= $row['title'];
			}
		}

		return $result;
	}

	/**
	 * Writes this record to the DB and adds any needed m:n records.
	 *
	 * This function actually calles the same method in the parent class
	 * (which saves the record to the DB) and then adds any necessary m:n
	 * relations.
	 *
	 * The UID of the parent page must be set in $this->recordData['pid'].
	 * (otherwise the record will be created in the root page).
	 *
	 * @return	boolean		true if everything went OK, false otherwise
	 *
	 * @access	protected
	 */
	function commitToDb() {
		$result = parent::commitToDb();

		if ($result) {
			$this->recordData['uid'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
			if ($this->recordData['uid']) {
				$this->createMmRecords(
					$this->tableAttendancesLodgingsMM,
					$this->lodgings
				);
				$this->createMmRecords(
					$this->tableAttendancesFoodsMM,
					$this->foods
				);
				$this->createMmRecords(
					$this->tableAttendancesCheckboxesMM,
					$this->checkboxes
				);
			}

			if ((float) $GLOBALS['TYPO3_CONF_VARS']['SYS']['compat_version'] >= 4.0) {
				// update the reference index
				$referenceIndex = t3lib_div::makeInstance('t3lib_refindex');
				$referenceIndex->updateRefIndexTable($this->tableAttendances, $this->getUid());
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']);
}

?>
