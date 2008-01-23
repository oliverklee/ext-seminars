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
 * Testcase for the testbag class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'tests/fixtures/class.tx_seminars_testbag.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_testbag_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** the UID of the first test record in the DB */
	private $uidOfFirstRecord = 0;
	/** the UID of the second test record in the DB */
	private $uidOfSecondRecord = 0;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixture = new tx_seminars_testbag();

		$this->uidOfFirstRecord = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'test 1')
		);
		$this->uidOfSecondRecord = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'test 2')
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic bag functionality.
	///////////////////////////////////////////

	public function testBagHasNoElementsWithUnsaturableParameters() {
		$bag = new tx_seminars_testbag('1=2');
		$this->assertEquals(
			0, $bag->getObjectCountWithoutLimit()
		);
	}

	public function testBagCanHaveOneElement() {
		$bag = new tx_seminars_testbag('uid='.$this->uidOfFirstRecord);

		$this->assertEquals(
			1, $bag->getObjectCountWithoutLimit()
		);

		$this->assertNotNull(
			$bag->getCurrent()
		);
		$this->assertTrue(
			$bag->getCurrent()->isOk()
		);

		$this->assertNull(
			$bag->getNext()
		);
	}

	public function testBagCanHaveTwoElements() {
		$bag = new tx_seminars_testbag(
			'uid IN('
			.$this->uidOfFirstRecord.','
			.$this->uidOfSecondRecord.')'
		);

		$this->assertEquals(
			2, $bag->getObjectCountWithoutLimit()
		);

		$this->assertNotNull(
			$bag->getCurrent()
		);
		$this->assertTrue(
			$bag->getCurrent()->isOk()
		);

		$this->assertNotNull(
			$bag->getNext()
		);
		$this->assertNotNull(
			$bag->getCurrent()
		);
		$this->assertTrue(
			$bag->getCurrent()->isOk()
		);

		$this->assertNull(
			$bag->getNext()
		);
	}

	public function testBagSortsByUidByDefault() {
		$bag = new tx_seminars_testbag(
			'uid IN('
			.$this->uidOfFirstRecord.','
			.$this->uidOfSecondRecord.')'
		);

		$this->assertNotNull(
			$bag->getCurrent()
		);
		$this->assertEquals(
			$this->uidOfFirstRecord,
			$bag->getCurrent()->getUid()
		);

		$this->assertNotNull(
			$bag->getNext()
		);
		$this->assertEquals(
			$this->uidOfSecondRecord,
			$bag->getCurrent()->getUid()
		);
	}
}

?>
