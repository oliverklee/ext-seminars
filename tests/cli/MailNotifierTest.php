<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Saskia Metzler <saskia@merlin.owl.de>
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

/**
 * Testcase for the tx_seminars_cli_MailNotifier in the "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_cli_MailNotifierTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_cli_MailNotifier
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		tx_oelib_mailerFactory::getInstance()->enableTestMode();

		// fakes the CLI definition
		define('TYPO3_cliKey', 'seminars');
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']
			['cliKeys'][TYPO3_cliKey][1] = '_cli_seminars_test';
		$this->testingFramework->createBackEndUser(
			array('username' => '_cli_seminars_test')
		);

		$configuration = new tx_oelib_Configuration();
		$configuration->setData(array(
			'sendEventTakesPlaceReminderDaysBeforeBeginDate' => 2,
			'sendCancelationDeadlineReminder' => TRUE,
			'filenameForRegistrationsCsv' => 'registrations.csv',
			'dateFormatYMD' => '%d.%m.%Y',
		));
		tx_oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_seminars', $configuration);

		tx_oelib_templatehelper::setCachedConfigurationValue(
			'fieldsFromAttendanceForEmailCsv', 'title'
		);
		tx_oelib_templatehelper::setCachedConfigurationValue(
			'showAttendancesOnRegistrationQueueInEmailCsv', 1
		);

		$this->fixture = new tx_seminars_cli_MailNotifier();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates a seminar record and an organizer record and the relation
	 * between them.
	 *
	 * @param array $additionalSeminarData additional data for the seminar record, may be empty
	 *
	 * @return integer UID of the added event, will be > 0
	 */
	private function createSeminarWithOrganizer(array $additionalSeminarData = array()) {
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'Mr. Test', 'email' => 'MrTest@example.com')
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array_merge($additionalSeminarData, array('organizers' => 1))
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid
		);

		return $eventUid;
	}

	/**
	 * Adds a speaker relation to an existing seminar record.
	 *
	 * Note: This function must only be called once per test.
	 *
	 * @param integer $eventUid event UID, must be > 0
	 */
	private function addSpeaker($eventUid) {
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('cancelation_period' => 2)
		);

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $eventUid, array('speakers' => 1)
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm', $eventUid, $speakerUid
		);
	}


	/////////////////////////////
	// Utility functions' tests
	/////////////////////////////

	public function testCreateSeminarWithOrganizerCreatesSeminarRecord() {
		$this->createSeminarWithOrganizer();

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', '1=1')
		);
	}

	public function testCreateSeminarWithOrganizerCreatesSeminarRecordWithAdditionalData() {
		$this->createSeminarWithOrganizer(array('title' => 'foo'));

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars', 'title = "foo"'
			)
		);
	}

	public function testCreateSeminarWithOrganizerCreatesOrganizerRecord() {
		$this->createSeminarWithOrganizer();

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_organizers', '1=1')
		);
	}

	public function testCreateSeminarWithOrganizerCreatesRealtionBetweenSeminarAndOrganizer() {
		$this->createSeminarWithOrganizer();

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars_organizers_mm', '1=1'
			)
		);
	}

	public function testAddSpeakerCreatesSpeakerRecord() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_speakers', '1=1')
		);
	}

	public function testAddSpeakerCreatesSpeakerRelation() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars_speakers_mm', '1=1'
			)
		);
	}

	public function testAddSpeakerSetsNumberOfSpeakersToOneForTheSeminarWithTheProvidedUid() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars', 'speakers = 1'
			)
		);
	}


	///////////////////////////////////
	// Tests for setConfigurationPage
	///////////////////////////////////

	public function testSetConfigurationPageThrowsExceptionIfNoPidIsProvided() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Please provide the UID for the page with the configuration ' .
				'for the CLI module.'
		);

		unset($_SERVER['argv'][1]);

		$this->fixture->setConfigurationPage();
	}

	public function testSetConfigurationPageThrowsExceptionIfZeroIsProvidedAsPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The provided UID for the page with the configuration was 0, which was not found to be a UID of an existing page. ' .
				'Please provide the UID of an existing page.'
		);

		$_SERVER['argv'][1] = 0;

		$this->fixture->setConfigurationPage();
	}

	public function testSetConfigurationPageThrowsExceptionIfANonExistingPidIsProvided() {
		$invalidPid = $this->testingFramework->getAutoIncrement('pages');
		$this->setExpectedException(
			'InvalidArgumentException',
			'The provided UID for the page with the configuration was ' . $invalidPid.
				', which was not found to be a UID of an existing page. Please provide the UID of an existing page.'
		);

		$_SERVER['argv'][1] = $invalidPid;

		$this->fixture->setConfigurationPage();
	}

	public function testSetConfigurationPageThrowsNoExceptionIfAnExistingPidIsProvided() {
		$_SERVER['argv'][1] = $this->testingFramework->createFrontEndPage();

		$this->fixture->setConfigurationPage();
	}

	public function testSetConfigurationPageSetsTheExistingPidIsProvidedForThePageFinder() {
		$pageUid = $this->testingFramework->createFrontEndPage();

		$_SERVER['argv'][1] = $pageUid;

		$this->fixture->setConfigurationPage();

		$this->assertEquals(
			$pageUid,
			tx_oelib_PageFinder::getInstance()->getPageUid()
		);
	}


	///////////////////////////////////////////////////
	// Tests concerning sendEventTakesPlaceReminders
	///////////////////////////////////////////////////

	public function testSendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameSendsReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			1,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceSubject() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(
			t3lib_extMgm::extPath('seminars') . 'locallang.xml'
		);
		$subject = $GLOBALS['LANG']->getLL('email_eventTakesPlaceReminderSubject');
		$subject = str_replace('%event', '', $subject);
		$subject = str_replace('%days', 2, $subject);

		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			$subject,
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	public function testSendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceMessage() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(
			t3lib_extMgm::extPath('seminars') . 'locallang.xml'
		);
		$message = $GLOBALS['LANG']->getLL('email_eventTakesPlaceReminder');
		$message = str_replace('%event', '', $message);
		$message = str_replace('%organizer', 'Mr. Test', $message);

		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			substr($message, 0, strpos($message, '%') - 1),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendEventTakesPlaceRemindersForTwoConfirmedEventsWithinConfiguredTimeFrameSendsTwoReminders() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersForConfirmedEventWithTwoOrganizersAndWithinConfiguredTimeFrameSendsTwoReminders() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $eventUid, array('organizers' => 2)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersSetsSentFlagInTheDatabaseWhenReminderWasSent() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars', 'event_takes_place_reminder_sent = 1'
			)
		);
	}

	public function testSendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameAndReminderSentFlagTrueSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			'event_takes_place_reminder_sent' => 1,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersForConfirmedEventWithPassedBeginDateSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersForConfirmedEventBeginningLaterThanConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * tx_oelib_Time::SECONDS_PER_DAY),
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersForConfirmedEventAndNoTimeFrameConfiguredSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->set('sendEventTakesPlaceReminderDaysBeforeBeginDate', 0);

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersForCanceledEventWithinConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendEventTakesPlaceRemindersForPlannedEventWithinConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}


	//////////////////////////////////////////////////////
	// Tests concerning sendCancelationDeadlineReminders
	//////////////////////////////////////////////////////

	public function testSendCancelationDeadlineRemindersForPlannedEventAndOptionEnabledSendsReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			1,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersSendsReminderWithCancelationDeadlineSubject() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(
			t3lib_extMgm::extPath('seminars') . 'locallang.xml'
		);
		$subject = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminderSubject');
		$subject = str_replace('%event', '', $subject);

		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertContains(
			$subject,
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	public function testSendCancelationDeadlineRemindersSendsReminderWithCancelationDeadlineMessage() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry
			::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(
			t3lib_extMgm::extPath('seminars') . 'locallang.xml'
		);
		$message = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminder');
		$message = str_replace('%event', '', $message);
		$message = str_replace('%organizer', 'Mr. Test', $message);

		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertContains(
			substr($message, 0, strpos($message, '%') - 1),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendCancelationDeadlineRemindersForTwoPlannedEventsAndOptionEnabledSendsTwoReminders() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersForPlannedEventWithTwoOrganizersAndOptionEnabledSendsTwoReminders() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		));
		$this->addSpeaker($eventUid);
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $eventUid, array('organizers' => 2)
		);

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersSetsFlagInTheDatabaseWhenReminderWasSent() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars', 'cancelation_deadline_reminder_sent = 1'
			)
		);
	}

	public function testSendCancelationDeadlineRemindersForPlannedEventAndOptionEnabledAndReminderSentFlagTrueSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
			'cancelation_deadline_reminder_sent' => 1,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersForPlannedEventWithPassedBeginDateSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersForPlannedEventWithSpeakersDeadlineNotYetReachedSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * tx_oelib_Time::SECONDS_PER_DAY),
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersForPlannedEventAndOptionDisabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
			->set('sendCancelationDeadlineReminder', FALSE);

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersForCanceledEventAndOptionEnabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	public function testSendCancelationDeadlineRemindersForConfirmedEventAndOptionEnabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertEquals(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}


	///////////////////////////////////////////
	// Tests concerning the reminders content
	//
	// * sender and recipients
	///////////////////////////////////////////

	public function testSendRemindersToOrganizersSendsEmailWithOrganizerAsRecipient() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'MrTest@example.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastRecipient()
		);
	}

	public function testSendRemindersToOrganizersSendsEmailWithOrganizerAsSender() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'MrTest@example.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders()
		);
	}

	public function testSendRemindersToOrganizersForEventWithTwoOrganizersSendsEmailWithFirstOrganizerAsSender() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid
		);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $eventUid, array('organizers' => 2)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'MrTest@example.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders()
		);
	}


	///////////////////
	// * attached CSV
	///////////////////

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithNoAttendancesAndAttachCsvFileTrueNotAttachesRegistrationsCsv() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->set(
				'addRegistrationCsvToOrganizerReminderMail', TRUE
		);

		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertNotContains(
			'registrations.csv',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithAttendancesAndAttachCsvFileTrueAttachesRegistrationsCsv() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->set(
				'addRegistrationCsvToOrganizerReminderMail', TRUE
		);

		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'registrations.csv',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithAttendancesAndAttachCsvFileFalseNotAttachesRegistrationsCsv() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->set(
				'addRegistrationCsvToOrganizerReminderMail', FALSE
		);
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertNotContains(
			'registrations.csv',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithCsvFileWhichContainsRegistration() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->set(
				'addRegistrationCsvToOrganizerReminderMail', TRUE
		);
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$attachment = array();
		preg_match(
			'/filename.*csv([^-=]+)/s',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody(),
			$attachment
		);

		$this->assertContains(
			'test registration' . CRLF,
			base64_decode($attachment[1])
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithCsvFileWithOfFrontEndUserData() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->set(
				'addRegistrationCsvToOrganizerReminderMail', TRUE
		);
		tx_oelib_templatehelper::setCachedConfigurationValue(
			'fieldsFromFeUserForEmailCsv', 'email'
		);

		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@bar.com')
				),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$attachment = array();
		preg_match(
			'/filename.*csv([^-=]+)/s',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody(),
			$attachment
		);

		$this->assertContains(
			'foo@bar.com',
			base64_decode($attachment[1])
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForShowAttendancesOnQueueInEmailCsvSendsEmailWithCsvWithRegistrationsOnQueue() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->set(
			'addRegistrationCsvToOrganizerReminderMail', TRUE
		);
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'real registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'on queue',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registration_queue' => 1
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$attachment = array();
		preg_match(
			'/filename.*csv([^-=]+)/s',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody(),
			$attachment
		);

		$this->assertContains(
			'on queue',
			base64_decode($attachment[1])
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForShowAttendancesOnQueueInEmailCsvFalseSendsEmailWithCsvFileWhichDoesNotContainDataOfAttendanceOnQueue() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')->set(
			'addRegistrationCsvToOrganizerReminderMail', TRUE
		);
		tx_oelib_templatehelper::setCachedConfigurationValue(
			'showAttendancesOnRegistrationQueueInEmailCsv', 0
		);

		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'real registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'on queue',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'registration_queue' => 1
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$attachment = array();
		preg_match(
			'/filename.*csv([^-=]+)/s',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody(),
			$attachment
		);

		$this->assertNotContains(
			'on queue',
			base64_decode($attachment[1])
		);
	}


	/////////////////////////
	// * customized subject
	/////////////////////////

	public function testSendRemindersToOrganizersSendsReminderWithSubjectWithEventTitle() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			'title' => 'test event'
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'test event',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	public function testSendRemindersToOrganizersSendsReminderWithSubjectWithDaysUntilBeginDate() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'2',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}


	/////////////////////////
	// * customized message
	/////////////////////////

	public function testSendRemindersToOrganizersSendsReminderWithMessageWithOrganizerName() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'Mr. Test',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendRemindersToOrganizersSendsReminderWithMessageWithEventTitle() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			'title' => 'test event'
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'test event',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendRemindersToOrganizersSendsReminderWithMessageWithEventUid() {
		$uid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			(string) $uid,
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendRemindersToOrganizersSendsReminderWithMessageWithDaysUntilBeginDate() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'2',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendRemindersToOrganizersSendsReminderWithMessageWithEventsBeginDate() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$this->assertContains(
			strftime(
				tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
					->getAsString('dateFormatYMD'),
				$GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
			),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendRemindersToOrganizersForEventWithNoRegistrationSendsReminderWithMessageWithNumberOfRegistrations() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'0',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function testSendRemindersToOrganizersForEventWithOneRegistrationsSendsReminderWithMessageWithNumberOfRegistrations() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'1',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}


	////////////////////
	// * used language
	////////////////////

	public function testSendRemindersToOrganizersForCliBackendUserWithoutLanguageSendsReminderInDefaultLanguage() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
		$GLOBALS['LANG']->lang = '';
		$subject = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminderSubject');
		$subject = str_replace('%event', '', $subject);

		$this->assertContains(
			$subject,
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	public function testSendRemindersToOrganizersForCliBackendUserWithLanguageGermanSendsReminderInGerman() {
		$this->testingFramework->changeRecord(
			'be_users',
			tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')
				->findByCliKey()->getUid(),
			array('lang' => 'de')
		);
		tx_oelib_MapperRegistry::purgeInstance();

		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancelationDeadlineReminders();

		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
		$GLOBALS['LANG']->lang = 'de';
		$subject = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminderSubject');
		$subject = str_replace('%event', '', $subject);

		$this->assertContains(
			t3lib_div::encodeHeader($subject, 'quoted-printable', 'utf-8'),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}
}
?>