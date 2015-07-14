<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This aggregate class holds a bunch of test objects and allows to iterate over them.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_tests_fixtures_Bag_Testing extends tx_seminars_Bag_Abstract {
	/**
	 * The constructor. Creates a bag that contains test records and allows to iterate over them.
	 *
	 * @param string $queryParameters
	 *        string that will be prepended to the WHERE clause using AND, e.g. 'pid=42'
	 *        (the AND and the enclosing spaces are not necessary for this parameter)
	 * @param string $additionalTableNames
	 *        comma-separated names of additional DB tables used for JOINs, may be empty
	 * @param string $groupBy
	 *        GROUP BY clause (may be empty), must already be safeguarded against SQL injection
	 * @param string $orderBy
	 *        ORDER BY clause (may be empty), must already be safeguarded against SQL injection
	 * @param string $limit
	 *        LIMIT clause (may be empty), must already be safeguarded against SQL injection
	 * @param int $showHiddenRecords
	 *        If $showHiddenRecords is set (0/1), any hidden fields in records are ignored.
	 * @param bool $ignoreTimingOfRecords
	 *        If $ignoreTimingOfRecords is TRUE the timing of records is ignored.
	 */
	public function __construct(
		$queryParameters = '1=1', $additionalTableNames = '', $groupBy = '',
		$orderBy = 'uid', $limit = '', $showHiddenRecords = -1,
		$ignoreTimingOfRecords = FALSE
	) {
		parent::__construct(
			'tx_seminars_test',
			$queryParameters,
			$additionalTableNames,
			$groupBy,
			$orderBy,
			$limit,
			$showHiddenRecords,
			$ignoreTimingOfRecords
		);
	}

	/**
	 * Creates the current item in $this->currentItem, using $this->dbResult
	 * as a source. If the current item cannot be created, $this->currentItem
	 * will be nulled out.
	 *
	 * $this->dbResult must be ensured to be non-NULL when this function is called.
	 *
	 * @return void
	 */
	protected function createItemFromDbResult() {
		$this->currentItem = new tx_seminars_tests_fixtures_OldModel_Testing(0, $this->dbResult);
		$this->valid();
	}
}