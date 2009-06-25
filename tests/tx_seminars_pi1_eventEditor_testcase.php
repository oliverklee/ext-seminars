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

	/**
	 * Creates a front end user ghost which has a group with the given publish
	 * settings.
	 *
	 * @param integer the publish settings for the user, must be one of the
	 *                following:
	 *                tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
	 *                tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW, or
	 *                tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
	 */
	private function createAndLoginUserWithPublishSetting($publishSetting) {
		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_publish_events' => $publishSetting)
		);
		$list = new tx_oelib_List();
		$list->add($userGroup);

		$user = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUser')->getNewGhost();
		$user->setData(array('usergroup' => $list));
		$this->testingFramework->loginFrontEndUser($user->getUid());
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
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->populateListCategories(array());
	}

	/**
	 * @test
	 */
	public function populateListCategoriesShowsCategoryWithoutOwner() {
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
	public function populateListCategoriesShowsCategoryWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array('owner' => $frontEndUserUid)
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
	public function populateListCategoriesHidesCategoryWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
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
	public function populateListEventTypesShowsEventTypeWithoutOwner() {
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
	public function populateListEventTypesShowsEventTypeWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('owner' => $frontEndUserUid)
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
	public function populateListEventTypesHidesEventTypeWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
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
	public function populateListLodgingsShowsLodgingWithoutOwner() {
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
	public function populateListLodgingsShowsLodgingWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('owner' => $frontEndUserUid)
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
	public function populateListLodgingsHidesLodgingWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$lodgingUid = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
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
	public function populateListFoodsShowsFoodWithoutOwner() {
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
	public function populateListFoodsShowsFoodWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('owner' => $frontEndUserUid)
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
	public function populateListFoodsHidesFoodWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$foodUid = $this->testingFramework->createRecord(
			'tx_seminars_foods', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
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
	public function populateListPaymentMethodsShowsPaymentMethodWithoutOwner() {
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
	public function populateListPaymentMethodsShowsPaymentMethodWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('owner' => $frontEndUserUid)
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
	public function populateListPaymentMethodsHidesPaymentMethodWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$paymentMethodUid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
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
	public function populateListOrganizersShowsOrganizerWithoutOwner() {
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
	public function populateListOrganizersShowsOrganizerWithOwnerIsLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('owner' => $frontEndUserUid)
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
	public function populateListOrganizersHidesOrganizerWithOwnerIsNotLoggedInFrontEndUser() {
		$frontEndUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('owner' => $frontEndUserUid + 1)
		);

		$this->assertFalse(
			in_array(
				array('caption' => '', 'value' => $organizerUid),
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
				array('caption' => '', 'value' => $placeUid),
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
				array('caption' => '', 'value' => $placeUid),
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
				array('caption' => '', 'value' => $placeUid),
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
				array('caption' => '', 'value' => $checkboxUid),
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
				array('caption' => '', 'value' => $checkboxUid),
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
				array('caption' => '', 'value' => $checkboxUid),
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
				array('caption' => '', 'value' => $targetGroupUid),
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
				array('caption' => '', 'value' => $targetGroupUid),
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
				array('caption' => '', 'value' => $targetGroupUid),
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
				array('caption' => '', 'value' => $speakerUid),
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
				array('caption' => '', 'value' => $speakerUid),
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
				array('caption' => '', 'value' => $speakerUid),
				$this->fixture->populateListSpeakers(array())
			)
		);
	}


	////////////////////////////////////////
	// Tests concerning modifyDataToInsert
	////////////////////////////////////////

	public function test_modifyDataToInsert_ForPublishSettingPublishImmediately_DoesNotHideCreatedEvent() {
		$formData = array('hidden' => 0);
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert($formData);

		$this->assertEquals(
			0,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingPublishImmediately_DoesNotHideEditedEvent() {
		$formData = array('hidden' => 0);
		$this->fixture->setObjectUid(42);
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert($formData);

		$this->assertEquals(
			0,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideNew_HidesCreatedEvent() {
		$formData = array('hidden' => 0);
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert($formData);

		$this->assertEquals(
			1,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideEdited_HidesCreatedEvent() {
		$formData = array('hidden' => 0);
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert($formData);

		$this->assertEquals(
			1,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideEdited_HidesEditedEvent() {
		$formData = array('hidden' => 0);
		$this->fixture->setObjectUid(42);
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert($formData);

		$this->assertEquals(
			1,
			$modifiedFormData['hidden']
		);
	}

	public function test_modifyDataToInsert_ForPublishSettingHideNew_DoesNotHideEditedEvent() {
		$formData = array('hidden' => 0);
		$this->fixture->setObjectUid(42);
		$this->createAndLoginUserWithPublishSetting(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		);

		$modifiedFormData = $this->fixture->modifyDataToInsert($formData);

		$this->assertEquals(
			0,
			$modifiedFormData['hidden']
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
			'allowFrontEndEditingOfTest', false
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
			'allowFrontEndEditingOfTest', true
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
			'allowFrontEndEditingOfTest', false
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
			'allowFrontEndEditingOfTest', true
		);

		$this->assertTrue(
			$this->fixture->isFrontEndEditingOfRelatedRecordsAllowed(
				array('relatedRecordType' => 'Test')
			)
		);
	}
}
?>