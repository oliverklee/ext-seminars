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
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_category.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the category class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_category_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** UID of the fixture's data in the DB */
	private $fixtureUid = 0;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'Test category')
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}

	public function testCreateFromUid() {
		$this->fixture = new tx_seminars_category($this->fixtureUid);

		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromUidFailsForInvalidUid() {
		$this->fixture = new tx_seminars_category($this->fixtureUid + 99);

		$this->assertFalse(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromUidFailsForZeroUid() {
		$this->fixture = new tx_seminars_category(0);

		$this->assertFalse(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromDbResult() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			SEMINARS_TABLE_CATEGORIES,
			'uid = '.$this->fixtureUid
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}

		$this->fixture = new tx_seminars_category(0, $dbResult);

		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromDbResultFailsForNull() {
		$this->fixture = new tx_seminars_category(0, null);

		$this->assertFalse(
			$this->fixture->isOk()
		);
	}

	public function testGetTitle() {
		$this->fixture = new tx_seminars_category($this->fixtureUid);

		$this->assertEquals(
			'Test category',
			$this->fixture->getTitle()
		);
	}
}
?>