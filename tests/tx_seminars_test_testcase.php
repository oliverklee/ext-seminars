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
 * Testcase for the test class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'tests/fixtures/class.tx_seminars_test.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_test_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;
	private $backupTsfe;

	/** UID of the minimal fixture's data in the DB */
	private $fixtureUid = 0;

	public function setUp() {
		// TODO: Remove this as soon as it is possible to build a front-end
		// environment on demand with phpunit.
		// In tx_phpunitt3_module1::simulateFrontendEnviroment() creates
		// an object in $GLOBALS['TSFE'] which is not properly filled with
		// information, so we need save it temporarily to $this->TSFE and unset
		// the global variable.
		// @see		https://bugs.oliverklee.com/show_bug.cgi?id=1557
		$this->backupTsfe = $GLOBALS['TSFE'];
		unset($GLOBALS['TSFE']);

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$systemFolderUid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createTemplate(
			$systemFolderUid,
			array(
				'tstamp' => time(),
				'sorting' => 256,
				'crdate' => time(),
				'cruser_id' => 1,
				'title' => 'TEST',
				'root' => 1,
				'clear' => 3,
				'include_static_file' => 'EXT:seminars/static/'
			)
		);
		$this->fixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TEST,
			array(
				'pid' => $systemFolderUid,
				'title' => 'Test'
			)
		);
		$this->fixture = new tx_seminars_test($this->fixtureUid);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);

		// TODO: Remove this as soon as it is possible to build a front-end
		// environment on demand with phpunit.
		// Restore $GLOBALS['TSFE'] from $this->TSFE after the tests ran.
		// @see		https://bugs.oliverklee.com/show_bug.cgi?id=1557
		$GLOBALS['TSFE'] = $this->backupTsfe;
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
		$test = new tx_seminars_test($this->fixtureUid + 99);

		$this->assertFalse(
			$test->isOk()
		);
	}

	public function testCreateFromUidFailsForZeroUid() {
		$test = new tx_seminars_test(0);

		$this->assertFalse(
			$test->isOk()
		);
	}

	public function testCreateFromDbResult() {
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			SEMINARS_TABLE_TEST,
			'uid = '.$this->fixtureUid
		);
		if (!$dbResult) {
			throw new Exception('There was an error with the database query.');
		}

		$test = new tx_seminars_test(0, $dbResult);

		$this->assertTrue(
			$test->isOk()
		);
	}

	public function testCreateFromDbResultFailsForNull() {
		$test = new tx_seminars_test(0, null);

		$this->assertFalse(
			$test->isOk()
		);
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
		$virginFixture = new tx_seminars_test(0);

		$this->assertEquals(
			0,
			$virginFixture->getUid()
		);
		$this->assertFalse(
			$virginFixture->hasUid()
		);
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

		$this->fixture->setBooleanTest(true);
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
				SEMINARS_TABLE_TEST,
				'title ="'.$title.'"',
				'Please make sure that no test record with the title "'
					.$title.'" exists in the DB.'
			)
		);

		$virginFixture = new tx_seminars_test(0);
		$virginFixture->setTitle($title);
		$virginFixture->enableTestMode();
		$this->testingFramework->markTableAsDirty(SEMINARS_TABLE_TEST);

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
				SEMINARS_TABLE_TEST,
				'title="'.$title.'"'
			)
		);
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
				SEMINARS_TABLE_TEST,
				'title="'.$title.'"'
			)
		);
	}

	public function testCommitToDbWillNotWriteIncompleteRecords() {
		$virginFixture = new tx_seminars_test(0);
		$this->testingFramework->markTableAsDirty(SEMINARS_TABLE_TEST);

		$this->assertFalse(
			$virginFixture->isOk()
		);
		$this->assertFalse(
			$virginFixture->commitToDb()
		);
	}
}
?>