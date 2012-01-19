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
 * Testcase for the 'EventMailForm' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_BackEnd_EventMailForm_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_EventMailForm
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

		tx_oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_seminars', new tx_oelib_Configuration());

		$this->dummySysFolderPid
			= $this->testingFramework->createSystemFolder();
		tx_oelib_PageFinder::getInstance()->setPageUid($this->dummySysFolderPid);

		$this->organizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'Dummy Organizer',
				'email' => 'foo@example.org',
			)
		);

		$this->eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'organizers' => 0,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
				'title' => 'Dummy Event',
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$this->eventUid,
			$this->organizerUid,
			'organizers'
		);

		$this->fixture = new tx_seminars_tests_fixtures_TestingEventMailForm(
			$this->eventUid
		);
		$this->fixture->setDateFormat();
	}

	public function tearDown() {
		// Resets the language of the interface to the value it had before
		// we set it to "default" for testing.
		$GLOBALS['LANG']->lang = $this->originalLanguage;

		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////////////////////////////////
	// Tests regarding the error handling of the form
	///////////////////////////////////////////////////

	public function testRenderThrowsExceptionForInvalidEventUid() {
		$this->setExpectedException('Exception', 'There is no event with this UID.');

		new tx_seminars_tests_fixtures_TestingEventMailForm(
			$this->testingFramework->getAutoIncrement(SEMINARS_TABLE_SEMINARS)
		);
	}


	//////////////////////////////////////////////
	// Tests regarding the rendering of the form
	//////////////////////////////////////////////

	public function testFormActionContainsCurrentPage() {
		tx_oelib_PageFinder::getInstance()->setPageUid(42);

		$this->assertContains(
			'?id=42',
			$this->fixture->render()
		);
	}

	public function testRenderContainsEventTitleInSubjectFieldForNewForm() {
		$this->assertContains(
			'Dummy Event',
			$this->fixture->render()
		);
	}

	public function testRenderContainsPrefilledBodyField() {
		$this->assertContains(
			$GLOBALS['LANG']->getLL('testForm_prefillField_messageBody'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsBodyFieldWithIntroduction() {
		$this->assertContains(
			sprintf(
				$GLOBALS['LANG']->getLL('testForm_prefillField_introduction'),
				htmlspecialchars('"Dummy Event"')
			),
			$this->fixture->render()
		);
	}

	public function testRenderDoesNotPrefillSubjectFieldIfEmptyStringWasSentViaPost() {
		$this->fixture->setPostData(
			array(
				'action' => 'cancelEvent',
				'isSubmitted' => '1',
				'subject' => '',
			)
		);

		$this->assertNotContains(
			'Dummy event',
			$this->fixture->render()
		);
	}

	public function testRenderContainsOrganizerNameAsSenderForEventWithOneOrganizer() {
		$this->assertContains(
			'<input type="hidden" id="sender" name="sender" value="' .
				$this->organizerUid . '" />' .
				htmlspecialchars('"Dummy Organizer" <foo@example.org>'),
			$this->fixture->render()
		);
	}

	public function testRenderContainsEventDateInSubjectFieldForNewFormAndEventWithBeginDate() {
		$this->assertContains(
			strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42),
			$this->fixture->render()
		);
	}

	public function testRenderContainsDropDownForSenderSelectionForEventWithMultipleOrganizers() {
		$secondOrganizerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS,
			array(
				'title' => 'Second Organizer',
				'email' => 'bar@example.org',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$this->eventUid,
			$secondOrganizerUid,
			'organizers'
		);

		$formOutput = $this->fixture->render();

		$this->assertContains(
			'<select id="sender" name="sender">',
			$formOutput
		);
		$this->assertContains(
			'<option value="' . $this->organizerUid . '">' . htmlspecialchars('"Dummy Organizer" <foo@example.org>'),
			$formOutput
		);
		$this->assertContains(
			'<option value="' . $secondOrganizerUid . '">' . htmlspecialchars('"Second Organizer" <bar@example.org>'),
			$formOutput
		);
	}

	public function testRenderSanitizesPostDataWhenPreFillingAFormField() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'messageBody' => '<test>',
			)
		);
		$formOutput = $this->fixture->render();

		$this->assertContains(
			'&lt;test&gt;',
			$formOutput
		);
	}

	public function testRenderFormContainsCancelButton() {
		$this->assertContains(
			'<input type="button" value="' .
				$GLOBALS['LANG']->getLL('eventMailForm_backButton') .
				'" class="backButton"' .
				' onclick="window.location=window.location" />',
			$this->fixture->render()
		);
	}

	public function testRenderContainsErrorMessageIfFormWasSubmittedWithEmptySubjectField() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => '',
			)
		);

		$this->assertContains(
			$GLOBALS['LANG']->getLL('eventMailForm_error_subjectMustNotBeEmpty'),
			$this->fixture->render()
		);
	}

	public function testRenderContainsErrorMessageIfFormWasSubmittedWithEmptyMessageField() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'messageBody' => '',
			)
		);

		$this->assertContains(
			$GLOBALS['LANG']->getLL('eventMailForm_error_messageBodyMustNotBeEmpty'),
			$this->fixture->render()
		);
	}

	public function testRenderContainsSubjectFieldPrefilledByUserInputIfFormIsReRendered() {
		$this->fixture->setPostData(
			array(
				'action' => 'sendForm',
				'isSubmitted' => '1',
				'subject' => 'foo bar',
			)
		);
		$this->fixture->markAsIncomplete();

		$this->assertContains(
			'foo bar',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderEncodesHtmlSpecialCharsInSubjectField() {
		$this->fixture->setPostData(
			array(
				'action' => 'sendForm',
				'isSubmitted' => '1',
				'subject' => '<foo> & "bar"',
			)
		);
		$this->fixture->markAsIncomplete();

		$this->assertContains(
			'&lt;foo&gt; &amp; &quot;bar&quot;',
			$this->fixture->render()
		);
	}

	public function testRenderContainsMessageFieldPrefilledByUserInputIfFormIsReRendered() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->markAsIncomplete();

		$this->assertContains(
			'foo bar',
			$this->fixture->render()
		);
	}

	public function testRenderContainsHiddenFieldWithVariableEventUid() {
		$this->assertContains(
			'<input type="hidden" name="eventUid" value="' . $this->eventUid . '" />',
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


	///////////////////////////////////////
	// Tests for sendEmailToRegistrations
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function sendEmailToRegistrationsSendsEmailWithSubjectOnSubmitOfValidForm() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@valid-email.org'))
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertEquals(
			'foo',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}


	/**
	 * @test
	 */
	public function sendEmailToRegistrationsForAttendeeWithoutEMailAddressDoesNotSendMail() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser()
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertEquals(
			array(),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToRegistrationsInsertsUserNameIntoMailText() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array(
						'email' => 'foo@valid-email.org', 'name' => 'test user'
					)
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar %' .
					$GLOBALS['LANG']->getLL('mailForm_salutation'),
			)
		);
		$this->fixture->render();

		$this->assertContains(
			'test user',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToRegistrationsWithoutReplacementMarkerInBodyDoesNotCrash() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array(
						'email' => 'foo@valid-email.org', 'name' => 'test user'
					)
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar foo',
			)
		);

		$this->fixture->render();
	}

	/**
	 * @test
	 */
	public function sendEmailToRegistrationsUsesSelectedOrganizerAsSender() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@valid-email.org')
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
				'sender' => $this->testingFramework->createRecord(
					SEMINARS_TABLE_ORGANIZERS,
					array(
						'title' => 'Second Organizer',
						'email' => 'bar@example.org',
					)
				)
			)
		);
		$this->fixture->render();

		$this->assertContains(
			'bar@example.org',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToRegistrationsForEventWithTwoRegistrationsSendsTwoEmails() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@valid-email.org')
				)
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@valid-email.org')
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
				'sender' => $this->testingFramework->createRecord(
					SEMINARS_TABLE_ORGANIZERS,
					array(
						'title' => 'Second Organizer',
						'email' => 'bar@example.org',
					)
				)
			)
		);
		$this->fixture->render();

		$this->assertEquals(
			2,
			count(tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail())
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToRegistrationsAppendsOrganizersFooterToMessageBodyIfSet() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@valid-email.org')
				)
			)
		);
		$organizerFooter = 'organizer footer';

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
				'sender' => $this->testingFramework->createRecord(
					SEMINARS_TABLE_ORGANIZERS,
					array(
						'title' => 'Second Organizer',
						'email' => 'bar@example.org',
						'email_footer' => $organizerFooter,
					)
				)
			)
		);
		$this->fixture->render();

		$this->assertContains(
			LF . '-- ' . LF . $organizerFooter,
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToRegistrationsForOrganizerWithoutFooterDoesNotAppendFooterMarkersToMessageBody() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@valid-email.org')
				)
			)
		);
		$organizerFooter = 'organizer footer';

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
				'sender' => $this->organizerUid
			)
		);
		$this->fixture->render();

		$this->assertNotContains(
			LF . '-- ' . LF,
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}


	/////////////////////////////////
	// Tests for redirectToListView
	/////////////////////////////////

	public function testRedirectToListViewSendsTheRedirectHeader() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertEquals(
			'Location: ' . t3lib_div::locationHeaderUrl(
				'/typo3conf/ext/seminars/BackEnd/index.php?id=' .
				tx_oelib_PageFinder::getInstance()->getPageUid()
			),
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}


	/////////////////////////////////////
	// Tests concerning getInitialValue
	/////////////////////////////////////

	public function test_getInitialValueForSubject_AppendsEventTitle() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->eventUid, array('title' => 'FooBar')
		);

		$fixture = new tx_seminars_tests_fixtures_TestingEventMailForm(
			$this->eventUid
		);

		$this->assertContains(
			'FooBar',
			$fixture->getInitialValue('subject')
		);

		$fixture->__destruct();
	}

	public function test_getInitialValueForSubject_AppendsEventDate() {
		$beginDate = strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42);

		$this->assertContains(
			$beginDate,
			$this->fixture->getInitialValue('subject')
		);
	}

	public function test_getInitialValueForFoo_ThrowsException() {
		$this->setExpectedException(
			'Exception',
			'There is no initial value for the field "foo" defined.'
		);

		$this->fixture->getInitialValue('foo');
	}


	////////////////////////////////////////
	// Tests concerning the error messages
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getErrorMessageForIncompleteFormAndNoStoredMessageReturnsEmptyString() {
		$this->fixture->markAsIncomplete();

		$this->assertEquals(
			'',
			$this->fixture->getErrorMessage('subject')
		);
	}

	/**
	 * @test
	 */
	public function getErrorMessageForCompleteFormAndStoredMessageReturnsStoredMessage() {
		$this->fixture->setErrorMessage('subject', 'Foo');

		$this->assertContains(
			'Foo',
			$this->fixture->getErrorMessage('subject')
		);
	}

	/**
	 * @test
	 */
	public function getErrorMessageForInCompleteFormAndStoredMessageReturnsThisErrorMessage() {
		$this->fixture->markAsIncomplete();
		$this->fixture->setErrorMessage('subject', 'Foo');

		$this->assertContains(
			'Foo',
			$this->fixture->getErrorMessage('subject')
		);
	}

	/**
	 * @test
	 */
	public function setErrorMessageForAlreadySetErrorMessageAppendsNewMessage() {
		$this->fixture->markAsIncomplete();
		$this->fixture->setErrorMessage('subject', 'Foo');
		$this->fixture->setErrorMessage('subject', 'Bar');
		$errorMessage = $this->fixture->getErrorMessage('subject');

		$this->assertContains(
			'Foo',
			$errorMessage
		);
		$this->assertContains(
			'Bar',
			$errorMessage
		);
	}
}
?>