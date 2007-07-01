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
 * Class 'tx_seminars_timeslot' for the 'seminars' extension.
 *
 * This class represents a time slot.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_timespan.php');

class tx_seminars_timeslot extends tx_seminars_timespan {
	/** same as class name */
	var $prefixId = 'tx_seminars_timeslot';
	/**  path to this script relative to the extension dir */
	var $scriptRelPath = 'class.tx_seminars_timeslot.php';

	/**
	 * The constructor. Creates a timeslot instance from a DB record.
	 *
	 * @param	integer		The UID of the timeslot to retrieve from the DB.
	 * 						This parameter will be ignored if $dbResult is provided.
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 						If this parameter is provided, $uid will be ignored.
	 *
	 * @access	public
	 */
	function tx_seminars_timeslot($timeslotUid, $dbResult = null) {
		$this->setTableNames();
		$this->tableName = $this->tableTimeslots;

		if (!$dbResult) {
			$dbResult = $this->retrieveRecord($timeslotUid);
		}

		if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->getDataFromDbResult($GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult));
		}

		$this->init();
	}

	/**
	 * Gets the speaker UIDs.
	 *
	 * @return	array		the speaker UIDs
	 *
	 * @access	public
	 */
	function getSpeakersUids() {
		$result = array();

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid_foreign',
			$this->tableTimeslotsSpeakersMM,
			'uid_local='.$this->getUid()
		);

		if ($dbResult) {
			while ($speaker = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result[] = $speaker['uid_foreign'];
			}
		}

		return $result;
	}

	/**
	 * Gets the place.
	 *
	 * @return	integer		the place UID
	 *
	 * @access	public
	 */
	function getPlace() {
		return $this->getRecordPropertyInteger('place');
	}

	/**
	 * Gets the entry date as a formatted date.
	 *
	 * @return	string		the entry date (or the localized string "will be 
	 * 						announced" if no entry date is set)
	 *
	 * @access	public
	 */
	function getEntryDate() {
		if ($this->hasEntryDate()) {
			$entryDate = $this->getRecordPropertyInteger('entry_date');
			$result = strftime($this->getConfValueString('dateFormatYMD'), $entryDate);
		} else {
			$result = $this->pi_getLL('message_willBeAnnounced');
		}

		return $result;
	}

	/**
	 * Checks whether the timeslot has a entry date set.
	 *
	 * @return	boolean		true if we have a entry date, false otherwise
	 *
	 * @access	public
	 */
	function hasEntryDate() {
		return $this->hasRecordPropertyInteger('entry_date');
	}

	/**
	 * Returns an associative array, containing fieldname/value pairs that need
	 * to be updated in the database. Update means "set the title" so far.
	 *
	 * @return	string		associative array containing data to update the
	 * 						database entry of the timeslot, might be empty but
	 * 						will not be null
	 *
	 * @access	public
	 */
	function getUpdateArray(&$fieldArray) {
		$updateArray = array();

		$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ?
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] :
			'iso-8859-1';

		$updateArray['title'] = html_entity_decode(
			$this->getDate(),
			ENT_COMPAT,
			$charset
		);

		return $updateArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timeslot.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_timeslot.php']);
}

?>
