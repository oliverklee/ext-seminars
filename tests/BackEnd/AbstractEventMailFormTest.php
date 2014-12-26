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
 */
class Tx_Seminars_BackEnd_AbstractEventMailFormTest extends Tx_Phpunit_TestCase {
	/**
	 * @var tx_seminars_BackEnd_AbstractEventMailForm
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

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
	 * @var Tx_Oelib_EmailCollector
	 */
	protected $mailer = NULL;

	protected function setUp() {
		$configuration = new Tx_Oelib_Configuration();
		Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

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

		$this->fixture = new tx_seminars_tests_fixtures_BackEnd_TestingEventMailForm($this->eventUid);
		$this->fixture->setDateFormat();
	}

	protected function tearDown() {
		$GLOBALS['LANG']->lang = $this->languageBackup;

		$this->testingFramework->cleanUp();

		$this->flushAllFlashMessages();
	}

	/**
	 * Returns the rendered flash messages.
	 *
	 * @return string
	 */
	protected function getRenderedFlashMessages() {
		if (class_exists('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService', TRUE)) {
			/** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
			$flashMessageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
			/** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $defaultFlashMessageQueue */
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


	///////////////////////////////////////////////////
	// Tests regarding the error handling of the form
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function renderThrowsExceptionForInvalidEventUid() {
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'There is no event with this UID.'
		);

		new tx_seminars_tests_fixtures_BackEnd_TestingEventMailForm(
			$this->testingFramework->getAutoIncrement('tx_seminars_seminars')
		);
	}


	//////////////////////////////////////////////
	// Tests regarding the rendering of the form
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function formActionContainsCurrentPage() {
		tx_oelib_PageFinder::getInstance()->setPageUid(42);

		$this->assertContains(
			'&amp;id=42',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsEventTitleInSubjectFieldForNewForm() {
		$this->assertContains(
			'Dummy Event',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsPrefilledBodyField() {
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

	/**
	 * @test
	 */
	public function renderNotPrefillsSubjectFieldIfEmptyStringWasSentViaPost() {
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

	/**
	 * @test
	 */
	public function renderContainsOrganizerNameAsSenderForEventWithOneOrganizer() {
		$this->assertContains(
			'<input type="hidden" id="sender" name="sender" value="' .
				$this->organizerUid . '" />' .
				htmlspecialchars('"Dummy Organizer" <foo@example.org>'),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsEventDateInSubjectFieldForNewFormAndEventWithBeginDate() {
		$this->assertContains(
			strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42),
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsDropDownForSenderSelectionForEventWithMultipleOrganizers() {
		$secondOrganizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers',
			array(
				'title' => 'Second Organizer',
				'email' => 'bar@example.org',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function renderSanitizesPostDataWhenPreFillingAFormField() {
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

	/**
	 * @test
	 */
	public function renderFormContainsCancelButton() {
		$this->assertContains(
			'<input type="button" value="' .
				$GLOBALS['LANG']->getLL('eventMailForm_backButton') .
				'" class="backButton"' .
				' onclick="window.location=window.location" />',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderContainsErrorMessageIfFormWasSubmittedWithEmptySubjectField() {
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

	/**
	 * @test
	 */
	public function renderContainsErrorMessageIfFormWasSubmittedWithEmptyMessageField() {
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

	/**
	 * @test
	 */
	public function renderContainsSubjectFieldPrefilledByUserInputIfFormIsReRendered() {
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

	/**
	 * @test
	 */
	public function renderContainsMessageFieldPrefilledByUserInputIfFormIsReRendered() {
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

	/**
	 * @test
	 */
	public function renderContainsHiddenFieldWithVariableEventUid() {
		$this->assertContains(
			'<input type="hidden" name="eventUid" value="' . $this->eventUid . '" />',
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


	///////////////////////////////////
	// Tests for sendEmailToAttendees
	///////////////////////////////////

	/**
	 * @test
	 */
	public function sendEmailToAttendeesSendsEmailWithSubjectOnSubmitOfValidForm() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com'))
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertSame(
			'foo',
			$this->mailer->getFirstSentEmail()->getSubject()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesForAttendeeWithoutEMailAddressDoesNotSendMail() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser()
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertNull(
			$this->mailer->getFirstSentEmail()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesInsertsUserNameIntoMailText() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array(
						'email' => 'foo@example.com', 'name' => 'test user'
					)
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar %' .
					$GLOBALS['LANG']->getLL('mailForm_salutation'),
			)
		);
		$this->fixture->render();

		$this->assertContains(
			'test user',
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesWithoutReplacementMarkerInBodyDoesNotCrash() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array(
						'email' => 'foo@example.com', 'name' => 'test user'
					)
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar foo',
			)
		);

		$this->fixture->render();
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesUsesSelectedOrganizerAsSender() {
		$secondOrganizer = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Organizer')->getLoadedTestingModel(array(
				'title' => 'Second Organizer',
				'email' => 'bar@example.org',
			));

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com')
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
				'sender' => (string) $secondOrganizer->getUid(),
			)
		);
		$this->fixture->render();

		$this->assertArrayHasKey(
			'bar@example.org',
			$this->mailer->getFirstSentEmail()->getFrom()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesForEventWithTwoRegistrationsSendsTwoEmails() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com')
				)
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com')
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
				'sender' => $this->organizerUid,
			)
		);
		$this->fixture->render();

		$this->assertSame(
			2,
			$this->mailer->getNumberOfSentEmails()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesAppendsOrganizersFooterToMessageBodyIfSet() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com')
				)
			)
		);

		$organizerFooter = 'organizer footer';
		$secondOrganizer = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Organizer')->getLoadedTestingModel(array(
				'title' => 'Second Organizer',
				'email' => 'bar@example.org',
				'email_footer' => $organizerFooter,
			));

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
				'sender' => (string) $secondOrganizer->getUid(),
			)
		);
		$this->fixture->render();

		$this->assertContains(
			LF . '-- ' . LF . $organizerFooter,
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesForOrganizerWithoutFooterDoesNotAppendFooterMarkersToMessageBody() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('email' => 'foo@example.com')
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertNotContains(
			LF . '-- ' . LF,
			$this->mailer->getFirstSentEmail()->getBody()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesForExistingRegistrationAddsEmailSentFlashMessage() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderUid,
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'',
					array('email' => 'foo@example.com')
				)
			)
		);

		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertContains(
			$GLOBALS['LANG']->getLL('message_emailToAttendeesSent'),
			$this->getRenderedFlashMessages()
		);
	}

	/**
	 * @test
	 */
	public function sendEmailToAttendeesForNoRegistrationsNotAddsEmailSentFlashMessage() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertNotContains(
			$GLOBALS['LANG']->getLL('message_emailToAttendeesSent'),
			$this->getRenderedFlashMessages()
		);
	}


	/////////////////////////////////
	// Tests for redirectToListView
	/////////////////////////////////

	/**
	 * @test
	 */
	public function redirectToListViewSendsTheRedirectHeader() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => '1',
				'sender' => $this->organizerUid,
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertSame(
			'Location: ' . t3lib_BEfunc::getModuleUrl(
				tx_seminars_BackEnd_AbstractEventMailForm::MODULE_NAME,
				array('id' => tx_oelib_PageFinder::getInstance()->getPageUid()), FALSE, TRUE
			),
			tx_oelib_headerProxyFactory::getInstance()->getHeaderProxy()->getLastAddedHeader()
		);
	}


	/////////////////////////////////////
	// Tests concerning getInitialValue
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getInitialValueForSubjectAppendsEventTitle() {
		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $this->eventUid, array('title' => 'FooBar')
		);

		$fixture = new tx_seminars_tests_fixtures_BackEnd_TestingEventMailForm(
			$this->eventUid
		);

		$this->assertContains(
			'FooBar',
			$fixture->getInitialValue('subject')
		);
	}

	/**
	 * @test
	 */
	public function getInitialValueForSubjectAppendsEventDate() {
		$beginDate = strftime('%d.%m.%Y', $GLOBALS['SIM_EXEC_TIME'] + 42);

		$this->assertContains(
			$beginDate,
			$this->fixture->getInitialValue('subject')
		);
	}

	/**
	 * @test
	 */
	public function getInitialValueForFooThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
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