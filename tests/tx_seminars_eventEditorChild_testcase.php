<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_eventEditorChild.php');
require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the eventEditorChild class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_eventEditorChild_testcase extends tx_phpunit_testcase {
	private $fixture;

	/** our instance of the testing framework */
	private $testingFramework;

	/** the UID of a dummy front end page */
	private $frontEndPageUid;

	/** the instance of tx_seminars_pi1*/
	private $pi1;

	/**
	 * @var integer the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createFakeFrontEnd($this->frontEndPageUid);

		$this->pi1 = new tx_seminars_pi1();
		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'eventSuccessfullySavedPID' => $this->frontEndPageUid,
			)
		);

		$this->fixture = new tx_seminars_eventEditorChild($this->pi1);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		$this->pi1->__destruct();
		unset($this->testingFramework, $this->fixture, $this->pi1);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Creates a FE user, adds him/her as a VIP to the seminar with the UID in
	 * $this->seminarUid and logs him/her in.
	 */
	private function createLogInAndAddFeUserAsVip() {
		$feUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_VIPS_MM,
			$this->seminarUid,
			$feUserUid
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('vips' => 1)
		);
	}

	/**
	 * Creates a FE user, adds his/her FE user group as a default VIP group via
	 * TS setup and logs him/her in.
	 */
	private function createLogInAndAddFeUserAsDefaultVip() {
		$feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->pi1->setConfigurationValue(
			'defaultEventVipsFeGroupID', $feUserGroupUid
		);
		$feUserUid = $this->testingFramework->createFrontEndUser($feUserGroupUid);
		$this->testingFramework->loginFrontEndUser($feUserUid);
	}

	/**
	 * Creates a FE user, adds him/her as a owner to the seminar with the UID in
	 * $this->seminarUid and logs him/her in.
	 */
	private function createLogInAndAddFeUserAsOwner() {
		$feUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $feUserUid)
		);
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testCreateLogInAndAddFeUserAsVipCreatesFeUser() {
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->createLogInAndAddFeUserAsVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords('fe_users')
		);
	}

	public function testCreateLogInAndAddFeUserAsVipLogsInFeUser() {
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->createLogInAndAddFeUserAsVip();

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	public function testCreateLogInAndAddFeUserAsVipAddsUserAsVip() {
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->createLogInAndAddFeUserAsVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS,
				'uid=' . $this->seminarUid . ' AND vips=1'
			)
		);
	}

	public function testCreateLogInAndAddFeUserAsOwnerCreatesFeUser() {
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords('fe_users')
		);
	}

	public function testCreateLogInAndAddFeUserAsOwnerLogsInFeUser() {
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	public function testCreateLogInAndAddFeUserAsOwnerAddsUserAsOwner() {
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS,
				'uid=' . $this->seminarUid . ' AND owner_feuser>0'
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
			$this->fixture->isLoggedIn()
		);
	}

	public function testCreateLogInAndAddFeUserAsDefaultVipAddsFeUserAsDefaultVip() {
		$this->createLogInAndAddFeUserAsDefaultVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				'fe_users',
				'uid=' . $this->fixture->getFeUserUid() .
					' AND usergroup=' . $this->pi1->getConfValueInteger(
						'defaultEventVipsFeGroupID'
					)
			)
		);
	}


	///////////////////////////////////////////////////////
	// Tests for getting the event-successfully-saved URL
	///////////////////////////////////////////////////////

	public function testGetEventSuccessfullySavedUrlReturnsUrlStartingWithHttp() {
		$this->assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getEventSuccessfullySavedUrl()
		);
	}


	/////////////////////////////////
	// Tests concerning hasAccess()
	/////////////////////////////////

	public function testHasAccessInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithActionUnequalToEditReturnsFalse() {
		$this->pi1->piVars['action'] = 'invalid';
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('deleted' => 1)
		);
		$this->pi1->piVars['seminar'] = $seminarUid;

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithoutSeminarInPiVarsReturnsFalse() {
		$this->pi1->piVars['action'] = 'EDIT';
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('deleted' => 1)
		);
		$this->pi1->piVars['seminar'] = $seminarUid;

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithInExistentSeminarInPiVarsReturnsFalse() {
		$this->pi1->piVars['action'] = 'EDIT';
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('deleted' => 1)
		);
		$this->pi1->piVars['seminar'] = $seminarUid;

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithNoLoggedInFeUserReturnsFalse() {
		$this->pi1->piVars['action'] = 'EDIT';
		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->pi1->piVars['seminar'] = $seminarUid;

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithLoggedInFeUserWhoIsNeitherVipNorOwnerReturnsFalse() {
		$this->pi1->piVars['action'] = 'EDIT';
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->pi1->piVars['seminar'] = $this->seminarUid;
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithLoggedInFeUserAsOwnerReturnsTrue() {
		$this->pi1->piVars['action'] = 'EDIT';
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->pi1->piVars['seminar'] = $this->seminarUid;
		$this->createLogInAndAddFeUserAsOwner();

		$this->assertTrue(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithLoggedInFeUserAsVipAndVipsMayNotEditTheirEventsReturnsFalse() {
		$this->pi1->setConfigurationValue('mayManagersEditTheirEvents' , 0);
		$this->pi1->piVars['action'] = 'EDIT';
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->pi1->piVars['seminar'] = $this->seminarUid;
		$this->createLogInAndAddFeUserAsVip();

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithLoggedInFeUserAsVipAndVipsMayEditTheirEventsReturnsTrue() {
		$this->pi1->setConfigurationValue('mayManagersEditTheirEvents' , 1);
		$this->pi1->piVars['action'] = 'EDIT';
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->pi1->piVars['seminar'] = $this->seminarUid;
		$this->createLogInAndAddFeUserAsVip();

		$this->assertTrue(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithLoggedInFeUserAsDefaultVipAndVipsMayNotEditTheirEventsReturnsFalse() {
		$this->pi1->setConfigurationValue('mayManagersEditTheirEvents' , 0);
		$this->pi1->piVars['action'] = 'EDIT';
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->pi1->piVars['seminar'] = $this->seminarUid;
		$this->createLogInAndAddFeUserAsDefaultVip();;

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessWithLoggedInFeUserAsDefaultVipAndVipsMayEditTheirEventsReturnsTrue() {
		$this->pi1->setConfigurationValue('mayManagersEditTheirEvents' , 1);
		$this->pi1->piVars['action'] = 'EDIT';
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->pi1->piVars['seminar'] = $this->seminarUid;
		$this->createLogInAndAddFeUserAsDefaultVip();;

		$this->assertTrue(
			$this->fixture->hasAccess()
		);
	}


	public function testHasAccessForLoggedOutUserReturnsFalse() {
		$this->testingFramework->logoutFrontEndUser();

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessForLoggedInUserInUnauthorizedUsergroupReturnsFalse() {
		$this->testingFramework->createAndLoginFrontEndUser();

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessForLoggedInUserInAuthorizedUsergroupAndNoUidSetReturnsTrue() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->pi1->setConfigurationValue('eventEditorFeGroupID', $groupUid);

		$this->assertTrue(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessForLoggedInUserInAuthorizedUsergroupButNotAuthorOfGivenEventReturnsFalse() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->pi1->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->pi1->piVars['seminar'] = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array()
		);
		$this->pi1->piVars['action'] = 'EDIT';

		$this->assertFalse(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessForLoggedInUserInAuthorizedUsergroupAndAuthorOfGivenEventReturnsTrue() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$userUid = $this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->pi1->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->pi1->piVars['seminar'] = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('owner_feuser' => $userUid)
		);
		$this->pi1->piVars['action'] = 'EDIT';

		$this->assertTrue(
			$this->fixture->hasAccess()
		);
	}

	public function testHasAccessForLoggedInUserAndInvalidSeminarIdReturnsFalse() {
		$groupUid = $this->testingFramework->createFrontEndUsergroup(
			array('title' => 'test')
		);
		$this->testingFramework->createAndLoginFrontEndUser($groupUid);

		$this->pi1->setConfigurationValue('eventEditorFeGroupID', $groupUid);
		$this->pi1->piVars['seminar']
			= $this->testingFramework->getAutoIncrement(
				SEMINARS_TABLE_SEMINARS
			);
		$this->pi1->piVars['action'] = 'EDIT';

		$this->assertFalse(
			$this->fixture->hasAccess()
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