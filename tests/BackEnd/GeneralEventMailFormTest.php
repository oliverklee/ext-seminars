<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 */
class tx_seminars_BackEnd_GeneralEventMailFormTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_GeneralEventMailForm
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
	 * backup of the BE user's language
	 *
	 * @var string
	 */
	private $languageBackup;

	/**
	 * UID of a dummy organizer record
	 *
	 * @var integer
	 */
	private $organizerUid;

	/**
	 * UID of a dummy event record
	 *
	 * @var integer
	 */
	private $eventUid;

	public function setUp() {
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = array();

		$this->languageBackup = $GLOBALS['LANG']->lang;
		$GLOBALS['LANG']->lang = 'default';

		// Loads the locallang file for properly working localization in the tests.
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/BackEnd/locallang.xml');

		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		tx_oelib_mailerFactory::getInstance()->enableTestMode();

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

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
				'organizers' => 1,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
				'title' => 'Dummy Event',
				'registrations' => 1,
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$this->eventUid,
			$this->organizerUid,
			'organizers'
		);

		$this->fixture = new tx_seminars_BackEnd_GeneralEventMailForm(
			$this->eventUid
		);
	}

	public function tearDown() {
		$GLOBALS['LANG']->lang = $this->languageBackup;

		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);

		t3lib_FlashMessageQueue::getAllMessagesAndFlush();

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
	}


	///////////////////////////////////////////////
	// Tests regarding the rendering of the form.
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function renderContainsSubmitButton() {
		$this->assertContains(
			'<button class="submitButton sendEmail"><p>' .
			$GLOBALS['LANG']->getLL('generalMailForm_sendButton') .
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
			'<input type="hidden" name="action" value="sendEmail" />',
			$this->fixture->render()
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
				'pid' => $this->dummySysFolderPid,
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
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
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
		$hook->expects($this->once())->method('modifyGeneralEmail')
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
		$hook->expects($this->exactly(2))->method('modifyGeneralEmail');

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