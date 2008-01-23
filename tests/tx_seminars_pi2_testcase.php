<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the pi2 class (CSV export) in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'pi2/class.tx_seminars_pi2.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_pi2_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** PID of the system folder in which we store our test data */
	private $pid;
	/** UID of a test event record */
	private $eventUid;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->pid = $this->testingFramework->createSystemFolder();
		$this->eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->pid)
		);

		$this->fixture = new tx_seminars_pi2();
		$this->fixture->init(array());
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}

	///////////////////////////////////////////////
	// Tests for the CSV export of registrations.
	///////////////////////////////////////////////

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

	public function testCreateListOfRegistrationsCanContainTwoRegistrationUids() {
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromFeUserForCsv', ''
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'fieldsFromAttendanceForCsv', 'uid'
		);
		$firstRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $this->eventUid)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array('seminar' => $this->eventUid)
		);

		$this->assertEquals(
			'"","uid"'.CRLF
				.'"'.((string) $firstRegistrationUid).'"'.CRLF
				.'"'.((string) $secondRegistrationUid).'"'.CRLF,
			$this->fixture->createListOfRegistrations($this->eventUid)
		);
	}
}

?>
