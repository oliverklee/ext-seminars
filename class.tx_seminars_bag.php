<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2010 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'tx_seminars_bag' for the 'seminars' extension.
 *
 * This aggregate class holds a bunch of objects that are created from
 * the result of an SQL query and allows to iterate over them.
 *
 * When inheriting from this class, make sure to implement the function
 * createItemFromDbResult.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class tx_seminars_bag implements Iterator {
	/**
	 * @var string the name of the main DB table from which we get the records
	 *             for this bag
	 */
	protected $dbTableName = '';

	/**
	 * @var string the comma-separated names of other DB tables which we need
	 *             for JOINs
	 */
	protected $additionalTableNames = '';

	/**
	 * @var string the ORDER BY clause (without the actual string "ORDER BY")
	 */
	private $orderBy = '';

	/**
	 * @var string the GROUP BY clause (without the actual string "GROUP BY")
	 */
	private $groupBy = '';

	/**
	 * @var string the LIMIT clause (without the actual string "LIMIT")
	 */
	private $limit = '';

	/**
	 * @var boolean whether $this->count has been calculated
	 */
	private $hasCount = FALSE;

	/**
	 * @var integer how many objects this bag contains
	 */
	private $count = 0;

	/**
	 * @var boolean whether $this->$countWithoutLimit has been calculated
	 */
	private $hasCountWithoutLimit = FALSE;

	/**
	 * @var integer how many objects this bag would hold without the LIMIT
	 */
	private $countWithoutLimit = 0;

	/**
	 * @var boolean whether this bag is at the first element
	 */
	private $isRewound = FALSE;

	/**
	 * @var boolean an SQL query result (not converted to an associative array
	 *              yet)
	 */
	protected $dbResult = FALSE;

	/**
	 * @var tx_seminars_objectfromdb the current object (may be null)
	 */
	protected $currentItem = null;

	/**
	 * @var string will be prepended to the WHERE clause using AND, e.g. 'pid=42'
	 *             (the AND and the enclosing spaces are not necessary for this
	 *             parameter)
	 */
	private $queryParameters = '';

	/**
	 * @var string will be prepended to the WHERE clause, making sure that only
	 *             enabled and non-deleted records will be processed
	 */
	private $enabledFieldsQuery = '';

	/**
	 * The constructor. Sets the iterator to the first result of a query
	 *
	 * @param string the name of the main DB table to query (comma-
	 *               separated), may not be empty
	 * @param string string that will be prepended to the WHERE clause
	 *               using AND, e.g. 'pid=42' (the AND and the enclosing
	 *               spaces are not necessary for this parameter)
	 *               the table name must be used as a prefix if more than
	 *               one table is queried
	 * @param string comma-separated names of additional DB tables used
	 *               for JOINs, may be empty
	 * @param string GROUP BY clause (may be empty), must already be
	 *               safeguarded against SQL injection
	 * @param string ORDER BY clause (may be empty), must already be
	 *               safeguarded against SQL injection
	 * @param string LIMIT clause (may be empty), must already be
	 *               safeguarded against SQL injection
	 * @param integer If $showHiddenRecords is set (0/1), any hidden-
	 *               fields in records are ignored.
	 * @param boolean If $ignoreTimingOfRecords is TRUE the timing of
	 *               records is ignored.
	 */
	public function __construct(
		$dbTableName, $queryParameters = '1=1', $additionalTableNames = '',
		$groupBy = '', $orderBy = 'uid', $limit = '', $showHiddenRecords = -1,
		$ignoreTimingOfRecords = FALSE
	) {
		$this->dbTableName = $dbTableName;
		$this->queryParameters = trim($queryParameters);
		$this->additionalTableNames = (!empty($additionalTableNames))
			? ', ' . $additionalTableNames : '';
		$this->createEnabledFieldsQuery(
			$showHiddenRecords, $ignoreTimingOfRecords
		);

		$this->orderBy = $orderBy;
		$this->groupBy = $groupBy;
		$this->limit = $limit;

		$this->rewind();
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->dbResult) {
			$GLOBALS['TYPO3_DB']->sql_free_result($this->dbResult);
		}

		unset($this->dbResult, $this->currentItem);
	}

	/**
	 * For the main DB table and the additional tables, writes the corresponding
	 * concatenated output from tx_oelib_db::enableFields into
	 * $this->enabledFieldsQuery.
	 *
	 * @param integer If $showHiddenRecords is set (0/1), any hidden-
	 *                fields in records are ignored.
	 * @param boolean If $ignoreTimingOfRecords is TRUE the timing of
	 *                records is ignored.
	 */
	private function createEnabledFieldsQuery(
		$showHiddenRecords = -1, $ignoreTimingOfRecords = FALSE
	) {
		$ignoreColumns = array();

		if ($ignoreTimingOfRecords) {
			$ignoreColumns = array(
				'starttime' => TRUE,
				'endtime' => TRUE
			);
		}

		$allTableNames = t3lib_div::trimExplode(
			',',
			$this->dbTableName.$this->additionalTableNames
		);
		$this->enabledFieldsQuery = '';

		foreach ($allTableNames as $currentTableName) {
			// Is there a TCA entry for that table?
			$ctrl = $GLOBALS['TCA'][$currentTableName]['ctrl'];
			if (is_array($ctrl)) {
				$this->enabledFieldsQuery .= tx_oelib_db::enableFields(
						$currentTableName, $showHiddenRecords, $ignoreColumns
				);
			}
		}
	}

	/**
	 * Sets the iterator to the first object, using additional
	 * query parameters from $this->queryParameters for the DB query.
	 * The query works so that the column names are *not*
	 * prefixed with the table name.
	 */
	public function rewind() {
		if ($this->isRewound) {
			return;
		}

		// frees old results if there are any
		if ($this->dbResult) {
			$GLOBALS['TYPO3_DB']->sql_free_result($this->dbResult);
			// We don't need to null out $this->dbResult as it will be
			// overwritten immediately anyway.
		}

		$this->dbResult = tx_oelib_db::select(
			$this->dbTableName . '.*',
			$this->dbTableName . $this->additionalTableNames,
			$this->queryParameters . $this->enabledFieldsQuery,
			$this->groupBy,
			$this->orderBy,
			$this->limit
		);

		$this->createItemFromDbResult();

		$this->isRewound = TRUE;
	}

	/**
	 * Advances to the next record and returns a reference to that object.
	 *
	 * @return tx_seminars_objectfromdb the now current object, will be null if
	 *                                  there is no next object
	 */
	public function next() {
		if (!$this->dbResult) {
			$this->currentItem = null;
			return null;
		}

		$this->createItemFromDbResult();
		$this->isRewound = FALSE;

		return $this->current();
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult as
	 * a source. If the current item cannot be created, $this->currentItem will
	 * be nulled out.
	 *
	 * $this->dbResult is ensured to be not FALSE when this function is called.
	 */
	abstract protected function createItemFromDbResult();

	/**
	 * Returns the current object (which may be null).
	 *
	 * @return tx_seminars_objectfromdb a reference to the current object,
	 * will be null if there is no current
	 * object
	 */
	public function current() {
		return $this->currentItem;
	}

	/**
	 * Checks isOk() and, in case of failure (e.g., there is no more data
	 * from the DB), nulls out $this->currentItem.
	 *
	 * If the function isOk() returns TRUE, nothing is changed.
	 *
	 * @return boolean TRUE if the current item is valid, FALSE otherwise
	 */
	public function valid() {
		if (!$this->currentItem || !$this->currentItem->isOk()) {
			$this->currentItem = null;
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Returns the UID of the current item.
	 *
	 * @return integer the UID of the current item, will be > 0
	 */
	public function key() {
		if (!$this->valid()) {
			throw new Exception('The current item is not valid.');
		}

		return $this->current()->getUid();
	}

	/**
	 * Retrieves the number of objects this bag contains.
	 *
	 * Note: This function might rewind().
	 *
	 * @return integer the total number of objects in this bag, may be zero
	 */
	public function count() {
		if ($this->hasCount) {
			return $this->count;
		}
		if ($this->isEmpty()) {
			return 0;
		}

		$this->count = $GLOBALS['TYPO3_DB']->sql_num_rows($this->dbResult);
		$this->hasCount = TRUE;

		return $this->count;
	}

	/**
	 * Retrieves the number of objects this bag would hold if the LIMIT part of
	 * the query would not have been used.
	 *
	 * @return integer the total number of objects in this bag without any
	 *                 limit, may be zero
	 */
	public function countWithoutLimit() {
		if ($this->hasCountWithoutLimit) {
			return $this->countWithoutLimit;
		}

		$dbResultRow = tx_oelib_db::selectSingle(
			'COUNT(*) AS number ',
			$this->dbTableName . $this->additionalTableNames,
			$this->queryParameters . $this->enabledFieldsQuery
		);

		$this->countWithoutLimit = $dbResultRow['number'];
		$this->hasCountWithoutLimit = TRUE;

		return $this->countWithoutLimit;
	}

	/**
	 * Checks whether this bag is empty.
	 *
	 * Note: This function might rewind().
	 *
	 * @return boolean TRUE if this bag is empty, FALSE otherwise
	 */
	public function isEmpty() {
		if ($this->hasCount) {
			return ($this->count == 0);
		}

		$this->rewind();
		$isEmpty = !is_object($this->current());
		if ($isEmpty) {
			$this->count = 0;
			$this->hasCount = TRUE;
		}

		return $isEmpty;
	}

	/**
	 * Gets a comma-separated, sorted list of UIDs of the records in this bag.
	 *
	 * This function will leave the iterator pointing to after the last element.
	 *
	 * @return string comma-separated, sorted list of UIDs of the records in
	 *                this bag, will be an empty string if this bag is empty
	 */
	public function getUids() {
		$uids = array();

		foreach ($this as $currentItem) {
			$uids[] = $currentItem->getUid();
		}

		sort($uids, SORT_NUMERIC);

		return implode(',', $uids);
	}

	/**
	 * Checks whether the current item is okay and returns its error messages
	 * from the configuration check.
	 *
	 * @return string error messages from the configuration check, may be empty
	 */
	public function checkConfiguration() {
		if ($this->current() && $this->current()->isOk()) {
			return $this->current()->checkConfiguration(TRUE);
		}

		return '';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_bag.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_bag.php']);
}
?>