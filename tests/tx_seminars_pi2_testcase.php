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
				'begin_date' => mktime(),
			)
		);

		$this->fixture = new tx_seminars_pi2();
		$this->fixture->init(array());
	}

	public function tearDown() {
		tx_oelib_headerProxyFactory::getInstance()->discardInstance();
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
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
			'"uid","title"'.CRLF,
			$this->fixture->createListOfEvents($pid)
		);
	}

	public function testCreateListOfEventsCanContainOneEventUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);

		$this->assertEquals(
			'"uid"'.CRLF
				.'"'.((string) $this->eventUid).'"'.CRLF,
			$this->fixture->createListOfEvents($this->pid)
		);
	}

	public function testMainCanExportOneEventUid() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromEventsForCsv', 'uid'
		);

		$this->fixture->piVars['table'] = SEMINARS_TABLE_SEMINARS;
		$this->fixture->piVars['pid'] = $this->pid;

		$this->assertEquals(
			'"uid"'.CRLF
				.'"'.((string) $this->eventUid).'"'.CRLF,
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
				'begin_date' => mktime() - 3600,
			)
		);

		$this->assertEquals(
			'"uid"'.CRLF
				.'"'.((string) $this->eventUid).'"'.CRLF
				.'"'.((string) $secondEventUid).'"'.CRLF,
			$this->fixture->createListOfEvents($this->pid)
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
				'begin_date' => mktime() - 3600,
			)
		);

		$this->fixture->piVars['pid'] = $this->pid;

		$this->assertEquals(
			'"uid"'.CRLF
				.'"'.((string) $this->eventUid).'"'.CRLF
				.'"'.((string) $secondEventUid).'"'.CRLF,
			$this->fixture->createAndOutputListOfEvents()
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
			'"name","uid"'.CRLF,
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
			array('seminar' => $this->eventUid)
		);

		$this->assertEquals(
			'"","uid"'.CRLF
				.'"'.((string) $registrationUid).'"'.CRLF,
			$this->fixture->createListOfRegistrations($this->eventUid)
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
			array('seminar' => $this->eventUid)
		);

		$this->fixture->piVars['table'] = SEMINARS_TABLE_ATTENDANCES;
		$this->fixture->piVars['seminar'] = $this->eventUid;

		$this->assertEquals(
			'"","uid"'.CRLF
				.'"'.((string) $registrationUid).'"'.CRLF,
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
		$firstRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $this->eventUid, 'crdate' => time())
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $this->eventUid, 'crdate' => (time() + 1))
		);

		$this->assertEquals(
			'"","uid"' . CRLF .
				'"' . ((string) $firstRegistrationUid) . '"' . CRLF .
				'"' . ((string) $secondRegistrationUid) . '"' . CRLF,
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}

	public function testCreateAndOutputListOfRegistrationsCanContainTwoRegistrationUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$firstRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $this->eventUid, 'crdate' => time())
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $this->eventUid, 'crdate' => (time() + 1))
		);

		$this->fixture->piVars['seminar'] = $this->eventUid;

		$this->assertEquals(
			'"","uid"' . CRLF .
				'"' . ((string) $firstRegistrationUid) . '"' . CRLF .
				'"' . ((string) $secondRegistrationUid) . '"' . CRLF,
			$this->fixture->createAndOutputListOfRegistrations()
		);
	}

	public function testMainCanExportOneReferrer() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'referrer'
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->eventUid,
				'referrer' => 'test referrer',
			)
		);

		$this->fixture->piVars['table'] = SEMINARS_TABLE_ATTENDANCES;
		$this->fixture->piVars['seminar'] = $this->eventUid;

		$this->assertEquals(
			'"","referrer"'.CRLF
				.'"test referrer"'.CRLF,
			$this->fixture->main(null, array())
		);
	}
}
?>