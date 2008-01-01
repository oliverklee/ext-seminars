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
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminar.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_timeslot.php');

class tx_seminars_tcemainprocdm extends tx_seminars_dbplugin {
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
	 * Checks that the registration deadline set by the user is in no way larger (=later)
	 * than the beginning of the event.
	 * It doesn't matter which field got changed in the form because this function is
	 * called AFTER writing the data from the form to the database.
	 * In the case that the deadline is later than the beginning time, the deadline will
	 * be set to zero (wich means: not set).
	 *
	 * Some of the parameters of this function are not used in this function. But they
	 * are given by the hook in t3lib/class.t3lib_tcemain.php.
	 *
	 * Note: When using the hook after INSERT operations, you will only get the
	 * temporary NEW... id passed to your hook as $id, but you can easily translate
	 * it to the real uid of the inserted record using the $pObj->substNEWwithIDs
	 * array.
	 *
	 * @param	string		the status of this record (new/update)
	 * @param	string		the affected table name
	 * @param	integer		the uid of the affected record (may be zero)
	 * @param	array		an array of all fields that got changed (as reference)
	 * @param	object		reference to tcemain calling object (as reference)
	 *
	 * @access	public
	 */
	function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$pObj) {
		// Translate new UIDs.
		if ($status == 'new') {
			$id = $pObj->substNEWwithIDs[$id];
		}

		// only do the database query if the right table was modified
		switch ($table) {
			case $this->tableSeminars:
				// Initialize a seminar object to have all functions available.
				$seminarClassname = t3lib_div::makeInstanceClassName('tx_seminars_seminar');
				$seminar =& new $seminarClassname($id, null, true);
	
				if ($seminar->isOk()) {
					// Get an associative array of fields that need to be updated
					// in the database.
					$updateArray = $seminar->getUpdateArray($fieldArray);
	
					// Only update the record in the database if needed.
					if (count($updateArray)) {
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							$this->tableSeminars,
							'uid='.$id,
							$updateArray
						);
					}
				}
				break;
			case $this->tableTimeslots:
				// Initialize a timeslot object to have all functions available
				$timeslotClassname = t3lib_div::makeInstanceClassName('tx_seminars_timeslot');
				$timeslot =& new $timeslotClassname($id, null);

				if ($timeslot->isOk()) {
					// Get an associative array of fields that need to be updated
					// in the database.
					$updateArray = $timeslot->getUpdateArray($fieldArray);

					// Only update the record in the database if needed.
					if (count($updateArray)) {
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							$this->tableTimeslots,
							'uid='.$id,
							$updateArray
						);
					}
				}
				break;
			default:
				// Do nothing.
				break;
		}

		return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_tcemain.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_tcemain.php']);
}

?>
