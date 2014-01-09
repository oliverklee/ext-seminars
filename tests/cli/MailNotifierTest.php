<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2014 Saskia Metzler <saskia@merlin.owl.de>
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
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_cli_MailNotifierTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_cli_MailNotifier
	 */
	protected $fixture = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	protected function setUp() {
		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
		tx_oelib_mailerFactory::getInstance()->enableTestMode();

		define('TYPO3_cliKey', 'seminars');
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][TYPO3_cliKey][1] = '_cli_seminars_test';
		$this->testingFramework->createBackEndUser(array('username' => '_cli_seminars_test'));

		tx_oelib_ConfigurationRegistry::getInstance()->set('plugin', new Tx_Oelib_Configuration());
		$this->configuration = new Tx_Oelib_Configuration();
		$this->configuration->setData(array(
			'sendEventTakesPlaceReminderDaysBeforeBeginDate' => 2,
			'sendCancelationDeadlineReminder' => TRUE,
			'filenameForRegistrationsCsv' => 'registrations.csv',
			'dateFormatYMD' => '%d.%m.%Y',
			'fieldsFromAttendanceForEmailCsv' => 'title',
			'showAttendancesOnRegistrationQueueInEmailCsv' => TRUE
		));
		tx_oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

		$this->fixture = new tx_seminars_cli_MailNotifier();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework, $this->configuration);
	}


	/*
	 * Utility functions
	 */

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

		$this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);

		return $eventUid;
	}

	/**
	 * Adds a speaker relation to an existing seminar record.
	 *
	 * Note: This function must only be called once per test.
	 *
	 * @param integer $eventUid event UID, must be > 0
	 *
	 * @return void
	 */
	private function addSpeaker($eventUid) {
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array('cancelation_period' => 2)
		);

		$this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, array('speakers' => 1));
		$this->testingFramework->createRelation('tx_seminars_seminars_speakers_mm', $eventUid, $speakerUid);
	}


	/*
	 * Tests for the utility functions
	 */

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesSeminarRecord() {
		$this->createSeminarWithOrganizer();

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesSeminarRecordWithAdditionalData() {
		$this->createSeminarWithOrganizer(array('title' => 'foo'));

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', 'title = "foo"')
		);
	}

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesOrganizerRecord() {
		$this->createSeminarWithOrganizer();

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_organizers', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function createSeminarWithOrganizerCreatesRealtionBetweenSeminarAndOrganizer() {
		$this->createSeminarWithOrganizer();

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars_organizers_mm', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerCreatesSpeakerRecord() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_speakers', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerCreatesSpeakerRelation() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars_speakers_mm', '1=1')
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerSetsNumberOfSpeakersToOneForTheSeminarWithTheProvidedUid() {
		$this->addSpeaker($this->createSeminarWithOrganizer());

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', 'speakers = 1')
		);
	}


	/*
	 * Tests for setConfigurationPage
	 */

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsExceptionIfNoPidIsProvided() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'Please provide the UID for the page with the configuration ' .
				'for the CLI module.'
		);

		unset($_SERVER['argv'][1]);

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsExceptionIfZeroIsProvidedAsPid() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The provided UID for the page with the configuration was 0, which was not found to be a UID of an existing page. ' .
				'Please provide the UID of an existing page.'
		);

		$_SERVER['argv'][1] = 0;

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsExceptionIfNonExistingPidIsProvided() {
		$invalidPid = $this->testingFramework->getAutoIncrement('pages');
		$this->setExpectedException(
			'InvalidArgumentException',
			'The provided UID for the page with the configuration was ' . $invalidPid .
				', which was not found to be a UID of an existing page. Please provide the UID of an existing page.'
		);

		$_SERVER['argv'][1] = $invalidPid;

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageThrowsNoExceptionIfAnExistingPidIsProvided() {
		$_SERVER['argv'][1] = $this->testingFramework->createFrontEndPage();

		$this->fixture->setConfigurationPage();
	}

	/**
	 * @test
	 */
	public function setConfigurationPageSetsTheExistingPidIsProvidedForThePageFinder() {
		$pageUid = $this->testingFramework->createFrontEndPage();

		$_SERVER['argv'][1] = $pageUid;

		$this->fixture->setConfigurationPage();

		$this->assertSame(
			$pageUid,
			tx_oelib_PageFinder::getInstance()->getPageUid()
		);
	}


	/*
	 * Tests concerning sendEventTakesPlaceReminders
	 */

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameSendsReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			1,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceSubject() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
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

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersSendsReminderWithEventTakesPlaceMessage() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
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
			quoted_printable_decode(tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForTwoConfirmedEventsWithinConfiguredTimeFrameSendsTwoReminders() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithTwoOrganizersAndWithinConfiguredTimeFrameSendsTwoReminders() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
		$this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, array('organizers' => 2));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersSetsSentFlagInTheDatabaseWhenReminderWasSent() {
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

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithinConfiguredTimeFrameAndReminderSentFlagTrueSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			'event_takes_place_reminder_sent' => 1,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventWithPassedBeginDateSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventBeginningLaterThanConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * tx_oelib_Time::SECONDS_PER_DAY),
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForConfirmedEventAndNoTimeFrameConfiguredSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$this->configuration->setAsInteger('sendEventTakesPlaceReminderDaysBeforeBeginDate', 0);

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForCanceledEventWithinConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEventTakesPlaceRemindersForPlannedEventWithinConfiguredTimeFrameSendsNoReminder() {
		$this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}


	/*
	 * Tests concerning sendCancellationDeadlineReminders
	 */

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventAndOptionEnabledSendsReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			1,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineSubject() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
		$subject = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminderSubject');
		$subject = str_replace('%event', '', $subject);

		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertContains(
			$subject,
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersSendsReminderWithCancelationDeadlineMessage() {
		$GLOBALS['LANG']->lang = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_BackEndUser')->findByCliKey()->getLanguage();
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
		$message = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminder');
		$message = str_replace('%event', '', $message);
		$message = str_replace('%organizer', 'Mr. Test', $message);

		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertContains(
			substr($message, 0, strpos($message, '%') - 1),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForTwoPlannedEventsAndOptionEnabledSendsTwoReminders() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventWithTwoOrganizersAndOptionEnabledSendsTwoReminders() {
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

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersSetsFlagInTheDatabaseWhenReminderWasSent() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertTrue(
			$this->testingFramework->existsRecord('tx_seminars_seminars', 'cancelation_deadline_reminder_sent = 1')
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventAndOptionEnabledAndReminderSentFlagTrueSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
			'cancelation_deadline_reminder_sent' => 1,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventWithPassedBeginDateSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventWithSpeakersDeadlineNotYetReachedSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (3 * tx_oelib_Time::SECONDS_PER_DAY),
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForPlannedEventAndOptionDisabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));
		$this->configuration->setAsBoolean('sendCancelationDeadlineReminder', FALSE);

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForCanceledEventAndOptionEnabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendCancellationDeadlineRemindersForConfirmedEventAndOptionEnabledSendsNoReminder() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertSame(
			0,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}


	/*
	 * Tests concerning the reminders content
	 *
	 * * sender and recipients
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithOrganizerAsRecipient() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsEmailWithOrganizerAsSender() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithTwoOrganizersSendsEmailWithFirstOrganizerAsSender() {
		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array('title' => 'foo', 'email' => 'foo@example.com')
		);
		$this->testingFramework->createRelation('tx_seminars_seminars_organizers_mm', $eventUid, $organizerUid);
		$this->testingFramework->changeRecord('tx_seminars_seminars', $eventUid, array('organizers' => 2));

		$this->fixture->sendEventTakesPlaceReminders();

		$this->assertContains(
			'MrTest@example.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders()
		);
	}


	/*
	 * * attached CSV
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithNoAttendancesAndAttachCsvFileTrueNotAttachesRegistrationsCsv() {
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);

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
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);

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
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', FALSE);
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
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
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
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
		$this->configuration->setAsString('fieldsFromFeUserForEmailCsv', 'email');

		$eventUid = $this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
		));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances', array(
				'title' => 'test registration',
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser('', array('email' => 'foo@bar.com')),
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
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
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
		$this->configuration->setAsBoolean('addRegistrationCsvToOrganizerReminderMail', TRUE);
		$this->configuration->setAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv', FALSE);

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


	/*
	 * * customized subject
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithSubjectWithEventTitle() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithSubjectWithDaysUntilBeginDate() {
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


	/*
	 * * customized message
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithOrganizerName() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithEventTitle() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithEventUid() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithDaysUntilBeginDate() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersSendsReminderWithMessageWithEventsBeginDate() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$this->assertContains(
			strftime(
				$this->configuration->getAsString('dateFormatYMD'),
				$GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY
			),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithNoRegistrationSendsReminderWithMessageWithNumberOfRegistrations() {
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

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForEventWithOneRegistrationsSendsReminderWithMessageWithNumberOfRegistrations() {
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


	/*
	 * * used language
	 */

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForCliBackendUserWithoutLanguageSendsReminderInDefaultLanguage() {
		$this->addSpeaker($this->createSeminarWithOrganizer(array(
			'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY,
			'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
		)));

		$this->fixture->sendCancellationDeadlineReminders();

		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
		$GLOBALS['LANG']->lang = '';
		$subject = $GLOBALS['LANG']->getLL('email_cancelationDeadlineReminderSubject');
		$subject = str_replace('%event', '', $subject);

		$this->assertContains(
			$subject,
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendRemindersToOrganizersForCliBackendUserWithLanguageGermanSendsReminderInGerman() {
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

		$this->fixture->sendCancellationDeadlineReminders();

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