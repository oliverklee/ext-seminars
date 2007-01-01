<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class 'tx_seminars' for the 'seminars' extension.
 * This naming scheme needs to be followed due to the naming conventions
 * imposed by FORMidable.
 *
 * This is a FORMidable data handler that can also handle m:n relations.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('ameos_formidable').'api/base/ameos_formidable_dh_db/api/class.tx_ameosformidabledhdb.php');

class tx_seminars extends tx_ameosformidabledhdb {
	/**
	 * List of relations that are m:n relations as key/mm-table pairs
	 */
	var $mmFields = array(
		'place' => 'tx_seminars_seminars_place_mm',
		'speakers' => 'tx_seminars_seminars_speakers_mm'
	);

	/**
	 * Takes the entered form data (via $this->_getFormDataManaged()) and inserts
	 * it in the DB, using the table name set in /control/datahandler/tablename.
	 * For fields that have a m:n table defined in $this->mmFields, a real m:n
	 * relation is created instead of a comma-separated list of foreign keys.
	 *
	 * Currently, this function can only insert new records, but not update
	 * existing records yet.
	 *
	 * This function has been copied from tx_ameosformidabledhdb and extended
	 * for m:n relations.
	 *
	 * @access	public
	 */
	function _doTheMagic() {
		if ($this->_isFullySubmitted() && $this->_allIsValid()) {
			$aVars = array();
			$aConf = $this->oForm->_navConf('/control/datahandler/');

			if ($this->_isSubmitted()) {
				$tablename = $this->oForm->_navConf('/control/datahandler/tablename');
				$keyname = $this->oForm->_navConf('/control/datahandler/keyname');
				$mmInserts = array();

				$aRs = array();

				$aFormData = $this->_getFormDataManaged();

				reset($aFormData);
				while (list($elementname, $value) = each($aFormData)) {
					if (array_key_exists($elementname, $this->oForm->aORenderlets)) {
						$oRenderlet = $this->oForm->aORenderlets[$elementname];
						// Do we have a field that should create a m:n relation?
						if (isset($this->mmFields[$elementname])) {
							if ($value && count($value)) {
								$aRs[$elementname] = count($value);
								foreach ($value as $currentValue) {
									$mmInserts[] = array(
										'table' => $this->mmFields[$elementname],
										'data' => array(
											// use the default sorting
											'sorting' => 1,
											'uid_foreign' => intval($currentValue)
										)
									);
								}
							} else {
								$aRs[$elementname] = 0;
							}
						} else {
							$aRs[$elementname] = $oRenderlet->_fromRenderletToDataHandler($value, $oRenderlet->aElement);
						}
					}
				}

				if (!empty($tablename) && !empty($keyname)) {
					$db = $GLOBALS['TYPO3_DB'];

					$editEntry = $this->_currentEntryId();

					if ($editEntry) {
						$this->oForm->_debug($aRs, 'EXECUTION OF DATAHANDLER MMDB - EDITION MODE in ' . $tablename . '[' . $keyname . '=' . $editEntry . ']');
						$db->store_lastBuiltQuery = TRUE;
						$db->exec_UPDATEquery($tablename, $keyname . '="' . $editEntry . '"', $aRs);
						$this->oForm->_debug($db->debug_lastBuiltQuery, 'DATAHANDLER MMDB - SQL EXECUTED');

						// remove all old m:n records
						$db->store_lastBuiltQuery = FALSE;

						// remove all old m:n records
						foreach ($this->mmFields as $currentTable) {
							$db->exec_DELETEquery(
								$currentTable,
								'uid_local='.$editEntry
							);
						}

						// create all new m:n records
						if (count($mmInserts)) {
							foreach ($mmInserts as $currentInsert) {
								$currentInsert['data']['uid_local'] = $editEntry;
								$db->exec_INSERTquery($currentInsert['table'], $currentInsert['data']);
							}
						}
					} else {
						$this->oForm->_debug($aRs, 'EXECUTION OF DATAHANDLER MMDB - INSERTION MODE in ' . $tablename);

						$db->store_lastBuiltQuery = TRUE;
						$db->exec_INSERTquery($tablename, $aRs);
						$this->oForm->_debug($db->debug_lastBuiltQuery, 'DATAHANDLER MMDB - SQL EXECUTED');
						$db->store_lastBuiltQuery = FALSE;

						$this->newEntryId = $db->sql_insert_id();
						$this->oForm->_debug('', 'NEW ENTRY ID [' . $keyname . '=' . $this->newEntryId . ']');

						// Create all the m:n records, but only if the main
						// record has been created successfully.
						if (count($mmInserts) && $this->newEntryId) {
							foreach ($mmInserts as $currentInsert) {
								$currentInsert['data']['uid_local'] = $this->newEntryId;
								$db->exec_INSERTquery($currentInsert['table'], $currentInsert['data']);
							}
						}
					}
				} else {
					$oForm->mayday('DATAHANDLER configuration isn\'t correct');
				}
			}
		}
	}

	/**
	 * Retrieves the data of the current record from the DB.
	 *
	 * @return	array		data from the DB as an associative array
	 *
	 * @access	protected
	 */
	function _getStoredData() {
		$result = parent::_getStoredData();

		// deal with data that has m:n relations
		foreach ($this->mmFields as $key => $mmTable) {
			// Do we have any data?
			if (is_array($result[$key]) && $result[$key][0]) {
				unset($result[$key]);
				$result[$key] = array();
				$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid_foreign',
					$mmTable,
					'uid_local='.$this->_currentEntryId()
				);
				if ($dbResult) {
					while ($dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
						$result[$key][] = $dbResultRow['uid_foreign'];
					}
				} else {
					unset($result[$key]);
					$result[$key] = 0;
				}
			} else {
				unset($result[$key]);
				$result[$key] = 0;
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/api/class.tx_seminars.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/api/class.tx_seminars.php']);
}

?>
