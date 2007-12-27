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
 * Testcase for the events list class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(PATH_t3lib.'class.t3lib_scbase.php');

require_once(t3lib_extMgm::extPath('oelib').'tests/fixtures/class.tx_oelib_testingframework.php');

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'mod2/class.tx_seminars_eventslist.php');

define('SEMINARS_SYSFOLDER_TITLE', 'tx_seminars unit test page');

class tx_seminars_eventslist_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** PID of a dummy system folder */
	private $dummySysFolderPid = 0;

	/** a BE page object */
	private $page;

	public function setUp() {
		global $BACK_PATH;

		$this->testingFramework
			= new tx_oelib_testingframework('tx_seminars');

		$this->fixture = new tx_seminars_eventslist($this->page);

		$this->createDummySystemFolder();

		$this->page = new t3lib_SCbase();
		$this->page->id = $this->dummySysFolderPid;
		$this->page->pageInfo = array();
		$this->page->pageInfo['uid'] = $this->dummySysFolderPid;

		$this->page->doc = t3lib_div::makeInstance('bigDoc');
		$this->page->doc->backPath = $BACK_PATH;
		$this->page->doc->docType = 'xhtml_strict';
	}

	public function tearDown() {
		$this->deleteDummySystemFolder();
		$this->resetAutoIncrement('pages');
		$this->testingFramework->cleanUp();
		unset($this->page);
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Creates a system folder and stores its PID in $this->dummySysFolderPid.
	 */
	private function createDummySystemFolder() {
		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'pages',
			array(
				'pid' => 0,
				'doktype' => 254,
				'title' => SEMINARS_SYSFOLDER_TITLE
			)
		);

		$this->dummySysFolderPid = $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/**
	 * Deletes the dummy system folder that has been created using
	 * createDummySystemFolder.
	 *
	 * If no such folder has been created, this function is a no-op.
	 */
	private function deleteDummySystemFolder() {
		if ($this->dummySysFolderPid == 0) {
			return;
		}

		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'pages',
			'uid = '.$this->dummySysFolderPid
		);

		$this->dummySysFolderPid = 0;
	}

	/**
	 * Counts the records on the table given by the first parameter $table that
	 * match a given WHERE clause.
	 *
	 * Note: This function has been copied from the oelib unit testing
	 * framework. This function can be removed once the unit testing framework
	 * supports the table "pages" (bug 1418).
	 *
	 * @see	https://bugs.oliverklee.com/show_bug.cgi?id=1418
	 *
	 * @param	string		the name of the table to query, must not be empty
	 * @param	string		the where part of the query, may be empty (all records
	 * 						will be counted in that case)
	 *
	 * @return	integer		the number of records that have been found
	 */
	private function countRecords($table, $whereClause = '1=1') {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS number',
			$table,
			$whereClause
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$row) {
			throw new Exception(
				'There was an error with the result of the database query.'
			);
		}

		return intval($row['number']);
	}

	/**
	 * Resets the auto increment value for a given table to the highest existing
	 * UID + 1. This is required to leave the table in the same status that it
	 * had before adding dummy records.
	 *
	 * Note: This function has been copied from the oelib unit testing
	 * framework. This function can be removed once the unit testing framework
	 * supports the table "pages" (bug 1418).
	 *
	 * @see	https://bugs.oliverklee.com/show_bug.cgi?id=1418
	 *
	 * @param	string		the name of the table on which we're going to reset
	 * 						the auto increment entry, must not be empty
	 */
	private function resetAutoIncrement($table) {
		// Checks whether the current table qualifies for this method. If there
		// is no column "uid" that has the "auto_icrement" flag set, we should not
		// try to reset this inexistent auto increment index to avoid DB errors.
		if (!$this->testingFramework->hasTableColumnUid($table)) {
			return;
		}

		// Searches for the record with the highest UID in this table.
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'SELECT MAX(uid) AS uid FROM '.$table.';'
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$row) {
			throw new Exception(
				'There was an error with the result of the database query.'
			);
		}
		$newAutoIncrementValue = $row['uid'] + 1;

		// Updates the auto increment index for this table. The index will be set
		// to one UID above the highest existing UID.
		$GLOBALS['TYPO3_DB']->sql_query(
			'ALTER TABLE '.$table.' AUTO_INCREMENT='.$newAutoIncrementValue.';'
		);
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testDummySystemFolderHasBeenCreated() {
		$this->assertNotEquals(
			0,
			$this->dummySysFolderPid
		);

		$this->assertNotEquals(
			0,
			$this->countRecords(
				'pages', 'uid='.$this->dummySysFolderPid
			)
		);
	}

	public function testDummySystemFolderCanBeDeleted() {
		$this->deleteDummySystemFolder();

		$this->assertEquals(
			0,
			$this->dummySysFolderPid
		);

		$this->assertEquals(
			0,
			$this->countRecords(
				'pages', 'title="'.SEMINARS_SYSFOLDER_TITLE.'"'
			)
		);
	}


	/////////////////////////////////////////
	// Tests for the events list functions.
	/////////////////////////////////////////

	public function testShowContainsNoBodyHeaderWithEmptySystemFolder() {
		$this->assertNotContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsTableBodyHeaderForOneEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid)
		);

		$this->assertContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsNoBodyHeaderIfEventIsOnOtherPage() {
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertNotContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1'
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForTwoEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_2'
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
		$this->assertContains(
			'event_2',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneHiddenEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'hidden' => 1
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneTimedEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'endtime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}
}

?>
