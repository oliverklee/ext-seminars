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
 * Testcase for the tx_seminars_BagBuilder_Category class in the "seminars"
 * extension.
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

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_BagBuilder_Category();
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBuilderBuildsABag() {
		$this->assertTrue(
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
		$this->assertEquals(
			2,
			$categoryBag->count()
		);

		$this->assertEquals(
			'Title 1',
			$categoryBag->current()->getTitle()
		);
		$this->assertEquals(
			'Title 2',
			$categoryBag->next()->getTitle()
		);

		$categoryBag->__destruct();
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

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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

		$this->assertEquals(
			1,
			$bag->count()
		);

		$bag->__destruct();
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

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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

		$this->assertEquals(
			2,
			$bag->count()
		);

		$bag->__destruct();
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

		$this->assertFalse(
			$bag->isEmpty()
		);
		$this->assertEquals(
			$categoryUid,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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

		$this->assertEquals(
			1,
			$bag->count()
		);
		$this->assertEquals(
			$categoryUid1,
			$bag->current()->getUid()
		);

		$bag->__destruct();
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

		$this->assertTrue(
			$bag->isEmpty()
		);

		$bag->__destruct();
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
?>