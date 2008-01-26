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
 * Testcase for the seminar bag builder class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbagbuilder.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_seminarbagbuilder_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_seminarbagbuilder();
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBuilderBuildsABagChildObject() {
		$this->assertTrue(
			is_subclass_of($this->fixture->build(), 'tx_seminars_bag')
		);
	}

	public function testBuilderIgnoresHiddenEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('hidden' => 1)
		);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsHiddenEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('hidden' => 1)
		);

		$this->fixture->setBackEndMode();

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresTimedEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => mktime() - 1000)
		);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsTimedEventsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('endtime' => mktime() - 1000)
		);

		$this->fixture->setBackEndMode();

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	///////////////////////////////////////////////////////////////
	// Test for limiting the bag to events in certain categories.
	///////////////////////////////////////////////////////////////

	public function testSkippingLimitToCategoryResultsInAllEvents() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToZeroCategoryUidResultsInAllEvents() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory(0);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToNegativeCategoryUidResultsInAllEvents() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory(-2);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryCanResultInOneEvent() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryCanResultInTwoEvents() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid2, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);

		$this->assertEquals(
			2,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testLimitToCategoryWillExcludeUnassignedEvents() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_SEMINARS);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->fixture->limitToCategory($categoryUid);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->getObjectCountWithoutLimit()
		);
		$this->assertEquals(
			$eventUid,
			$bag->getCurrent()->getUid()
		);
	}

	public function testLimitToCategoryWillExcludeEventsOfOtherCategories() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid2, $categoryUid2
		);

		$this->fixture->limitToCategory($categoryUid1);
		$bag = $this->fixture->build();

		$this->assertEquals(
			1,
			$bag->getObjectCountWithoutLimit()
		);
		$this->assertEquals(
			$eventUid1,
			$bag->getCurrent()->getUid()
		);
	}

	public function testLimitToCategoryResultsInAnEmptyBagIfThereAreNoMatches() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES
		);

		$this->fixture->limitToCategory($categoryUid2);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}


	/////////////////////////////////////
	// Test concerning canceled events.
	/////////////////////////////////////

	public function testBuilderFindsCanceledEventsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents(true);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsDisabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents(false);

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderFindsCanceledEventsWithHideCanceledEventsEnabledThenDisabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents(true);
		$this->fixture->ignoreCanceledEvents(false);

		$this->assertEquals(
			1,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresCanceledEventsWithHideCanceledDisabledThenEnabled() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('cancelled' => 1)
		);

		$this->fixture->ignoreCanceledEvents(false);
		$this->fixture->ignoreCanceledEvents(true);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}
}

?>
