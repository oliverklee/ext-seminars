<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2014 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_pi2_pi2Test extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_seminars_pi2
	 */
	protected $fixture = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * a backup of $GLOBALS['TYPO3_CONF_VARS']['BE']
	 *
	 * @var array
	 */
	protected $backEndConfigurationBackup = array();

	/**
	 * PID of the system folder in which we store our test data
	 *
	 * @var integer
	 */
	protected $pid = 0;

	/**
	 * UID of a test event record
	 *
	 * @var integer
	 */
	protected $eventUid = 0;

	public function setUp() {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 4007000) {
			$this->backEndConfigurationBackup = $GLOBALS['TYPO3_CONF_VARS']['BE'];
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] = 'utf-8';
		}

		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang_db.xml');
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('lang') . 'locallang_general.xml');

		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$this->pid = $this->testingFramework->createSystemFolder();
		$this->eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$this->fixture = new tx_seminars_pi2();
		$this->fixture->init(array());
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'charsetForCsv', 'utf-8'
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
		unset($this->fixture, $this->testingFramework);

		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 4007000) {
			$GLOBALS['TYPO3_CONF_VARS']['BE'] = $this->backEndConfigurationBackup;
		}
	}


	/*
	 * Utility functions
	 */

	/*
	 * Retrieves the localization for the given locallang key and then strips
	 * the trailing colon from the localization
	 *
	 * @param string $locallangKey
	 *        the locallang key with the localization to remove the trailing
	 *        colon from, must not be empty and the localization must have a
	 *        trailing colon
	 *
	 * @return string locallang string with the removed trailing colon, will not
	 *                be empty
	 */
	private function localizeAndRemoveColon($locallangKey) {
		return substr($GLOBALS['LANG']->getLL($locallangKey), 0, -1);
	}


	/*
	 * Tests for the CSV export of events.
	 */

	public function createListOfEventsIsEmptyForZeroPid() {
		$this->assertSame(
			'',
			$this->fixture->createListOfEvents(0)
		);
	}

	/**
	 * @test
	 */
	public function createListOfEventsIsEmptyForNegativePid() {
		$this->assertSame(
			'',
			$this->fixture->createListOfEvents(-2)
		);
	}

	/**
	 * @test
	 */
	public function createListOfEventsHasOnlyHeaderLineForZeroRecords() {
		$pid = $this->testingFramework->createSystemFolder();

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid,title'
		);

		$this->assertSame(
			$this->localizeAndRemoveColon('tx_seminars_seminars' . '.uid') . ';' .
				$this->localizeAndRemoveColon('tx_seminars_seminars' . '.title') .
				CRLF,
			$this->fixture->createListOfEvents($pid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfEventsCanContainOneEventUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);

		$this->assertContains(
			(string) $this->eventUid,
			$this->fixture->createListOfEvents($this->pid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfEventsCanContainEventFromSubFolder() {
		$subFolderPid = $this->testingFramework->createSystemFolder($this->pid);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $subFolderPid,
				'title' => 'another event',
			)
		);

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'title'
		);

		$this->assertContains(
			'another event',
			$this->fixture->createListOfEvents($this->pid)
		);
	}

	/**
	 * @test
	 */
	public function mainCanExportOneEventUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);

		$this->fixture->piVars['table'] = 'tx_seminars_seminars';
		$this->fixture->piVars['pid'] = $this->pid;

		$this->assertContains(
			(string) $this->eventUid,
			$this->fixture->main(NULL, array())
		);
	}

	/**
	 * @test
	 */
	public function createListOfEventsCanContainTwoEventUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$secondEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pid,
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

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsCanContainTwoEventUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$secondEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pid,
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

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsSeparatesLinesWithCarriageReturnsAndLineFeeds() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$secondEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
			)
		);

		$this->assertSame(
			$this->localizeAndRemoveColon('tx_seminars_seminars' . '.uid')
				. CRLF . $this->eventUid . CRLF . $secondEventUid . CRLF,
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsHasResultEndingWithCariageReturnAndLineFeed() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600,
			)
		);

		$this->assertRegExp(
			'/\r\n$/',
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsDoesNotWrapRegularValuesWithDoubleQuotes() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->eventUid,
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

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsEscapesDoubleQuotes() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->eventUid,
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


	/**
	 * @test
	 */
	public function createAndOutputListOfEventsDoesWrapValuesWithLineFeedsInDoubleQuotes() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->eventUid,
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

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsDoesWrapValuesWithDoubleQuotesInDoubleQuotes() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->eventUid,
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

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsDoesWrapValuesWithSemicolonsInDoubleQuotes() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->eventUid,
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

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsSeparatesValuesWithSemicolons() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->eventUid,
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

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsDoesNotWrapHeadlineFieldsInDoubleQuotes() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'description,title'
		);
		$eventList = $this->fixture->createAndOutputListOfEvents($this->pid);
		$description = $this->localizeAndRemoveColon(
			'tx_seminars_seminars' . '.description'
		);

		$this->assertContains(
			$description,
			$eventList
		);
		$this->assertNotContains(
			'"' . $description . '"',
			$eventList
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfEventsSeparatesHeadlineFieldsWithSemicolons() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'description,title'
		);

		$this->assertContains(
			$this->localizeAndRemoveColon('tx_seminars_seminars' . '.description') .
				';' . $this->localizeAndRemoveColon('tx_seminars_seminars' . '.title'),
			$this->fixture->createAndOutputListOfEvents($this->pid)
		);
	}


	/*
	 * Tests for the CSV export of registrations.
	 */

	/**
	 * @test
	 */
	public function createListOfRegistrationsIsEmptyForNonExistentEvent() {
		$this->assertSame(
			'',
			$this->fixture->createListOfRegistrations($this->eventUid + 9999)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsHasOnlyHeaderLineForZeroRecords() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', 'name'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);

		$this->assertSame(
			$this->localizeAndRemoveColon('LGL.name') . ';' .
				$this->localizeAndRemoveColon('tx_seminars_attendances.uid') .
				CRLF,
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsCanContainOneRegistrationUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
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

	/**
	 * @test
	 */
	public function createListOfRegistrationsCanContainLocalizedRegisteredThemselves() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'registered_themselves'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registered_themselves' => 1,
			)
		);

		$this->assertContains(
			$this->localizeAndRemoveColon(
				'tx_seminars_attendances.registered_themselves'
			),
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsCanContainLocalizedCompanyHeading() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'company'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'company' => 'foo',
			)
		);

		$this->assertContains(
			$this->localizeAndRemoveColon('tx_seminars_attendances.company'),
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsCanContainCompanyContent() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'company'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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

	/**
	 * @test
	 */
	public function createListOfRegistrationsForFrontEndModeCanExportRegistrationsBelongingToAnEvent() {
		$this->fixture->setTypo3Mode('FE');
		$globalBackEndUser = $GLOBALS['BE_USER'];
		$GLOBALS['BE_USER'] = NULL;

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'company'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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


	/*
	 * Tests concerning the main function
	 */

	/**
	 * @test
	 */
	public function mainCanExportValueOfSignedThemselves() {
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
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registered_themselves' => 1,
			)
		);

		$this->fixture->piVars['table'] = 'tx_seminars_attendances';
		$this->fixture->piVars['eventUid'] = $this->eventUid;

		$this->assertContains(
			$this->fixture->translate('label_yes'),
			$this->fixture->main(NULL, array())
		);
	}

	/**
	 * @test
	 */
	public function mainCanExportOneRegistrationUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->piVars['table'] = 'tx_seminars_attendances';
		$this->fixture->piVars['eventUid'] = $this->eventUid;

		$this->assertContains(
			(string) $registrationUid,
			$this->fixture->main(NULL, array())
		);
	}

	/**
	 * @test
	 */
	public function createListOfRegistrationsCanContainTwoRegistrationUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		;
		$firstRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
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

	/**
	 * @test
	 */
	public function mainCanKeepEventDataInUtf8() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'title'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pid,
				'title' => 'Schöne Bären führen',
			)
		);

		$this->fixture->piVars['table'] = 'tx_seminars_seminars';
		$this->fixture->piVars['pid'] = $this->pid;

		$this->assertContains(
			'Schöne Bären führen',
			$this->fixture->main(NULL, array())
		);
	}

	/**
	 * @test
	 */
	public function mainCanChangeEventDataToIso885915() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'title'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pid,
				'title' => 'Schöne Bären führen',
			)
		);

		$this->fixture->piVars['table'] = 'tx_seminars_seminars';
		$this->fixture->piVars['pid'] = $this->pid;

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'charsetForCsv', 'iso-8859-15'
		);

		$this->assertContains(
			'Sch' . chr(246) . 'ne B' . chr(228) . 'ren f' . chr(252) . 'hren',
			$this->fixture->main(NULL, array())
		);
	}

	/**
	 * @test
	 */
	public function mainCanKeepRegistrationDataInUtf8() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'title'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->pid,
				'title' => 'Schöne Bären führen',
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->piVars['table'] = 'tx_seminars_attendances';
		$this->fixture->piVars['pid'] = $this->pid;

		$this->assertContains(
			'Schöne Bären führen',
			$this->fixture->main(NULL, array())
		);
	}

	/**
	 * @test
	 */
	public function mainCanChangeRegistrationDataToIso885915() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'title'
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->pid,
				'title' => 'Schöne Bären führen',
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->piVars['table'] = 'tx_seminars_attendances';
		$this->fixture->piVars['pid'] = $this->pid;

		$this->fixture->getConfigGetter()->setConfigurationValue(
			'charsetForCsv', 'iso-8859-15'
		);

		$this->assertContains(
			'Sch' . chr(246) . 'ne B' . chr(228) . 'ren f' . chr(252) . 'hren',
			$this->fixture->main(NULL, array())
		);
	}


	/*
	 * Tests concerning createAndOutputListOfRegistrations
	 */

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
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => 1,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => 2,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertContains(
			CRLF . $firstRegistrationUid . CRLF .
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
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
		$localizedAddress = $this->localizeAndRemoveColon(
			'tx_seminars_attendances.address'
		);

		$this->assertContains(
			$localizedAddress,
			$registrationsList
		);
		$this->assertNotContains(
			'"' . $localizedAddress . '"',
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
			$this->localizeAndRemoveColon('tx_seminars_attendances.address') .
				';' . $this->localizeAndRemoveColon('tx_seminars_attendances.title'),
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
			$this->localizeAndRemoveColon('LGL.name') . ';' .
				$this->localizeAndRemoveColon('tx_seminars_attendances.address'),
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

		$this->assertSame(
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
			'tx_seminars_attendances',
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
	public function createAndOutputListOfRegistrationsForNonExistingEventUidAddsNotFoundStatusToHeader() {
		$this->fixture->createAndOutputListOfRegistrations(
			$this->testingFramework->getAutoIncrement('tx_seminars_seminars')
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
	public function createAndOutputListOfRegistrationsForNoGivenEventUidAndFeModeAddsAccessForbiddenStatusToHeader() {
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

		$this->assertTrue(
			in_array(
				'Content-type: text/csv; header=present; charset=utf-8',
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
					->getAllAddedHeaders()
			)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForNoEventUidGivenSetsPageContentTypeToCsv() {
		$this->fixture->piVars['pid'] = $this->pid;
		$this->fixture->createAndOutputListOfRegistrations();

		$this->assertTrue(
			in_array(
				'Content-type: text/csv; header=present; charset=utf-8',
				tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()
					->getAllAddedHeaders()
			)
		);
	}


	/*
	 * Tests concerning the export mode and the configuration
	 */

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForEmailModeUsesFeUserFieldsFromEmailConfiguration() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForEmailCsv', 'email'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', 'name'
		);

		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			'', array('email' => 'foo@bar.com')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $this->eventUid, 'user' => $frontEndUserUid)
		);
		$this->fixture->setExportMode(tx_seminars_pi2::EXPORT_MODE_EMAIL);

		$this->assertContains(
			'foo@bar.com',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForWebModeNotUsesFeUserFieldsFromEmailConfiguration() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForEmailCsv', 'email'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', 'name'
		);

		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			'', array('email' => 'foo@bar.com')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('seminar' => $this->eventUid, 'user' => $frontEndUserUid)
		);
		$this->fixture->setExportMode(tx_seminars_pi2::EXPORT_MODE_WEB);

		$this->assertNotContains(
			'foo@bar.com',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForEmailModeUsesRegistrationFieldsFromEmailConfiguration() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForEmailCsv', 'bank_name'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', ''
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'bank_name' => 'foo bank'
			)
		);
		$this->fixture->setExportMode(tx_seminars_pi2::EXPORT_MODE_EMAIL);

		$this->assertContains(
			'foo bank',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForWebModeNotUsesRegistrationFieldsFromEmailConfiguration() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForEmailCsv', 'bank_name'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', ''
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'bank_name' => 'foo bank'
			)
		);
		$this->fixture->setExportMode(tx_seminars_pi2::EXPORT_MODE_WEB);

		$this->assertNotContains(
			'foo bank',
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForEmailModeUsesRegistrationsOnQueueSettingFromEmailConfiguration() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'showAttendancesOnRegistrationQueueInEmailCsv', TRUE
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForEmailCsv', 'uid'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'showAttendancesOnRegistrationQueueInCsv', FALSE
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$queueUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'bank_name' => 'foo bank',
				'registration_queue' => 1,
			)
		);
		$this->fixture->setExportMode(tx_seminars_pi2::EXPORT_MODE_EMAIL);

		$this->assertContains(
			(string) $queueUid,
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}

	/**
	 * @test
	 */
	public function createAndOutputListOfRegistrationsForWebModeNotUsesRegistrationsOnQueueSettingFromEmailConfiguration() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'showAttendancesOnRegistrationQueueInEmailCsv', TRUE
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForEmailCsv', 'uid'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'showAttendancesOnRegistrationQueueInCsv', FALSE
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$queueUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'bank_name' => 'foo bank',
				'registration_queue' => 1,
			)
		);
		$this->fixture->setExportMode(tx_seminars_pi2::EXPORT_MODE_WEB);

		$this->assertNotContains(
			(string) $queueUid,
			$this->fixture->createAndOutputListOfRegistrations($this->eventUid)
		);
	}
}