<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the eventEditor class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi1_eventEditor_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_pi1_eventEditor
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->fixture = new tx_seminars_pi1_eventEditor(
			array(
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
				'form.' => array('eventEditor.' => array()),
			),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();

		unset($this->testingFramework, $this->fixture);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Creates a FE user, adds him/her as a VIP to the seminar with the UID in
	 * $this->seminarUid and logs him/her in.
	 */
	private function createLogInAndAddFeUserAsVip() {
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('vips' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_MANAGERS_MM,
			$seminarUid, $this->testingFramework->createAndLoginFrontEndUser()
		);
		$this->fixture->setObjectUid($seminarUid);
	}

	/**
	 * Creates a FE user, adds his/her FE user group as a default VIP group via
	 * TS setup and logs him/her in.
	 */
	private function createLogInAndAddFeUserAsDefaultVip() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->fixture->setConfigurationValue(
			'defaultEventVipsFeGroupID', $feUserGroupUid
		);
		$this->testingFramework->createAndLoginFrontEndUser($feUserGroupUid);
	}

	/**
	 * Creates a FE user, adds him/her as a owner to the seminar with the UID in
	 * $this->seminarUid and logs him/her in.
	 */
	private function createLogInAndAddFeUserAsOwner() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('owner_feuser' => $this->testingFramework->createAndLoginFrontEndUser())
		));
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testCreateLogInAndAddFeUserAsVipCreatesFeUser() {
		$this->createLogInAndAddFeUserAsVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords('fe_users')
		);
	}

	public function testCreateLogInAndAddFeUserAsVipLogsInFeUser() {
		$this->createLogInAndAddFeUserAsVip();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testCreateLogInAndAddFeUserAsVipAddsUserAsVip() {
		$this->createLogInAndAddFeUserAsVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS,
				'uid=' . $this->fixture->getObjectUid() . ' AND vips=1'
			)
		);
	}

	public function testCreateLogInAndAddFeUserAsOwnerCreatesFeUser() {
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords('fe_users')
		);
	}

	public function testCreateLogInAndAddFeUserAsOwnerLogsInFeUser() {
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testCreateLogInAndAddFeUserAsOwnerAddsUserAsOwner() {
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS,
				'uid=' . $this->fixture->getObjectUid() . ' AND owner_feuser>0'
			)
		);
	}

	public function testCreateLogInAndAddFeUserAsDefaultVipCreatesFeUser() {
		$this->createLogInAndAddFeUserAsDefaultVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords('fe_users')
		);
	}

	public function testCreateLogInAndAddFeUserAsDefaultVipLogsInFeUser() {
		$this->createLogInAndAddFeUserAsDefaultVip();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testCreateLogInAndAddFeUserAsDefaultVipAddsFeUserAsDefaultVip() {
		$this->createLogInAndAddFeUserAsDefaultVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'fe_users',
				'uid=' . $this->fixture->getFeUserUid() .
					' AND usergroup=' . $this->fixture->getConfValueInteger(
						'defaultEventVipsFeGroupID'
					)
			)
		);
	}


	///////////////////////////////////////////////////////
	// Tests for getting the event-successfully-saved URL
	///////////////////////////////////////////////////////

	public function testGetEventSuccessfullySavedUrlReturnsUrlStartingWithHttp() {
		$this->fixture->setConfigurationValue(
			'eventSuccessfullySavedPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getEventSuccessfullySavedUrl()
		);
	}

	public function testGetEventSuccessfullySavedUrlReturnsConfiguredTargetPid() {
		$frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->fixture->setConfigurationValue(
			'eventSuccessfullySavedPID', $frontEndPageUid
		);

		$this->assertContains(
			'?id=' . $frontEndPageUid,
			$this->fixture->getEventSuccessfullySavedUrl()
		);
	}

	public function testGetEventSuccessfullySavedUrlNotReturnsSeminarToEditAsLinkParameter() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));
		$this->fixture->setConfigurationValue(
			'eventSuccessfullySavedPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertNotContains(
			'tx_seminars_pi1[seminar]=' . $this->fixture->getObjectUid(),
			$this->fixture->getEventSuccessfullySavedUrl()
		);
	}

	public function testGetEventSuccessfullySavedUrlReturnsCurrentPidAsTargetPidForProceedUpload() {
		$this->fixture->setFakedFormValue('proceed_file_upload', 1);

		$this->assertContains(
			'?id=' . $GLOBALS['TSFE']->id,
			$this->fixture->getEventSuccessfullySavedUrl()
		);
	}

	public function testGetEventSuccessfullySavedUrlReturnsSeminarToEditAsLinkParameterForProceedUpload() {
		$this->fixture->setFakedFormValue('proceed_file_upload', 1);

		$this->assertContains(
			'tx_seminars_pi1[seminar]=' . $this->fixture->getObjectUid(),
			$this->fixture->getEventSuccessfullySavedUrl()
		);
	}


	////////////////////////////////////////
	// Tests concerning hasAccessMessage()
	////////////////////////////////////////

	public function testHasAccessMessageWithNoLoggedInFeUserReturnsNotLoggedInMessage() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));

		$this->assertContains(
			$this->fixture->translate('message_notLoggedIn'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageWithLoggedInFeUserWhoIsNeitherVipNorOwnerReturnsNoAccessMessage() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertContains(
			$this->fixture->translate('message_noAccessToEventEditor'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageWithLoggedInFeUserAsOwnerReturnsEmptyResult() {
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertTrue(
			$this->fixture->hasAccessMessage() == ''
		);
	}

	public function testHasAccessMessageWithLoggedInFeUserAsVipAndVipsMayNotEditTheirEventsReturnsNonEmptyResult() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents' , 0);
		$this->createLogInAndAddFeUserAsVip();

		$this->assertContains(
			$this->fixture->translate('message_noAccessToEventEditor'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageWithLoggedInFeUserAsVipAndVipsMayEditTheirEventsReturnsEmptyResult() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents' , 1);
		$this->createLogInAndAddFeUserAsVip();

		$this->assertTrue(
			$this->fixture->hasAccessMessage() == ''
		);
	}

	public function testHasAccessWithLoggedInFeUserAsDefaultVipAndVipsMayNotEditTheirEventsReturnsNonEmptyResult() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents' , 0);
		$this->createLogInAndAddFeUserAsDefaultVip();

		$this->assertContains(
			$this->fixture->translate('message_noAccessToEventEditor'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessWithLoggedInFeUserAsDefaultVipAndVipsMayEditTheirEventsReturnsEmptyResult() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents' , 1);
		$this->createLogInAndAddFeUserAsDefaultVip();

		$this->assertTrue(
			$this->fixture->hasAccessMessage() == ''
		);
	}

	public function testHasAccessForLoggedInUserInUnauthorizedUsergroupReturnsNonEmptyResult() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertContains(
			$this->fixture->translate('message_noAccessToEventEditor'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessForLoggedInUserInAuthorizedUsergroupAndNoUidSetReturnsEmptyResult() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->fixture->setConfigurationValue('eventEditorFeGroupID', $groupUid);

		$this->assertTrue(
			$this->fixture->hasAccessMessage() == ''
		);
	}

	public function testHasAccessForLoggedInNonOwnerInAuthorizedUsergroupReturnsNoAccessMessage() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->fixture->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		));

		$this->assertContains(
			$this->fixture->translate('message_noAccessToEventEditor'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessForLoggedInOwnerInAuthorizedUsergroupReturnsEmptyResult() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$userUid = $this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->fixture->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('owner_feuser' => $userUid)
		));

		$this->assertTrue(
			$this->fixture->hasAccessMessage() == ''
		);
	}

	public function testHasAccessForLoggedInUserAndInvalidSeminarUidReturnsWrongSeminarMessage() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->fixture->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->fixture->setObjectUid($this->testingFramework->getAutoIncrement(
			SEMINARS_TABLE_SEMINARS
		));

		$this->assertContains(
			$this->fixture->translate('message_wrongSeminarNumber'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageForDeletedSeminarUidAndUserLoggedInReturnsWrongSeminaMessage() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('deleted' => 1)
		));

		$this->assertContains(
			$this->fixture->translate('message_wrongSeminarNumber'),
			$this->fixture->hasAccessMessage()
		);
	}


	////////////////////////////////////////////
	// Tests concerning populateListCategories
	////////////////////////////////////////////

	public function testPopulateListCategoriesDoesNotCrash() {
		$this->fixture->populateListCategories(array());
	}
}
?>