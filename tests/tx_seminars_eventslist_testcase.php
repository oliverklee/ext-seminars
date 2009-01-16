<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the events list class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_eventslist_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_mod2_eventslist
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer PID of a dummy system folder
	 */
	private $dummySysFolderPid = 0;

	/**
	 * @var tx_seminars_mod2_BackEndModule a dummy BE module
	 */
	private $backEndModule;

	/**
	* @var string the original language of the back-end module
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

		$this->backEndModule = new tx_seminars_mod2_BackEndModule();
		$this->backEndModule->id = $this->dummySysFolderPid;
		$this->backEndModule->setPageData(array('uid' => $this->dummySysFolderPid));

		$this->backEndModule->doc = t3lib_div::makeInstance('bigDoc');
		$this->backEndModule->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->backEndModule->doc->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_mod2_eventslist($this->backEndModule);
	}

	public function tearDown() {
		// Resets the language of the interface to the value it had before
		// we set it to "default" for testing.
		$GLOBALS['LANG']->lang = $this->originalLanguage;
		unset($this->originalLanguage);

		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		$this->backEndModule->__destruct();
		unset(
			$this->backEndModule, $this->fixture, $this->testingFramework
		);
	}


	/////////////////////////////////////////
	// Tests for the events list functions.
	/////////////////////////////////////////

	public function testShowContainsNoBodyHeaderWithEmptySystemFolder() {
		$this->assertNotContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsTableBodyHeaderForOneEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid)
		);

		$this->assertContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsNoBodyHeaderIfEventIsOnOtherPage() {
		// Puts this record on a non-existing page. This is intentional.
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertNotContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1'
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForTwoEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_2'
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
		$this->assertContains(
			'event_2',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneHiddenEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'hidden' => 1
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneTimedEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'endtime' => mktime() - 1000
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowForOneEventContainsAccreditationNumber() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => 'accreditation number 123',
			)
		);

		$this->assertContains(
			'accreditation number 123',
			$this->fixture->show()
		);
	}

	public function testShowForOneEventContainsHtmlSpecialCharedAccreditationNumber() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => '&"<>',
			)
		);

		$this->assertContains(
			'&amp;&quot;&lt;&gt;',
			$this->fixture->show()
		);
	}

	public function testShowContainsCanceledStatusIconForCanceledEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
			)
		);

		$this->assertContains(
			'<img src="icon_canceled.png" title="canceled" alt="canceled" />',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmedStatusIconForConfirmedEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			)
		);

		$this->assertContains(
			'<img src="icon_confirmed.png" title="confirmed" alt="confirmed" />',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainCanceledORConfirmedStatusIconForPlannedEvent() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
			)
		);

		$this->assertNotContains(
			'<img src="icon_canceled.png" title="canceled" alt="canceled" />',
			$this->fixture->show()
		);

		$this->assertNotContains(
			'<img src="icon_confirmed.png" title="confirmed" alt="confirmed" />',
			$this->fixture->show()
		);
	}



	public function testShowDoesNotContainConfirmButtonForEventThatIsAlreadyConfirmed() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertNotContains(
			'<input type="submit" value="Confirm" />',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainConfirmButtonForPlannedEventThatHasAlreadyBegun() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 42,
			)
		);

		$this->assertNotContains(
			'<input type="submit" value="Confirm" />',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmButtonForPlannedEventThatHasNotStartedYet() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<input type="submit" value="Confirm" />',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmButtonForCanceledEventThatHasNotStartedYet() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<input type="submit" value="Confirm" />',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainConfirmButtonForTopicRecords() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
			)
		);

		$this->assertNotContains(
			'<input type="submit" value="Confirm" />',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmButtonWithVariableEventUidInHiddenField() {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<input type="hidden" name="eventUid" value="' . $uid . '" />',
			$this->fixture->show()
		);
	}


	/////////////////////////
	// Tests for the icons.
	/////////////////////////

	public function testHasEventIcon() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE
			)
		);

		$this->assertContains(
			'icon_tx_seminars_seminars_',
			$this->fixture->show()
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