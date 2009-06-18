<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

// In the back end, include the extension's locallang.xml.
if ((TYPO3_MODE == 'BE') && is_object($LANG)) {
    $LANG->includeLLFile('EXT:seminars/locallang.xml');
}

/**
 * Class 'tx_seminars_objectfromdb' for the 'seminars' extension.
 *
 * This class represents an object that is created from a DB record
 * or can be written to a DB record.
 *
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class tx_seminars_objectfromdb extends tx_oelib_templatehelper {
	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * @var string the charset that is used for the output
	 */
	protected $renderCharset = 'utf-8';

	/**
	 * @var t3lib_cs helper for charset conversion
	 */
	protected $charsetConversion = null;

	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = '';

	/**
	 * @var array the values from/for the DB
	 */
	protected $recordData = array();

	/**
	 * @var boolean whether this record already is stored in the DB
	 */
	protected $isInDb = false;

	/**
	 * The constructor. Creates a test instance from a DB record.
	 *
	 * @param integer The UID of the record to retrieve from the DB. This
	 *                parameter will be ignored if $dbResult is provided.
	 * @param pointer MySQL result pointer (of SELECT query)/DBAL object.
	 *                If this parameter is provided, $uid will be
	 *                ignored.
	 */
	public function __construct(
		$uid, $dbResult = null, $allowHiddenRecords = false
	) {
		$this->initializeCharsetConversion();
		$this->retrieveRecordAndGetData($uid, $dbResult, $allowHiddenRecords);
		$this->init();
	}

	/**
	 * The destructor.
	 */
	public function __destruct() {
		unset($this->charsetConversion);
		parent::__destruct();
	}

	/**
	 * Retrieves this record's data from the DB (if it has not been retrieved
	 * yet) and gets the record data from the DB result.
	 *
	 * @param integer The UID of the record to retrieve from the DB. This
	 *                parameter will be ignored if $dbResult is provided.
	 * @param pointer MySQL result pointer (of SELECT query)/DBAL object.
	 *                If this parameter is provided, $uid will be ignored.
	 * @param boolean whether it is possible to create an object from a
	 *                hidden record
	 */
	protected function retrieveRecordAndGetData(
		$uid, $dbResult = false, $allowHiddenRecords = false
	) {
		if (!$dbResult) {
			$dbResult = $this->retrieveRecord($uid, $allowHiddenRecords);
		}

		if ($dbResult) {
			$data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			if ($data) {
				$this->getDataFromDbResult($data);
			}
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
	 * @param array associative array of a DB query result
	 */
	protected function getDataFromDbResult(array $dbResultRow) {
		if (!empty($this->tableName) && !empty($dbResultRow)) {
			$this->recordData = $dbResultRow;
			$this->isInDb = true;
		}
	}

	/**
	 * Checks whether this object has been properly initialized,
	 * has a non-empty table name set and thus is basically usable.
	 *
	 * @return boolean true if the object has been initialized, false otherwise
	 */
	public function isOk() {
		return (!empty($this->recordData) && !empty($this->tableName));
	}

	/**
	 * Gets a trimmed string element of the record data array.
	 * If the array has not been initialized properly, an empty string is
	 * returned instead.
	 *
	 * @param string key of the element to return
	 *
	 * @return string the corresponding element from the record data array
	 */
	public function getRecordPropertyString($key) {
		$result = $this->hasKey($key)
			? trim($this->recordData[$key]) : '';

		return $result;
	}

	/**
	 * Gets a decimal element of the record data array.
	 * If the array has not been initialized properly, '0.00' is returned
	 * instead.
	 *
	 * @param string key of the element to return
	 *
	 * @return string the corresponding element from the record data array
	 */
	public function getRecordPropertyDecimal($key) {
		$result = $this->hasKey($key)
			? trim($this->recordData[$key]) : '0.00';

		return $result;
	}

	/**
	 * Checks a string element of the record data array for existence and
	 * non-emptiness.
	 *
	 * @param string key of the element to check
	 *
	 * @return boolean true if the corresponding string exists and is non-empty
	 */
	public function hasRecordPropertyString($key) {
		return ($this->getRecordPropertyString($key) != '');
	}

	/**
	 * Checks an integer element of the record data array for existence and
	 * non-zeroness.
	 *
	 * @param string key of the element to check
	 *
	 * @return boolean true if the corresponding value exists and is non-zero
	 */
	public function hasRecordPropertyInteger($key) {
		return (boolean) $this->getRecordPropertyInteger($key);
	}

	/**
	 * Checks a decimal element of the record data array for existence and
	 * value != 0.00.
	 *
	 * @param string key of the element to check
	 *
	 * @return boolean true if the corresponding field exists and its value
	 *                 is not "0.00".
	 */
	public function hasRecordPropertyDecimal($key) {
		return ($this->getRecordPropertyDecimal($key) != '0.00');
	}

	/**
	 * Gets an (intval'ed) integer element of the record data array.
	 * If the array has not been initialized properly, 0 is returned instead.
	 *
	 * @param string key of the element to return
	 *
	 * @return integer the corresponding element from the record data array
	 */
	public function getRecordPropertyInteger($key) {
		$result = $this->hasKey($key)
			? intval($this->recordData[$key]) : 0;

		return $result;
	}

	/**
	 * Sets an integer element of the record data array (and intvals it).
	 *
	 * @param string key of the element to set (must be non-empty)
	 * @param integer the value that will be written into the element
	 */
	protected function setRecordPropertyInteger($key, $value) {
		if (!empty($key)) {
			$this->recordData[$key] = intval($value);
		}
	}

	/**
	 * Sets a string element of the record data array (and trims it).
	 *
	 * @param string key of the element to set (must be non-empty)
	 * @param string the value that will be written into the element
	 */
	protected function setRecordPropertyString($key, $value) {
		if (!empty($key)) {
			$this->recordData[$key] = trim((string) $value);
		}
	}

	/**
	 * Sets a boolean element of the record data array.
	 *
	 * @param string key of the element to set (must be non-empty)
	 * @param boolean the value that will be written into the element
	 */
	protected function setRecordPropertyBoolean($key, $value) {
		if (!empty($key)) {
			$this->recordData[$key] = (boolean) $value;
		}
	}

	/**
	 * Gets an element of the record data array, converted to a boolean.
	 * If the array has not been initialized properly, false is returned.
	 *
	 * @param string key of the element to return
	 *
	 * @return boolean the corresponding element from the record data array
	 */
	public function getRecordPropertyBoolean($key) {
		$result = $this->hasKey($key)
			? ((boolean) $this->recordData[$key]) : false;

		return $result;
	}

	/**
	 * Checks whether $this->recordData is initialized at all and
	 * whether a given key exists.
	 *
	 * @param string the array key to search for
	 *
	 * @return boolean true if $this->recordData has been initialized
	 *                 and the array key exists, false otherwise
	 */
	private function hasKey($key) {
		return ($this->isOk() && !empty($key) && isset($this->recordData[$key]));
	}

	/**
	 * Writes this record to the DB.
	 *
	 * The UID of the parent page must be set in $this->recordData['pid'].
	 * (otherwise the record will be created in the root page).
	 *
	 * @return boolean true if everything went OK, false otherwise
	 */
	public function commitToDb() {
		if (!$this->isOk()) {
			return false;
		}

		// Saves the current time so that tstamp and crdate will be the same.
		$now = time();
		$this->setRecordPropertyInteger('tstamp', $now);

		if (!$this->isInDb || !$this->hasUid()) {
			$this->setRecordPropertyInteger('crdate', $now);
			$this->isInDb = (boolean) $GLOBALS['TYPO3_DB']->exec_INSERTquery(
				$this->tableName,
				$this->recordData
			);
		} else {
			$this->isInDb = (boolean) $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$this->tableName,
				'uid='.$this->getUid(),
				$this->recordData
			);
		}

		return $this->isInDb;
	}

	/**
	 * Adds m:n records that are referenced by this record.
	 *
	 * Before this function may be called, $this->recordData['uid'] must be set
	 * correctly.
	 *
	 * @param string the name of the m:n table, having the fields
	 *               uid_local, uid_foreign and sorting, must not be empty
	 * @param array array of uids of records from the foreign table to
	 *              which we should create references, may be empty
	 *
	 * @return integer the number of created m:n records
	 */
	protected function createMmRecords($mmTable, array $references) {
		if ($mmTable == '') {
			throw new Exception('$mmTable must not be empty.');
		}
		if (!$this->hasUid()) {
			throw new Exception(
				'createMmRecords may only be called on objects that have a UID.'
			);
		}
		if (empty($references)) {
			return 0;
		}

		$numberOfCreatedMmRecords = 0;
		$isDummyRecord = $this->getRecordPropertyBoolean('is_dummy_record');

		$sorting = 1;

		foreach ($references as $currentRelationUid) {
			// We might get unsafe data here, so better be safe.
			$foreignUid = intval($currentRelationUid);
			if ($foreignUid > 0) {
				$dataToInsert = array(
					'uid_local' => $this->getUid(),
					'uid_foreign' => $foreignUid,
					'sorting' => $sorting,
					'is_dummy_record' => $isDummyRecord
				);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					$mmTable, $dataToInsert
				);
				$sorting++;
				$numberOfCreatedMmRecords++;
			}
		}

		return $numberOfCreatedMmRecords;
	}

	/**
	 * Checks whether a non-deleted record with a given UID exists in the DB.
	 *
	 * If the parameter $allowHiddenRecords is set to true, hidden records will
	 * be selected, too.
	 *
	 * This method may be called statically.
	 *
	 * @param string string with a UID (need not necessarily be escaped, will be
	 *               intvaled)
	 * @param string string with the tablename where the UID should be searched
	 *               for
	 * @param boolean whether hidden records should be found as well
	 *
	 * @return boolean true if a visible record with that UID exists, false
	 *                 otherwise
	 */
	public function recordExists($uid, $tableName, $allowHiddenRecords = false) {
		$result = is_numeric($uid) && ($uid);

		if ($result && !empty($tableName)) {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*) AS num',
				$tableName,
				'uid=' . intval($uid) . tx_oelib_db::enableFields(
					$tableName, intval($allowHiddenRecords)
				)
			);

			if ($dbResult) {
				$dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$result = ($dbResultAssoc['num'] == 1);
				$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}

		return (boolean) $result;
	}

	/**
	 * Retrieves a record from the database.
	 *
	 * The record is retrieved from $this->tableName. Therefore $this->tableName
	 * has to be set before calling this method.
	 *
	 * @param integer The UID of the record to retrieve from the DB.
	 * @param boolean whether to allow hidden records
	 *
	 * @return pointer MySQL result pointer (of SELECT query)/DBAL object, false
	 *                 if the UID is invalid
	 */
	protected function retrieveRecord($uid, $allowHiddenRecords = false) {
		if ($this->recordExists($uid, $this->tableName, $allowHiddenRecords)) {
		 	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'*',
				$this->tableName,
				'uid=' . intval($uid) . tx_oelib_db::enableFields(
					$this->tableName, $allowHiddenRecords
				),
				'',
				'',
				'1'
			);
	 	} else {
	 		$result = false;
	 	}

		return $result;
	}

	/**
	 * Gets our UID.
	 *
	 * @return integer our UID (or 0 if there is an error)
	 */
	public function getUid() {
		return $this->getRecordPropertyInteger('uid');
	}

	/**
	 * Checks whether this object has a UID.
	 *
	 * @return boolean true if this object has a UID, false otherwise
	 */
	public function hasUid() {
		return $this->hasRecordPropertyInteger('uid');
	}

	/**
	 * Gets our title.
	 *
	 * @return string our title (or '' if there is an error)
	 */
	public function getTitle() {
		return $this->getRecordPropertyString('title');
	}

	/**
	 * Sets the title element of the record data array.
	 *
	 * @param string the value that will be written into the title element
	 */
	public function setTitle($title) {
		$this->setRecordPropertyString('title', $title);
	}

	/**
	 * Gets our PID.
	 *
	 * @return integer our PID (or 0 if there is an error)
	 */
	public function getCurrentBePageId() {
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
	 * @return string our HTML image tag with the URL of the icon file of
	 *                the record or a "not found" icon if there's no icon
	 *                for this record
	 */
	public function getRecordIcon() {
		$imageURL = '';
		$iconProperties = array();

		t3lib_div::loadTCA($this->tableName);
		$tableConfiguration =& $GLOBALS['TCA'][$this->tableName]['ctrl'];

		$hiddenColumn = $tableConfiguration['enablecolumns']['disabled'];
		$startTimeColumn = $tableConfiguration['enablecolumns']['starttime'];
		$endTimeColumn = $tableConfiguration['enablecolumns']['endtime'];

		// Checks if there are enable columns configured in TCA and sends them
		// as parameter to t3lib_iconworks::getIcon().
		if ($this->getRecordPropertyBoolean($hiddenColumn)) {
			$iconProperties[$hiddenColumn] = $this->getRecordPropertyInteger(
				$hiddenColumn
			);
		}
		if ($this->hasRecordPropertyInteger($startTimeColumn)) {
			$iconProperties[$startTimeColumn] = $this->getRecordPropertyInteger(
				$startTimeColumn
			);
		}
		if ($this->hasRecordPropertyInteger($endTimeColumn)) {
			$iconProperties[$endTimeColumn] = $this->getRecordPropertyInteger(
				$endTimeColumn
			);
		}
		if (isset($tableConfiguration['typeicon_column'])) {
			$typeIconColumn = $tableConfiguration['typeicon_column'];
			$iconProperties[$typeIconColumn] = $this->getRecordPropertyInteger(
				$typeIconColumn
			);
		}

		$imageURL = $GLOBALS['BACK_PATH'].t3lib_iconworks::getIcon(
			$this->tableName, $iconProperties
		);

		return '<img src="'.$imageURL.'" title="id='.$this->getUid()
			.'" alt="'.$this->getUid().'" />';
	}

	/**
	 * Commits the changes of an record to the database.
	 *
	 * @param array an associative array with the keys being the field names
	 *              and the value being the field values
	 */
	public function saveToDatabase(array $updateArray) {
		if (count($updateArray)) {
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				$this->tableName,
				'uid='.$this->getUid(),
				$updateArray
			);
		}
	}

	/**
	 * Marks this object as a dummy record (when it is written to the DB).
	 */
	public function enableTestMode() {
		$this->setRecordPropertyBoolean('is_dummy_record', true);
	}

	/**
	 * Sets the current charset in $this->renderCharset and the charset
	 * conversion instance in $this->$charsetConversion.
	 */
	protected function initializeCharsetConversion() {
		if (isset($GLOBALS['TSFE'])) {
			$this->renderCharset = $GLOBALS['TSFE']->renderCharset;
			$this->charsetConversion = $GLOBALS['TSFE']->csConvObj;
		} elseif (isset($GLOBALS['LANG'])) {
			$this->renderCharset = $GLOBALS['LANG']->charset;
			$this->charsetConversion = $GLOBALS['LANG']->csConvObj;
		} else {
			throw new Exception(
				'There was neither a front end nor a back end detected.'
			);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']);
}
?>