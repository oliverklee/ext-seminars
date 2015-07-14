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
class tx_seminars_OldModel_AbstractTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_OldModel_Testing
	 */
	protected $fixture = NULL;
	/**
	 * @var tx_oelib_testingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var int UID of the minimal fixture's data in the DB
	 */
	private $fixtureUid = 0;

	protected function setUp() {
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
		$this->fixture = new tx_seminars_tests_fixtures_OldModel_Testing($this->fixtureUid);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	////////////////////////////////
	// Tests for creating objects.
	////////////////////////////////

	public function testCreateFromUid() {
		self::assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testCreateFromUidFailsForInvalidUid() {
		$test = new tx_seminars_tests_fixtures_OldModel_Testing(
			$this->fixtureUid + 99
		);

		self::assertFalse(
			$test->isOk()
		);
	}

	public function testCreateFromUidFailsForZeroUid() {
		$test = new tx_seminars_tests_fixtures_OldModel_Testing(0);

		self::assertFalse(
			$test->isOk()
		);
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

		self::assertTrue(
			$test->isOk()
		);
	}

	public function testCreateFromDbResultFailsForNull() {
		$test = new tx_seminars_tests_fixtures_OldModel_Testing(
			0, NULL
		);

		self::assertFalse(
			$test->isOk()
		);
	}

	/**
	 * @test
	 */
	public function createFromDbResultFailsForHiddenRecord() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('hidden' => 1)
		);

		$test = new tx_seminars_tests_fixtures_OldModel_Testing($this->fixtureUid);

		self::assertFalse(
			$test->isOk()
		);
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

		self::assertTrue(
			$test->isOk()
		);
	}


	//////////////////////////////////
	// Tests for getting attributes.
	//////////////////////////////////

	public function testGetUid() {
		self::assertEquals(
			$this->fixtureUid,
			$this->fixture->getUid()
		);
	}

	public function testHasUidIsTrueForObjectsWithAUid() {
		self::assertNotEquals(
			0,
			$this->fixtureUid
		);
		self::assertTrue(
			$this->fixture->hasUid()
		);
	}

	public function testHasUidIsFalseForObjectsWithoutUid() {
		$virginFixture = new tx_seminars_tests_fixtures_OldModel_Testing(0);

		self::assertEquals(
			0,
			$virginFixture->getUid()
		);
		self::assertFalse(
			$virginFixture->hasUid()
		);
	}

	public function testGetTitle() {
		self::assertEquals(
			'Test',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////////
	// Tests for setting attributes.
	//////////////////////////////////

	public function testSetAndGetRecordBooleanTest() {
		self::assertFalse(
			$this->fixture->getBooleanTest()
		);

		$this->fixture->setBooleanTest(TRUE);
		self::assertTrue(
			$this->fixture->getBooleanTest()
		);
	}

	public function testSetAndGetTitle() {
		$title = 'Test';
		$this->fixture->setTitle($title);

		self::assertEquals(
			$title,
			$this->fixture->getTitle()
		);
	}

	public function testTypoScriptConfigurationIsLoaded() {
		self::assertTrue(
			$this->fixture->getConfValueBoolean('isStaticTemplateLoaded')
		);
	}


	///////////////////////////////////
	// Tests for commiting to the DB.
	///////////////////////////////////

	public function testCommitToDbCanInsertNewRecord() {
		$title = 'Test record (with a unique title)';
		self::assertEquals(
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

		self::assertTrue(
			$virginFixture->isOk(),
			'The virgin fixture has not been completely initialized yet.'
		);

		self::assertTrue(
			$virginFixture->commitToDb()
		);
		self::assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_test',
				'title = "' . $title . '"'
			)
		);
	}

	public function testCommitToDbCanUpdateExistingRecord() {
		$title = 'Test record (with a unique title)';
		$this->fixture->setTitle($title);

		self::assertTrue(
			$this->fixture->commitToDb()
		);
		self::assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_test',
				'title = "' . $title . '"'
			)
		);
	}

	public function testSaveToDatabaseCanUpdateExistingRecord() {
		$this->fixture->saveToDatabase(array('title' => 'new title'));

		self::assertEquals(
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

		self::assertFalse(
			$virginFixture->isOk()
		);
		self::assertFalse(
			$virginFixture->commitToDb()
		);
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
		self::assertEquals(
			0,
			$this->fixture->createMmRecords(
				'tx_seminars_test_test_mm', array()
			)
		);
	}

	public function testCreateMmRecordsWithOneReferenceReturnsOne() {
		$this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');

		self::assertEquals(
			1,
			$this->fixture->createMmRecords(
				'tx_seminars_test_test_mm', array(42)
			)
		);
	}

	public function testCreateMmRecordsWithTwoReferencesReturnsTwo() {
		$this->testingFramework->markTableAsDirty('tx_seminars_test_test_mm');

		self::assertEquals(
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

		self::assertTrue(
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

		self::assertTrue(
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

		self::assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_test_test_mm',
				'uid_local = ' . $this->fixtureUid . ' AND uid_foreign = 31 ' .
					'AND sorting = 2'
			)
		);
	}


	/*
	 * Tests concerning recordExists
	 */

	/**
	 * @test
	 */
	public function recordExistsForHiddenRecordAndNoHiddenRecordsAllowedReturnsFalse() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('hidden' => 1)
		);

		self::assertFalse(
			tx_seminars_tests_fixtures_OldModel_Testing::recordExists($this->fixtureUid, 'tx_seminars_test', FALSE)
		);
	}

	/**
	 * @test
	 */
	public function recordExistsForHiddenRecordAndHiddenRecordsAllowedReturnsTrue() {
		$this->testingFramework->changeRecord(
			'tx_seminars_test', $this->fixtureUid, array('hidden' => 1)
		);

		self::assertTrue(
			tx_seminars_tests_fixtures_OldModel_Testing::recordExists($this->fixtureUid, 'tx_seminars_test', TRUE)
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

		self::assertEquals(
			42,
			$fixture->getPageUid()
		);
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

		self::assertEquals(
			0,
			$fixture->getPageUid()
		);
	}
}