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
 * Class 'tx_seminars_bag' for the 'seminars' extension.
 *
 * This aggregate class holds a bunch of objects that are created from
 * the result of an SQL query and allows to iterate over them.
 *
 * This is an abstract class; don't instantiate it.
 *
 * When inheriting from this class, make sure to implement the function
 * createItemFromDbResult.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');

class tx_seminars_bag extends tx_seminars_dbplugin {
	/** Same as class name */
	var $prefixId = 'tx_seminars_seminar_bag';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_bag.php';

	/** the name of the main DB table from which we get the records for this bag */
	var $dbTableName;
	/** the comma-separated names of other DB tables which we need for JOINs */
	var $additionalTableNames;

	/** the ORDER BY clause (without the actual string "ORDER BY") */
	var $orderBy;
	/** the GROUP BY clause (without the actual string "GROUP BY") */
	var $groupBy;
	/** the LIMIT clause (without the actual string "LIMIT") */
	var $limit;

	/** how many objects this bag actually holds with the LIMIT */
	var $objectCountWithLimit;
	/** how many objects this bag would hold without the LIMIT */
	var $objectCountWithoutLimit;

	/** an SQL query result (not converted to an associative array yet) */
	var $dbResult = null;
	/** the current object (may be null) */
	var $currentItem = null;
	/**
	 * string that will be prepended to the WHERE clause using AND, e.g. 'pid=42'
	 * (the AND and the enclosing spaces are not necessary for this parameter)
	 */
	var $queryParameters;
	/**
	 * string that will be prepended to the WHERE clause, making sure that only
	 * enabled and non-deleted records will be processed
	 */
	var $enabledFieldsQuery;

	/**
	 * The constructor. Sets the iterator to the first result of a query
	 *
	 * @param	string		the name of the main DB table to query (comma-separated), may not be empty
	 * @param	string		string that will be prepended to the WHERE clause using AND, e.g. 'pid=42' (the AND and the enclosing spaces are not necessary for this parameter)
	 *						the table name must be used as a prefix if more than one table is queried
	 * @param	string		comma-separated names of additional DB tables used for JOINs, may be empty
	 * @param	string		GROUP BY clause (may be empty), must already by safeguarded against SQL injection
	 * @param	string		ORDER BY clause (may be empty), must already by safeguarded against SQL injection
	 * @param	string		LIMIT clause (may be empty), must already by safeguarded against SQL injection
	 *
	 * @access	public
	 */
	function tx_seminars_bag($dbTableName, $queryParameters = '1', $additionalTableNames = '', $groupBy = '', $orderBy = '', $limit = '') {
		$this->dbTableName = $dbTableName;
		$this->queryParameters = trim($queryParameters);
		$this->additionalTableNames =
			(!empty($additionalTableNames)) ? ', '.$additionalTableNames : '';
		$this->createEnabledFieldsQuery();

		$this->orderBy = $orderBy;
		$this->groupBy = $groupBy;
		$this->limit = $limit;

		$this->init();
		$this->resetToFirst();

		return;
	}

	/**
	 * For the main DB table and the additional tables, writes the corresponding
	 * concatenated output from t3lib_pageSelect::enableFields into
	 * $this->enabledFieldsQuery.
	 *
	 * @access	private
	 */
	function createEnabledFieldsQuery() {
		$allTableNames = explode(',', $this->dbTableName.$this->additionalTableNames);
		$this->enabledFieldsQuery = '';

		foreach ($allTableNames as $currentTableName) {
			$trimmedTableName = trim($currentTableName);
			// Is there a TCA entry for that table?
			$ctrl = $GLOBALS['TCA'][$trimmedTableName]['ctrl'];
			if (is_array($ctrl)) {
				$this->enabledFieldsQuery .= t3lib_pageSelect::enableFields($trimmedTableName);
			}
		}
		return;
	}

	/**
	 * Sets the iterator to the first object, using additional
	 * query parameters from $this->queryParameters for the DB query.
	 * The query works so that the column names are *not*
	 * prefixed with the table name.
	 *
	 * @return	boolean		true if everything went okay, false otherwise
	 *
	 * @access	public
	 */
	function resetToFirst() {
		$result = false;

		// free old results if there are any
		if ($this->dbResult) {
			$GLOBALS['TYPO3_DB']->sql_free_result($this->dbResult);
			// We don't need to null out $this->dbResult as it will be
			// overwritten immediately anyway.
		}

		$this->dbResult =& $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			$this->dbTableName.'.*',
			$this->dbTableName.$this->additionalTableNames,
			$this->queryParameters.$this->enabledFieldsQuery,
			$this->groupBy,
			$this->orderBy,
			$this->limit
		);

		if ($this->dbResult) {
			$this->objectCountWithLimit = $GLOBALS['TYPO3_DB']->sql_num_rows($this->dbResult);
			$dbResultWithoutLimit = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'DISTINCT '.$this->dbTableName.'.*',
				$this->dbTableName.$this->additionalTableNames,
				$this->queryParameters.$this->enabledFieldsQuery
			);
			$this->objectCountWithoutLimit = $GLOBALS['TYPO3_DB']->sql_num_rows($dbResultWithoutLimit);

			$result = (boolean) $this->getNext();
		} else {
			$this->objectCountWithLimit = 0;
			$this->objectCountWithoutLimit = 0;
		}

		return $result;
	}

	/**
	 * Advances to the next event record and returns a reference to that object.
	 *
	 * @return	object		a reference to the now current object
	 *						(may be null if there is no next object)
	 *
	 * @access	public
	 */
	function &getNext() {
		if ($this->dbResult) {
			$this->createItemFromDbResult();
		} else {
			$this->currentItem = null;
		}

		return $this->getCurrent();
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult as a source.
	 * If the current item cannot be created, $this->currentItem will be nulled out.
	 *
	 * $this->dbResult is ensured to be non-null when this function is called.
	 *
	 * @access	protected
	 */
	function createItemFromDbResult() {
		trigger_error('The function tx_seminars_bag::createItemFromDbResult() needs to be implemented in a derived class.');
	}

	/**
	 * Returns the current seminar object (which may be null).
	 *
	 * @return	object		a reference to the current seminar object (may be null if there is no current object)
	 *
	 * @access	public
	 */
	function &getCurrent() {
		return $this->currentItem;
	}

	/**
	 * Checks isOk() and, in case of failure (e.g. there is no more data
	 * from the DB), nulls out $this->currentItem.
	 *
	 * If the function isOk() returns true, nothing is changed.
	 *
	 * @access	protected
	 */
	function checkCurrentItem() {
		// Only test $this->currentItem if it is not null.
		if ($this->currentItem && (!$this->currentItem->isOk())) {
			$this->currentItem = null;
		}

		if ($this->currentItem) {
			// Let warnings from the single records bubble up to us.
			$this->setErrorMessage($this->currentItem->checkConfiguration(true));
		}

		return;
	}

	/**
	 * Retrieves the number of objects this bag would hold if
	 * the LIMIT part of the query would not have been used.
	 *
	 * @return	integer		the total number of objects in this bag (may be zero)
	 *
	 * @access	public
	 */
	function getObjectCountWithoutLimit() {
		return $this->objectCountWithoutLimit;
	}

	/**
	 * Retrieves the total number of objects in this bag
	 * (with having applied the LIMIT part of the query).
	 *
	 * @return	integer		the total number of objects in this bag (may be zero)
	 *
	 * @access	public
	 */
	function getObjectCountWithLimit() {
		return $this->objectCountLimit;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_bag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_bag.php']);
}

?>
