<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
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
	 * Checks whether this object has been properly initialized,
	 * has a non-empty table name set and thus is basically usable.
	 *
	 * @return	boolean		true if the object has been initialized, false otherwise.
	 *
	 * @access	public
	 */
	function isOk() {
		return (($this->recordData !== null) && 
			($this->tableName !== null) && 
			(!empty($this->tableName))
		);
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
		return $this->getRecordPropertyBoolean($key);
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
		return ($this->isOk() && array_key_exists($key, $this->recordData));
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
			$dbResult = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tableName, $this->recordData);
			if ($dbResult) {
				$this->isInDb = true;
				$result = true;
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_objectfromdb.php']);
}
