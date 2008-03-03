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
require_once(t3lib_extMgm::extPath('seminars').'tests/fixtures/class.tx_seminars_testbagbuilder.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

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
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic builder functions.
	///////////////////////////////////////////

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
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePages() {
		$this->fixture->setSourcePages(
			''
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
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

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
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

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresTimedRecordsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('endtime' => mktime() - 1000)
		);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
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
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresDeletedRecordsByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('deleted' => 1)
		);

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
		);
	}

	public function testBuilderIgnoresDeletedRecordsInBackEndMode() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array('deleted' => 1)
		);

		$this->fixture->setBackEndMode();

		$this->assertEquals(
			0,
			$this->fixture->build()->getObjectCountWithoutLimit()
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

}

?>
