<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2007 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_templatehelper.php');

class tx_seminars_objectfromdb extends tx_seminars_templatehelper {
	/** Same as class name */
	var $prefixId = 'tx_seminars_objectfromdb';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_objectfromdb.php';

	/** string with the name of the SQL table this class corresponds to
	 *  (must be set in $this->init()) */
	var $tableName = null;
	/** associative array with the values from/for the DB */
	var $recordData = null;
	/** whether this record already is stored in the DB */
	var $isInDb = false;

	/**
	 * Dummy constructor: Does nothing.
	 *
	 * Child classes MUST do the following:
	 * 1. call $this->init()
	 * 2. set $this->tableName
	 * 3. fill $this->recordData with data
	 *
	 * @access	public
	 */
	function tx_seminars_objectfromdb() {
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
		return;
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
	 * Checks a string element of the record data array for existence and non-emptiness.
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
	 * Checks an integer element of the record data array for existence and non-zeroness.
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
	 * Checks a decimal element of the record data array for existence and value != 0.00.
	 *
	 * @param	string		key of the element to check
	 *
	 * @return	boolean		true if the corresponding field exists and its value is not "0.00".
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

		return;
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
	 * Adds m:n records that are referenced by this record.
	 *
	 * Before this function may be called, $this->recordData['uid'] must be set
	 * correctly.
	 *
	 * @param	string		the name of the m:m table, having the fields uid_local, uid_foreign and sorting, must not be empty
	 * @param	array		array of uids of records from the foreign table to which we should create references
	 *
	 * @access	protected
	 */
	function createMmRecords($mmTable, $references) {
		if (!empty($references)) {
			$sorting = 1;

			foreach ($references as $currentRelation) {
				// We might get unsafe data here, so better be safe.
				$foreignUid = intval($currentRelation);
				if ($foreignUid) {
					$GLOBALS['TYPO3_DB']->exec_INSERTquery(
						$mmTable,
						array(
							'uid_local' => $this->recordData['uid'],
							'uid_foreign' => $foreignUid,
							'sorting' => $sorting
						)
					);
					$sorting++;
				}
			}
		}

		return;
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
					.$enableFields,
				'',
				'',
				'');

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
		return t3lib_pageSelect::enableFields(
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
		$enableFields = $this->retrieveEnableFields($this->tableName, $allowHiddenRecords);

		if ($this->recordExists($uid, $this->tableName, $allowHiddenRecords)) {
		 	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$this->tableName,
				'uid='.intval($uid)
					.$enableFields,
				'',
				'',
				'1');
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']);
}

?>
