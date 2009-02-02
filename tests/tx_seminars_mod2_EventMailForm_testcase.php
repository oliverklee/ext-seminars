<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Mario Rimann (mario@screenteam.com)
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
class tx_seminars_mod2_EventMailForm_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_mod2_EventMailForm
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
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/mod2/locallang.xml');

		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid
			= $this->testingFramework->createSystemFolder();

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
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$this->eventUid,
			$this->organizerUid,
			'organizers'
		);

		$this->fixture = new tx_seminars_tests_fixtures_TestingEventMailForm($this->eventUid);
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

		// Finds out the record with the highest UID in the table.
		// TODO: See bug 2311 which will bring us a shiny getMaximumUid() method.
		// https://bugs.oliverklee.com/show_bug.cgi?id=2311
		$dbRow = tx_oelib_db::selectSingle(
			'uid',
			SEMINARS_TABLE_SEMINARS,
			'1=1',
			'',
			'uid DESC',
			1
		);

		new tx_seminars_tests_fixtures_TestingEventMailForm(
			$dbRow['uid'] + 1
		);
	}


	//////////////////////////////////////////////
	// Tests regarding the rendering of the form
	//////////////////////////////////////////////

	public function testRenderContainsOrganizerNameAsSenderForEventWithOneOrganizer() {
		$this->assertContains(
			'<input type="hidden" id="sender" name="sender" value="' .
				htmlspecialchars('"Dummy Organizer" <foo@example.org>') . '" />',
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
			'<option value="' . htmlspecialchars('"Dummy Organizer" <foo@example.org>') . '">',
			$formOutput
		);
		$this->assertContains(
			'<option value="' . htmlspecialchars('"Second Organizer" <bar@example.org>') . '">',
			$formOutput
		);
	}

	public function testRenderSanitizesPostDataWhenPreFillingAFormField() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => 'true',
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
				$GLOBALS['LANG']->getLL('eventMailForm_cancelButton') .
				'" class="cancelButton" onclick="window.location=window.location" />',
			$this->fixture->render()
		);
	}

	public function testRenderContainsErrorMessageIfFormWasSubmittedWithEmptySubjectField() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => 'true',
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
				'isSubmitted' => 'true',
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
				'isSubmitted' => 'true',
				'subject' => 'foo bar',
			)
		);
		$this->fixture->markAsIncomplete();

		$this->assertContains(
			'foo bar',
			$this->fixture->render()
		);
	}

	public function testRenderContainsMessageFieldPrefilledByUserInputIfFormIsReRendered() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => 'true',
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
}
?>