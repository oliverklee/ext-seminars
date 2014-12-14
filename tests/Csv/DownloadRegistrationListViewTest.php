<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Oliver Klee (typo3-coding@oliverklee.de)
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
class Tx_Seminars_Tests_Csv_DownloadRegistrationListViewTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_Csv_DownloadRegistrationListView
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * PID of the system folder in which we store our test data
	 *
	 * @var int
	 */
	protected $pageUid = 0;

	/**
	 * UID of a test event record
	 *
	 * @var int
	 */
	protected $eventUid = 0;

	protected function setUp() {
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang_db.xml');
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('lang') . 'locallang_general.xml');

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('plugin', new Tx_Oelib_Configuration());
		$this->configuration = new Tx_Oelib_Configuration();
		$this->configuration->setData(array('charsetForCsv' => 'utf-8'));
		$configurationRegistry->set('plugin.tx_seminars', $this->configuration);

		$this->pageUid = $this->testingFramework->createSystemFolder();
		$this->eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pageUid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$this->subject = new Tx_Seminars_Csv_DownloadRegistrationListView();
		$this->subject->setEventUid($this->eventUid);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->subject, $this->testingFramework, $this->configuration);
	}

	/**
	 * Retrieves the localization for the given locallang key and then strips the trailing colon from the localization.
	 *
	 * @param string $locallangKey
	 *        the locallang key with the localization to remove the trailing colon from, must not be empty and the localization
	 *        must have a trailing colon
	 *
	 * @return string locallang string with the removed trailing colon, will not be empty
	 */
	protected function localizeAndRemoveColon($locallangKey) {
		return rtrim($GLOBALS['LANG']->getLL($locallangKey), ':');
	}

	/**
	 * @test
	 */
	public function renderCanContainOneRegistrationUid() {
		$this->configuration->setAsString('fieldsFromFeUserForCsv', '');
		$this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsFrontEndUserFieldsForDownload() {
		$firstName = 'John';
		$lastName = 'Doe';

		$this->configuration->setAsString('fieldsFromFeUserForCsv', 'first_name');
		$this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'last_name');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('first_name' => $firstName, 'last_name' => $lastName)
				),
			)
		);

		$this->assertContains(
			$firstName,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotContainsFrontEndUserFieldsForEmail() {
		$firstName = 'John';
		$lastName = 'Doe';

		$this->configuration->setAsString('fieldsFromFeUserForCsv', 'first_name');
		$this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'last_name');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('first_name' => $firstName, 'last_name' => $lastName)
				),
			)
		);

		$this->assertNotContains(
			$lastName,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsRegistrationFieldsForDownload() {
		$knownFrom = 'Google';
		$notes = 'Looking forward to the event!';

		$this->configuration->setAsString('fieldsFromAttendanceForCsv', 'known_from');
		$this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'notes');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'known_from' => $knownFrom,
				'notes' => $notes,
			)
		);

		$this->assertContains(
			$knownFrom,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotContainsRegistrationFieldsForEmail() {
		$knownFrom = 'Google';
		$notes = 'Looking forward to the event!';

		$this->configuration->setAsString('fieldsFromAttendanceForCsv', 'known_from');
		$this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'notes');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'known_from' => $knownFrom,
				'notes' => $notes,
			)
		);

		$this->assertNotContains(
			$notes,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForQueueRegistrationsNotAllowedForDownloadNotContainsRegistrationOnQueue() {
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInCSV', FALSE);
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', TRUE);

		$this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');
		$this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'uid');

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'registration_queue' => TRUE,
			)
		);

		$this->assertNotContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForQueueRegistrationsAllowedForDownloadNotContainsRegistrationOnQueue() {
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInCSV', TRUE);
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', FALSE);

		$this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');
		$this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'uid');

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'registration_queue' => TRUE,
			)
		);

		$this->assertContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}
}