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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_BackEnd_CancelEventMailFormTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_seminars_BackEnd_CancelEventMailForm
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * backed-up extension configuration of the TYPO3 configuration variables
	 *
	 * @var array
	 */
	private $extConfBackup = array();

	/**
	 * backed-up T3_VAR configuration
	 *
	 * @var array
	 */
	private $t3VarBackup = array();

	/**
	 * UID of a dummy system folder
	 *
	 * @var int
	 */
	private $dummySysFolderUid;

	/**
	 * UID of a dummy organizer record
	 *
	 * @var int
	 */
	private $organizerUid;

	/**
	 * UID of a dummy event record
	 *
	 * @var int
	 */
	private $eventUid;

	/**
	 * backup of the BE user's language
	 *
	 * @var string
	 */
	private $languageBackup;

	/**
	 * @var tx_seminars_Service_SingleViewLinkBuilder
	 */
	private $linkBuilder = NULL;

	/**
	 * @var Tx_Oelib_EmailCollector
	 */
	protected $mailer = NULL;

	protected function setUp() {
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = array();

		$this->languageBackup = $GLOBALS['LANG']->lang;
		$GLOBALS['LANG']->lang = 'default';

		// Loads the locallang file for properly working localization in the tests.
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/BackEnd/locallang.xml');

		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		/** @var Tx_Oelib_MailerFactory $mailerFactory */
		$mailerFactory = t3lib_div::makeInstance('Tx_Oelib_MailerFactory');
		$mailerFactory->enableTestMode();
		$this->mailer = $mailerFactory->getMailer();

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		tx_oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

		$this->dummySysFolderUid = $this->testingFramework->createSystemFolder();
		tx_oelib_PageFinder::getInstance()->setPageUid($this->dummySysFolderUid);

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
				'pid' => $this->dummySysFolderUid,
				'title' => 'Dummy event',
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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

		$this->linkBuilder = $this->getMock(
			'tx_seminars_Service_SingleViewLinkBuilder',
			array('createAbsoluteUrlForEvent')
		);
		$this->linkBuilder->expects($this->any())
			->method('createAbsoluteUrlForEvent')
			->will($this->returnValue('http://singleview.example.com/'));
		$this->fixture->injectLinkBuilder($this->linkBuilder);
	}

	protected function tearDown() {
		$GLOBALS['LANG']->lang = $this->languageBackup;

		$this->testingFramework->cleanUp();

		unset($this->linkBuilder, $this->fixture, $this->testingFramework, $this->mailer);

		$this->flushAllFlashMessages();

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
	}

	/**
	 * Returns the rendered flash messages.
	 *
	 * @return string
	 */
	protected function getRenderedFlashMessages() {
		if (class_exists('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService', TRUE)) {
			/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
			$flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
			/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
			$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
			$renderedFlashMessages = $defaultFlashMessageQueue->renderFlashMessages();
		} else {
			$renderedFlashMessages = t3lib_FlashMessageQueue::renderFlashMessages();
		}

		return $renderedFlashMessages;
	}

	/**
	 * Flushes all flash messages from the queue.
	 *
	 * @return void
	 */
	protected function flushAllFlashMessages() {
		if (class_exists('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService', TRUE)) {
			/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
			$flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
			/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
			$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
			$defaultFlashMessageQueue->getAllMessagesAndFlush();
		} else {
			t3lib_FlashMessageQueue::getAllMessagesAndFlush();
		}
	}


	///////////////////////////////////////////////
	// Tests regarding the rendering of the form.
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function renderContainsSubmitButton() {
		$this->assertContains(
			'<button class="submitButton cancelEvent"><p>' .
			$GLOBALS['LANG']->getLL('cancelMailForm_sendButton') .
			'</p></button>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsPrefilledBodyFieldWithLocalizedSalutation() {
		$this->assertContains(
			$GLOBALS['LANG']->getLL('mailForm_salutation'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsTheCancelEventActionForThisForm() {
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
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->eventUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$this->assertNotContains(
			'http://singleview.example.com/',
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
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
		$fixture->injectLinkBuilder($this->linkBuilder);

		$this->assertContains(
			'http://singleview.example.com/',
			$fixture->render()
		);
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
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
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
		$fixture->injectLinkBuilder($this->linkBuilder);

		$this->assertNotContains(
			'http://singleview.example.com/',
			$fixture->render()
		);
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
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME']
					- tx_oelib_Time::SECONDS_PER_DAY,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$dateUid,
			$this->testingFramework->createRecord('tx_seminars_organizers'),
			'organizers'
		);

		$fixture = new tx_seminars_BackEnd_CancelEventMailForm($dateUid);
		$fixture->injectLinkBuilder($this->linkBuilder);

		$this->assertNotContains(
			'http://singleview.example.com/',
			$fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForEmptyLinkShowsErrorMessage() {
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
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicUid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME']
					+ tx_oelib_Time::SECONDS_PER_DAY,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$dateUid,
			$this->testingFramework->createRecord('tx_seminars_organizers'),
			'organizers'
		);

		$fixture = new tx_seminars_BackEnd_CancelEventMailForm($dateUid);

		$linkBuilder = $this->getMock(
			'tx_seminars_Service_SingleViewLinkBuilder',
			array('createAbsoluteUrlForEvent')
		);
		$linkBuilder->expects($this->any())
			->method('createAbsoluteUrlForEvent')->will($this->returnValue(''));
		$fixture->injectLinkBuilder($linkBuilder);

		$this->assertContains(
			$GLOBALS['LANG']->getLL('eventMailForm_error_noDetailsPageFound'),
			$fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForSingleEventEmptyLinkNotShowsErrorMessage() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars',
			$this->eventUid,
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$linkBuilder = $this->getMock(
			'tx_seminars_Service_SingleViewLinkBuilder',
			array('createAbsoluteUrlForEvent')
		);
		$linkBuilder->expects($this->any())
			->method('createAbsoluteUrlForEvent')->will($this->returnValue(''));
		$this->fixture->injectLinkBuilder($linkBuilder);


		$this->assertNotContains(
			$GLOBALS['LANG']->getLL('eventMailForm_error_noDetailsPageFound'),
			$this->fixture->render()
		);
	}


	////////////////////////////////
	// Tests for the localization.
	////////////////////////////////

	/**
	 * @test
	 */
	public function localizationReturnsLocalizedStringForExistingKey() {
		$this->assertEquals(
			'Events',
			$GLOBALS['LANG']->getLL('title')
		);
	}


	////////////////////////////
	// Tests for setEventState
	////////////////////////////

	/**
	 * @test
	 */
	public function setEventStateSetsStatusToCanceled() {
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

	/**
	 * @test
	 */
	public function setEventStateCreatesFlashMessage() {
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

		$this->assertContains(
			$GLOBALS['LANG']->getLL('message_eventCanceled'),
			$this->getRenderedFlashMessages()
		);
	}


	/////////////////////////////////
	// Tests concerning the e-mails
	/////////////////////////////////

	/**
	 * @test
	 */
	public function sendEmailToAttendeesSendsEmailWithNameOfRegisteredUserOnSubmitOfValidForm() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'',
					array('email' => 'foo@example.com', 'name' => 'foo User')
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
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailCallsHookWithRegistration() {
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com', 'name' => 'foo User')
				),
			)
		);

		$registration = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Registration')->find($registrationUid);
		$hook = $this->getMock('tx_seminars_Interface_Hook_BackEndModule');
		$hook->expects($this->once())->method('modifyCancelEmail')
			->with($registration, $this->anything());

		$hookClass = get_class($hook);
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClass] = $hookClass;

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'some message body',
			)
		);
		$this->fixture->render();
	}

	/**
	 * @test
	 */
	public function sendEmailForTwoRegistrationsCallsHookTwice() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com', 'name' => 'foo User')
				),
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'bar@example.com', 'name' => 'foo User')
				),
			)
		);

		$hook = $this->getMock('tx_seminars_Interface_Hook_BackEndModule');
		$hook->expects($this->exactly(2))->method('modifyCancelEmail');

		$hookClass = get_class($hook);
		$GLOBALS['T3_VAR']['getUserObj'][$hookClass] = $hook;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClass] = $hookClass;

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'some message body',
			)
		);
		$this->fixture->render();
	}
}