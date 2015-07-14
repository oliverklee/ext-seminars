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
 */
class tx_seminars_BagBuilder_CategoryTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BagBuilder_Category
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_BagBuilder_Category();
		$this->fixture->setTestMode();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBuilderBuildsABag() {
		self::assertTrue(
			is_subclass_of($this->fixture->build(), 'tx_seminars_Bag_Abstract')
		);
	}

	public function testBuiltBagIsSortedAscendingByTitle() {
		$this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'Title 2')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'Title 1')
		);

		$categoryBag = $this->fixture->build();
		self::assertEquals(
			2,
			$categoryBag->count()
		);

		self::assertEquals(
			'Title 1',
			$categoryBag->current()->getTitle()
		);
		self::assertEquals(
			'Title 2',
			$categoryBag->next()->getTitle()
		);
	}


	///////////////////////////////////////////////////////////////
	// Test for limiting the bag to categories of certain events.
	///////////////////////////////////////////////////////////////

	public function testSkippingLimitToEventResultsInAllCategories() {
		$this->testingFramework->createRecord('tx_seminars_categories');

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);
		$bag = $this->fixture->build();

		self::assertEquals(
			2,
			$bag->count()
		);
	}

	public function testToLimitEmptyEventUidsResultsInAllCategories() {
		$this->testingFramework->createRecord('tx_seminars_categories');

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->fixture->limitToEvents('');
		$bag = $this->fixture->build();

		self::assertEquals(
			2,
			$bag->count()
		);
	}

	public function testLimitToZeroEventUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('0');
	}

	public function testLimitToNegativeEventUidFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('-2');
	}

	public function testLimitToInvalidEventUidAtTheStartFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('0,1');
	}

	public function testLimitToInvalidEventUidAtTheEndFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('1,0');
	}

	public function testLimitToInvalidEventUidInTheMiddleFails() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('1,0,2');
	}

	public function testLimitToEventsCanResultInOneCategory() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->fixture->limitToEvents($eventUid);
		$bag = $this->fixture->build();

		self::assertEquals(
			1,
			$bag->count()
		);
	}

	public function testLimitToEventsCanResultInTwoCategoriesForOneEvent() {
		$this->testingFramework->createRecord('tx_seminars_categories');

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid2
		);

		$this->fixture->limitToEvents($eventUid);
		$bag = $this->fixture->build();

		self::assertEquals(
			2,
			$bag->count()
		);
	}

	public function testLimitToEventsCanResultInTwoCategoriesForTwoEvents() {
		$this->testingFramework->createRecord('tx_seminars_categories');

		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid2, $categoryUid2
		);

		$this->fixture->limitToEvents($eventUid1.','.$eventUid2);
		$bag = $this->fixture->build();

		self::assertEquals(
			2,
			$bag->count()
		);
	}

	public function testLimitToEventsWillExcludeUnassignedCategories() {
		$this->testingFramework->createRecord('tx_seminars_categories');

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->fixture->limitToEvents($eventUid);
		$bag = $this->fixture->build();

		self::assertFalse(
			$bag->isEmpty()
		);
		self::assertEquals(
			$categoryUid,
			$bag->current()->getUid()
		);
	}

	public function testLimitToEventsWillExcludeCategoriesOfOtherEvents() {
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid2, $categoryUid2
		);

		$this->fixture->limitToEvents($eventUid1);
		$bag = $this->fixture->build();

		self::assertEquals(
			1,
			$bag->count()
		);
		self::assertEquals(
			$categoryUid1,
			$bag->current()->getUid()
		);
	}

	public function testLimitToEventsResultsInAnEmptyBagIfThereAreNoMatches() {
		$this->testingFramework->createRecord(
			'tx_seminars_categories'
		);

		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->fixture->limitToEvents($eventUid2);
		$bag = $this->fixture->build();

		self::assertTrue(
			$bag->isEmpty()
		);
	}


	//////////////////////////////////
	// Tests for sortByRelationOrder
	//////////////////////////////////

	public function testSortByRelationOrderThrowsExceptionIfLimitToEventsHasNotBeenCalledBefore() {
		$this->setExpectedException(
			'BadMethodCallException',
			'The event UIDs were empty. This means limitToEvents has not been called. LimitToEvents has to be called before ' .
				'calling this function.'
		);

		$this->fixture->sortByRelationOrder();
	}
}