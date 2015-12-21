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
 * This class represents a test object from the database.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_tests_fixtures_OldModel_Testing extends tx_seminars_OldModel_Abstract {
	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = 'tx_seminars_test';

	/**
	 * Sets the test field of this record to a boolean value.
	 *
	 * @param bool $test the boolean value to set
	 *
	 * @return void
	 */
	public function setBooleanTest($test) {
		$this->setRecordPropertyBoolean('test', $test);
	}

	/**
	 * Returns TRUE if the test field of this record is set, FALSE otherwise.
	 *
	 * @return bool TRUE if the test field of this record is set, FALSE
	 *                 otherwise
	 */
	public function getBooleanTest() {
		return $this->getRecordPropertyBoolean('test');
	}

	/**
	 * Adds m:n records that are referenced by this record.
	 *
	 * Before this function may be called, $this->recordData['uid'] must be set
	 * correctly.
	 *
	 * @param string $mmTable the name of the m:n table, having the fields uid_local, uid_foreign and sorting, must not be empty
	 * @param int[] $references array of uids of records from the foreign table to which we should create references, may be empty
	 *
	 * @return int the number of created m:n records
	 */
	public function createMmRecords($mmTable, array $references) {
		return parent::createMmRecords($mmTable, $references);
	}
}