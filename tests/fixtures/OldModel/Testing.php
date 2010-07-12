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
 * Class tx_seminars_tests_fixtures_OldModel_Testing for the "seminars"
 * extension.
 *
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
	 * @param boolean the boolean value to set
	 */
	public function setBooleanTest($test) {
		$this->setRecordPropertyBoolean('test', $test);
	}

	/**
	 * Returns TRUE if the test field of this record is set, FALSE otherwise.
	 *
	 * @return boolean TRUE if the test field of this record is set, FALSE
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
	 * @param string the name of the m:n table, having the fields
	 *               uid_local, uid_foreign and sorting, must not be empty
	 * @param array array of uids of records from the foreign table to
	 *              which we should create references, may be empty
	 *
	 * @return integer the number of created m:n records
	 */
	public function createMmRecords($mmTable, array $references) {
		return parent::createMmRecords($mmTable, $references);
	}
}
?>