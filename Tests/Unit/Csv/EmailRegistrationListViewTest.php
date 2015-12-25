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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Csv_EmailRegistrationListViewTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_Csv_EmailRegistrationListView
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
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('seminars') . 'locallang_db.xml');
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('lang') . 'locallang_general.xml');

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

		$this->subject = new Tx_Seminars_Csv_EmailRegistrationListView();
		$this->subject->setEventUid($this->eventUid);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
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
		$this->configuration->setAsString('fieldsFromFeUserForEmailCsv', '');
		$this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');
		$this->configuration->setAsString('fieldsFromAttendanceForEmailCsv', 'uid');

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		self::assertContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotContainsFrontEndUserFieldsForDownload() {
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

		self::assertNotContains(
			$firstName,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsFrontEndUserFieldsForEmail() {
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

		self::assertContains(
			$lastName,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotContainsRegistrationFieldsForDownload() {
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

		self::assertNotContains(
			$knownFrom,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsRegistrationFieldsForEmail() {
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

		self::assertContains(
			$notes,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForQueueRegistrationsNotAllowedForEmailNotContainsRegistrationOnQueue() {
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', FALSE);
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInCSV', TRUE);

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

		self::assertNotContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForQueueRegistrationsAllowedForEmailNotContainsRegistrationOnQueue() {
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', TRUE);
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInCSV', FALSE);

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

		self::assertContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}
}