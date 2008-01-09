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
/**
 * Testcase for the test class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'tests/fixtures/class.tx_seminars_test.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_test_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** UID of the minimal fixture's data in the DB */
	private $fixtureUid = 0;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array(
				'title' => 'Test'
			)
		);
		$this->fixture = new tx_seminars_test($this->fixtureUid);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	////////////////////////////////
	// Tests for creating objects.
	////////////////////////////////

	public function testCreateFromUid() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromUidFailsForInvalidUid() {
		$test = new tx_seminars_test($this->fixtureUid + 99);

		$this->assertFalse(
			$test->isOk()
		);
	}

	public function testCreateFromUidFailsForZeroUid() {
		$test = new tx_seminars_test(0);

		$this->assertFalse(
			$test->isOk()
		);
	}

	public function testCreateFromDbResult() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			SEMINARS_TABLE_TEST,
			'uid = '.$this->fixtureUid
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}

		$test = new tx_seminars_test(0, $dbResult);

		$this->assertTrue(
			$test->isOk()
		);
	}

	public function testCreateFromDbResultFailsForNull() {
		$test = new tx_seminars_test(0, null);

		$this->assertFalse(
			$test->isOk()
		);
	}


	//////////////////////////////////
	// Tests for getting attributes.
	//////////////////////////////////

	public function testGetUid() {
		$this->assertEquals(
			$this->fixtureUid,
			$this->fixture->getUid()
		);
	}

	public function testGetTitle() {
		$this->assertEquals(
			'Test',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////////
	// Tests for setting attributes.
	//////////////////////////////////

	public function testSetAndGetRecordPropertyBoolean() {
		$this->assertFalse(
			$this->fixture->getRecordPropertyBoolean('test')
		);

		$this->fixture->setRecordPropertyBoolean('test', true);
		$this->assertTrue(
			$this->fixture->getRecordPropertyBoolean('test')
		);
	}

	public function testSetAndGetTitle() {
		$title = 'Test';
		$this->fixture->setTitle($title);

		$this->assertEquals(
			$title,
			$this->fixture->getTitle()
		);
	}
}

?>
