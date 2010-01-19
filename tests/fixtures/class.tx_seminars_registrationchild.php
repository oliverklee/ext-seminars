<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Niels Pardon (mail@niels-pardon.de)
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

require_once(PATH_tslib . 'class.tslib_content.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_registration.php');

/**
 * Class 'tx_seminars_registrationchild' for the 'seminars' extension.
 *
 * This is mere a class used for unit tests of the 'seminars' extension. Don't
 * use it for any other purpose.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
final class tx_seminars_registrationchild extends tx_seminars_registration {
	/**
	 * The constructor.
	 *
	 * @param integer UID of the registration record, must be > 0
	 */
	public function __construct($registrationUid) {
		$dbResult = tx_oelib_db::select(
			'*',
			$this->tableName,
			'uid = ' . $registrationUid
		);

		$contentObject = t3lib_div::makeInstance('tslib_cObj');
		$contentObject->start('');

		parent::__construct($contentObject, $dbResult);
	}

	/**
	 * Sets the "registration_queue" field of the registration record.
	 *
	 * @param boolean true if the registration should be on the waiting
	 * list, false otherwise
	 */
	public function setIsOnRegistrationQueue($isOnRegistrationQueueValue) {
		$this->setRecordPropertyInteger(
			'registration_queue',
			intval($isOnRegistrationQueueValue)
		);
	}

	/**
	 * Sets the payment method of this registration.
	 *
	 * @param integer the UID of the payment method to set
	 */
	public function setPaymentMethod($uid) {
		if ($uid <= 0) {
			throw new Exception('Invalid payment method UID.');
		}

		$this->setRecordPropertyInteger(
			'method_of_payment',
			$uid
		);
	}

	/**
	 * Sets the data of the FE user of this registration.
	 *
	 * @param array data of the front-end user, may be empty
	 */
	public function setUserData(array $userData) {
		parent::setUserData($userData);
	}

	/**
	 * Returns the content of the member variable foods.
	 *
	 * @return array the content of the member variable foods, will be empty if
	 *               foods is empty
	 */
	public function getFoodsData() {
		return $this->foods;
	}

	/**
	 * Returns the content of the member variable lodgings.
	 *
	 * @return array the content of the member variable lodgings, will be empty
	 *               if lodgings is empty
	 */
	public function getLodgingsData() {
		return $this->lodgings;
	}

	/**
	 * Returns the content of the member variable checkboxes.
	 *
	 * @return array the content of the member variable checkboxes, will be
	 *               empty if checkboxes is empty
	 */
	public function getCheckboxesData() {
		return $this->checkboxes;
	}
}
?>