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
	 * @var int the UID of the first test record in the DB
	 */
	private $uidOfFirstRecord = 0;

	/**
	 * @var int the UID of the second test record in the DB
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
	}


	///////////////////////////////////////////
	// Tests for the basic bag functionality.
	///////////////////////////////////////////

	public function testEmptyBagHasNoUids() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');

		self::assertEquals(
			'', $bag->getUids()
		);
	}

	public function testBagCanHaveOneUid() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

		self::assertEquals(
			(string) $this->uidOfFirstRecord, $bag->getUids()
		);
	}

	public function testBagCanHaveTwoUids() {
		self::assertEquals(
			$this->uidOfFirstRecord.','.$this->uidOfSecondRecord,
			$this->fixture->getUids()
		);
	}

	public function testBagSortsByUidByDefault() {
		self::assertEquals(
			$this->uidOfFirstRecord,
			$this->fixture->current()->getUid()
		);

		self::assertEquals(
			$this->uidOfSecondRecord,
			$this->fixture->next()->getUid()
		);
	}


	///////////////////////////
	// Tests concerning count
	///////////////////////////

	public function testCountForEmptyBagReturnsZero() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');

		self::assertEquals(
			0,
			$bag->count()
		);
	}

	public function testCountForBagWithOneElementReturnsOne() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid=' . $this->uidOfFirstRecord);

		self::assertEquals(
			1,
			$bag->count()
		);
	}

	public function testCountForBagWithTwoElementsReturnsTwo() {
		self::assertEquals(
			2,
			$this->fixture->count()
		);
	}

	public function testCountAfterCallingNextForBagWithTwoElementsReturnsTwo() {
		$this->fixture->rewind();
		$this->fixture->next();

		self::assertEquals(
			2,
			$this->fixture->count()
		);
	}

	public function testCountForBagWithTwoMatchesElementsAndLimitOfOneReturnsOne() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('is_dummy_record = 1', '', '', '', 1);

		self::assertEquals(
			1,
			$bag->count()
		);
	}


	///////////////////////////////////////
	// Tests concerning countWithoutLimit
	///////////////////////////////////////

	public function testCountWithoutLimitForEmptyBagReturnsZero() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');

		self::assertEquals(
			0,
			$bag->countWithoutLimit()
		);
	}

	public function testCountWithoutLimitForBagWithOneElementReturnsOne() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

		self::assertEquals(
			1,
			$bag->countWithoutLimit()
		);
	}

	public function testCountWithoutLimitForBagWithTwoElementsReturnsTwo() {
		self::assertEquals(
			2,
			$this->fixture->countWithoutLimit()
		);
	}

	public function testCountWithoutLimitAfterCallingNextForBagWithTwoElementsReturnsTwo() {
		$this->fixture->rewind();
		$this->fixture->next();

		self::assertEquals(
			2,
			$this->fixture->countWithoutLimit()
		);
	}

	public function testCountWithoutLimitForBagWithTwoMatchesElementsAndLimitOfOneReturnsTwo() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('is_dummy_record = 1', '', '', '', 1);

		self::assertEquals(
			2,
			$bag->countWithoutLimit()
		);
	}


	/////////////////////////////
	// Tests concerning isEmpty
	/////////////////////////////

	public function testIsEmptyForEmptyBagReturnsTrue() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1=2');

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testIsEmptyForEmptyBagAfterIteratingReturnsTrue() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('1 = 2');
		/** @var tx_seminars_tests_fixtures_OldModel_Testing $item */
		foreach ($bag as $item);

		self::assertTrue(
			$bag->isEmpty()
		);
	}

	public function testIsEmptyForBagWithOneElementReturnsFalse() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);

		self::assertFalse(
			$bag->isEmpty()
		);
	}

	public function testIsEmptyForBagWithOneElementAfterIteratingReturnsFalse() {
		$bag = new tx_seminars_tests_fixtures_Bag_Testing('uid = ' . $this->uidOfFirstRecord);
		/** @var tx_seminars_tests_fixtures_OldModel_Testing $item */
		foreach ($bag as $item);

		self::assertFalse(
			$bag->isEmpty()
		);
	}

	public function testIsEmptyForBagWithTwoElementsReturnsFalse() {
		self::assertFalse(
			$this->fixture->isEmpty()
		);
	}
}