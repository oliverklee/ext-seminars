<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Mario Rimann (mario@screenteam.com)
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

/**
 * Testcase for the 'CancelEventMailForm' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_BackEnd_CancelEventMailForm_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_CancelEventMailForm
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer PID of a dummy system folder
	 */
	private $dummySysFolderPid;

	/**
	 * @var integer UID of a dummy event record
	 */
	private $eventUid;

	/**
	 * @var integer UID of a dummy organizer record
	 */
	private $organizerUid;

	/**
	 * @var string the original language of the BE user
	 */
	private $originalLanguage;

	public function setUp() {
		// Set's the localization to the default language so that all tests can
		// run, even if the BE user has it's interface set to another language.
		$this->originalLanguage = $GLOBALS['LANG']->lang;
		$GLOBALS['LANG']->lang = 'default';

		// Loads the locallang file for properly working localization in the tests.
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/BackEnd/locallang.xml');

		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		tx_oelib_mailerFactory::getInstance()->enableTestMode();

		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid
			= $this->testingFramework->createSystemFolder();
		tx_oelib_PageFinder::getInstance()->setPageUid($this->dummySysFolderPid);

		$this->organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'Dummy Organizer',
				'email' => 'foo@example.org',
			)
		);

		$this->eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'Dummy event',
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 86400,
				'organizers' => 0,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$this->eventUid,
			$this->organizerUid,
			'organizers'
		);

		$this->fixture = new tx_seminars_BackEnd_CancelEventMailForm(
			$this->eventUid
		);
	}

	public function tearDown() {
		// Resets the language of the interface to the value it had before
		// we set it to "default" for testing.
		$GLOBALS['LANG']->lang = $this->originalLanguage;

		$this->fixture->__destruct();
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}



	///////////////////////////////////////////////
	// Tests regarding the rendering of the form.
	///////////////////////////////////////////////

	public function testRenderContainsSubmitButton() {
		$this->assertContains(
			'<button class="submitButton cancelEvent"><p>' .
			$GLOBALS['LANG']->getLL('cancelMailForm_sendButton') .
			'</p></button>',
			$this->fixture->render()
		);
	}

	public function testRenderContainsPrefilledBodyFieldWithLocalizedSalutation() {
		$this->assertContains(
			$GLOBALS['LANG']->getLL('mailForm_salutation'),
			$this->fixture->render()
		);
	}

	public function testRenderContainsTheCancelEventActionForThisForm() {
		$this->assertContains(
			'<input type="hidden" name="action" value="cancelEvent" />',
			$this->fixture->render()
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning the link to the single view
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function renderForSingleEventDoesNotAppendSingleViewLink() {
		$detailPid = $this->testingFramework->createFrontEndPage();
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', $detailPid);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->eventUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$this->assertNotContains(
			(string) $detailPid,
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForDateWithOtherDatesInFutureAppendsSingleViewLink() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'Dummy event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$dateUid,
			$this->testingFramework->createRecord('tx_seminars_organizers'),
			'organizers'
		);

		$fixture = new tx_seminars_BackEnd_CancelEventMailForm($dateUid);

		$detailPid = $this->testingFramework->createFrontEndPage();
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', $detailPid);

		$this->assertContains(
			$GLOBALS['LANG']->getLL('cancelMailForm_alternativeDate'),
			$fixture->render()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function renderForDateWithoutOtherDatesNotAppendsSingleViewLink() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'Dummy event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$dateUid,
			$this->testingFramework->createRecord('tx_seminars_organizers'),
			'organizers'
		);

		$fixture = new tx_seminars_BackEnd_CancelEventMailForm($dateUid);

		$detailPid = $this->testingFramework->createFrontEndPage();
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', $detailPid);

		$this->assertNotContains(
			(string) $detailPid,
			$fixture->render()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function renderForDateWithOtherDatesInPastNotAppendsSingleViewLink() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'Dummy event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - ONE_DAY,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$dateUid,
			$this->testingFramework->createRecord('tx_seminars_organizers'),
			'organizers'
		);

		$fixture = new tx_seminars_BackEnd_CancelEventMailForm($dateUid);

		$detailPid = $this->testingFramework->createFrontEndPage();
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', $detailPid);

		$this->assertNotContains(
			(string) $detailPid,
			$fixture->render()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function renderForDateWithoutSetDetailPidShowsErrorMessage() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'Dummy event',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + ONE_DAY,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$dateUid,
			$this->testingFramework->createRecord('tx_seminars_organizers'),
			'organizers'
		);

		$fixture = new tx_seminars_BackEnd_CancelEventMailForm($dateUid);

		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', 0);

		$this->assertContains(
			$GLOBALS['LANG']->getLL('eventMailForm_error_noDetailsPageFound'),
			$fixture->render()
		);

		$fixture->__destruct();
	}

	/**
	 * @test
	 */
	public function renderForSingleEventAndNoSetDetailPidNotShowsErrorMessage() {
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
			->set('detailPID', 0);
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->eventUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$this->assertNotContains(
			$GLOBALS['LANG']->getLL('eventMailForm_error_noDetailsPageFound'),
			$this->fixture->render()
		);
	}


	////////////////////////////////
	// Tests for the localization.
	////////////////////////////////

	public function testLocalizationReturnsLocalizedStringForExistingKey() {
		$this->assertEquals(
			'Events',
			$GLOBALS['LANG']->getLL('title')
		);
	}


	////////////////////////////
	// Tests for setEventState
	////////////////////////////

	public function testSetEventStateSetsStatusToCanceled() {
		$this->fixture->setPostData(
			array(
				'action' => 'cancelEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_seminars_seminars',
				'uid = ' . $this->eventUid . ' AND cancelled = ' .
					tx_seminars_seminar::STATUS_CANCELED
			)
		);
	}


	/////////////////////////////////
	// Tests concerning the e-mails
	/////////////////////////////////

	public function testSendEmailToRegistrationsSendsEmailWithNameOfRegisteredUserOnSubmitOfValidForm() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'',
					array('email' => 'foo@valid-email.org', 'name' => 'foo User')
				)
			)
		);

		$messageBody = '%' . $GLOBALS['LANG']->getLL('mailForm_salutation') .
			$GLOBALS['LANG']->getLL('confirmMailForm_prefillField_messageBody');
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => $messageBody,
			)
		);
		$this->fixture->render();

		$this->assertContains(
			'foo User',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
		);
	}
}
?>