<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_objectfromdb.php');

/**
 * Class 'tx_seminars_test' for the 'seminars' extension.
 *
 * This class represents a test object from the database.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_test extends tx_seminars_objectfromdb {
	/** string with the name of the SQL table this class corresponds to */
	protected $tableName = SEMINARS_TABLE_TEST;

	/**
	 * Sets the test field of this record to a boolean value.
	 *
	 * @param boolean the boolean value to set
	 */
	public function setBooleanTest($test) {
		$this->setRecordPropertyBoolean('test', $test);
	}

	/**
	 * Returns true if the test field of this record is set, false otherwise.
	 *
	 * @return boolean true if the test field of this record is set, false
	 * otherwise
	 */
	public function getBooleanTest() {
		return $this->getRecordPropertyBoolean('test');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_test.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_test.php']);
}
?>