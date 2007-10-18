<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_seminarchild' for the 'seminars' extension.
 *
 * This is mere a class used for unit tests of the 'seminars' extension. Don't
 * use it for any other purpose.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');

// Define the UIDs used for testing.
// TODO: This should be moved to a separate class that generates dummy records
// for unit testing. See https://bugs.oliverklee.com/show_bug.cgi?id=1237
define(PLACE_VALID_COUNTRY_UID, 100000);
define(PLACE_OTHER_VALID_COUNTRY_UID, 100001);
define(PLACE_INVALID_COUNTRY_UID, 100002);
define(PLACE_NO_COUNTRY_UID, 100003);
define(PLACE_DELETED_UID, 100004);

final class tx_seminars_seminarchild extends tx_seminars_seminar {
	public $prefixId = 'tx_seminars_seminarchild';
	public $scriptRelPath
		= 'tests/fixtures/class.tx_seminars_seminarchild.php';

	/**
	 * The constructor.
	 *
	 * @param	array	TS setup configuration array, may be empty
	 */
	public function __construct(array $configuration) {
		// Call the base classe's constructor manually as this isn't done
		// automatically.
		parent::tslib_pibase();

		$this->conf = $configuration;

		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->setTableNames();
		$this->setRecordTypes();

		$this->tableName = $this->tableSeminars;
	}

	/**
	 * Sets the event data.
	 *
	 * @param	array		event data array
	 */
	public function setEventData(array $eventData) {
		$this->recordData = $eventData;
		$this->isInDb = true;
	}

	/**
	 * Sets the event's unregistration deadline.
	 *
	 * @param	integer		unregistration deadline as UNIX timestamp
	 */
	public function setUnregistrationDeadline($unregistrationDeadline) {
		$this->setRecordPropertyInteger('deadline_unregistration', $unregistrationDeadline);
	}

	/**
	 * Sets the event's begin date.
	 *
	 * @param	integer		begin date as UNIX timestamp
	 */
	public function setBeginDate($beginDate) {
		$this->setRecordPropertyInteger('begin_date', $beginDate);
	}

	/**
	 * Sets the event's maximum number of attendances.
	 *
	 * @param	integer		maximum attendances number
	 */
	public function setAttendancesMax($attendancesMax) {
		$this->setRecordPropertyInteger('attendees_max', $attendancesMax);
	}

	/**
	 * Sets the event's type.
	 *
	 * @param	integer		event's type
	 */
	public function setEventType($type) {
		$this->setRecordPropertyInteger('object_type', $type);
	}

	/**
	 * Sets an m:m relation between this event and one or multiple place records.
	 * 
	 * @param	string		comma-separated list of place UIDs
	 */
	public function setPlaceMM($places) {
		$placeUIDs = explode(',', $places);
		foreach ($placeUIDs as $currentUID) {
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$this->tableSitesMM,
				array(
					'uid_local' => $this->getUid(),
					'uid_foreign' => $currentUID
				)
			);
		}

		// Add the number of UIDs that we've set as relation to the field
		// "place" of the event record. Functions like hasPlace() only check
		// whether this field is empty and not zero.
		$this->setRecordPropertyInteger('place', count($placeUIDs));
	}

	/**
	 * Removes all entries in the m:m table for the dummy dummy place records
	 * we have entered into the database for testing.
	 */
	public function removePlaceMM() {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$this->tableSitesMM,
			'uid_foreign >= ' . PLACE_VALID_COUNTRY_UID
		);

		// Set the "place" field of the event back to zero. The method hasPlace()
		// will now return false.
		$this->setRecordPropertyInteger('place', 0);
	}

	/**
	 * This creates some dummy seminar site records in the database that we can
	 * use for testing.
	 *
	 * There are the following places created:
	 * 100000	CH	place record with valid country
	 * 100001	DE	place record with valid country
	 * 100002	XY	place record with invalid country
	 * 100003		place record with no country set
	 */
	public function createPlaces() {
		$insertArray = array(
			array(
				'uid' => PLACE_VALID_COUNTRY_UID,
				'country' => 'ch'
			),
			array(
				'uid' => PLACE_OTHER_VALID_COUNTRY_UID,
				'country' => 'de'
			),
			array(
				'uid' => PLACE_INVALID_COUNTRY_UID,
				'country' => 'xy'
			),
			array(
				'uid' => PLACE_NO_COUNTRY_UID,
				'country' => ''
			),
			array(
				'uid' => PLACE_DELETED_UID,
				'country' => 'at',
				'deleted' => 1
			)
		);
		foreach ($insertArray as $currentRecord) {
			$res = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$this->tableSites,
				$currentRecord
			);
		}
	}

	/**
	 * Deletes the dummy seminar site records after usage.
	 */
	public function removePlacesFixture() {
		$this->removePlaceMM();
		$this->removePlaceRecords();
	}

	/**
	 * Removes all dummy place records we have entered into the database for
	 * testing.
	 */
	public function removePlaceRecords() {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			$this->tableSites,
			'uid >= ' . PLACE_VALID_COUNTRY_UID
		);
	}

	/**
	 * Sets the configuration for showTimeOfUnregistrationDeadline.
	 *
	 * @param	integer		value for showTimeOfUnregistrationDeadline (0 or 1)
	 */
	public function setShowTimeOfUnregistrationDeadline($value) {
		$this->setConfigurationValue(
			'showTimeOfUnregistrationDeadline', $value
		);
	}

	/**
	 * Sets the TypoScript configuration for the parameter
	 * unregistrationDeadlineDaysBeforeBeginDate.
	 *
	 * @param	integer		days before the begin date until unregistration
	 *						should be possible
	 */
	public function setGlobalUnregistrationDeadline($days) {
		$this->setConfigurationValue(
			'unregistrationDeadlineDaysBeforeBeginDate', $days
		);
	}

	/**
	 * Sets the registration queue size.
	 *
	 * @param	integer		size of the registration queue
	 */
	public function setRegistrationQueueSize($size) {
		$this->setRecordPropertyInteger('queue_size', $size);
	}

	/**
	 * Sets the number of attendances.
	 *
	 * @param	integer		number of attendances
	 */
	public function setNumberOfAttendances($number) {
		$this->numberOfAttendances = $number;
		$this->statisticsHaveBeenCalculated = true;
	}

	/**
	 * Sets the number of attendances on the registration queue.
	 *
	 * @param	integer		number of attendances on the registration queue
	 */
	public function setNumberOfAttendancesOnQueue($number) {
		$this->numberOfAttendancesOnQueue = $number;
		$this->statisticsHaveBeenCalculated = true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminarst/tests/fixtures/class.tx_seminars_seminarchild.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_seminarchild.php']);
}

?>
