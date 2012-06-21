<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2012 Niels Pardon (mail@niels-pardon.de)
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

/**
 * Testcase for the tx_seminars_FrontEnd_EventEditor class in the "seminars"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_FrontEnd_EventEditorTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_EventEditor
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();
		tx_oelib_MapperRegistry::getInstance()
			->activateTestingMode($this->testingFramework);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', FALSE
		);

		$configuration = new tx_oelib_Configuration();
		$configuration->setAsInteger('createAuxiliaryRecordsPID', 0);
		tx_oelib_ConfigurationRegistry::getInstance()
			->set('plugin.tx_seminars_pi1', $configuration);

		$this->fixture = new tx_seminars_FrontEnd_EventEditor(
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'form.' => array('eventEditor.' => array()),
			),
			$GLOBALS['TSFE']->cObj
		);
		$this->fixture->setTestMode();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();

		tx_seminars_registrationmanager::purgeInstance();
		tx_oelib_configurationProxy::purgeInstances();
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
			'tx_seminars_seminars', array('vips' => 1)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_feusers_mm',
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
			'tx_seminars_seminars',
			array('owner_feuser' => $this->testingFramework->createAndLoginFrontEndUser())
		));
	}

	/**
	 * Creates a front end user testing model which has a group with the given
	 * publish settings.
	 *
	 * @param integer $publishSetting
	 *        the publish settings for the user, must be one of the following:
	 *        tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY, tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW, or
	 *        tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
	 */
	private function createAndLoginUserWithPublishSetting($publishSetting) {
		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getLoadedTestingModel(
				array('tx_seminars_publish_events' => $publishSetting)
		);

		$user = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUser')
			->getLoadedTestingModel(
				array('usergroup' => $userGroup->getUid())
		);
		$this->testingFramework->loginFrontEndUser($user->getUid());
	}

	/**
	 * Creates a front-end user which has a group with the publish setting
	 * tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED and a reviewer.
	 */
	private function createAndLoginUserWithReviewer() {
		$backendUserUid = $this->testingFramework->createBackEndUser(
			array('email' => 'foo@bar.com', 'realName' => 'Mr. Foo'));
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup(
			array(
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
				'tx_seminars_reviewer' => $backendUserUid,
			)
		);

		$this->testingFramework->createAndLoginFrontEndUser(
			$userGroupUid, array('name' => 'Mr. Bar', 'email' => 'mail@foo.com')
		);
	}

	/**
	 * Creates a front-end user adds his/her front-end user group as event
	 * editor front-end group and logs him/her in.
	 *
	 * @param array $frontEndUserGroupData front-end user group data to set, may
	 *                                     be empty
	 */
	private function createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
		array $frontEndUserGroupData = array()
	) {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup(
			$frontEndUserGroupData
		);
		$this->fixture->setConfigurationValue(
			'eventEditorFeGroupID', $feUserGroupUid
		);
		$this->testingFramework->createAndLoginFrontEndUser($feUserGroupUid);
	}

	/**
	 * Creates a fixture with the given field as required field.
	 *
	 * @param string $requiredField
	 *        the field which should be required, may be empty
	 *
	 * @return tx_seminars_FrontEnd_EventEditor event editor fixture with the given
	 *         field as required field, will not be NULL.
	 */
	private function getFixtureWithRequiredField($requiredField) {
		$result = tx_oelib_ObjectFactory::make(
			'tx_seminars_FrontEnd_EventEditor',
			array(
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'form.' => array('eventEditor.' => array()),
				'requiredFrontEndEditorFields' => $requiredField,
			),
			$GLOBALS['TSFE']->cObj
		);
		$result->setTestMode();

		return $result;
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
				'tx_seminars_seminars',
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
				'tx_seminars_seminars',
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

	/**
	 * @test
	 */
	public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupCreatesFeUser() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupLogsInFrontEndUser() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function createLogInAndAddFrontEndUserToEventEditorFrontEndGroupAddsFrontEndUserToEventEditorFrontEndGroup() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'fe_users',
				'uid=' . $this->fixture->getFeUserUid() .
					' AND usergroup=' . $this->fixture->getConfValueInteger(
						'eventEditorFeGroupID'
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
			'tx_seminars_seminars'
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
			'tx_seminars_seminars'
		));

		$this->assertContains(
			$this->fixture->translate('message_notLoggedIn'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageWithLoggedInFeUserWhoIsNeitherVipNorOwnerReturnsNoAccessMessage() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			'tx_seminars_seminars'
		));
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertContains(
			$this->fixture->translate('message_noAccessToEventEditor'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageWithLoggedInFeUserAsOwnerReturnsEmptyResult() {
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertEquals(
			'',
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageWithLoggedInFeUserAsVipAndVipsMayNotEditTheirEventsReturnsNonEmptyResult() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars'
		));
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents' , 1);
		$this->createLogInAndAddFeUserAsVip();

		$this->assertEquals(
			'',
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessWithLoggedInFeUserAsDefaultVipAndVipsMayNotEditTheirEventsReturnsNonEmptyResult() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars'
		));
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents' , 1);
		$this->createLogInAndAddFeUserAsDefaultVip();

		$this->assertEquals(
			'',
			$this->fixture->hasAccessMessage()
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

		$this->assertEquals(
			'',
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessForLoggedInNonOwnerInAuthorizedUsergroupReturnsNoAccessMessage() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->fixture->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars', array('owner_feuser' => $userUid)
		));

		$this->assertEquals(
			'',
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessForLoggedInUserAndInvalidSeminarUidReturnsWrongSeminarMessage() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->fixture->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->fixture->setObjectUid($this->testingFramework->getAutoIncrement(
			'tx_seminars_seminars'
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
			'tx_seminars_seminars', array('deleted' => 1)
		));

		$this->assertContains(
			$this->fixture->translate('message_wrongSeminarNumber'),
			$this->fixture->hasAccessMessage()
		);
	}

	public function testHasAccessMessageForHiddenSeminarUidAndUserLoggedInReturnsEmptyString() {
		$this->fixture->setObjectUid($this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'hidden' => 1,
				'owner_feuser' => $this->testingFramework->createAndLoginFrontEndUser(),
			)
		));

		$this->assertEquals(
			'',
			$this->fixture->hasAccessMessage()
		);
	}


	////////////////////////////////////////////
	// Tests concerning populateListCategories
	////////////////////////////////////////////

	public function testPopulateListCategoriesDoesNotCrash() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->populateListCategories(array());
	}

	/**
	 * @test
	 */
	public function populateListCategoriesShowsCategory() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories'
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $categoryUid),
				$this->fixture->populateListCategories(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCategoriesForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array('pid' => 23)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $categoryUid),
				$this->fixture->populateListCategories(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCategoriesForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $categoryUid),
				$this->fixture->populateListCategories(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCategoriesForSetStoragePageAndUseStoragePidSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $categoryUid),
				$this->fixture->populateListCategories(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCategoriesForSetStoragePageAndUseStoragePidNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $categoryUid),
				$this->fixture->populateListCategories(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCategoriesForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);

		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $categoryUid),
				$this->fixture->populateListCategories(array())
			)
		);
	}


	///////////////////////////////////////////////
	// Tests concerning populateListEventTypes().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListEventTypesShowsEventType() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types'
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $eventTypeUid),
				$this->fixture->populateListEventTypes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListEventTypesForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('pid' => 87)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $eventTypeUid),
				$this->fixture->populateListEventTypes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListEventTypesForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $eventTypeUid),
				$this->fixture->populateListEventTypes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListEventTypesForSetStoragePageAndUseStoragePidSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('pid' => 23)
		);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $eventTypeUid),
				$this->fixture->populateListEventTypes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListEventTypesForSetStoragePageAndUseStoragePidNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('pid' => 23)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $eventTypeUid),
				$this->fixture->populateListEventTypes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListEventTypesForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);

		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $eventTypeUid),
				$this->fixture->populateListEventTypes(array())
			)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning populateListLodgings().
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListLodgingsShowsLodging() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings'
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $lodgingUid),
				$this->fixture->populateListLodgings(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListLodgingsForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('pid' => 11)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $lodgingUid),
				$this->fixture->populateListLodgings(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListLodgingsForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $lodgingUid),
				$this->fixture->populateListLodgings(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListLodgingsForSetStoragePageAndUseStoragePidSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $lodgingUid),
				$this->fixture->populateListLodgings(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListLodgingsForSetStoragePageAndUseStoragePidNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $lodgingUid),
				$this->fixture->populateListLodgings(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListLodgingsForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);

		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $lodgingUid),
				$this->fixture->populateListLodgings(array())
			)
		);
	}


	//////////////////////////////////////////
	// Tests concerning populateListFoods().
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListFoodsShowsFood() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods'
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $foodUid),
				$this->fixture->populateListFoods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListFoodsForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('pid' => 22)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $foodUid),
				$this->fixture->populateListFoods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListFoodsForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $foodUid),
				$this->fixture->populateListFoods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListFoodsForSetStoragePageAndUseStoragePidSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $foodUid),
				$this->fixture->populateListFoods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListFoodsForSetStoragePageAndUseStoragePidNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $foodUid),
				$this->fixture->populateListFoods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListFoodsForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);

		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $foodUid),
				$this->fixture->populateListFoods(array())
			)
		);
	}


	///////////////////////////////////////////////////
	// Tests concerning populateListPaymentMethods().
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListPaymentMethodsShowsPaymentMethod() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods'
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $paymentMethodUid),
				$this->fixture->populateListPaymentMethods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPaymentMethodsForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('pid' => 52)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $paymentMethodUid),
				$this->fixture->populateListPaymentMethods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPaymentMethodsForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $paymentMethodUid),
				$this->fixture->populateListPaymentMethods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPaymentMethodsForSetStoragePageAndUseStoragePidSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $paymentMethodUid),
				$this->fixture->populateListPaymentMethods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPaymentMethodsForSetStoragePageAndUseStoragePidNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $paymentMethodUid),
				$this->fixture->populateListPaymentMethods(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPaymentMethodsForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);

		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $paymentMethodUid),
				$this->fixture->populateListPaymentMethods(array())
			)
		);
	}


	///////////////////////////////////////////////
	// Tests concerning populateListOrganizers().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListOrganizersShowsOrganizerFromDatabase() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('pid' => 12)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersForSetStoragePageAndUseStoragePageSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('pid' => 12)
		);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersForSetStoragePageAndUseStoragePageNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('pid' => 12)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);

		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersForSetStoragePageAndAuxiliaryRecordsConfigurationPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();
		tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')->set(
			'createAuxiliaryRecordsPID', 21
		);

		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersShowsDefaultOrganizerFromUserGroup() {
		$organizerUid = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Organizer')
			->getLoadedTestingModel(array())->getUid();
		$frontEndUserGroupUid = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_FrontEndUserGroup')->getLoadedTestingModel(
				array('tx_seminars_default_organizer' => $organizerUid)
			)->getUid();

		$this->testingFramework
			->createAndLoginFrontEndUser($frontEndUserGroupUid);

		$this->assertTrue(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListOrganizersForDefaultOrganizerInUserGroupNotIncludesOtherOrganizer() {
		$organizerMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Organizer'
		);
		$organizerUidFromDatabase = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);
		// makes sure the mapper knows that UID
		$organizerMapper->find($organizerUidFromDatabase);

		$organizerUid = $organizerMapper->getLoadedTestingModel(array())->getUid();
		$frontEndUserGroupUid = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_FrontEndUserGroup')->getLoadedTestingModel(
				array('tx_seminars_default_organizer' => $organizerUid)
			)->getUid();
		$this->testingFramework
			->createAndLoginFrontEndUser($frontEndUserGroupUid);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $organizerUidFromDatabase),
				$this->fixture->populateListOrganizers(array())
			)
		);
	}


	///////////////////////////////////////////
	// Tests concerning populateListPlaces().
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListPlacesShowsPlaceWithoutOwner() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites'
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPlacesShowsPlaceWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('owner' => $frontEndUserUid)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPlacesHidesPlaceWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPlacesForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('pid' => 55)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPlacesForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPlacesForSetStoragePageAndUseStoragePidSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPlacesForSetStoragePageAndUseStoragePidNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListPlacesForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);

		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $placeUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListPlaces(array())
			)
		);
	}


	///////////////////////////////////////////////
	// Tests concerning populateListCheckboxes().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListCheckboxesShowsCheckboxWithoutOwner() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes'
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCheckboxesShowsCheckboxWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes', array('owner' => $frontEndUserUid)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCheckboxesHidesCheckboxWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCheckboxesForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes', array('pid' => 12)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCheckboxesForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCheckboxesForSetStoragePageAndUseStoragePageSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCheckboxesForSetStoragePageAndUseStoragePageNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCheckboxesForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);
		$checkboxUid = $this->testingFramework->createRecord(
			'tx_seminars_checkboxes', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $checkboxUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListCheckboxes(array())
			)
		);
	}


	/////////////////////////////////////////////////
	// Tests concerning populateListTargetGroups().
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListTargetGroupsShowsTargetGroupWithoutOwner() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups'
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListTargetGroupsShowsTargetGroupWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('owner' => $frontEndUserUid)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListTargetGroupsHidesTargetGroupWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListTargetGroupsForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListTargetGroupsForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListTargetGroupsForSetStoragePageAndUseStoragePidSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListTargetGroupsForSetStoragePageAndUseStoragePidNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListTargetGroupsForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);
		$targetGroupUid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $targetGroupUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListTargetGroups(array())
			)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning populateListSpeakers().
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListSpeakersShowsSpeakerWithoutOwner() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers'
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListSpeakersShowsSpeakerWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', array('owner' => $frontEndUserUid)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListSpeakersHidesSpeakerWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListSpeakersForNoSetStoragePageReturnsRecordWithAnyPageId() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', array('pid' => 25)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListSpeakersForSetStoragePageReturnsRecordWithThisPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', array('pid' => 42)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListSpeakersForSetStoragePageAndUseStoragePageSetDoesNotReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean(
			'useStoragePid', TRUE
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', array('pid' => 21)
		);

		$this->assertFalse(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListSpeakersForSetStoragePageAndUseStoragePageNotSetReturnsRecordWithOtherPageId() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->testingFramework->createAndLoginFrontEndUser();
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListSpeakersForSetStoragePageAndUserWithSetAuxiliaryPidReturnsRecordWithAuxiliaryPid() {
		$pageUid = $this->testingFramework->createFrontEndPage(
			0, array('storage_pid' => 42)
		);
		$this->testingFramework->createFakeFrontEnd($pageUid);
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 21)
		);
		$speakerUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', array('pid' => 21)
		);

		$this->assertTrue(
			in_array(
				array(
					'caption' => '', 'value' => $speakerUid,
					'wrapitem' => '|</td><td>&nbsp;'
				),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}


	////////////////////////////////////////
	// Tests concerning modifyDataToInsert
	////////////////////////////////////////

	public function test_modifyDataToInsert_ForPublishSettingPublishImmediately_DoesNotHideCreatedEvent() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['hidden'])
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingPublishImmediately_DoesNotHideEditedEvent() {
		$event = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$this->fixture->setObjectUid($event->getUid());
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['hidden'])
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideNew_HidesCreatedEvent() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			1,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideEdited_HidesCreatedEvent() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			1,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideEdited_HidesEditedEvent() {
		$event = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$this->fixture->setObjectUid($event->getUid());
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			1,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideNew_DoesNotHideEditedEvent() {
		$event = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$this->fixture->setObjectUid($event->getUid());
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['hidden'])
		);
	}

	public function test_modifyDataToInsertForEventHiddenOnEditing_AddsPublicationHashToEvent() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertTrue(
			isset($modifiedFormData['publication_hash'])
				&& !empty($modifiedFormData['publication_hash'])
		);
	}

	public function test_modifyDataToInsertForEventHiddenOnCreation_AddsPublicationHashToEvent() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertTrue(
			isset($modifiedFormData['publication_hash'])
				&& !empty($modifiedFormData['publication_hash'])
		);
	}

	public function test_modifyDataToInsertForEventNotHiddenOnEditing_DoesNotAddPublicationHashToEvent() {
		$event = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Event')->getLoadedTestingModel(array());
		$this->fixture->setObjectUid($event->getUid());
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['publication_hash'])
		);
	}

	public function test_modifyDataToInsertForEventNotHiddenOnCreation_DoesNotAddPublicationHashToEvent() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['publication_hash'])
		);
	}

	public function test_modifyDataToInsertForHiddenEvent_DoesNotAddPublicationHashToEvent() {
		$event = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Event')->getLoadedTestingModel(
			array('hidden' => 1)
		);
		$this->fixture->setObjectUid($event->getUid());
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['publication_hash'])
		);
	}

	public function test_modifyDataToInsert_AddsTimestampToFormData() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertTrue(
			isset($modifiedFormData['tstamp'])
		);
	}

	public function test_modifyDataToInsert_setsTimestampToCurrentExecutionTime() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			$GLOBALS['SIM_EXEC_TIME'],
			$modifiedFormData['tstamp']
		);
	}

	public function test_modifyDataToInsert_AddsCreationDateToFormData() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertTrue(
			isset($modifiedFormData['crdate'])
		);
	}

	public function test_modifyDataToInsert_SetsCreationDateToCurrentExecutionTime() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			$GLOBALS['SIM_EXEC_TIME'],
			$modifiedFormData['crdate']
		);
	}

	public function test_modifyDataToInsert_AddsOwnerFeUserToFormData() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertTrue(
			isset($modifiedFormData['owner_feuser'])
		);
	}

	public function test_modifyDataToInsert_SetsOwnerFeUserToCurrentlyLoggedInUser() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			1,
			$modifiedFormData['owner_feuser']
		);
	}

	public function test_modifyDataToInsert_AddsEventsPidToFormData() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertTrue(
			isset($modifiedFormData['pid'])
		);
	}

	public function test_modifyDataToInsertForNoUsergroupSpecificEventPid_SetsPidFromTsSetupAsEventPid() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);
		$this->fixture->setConfigurationValue('createEventsPID', 42);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			42,
			$modifiedFormData['pid']
		);
	}

	public function test_modifyDataToInsertForUsergroupSpecificEventPid_SetsPidFromUsergroupAsEventPid() {
		$this->fixture->setConfigurationValue('createEventsPID', 42);

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getLoadedTestingModel(
				array('tx_seminars_events_pid' => 21)
		);

		$user = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUser')->getLoadedTestingModel(
				array('usergroup' => $userGroup->getUid())
		);
		$this->testingFramework->loginFrontEndUser($user->getUid());

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			21,
			$modifiedFormData['pid']
		);
	}

	public function test_modifyDataToInsertForNewEventAndUserWithoutDefaultCategories_DoesNotAddAnyCategories() {
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['categories'])
		);
	}

	public function test_modifyDataToInsertForNewEventAndUserWithOneDefaultCategory_AddsThisCategory() {
		$categories = new tx_oelib_List();
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$categories->add($category);

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array(
				'tx_seminars_default_categories' => $categories,
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			)
		);

		$this->testingFramework->createAndLogInFrontEndUser(
			$userGroup->getUid()
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			$category->getUid(),
			$modifiedFormData['categories']
		);
	}

	public function test_modifyDataToInsertForNewEventAndUserWithTwoDefaultCategories_AddsTheseCategories() {
		$categoryMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category');
		$category1 = $categoryMapper->getNewGhost();
		$category2 = $categoryMapper->getNewGhost();

		$categories = new tx_oelib_List();
		$categories->add($category1);
		$categories->add($category2);

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array(
				'tx_seminars_default_categories' => $categories,
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			)
		);

		$this->testingFramework->createAndLogInFrontEndUser(
			$userGroup->getUid()
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertEquals(
			$category1->getUid() . ',' . $category2->getUid(),
			$modifiedFormData['categories']
		);
	}

	public function test_modifyDataToInsertForEditedEventAndUserWithOneDefaultCategory_DoesNotAddTheUsersCategory() {
		$categories = new tx_oelib_List();
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();
		$categories->add($category);

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array(
				'tx_seminars_default_categories' => $categories,
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			)
		);

		$this->testingFramework->createAndLogInFrontEndUser(
			$userGroup->getUid()
		);

		$this->fixture->setObjectUid(tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Event')->getLoadedTestingModel(
				array())->getUid()
		);
		$modifiedFormData = $this->fixture->modifyDataToInsert(array());

		$this->assertFalse(
			isset($modifiedFormData['categories'])
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests regarding isFrontEndEditingOfRelatedRecordsAllowed().
	////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function isFrontEndEditingOfRelatedRecordsAllowedWithoutPermissionAndWithoutPidReturnsFalse() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

		$this->fixture->setConfigurationValue(
			'allowFrontEndEditingOfTest', FALSE
		);

		$this->assertFalse(
			$this->fixture->isFrontEndEditingOfRelatedRecordsAllowed(
				array('relatedRecordType' => 'Test')
			)
		);
	}

	/**
	 * @test
	 */
	public function isFrontEndEditingOfRelatedRecordsAllowedWithPermissionAndWithoutPidReturnsFalse() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

		$this->fixture->setConfigurationValue(
			'allowFrontEndEditingOfTest', TRUE
		);

		$this->assertFalse(
			$this->fixture->isFrontEndEditingOfRelatedRecordsAllowed(
				array('relatedRecordType' => 'Test')
			)
		);
	}

	/**
	 * @test
	 */
	public function isFrontEndEditingOfRelatedRecordsAllowedWithoutPermissionAndWithPidReturnsFalse() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 42)
		);

		$this->fixture->setConfigurationValue(
			'allowFrontEndEditingOfTest', FALSE
		);

		$this->assertFalse(
			$this->fixture->isFrontEndEditingOfRelatedRecordsAllowed(
				array('relatedRecordType' => 'Test')
			)
		);
	}

	/**
	 * @test
	 */
	public function isFrontEndEditingOfRelatedRecordsAllowedWithPermissionAndWithPidReturnsTrue() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup(
			array('tx_seminars_auxiliary_records_pid' => 42)
		);

		$this->fixture->setConfigurationValue(
			'allowFrontEndEditingOfTest', TRUE
		);

		$this->assertTrue(
			$this->fixture->isFrontEndEditingOfRelatedRecordsAllowed(
				array('relatedRecordType' => 'Test')
			)
		);
	}

	/**
	 * @test
	 */
	public function isFrontEndEditingOfRelatedRecordsAllowedWithPermissionAndWithPidSetInSetupButNotUsergroupReturnsTrue() {
		$this->createLoginAndAddFrontEndUserToEventEditorFrontEndGroup();

		$this->fixture->setConfigurationValue(
			'allowFrontEndEditingOfTest', TRUE
		);
		$this->fixture->setConfigurationValue(
			'createAuxiliaryRecordsPID', 42
		);

		$this->assertTrue(
			$this->fixture->isFrontEndEditingOfRelatedRecordsAllowed(
				array('relatedRecordType' => 'Test')
			)
		);
	}


	/////////////////////////////////////////
	// Tests concerning validateStringField
	/////////////////////////////////////////

	public function test_validateStringFieldForNonRequiredFieldAndEmptyString_ReturnsTrue() {
		$this->assertTrue(
			$this->fixture->validateString(
				array('elementName' => 'teaser', 'value' => '')
			)
		);
	}

	public function test_validateStringFieldForRequiredFieldAndEmptyString_ReturnsFalse() {
		$fixture = $this->getFixtureWithRequiredField('teaser');

		$this->assertFalse(
			$fixture->validateString(
				array('elementName' => 'teaser', 'value' => '')
			)
		);

		$fixture->__destruct();
	}

	public function test_validateStringFieldForRequiredFieldAndNonEmptyString_ReturnsTrue() {
		$fixture = $this->getFixtureWithRequiredField('teaser');

		$this->assertTrue(
			$fixture->validateString(
				array('elementName' => 'teaser', 'value' => 'foo')
			)
		);

		$fixture->__destruct();
	}


	//////////////////////////////////////////
	// Tests concerning validateIntegerField
	//////////////////////////////////////////

	public function test_validateIntegerFieldForNonRequiredFieldAndValueZero_ReturnsTrue() {
		$this->assertTrue(
			$this->fixture->validateInteger(
				array('elementName' => 'attendees_max', 'value' => 0)
			)
		);
	}

	public function test_validateIntegerFieldForRequiredFieldAndValueZero_ReturnsFalse() {
		$fixture = $this->getFixtureWithRequiredField('attendees_max');

		$this->assertFalse(
			$fixture->validateInteger(
				array('elementName' => 'attendees_max', 'value' => 0)
			)
		);

		$fixture->__destruct();
	}

	public function test_validateIntegerFieldForRequiredFieldAndValueNonZero_ReturnsTrue() {
		$fixture = $this->getFixtureWithRequiredField('attendees_max');

		$this->assertTrue(
			$fixture->validateInteger(
				array('elementName' => 'attendees_max', 'value' => 15)
			)
		);

		$fixture->__destruct();
	}


	////////////////////////////////////////
	// Tests concerning validateCheckboxes
	////////////////////////////////////////

	public function test_validateCheckboxesForNonRequiredFieldAndEmptyValue_ReturnsTrue() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$this->assertTrue(
			$this->fixture->validateCheckboxes(
				array('elementName' => 'categories', 'value' => '')
			)
		);
	}

	public function test_validateCheckboxesForRequiredFieldAndValueNotArray_ReturnsFalse() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$fixture = $this->getFixtureWithRequiredField('categories');

		$this->assertFalse(
			$fixture->validateCheckboxes(
				array('elementName' => 'categories', 'value' => '')
			)
		);

		$fixture->__destruct();
	}

	public function test_validateCheckboxesForRequiredFieldAndValueEmptyArray_ReturnsFalse() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$fixture = $this->getFixtureWithRequiredField('categories');

		$this->assertFalse(
			$fixture->validateCheckboxes(
				array('elementName' => 'categories', 'value' => array())
			)
		);

		$fixture->__destruct();
	}

	public function test_validateCheckboxesForRequiredFieldAndValueNonEmptyArray_ReturnsTrue() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$fixture = $this->getFixtureWithRequiredField('categories');

		$this->assertTrue(
			$fixture->validateCheckboxes(
				array('elementName' => 'categories', 'value' => array(42))
			)
		);

		$fixture->__destruct();
	}

	public function test_validateCheckboxesForUserWithDefaultCategoriesAndCategoriesRequiredAndEmpty_ReturnsTrue() {
		$categories = new tx_oelib_List();
		$categories->add(
			tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
				->getNewGhost()
		);

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => $categories)
		);

		$this->testingFramework->createAndLogInFrontEndUser(
			$userGroup->getUid()
		);

		$fixture = $this->getFixtureWithRequiredField('categories');

		$this->assertTrue(
			$fixture->validateCheckboxes(
				array('elementName' => 'categories', 'value' => '')
			)
		);

		$fixture->__destruct();
	}

	public function test_validateCheckboxesForUserWithoutDefaultCategoriesAndCategoriesRequiredAndEmpty_ReturnsFalse() {
		$this->testingFramework->createAndLogInFrontEndUser();
		$fixture = $this->getFixtureWithRequiredField('categories');

		$this->assertFalse(
			$fixture->validateCheckboxes(
				array('elementName' => 'categories', 'value' => '')
			)
		);

		$fixture->__destruct();
	}


	//////////////////////////////////
	// Tests concerning validateDate
	//////////////////////////////////

	public function test_validateDateForNonRequiredFieldAndEmptyString_ReturnsTrue() {
		$this->assertTrue(
			$this->fixture->validateDate(
				array('elementName' => 'begin_date', 'value' => '')
			)
		);
	}

	public function test_validateDateForRequiredFieldAndEmptyString_ReturnsFalse() {
		$fixture = $this->getFixtureWithRequiredField('begin_date');

		$this->assertFalse(
			$fixture->validateDate(
				array('elementName' => 'begin_date', 'value' => '')
			)
		);

		$fixture->__destruct();
	}

	public function test_validateDateForRequiredFieldAndValidDate_ReturnsTrue() {
		$fixture = $this->getFixtureWithRequiredField('begin_date');

		$this->assertTrue(
			$fixture->validateDate(
				array(
					'elementName' => 'begin_date',
					'value' => '10:52 23-05-2008'
				)
			)
		);

		$fixture->__destruct();
	}

	public function test_validateDateForRequiredFieldAndNonValidDate_ReturnsFalse() {
		$fixture = $this->getFixtureWithRequiredField('begin_date');

		$this->assertFalse(
			$fixture->validateDate(
				array(
					'elementName' => 'begin_date',
					'value' => 'foo'
				)
			)
		);

		$fixture->__destruct();
	}


	///////////////////////////////////
	// Tests concerning validatePrice
	///////////////////////////////////

	public function test_validatePriceForNonRequiredFieldAndEmptyString_ReturnsTrue() {
		$this->assertTrue(
			$this->fixture->validatePrice(
				array('elementName' => 'price_regular', 'value' => '')
			)
		);
	}

	public function test_validatePriceForRequiredFieldAndEmptyString_ReturnsFalse() {
		$fixture = $this->getFixtureWithRequiredField('price_regular');

		$this->assertFalse(
			$fixture->validatePrice(
				array('elementName' => 'price_regular', 'value' => '')
			)
		);

		$fixture->__destruct();
	}

	public function test_validatePriceForRequiredFieldAndValidPrice_ReturnsTrue() {
		$fixture = $this->getFixtureWithRequiredField('price_regular');

		$this->assertTrue(
			$fixture->validatePrice(
				array('elementName' => 'price_regular', 'value' => '20,08')
			)
		);

		$fixture->__destruct();
	}

	public function test_validatePriceForRequiredFieldAndInvalidPrice_ReturnsFalse() {
		$fixture = $this->getFixtureWithRequiredField('price_regular');

		$this->assertFalse(
			$fixture->validatePrice(
				array('elementName' => 'price_regular', 'value' => 'foo')
			)
		);

		$fixture->__destruct();
	}


	///////////////////////////////////////////
	// Tests concerning the publishing emails
	///////////////////////////////////////////

	public function test_eventEditorForNonHiddenEvent_DoesNotSendMail() {
		tx_oelib_mailerFactory::getInstance()->enableTestMode();

		$this->fixture->sendEMailToReviewer();

		$this->assertEquals(
			array(),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail()
		);
	}

	public function test_eventEditorForEventHiddenBeforeEditing_DoesNotSendMail() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('hidden' => 1)
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$this->fixture->modifyDataToInsert(array());

		$this->fixture->sendEMailToReviewer();

		$this->assertEquals(
			array(),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail()
		);
	}

	public function test_eventEditorForEventHiddenByForm_DoesSendMail() {
		$seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertNotEquals(
			array(),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getAllEmail()
		);
	}

	public function test_sendEMailToReviewer_SendsMailToReviewerMailAddress() {
		$seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertEquals(
			'foo@bar.com',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastRecipient()
		);
	}

	public function test_sendEMailToReviewer_SetsPublishEventSubjectInMail() {
		$seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertEquals(
			$this->fixture->translate('publish_event_subject'),
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastSubject()
		);
	}

	public function test_sendEMailToReviewer_SendsTheTitleOfTheEvent() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('title' => 'foo Event')
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertContains(
			'foo Event',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_sendEMailToReviewerForEventWithDate_SendsTheDateOfTheEvent() {
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('begin_date' => $GLOBALS['SIM_EXEC_TIME'])
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertContains(
			strftime(
				$this->fixture->getConfValueString('dateFormatYMD'),
				$GLOBALS['SIM_EXEC_TIME']
			),
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_sendEMailToReviewerForEventWithoutDate_HidesDateMarker() {
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertNotContains(
			'###PUBLISH_EVENT_DATE###',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_sendEMailToReviewerForEventWithoutDate_DoesNotSendDate() {
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
				'title' => 'foo event',
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertNotContains(
			'foo event,',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_sendEMailToReviewer_SendsMailWithoutAnyUnreplacedMarkers() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertNotContains(
			'###',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_sendEMailToReviewerForEventWithDescription_ShowsDescriptionInMail() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('description' => 'Foo Description')
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertContains(
			'Foo Description',
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_sendEMailToReviewer_SendsPublicationLinkInMail() {
		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertContains(
			'tx_seminars_publication%5Bhash%5D=' . $formData['publication_hash'],
			quoted_printable_decode(
				tx_oelib_mailerFactory::getInstance()->getMailer()->getLastBody()
			)
		);
	}

	public function test_sendEMailToReviewer_UsesFrontEndUserNameAsFromNameForMail() {
		$seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertContains(
			'Mr. Bar',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders()
		);
	}

	public function test_sendEMailToReviewer_UsesFrontEndUserMailAddressAsFromAddressForMail() {
		$seminarUid = $this->testingFramework->createRecord('tx_seminars_seminars');
		tx_oelib_mailerFactory::getInstance()->enableTestMode();
		$this->createAndLoginUserWithReviewer();

		$this->fixture->setObjectUid($seminarUid);
		$formData = $this->fixture->modifyDataToInsert(array());

		$this->testingFramework->changeRecord(
			'tx_seminars_seminars', $seminarUid,
			array(
				'hidden' => 1,
				'publication_hash' => $formData['publication_hash'],
			)
		);

		$this->fixture->sendEMailToReviewer();

		$this->assertContains(
			'<mail@foo.com>',
			tx_oelib_mailerFactory::getInstance()->getMailer()->getLastHeaders()
		);
	}


	///////////////////////////////////////////
	// Tests concerning populateListCountries
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListCountriesContainsGermany() {
		$this->assertTrue(
			in_array(
				array('caption' => 'Deutschland', 'value' => 54),
				tx_seminars_FrontEnd_EventEditor::populateListCountries()
			)
		);
	}

	/**
	 * @test
	 */
	public function populateListCountriesSortsResultsByLocalCountryName() {
		$countries = tx_seminars_FrontEnd_EventEditor::populateListCountries();
		$positionGermany = array_search(
			array('caption' => 'Deutschland', 'value' => 54), $countries
		);
		$positionGambia = array_search(
			array('caption' => 'Gambia', 'value' => 81), $countries
		);

		$this->assertTrue(
			$positionGermany < $positionGambia
		);
	}


	///////////////////////////////////////////
	// Tests concerning populateListSkills
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function populateListSkillsHasSkillFromDatabase() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_skills', array('title' => 'Juggling')
		);

		$this->assertTrue(
			in_array(
				array('caption' => 'Juggling', 'value' => $uid),
				tx_seminars_FrontEnd_EventEditor::populateListSkills()
			)
		);
	}


	//////////////////////////////////////////////
	// Tests concerning makeListToFormidableList
	//////////////////////////////////////////////

	public function test_makeListToFormidableList_ForEmptyListGiven_ReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			tx_seminars_FrontEnd_EventEditor::makeListToFormidableList(new tx_oelib_List())
		);
	}

	public function test_makeListToFormidableList_ForListWithOneElement_ReturnsModelDataInArray() {
		$targetGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_TargetGroup')->getLoadedTestingModel(
				array('title' => 'foo')
		);

		$list = new tx_oelib_List();
		$list->add($targetGroup);

		$this->assertTrue(
			in_array(
				array('caption' => 'foo', 'value' => $targetGroup->getUid()),
				tx_seminars_FrontEnd_EventEditor::makeListToFormidableList($list)
			)
		);
	}

	public function test_makeListToFormidableList_ForListWithTwoElements_ReturnsArrayWithTwoModels() {
		$targetGroup1 = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_TargetGroup')->getLoadedTestingModel(array());
		$targetGroup2 = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_TargetGroup')->getLoadedTestingModel(array());

		$list = new tx_oelib_List();
		$list->add($targetGroup1);
		$list->add($targetGroup2);

		$this->assertEquals(
			2,
			count(tx_seminars_FrontEnd_EventEditor::makeListToFormidableList($list))
		);
	}


	/////////////////////////////////////////////
	// Tests concerning getPreselectedOrganizer
	/////////////////////////////////////////////

	public function test_getPreselectedOrganizerForNoAvailableOrganizer_ReturnsZero() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertEquals(
			0,
			$this->fixture->getPreselectedOrganizer()
		);
	}

	public function test_getPreselectedOrganizerForOneAvailableOrganizer_ReturnsTheOrganizersUid() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord('tx_seminars_organizers');

		$this->assertEquals(
			$organizerUid,
			$this->fixture->getPreselectedOrganizer()
		);
	}

	public function test_getPreselectedOrganizerForTwoAvailableOrganizers_ReturnsZero() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->testingFramework->createRecord('tx_seminars_organizers');
		$this->testingFramework->createRecord('tx_seminars_organizers');

		$this->assertEquals(
			0,
			$this->fixture->getPreselectedOrganizer()
		);
	}
}
?>