<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Class tx_seminars_BagBuilder_Abstract for the "seminars" extension.
 *
 * This builder class creates customized bag objects.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class tx_seminars_BagBuilder_Abstract {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = '';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = '';

	/**
	 * @var array associative array with the WHERE clause parts (will be
	 *            concatenated with " AND " later)
	 */
	protected $whereClauseParts = array();

	/**
	 * @var string the sorting field
	*/
	protected $orderBy = 'uid';

	/**
	 * @var integer the field by which the DB query result should be grouped
	 */
	protected $groupBy = '';

	/**
	 * @var string the number of records to retrieve; leave empty to set
	 *             no limit
	 */
	protected $limit = '';

	/**
	 * @var array array of additional table names for the query
	 */
	protected $additionalTableNames = array();

	/**
	 * @var boolean whether the timing of records should be ignored
	 */
	protected $ignoreTimingOfRecords = FALSE;

	/**
	 * @var boolean whether hidden records should be shown, too
	 */
	protected $showHiddenRecords = FALSE;

	/**
	 * The constructor. Checks that $this->tableName is not empty.
	 */
	public function __construct() {
		if ($this->tableName == '') {
			throw new Exception(
				'The attribute $this->tableName must not be empty.'
			);
		}
	}

	/**
	 * Creates and returns the customized bag.
	 *
	 * @return tx_seminars_Bag_Abstract customized, newly-created bag
	 */
	public function build() {
		return tx_oelib_ObjectFactory::make(
			$this->bagClassName,
			$this->getWhereClause(),
			implode(',', $this->additionalTableNames),
			$this->groupBy,
			$this->orderBy,
			$this->limit,
			($this->showHiddenRecords ? 1 : -1),
			$this->ignoreTimingOfRecords
		);
	}

	/**
	 * Configures the bag to work like a BE list: It will use the default
	 * sorting in the BE, and hidden records will be shown.
	 */
	public function setBackEndMode() {
		$this->showHiddenRecords();
		$this->ignoreTimingOfRecords = TRUE;
	}

	/**
	 * Sets the PIDs of the system folders that contain the records.
	 *
	 * @param string comma-separated list of PIDs of the system folders
	 *               with the records; must not be empty; need not be
	 *               safeguarded against SQL injection
	 * @param integer recursion depth, must be >= 0
	 */
	public function setSourcePages($sourcePagePids, $recursionDepth = 0) {
		if (!preg_match('/^([\d+,] *)*\d+$/', $sourcePagePids)) {
			unset($this->whereClauseParts['pages']);
			return;
		}

		$recursivePidList = tx_oelib_db::createRecursivePageList(
			$sourcePagePids, $recursionDepth
		);

		$this->whereClauseParts['pages'] = $this->tableName . '.pid IN (' .
			$recursivePidList . ')';
	}

	/**
	 * Checks whether some source pages have already been set.
	 *
	 * @return boolean TRUE if source pages have already been set, FALSE
	 *                 otherwise
	 */
	public function hasSourcePages() {
		return isset($this->whereClauseParts['pages']);
	}

	/**
	 * Sets the created bag to only take records into account that have been
	 * created with the oelib testing framework.
	 */
	public function setTestMode() {
		$this->whereClauseParts['tests'] = $this->tableName .
			'.is_dummy_record = 1';
	}

	/**
	 * Returns the WHERE clause for the bag to create.
	 *
	 * The WHERE clause will be complete except for the enableFields additions.
	 *
	 * If the bag does not have any limitations imposed upon, the return value
	 * will be a tautology.
	 *
	 * @return string complete WHERE clause for the bag to create, will
	 *                not be empty
	 */
	public function getWhereClause() {
		if (empty($this->whereClauseParts)) {
			return '1=1';
		}

		return implode(' AND ', $this->whereClauseParts);
	}

	/**
	 * Adds the table name given in the parameter $additionalTableName to
	 * $this->additionalTableNames.
	 *
	 * @param string the table name to add to the additional table names
	 *               array, must not be empty
	 */
	public function addAdditionalTableName($additionalTableName) {
		if ($additionalTableName == '') {
			throw new Exception(
				'The parameter $additionalTableName must not be empty.'
			);
		}

		$this->additionalTableNames[$additionalTableName] = $additionalTableName;
	}

	/**
	 * Removes the table name given in the parameter $additionalTableName from
	 * $this->additionalTableNames.
	 *
	 * @param string the table name to remove from the additional table
	 *               names array, must not be empty
	 */
	public function removeAdditionalTableName($additionalTableName) {
		if ($additionalTableName == '') {
			throw new Exception(
				'The parameter $additionalTableName must not be empty.'
			);
		}

		if (!isset($this->additionalTableNames[$additionalTableName])) {
			throw new Exception(
				'The given additional table name does not exist in the list ' .
					'of additional table names.'
			);
		}

		unset($this->additionalTableNames[$additionalTableName]);
	}

	/**
	 * Sets the ORDER BY statement for the bag to build.
	 *
	 * @param string the ORDER BY statement to set, may be empty
	 */
	public function setOrderBy($orderBy) {
		$this->orderBy = $orderBy;
	}

	/**
	 * Sets the LIMIT statement of the bag to build.
	 *
	 * Examples for the parameter:
	 * - "0, 10" to limit the bag to 10 records, starting from the first record
	 * - "10, 10" to limit the bag to 10 records, starting from the 11th record
	 * - "10" to limit the bag to the first 10 records
	 *
	 * @param string the LIMIT statement to set, may be empty
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
	}

	/**
	 * Configures the bag to also contain hidden records.
	 */
	public function showHiddenRecords() {
		$this->showHiddenRecords = TRUE;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Abstract.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Abstract.php']);
}
?>