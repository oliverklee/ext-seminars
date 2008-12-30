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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Testcase for the seminar bag builder class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_categorybagbuilder_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_categorybagbuilder();
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBuilderBuildsABagChildObject() {
		$this->assertTrue(
			is_subclass_of($this->fixture->build(), 'tx_seminars_bag')
		);
	}

	public function testBuiltBagIsSortedAscendingByTitle() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'Title 2')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
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
	}


	///////////////////////////////////////////////////////////////
	// Test for limiting the bag to categories of certain events.
	///////////////////////////////////////////////////////////////

	public function testSkippingLimitToEventResultsInAllCategories() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_CATEGORIES);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testToLimitEmptyEventUidsResultsInAllCategories() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_CATEGORIES);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToEvents('');

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToZeroEventUidFails() {
		$this->setExpectedException(
			'Exception',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('0');
	}

	public function testLimitToNegativeEventUidFails() {
		$this->setExpectedException(
			'Exception',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('-2');
	}

	public function testLimitToInvalidEventUidAtTheStartFails() {
		$this->setExpectedException(
			'Exception',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('0,1');
	}

	public function testLimitToInvalidEventUidAtTheEndFails() {
		$this->setExpectedException(
			'Exception',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('1,0');
	}

	public function testLimitToInvalidEventUidInTheMiddleFails() {
		$this->setExpectedException(
			'Exception',
			'$eventUids must be a comma-separated list of positive integers.'
		);
		$this->fixture->limitToEvents('1,0,2');
	}

	public function testLimitToEventsCanResultInOneCategory() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToEvents($eventUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventsCanResultInTwoCategoriesForOneEvent() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_CATEGORIES);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid, $categoryUid2
		);

		$this->fixture->limitToEvents($eventUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventsCanResultInTwoCategoriesForTwoEvents() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_CATEGORIES);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid2, $categoryUid2
		);

		$this->fixture->limitToEvents($eventUid1.','.$eventUid2);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToEventsWillExcludeUnassignedCategories() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_CATEGORIES);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid, $categoryUid
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
	}

	public function testLimitToEventsWillExcludeCategoriesOfOtherEvents() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid2, $categoryUid2
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
	}

	public function testLimitToEventsResultsInAnEmptyBagIfThereAreNoMatches() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM, $eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->fixture->limitToEvents($eventUid2);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	//////////////////////////////////
	// Tests for sortByRelationOrder
	//////////////////////////////////

	public function testSortByRelationOrderThrowsExceptionIfLimitToEventsHasNotBeenCalledBefore() {
		$this->setExpectedException(
			'Exception',
			'The event UIDs were empty. This means limitToEvents has not ' .
				'been called. LimitToEvents has to be called before calling ' .
				'this function.'
		);

		$this->fixture->sortByRelationOrder();
	}
}
?>