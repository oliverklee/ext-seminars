<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
	protected $tableName = 'tx_seminars_attendances';

	/** Same as class name */
	public $prefixId = 'tx_seminars_registration';
	/**  Path to this script relative to the extension dir. */
	public $scriptRelPath = 'class.tx_seminars_registration.php';

	/**
	 * @var tx_seminars_seminar the event to which this registration relates
	 */
	private $seminar = null;

	/**
	 * @var boolean whether the user data has already been retrieved
	 */
	private $userDataHasBeenRetrieved = FALSE;

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
	 * The constructor.
	 *
	 * @param object content object
	 * @param pointer MySQL result pointer (of SELECT query)/DBAL object.
	 *                If this parameter is not provided or FALSE,
	 *                setRegistrationData() needs to be called directly
	 *                after construction or this object will not be usable.
	 */
	public function __construct(tslib_cObj $cObj, $dbResult = FALSE) {
		$this->cObj = $cObj;
		$this->initializeCharsetConversion();
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
		$this->recordData['registered_themselves'] =
			($registrationData['registered_themselves']) ? 1 : 0;

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
				$rows = tx_oelib_db::selectMultiple(
					'uid',
					'tx_seminars_payment_methods, tx_seminars_seminars_payment_methods_mm',
					'tx_seminars_payment_methods.uid = ' .
						'tx_seminars_seminars_payment_methods_mm.uid_foreign ' .
						'AND tx_seminars_seminars_payment_methods_mm.uid_local=' .
						$seminar->getTopicUid() .
						tx_oelib_db::enableFields('tx_seminars_payment_methods'),
					'',
					'tx_seminars_seminars_payment_methods_mm.sorting'
				);
				$methodOfPayment = $rows[0]['uid'];
		}
		$this->recordData['method_of_payment'] = $methodOfPayment;

		$this->recordData['account_number'] = $registrationData['account_number'];
		$this->recordData['bank_code'] = $registrationData['bank_code'];
		$this->recordData['bank_name'] = $registrationData['bank_name'];
		$this->recordData['account_owner'] = $registrationData['account_owner'];

		$this->recordData['company'] = $registrationData['company'];
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
	 * @return boolean TRUE if this registration has seats, FALSE otherwise
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
		$this->userDataHasBeenRetrieved = TRUE;
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
				$result = $this->getTotalPrice();
				break;
			case 'registered_themselves':
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
			case 'attendees_names':
				$result = $this->getEnumeratedAttendeeNames();
				break;
			default:
				$result = $this->getRecordPropertyString($trimmedKey);
				break;
		}

		$carriageReturnRemoved = str_replace(CR, LF, (string) $result);

		return preg_replace('/\\x0a{2,}/', LF, $carriageReturnRemoved);
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
					case 'name':
						$result = $this->getFrontEndUser()->getName();
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
		$singleKeys = t3lib_div::trimExplode(',', $keys, TRUE);
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
	 * @return tx_seminars_Model_FrontEndUser the front-end user of the registration
	 */
	public function getFrontEndUser() {
		return tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')->find(
			$this->getUser()
		);
	}

	/**
	 * Returns whether the registration has an existing front-end user.
	 *
	 * @return boolean TRUE if the registration has an existing front-end user,
	 *                 FALSE otherwise
	 */
	public function hasExistingFrontEndUser() {
		if ($this->getUser() <= 0) {
			return FALSE;
		}

		return tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')->
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
				$this->seminar = tx_oelib_ObjectFactory::make(
					'tx_seminars_seminar', $seminarUid
				);
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
		return $this->getRecordPropertyInteger('datepaid') > 0;
	}

	/**
	 * Sets the date when this registration has been paid for.
	 *
	 * @param integer $paymentDate
	 *        the date of the payment as UNIX timestamp, must be >= 0
	 */
	public function setPaymentDateAsUnixTimestamp($paymentDate) {
		$this->setRecordPropertyInteger('datepaid', $paymentDate);
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
	 * Sets our price category name and its single price.
	 *
	 * @param string the price category name and its single price, may be empty
	 */
	public function setPrice($price) {
		$this->setRecordPropertyString('price', $price);
	}

	/**
	 * Returns whether this registration has a saved price category name and
	 * its single price.
	 *
	 * @return boolean TRUE if this registration has a price, FALSE otherwise
	 */
	public function hasPrice() {
		return $this->hasRecordPropertyString('price');
	}

	/**
	 * Gets the saved total price and the currency.
	 * An empty string will be returned if no total price could be calculated.
	 *
	 * @return string the total price and the currency or an empty string
	 *                if no total price could be calculated
	 */
	public function getTotalPrice() {
		$result = '';
		$totalPrice = $this->getRecordPropertyDecimal('total_price');
		if ($totalPrice != '0.00') {
			$result = $this->getSeminarObject()->formatPrice($totalPrice);
		}

		return $result;
	}

	/**
	 * Sets our total price.
	 *
	 * @param string the total price, may be empty
	 */
	public function setTotalPrice($price) {
		$this->setRecordPropertyString('total_price', $price);
	}

	/**
	 * Returns whether this registration has a total price.
	 *
	 * @return boolean TRUE if this registration has a total price, FALSE
	 *                 otherwise
	 */
	public function hasTotalPrice() {
		return $this->hasRecordPropertyDecimal('total_price');
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
		$keys = t3lib_div::trimExplode(',', $keysList, TRUE);
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
			$maxLength = max(
				$maxLength,
				$this->charsetConversion->strlen(
					$this->renderCharset, $currentLabel
				)
			);
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
		$keys = t3lib_div::trimExplode(',', $keysList, TRUE);
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
			$maxLength = max(
				$maxLength,
				$this->charsetConversion->strlen(
					$this->renderCharset, $currentLabel
				)
			);
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
	public function getBillingAddress() {
		/**
		 * the keys of the corresponding fields and whether to add a LF after
		 * the entry (instead of just a space)
		 */
		$billingAddressFields = array(
			'gender' => FALSE,
			'name' => TRUE,
			'address' => TRUE,
			'zip' => FALSE,
			'city' => TRUE,
			'country' => TRUE,
			'telephone' => TRUE,
			'email' => TRUE
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
	 * @return boolean TRUE if at least one lodging option is referenced by this record, FALSE otherwise
	 */
	public function hasLodgings() {
		return $this->hasRecordPropertyInteger('lodgings');
	}

	/**
	 * Gets the selected lodging options separated by LF. If there is no
	 * lodging option selected, this function will return an empty string.
	 *
	 * @return string the titles of the selected loding options separated by
	 *                LF or an empty string if no lodging option is selected
	 */
	public function getLodgings() {
		$result = '';

		if ($this->hasLodgings()) {
			$result = $this->getMmRecords(
				'tx_seminars_lodgings',
				'tx_seminars_attendances_lodgings_mm'
			);
		}

		return $result;
	}

	/**
	 * Checks whether there are any food options referenced by this record.
	 *
	 * @return boolean TRUE if at least one food option is referenced by this
	 *                 record, FALSE otherwise
	 */
	public function hasFoods() {
		return $this->hasRecordPropertyInteger('foods');
	}

	/**
	 * Gets the selected food options separated by LF. If there is no
	 * food option selected, this function will return an empty string.
	 *
	 * @return string the titles of the selected loding options separated by
	 *                LF or an empty string if no food option is selected
	 */
	public function getFoods() {
		$result = '';

		if ($this->hasFoods()) {
			$result = $this->getMmRecords(
				'tx_seminars_foods',
				'tx_seminars_attendances_foods_mm'
			);
		}

		return $result;
	}

	/**
	 * Checks whether any option checkboxes are referenced by this record.
	 *
	 * @return boolean TRUE if at least one option checkbox is referenced by
	 *                 this record, FALSE otherwise
	 */
	public function hasCheckboxes() {
		return $this->hasRecordPropertyInteger('checkboxes');
	}

	/**
	 * Gets the selected option checkboxes separated by LF. If no option
	 * checkbox is selected, this function will return an empty string.
	 *
	 * @return string the titles of the selected option checkboxes separated by
	 *                LF or an empty string if no option checkbox is selected
	 */
	public function getCheckboxes() {
		$result = '';

		if ($this->hasCheckboxes()) {
			$result = $this->getMmRecords(
				'tx_seminars_checkboxes',
				'tx_seminars_attendances_checkboxes_mm'
			);
		}

		return $result;
	}

	/**
	 * Gets a LF-separated list of the titles of records referenced by this
	 * record.
	 *
	 * @param string the name of the foreign table (must not be empty), must
	 *               have the fields uid and title
	 * @param string the name of the m:m table, having the fields uid_local,
	 *               uid_foreign and sorting, must not be empty
	 *
	 * @return string the titles of the referenced records separated by LF,
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
					$result .= LF;
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
	 * @return boolean TRUE if everything went OK, FALSE otherwise
	 */
	public function commitToDb() {
		$result = parent::commitToDb();

		if ($result) {
			$this->recordData['uid'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
			if ($this->hasUid()) {
				$this->createMmRecords(
					'tx_seminars_attendances_lodgings_mm',
					$this->lodgings
				);
				$this->createMmRecords(
					'tx_seminars_attendances_foods_mm',
					$this->foods
				);
				$this->createMmRecords(
					'tx_seminars_attendances_checkboxes_mm',
					$this->checkboxes
				);
			}

			// update the reference index
			$referenceIndex = t3lib_div::makeInstance('t3lib_refindex');
			$referenceIndex->updateRefIndexTable(
				'tx_seminars_attendances',
				$this->getUid()
			);
		}

		return $result;
	}

	/**
	 * Returns TRUE if this registration is on the registration queue, FALSE
	 * otherwise.
	 *
	 * @return boolean TRUE if this registration is on the registration
	 *                 queue, FALSE otherwise
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
	 * Returns our attendees names.
	 *
	 * @return string our attendees names, will be empty if this registration
	 *                has no attendees names
	 */
	public function getAttendeesNames() {
		return $this->getRecordPropertyString('attendees_names');
	}

	/**
	 * Sets our attendees names.
	 *
	 * @param string our attendees names, may be empty
	 */
	public function setAttendeesNames($attendeesNames) {
		$this->setRecordPropertyString('attendees_names', $attendeesNames);
	}

	/**
	 * Returns whether this registration has attendees names.
	 *
	 * @return boolean TRUE if this registration has attendees names, FALSE
	 *                 otherwise
	 */
	public function hasAttendeesNames() {
		return $this->hasRecordPropertyString('attendees_names');
	}

	/**
	 * Returns our number of kids.
	 *
	 * @return integer the number of kids, will be >= 0, will be 0 if this
	 *                 registration has no kids
	 */
	public function getNumberOfKids() {
		return $this->getRecordPropertyInteger('kids');
	}

	/**
	 * Sets the number of kids.
	 *
	 * @param integer the number of kids, must be >= 0
	 */
	public function setNumberOfKids($numberOfKids) {
		if ($numberOfKids < 0) {
			throw new Exception('The parameter $numberOfKids must be >= 0.');
		}

		$this->setRecordPropertyInteger('kids', $numberOfKids);
	}

	/**
	 * Returns whether this registration has kids.
	 *
	 * @return boolean TRUE if this registration has kids, FALSE otherwise
	 */
	public function hasKids() {
		return $this->hasRecordPropertyInteger('kids');
	}

	/**
	 * Returns our method of payment UID.
	 *
	 * @return integer our method of payment UID, will be >= 0, will be 0 if
	 *                 this registration has no method of payment
	 */
	public function getMethodOfPaymentUid() {
		return $this->getRecordPropertyInteger('method_of_payment');
	}

	/**
	 * Sets our method of payment UID.
	 *
	 * @param integer our method of payment UID, must be >= 0
	 */
	public function setMethodOfPaymentUid($uid) {
		if ($uid < 0) {
			throw new Exception('The parameter $uid must be >= 0.');
		}

		$this->setRecordPropertyInteger('method_of_payment', $uid);
	}

	/**
	 * Returns whether this registration has a method of payment.
	 *
	 * @return boolean TRUE if this event has a method of payment, FALSE
	 *                 otherwise
	 */
	public function hasMethodOfPayment() {
		return $this->hasRecordPropertyInteger('method_of_payment');
	}

	/**
	 * Returns the enumerated attendees_names.
	 *
	 * If the enumerated names should be built by using HTML, they will be
	 * created as list items of an ordered list. In the plain text case the
	 * entries will be separated by LF.
	 *
	 * @param boolean $useHtml whether to use HTML to build the enumeration
	 *
	 * @return string the names stored in attendees_name enumerated, will be
	 *                empty if this registration has no attendees names
	 */
	public function getEnumeratedAttendeeNames($useHtml = FALSE) {
		if (!$this->hasAttendeesNames() && !$this->hasRegisteredMySelf()) {
			return '';
		}

		$names = t3lib_div::trimExplode(
			LF, $this->getAttendeesNames(), TRUE
		);
		if ($this->hasRegisteredMySelf()) {
			$names = array_merge(
				array($this->getFrontEndUser()->getName()), $names
			);
		}

		if ($useHtml) {
			$result = '<ol><li>' . implode('</li><li>', $names) . '</li></ol>';
		} else {
			$enumeratedNames = array();
			$attendeeCounter = 1;
			foreach ($names as $name) {
				$enumeratedNames[] = $attendeeCounter . '. ' . $name;
				$attendeeCounter++;
			}
			$result = implode(LF, $enumeratedNames);
		}

		return $result;
	}

	/**
	 * Checks whether the user has registered themselves.
	 *
	 * @return boolean TRUE if the user registered themselves, FALSE otherwise
	 */
	public function hasRegisteredMySelf() {
		return $this->getRecordPropertyBoolean('registered_themselves');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']);
}
?>