<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the registrations list class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEnd_RegistrationsList_testcase extends tx_phpunit_testcase {
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

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->dummySysFolderPid = $this->testingFramework->createSystemFolder();

		$this->backEndModule = new tx_seminars_BackEnd_Module();
		$this->backEndModule->id = $this->dummySysFolderPid;
		$this->backEndModule->setPageData(array('uid' => $this->dummySysFolderPid));

		$this->backEndModule->doc = t3lib_div::makeInstance('bigDoc');
		$this->backEndModule->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->backEndModule->doc->docType = 'xhtml_strict';

		$this->fixture = new tx_seminars_BackEnd_RegistrationsList(
			$this->backEndModule
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registration::purgeCachedSeminars();
		$this->fixture->__destruct();
		$this->backEndModule->__destruct();
		unset(
			$this->backEndModule, $this->fixture, $this->testingFramework
		);
	}


	////////////////////////////////////////////////
	// Tests for the registrations list functions.
	////////////////////////////////////////////////

	public function testShowForOneEventContainsAccreditationNumber() {
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => 'accreditation number 123',
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'accreditation_number' => '&"<>',
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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
			SEMINARS_TABLE_SEMINARS,
			array('pid' => $this->dummySysFolderPid)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->dummySysFolderPid,
				'title' => 'event_1',
				'deleted' => 1,
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
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
}
?>