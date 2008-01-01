<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Mario Rimann (typo3-coding@rimann.org)
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
 * Class 'tx_seminars_tcemainprocdm' for the 'seminars' extension.
 *
 * This class holds functions used to validate submitted forms in the back end.
 * These functions are called from t3lib/class.t3lib_tcemain.php via hooks.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Mario Rimann <typo3-coding@rimann.org>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_timeslot.php');

class tx_seminars_tcemainprocdm extends tx_seminars_dbplugin {
	var $tceMainFieldArrays = array();

	/**
	 * The constructor.
	 *
	 * @access	public
	 */
	function tx_seminars_tcemainprocdm() {
		parent::init();

		return;
	}

	/**
	 * Handles data after everything had been written to the database.
	 *
	 * @param	object		the calling TCEmain object as reference
	 *
	 * @access	public
	 */
	function processDatamap_afterAllOperations(&$parentObj) {
		$this->processTimeSlots();
		$this->processEvents();
	}

	/**
	 * Builds $this->tceMainFieldArrays if the right tables were modified.
	 *
	 * Some of the parameters of this function are not used in this function.
	 * But they are given by the hook in t3lib/class.t3lib_tcemain.php.
	 *
	 * Note: When using the hook after INSERT operations, you will only get the
	 * temporary NEW... id passed to your hook as $id, but you can easily
	 * translate it to the real uid of the inserted record using the
	 * $pObj->substNEWwithIDs array.
	 *
	 * @param	string		the status of this record (new/update)
	 * @param	string		the affected table name
	 * @param	integer		the uid of the affected record (may be zero)
	 * @param	array		an array of all fields that got changed (as reference)
	 * @param	object		reference to tcemain calling object (as reference)
	 *
	 * @access	public
	 */
	function processDatamap_afterDatabaseOperations(
		$status, $table, $uid, &$fieldArray, &$pObj
	) {
		// Translates new UIDs.
		if ($status == 'new') {
			$uid = $pObj->substNEWwithIDs[$uid];
		}

		if (($table == SEMINARS_TABLE_SEMINARS)
			|| ($table == SEMINARS_TABLE_TIME_SLOTS)
		) {
			$this->tceMainFieldArrays[$table][$uid] = $fieldArray;
		}
	}

	/**
	 * Processes all time slots.
	 *
	 * @access	protected
	 */
	function processTimeSlots() {
		$table = SEMINARS_TABLE_TIME_SLOTS;

		if (
			isset($this->tceMainFieldArrays[$table])
			&& is_array($this->tceMainFieldArrays[$table])
		) {
			foreach ($this->tceMainFieldArrays[$table] as $uid => $fieldArray) {
				$this->processSingleTimeSlot($uid, $fieldArray);
			}
		}
	}

	/**
	 * Processes all events.
	 *
	 * @access	protected
	 */
	function processEvents() {
		$table = SEMINARS_TABLE_SEMINARS;

		if (
			isset($this->tceMainFieldArrays[$table])
			&& is_array($this->tceMainFieldArrays[$table])
		) {
			foreach ($this->tceMainFieldArrays[$table] as $uid => $fieldArray) {
				$this->processSingleEvent($uid, $fieldArray);
			}
		}
	}

	/**
	 * Processes a single time slot.
	 *
	 * @param	integer		the UID of the affected record (may be 0)
	 * @param	array		an array of all fields that got changed
	 *
	 * @access	protected
	 */
	function processSingleTimeSlot($uid, $fieldArray) {
		// Initializes a timeslot object to have all
		// functions available.
		$timeslotClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_timeslot'
		);
		$timeslot =& new $timeslotClassname($uid, null);

		if ($timeslot->isOk()) {
			// Gets an associative array of fields that need
			// to be updated in the database and update them.
			$timeslot->saveToDatabase(
				$timeslot->getUpdateArray($fieldArray)
			);
		}
	}

	/**
	 * Processes a single event.
	 *
	 * @param	integer		the UID of the affected record (may be 0)
	 * @param	array		an array of all fields that got changed
	 *
	 * @access	protected
	 */
	function processSingleEvent($uid, $fieldArray) {
		// Initializes a seminar object to have all functions
		// available.
		$seminarClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_seminar'
		);
		$seminar =& new $seminarClassname($uid, null, true);

		if ($seminar->isOk()) {
			// Gets an associative array of fields that need
			// to be updated in the database.
			$seminar->saveToDatabase(
				$seminar->getUpdateArray($fieldArray)
			);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_tcemain.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_tcemain.php']);
}

?>
