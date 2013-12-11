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
 */
class tx_seminars_OldModel_AbstractTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_OldModel_Testing
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer UID of the minimal fixture's data in the DB
	 */
	private $fixtureUid = 0;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$systemFolderUid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createTemplate(
			$systemFolderUid,
			array(
				'tstamp' => $GLOBALS['SIM_EXEC_TIME'],
				'sorting' => 256,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'cruser_id' => 1,
				'title' => 'TEST',
				'root' => 1,
				'clear' => 3,
				'include_static_file' => 'EXT:seminars/Configuration/TypoScript/',
			)
		);
		$this->fixtureUid = $this->testingFramework->createRecord(
			'tx_seminars_test',
			array(
				'pid' => $systemFolderUid,
				'title' => 'Test',
			)
		);
		$this->fixture = new tx_seminars_tests_fixtures_OldModel_Testing(
			$this->fixtureUid
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	////////////////////////////////
	// Tests for creating objects.
	////////////////////////////////

	public function testCreateFromUid() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromUidFailsForInvalidUid() {
		$test = new tx_seminars_tests_fixtures_OldModel_Testing(
			$this->fixtureUid + 99
		);

		$this->assertFalse(
			$test->isOk()
		);

		$test->__destruct();
	}

	public function testCreateFromUidFailsForZeroUid() {
		$test = new tx_seminars_tests_fixtures_OldModel_Testing(0);

		$this->assertFalse(
			$test->isOk()
		);

		$test->__destruct();
	}

	public function testCreateFromDbResult() {
		$dbResult = tx_oelib_db::select(
			'*',
			'tx_seminars_test',
			'uid = ' . $this->fixtureUid
		);

		$test = new tx_seminars_tests_fixtures_OldModel_Testing(
			0, $dbResult
		);

		$this->assertTrue(
			$test->isOk()
		);

		$test->__destruct();
	}

	public function testCreateFromDbResultFailsForNull() {
		$test = new tx_seminars_tests_fixtures_OldModel_Testing(
			0, NULL
		);

		$this->assertFalse(
			$test->isOk()
		);

		$test->__destruct();
	}

	/**
	 * @test
	 */
	public function createFromDbResultFailsForHiddenRecord() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('hidden' => 1)
		);

		$test = new tx_seminars_tests_fixtures_OldModel_Testing($this->fixtureUid);

		$this->assertFalse(
			$test->isOk()
		);

		$test->__destruct();
	}

	/**
	 * @test
	 */
	public function createFromDbResultWithAllowedHiddenRecordsGetsHiddenRecordFromDb() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('hidden' => 1)
		);

		$test = new tx_seminars_tests_fixtures_OldModel_Testing(
			$this->fixtureUid, NULL, TRUE
		);

		$this->assertTrue(
			$test->isOk()
		);

		$test->__destruct();
	}


	//////////////////////////////////
	// Tests for getting attributes.
	//////////////////////////////////

	public function testGetUid() {
		$this->assertEquals(
			$this->fixtureUid,
			$this->fixture->getUid()
		);
	}

	public function testHasUidIsTrueForObjectsWithAUid() {
		$this->assertNotEquals(
			0,
			$this->fixtureUid
		);
		$this->assertTrue(
			$this->fixture->hasUid()
		);
	}

	public function testHasUidIsFalseForObjectsWithoutUid() {
		$virginFixture = new tx_seminars_tests_fixtures_OldModel_Testing(0);

		$this->assertEquals(
			0,
			$virginFixture->getUid()
		);
		$this->assertFalse(
			$virginFixture->hasUid()
		);

		$virginFixture->__destruct();
	}

	public function testGetTitle() {
		$this->assertEquals(
			'Test',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////////
	// Tests for setting attributes.
	//////////////////////////////////

	public function testSetAndGetRecordBooleanTest() {
		$this->assertFalse(
			$this->fixture->getBooleanTest()
		);

		$this->fixture->setBooleanTest(TRUE);
		$this->assertTrue(
			$this->fixture->getBooleanTest()
		);
	}

	public function testSetAndGetTitle() {
		$title = 'Test';
		$this->fixture->setTitle($title);

		$this->assertEquals(
			$title,
			$this->fixture->getTitle()
		);
	}

	public function testTypoScriptConfigurationIsLoaded() {
		$this->assertTrue(
			$this->fixture->getConfValueBoolean('isStaticTemplateLoaded')
		);
	}


	///////////////////////////////////
	// Tests for commiting to the DB.
	///////////////////////////////////

	public function testCommitToDbCanInsertNewRecord() {
		$title = 'Test record (with a unique title)';
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_test',
				'title = "' . $title . '"',
				'Please make sure that no test record with the title "' .
					$title . '" exists in the DB.'
			)
		);

		$virginFixture = new tx_seminars_tests_fixtures_OldModel_Testing(0);
		$virginFixture->setTitle($title);
		$virginFixture->enableTestMode();
		$this->testingFramework->markTableAsDirty('tx_seminars_test');

		$this->assertTrue(
			$virginFixture->isOk(),
			'The virgin fixture has not been completely initialized yet.'
		);

		$this->assertTrue(
			$virginFixture->commitToDb()
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_test',
				'title = "' . $title . '"'
			)
		);

		$virginFixture->__destruct();
	}

	public function testCommitToDbCanUpdateExistingRecord() {
		$title = 'Test record (with a unique title)';
		$this->fixture->setTitle($title);

		$this->assertTrue(
			$this->fixture->commitToDb()
		);
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_test',
				'title = "' . $title . '"'
			)
		);
	}

	public function testSaveToDatabaseCanUpdateExistingRecord() {
		$this->fixture->saveToDatabase(array('title' => 'new title'));

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_test',
				'title = "new title"'
			)
		);
	}

	public function testCommitToDbWillNotWriteIncompleteRecords() {
		$virginFixture = new tx_seminars_tests_fixtures_OldModel_Testing(0);
		$this->testingFramework->markTableAsDirty('tx_seminars_test');

		$this->assertFalse(
			$virginFixture->isOk()
		);
		$this->assertFalse(
			$virginFixture->commitToDb()
		);

		$virginFixture->__destruct();
	}


	/////////////////////////////////////
	// Tests concerning createMmRecords
	/////////////////////////////////////

	public function testCreateMmRecordsForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$mmTable must not be empty.'
		);

		$this->fixture->createMmRecords('', array());
	}

	public function testCreateMmRecordsOnObjectWithoutUidThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'createMmRecords may only be called on objects that have a UID.'
		);

		$virginFixture = new tx_seminars_tests_fixtures_OldModel_Testing(0);
		$virginFixture->createMmRecords('tx_seminars_test_test_mm', array());
	}

	public function testCreateMmRecordsWithEmptyReferencesReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->createMmRecords(
				'tx_seminars_test_test_mm', array()
			)
		);
	}

	public function testCreateMmRecordsWithOneReferenceReturnsOne() {
		$this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');

		$this->assertEquals(
			1,
			$this->fixture->createMmRecords(
				'tx_seminars_test_test_mm', array(42)
			)
		);
	}

	public function testCreateMmRecordsWithTwoReferencesReturnsTwo() {
		$this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');

		$this->assertEquals(
			2,
			$this->fixture->createMmRecords(
				'tx_seminars_test_test_mm', array(42, 31)
			)
		);
	}

	public function testCreateMmRecordsWithOneReferenceCreatesMmRecord() {
		$this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');
		$this->fixture->createMmRecords(
			'tx_seminars_test_test_mm', array(42)
		);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_test_test_mm',
				'uid_local = ' . $this->fixtureUid . ' AND uid_foreign = 42'
			)
		);
	}

	public function testCreateMmRecordsWithCreatesFirstMmRecordWithSortingOne() {
		$this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');
		$this->fixture->createMmRecords(
			'tx_seminars_test_test_mm', array(42)
		);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_test_test_mm',
				'uid_local = ' . $this->fixtureUid . ' AND sorting = 1'
			)
		);
	}

	public function testCreateMmRecordsWithCreatesSecondMmRecordWithSortingTwo() {
		$this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');
		$this->fixture->createMmRecords(
			'tx_seminars_test_test_mm', array(42, 31)
		);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_test_test_mm',
				'uid_local = ' . $this->fixtureUid . ' AND uid_foreign = 31 ' .
					'AND sorting = 2'
			)
		);
	}


	//////////////////////////////////
	// Tests concerning recordExists
	//////////////////////////////////

	/**
	 * @test
	 */
	public function recordExistsForHiddenRecordAndNoHiddenRecordsAllowedReturnsFalse() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('hidden' => 1)
		);

		$this->assertFalse(
			$this->fixture->recordExists(
				$this->fixtureUid, 'tx_seminars_test', FALSE
			)
		);
	}

	/**
	 * @test
	 */
	public function recordExistsForHiddenRecordAndHiddenRecordsAllowedReturnsTrue() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('hidden' => 1)
		);

		$this->assertTrue(
			$this->fixture->recordExists(
				$this->fixtureUid, 'tx_seminars_test', TRUE
			)
		);
	}


	////////////////////////////////
	// Tests concerning getPageUid
	////////////////////////////////

	/**
	 * @test
	 */
	public function getPageUidCanReturnRecordsPageUid() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('pid' => 42)
		);
		$fixture = new tx_seminars_tests_fixtures_OldModel_Testing(
			$this->fixtureUid
		);

		$this->assertEquals(
			42,
			$fixture->getPageUid()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function getPageUidForRecordWithPageUidZeroReturnsZero() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('pid' => 0)
		);
		$fixture = new tx_seminars_tests_fixtures_OldModel_Testing(
			$this->fixtureUid
		);

		$this->assertEquals(
			0,
			$fixture->getPageUid()
		);

		$fixture->__destruct();
	}
}