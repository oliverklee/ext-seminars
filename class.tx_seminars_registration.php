<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'class.t3lib_refindex.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'tx_seminars_registration' for the 'seminars' extension.
 *
 * This class represents a registration/attendance.
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registration extends tx_seminars_objectfromdb {
	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = SEMINARS_TABLE_ATTENDANCES;

	/** Same as class name */
	public $prefixId = 'tx_seminars_registration';
	/**  Path to this script relative to the extension dir. */
	public $scriptRelPath = 'class.tx_seminars_registration.php';

	/**
	 * @var tx_seminars_seminar the event to which this registration relates
	 */
	private $seminar = null;

	/** whether we have already initialized the templates (which is done lazily) */
	private $isTemplateInitialized = false;

	/**
	 * @var boolean whether the user data has already been retrieved
	 */
	private $userDataHasBeenRetrieved = false;

	/**
	 * This variable stores the data of the user as an array and makes it
	 * available without further database queries. It will get filled with data
	 * in the constructor.
	 */
	private $userData;

	/**
	 * An array of UIDs of lodging options associated with this record.
	 */
	protected $lodgings = array();

	/**
	 * An array of UIDs of food options associated with this record.
	 */
	protected $foods = array();

	/**
	 * An array of UIDs of option checkboxes associated with this record.
	 */
	protected $checkboxes = array();

	/**
	 * An array of cached seminar objects with the seminar UIDs as keys and the
	 * objects as values.
	 */
	private static $cachedSeminars = array();

	/**
	 * @var integer the different options for the format of attendee e-mails
	 */
	const SEND_TEXT_MAIL = 0,
		SEND_HTML_MAIL = 1,
		SEND_USER_MAIL = 2;

	/**
	 * The constructor.
	 *
	 * @param object content object
	 * @param pointer MySQL result pointer (of SELECT query)/DBAL object.
	 *                If this parameter is not provided or false,
	 *                setRegistrationData() needs to be called directly
	 *                after construction or this object will not be usable.
	 */
	public function __construct(tslib_cObj $cObj, $dbResult = false) {
		$this->cObj = $cObj;
		$this->init();

	 	if (!$dbResult) {
	 		return;
	 	}

	 	$data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if ($data) {
			$this->getDataFromDbResult($data);
		}
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->seminar);
		parent::__destruct();
	}

	/**
	 * Purges our cached seminars array.
	 *
	 * This function is intended for testing purposes only.
	 */
	public static function purgeCachedSeminars() {
		self::$cachedSeminars = array();
	}

	/**
	 * Sets this registration's data if this registration is newly created
	 * instead of from a DB query.
	 * This function must be called directly after construction or this object
	 * will not be usable.
	 *
	 * @param tx_seminars_seminar the seminar object (that's the seminar
	 *                            we would like to register for)
	 * @param integer the UID of the FE user who wants to sign up
	 * @param array associative array with the registration data the user has
	 *              just entered, may be empty
	 */
	public function setRegistrationData(
		tx_seminars_seminar $seminar, $userUid, array $registrationData
	) {
		$this->seminar = $seminar;

		$this->recordData = array();

		$this->recordData['seminar'] = $seminar->getUid();
		$this->recordData['user'] = $userUid;
		$this->recordData['registration_queue']
			= (!$seminar->hasVacancies()) ? 1 : 0;

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
				$availablePaymentMethods = t3lib_div::trimExplode(
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

		$this->lodgings = (isset($registrationData['lodgings'])
			&& is_array($registrationData['lodgings']))
			? $registrationData['lodgings'] : array();
		$this->recordData['lodgings'] = count($this->lodgings);

		$this->foods = (isset($registrationData['foods'])
			&& is_array($registrationData['foods']))
			? $registrationData['foods'] : array();
		$this->recordData['foods'] = count($this->foods);

		$this->checkboxes = (isset($registrationData['checkboxes'])
			&& is_array($registrationData['checkboxes']))
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
			// Stores the user data in $this->userData.
			$this->retrieveUserData();
			$this->createTitle();
		}
	}

	/**
	 * Gets the number of seats that are registered with this registration.
	 * If no value is saved in the record, 1 will be returned.
	 *
	 * @return integer the number of seats
	 */
	public function getSeats() {
		if ($this->hasSeats()) {
			$seats = $this->getRecordPropertyInteger('seats');
		} else {
			$seats = 1;
		}

		return $seats;
	}

	/**
	 * Sets our number of seats.
	 *
	 * @param integer the number of seats, must be >= 0
	 */
	public function setSeats($seats) {
		if ($seats < 0) {
			throw new Exception('The parameter $seats must be >= 0.');
		}

		$this->setRecordPropertyInteger('seats', $seats);
	}

	/**
	 * Returns whether this registration has seats.
	 *
	 * @return boolean true if this registration has seats, false otherwise
	 */
	public function hasSeats() {
		return $this->hasRecordPropertyInteger('seats');
	}

	/**
	 * Creates our title and writes it to $this->title.
	 *
	 * The title is constructed like this:
	 *   Name of Attendee / Title of Seminar seminardate
	 */
	private function createTitle() {
		$this->recordData['title'] = $this->getUserName() .
			' / ' . $this->getSeminarObject()->getTitle() .
			', ' . $this->getSeminarObject()->getDate('-');
	}

 	/**
	 * Gets the complete FE user data as an array.
	 * The attendee's user data (from fe_users) will be written to
	 * $this->userData.
	 *
	 * $this->userData will be null if retrieving the user data fails.
	 */
	private function retrieveUserData() {
		$uid = $this->getUser();
		if ($uid == 0) {
			$this->userData = null;
			return;
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'fe_users',
			'uid=' . $uid . tx_oelib_db::enableFields('fe_users')
		);
		if (!$dbResult) {
			throw new Exception(DATABASE_QUERY_ERROR);
		}
		$userData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$userData) {
			throw new tx_oelib_Exception_NotFound(
				'The FE user with the UID ' . $uid . ' could not be retrieved.'
			);
		}

		$this->setUserData($userData);
	}

	/**
	 * Sets the data of the FE user of this registration.
	 *
	 * @param array data of the front-end user, must not be empty
	 */
	protected function setUserData(array $userData) {
		if (empty($userData)) {
			throw new Exception('$userData must not be empty.');
		}

		$this->userData = $userData;
		$this->userDataHasBeenRetrieved = true;
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
	 * @param string the key of the data to retrieve (the key doesn't need to be
	 *               trimmed)
	 *
	 * @return string the trimmed value retrieved from $this->recordData, may be
	 *                empty
	 */
	public function getRegistrationData($key) {
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
			case 'uid':
				$result = $this->getUid();
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
					? $this->translate('label_yes')
					: $this->translate('label_no');
				break;
			case 'datepaid':
				$result = strftime(
					$this->getConfValueString('dateFormatYMD'),
					$this->getRecordPropertyInteger($trimmedKey)
				);
				break;
			case 'method_of_payment':
				$result = $this->getSeminarObject()->getSinglePaymentMethodShort(
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

		return (string) $result;
	}

	/**
	 * Retrieves a value out of the userData array. The return value will be an
	 * empty string if the key is not defined in the $this->userData array.
	 *
	 * If the data needs to be decoded to be readable (eg. the gender, the date
	 * of birth or the status), this function will already return the clear text
	 * version.
	 *
	 * @param string the key of the data to retrieve, may contain leading
	 *               or trailing spaces, must not be empty
	 *
	 * @return string the trimmed value retrieved from $this->userData,
	 *                may be empty
	 */
	public function getUserData($key) {
		if (!$this->userDataHasBeenRetrieved) {
			$this->retrieveUserData();
		}
		$result = '';
		$trimmedKey = trim($key);

		if (is_array($this->userData) && !empty($trimmedKey)) {
			if (array_key_exists($trimmedKey, $this->userData)) {
				$rawData = trim($this->userData[$trimmedKey]);

				// deal with special cases
				switch ($trimmedKey) {
					case 'gender':
						$result = $this->translate('label_gender.I.'.$rawData);
						break;
					case 'status':
						if ($rawData) {
							$result = $this->translate('label_status.I.'.$rawData);
						}
						break;
					case 'wheelchair':
						$result = ($rawData)
							? $this->translate('label_yes')
							: $this->translate('label_no');
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

		return (string) $result;
	}

	/**
	 * Returns values out of the userData array, nicely formatted as safe
	 * HTML and with the e-mail address as a mailto: link.
	 * If more than one key is provided, the return values are separated
	 * by a comma and a space.
	 * Empty values will be removed from the output.
	 *
	 * @param string comma-separated list of keys to retrieve
	 * @param object a tslib_pibase object for a live page
	 *
	 * @return string the values retrieved from $this->userData, may be empty
	 */
	public function getUserDataAsHtml($keys, tslib_pibase $plugin) {
		$singleKeys = t3lib_div::trimExplode(',', $keys, true);
		$singleValues = array();

		foreach ($singleKeys as $currentKey) {
			$rawValue = $this->getUserData($currentKey);
			if (!empty($rawValue)) {
				switch ($currentKey) {
					case 'email':
						$singleValues[$currentKey]
							= $plugin->cObj->mailto_makelinks(
								'mailto:'.$rawValue,
								array()
							);
						break;
					default:
						$singleValues[$currentKey] = htmlspecialchars($rawValue);
						break;
				}
			}
		}

		// And now: Everything separated by a comma and a space!
		return implode(', ', $singleValues);
	}

	/**
	 * Gets the attendee's UID.
	 *
	 * @return integer the attendee's feuser uid
	 */
	public function getUser() {
		return $this->getRecordPropertyInteger('user');
	}

	/**
	 * Gets the attendee's (real) name
	 *
	 * @return string the attendee's name
	 */
	public function getUserName() {
		return $this->getUserData('name');
	}

	/**
	 * Gets the attendee's e-mail address in the format
	 * "john.doe@example.com".
	 *
	 * @return string the attendee's e-mail address
	 */
	private function getUserEmail() {
		return $this->getUserData('email');
	}

	/**
	 * Gets the attendee's name and e-mail address in the format
	 * '"John Doe" <john.doe@example.com>'.
	 *
	 * @return string the attendee's name and e-mail address
	 */
	public function getUserNameAndEmail() {
		return '"'.$this->getUserData('name').'" <'.$this->getUserData('email').'>';
	}

	/**
	 * Returns the front-end user of the registration.
	 *
	 * @return tx_oelib_Model_FrontEndUser the front-end user of the registration
	 */
	public function getFrontEndUser() {
		return tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')->find(
			$this->getUser()
		);
	}

	/**
	 * Returns whether the registration has an existing front-end user.
	 *
	 * @return boolean true if the registration has an existing front-end user,
	 *                 false otherwise
	 */
	public function hasExistingFrontEndUser() {
		if ($this->getUser() <= 0) {
			return false;
		}

		return tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')->
			existsModel(
				$this->getUser()
			);
	}

	/**
	 * Sets the front-end user UID of the registration.
	 *
	 * @param integer the front-end user UID of the attendee, must be > 0
	 */
	public function setFrontEndUserUID($frontEndUserUID) {
		$this->setRecordPropertyInteger('user', $frontEndUserUID);
	}

	/**
	 * Gets the seminar's UID.
	 *
	 * @return integer the seminar's UID
	 */
	public function getSeminar() {
		return $this->getRecordPropertyInteger('seminar');
	}

	/**
	 * Gets the seminar to which this registration belongs.
	 *
	 * @return object the seminar to which this registration belongs
	 */
	public function getSeminarObject() {
		if (!$this->seminar && $this->isOk()) {
			$seminarUid = $this->recordData['seminar'];
			if (isset(self::$cachedSeminars[$seminarUid])) {
				$this->seminar = self::$cachedSeminars[$seminarUid];
			} else {
				$seminarClassname = t3lib_div::makeInstanceClassName(
					'tx_seminars_seminar'
				);
				$this->seminar = new $seminarClassname($seminarUid);
				self::$cachedSeminars[$seminarUid] = $this->seminar;
			}
		}

		return $this->seminar;
	}

	/**
	 * Gets whether this attendance has already been paid for.
	 *
	 * @return boolean whether this attendance has already been paid for
	 */
	public function isPaid() {
		return $this->getRecordPropertyBoolean('paid');
	}

	/**
	 * Sets whether this registration has already been paid for.
	 *
	 * @param boolean whether this attendance has already been paid for
	 */
	public function setIsPaid($isPaid) {
		$this->setRecordPropertyBoolean('paid', $isPaid);
	}

	/**
	 * Gets the attendee's special interests in the subject.
	 *
	 * @return string a description of the attendee's special interests
	 *                (may be empty)
	 */
	public function getInterests() {
		return $this->getRecordPropertyString('interests');
	}

	/**
	 * Gets the attendee's expectations for the event.
	 *
	 * @return string a description of the attendee's expectations for the
	 *                event (may be empty)
	 */
	public function getExpectations() {
		return $this->getRecordPropertyString('expectations');
	}

	/**
	 * Gets the attendee's background knowledge on the subject.
	 *
	 * @return string a description of the attendee's background knowledge
	 *                (may be empty)
	 */
	public function getKnowledge() {
		return $this->getRecordPropertyString('background_knowledge');
	}

	/**
	 * Gets where the attendee has heard about this event.
	 *
	 * @return string a description of where the attendee has heard about
	 *                this event (may be empty)
	 */
	public function getKnownFrom() {
		return $this->getRecordPropertyString('known_from');
	}

	/**
	 * Gets text from the "additional notes" field the attendee could fill at
	 * online registration.
	 *
	 * @return string additional notes on registration (may be empty)
	 */
	public function getNotes() {
		return $this->getRecordPropertyString('notes');
	}

	/**
	 * Gets the saved price category name and its single price, all in one long
	 * string.
	 *
	 * @return string the saved price category name and its single price
	 *                or an empty string if no price had been saved
	 */
	public function getPrice() {
		return $this->getRecordPropertyString('price');
	}

	/**
	 * Gets the saved total price and the currency.
	 * An empty string will be returned if no total price could be calculated.
	 *
	 * @param string character(s) used to separate the price from the currency
	 *
	 * @return string the total price and the currency or an empty string
	 *                if no total price could be calculated
	 */
	public function getTotalPrice($space = '&nbsp;') {
		$result = '';
		$totalPrice = $this->getRecordPropertyDecimal('total_price');
		$currency = $this->getConfValueString('currency');
		if ($totalPrice != '0.00') {
			$result = $this->getSeminarObject()->formatPrice($totalPrice) .
				$space . $currency;
		}

		return $result;
	}

	/**
	 * Sends an e-mail to the attendee with a message concerning his/her
	 * registration or unregistration.
	 *
	 * @param tslib_pibase a live page
	 * @param string prefix for the locallang key of the localized hello
	 *               and subject string, allowed values are:
	 *               - confirmation
	 *               - confirmationOnUnregistration
	 *               - confirmationOnRegistrationForQueue
	 *               - confirmationOnQueueUpdate
	 *               In the following the parameter is prefixed with
	 *               "email_" and postfixed with "Hello" or "Subject".
	 */
	public function notifyAttendee(
		tslib_pibase $plugin, $helloSubjectPrefix = 'confirmation'
	) {
		if (!$this->getConfValueBoolean('send'.ucfirst($helloSubjectPrefix))) {
			return;
		}

		if (!$this->getSeminarObject()->hasOrganizers()) {
			return;
		}

		if (!$this->hasExistingFrontEndUser()) {
			return;
		}

		$event = $this->getSeminarObject();

		$eMailNotification = t3lib_div::makeInstance('tx_oelib_Mail');
		$eMailNotification->addRecipient($this->getFrontEndUser());
		$eMailNotification->setSender($event->getOrganizerBag()->current());
		$eMailNotification->setSubject(
			$this->translate('email_' . $helloSubjectPrefix . 'Subject') . ': ' .
				$event->getTitleAndDate('-')
		);

		$this->initializeTemplate();

		$mailFormat = tx_oelib_configurationProxy::getInstance('seminars')
			->getConfigurationValueInteger('eMailFormatForAttendees');
		if (($mailFormat == self::SEND_HTML_MAIL)
			|| (($mailFormat == self::SEND_USER_MAIL)
				&& $this->getFrontEndUser()->wantsHtmlEMail())
		) {
			$eMailNotification->setCssFile(
				$this->getConfValueString('cssFileForAttendeeMail')
			);
			$eMailNotification->setHTMLMessage(
				$this->buildEmailContent($plugin, $helloSubjectPrefix, true)
			);
		}

		$eMailNotification->setMessage(
			$this->buildEmailContent($plugin, $helloSubjectPrefix)
		);

		tx_oelib_mailerFactory::getInstance()->getMailer()->send(
			$eMailNotification
		);

		$eMailNotification->__destruct();
	}

	/**
	 * Sends an e-mail to all organizers with a message about a registration or
	 * unregistration.
	 *
	 * @param string prefix for the locallang key of the localized hello
	 *               and subject string, allowed values are:
	 *               - notification
	 *               - notificationOnUnregistration
	 *               - notificationOnRegistrationForQueue
	 *               - notificationOnQueueUpdate
	 *               In the following the parameter is prefixed with
	 *               "email_" and postfixed with "Hello" or "Subject".
	 */
	public function notifyOrganizers($helloSubjectPrefix = 'notification') {
		if (!$this->getConfValueBoolean('send' . ucfirst($helloSubjectPrefix))) {
			return;
		}

		if (!$this->getSeminarObject()->hasOrganizers()) {
			return;
		}

		if (!$this->hasExistingFrontEndUser()) {
			return;
		}

		$eMailNotification = t3lib_div::makeInstance('tx_oelib_Mail');
		$eMailNotification->setSender($this->getFrontEndUser());

		foreach ($this->getSeminarObject()->getOrganizerBag() as $organizer) {
			$eMailNotification->addRecipient($organizer);
		}

		$eMailNotification->setSubject(
			$this->translate('email_' . $helloSubjectPrefix . 'Subject') .
				': ' . $this->getTitle()
		);

		$this->initializeTemplate();
		$this->hideSubparts(
			$this->getConfValueString('hideFieldsInNotificationMail'),
			'field_wrapper'
		);

		$this->setMarker(
			'hello',
			$this->translate('email_' . $helloSubjectPrefix . 'Hello')
		);
		$this->setMarker('summary', $this->getTitle());

		if ($this->hasConfValueString('showSeminarFieldsInNotificationMail')) {
			$this->setMarker(
				'seminardata',
				$this->getSeminarObject()->dumpSeminarValues(
					$this->getConfValueString(
						'showSeminarFieldsInNotificationMail'
					)
				)
			);
		} else {
			$this->hideSubparts('seminardata', 'field_wrapper');
		}

		if ($this->hasConfValueString('showFeUserFieldsInNotificationMail')) {
			$this->setMarker(
				'feuserdata',
				$this->dumpUserValues(
					$this->getConfValueString('showFeUserFieldsInNotificationMail')
				)
			);
		} else {
			$this->hideSubparts('feuserdata', 'field_wrapper');
		}

		if ($this->hasConfValueString('showAttendanceFieldsInNotificationMail')) {
			$this->setMarker(
				'attendancedata',
				$this->dumpAttendanceValues(
					$this->getConfValueString(
						'showAttendanceFieldsInNotificationMail'
					)
				)
			);
		} else {
			$this->hideSubparts('attendancedata', 'field_wrapper');
		}

		$eMailNotification->setMessage($this->getSubpart('MAIL_NOTIFICATION'));

		tx_oelib_mailerFactory::getInstance()->getMailer()->send(
			$eMailNotification
		);

		$eMailNotification->__destruct();
	}

	/**
	 * Checks if additional notifications to the organizers are necessary.
	 * In that case, the notification e-mails will be sent to all organizers.
	 *
	 * Additional notifications mails will be sent out upon the following events:
	 * - an event now has enough registrations
	 * - an event is fully booked
	 * If both things happen at the same time (minimum and maximum count of
	 * attendees are the same), only the "event is full" message will be sent.
	 */
	public function sendAdditionalNotification() {
		if ($this->isOnRegistrationQueue()) {
			return;
		}

		$emailReason = $this->getReasonForNotification();
		if ($emailReason == '') {
			return;
		}

		$event = $this->getSeminarObject();
		$eMail = t3lib_div::makeInstance('tx_oelib_Mail');

		$eMail->setSender($event->getOrganizerBag()->current());
		$eMail->setMessage($this->getMessageForNotification($emailReason));
		$eMail->setSubject(sprintf(
			$this->translate(
				'email_additionalNotification' . $emailReason . 'Subject'
			),
			$event->getUid(),
			$event->getTitleAndDate('-')
		));

		foreach ($event->getOrganizerBag() as $organizer) {
			$eMail->addRecipient($organizer);
		}

		tx_oelib_mailerFactory::getInstance()->getMailer()->send($eMail);

		$eMail->__destruct();
	}

	/**
	 * Returns the topic for the additional notification e-mail.
	 *
	 * @return string "EnoughRegistrations" if the event has enough attendances,
	 *                "IsFull" if the event is fully booked, otherwise an empty
	 *                string
	 */
	private function getReasonForNotification() {
		$result = '';

		$event = $this->getSeminarObject();
		if ($event->isFull()) {
			$result = 'IsFull';
		// Using "==" instead of ">=" ensures that only one set of e-mails is
		// sent to the organizers.
		// This also ensures that no e-mail is send when minAttendances is 0
		// since this function is only called when at least one registration
		// is present.
		} elseif ($event->getAttendances() == $event->getAttendancesMin()) {
			$result = 'EnoughRegistrations';
		}

		return $result;
	}

	/**
	 * Returns the message for an e-mail according to the reason
	 * $reasonForNotification provided.
	 *
	 * @param string reason for the notification, must be either "IsFull" or
	 *               "EnoughRegistrations", must not be empty
	 */
	private function getMessageForNotification($reasonForNotification) {
		$localllangKey = 'email_additionalNotification' . $reasonForNotification;
		$this->initializeTemplate();

		$this->setMarker('message', $this->translate($localllangKey));
		$showSeminarFields = $this->getConfValueString(
			'showSeminarFieldsInNotificationMail'
		);
		if ($showSeminarFields != '') {
			$this->setMarker(
				'seminardata',
				$this->getSeminarObject()->dumpSeminarValues($showSeminarFields)
			);
		} else {
			$this->hideSubparts('seminardata', 'field_wrapper');
		}

		return $this->getSubpart('MAIL_ADDITIONALNOTIFICATION');
	}

	/**
	 * Reads and initializes the templates.
	 * If this has already been called for this instance, this function does
	 * nothing.
	 *
	 * This function will read the template file as it is set in the TypoScript
	 * setup. If there is a template file set in the flexform of pi1, this will
	 * be ignored!
	 */
	private function initializeTemplate() {
		if (!$this->isTemplateInitialized) {
			$this->getTemplateCode(true);
			$this->setLabels();

			$this->isTemplateInitialized = true;
		}
	}

	/**
	 * Gets a plain text list of feuser property values (if they exist),
	 * formatted as strings (and nicely lined up) in the following format:
	 *
	 * key1: value1
	 *
	 * @param string comma-separated list of key names
	 *
	 * @return string formatted output (may be empty)
	 */
	public function dumpUserValues($keysList) {
		$keys = t3lib_div::trimExplode(',', $keysList, true);
		$keysWithLabels = array();

		$maxLength = 0;
		foreach ($keys as $currentKey) {
			$loweredKey = strtolower($currentKey);
			$labelKey = 'label_' . $loweredKey;

			$currentLabel = $this->translate($labelKey);
			if (($currentLabel == '') || ($currentLabel == $labelKey)) {
				$currentLabel = ucfirst($loweredKey);
			}

			$keysWithLabels[$loweredKey] = $currentLabel;
			$maxLength = max($maxLength, mb_strlen($currentLabel));
		}

		$result = '';
		foreach ($keysWithLabels as $currentKey => $currentLabel) {
			$value = $this->getUserData($currentKey);
			// Checks whether there is a value to display. If not, we don't use
			// the padding and break the line directly after the label.
			if ($value != '') {
				$result .= str_pad(
					$currentLabel . ': ',
					$maxLength + 2,
					' '
				).$value.LF;
			} else {
				$result .= $currentLabel . ':' . LF;
			}
		}

		return $result;
	}

	/**
	 * Gets a plain text list of attendance (registration) property values
	 * (if they exist), formatted as strings (and nicely lined up) in the
	 * following format:
	 *
	 * key1: value1
	 *
	 * @param string comma-separated list of key names
	 *
	 * @return string formatted output (may be empty)
	 */
	public function dumpAttendanceValues($keysList) {
		$keys = t3lib_div::trimExplode(',', $keysList, true);
		$keysWithLabels = array();

		$maxLength = 0;
		foreach ($keys as $currentKey) {
			$loweredKey = strtolower($currentKey);
			if ($loweredKey == 'uid') {
				// The UID label is a special case as we also have a UID label
				// for events.
				$currentLabel = $this->translate('label_registration_uid');
			} else {
				$currentLabel = $this->translate('label_' . $loweredKey);
			}
			$keysWithLabels[$loweredKey] = $currentLabel;
			$maxLength = max($maxLength, mb_strlen($currentLabel));
		}

		$result = '';
		foreach ($keysWithLabels as $currentKey => $currentLabel) {
			$value = $this->getRegistrationData($currentKey);

			// Check whether there is a value to display. If not, we don't use
			// the padding and break the line directly after the label.
			if ($value != '') {
				$result .= str_pad(
					$currentLabel . ': ',
					$maxLength + 2,
					' '
				) . $value . LF;
			} else {
				$result .= $currentLabel . ':' . LF;
			}
		}

		return $result;
	}

	/**
	 * Gets the billing address, formatted as plain text.
	 *
	 * @return string the billing address
	 */
	private function getBillingAddress() {
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
					$result .= $this->translate('label_'.$key).': ';
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
	 * @return string the localized gender as entered for the billing address
	 *                (Mr. or Mrs.)
	 */
	public function getGender() {
		return $this->translate('label_gender.I.'
			.$this->getRecordPropertyInteger('gender'));
	}

	/**
	 * Checks whether there are any lodging options referenced by this record.
	 *
	 * @return boolean true if at least one lodging option is referenced by this record, false otherwise
	 */
	public function hasLodgings() {
		return $this->hasRecordPropertyInteger('lodgings');
	}

	/**
	 * Gets the selected lodging options separated by CRLF. If there is no
	 * lodging option selected, this function will return an empty string.
	 *
	 * @return string the titles of the selected loding options separated by
	 *                CRLF or an empty string if no lodging option is selected
	 */
	public function getLodgings() {
		$result = '';

		if ($this->hasLodgings()) {
			$result = $this->getMmRecords(
				SEMINARS_TABLE_LODGINGS,
				SEMINARS_TABLE_ATTENDANCES_LODGINGS_MM
			);
		}

		return $result;
	}

	/**
	 * Checks whether there are any food options referenced by this record.
	 *
	 * @return boolean true if at least one food option is referenced by this
	 *                 record, false otherwise
	 */
	public function hasFoods() {
		return $this->hasRecordPropertyInteger('foods');
	}

	/**
	 * Gets the selected food options separated by CRLF. If there is no
	 * food option selected, this function will return an empty string.
	 *
	 * @return string the titles of the selected loding options separated by
	 *                CRLF or an empty string if no food option is selected
	 */
	public function getFoods() {
		$result = '';

		if ($this->hasFoods()) {
			$result = $this->getMmRecords(
				SEMINARS_TABLE_FOODS,
				SEMINARS_TABLE_ATTENDANCES_FOODS_MM
			);
		}

		return $result;
	}

	/**
	 * Checks whether any option checkboxes are referenced by this record.
	 *
	 * @return boolean true if at least one option checkbox is referenced by
	 *                 this record, false otherwise
	 */
	public function hasCheckboxes() {
		return $this->hasRecordPropertyInteger('checkboxes');
	}

	/**
	 * Gets the selected option checkboxes separated by CRLF. If no option
	 * checkbox is selected, this function will return an empty string.
	 *
	 * @return string the titles of the selected option checkboxes separated by
	 *                CRLF or an empty string if no option checkbox is selected
	 */
	public function getCheckboxes() {
		$result = '';

		if ($this->hasCheckboxes()) {
			$result = $this->getMmRecords(
				SEMINARS_TABLE_CHECKBOXES,
				SEMINARS_TABLE_ATTENDANCES_CHECKBOXES_MM
			);
		}

		return $result;
	}

	/**
	 * Gets a CRLF-separated list of the titles of records referenced by this
	 * record.
	 *
	 * @param string the name of the foreign table (must not be empty), must
	 *               have the fields uid and title
	 * @param string the name of the m:m table, having the fields uid_local,
	 *               uid_foreign and sorting, must not be empty
	 *
	 * @return string the titles of the referenced records separated by CRLF,
	 *                might be empty if no records are referenced
	 */
	private function getMmRecords($foreignTable, $mmTable) {
		$result = '';

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, sorting',
			$foreignTable.', '.$mmTable,
			'uid_local=' . $this->getUid() . ' AND uid_foreign=uid' .
				tx_oelib_db::enableFields($foreignTable),
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
	 * @return boolean true if everything went OK, false otherwise
	 */
	public function commitToDb() {
		$result = parent::commitToDb();

		if ($result) {
			$this->recordData['uid'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
			if ($this->hasUid()) {
				$this->createMmRecords(
					SEMINARS_TABLE_ATTENDANCES_LODGINGS_MM,
					$this->lodgings
				);
				$this->createMmRecords(
					SEMINARS_TABLE_ATTENDANCES_FOODS_MM,
					$this->foods
				);
				$this->createMmRecords(
					SEMINARS_TABLE_ATTENDANCES_CHECKBOXES_MM,
					$this->checkboxes
				);
			}

			// update the reference index
			$referenceIndex = t3lib_div::makeInstance('t3lib_refindex');
			$referenceIndex->updateRefIndexTable(
				SEMINARS_TABLE_ATTENDANCES,
				$this->getUid()
			);
		}

		return $result;
	}

	/**
	 * Returns true if this registration is on the registration queue, false
	 * otherwise.
	 *
	 * @return boolean true if this registration is on the registration
	 *                 queue, false otherwise
	 */
	public function isOnRegistrationQueue() {
		return $this->getRecordPropertyBoolean('registration_queue');
	}

	/**
	 * Gets this registration's status as a localized string.
	 *
	 * @return string a localized version of either "waiting list" or
	 *                "regular", will not be empty
	 */
	public function getStatus() {
		$languageKey = 'label_' .
			($this->isOnRegistrationQueue() ? 'waiting_list' : 'regular');
		return $this->translate($languageKey);
	}

	/**
	 * Gets our referrer.
	 *
	 * @return string our referrer, may be empty
	 */
	public function getReferrer() {
		return $this->getRecordPropertyString('referrer');
	}

	/**
	 * Sets our referrer.
	 *
	 * @param string our referrer to set, may be empty
	 */
	public function setReferrer($referrer) {
		$this->setRecordPropertyString('referrer', $referrer);
	}

	/**
	 * Returns whether this registration has a referrer set.
	 *
	 * @return boolean true if this registraiton has a referrer set, false
	 *                 otherwise
	 */
	public function hasReferrer() {
		return $this->hasRecordPropertyString('referrer');
	}

	/**
	 * Builds the e-mail body for an e-mail to the attendee.
	 *
	 * @param tslib_pibase a live plugin
	 * @param string prefix for the locallang key of the localized hello
	 *               and subject string, allowed values are:
	 *               - confirmation
	 *               - confirmationOnUnregistration
	 *               - confirmationOnRegistrationForQueue
	 *               - confirmationOnQueueUpdate
	 *               In the following the parameter is prefixed with
	 *               "email_" and postfixed with "Hello" or "Subject".
	 * @param boolean whether to create a HTML body for the e-mail or just the
	 *                plain text version
	 *
	 * @return string the e-mail body for the attendee e-mail, will not be empty
	 */
	private function buildEmailContent(
		tslib_pibase $plugin, $helloSubjectPrefix , $useHtml = false
	) {
		$wrapperPrefix = (($useHtml) ? 'html_' : '') . 'field_wrapper';

		$this->setMarker('html_mail_charset', (
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']
				? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']
				: 'ISO-8859-1'
			)
		);
		$this->hideSubparts(
			$this->getConfValueString('hideFieldsInThankYouMail'),
			$wrapperPrefix
		);

		$hello = sprintf(
			$this->translate('email_' . $helloSubjectPrefix . 'Hello'),
			$this->getUserName()
		);
		$this->setMarker('hello', (($useHtml) ? nl2br($hello) : $hello));
		$event = $this->getSeminarObject();
		if ($event->hasEventType()) {
			$this->setMarker('event_type', $event->getEventType());
		} else {
			$this->hideSubparts('event_type', $wrapperPrefix);
		}
		$this->setMarker('title', $event->getTitle());
		$this->setMarker('uid', $event->getUid());

		$this->setMarker('registration_uid', $this->getUid());

		if ($this->hasSeats()) {
			$this->setMarker('seats', $this->getSeats());
		} else {
			$this->hideSubparts('seats', $wrapperPrefix);
		}

		if ($this->hasRecordPropertyString('attendees_names')) {
			$this->setMarker(
				'attendees_names',
				$this->getRecordPropertyString('attendees_names')
			);
		} else {
			$this->hideSubparts('attendees_names', $wrapperPrefix);
		}

		if ($this->hasLodgings()) {
			$this->setMarker('lodgings', $this->getLodgings());
		} else {
			$this->hideSubparts('lodgings', $wrapperPrefix);
		}

		if ($this->hasFoods()) {
			$this->setMarker('foods', $this->getFoods());
		} else {
			$this->hideSubparts('foods', $wrapperPrefix);
		}

		if ($this->hasCheckboxes()) {
			$this->setMarker('checkboxes', $this->getCheckboxes());
		} else {
			$this->hideSubparts('checkboxes', $wrapperPrefix);
		}

		if ($this->hasRecordPropertyInteger('kids')) {
			$this->setMarker(
				'kids',
				$this->getRecordPropertyInteger('kids')
			);
		} else {
			$this->hideSubparts('kids', $wrapperPrefix);
		}

		if ($event->hasAccreditationNumber()) {
			$this->setMarker(
				'accreditation_number',
				$event->getAccreditationNumber()
			);
		} else {
			$this->hideSubparts('accreditation_number', $wrapperPrefix);
		}

		if ($event->hasCreditPoints()) {
			$this->setMarker(
				'credit_points',
				$event->getCreditPoints()
			);
		} else {
			$this->hideSubparts('credit_points', $wrapperPrefix);
		}

		$this->setMarker('date',
			$event->getDate(
				(($useHtml) ? '&#8212;' : '-')
			)
		);
		$this->setMarker('time',
			$event->getTime(
				(($useHtml) ? '&#8212;' : '-')
			)
		);
		$this->setMarker('place', $event->getPlaceShort());

		if ($event->hasRoom()) {
			$this->setMarker('room', $event->getRoom());
		} else {
			$this->hideSubparts('room', $wrapperPrefix);
		}

		if ($this->hasRecordPropertyString('price')) {
			$this->setMarker('price', $this->getPrice());
		} else {
			$this->hideSubparts('price', $wrapperPrefix);
		}

		if ($this->hasRecordPropertyDecimal('total_price')) {
			$this->setMarker('total_price', $this->getTotalPrice(' '));
		} else {
			$this->hideSubparts('total_price', $wrapperPrefix);
		}

		// We don't need to check $this->seminar->hasPaymentMethods() here as
		// method_of_payment can only be set (using the registration form) if
		// the event has at least one payment method.
		if ($this->hasRecordPropertyInteger('method_of_payment')) {
			$this->setMarker(
				'paymentmethod',
				$event->getSinglePaymentMethodPlain(
					$this->getRecordPropertyInteger('method_of_payment')
				)
			);
		} else {
			$this->hideSubparts('paymentmethod', $wrapperPrefix);
		}

		$this->setMarker('billing_address', $this->getBillingAddress());

		$this->setMarker(
			'url',
			(($useHtml)
				? htmlspecialchars($event->getDetailedViewUrl($plugin))
				: $event->getDetailedViewUrl($plugin)
			)
		);

		if ($event->isPlanned()) {
			$this->unhideSubparts('planned_disclaimer', $wrapperPrefix);
		} else {
			$this->hideSubparts('planned_disclaimer', $wrapperPrefix);
		}

		$footers = $event->getOrganizersFooter();
		$this->setMarker('footer', $footers[0]);

		return $this->getSubpart(
			(($useHtml) ? 'MAIL_THANKYOU_HTML' : 'MAIL_THANKYOU')
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']);
}
?>