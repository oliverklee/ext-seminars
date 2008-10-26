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
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_testbagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_brokenTestingBagBuilder.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the seminar bag builder class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_testbagbuilder_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** PID of a dummy system folder */
	private $dummySysFolderPid = 0;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_testbagbuilder();
		$this->fixture->setTestMode();

		$this->dummySysFolderPid
			= $this->testingFramework->createSystemFolder();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

	public function testBuilderThrowsExceptionForEmptyTableName() {
		$this->setExpectedException(
			'Exception', 'The attribute $this->tableName must not be empty.'
		);

		new tx_seminars_brokenTestingBagBuilder();
	}

	public function testBuilderBuildsAnObject() {
		$this->assertTrue(
			is_object($this->fixture->build())
		);
	}

	public function testBuilderBuildsABagChildObject() {
		$this->assertTrue(
			is_subclass_of($this->fixture->build(), 'tx_seminars_bag')
		);
	}

	public function testBuilderBuildsBagSortedAscendingByUid() {
		$eventUid1 = $this->testingFramework->createRecord(SEMINARS_TABLE_TEST);
		$eventUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_TEST);

		$testBag = $this->fixture->build();
		$this->assertEquals(
			2,
			$testBag->count()
		);

		$this->assertEquals(
			$eventUid1,
			$testBag->current()->getUid()
		);
		$this->assertEquals(
			$eventUid2,
			$testBag->next()->getUid()
		);
	}

	public function testBuilderWithAdditionalTableNameDoesNotProduceSqlError() {
		$this->fixture->addAdditionalTableName(SEMINARS_TABLE_SEMINARS);

		$this->fixture->build();
	}


	///////////////////////////////////
	// Tests concerning source pages.
	///////////////////////////////////

	public function testBuilderInitiallyHasNoSourcePages() {
		$this->assertFalse(
			$this->fixture->hasSourcePages()
		);
	}

	public function testBuilderHasSourcePagesWithOnePage() {
		$this->fixture->setSourcePages($this->dummySysFolderPid);

		$this->assertTrue(
			$this->fixture->hasSourcePages()
		);
	}

	public function testBuilderHasSourcePagesWithTwoPages() {
		$this->fixture->setSourcePages(
			$this->dummySysFolderPid.','.($this->dummySysFolderPid + 1)
		);

		$this->assertTrue(
			$this->fixture->hasSourcePages()
		);
	}

	public function testBuilderHasNoSourcePagesWithEvilSql() {
		$this->fixture->setSourcePages(
			'; DROP TABLE '.SEMINARS_TABLE_TEST.';'
		);

		$this->assertFalse(
			$this->fixture->hasSourcePages()
		);
	}

	public function testBuilderSelectsRecordsFromAllPagesByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid)
		);
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePages() {
		$this->fixture->setSourcePages('');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid)
		);
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsFromAllPagesWithEmptyAfterNonEmptySourcePages() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid)
		);
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->fixture->setSourcePages($this->dummySysFolderPid);
		$this->fixture->setSourcePages('');

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePagesAndZeroRecursion() {
		$this->fixture->setSourcePages(
			'', 0
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid)
		);
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePagesAndNonZeroRecursion() {
		$this->fixture->setSourcePages(
			'', 1
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid)
		);
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsFromOnePage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid)
		);
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->fixture->setSourcePages($this->dummySysFolderPid);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsFromTwoPages() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid)
		);
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->fixture->setSourcePages(
			$this->dummySysFolderPid.','.($this->dummySysFolderPid + 1)
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderIgnoresRecordsOnSubpageWithoutRecursion() {
		$subPagePid = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $subPagePid)
		);

		$this->fixture->setSourcePages($this->dummySysFolderPid);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testBuilderSelectsRecordsOnSubpageWithRecursion() {
		$subPagePid = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $subPagePid)
		);

		$this->fixture->setSourcePages($this->dummySysFolderPid, 1);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsOnTwoSubpagesWithRecursion() {
		$subPagePid1 = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $subPagePid1)
		);

		$subPagePid2 = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $subPagePid2)
		);

		$this->fixture->setSourcePages($this->dummySysFolderPid, 1);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderSelectsRecordsOnSubpageFromTwoParentsWithRecursion() {
		$subPagePid1 = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $subPagePid1)
		);

		$parentPid2 = $this->testingFramework->createSystemFolder();
		$subPagePid2 = $this->testingFramework->createSystemFolder($parentPid2);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $subPagePid2)
		);

		$this->fixture->setSourcePages(
			$this->dummySysFolderPid.','.$parentPid2,
			1
		);

		$this->assertEquals(
			2,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderIgnoresRecordsOnSubpageWithTooShallowRecursion() {
		$subPagePid = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$subSubPagePid = $this->testingFramework->createSystemFolder(
			$subPagePid
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('pid' => $subSubPagePid)
		);

		$this->fixture->setSourcePages($this->dummySysFolderPid, 1);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	////////////////////////////////////////////////////////
	// Tests concerning hidden/deleted/timed etc. records.
	////////////////////////////////////////////////////////

	public function testBuilderIgnoresHiddenRecordsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('hidden' => 1)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testBuilderFindsHiddenRecordsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('hidden' => 1)
		);

		$this->fixture->setBackEndMode();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderIgnoresTimedRecordsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('endtime' => mktime() - 1000)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testBuilderFindsTimedRecordsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('endtime' => mktime() - 1000)
		);

		$this->fixture->setBackEndMode();

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testBuilderIgnoresDeletedRecordsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('deleted' => 1)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testBuilderIgnoresDeletedRecordsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('deleted' => 1)
		);

		$this->fixture->setBackEndMode();

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testWhereClauseInitiallyIsNotEmpty() {
		$this->assertNotEquals(
			'',
			$this->fixture->getWhereClause()
		);
	}

	public function testWhereClauseCanSelectPids() {
		$this->fixture->setSourcePages($this->dummySysFolderPid);

		// We're using assertContains here because the PID in the WHERE clause
		// may be prefixed with the table name.
		$this->assertContains(
			'pid IN ('.$this->dummySysFolderPid.')',
			$this->fixture->getWhereClause()
		);
	}


	/////////////////////////////////
	// Test concerning limitToTitle
	/////////////////////////////////

	public function testLimitToTitleFindsRecordWithThatTitle() {
		$this->fixture->limitToTitle('foo');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'foo')
		);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToTitleIgnoresRecordWithOtherTitle() {
		$this->fixture->limitToTitle('foo');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'bar')
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	///////////////////////////////////////////////////
	// Test concerning the combination of limitations
	///////////////////////////////////////////////////

	public function testLimitToTitleAndPagesFindsRecordThatMatchesBoth() {
		$this->fixture->setSourcePages($this->dummySysFolderPid);
		$this->fixture->limitToTitle('foo');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'foo', 'pid' => $this->dummySysFolderPid)
		);

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}

	public function testLimitToTitleAndPagesExcludesRecordThatMatchesOnlyTheTitle() {
		$this->fixture->setSourcePages($this->dummySysFolderPid);
		$this->fixture->limitToTitle('foo');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToTitleAndPagesExcludesRecordThatMatchesOnlyThePage() {
		$this->fixture->setSourcePages($this->dummySysFolderPid);
		$this->fixture->limitToTitle('foo');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'bar', 'pid' => $this->dummySysFolderPid)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToTitleStillExcludesHiddenRecords() {
		$this->fixture->limitToTitle('foo');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'foo', 'hidden' => 1)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}

	public function testLimitToTitleStillExcludesDeletedRecords() {
		$this->fixture->limitToTitle('foo');
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('title' => 'foo', 'deleted' => 1)
		);

		$this->assertTrue(
			$this->fixture->build()->isEmpty()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning addAdditionalTableName()
	//////////////////////////////////////////////

	public function testAddAdditionalTableNameWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $additionalTableName must not be empty.'
		);

		$this->fixture->addAdditionalTableName('');
	}

	public function testAddAdditionalTableNameWithTableNameAddsAdditionalTableName() {
		$this->fixture->addAdditionalTableName(SEMINARS_TABLE_SEMINARS);

		$this->assertTrue(
			in_array(
				SEMINARS_TABLE_SEMINARS,
				$this->fixture->getAdditionalTableNames()
			)
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning removeAdditionalTableName()
	/////////////////////////////////////////////////

	public function testRemoveAdditionalTableNameWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $additionalTableName must not be empty.'
		);

		$this->fixture->removeAdditionalTableName('');
	}

	public function testRemoveAdditionalTableNameWithNotSetTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The given additional table name does not exist in the list ' .
				'of additional table names.'
		);

		$this->fixture->removeAdditionalTableName(SEMINARS_TABLE_SEMINARS);
	}

	public function testRemoveAdditionalTableNameWithSetTableNameRemovesAdditionalTableName() {
		$this->fixture->addAdditionalTableName(SEMINARS_TABLE_SEMINARS);
		$this->fixture->removeAdditionalTableName(SEMINARS_TABLE_SEMINARS);

		$this->assertFalse(
			in_array(
				SEMINARS_TABLE_SEMINARS,
				$this->fixture->getAdditionalTableNames()
			)
		);
	}


	//////////////////////////////////
	// Tests concerning setOrderBy()
	//////////////////////////////////

	public function testSetOrderByWithOrderBySetsOrderBy() {
		$this->fixture->setOrderBy('field ASC');

		$this->assertEquals(
			'field ASC',
			$this->fixture->getOrderBy()
		);
	}

	public function testSetOrderByWithEmptyStringRemovesOrderBy() {
		$this->fixture->setOrderBy('');

		$this->assertEquals(
			'',
			$this->fixture->getOrderBy()
		);
	}

	public function testSetOrderByWithOrderByActuallySortsTheBag() {
		$this->fixture->setOrderBy('uid DESC');
		$eventUid1 = $this->testingFramework->createRecord(SEMINARS_TABLE_TEST);
		$eventUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_TEST);

		$testBag = $this->fixture->build();
		$this->assertEquals(
			2,
			$testBag->count()
		);

		$this->assertEquals(
			$eventUid2,
			$testBag->current()->getUid()
		);
		$this->assertEquals(
			$eventUid1,
			$testBag->next()->getUid()
		);
	}


	////////////////////////////////
	// Tests concerning setLimit()
	////////////////////////////////

	public function testSetLimitWithNonEmptyLimitSetsLimit() {
		$this->fixture->setLimit('0, 30');

		$this->assertEquals(
			'0, 30',
			$this->fixture->getLimit()
		);
	}

	public function testSetLimitWithEmptyStringRemovesLimit() {
		$this->fixture->setLimit('');

		$this->assertEquals(
			'',
			$this->fixture->getLimit()
		);
	}

	public function testSetLimitWithLimitActuallyLimitsTheBag() {
		$this->testingFramework->createRecord(SEMINARS_TABLE_TEST);
		$this->testingFramework->createRecord(SEMINARS_TABLE_TEST);
		$this->fixture->setLimit('0, 1');

		$this->assertEquals(
			1,
			$this->fixture->build()->count()
		);
	}


	///////////////////////////////////
	// Tests concerning setTestMode()
	///////////////////////////////////

	public function testSetTestModeAddsTheTableNameBeforeIsDummy() {
		$this->assertContains(
			SEMINARS_TABLE_TEST . '.is_dummy_record = 1',
			$this->fixture->getWhereClause()
		);
	}
}
?>