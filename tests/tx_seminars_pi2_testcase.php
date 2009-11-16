<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Oliver Klee (typo3-coding@oliverklee.de)
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
require_once(t3lib_extMgm::extPath('seminars') . 'pi2/class.tx_seminars_pi2.php');

/**
 * Testcase for the pi2 class (CSV export) in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi2_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** PID of the system folder in which we store our test data */
	private $pid;
	/** UID of a test event record */
	private $eventUid;

	public function setUp() {
		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->pid = $this->testingFramework->createSystemFolder();
		$this->eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->pid,
				'sorting' => 1,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$this->fixture = new tx_seminars_pi2();
		$this->fixture->init(array());
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->testingFramework);
	}


	////////////////////////////////////////
	// Tests for the CSV export of events.
	////////////////////////////////////////

	public function testCreateListOfEventsIsEmptyForZeroPid() {
		$this->assertEquals(
			'',
			$this->fixture->createListOfEvents(0)
		);
	}

	public function testCreateListOfEventsIsEmptyForNegativePid() {
		$this->assertEquals(
			'',
			$this->fixture->createListOfEvents(-2)
		);
	}

	public function testCreateListOfEventsHasOnlyHeaderLineForZeroRecords() {
		$pid = $this->testingFramework->createSystemFolder();

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid,title'
		);

		$this->assertEquals(
			'uid;title' . CRLF,
			$this->fixture->createListOfEvents($pid)
		);
	}

	public function testCreateListOfEventsCanContainOneEventUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);

		$this->assertContains(
			(string) $this->eventUid,
			$this->fixture->createListOfEvents($this->pid)
		);
	}

	public function testMainCanExportOneEventUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);

		$this->fixture->piVars['table'] = SEMINARS_TABLE_SEMINARS;
		$this->fixture->piVars['pid'] = $this->pid;

		$this->assertContains(
			(string) $this->eventUid,
			$this->fixture->main(null, array())
		);
	}

	public function testCreateListOfEventsCanContainTwoEventUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$secondEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->pid,
				'sorting' => 2,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
			)
		);
		$eventList = $this->fixture->createListOfEvents($this->pid);

		$this->assertContains(
			(string) $this->eventUid,
			$eventList
		);
		$this->assertContains(
			(string) $secondEventUid,
			$eventList
		);
	}

	public function testCreateAndOutputListOfEventsCanContainTwoEventUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$secondEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->pid,
				'sorting' => 2,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
			)
		);

		$output = $this->fixture->createAndOutputListOfEvents($this->pid);

		$this->assertContains(
			(string) $this->eventUid,
			$output
		);
		$this->assertContains(
			(string) $secondEventUid,
			$output
		);
	}

	public function testCreateAndOutputListOfEventsSeparatesLinesWithCarriageReturnsAndLineFeeds() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$secondEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->pid,
				'sorting' => 2,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
			)
		);

		$this->assertEquals(
			'uid' . CRLF . $this->eventUid . CRLF . $secondEventUid . CRLF,
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	public function testCreateAndOutputListOfEventsHasResultEndingWithCariageReturnAndLineFeed() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->pid,
				'sorting' => 2,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
			)
		);

		$this->assertRegExp(
			'/\r\n$/',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	public function testCreateAndOutputListOfEventsDoesNotWrapRegularValuesWithDoubleQuotes() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->eventUid,
			array('title' => 'bar')
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'title'
		);

		$this->assertNotContains(
			'"bar"',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	public function testCreateAndOutputListOfEventsEscapesDoubleQuotes() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->eventUid,
			array('description' => 'foo " bar')
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'description'
		);

		$this->assertContains(
			'foo "" bar',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}


	public function testCreateAndOutputListOfEventsDoesWrapValuesWithLineFeedsInDoubleQuotes() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->eventUid,
			array('title' => 'foo' . LF . 'bar')
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'title'
		);

		$this->assertContains(
			'"foo' . LF . 'bar"',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	public function testCreateAndOutputListOfEventsDoesWrapValuesWithDoubleQuotesInDoubleQuotes() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->eventUid,
			array('title' => 'foo " bar')
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'title'
		);

		$this->assertContains(
			'"foo "" bar"',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	public function testCreateAndOutputListOfEventsDoesWrapValuesWithSemicolonsInDoubleQuotes() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->eventUid,
			array('title' => 'foo ; bar')
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'title'
		);

		$this->assertContains(
			'"foo ; bar"',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	public function testCreateAndOutputListOfEventsSeparatesValuesWithSemicolons() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->eventUid,
			array('description' => 'foo', 'title' => 'bar')
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'description,title'
		);

		$this->assertContains(
			'foo;bar',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	public function testCreateAndOutputListOfEventsDoesNotWrapHeadlineFieldsInDoubleQuotes() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'description,title'
		);
		$eventList = $this->fixture->createAndOutputListOfEvents($this->pid);

		$this->assertContains(
			'description',
			$eventList
		);
		$this->assertNotContains(
			'"description"',
			$eventList
		);
	}

	public function testCreateAndOutputListOfEventsSeparatesHeadlineFieldsWithSemicolons() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'description,title'
		);

		$this->assertContains(
			'description;title',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}


	///////////////////////////////////////////////
	// Tests for the CSV export of registrations.
	///////////////////////////////////////////////

	public function testCreateListOfRegistrationsIsEmptyForNonExistentEvent() {
		$this->assertEquals(
			'',
			$this->fixture->createListOfRegistrations($this->eventUid + 9999)
		);
	}

	public function testCreateListOfRegistrationsHasOnlyHeaderLineForZeroRecords() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', 'name'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);

		$this->assertEquals(
			'name;uid' . CRLF,
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	public function testCreateListOfRegistrationsCanContainOneRegistrationUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertContains(
			(string) $registrationUid,
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	public function test_CreateListOfRegistrations_CanContainRegisteredThemselves() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'registered_themselves'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registered_themselves' => 1,
			)
		);

		$this->assertContains(
			'registered_themselves',
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	public function test_createListOfRegistrations_CanContainCompanyHeading() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'company'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'company' => 'foo',
			)
		);

		$this->assertContains(
			'company',
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	public function test_createListOfRegistrations_CanContainCompanyContent() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'company'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'company' => 'foo bar inc.',
			)
		);

		$this->assertContains(
			'foo bar inc.',
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	public function test_createListOfRegistrationsForFrontEndMode_CanExportRegistrationsBelongingToAnEvent() {
		$this->fixture->setTypo3Mode('FE');
		$globalBackEndUser = $GLOBALS['BE_USER'];
		$GLOBALS['BE_USER'] = null;

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'company'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'company' => 'foo bar inc.',
			)
		);

		$result = $this->fixture->createListOfRegistrations($this->eventUid);

		$GLOBALS['BE_USER'] = $globalBackEndUser;

		$this->assertContains(
			'foo bar inc.',
			$result
		);
	}


	//////////////////////////////////////
	// Tests concernin the main function
	//////////////////////////////////////

	public function testMainCanExportValueOfSignedThemseles() {
		$this->markTestIncomplete(
			'For this test to run, we need to provide a language file to the ' .
				'registration class @see ' .
				'https://bugs.oliverklee.com/show_bug.cgi?id=3133'
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'registered_themselves'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registered_themselves' => 1,
			)
		);

		$this->fixture->piVars['table'] = SEMINARS_TABLE_ATTENDANCES;
		$this->fixture->piVars['eventUid'] = $this->eventUid;

		$this->assertContains(
			$this->fixture->translate('label_yes'),
			$this->fixture->main(null, array())
		);
	}

	public function testMainCanExportOneRegistrationUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->piVars['table'] = SEMINARS_TABLE_ATTENDANCES;
		$this->fixture->piVars['eventUid'] = $this->eventUid;

		$this->assertContains(
			(string) $registrationUid,
			$this->fixture->main(null, array())
		);
	}

	public function testCreateListOfRegistrationsCanContainTwoRegistrationUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		;
		$firstRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => ($GLOBALS['SIM_EXEC_TIME'] + 1),
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$registrationsList
			= $this->fixture->createListOfRegistrations($this->eventUid);

		$this->assertContains(
			(string) $firstRegistrationUid,
			$registrationsList
		);
		$this->assertContains(
			(string) $secondRegistrationUid,
			$registrationsList
		);
	}


	////////////////////////////////////////////////////////
	// Tests concerning createAndOutputListOfRegistrations
	////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsCanContainTwoRegistrationUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$firstRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => ($GLOBALS['SIM_EXEC_TIME'] + 1),
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$registrationsList
			= $this->fixture->createAndOutputListOfRegistrations($this->eventUid);
		$this->assertContains(
			(string) $firstRegistrationUid,
			$registrationsList
		);
		$this->assertContains(
			(string) $secondRegistrationUid,
			$registrationsList
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsCanContainNameOfUser() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', 'name'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', ''
		);
		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'foo_user')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $frontEndUserUid,
			)
		);

		$this->assertContains(
			'foo_user',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsDoesNotContainUidOfRegistrationWithDeletedUser() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			'', array('deleted' => 1)
		);
		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $frontEndUserUid,
			)
		);

		$this->assertNotContains(
			(string) $registrationUid,
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsDoesNotContainUidOfRegistrationWithInexistentUser() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);

		$registrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->getAutoIncrement('fe_users'),
			)
		);

		$this->assertNotContains(
			(string) $registrationUid,
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsSeparatesLinesWithCarriageReturnAndLineFeed() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);

		$firstRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => 1,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => 2,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertEquals(
			'uid' . CRLF . $firstRegistrationUid . CRLF .
				 $secondRegistrationUid . CRLF,
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsHasResultThatEndsWithCarriageReturnAndLineFeed() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertRegExp(
			'/\r\n$/',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsEscapesDoubleQuotes() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid,address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo " bar',
			)
		);

		$this->assertContains(
			'foo "" bar',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsDoesNotEscapeRegularValues() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo " bar',
			)
		);

		$this->assertNotContains(
			'"foo bar"',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsWrapsValuesWithSemicolonsInDoubleQuotes() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo ; bar',
			)
		);

		$this->assertContains(
			'"foo ; bar"',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsWrapsValuesWithLineFeedsInDoubleQuotes() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo' . LF . 'bar',
			)
		);

		$this->assertContains(
			'"foo' . LF . 'bar"',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsWrapsValuesWithDoubleQuotesInDoubleQuotes() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo " bar',
			)
		);

		$this->assertContains(
			'"foo "" bar"',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsSeparatesTwoValuesWithSemicolons() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address,title'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'title' => 'test',
			)
		);

		$this->assertContains(
			'foo;test',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsDoesNotWrapHeadlineFieldsInDoubleQuotes() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$registrationsList
			= $this->fixture->createAndOutputListOfRegistrations($this->eventUid);

		$this->assertContains(
			'address',
			$registrationsList
		);
		$this->assertNotContains(
			'"address"',
			$registrationsList
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsSeparatesHeadlineFieldsWithSemicolons() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address,title'
		);

		$this->assertContains(
			'address;title',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsForConfigurationAttendanceCsvFieldsEmptyDoesNotAddSemicolonOnEndOfHeadline() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', 'name'
		);

		$this->assertNotContains(
			'name;',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsForConfigurationFeUserCsvFieldsEmptyDoesNotAddSemicolonAtBeginningOfHeadline() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);

		$this->assertNotContains(
			';address',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsForBothConfigurationFieldsNotEmptyAddsSemicolonBetweenConfigurationFields() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', 'name'
		);

		$this->assertContains(
			'name;address',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsForBothConfigurationFieldsEmptyReturnsEmptyLine() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);

		$this->assertEquals(
			CRLF,
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOuptutListOfRegistrationsForNoEventUidGivenReturnsRegistrationsOnCurrentPage() {
		$this->fixture->piVars['pid'] = $this->pid;
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'pid' => $this->pid,
			)
		);

		$this->assertContains(
			'foo',
			$this->fixture->createAndOutputListOfRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function createAndOuptutListOfRegistrationsForNoEventUidGivenDoesNotReturnRegistrationsOnOtherPage() {
		$this->fixture->piVars['pid'] = $this->pid;
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'pid' => $this->pid + 1,
			)
		);

		$this->assertNotContains(
			'foo',
			$this->fixture->createAndOutputListOfRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function createAndOuptutListOfRegistrationsForNoEventUidGivenReturnsRegistrationsOnSubpageOfCurrentPage() {
		$this->fixture->piVars['pid'] = $this->pid;
		$subpagePid = $this->testingFramework->createSystemFolder($this->pid);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'address'
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'pid' => $subpagePid,
			)
		);

		$this->assertContains(
			'foo',
			$this->fixture->createAndOutputListOfRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function createAndOututListOfRegistrationsForNonExistingEventUidAddsNotFoundStatusToHeader() {
		$this->fixture->createAndOutputListOfRegistrations(
			$this->testingFramework->getAutoIncrement(SEMINARS_TABLE_SEMINARS)
		);

		$this->assertContains(
			'404',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
				->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function createAndOututListOfRegistrationsForNoGivenEventUidAndFeModeAddsAccessForbiddenStatusToHeader() {
		$this->fixture->setTypo3Mode('FE');
		$this->fixture->createAndOutputListOfRegistrations();

		$this->assertContains(
			'403',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
				->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForEventUidGivenSetsPageContentTypeToCsv() {
		$this->fixture->createAndOutputListOfRegistrations($this->eventUid);

		$this->assertContains(
			'Content-type: text/csv;',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
				->getLastAddedHeader()
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForNoEventUidGivenSetsPageContentTypeToCsv() {
		$this->fixture->piVars['pid'] = $this->pid;
		$this->fixture->createAndOutputListOfRegistrations();

		$this->assertContains(
			'Content-type: text/csv;',
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
				->getLastAddedHeader()
		);
	}
}
?>