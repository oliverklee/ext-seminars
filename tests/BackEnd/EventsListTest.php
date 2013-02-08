<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_BackEnd_EventsListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_EventsList
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
	 * @var tx_seminars_BackEnd_Module a dummy BE module
	 */
	private $backEndModule;

	/**
	* @var string the original language of the back-end module
	*/
	private $originalLanguage;

	public function setUp() {
		// Sets the localization to the default language so that all tests can
		// run even if the BE user has its interface set to another language.
		$this->originalLanguage = $GLOBALS['LANG']->lang;
		$GLOBALS['LANG']->lang = 'default';

		// Loads the locallang file for properly working localization in the tests.
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/BackEnd/locallang.xml');

		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid
			= $this->testingFramework->createSystemFolder();

		$this->backEndModule = new tx_seminars_BackEnd_Module();
		$this->backEndModule->id = $this->dummySysFolderPid;
		$this->backEndModule->setPageData(array(
			'uid' => $this->dummySysFolderPid,
			'doktype' => tx_seminars_BackEnd_AbstractList::SYSFOLDER_TYPE,
		));

		$this->backEndModule->doc = t3lib_div::makeInstance('bigDoc');
		$this->backEndModule->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->backEndModule->doc->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_BackEnd_EventsList(
			$this->backEndModule
		);

		$backEndGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUserGroup')->getLoadedTestingModel(
			array('tx_seminars_events_folder' => $this->dummySysFolderPid + 1)
		);
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUser')->getLoadedTestingModel(
			array('usergroup' => $backEndGroup->getUid())
		);
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
			$backEndUser
		);
	}

	public function tearDown() {
		// Resets the language of the interface to the value it had before
		// we set it to "default" for testing.
		$GLOBALS['LANG']->lang = $this->originalLanguage;

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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid + 1)
		);

		$this->assertNotContains(
			'<td class="datecol">',
			$this->fixture->show()
		);
	}

	public function testShowContainsEventTitleForOneEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1'
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowForOneEventContainsAccreditationNumber() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
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
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
			)
		);

		$this->assertContains(
			'<img src="../Resources/Public/Icons/Canceled.png" title="canceled" alt="canceled" />',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmedStatusIconForConfirmedEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			)
		);

		$this->assertContains(
			'<img src="../Resources/Public/Icons/Confirmed.png" title="confirmed" alt="confirmed" />',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainCanceledOrConfirmedStatusIconForPlannedEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
			)
		);

		$this->assertNotContains(
			'<img src="../Resources/Public/Icons/Canceled.png" title="canceled" alt="canceled" />',
			$this->fixture->show()
		);

		$this->assertNotContains(
			'<img src="../Resources/Public/Icons/Confirmed.png" title="confirmed" alt="confirmed" />',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventWithRegistrationsContainsEmailButton() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'registrations' => 1,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $eventUid,
			)
		);

		$this->assertContains(
			'<button><p>E-mail</p></button>',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventWithoutRegistrationsNotContainsEmailButton() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'registrations' => 0,
			)
		);

		$this->assertNotContains(
			'<button><p>E-mail</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainConfirmButtonForEventThatIsAlreadyConfirmed() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertNotContains(
			'<button><p>Confirm</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainConfirmButtonForPlannedEventThatHasAlreadyBegun() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 42,
			)
		);

		$this->assertNotContains(
			'<button><p>Confirm</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmButtonForPlannedEventThatHasNotStartedYet() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_PLANNED,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<button><p>Confirm</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmButtonForCanceledEventThatHasNotStartedYet() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<button><p>Confirm</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainConfirmButtonForTopicRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);

		$this->assertNotContains(
			'<button><p>Confirm</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowContainsConfirmButtonWithVariableEventUidInHiddenField() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<button><p>Confirm</p></button>' .
			'<input type="hidden" name="eventUid" value="' . $uid . '" />',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showDoesNotContainConfirmButtonForHiddenEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'hidden' => 1,
			)
		);

		$this->assertNotContains(
			'<button><p>Confirm</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainCancelButtonForAlreadyCanceledEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'cancelled' => tx_seminars_seminar::STATUS_CANCELED,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertNotContains(
			'<button><p>Cancel</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainCancelButtonPlannedEventThatHasAlreadyBegun() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 42,
			)
		);

		$this->assertNotContains(
			'<button><p>Cancel</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowContainsCancelButtonForPlannedEventThatHasNotStartedYet() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<button><p>Cancel</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowContainsCancelButtonForConfirmedEventThatHasNotStartedYet() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
				'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED,
			)
		);

		$this->assertContains(
			'<button><p>Cancel</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowDoesNotContainCancelButtonForTopicRecords() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);

		$this->assertNotContains(
			'<button><p>Cancel</p></button>',
			$this->fixture->show()
		);
	}

	public function testShowContainsCancelButtonWithVariableEventUidInHiddenField() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 42,
			)
		);

		$this->assertContains(
			'<button><p>Cancel</p></button>' .
			'<input type="hidden" name="eventUid" value="' . $uid . '" />',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showDoesNotContainCancelButtonForHiddenEvent() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'hidden' => 1,
			)
		);

		$this->assertNotContains(
			'<button><p>Cancel</p></button>',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showContainsCsvExportButtonForEventWithRegistration() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'needs_registration' => 1,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $eventUid,
			)
		);

		$this->assertContains(
			'<a href="CSV.php?id=' .
				$this->dummySysFolderPid .
				'&amp;tx_seminars_pi2[table]=tx_seminars_attendances' .
				'&amp;tx_seminars_pi2[eventUid]=' . $eventUid . '">',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showDoesNotContainCsvExportButtonForHiddenEventWithRegistration() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'hidden' => 1,
				'needs_registration' => 1,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $eventUid,
			)
		);

		$this->assertNotContains(
			'<a href="CSV.php?id=' .
				$this->dummySysFolderPid .
				'&amp;tx_seminars_pi2[table]=tx_seminars_attendances' .
				'&amp;tx_seminars_pi2[eventUid]=' . $eventUid . '">',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showContainsEventFromSubfolder() {
		$subfolderPid = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'Event in subfolder',
				'pid' => $subfolderPid,
			)
		);

		$this->assertContains(
			'Event in subfolder',
			$this->fixture->show()
		);
	}

	public function testShowForEventWithRegistrationHasShowLink() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid, 'needs_registration' => 1)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('pid' => $this->dummySysFolderPid, 'seminar' => $eventUid)
		);

		$this->assertContains(
			$GLOBALS['LANG']->getLL('label_show_event_registrations'),
			$this->fixture->show()
		);
	}

	public function testShowForEventWithoutRegistrationDoesNotHaveShowLink() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid, 'needs_registration' => 1)
		);

		$this->assertNotContains(
			$GLOBALS['LANG']->getLL('label_show_event_registrations'),
			$this->fixture->show()
		);
	}

	public function testShowLinkLinksToRegistrationsTab() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid, 'needs_registration' => 1)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('pid' => $this->dummySysFolderPid, 'seminar' => $eventUid)
		);

		$this->assertContains(
			'&amp;subModule=2',
			$this->fixture->show()
		);
	}

	public function testShowLinkLinksToTheEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid, 'needs_registration' => 1)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('pid' => $this->dummySysFolderPid, 'seminar' => $eventUid)
		);

		$this->assertContains(
			'&amp;eventUid=' . $eventUid,
			$this->fixture->show()
		);
	}

	public function testShowForHiddenEventWithRegistrationDoesNotHaveShowLink() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'needs_registration' => 1,
				'hidden' => 1,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array('pid' => $this->dummySysFolderPid, 'seminar' => $eventUid)
		);

		$this->assertNotContains(
			$GLOBALS['LANG']->getLL('label_show_event_registrations'),
			$this->fixture->show()
		);
	}


	/////////////////////////
	// Tests for the icons.
	/////////////////////////

	public function testHasEventIcon() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE
			)
		);

		$this->assertContains(
			'EventComplete.gif',
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


	///////////////////////////////////////////
	// Tests concerning the new record button
	///////////////////////////////////////////

	public function testEventListCanContainNewButton() {
		$this->assertContains(
			'newRecordLink',
			$this->fixture->show()
		);
	}

	public function testNewButtonForNoEventStorageSettingInUserGroupsSetsCurrentPageIdAsNewRecordPid() {
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUser')->getLoadedTestingModel(array());
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
			$backEndUser
		);

		$this->assertContains(
			'edit[tx_seminars_seminars][' . $this->dummySysFolderPid . ']=new',
			$this->fixture->show()
		);
	}

	public function testNewButtonForEventStoredOnCurrentPageHasCurrentFolderLabel() {
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUser')->getLoadedTestingModel(array());
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
			$backEndUser
		);

		$this->assertContains(
			sprintf(
				$GLOBALS['LANG']->getLL('label_create_record_in_current_folder'),
				'',
				$this->dummySysFolderPid
			),
			$this->fixture->show()
		);
	}

	public function testNewButtonForEventStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid() {
		$newEventFolder = tx_oelib_BackEndLoginManager::getInstance()->
			getLoggedInUser('tx_seminars_Mapper_BackEndUser')
				->getEventFolderFromGroup();

		$this->assertContains(
			'edit[tx_seminars_seminars][' . $newEventFolder . ']=new',
			$this->fixture->show()
		);
	}

	public function testNewButtonForEventStoredInPageDetermindedByGroupHasForeignFolderLabel() {
		$newEventFolder = tx_oelib_BackEndLoginManager::getInstance()->
			getLoggedInUser('tx_seminars_Mapper_BackEndUser')
				->getEventFolderFromGroup();

		$this->assertContains(
			sprintf(
				$GLOBALS['LANG']->getLL('label_create_record_in_foreign_folder'),
				'',
				$newEventFolder
			),
			$this->fixture->show()
		);
	}
}
?>