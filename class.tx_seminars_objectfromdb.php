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
 * Class 'tx_seminars_objectfromdb' for the 'seminars' extension.
 *
 * This class represents an object that is created from a DB record
 * or can be written to a DB record.
 *
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * This is an abstract class; don't instantiate it.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');

class tx_seminars_objectfromdb extends tx_seminars_templatehelper {
	/** string with the name of the SQL table this class corresponds to */
	var $tableName = '';
	/** associative array with the values from/for the DB */
	var $recordData = array();
	/** whether this record already is stored in the DB */
	var $isInDb = false;

	/**
	 * The constructor. Creates a test instance from a DB record.
	 *
	 * @param	integer		The UID of the record to retrieve from the DB. This
	 * 						parameter will be ignored if $dbResult is provided.
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 						If this parameter is provided, $uid will be
	 * 						ignored.
	 *
	 * @access	public
	 */
	function tx_seminars_objectfromdb($uid, $dbResult = null) {
		$this->init();
		$this->retrieveRecordAndGetData($uid, $dbResult);
	}

	/**
	 * Retrieves this record's data from the DB (if it has not been retrieved
	 * yet) and gets the record data from the DB result.
	 *
	 * @param	integer		The UID of the record to retrieve from the DB. This
	 * 						parameter will be ignored if $dbResult is provided.
	 * @param	pointer		MySQL result pointer (of SELECT query)/DBAL object.
	 * 						If this parameter is provided, $uid will be ignored.
	 * @param	boolean		whether it is possible to create an object from a
	 * 						hidden record
	 *
	 * @access	protected
	 */
	function retrieveRecordAndGetData(
		$uid, $dbResult = null, $allowHiddenRecords = false
	) {
		if (!$dbResult) {
			$dbResult = $this->retrieveRecord($uid, $allowHiddenRecords);
		}

		if ($dbResult && $GLOBALS['TYPO3_DB']->sql_num_rows($dbResult)) {
			$this->getDataFromDbResult(
				$GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)
			);
		}
	}

	/**
	 * Reads the record data from an DB query result represented as an
	 * associative array and stores it in $this->recordData.
	 * The column names will be used as array keys.
	 * The column names must *not* be prefixed with the table name.
	 *
	 * Before this function may be called, $this->tableName must be set
	 * to the correspondonding DB table name.
	 *
	 * If at least one element is taken, this function sets $this->isInDb to true.
	 *
	 * Example:
	 * $dbResultRow['name'] => $this->recordData['name']
	 *
	 * @param	array		associative array of a DB query result
	 *
	 * @access	protected
	 */
	function getDataFromDbResult($dbResultRow) {
		if (!empty($this->tableName) && !empty($dbResultRow)) {
			$this->recordData = $dbResultRow;
			$this->isInDb = true;
		}
	}

	/**
	 * Checks whether this object has been properly initialized,
	 * has a non-empty table name set and thus is basically usable.
	 *
	 * @return	boolean		true if the object has been initialized, false otherwise.
	 *
	 * @access	public
	 */
	function isOk() {
		return (!empty($this->recordData) && !empty($this->tableName));
	}

	/**
	 * Gets a trimmed string element of the record data array.
	 * If the array has not been initialized properly, an empty string is returned instead.
	 *
	 * @param	string		key of the element to return
	 *
	 * @return	string		the corresponding element from the record data array
	 *
	 * @access	protected
	 */
	function getRecordPropertyString($key) {
		$result = $this->hasKey($key)
			? trim($this->recordData[$key]) : '';

		return $result;
	}

	/**
	 * Gets a decimal element of the record data array.
	 * If the array has not been initialized properly, '0.00' is returned instead.
	 *
	 * @param	string		key of the element to return
	 *
	 * @return	string		the corresponding element from the record data array
	 *
	 * @access	protected
	 */
	function getRecordPropertyDecimal($key) {
		$result = $this->hasKey($key)
			? trim($this->recordData[$key]) : '0.00';

		return $result;
	}

	/**
	 * Checks a string element of the record data array for existence and
	 * non-emptiness.
	 *
	 * @param	string		key of the element to check
	 *
	 * @return	boolean		true if the corresponding string exists and is non-empty
	 *
	 * @access	protected
	 */
	function hasRecordPropertyString($key) {
		return ($this->getRecordPropertyString($key) != '');
	}

	/**
	 * Checks an integer element of the record data array for existence and
	 * non-zeroness.
	 *
	 * @param	string		key of the element to check
	 *
	 * @return	boolean		true if the corresponding value exists and is non-zero
	 *
	 * @access	protected
	 */
	function hasRecordPropertyInteger($key) {
		return (boolean) $this->getRecordPropertyInteger($key);
	}

	/**
	 * Checks a decimal element of the record data array for existence and
	 * value != 0.00.
	 *
	 * @param	string		key of the element to check
	 *
	 * @return	boolean		true if the corresponding field exists and its value
	 * 						is not "0.00".
	 *
	 * @access	protected
	 */
	function hasRecordPropertyDecimal($key) {
		return ($this->getRecordPropertyDecimal($key) != '0.00');
	}

	/**
	 * Gets an (intval'ed) integer element of the record data array.
	 * If the array has not been initialized properly, 0 is returned instead.
	 *
	 * @param	string		key of the element to return
	 *
	 * @return	integer		the corresponding element from the record data array
	 *
	 * @access	protected
	 */
	function getRecordPropertyInteger($key) {
		$result = $this->hasKey($key)
			? intval($this->recordData[$key]) : 0;

		return $result;
	}

	/**
	 * Sets an integer element of the record data array (and intvals it).
	 *
	 * @param	string		key of the element to set (must be non-empty)
	 * @param	integer		the value that will be written into the element
	 *
	 * @access	protected
	 */
	function setRecordPropertyInteger($key, $value) {
		if (!empty($key)) {
			$this->recordData[$key] = intval($value);
		}
	}

	/**
	 * Sets a string element of the record data array (and trims it).
	 *
	 * @param	string		key of the element to set (must be non-empty)
	 * @param	string		the value that will be written into the element
	 *
	 * @access	protected
	 */
	function setRecordPropertyString($key, $value) {
		if (!empty($key)) {
			$this->recordData[$key] = trim((string) $value);
		}
	}

	/**
	 * Gets an element of the record data array, converted to a boolean.
	 * If the array has not been initialized properly, false is returned.
	 *
	 * @param	string		key of the element to return
	 *
	 * @return	boolean		the corresponding element from the record data array
	 *
	 * @access	protected
	 */
	function getRecordPropertyBoolean($key) {
		$result = $this->hasKey($key)
			? ((boolean) $this->recordData[$key]) : false;

		return $result;
	}

	/**
	 * Checks whether $this->recordData is initialized at all and
	 * whether a given key exists.
	 *
	 * @param	string		the array key to search for
	 *
	 * @return	boolean		true if $this->recordData has been initialized
	 * 						and the array key exists, false otherwise
	 *
	 * @access	private
	 */
	function hasKey($key) {
		return ($this->isOk() && !empty($key) && isset($this->recordData[$key]));
	}

	/**
	 * Writes this record to the DB.
	 *
	 * The UID of the parent page must be set in $this->recordData['pid'].
	 * (otherwise the record will be created in the root page).
	 *
	 * @return	boolean		true if everything went OK, false otherwise
	 *
	 * @access	protected
	 */
	function commitToDb() {
		$result = false;

		if ($this->isOk()) {
			// We save the current time so that tstamp and crdate will be the same.
			$now = time();
			$this->setRecordPropertyInteger('tstamp', $now);
			if (!$this->isInDb) {
				$this->setRecordPropertyInteger('crdate', $now);
			}

			$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$this->tableName,
				$this->recordData
			);
			if ($dbResult) {
				$this->isInDb = true;
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Adds m:m records that are referenced by this record.
	 *
	 * Before this function may be called, $this->recordData['uid'] must be set
	 * correctly.
	 *
	 * @param	string		the name of the m:m table, having the fields
	 * 						uid_local, uid_foreign and sorting, must not be empty
	 * @param	array		array of uids of records from the foreign table to
	 * 						which we should create references
	 *
	 * @return	integer		the number of created m:m records
	 *
	 * @access	protected
	 */
	function createMmRecords($mmTable, $references) {
		$numberOfCreatedMmRecords = 0;

		if (!empty($references)) {
			$sorting = 256;

			foreach ($references as $currentRelation) {
				// We might get unsafe data here, so better be safe.
				$foreignUid = intval($currentRelation);
				if ($foreignUid) {
					$GLOBALS['TYPO3_DB']->exec_INSERTquery(
						$mmTable,
						array(
							'uid_local' => $this->getUid(),
							'uid_foreign' => $foreignUid,
							'sorting' => $sorting
						)
					);
					$sorting += 256;
					$numberOfCreatedMmRecords++;
				}
			}
		}

		return $numberOfCreatedMmRecords;
	}

	/**
	 * Checks whether a non-deleted and non-hidden record with a given UID exists
	 * in the DB. If the parameter $allowHiddenRecords is set to true, hidden
	 * records will be selected, too.
	 *
	 * This method may be called statically.
	 *
	 * @param	string		string with a UID (need not necessarily be escaped, will be intval'ed)
	 * @param	string		string with the tablename where the UID should be searched for
	 * @param	boolean		whether hidden records should be accepted
	 *
	 * @return	boolean		true if a visible record with that UID exists; false otherwise.
	 *
	 * @access	protected
	 */
	function recordExists($uid, $tableName, $allowHiddenRecords = false) {
		$result = is_numeric($uid) && ($uid);
		$enableFields = tx_seminars_objectfromdb::retrieveEnableFields(
			$tableName,
			$allowHiddenRecords
		);

		if ($result && !empty($tableName)) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*) AS num',
				$tableName,
				'uid='.intval($uid)
					.$enableFields
			);

			if ($dbResult) {
				$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$result = ($dbResultAssoc['num'] == 1);
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}

		return (boolean) $result;
	}

	/**
	 * Returns additional parameters for an SQL query. They depend on the
	 * table name and whether hidden records should be selected, too.
	 *
	 * The returned string will always start with " AND" so that it can
	 * simply beeing attached to an existing SQL Query.
	 *
	 * This function can be called statically.
	 *
	 * @param	string		the name of the database table
	 * @param	boolean		whether hidden records are allowed
	 *
	 * @return	string		the additional query parameters that need to be added to a SQL query
	 *
	 * @access	public
	 */
	function retrieveEnableFields($tableName, $allowHiddenRecords = false) {
		// The second parameter for the enableFields() function controls
		// whether hidden records should be ignored.
		return $this->enableFields(
			$tableName,
			intval($allowHiddenRecords)
		);
	}

	/**
	 * Retrieves a record from the database.
	 *
	 * The record is retrieved from $this->tableName. Therefore $this->tableName
	 * has to be set before calling this method.
	 *
	 * @param	integer		The UID of the record to retrieve from the DB.
	 * @param	boolean		whether to allow hidden records
	 *
	 * @return	pointer		MySQL result pointer (of SELECT query)/DBAL object, null if the UID is invalid
	 *
	 * @access	protected
	 */
	function retrieveRecord($uid, $allowHiddenRecords = false) {
		$enableFields = $this->retrieveEnableFields(
			$this->tableName,
			$allowHiddenRecords
		);

		if ($this->recordExists($uid, $this->tableName, $allowHiddenRecords)) {
		 	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$this->tableName,
				'uid='.intval($uid)
					.$enableFields,
				'',
				'',
				'1'
			);
	 	} else {
	 		$result = null;
	 	}

		return $result;
	}

	/**
	 * Gets our UID.
	 *
	 * @return	integer		our UID (or 0 if there is an error)
	 *
	 * @access	public
	 */
	function getUid() {
		return $this->getRecordPropertyInteger('uid');
	}

	/**
	 * Gets our title.
	 *
	 * @return	string		our title (or '' if there is an error)
	 *
	 * @access	public
	 */
	function getTitle() {
		return $this->getRecordPropertyString('title');
	}

	/**
	 * Gets our PID.
	 *
	 * @return	integer		our PID (or 0 if there is an error)
	 *
	 * @access	public
	 */
	function getCurrentBePageId() {
		$result = parent::getCurrentBePageId();

		if (!$result) {
			$result = $this->getRecordPropertyInteger('pid');
		}

		return $result;
	}

	/**
	 * Gets an HTML image tag with the URL of the icon file of the record as
	 * configured in TCA.
	 *
	 * @return	integer		our HTML image tag with the URL of the icon file of
	 * 						the record or a "not found" icon if there's no icon
	 * 						for this record
	 *
	 * @access	public
	 */
	function getRecordIcon() {
		global $BACK_PATH;

		$result = '';
		$imageURL = '';
		$enableFields = array();

		t3lib_div::loadTCA($this->tableName);
		$tableConfiguration =& $GLOBALS['TCA'][$this->tableName]['ctrl'];

		$hiddenColumn = $tableConfiguration['enablecolumns']['disabled'];
		$startTimeColumn = $tableConfiguration['enablecolumns']['starttime'];
		$endTimeColumn = $tableConfiguration['enablecolumns']['endtime'];

		// Checks if there are enable columns configured in TCA and sends them
		// as parameter to t3lib_iconworks::getIcon().
		if ($this->getRecordPropertyBoolean($hiddenColumn)) {
			$enableFields[$hiddenColumn] = $this->getRecordPropertyInteger(
				$hiddenColumn
			);
		}
		if ($this->hasRecordPropertyInteger($startTimeColumn)) {
			$enableFields[$startTimeColumn] = $this->getRecordPropertyInteger(
				$startTimeColumn
			);
		}
		if ($this->hasRecordPropertyInteger($endTimeColumn)) {
			$enableFields[$endTimeColumn] = $this->getRecordPropertyInteger(
				$endTimeColumn
			);
		}

		$imageURL = $BACK_PATH.t3lib_iconworks::getIcon(
			$this->tableName, $enableFields
		);

		return '<img src="'.$imageURL.'" title="id='.$this->getUid()
			.'" alt="'.$this->getUid().'" />';
	}

	/**
	 * Commits the changes of an record to the database.
	 *
	 * @param	array	an associative array with the keys being the field names
	 * 					and the value being the field values
	 *
	 * @access	public
	 */
	function saveToDatabase($updateArray) {
		if (count($updateArray)) {
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$this->tableName,
				'uid='.$this->getUid(),
				$updateArray
			);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']);
}

?>
