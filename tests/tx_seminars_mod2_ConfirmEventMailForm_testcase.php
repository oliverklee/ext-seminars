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
 * Testcase for the 'ConfirmEventMailForm' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Mario Rimann <mario@screenteam.com>
 */
class tx_seminars_mod2_ConfirmEventMailForm_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_mod2_ConfirmEventMailForm
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var string the original language of the BE user
	 */
	private $originalLanguage;

	/**
	 * @var integer PID of a dummy system folder
	 */
	private $dummySysFolderPid;

	/**
	 * @var integer PID of a dummy organizer record
	 */
	private $organizerUid;

	/**
	 * @var integer UID of a dummy event record
	 */
	private $eventUid;

	public function setUp() {
		// Set's the localization to the default language so that all tests can
		// run, even if the BE user has it's interface set to another language.
		$this->originalLanguage = $GLOBALS['LANG']->lang;
		$GLOBALS['LANG']->lang = 'default';

		// Loads the locallang file for properly working localization in the tests.
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/mod2/locallang.xml');

		tx_oelib_headerProxyFactory::getInstance()->enableTestMode();
		tx_oelib_mailerFactory::getInstance()->enableTestMode();

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
				'pid' => $this->dummySysFolderPid,
				'title' => 'Dummy event',
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 86400,
				'organizers' => 0,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$this->eventUid,
			$this->organizerUid,
			'organizers'
		);

		$this->fixture = new tx_seminars_mod2_ConfirmEventMailForm($this->eventUid);
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

	public function testRenderContainsEventTitleInSubjectFieldForNewForm() {
		$this->assertContains(
			'Dummy event',
			$this->fixture->render()
		);
	}

	public function testRenderContainsPrefilledBodyField() {
		$this->assertContains(
			$GLOBALS['LANG']->getLL('confirmMailForm_prefillField_messageBody'),
			$this->fixture->render()
		);
	}

	public function testRenderContainsSubmitButton() {
		$this->assertContains(
			'<input type="submit" value="' .
				$GLOBALS['LANG']->getLL('confirmMailForm_sendButton') .
				'" class="confirmButton" />',
			$this->fixture->render()
		);
	}

	public function testRenderDoesNotPrefillSubjectFieldIfEmptyStringWasSentViaPost() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => 'true',
				'subject' => '',
			)
		);

		$this->assertNotContains(
			'Dummy event',
			$this->fixture->render()
		);
	}

	public function testRenderContainsTheConfirmEventActionForThisForm() {
		$this->assertContains(
			'<input type="hidden" name="action" value="confirmEvent" />',
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

	public function testSetEventStateSetsStatusToConfirmed() {
		$this->fixture->setPostData(
			array(
				'action' => 'confirmEvent',
				'isSubmitted' => 'true',
				'subject' => 'foo',
				'messageBody' => 'foo bar',
			)
		);
		$this->fixture->render();

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				SEMINARS_TABLE_SEMINARS,
				'uid = ' . $this->eventUid . ' AND cancelled = ' .
					tx_seminars_seminar::STATUS_CONFIRMED
			)
		);
	}
}
?>