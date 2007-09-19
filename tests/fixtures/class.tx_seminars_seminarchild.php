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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminarst/tests/fixtures/class.tx_seminars_seminarchild.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_seminarchild.php']);
}

?>
