<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Bag_AbstractTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_Bag_Testing
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer the UID of the first test record in the DB
	 */
	private $uidOfFirstRecord = 0;

	/**
	 * @var integer the UID of the second test record in the DB
	 */
	private $uidOfSecondRecord = 0;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->uidOfFirstRecord = $this->testingFramework->createRecord(
			'tx_seminars_test',
			array('title' => 'test 1')
		);
		$this->uidOfSecondRecord = $this->testingFramework->createRecord(
			'tx_seminars_test',
			array('title' => 'test 2')
		);

		$this->fixture = new tx_seminars_tests_fixtures_Bag_Testing('is_dummy_record=1');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic bag functionality.
	///////////////////////////////////////////

	public function testEmptyBagHasNoUids() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');

		$this->assertEquals(
			'', $bag->getUids()
		);

		$bag->__destruct();
	}

	public function testBagCanHaveOneUid() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

		$this->assertEquals(
			(string) $this->uidOfFirstRecord, $bag->getUids()
		);

		$bag->__destruct();
	}

	public function testBagCanHaveTwoUids() {
		$this->assertEquals(
			$this->uidOfFirstRecord.','.$this->uidOfSecondRecord,
			$this->fixture->getUids()
		);
	}

	public function testBagSortsByUidByDefault() {
		$this->assertEquals(
			$this->uidOfFirstRecord,
			$this->fixture->current()->getUid()
		);

		$this->assertEquals(
			$this->uidOfSecondRecord,
			$this->fixture->next()->getUid()
		);
	}


	///////////////////////////
	// Tests concerning count
	///////////////////////////

	public function testCountForEmptyBagReturnsZero() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');

		$this->assertEquals(
			0,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testCountForBagWithOneElementReturnsOne() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid=' . $this->uidOfFirstRecord);

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}

	public function testCountForBagWithTwoElementsReturnsTwo() {
		$this->assertEquals(
			2,
			$this->fixture->count()
		);
	}

	public function testCountAfterCallingNextForBagWithTwoElementsReturnsTwo() {
		$this->fixture->rewind();
		$this->fixture->next();

		$this->assertEquals(
			2,
			$this->fixture->count()
		);
	}

	public function testCountForBagWithTwoMatchesElementsAndLimitOfOneReturnsOne() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('is_dummy_record = 1', '', '', '', 1);

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
	}


	///////////////////////////////////////
	// Tests concerning countWithoutLimit
	///////////////////////////////////////

	public function testCountWithoutLimitForEmptyBagReturnsZero() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');

		$this->assertEquals(
			0,
			$bag->countWithoutLimit()
		);

		$bag->__destruct();
	}

	public function testCountWithoutLimitForBagWithOneElementReturnsOne() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

		$this->assertEquals(
			1,
			$bag->countWithoutLimit()
		);

		$bag->__destruct();
	}

	public function testCountWithoutLimitForBagWithTwoElementsReturnsTwo() {
		$this->assertEquals(
			2,
			$this->fixture->countWithoutLimit()
		);
	}

	public function testCountWithoutLimitAfterCallingNextForBagWithTwoElementsReturnsTwo() {
		$this->fixture->rewind();
		$this->fixture->next();

		$this->assertEquals(
			2,
			$this->fixture->countWithoutLimit()
		);
	}

	public function testCountWithoutLimitForBagWithTwoMatchesElementsAndLimitOfOneReturnsTwo() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('is_dummy_record = 1', '', '', '', 1);

		$this->assertEquals(
			2,
			$bag->countWithoutLimit()
		);

		$bag->__destruct();
	}


	/////////////////////////////
	// Tests concerning isEmpty
	/////////////////////////////

	public function testIsEmptyForEmptyBagReturnsTrue() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1=2');

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testIsEmptyForEmptyBagAfterIteratingReturnsTrue() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');
		foreach ($bag as $item);

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testIsEmptyForBagWithOneElementReturnsFalse() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

		$this->assertFalse(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testIsEmptyForBagWithOneElementAfterIteratingReturnsFalse() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);
		foreach ($bag as $item);

		$this->assertFalse(
			$bag->isEmpty()
		);

		$bag->__destruct();
	}

	public function testIsEmptyForBagWithTwoElementsReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isEmpty()
		);
	}
}