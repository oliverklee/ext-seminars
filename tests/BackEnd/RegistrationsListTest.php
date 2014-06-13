<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2013 Niels Pardon (mail@niels-pardon.de)
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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEnd_RegistrationsListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEnd_RegistrationsList
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
	 * @var tx_seminars_BackEnd_Module a dummy back-end module
	 */
	private $backEndModule;

	/**
	 * @var string a backup of the current BE user's language
	 */
	private $backEndLanguageBackup;

	public function setUp() {
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid = $this->testingFramework->createSystemFolder();
		$this->backEndLanguageBackup = $GLOBALS['LANG']->lang;
		$GLOBALS['LANG']->lang = 'default';

		// Loads the locallang file for properly working localization in the tests.
		$GLOBALS['LANG']->includeLLFile('EXT:seminars/BackEnd/locallang.xml');

		$this->backEndModule = new tx_seminars_BackEnd_Module();
		$this->backEndModule->id = $this->dummySysFolderPid;
		$this->backEndModule->setPageData(array(
			'uid' => $this->dummySysFolderPid,
			'doktype' => tx_seminars_BackEnd_AbstractList::SYSFOLDER_TYPE,
		));

		$this->backEndModule->doc = t3lib_div::makeInstance('bigDoc');
		$this->backEndModule->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->backEndModule->doc->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_BackEnd_RegistrationsList(
			$this->backEndModule
		);
	}

	public function tearDown() {
		$GLOBALS['LANG']->lang = $this->backEndLanguageBackup;

		$this->testingFramework->cleanUp();
		tx_seminars_registration::purgeCachedSeminars();

		unset(
			$this->backEndModule, $this->fixture, $this->testingFramework,
			$_GET['eventUid']
		);
	}


	////////////////////////////////////////////////
	// Tests for the registrations list functions.
	////////////////////////////////////////////////

	public function testShowForOneEventContainsAccreditationNumber() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => 'accreditation number 123',
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertContains(
			'accreditation number 123',
			$this->fixture->show()
		);
	}

	public function testShowForOneEventContainsHtmlSpecialCharedAccreditationNumber() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => '&"<>',
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertContains(
			'&amp;&quot;&lt;&gt;',
			$this->fixture->show()
		);
	}

	public function testShowShowsUserName() {
		$userUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'foo_user')
		);
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
				'user' => $userUid,
			)
		);

		$this->assertContains(
			'foo_user',
			$this->fixture->show()
		);
	}

	public function testShowWithRegistrationForDeletedUserDoesNotShowUserName() {
		$userUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'foo_user', 'deleted' => 1)
		);
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
				'user' => $userUid,
			)
		);

		$this->assertNotContains(
			'foo_user',
			$this->fixture->show()
		);
	}

	public function testShowWithRegistrationForInexistentUserDoesNotShowUserName() {
		$userUid = $this->testingFramework->getAutoIncrement('fe_users');
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('pid' => $this->dummySysFolderPid)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
				'user' => $userUid,
			)
		);

		$this->assertNotContains(
			'foo_user',
			$this->fixture->show()
		);
	}

	public function testShowForOneEventContainsEventTitle() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowForOneDeletedEventDoesNotContainEventTitle() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'deleted' => 1,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertNotContains(
			'event_1',
			$this->fixture->show()
		);
	}

	public function testShowForOneInexistentEventShowsUserName() {
		$userUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'user_foo')
		);
		$seminarUid = $this->testingFramework->getAutoIncrement(
			'tx_seminars_seminars'
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
				'user' => $userUid,
			)
		);

		$this->assertContains(
			'user_foo',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showContainsRegistrationFromSubfolder() {
		$subfolderPid = $this->testingFramework->createSystemFolder(
			$this->dummySysFolderPid
		);
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event for registration in subfolder',
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $subfolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertContains(
			'event for registration in subfolder',
			$this->fixture->show()
		);
	}

	public function testShowForNonEmptyRegularRegistrationsListContainsCsvExportButton() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'pid' => $this->dummySysFolderPid,
				'seminar' => $seminarUid,
			)
		);

		$this->assertContains(
			'mod.php?M=web_txseminarsM2&amp;csv=1',
			$this->fixture->show()
		);
	}

	public function testShowForEmptyRegularRegistrationsListContainsCsvExportButton() {
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
			)
		);

		$this->assertNotContains(
			'mod.php?M=web_txseminarsM2&amp;csv=1',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventUidSetShowsTitleOfThisEvent() {
		$_GET['eventUid'] = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
			)
		);

		$this->assertContains(
			'event_1',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventUidSetShowsUidOfThisEvent() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
			)
		);
		$_GET['eventUid'] = $eventUid;

		$this->assertContains(
			'(UID ' . $eventUid . ')',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventUidSetShowsRegistrationOfThisEvent() {
		$userUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'user_foo')
		);
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $userUid,
			)
		);

		$_GET['eventUid'] = $eventUid;

		$this->assertContains(
			'user_foo',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventUidSetDoesNotShowRegistrationOfAnotherEvent() {
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $this->testingFramework->createFrontEndUser(
					'', array('name' => 'user_foo')
				),
			)
		);

		$_GET['eventUid'] = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->assertNotContains(
			'user_foo',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventUidAddsEventUidToCsvExportIcon() {
		$userUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'user_foo')
		);
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $userUid,
			)
		);

		$_GET['eventUid'] = $eventUid;

		$this->assertContains(
			'tx_seminars_pi2[eventUid]=' . $eventUid,
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForEventUidDoesNotAddPidToCsvExportIcon() {
		$userUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'user_foo')
		);
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $userUid,
			)
		);

		$_GET['eventUid'] = $eventUid;

		$this->assertNotContains(
			'tx_seminars_pi2[pid]=',
			$this->fixture->show()
		);
	}

	/**
	 * @test
	 */
	public function showForNoEventUidDoesNotAddEventUidToCsvExportIcon() {
		$userUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'user_foo')
		);
		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $userUid,
			)
		);

		$this->assertNotContains(
			'tx_seminars_pi2[eventUid]=',
			$this->fixture->show()
		);
	}


	//////////////////////////////////////
	// Tests concerning the "new" button
	//////////////////////////////////////

	public function testNewButtonForRegistrationStorageSettingSetInUsersGroupSetsThisPidAsNewRecordPid() {
		$newRegistrationFolder = $this->dummySysFolderPid + 1;
		$backEndGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUserGroup')->getLoadedTestingModel(
			array('tx_seminars_registrations_folder' => $newRegistrationFolder)
		);
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_BackEndUser')->getLoadedTestingModel(
				array('usergroup' => $backEndGroup->getUid())
		);
		tx_oelib_BackEndLoginManager::getInstance()->setLoggedInUser(
			$backEndUser
		);

		$this->assertContains(
			'edit[tx_seminars_attendances][' . $newRegistrationFolder . ']=new',
			$this->fixture->show()
		);
	}
}