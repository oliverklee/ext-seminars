<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_frontEndEditor.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the tx_seminars_frontEndEditor class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_frontEndEditor_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_frontEndEditor
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_frontEndEditor();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////////
	// Tests concerning populateList
	//////////////////////////////////

	public function testPopulateListWithEmptyArrayForEmptyTableReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->fixture->populateList(array(), 'tx_seminars_test')
		);
	}

	public function testPopulateListWithNonEmptyArrayForEmptyTableReturnsProvidedArray() {
		$originalArray = array(
			1 => array('caption' => 'foo', 'value' => 1),
		);

		$this->assertEquals(
			$originalArray,
			$this->fixture->populateList($originalArray, 'tx_seminars_test')
		);
	}

	public function testPopulateListWithEmptyArrayReturnsSingleRecordInTable() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array(
				$uid => array('caption' => 'foo', 'value' => $uid),
			),
			$this->fixture->populateList(array(), 'tx_seminars_test')
		);
	}

	public function testPopulateListWithEmptyArrayReturnsAllRecordsInTable() {
		$uid1 = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);
		$uid2 = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'bar')
		);

		$this->assertEquals(
			array(
				$uid1 => array('caption' => 'foo', 'value' => $uid1),
				$uid2 => array('caption' => 'bar', 'value' => $uid2),
			),
			$this->fixture->populateList(array(), 'tx_seminars_test')
		);
	}

	public function testPopulateListWithNonEmptyArrayReturnsOriginalArrayPlusSingleRecordInTable() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);
		$fakeUid = $uid + 1;
		$originalArray = array(
			$fakeUid => array('caption' => 'bar', 'value' => $fakeUid),
		);
		$expectedArray = $originalArray;
		$expectedArray[$uid] = array('caption' => 'foo', 'value' => $uid);

		$this->assertEquals(
			$expectedArray,
			$this->fixture->populateList($originalArray, 'tx_seminars_test')
		);
	}

	public function testPopulateListForTableOnlyWithDeletedRecordReturnsEmptyArray() {
		$this->testingFramework->createRecord(
			'tx_seminars_test', array('deleted' => 1)
		);

		$this->assertEquals(
			array(),
			$this->fixture->populateList(array(), 'tx_seminars_test')
		);
	}

	public function testPopulateListWithNonEmptyQueryParameterReturnsMatchingRecord() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array(
				$uid => array('caption' => 'foo', 'value' => $uid),
			),
			$this->fixture->populateList(
				array(), 'tx_seminars_test', 'uid = ' . $uid
			)
		);
	}

	public function testPopulateListWithNonEmptyQueryParameterIgnoresNonMatchingRecord() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array(),
			$this->fixture->populateList(
				array(), 'tx_seminars_test', 'uid = ' . ($uid + 1)
			)
		);
	}

	public function testPopulateListWithAppendBreakFalseNotChangesCaption() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array(
				$uid => array('caption' => 'foo', 'value' => $uid),
			),
			$this->fixture->populateList(
				array(), 'tx_seminars_test', '1=1', false
			)
		);
	}

	public function testPopulateListWithAppendBreakTrueAppendsBreakAtCaption() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array(
				$uid => array('caption' => 'foo<br />', 'value' => $uid),
			),
			$this->fixture->populateList(
				array(), 'tx_seminars_test', '1=1', true
			)
		);
	}

	public function testPopulateListWithFrontEndUserTableUsesNameFieldAsCaptiobn() {
		$uid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'foo')
		);

		$this->assertEquals(
			array($uid => array('caption' => 'foo', 'value' => $uid)),
			$this->fixture->populateList(array(), 'fe_users', 'uid = ' . $uid)
		);
	}

	public function testPopulateListWithOtherTableUsesTitleFieldAsCaptiobn() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array(
				$uid => array('caption' => 'foo', 'value' => $uid),
			),
			$this->fixture->populateList(array(), 'tx_seminars_test')
		);
	}
}
?>