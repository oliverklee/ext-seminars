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

require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1.php');

/**
 * Testcase for the pi1 class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi1_testcase extends tx_phpunit_testcase {
	/** @var tx_seminars_pi1 */
	private $fixture;
	/** @var tx_oelib_testingFramework */
	private $testingFramework;

	/** the UID of a seminar to which the fixture relates */
	private $seminarUid;

	/** PID of a dummy system folder */
	private $systemFolderPid = 0;

	/** the number of target groups for the current event record */
	private $numberOfTargetGroups = 0;

	/** @var integer the number of categories for the current event record */
	private $numberOfCategories = 0;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->systemFolderPid = $this->testingFramework->createSystemFolder();
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Test event',
			)
		);

		$this->fixture = new tx_seminars_pi1();
		$this->fixture->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
				'what_to_display' => 'seminar_list',
				'pidList' => $this->systemFolderPid,
				'pages' => $this->systemFolderPid,
				'recursive' => 1,
				'listView.' => array(
					'orderBy' => 'data',
					'descFlag' => 0,
					'results_at_a_time' => 5,
					'maxPages' => 5,
				),
			)
		);
		$this->fixture->getTemplateCode();
		$this->fixture->setLabels();
		$this->fixture->createHelperObjects();
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'dateFormatYMD', '%d.%m.%Y'
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Inserts a target group record into the database and creates a relation to
	 * it from the event with the UID store in $this->seminarUid.
	 *
	 * @param array data of the target group to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addTargetGroupRelation(array $targetGroupData = array()) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS, $targetGroupData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM,
			$this->seminarUid, $uid
		);

		$this->numberOfTargetGroups++;
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('target_groups' => $this->numberOfTargetGroups)
		);

		return $uid;
	}

	/**
	 * Creates a FE user, registers him/her to the seminar with the UID in
	 * $this->seminarUid and logs him/her in.
	 */
	private function createLogInAndRegisterFeUser() {
		$feUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $this->seminarUid,
				'user' => $feUserUid,
			)
		);
	}

	/**
	 * Creates a FE user, adds him/her as a VIP to the seminar with the UID in
	 * $this->seminarUid and logs him/her in.
	 */
	private function createLogInAndAddFeUserAsVip() {
		$feUserUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_MANAGERS_MM,
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
	 * Inserts a category record into the database and creates a relation to
	 * it from the event with the UID stored in $this->seminarUid.
	 *
	 * @param array data of the category to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addCategoryRelation(array $categoryData = array()) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES, $categoryData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$this->seminarUid, $uid
		);

		$this->numberOfCategories++;
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('categories' => $this->numberOfCategories)
		);

		return $uid;
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testAddTargetGroupRelationReturnsUid() {
		$this->assertTrue(
			$this->addTargetGroupRelation(array()) > 0
		);
	}

	public function testAddTargetGroupRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addTargetGroupRelation(array()),
			$this->addTargetGroupRelation(array())
		);
	}

	public function testAddTargetGroupRelationIncreasesTheNumberOfTargetGroups() {
		$this->assertEquals(
			0,
			$this->numberOfTargetGroups
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			1,
			$this->numberOfTargetGroups
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			2,
			$this->numberOfTargetGroups
		);
	}

	public function testAddTargetGroupRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM,
				'uid_local='.$this->seminarUid
			)

		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM,
				'uid_local='.$this->seminarUid
			)
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM,
				'uid_local='.$this->seminarUid
			)
		);
	}

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
			$this->fixture->isLoggedIn()
		);
	}

	public function testCreateLogInAndAddFeUserAsVipAddsUserAsVip() {
		$this->createLogInAndAddFeUserAsVip();

		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS,
				'uid=' . $this->seminarUid . ' AND vips=1'
			)
		);
	}

	public function testAddCategoryRelationReturnsPositiveUid() {
		$this->assertTrue(
			$this->addCategoryRelation(array()) > 0
		);
	}

	public function testAddCategoryRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addCategoryRelation(array()),
			$this->addCategoryRelation(array())
		);
	}

	public function testAddCategoryRelationIncreasesTheNumberOfCategories() {
		$this->assertEquals(
			0,
			$this->numberOfCategories
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			1,
			$this->numberOfCategories
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			2,
			$this->numberOfCategories
		);
	}

	public function testAddCategoryRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
				'uid_local='.$this->seminarUid
			)

		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
				'uid_local='.$this->seminarUid
			)
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
				'uid_local='.$this->seminarUid
			)
		);
	}


	////////////////////////////////////////////
	// Test concerning the base functionality.
	////////////////////////////////////////////

	public function testPi1MustBeInitialized() {
		$this->assertNotNull(
			$this->fixture
		);
		$this->assertTrue(
			$this->fixture->isInitialized()
		);
	}

	public function testGetSeminarReturnsSeminarIfSet() {
		$this->fixture->createSeminar($this->seminarUid);

		$this->assertTrue(
			$this->fixture->getSeminar() instanceof tx_seminars_seminar
		);
	}

	public function testGetRegistrationReturnsRegistrationIfSet() {
		$this->fixture->createRegistration(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_ATTENDANCES,
				array('seminar' => $this->seminarUid)
			)
		);

		$this->assertTrue(
			$this->fixture->getRegistration()
				instanceof tx_seminars_registration
		);
	}

	public function testGetRegistrationManagerReturnsRegistrationManagerIfSet() {
		$this->assertTrue(
			$this->fixture->getRegistrationManager()
				instanceof tx_seminars_registrationmanager
		);
	}


	//////////////////////////////////////
	// Tests concerning the single view.
	//////////////////////////////////////

	public function testSingleViewContainsEventTitle() {
		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testOtherDatesListInSingleViewContainsOtherDateWithDateLinkedToSingleViewOfOtherDate() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'Test topic',
			)
		);
		$dateUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'title' => 'Test date',
				'begin_date' => time() + ONE_WEEK,
				'end_date' => time() + ONE_WEEK + ONE_DAY,
			)
		);
		$dateUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'title' => 'Test date 2',
				'begin_date' => time() + ONE_WEEK + 2*ONE_DAY,
				'end_date' => time() + ONE_WEEK + 3*ONE_DAY,
			)
		);

		$this->fixture->piVars['showUid'] = $dateUid1;
		$result = $this->fixture->main('', array());
		$this->assertContains(
			'tx_seminars_pi1%5BshowUid%5D=' . $dateUid2,
			$result
		);
	}

	public function testOtherDatesListInSingleViewDoesNotContainSingleEventRecordWithTopicSet() {
		$this->fixture->setConfigurationValue(
			'detailPID', $this->testingFramework->createFrontEndPage()
		);
		$this->fixture->setConfigurationValue(
			'hideFields', 'eventsnextday'
		);
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'Test topic',
			)
		);
		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'title' => 'Test date',
				'begin_date' => time() + ONE_WEEK,
				'end_date' => time() + ONE_WEEK + ONE_DAY,
			)
		);
		$singleEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'topic' => $topicUid,
				'title' => 'Test single 2',
				'begin_date' => time() + ONE_WEEK + 2*ONE_DAY,
				'end_date' => time() + ONE_WEEK + 3*ONE_DAY,
			)
		);

		$this->fixture->piVars['showUid'] = $dateUid;
		$result = $this->fixture->main('', array());

		$this->assertNotContains(
			'tx_seminars_pi1%5BshowUid%5D=' . $singleEventUid,
			$result
		);
	}

	public function testSingleViewDisplaysAndLinksSpeakersNameButNotCompany() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$this->fixture->setConfigurationValue('showSpeakerDetails', true);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$speakerUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array (
				'title' => 'foo',
				'organization' => 'bar',
				'homepage' => 'www.foo.com',
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SPEAKERS_MM,
			$this->seminarUid, $speakerUid
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('speakers' => '1')
		);

		$this->assertRegExp(
			'/<a href="http:\/\/www.foo.com".*>foo<\/a>, bar/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithoutImageDoesNotDisplayImage() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$this->fixture->setConfigurationValue(
			'seminarImageSingleViewWidth', 260
		);
		$this->fixture->setConfigurationValue(
			'seminarImageSingleViewHeight', 160
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertNotContains(
			'style="background-image:',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewDisplaysSeminarImage() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$this->fixture->setConfigurationValue(
			'seminarImageSingleViewWidth', 260
		);
		$this->fixture->setConfigurationValue(
			'seminarImageSingleViewHeight', 160
		);

		$this->testingFramework->createDummyFile('test_foo.gif');
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('image' => 'test_foo.gif')
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$seminarWithImage = $this->fixture->main('', array());

		$this->testingFramework->deleteDummyFile('test_foo.gif');

		$this->assertContains(
			'style="background-image:',
			$seminarWithImage
		);
	}

	public function testSingleViewForHideFieldsContainingImageHidesSeminarImage() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$this->fixture->setConfigurationValue('hideFields', 'image');
		$this->fixture->setConfigurationValue(
			'seminarImageSingleViewWidth', 260
		);
		$this->fixture->setConfigurationValue(
			'seminarImageSingleViewHeight', 160
		);

		$this->testingFramework->createDummyFile('test_foo.gif');
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('image' => 'test_foo.gif')
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$seminarWithImage = $this->fixture->main('', array());

		$this->testingFramework->deleteDummyFile('test_foo.gif');

		$this->assertNotContains(
			'style="background-image:',
			$seminarWithImage
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning attached files in the single view
	///////////////////////////////////////////////////////

	public function testSingleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFile() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			$dummyFileName,
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFileLinkedToFile() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertRegExp(
			'/<a href="http:\/\/[\w\d_\-\/]*' . $dummyFileName . '" >' .
				$dummyFileName . '<\/a>/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithOneAttachedFileInSubfolderOfUploadFolderAndDisabledLimitFileDownloadToAttendeesContainsFileNameOfFileLinkedToFile() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$dummyFolder = $this->testingFramework->createDummyFolder('test_folder');
		$dummyFile = $this->testingFramework->createDummyFile(
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertRegExp(
			'/<a href="http:\/\/[\w\d_\-\/]*' .
				str_replace('/', '\/', $dummyFileName) . '" >' .
				basename($dummyFile) . '<\/a>/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithTwoAttachedFilesAndDisabledLimitFileDownloadToAttendeesContainsBothFileNames() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$dummyFile2 = $this->testingFramework->createDummyFile();
		$dummyFileName2 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName . ',' . $dummyFileName2)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			$dummyFileName,
			$this->fixture->main('', array())
		);
		$this->assertContains(
			$dummyFileName2,
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithTwoAttachedFilesAndDisabledLimitFileDownloadToAttendeesContainsTwoAttachedFilesWithSortingSetInBackEnd() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$dummyFile2 = $this->testingFramework->createDummyFile();
		$dummyFileName2 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName . ',' . $dummyFileName2)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertRegExp(
			'/.*(' . preg_quote($dummyFileName) . ').*\s*' .
				'.*(' . preg_quote($dummyFileName2) . ').*/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithOneAttachedFileAndLoggedInFeUserAndRegisteredContainsFileNameOfFile() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->createLogInAndRegisterFeUser();

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			$dummyFileName,
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithOneAttachedFileAndLoggedInFeUserAndRegisteredContainsFileNameOfFileLinkedToFile() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->createLogInAndRegisterFeUser();

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertRegExp(
			'/<a href="http:\/\/[\w\d_\-\/]*' . $dummyFileName . '" >' .
				$dummyFileName . '<\/a>/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithOneAttachedFileInSubfolderOfUploadFolderAndLoggedInFeUserAndRegisteredContainsFileNameOfFileLinkedToFile() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->createLogInAndRegisterFeUser();

		$dummyFolder = $this->testingFramework->createDummyFolder('test_folder');
		$dummyFile = $this->testingFramework->createDummyFile(
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertRegExp(
			'/<a href="http:\/\/[\w\d_\-\/]*' .
				str_replace('/', '\/', $dummyFileName) . '" >' .
				basename($dummyFile) . '<\/a>/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithTwoAttachedFilesAndLoggedInFeUserAndRegisteredContainsBothFileNames() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->createLogInAndRegisterFeUser();

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$dummyFile2 = $this->testingFramework->createDummyFile();
		$dummyFileName2 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName . ',' . $dummyFileName2)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			$dummyFileName,
			$this->fixture->main('', array())
		);
		$this->assertContains(
			$dummyFileName2,
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithTwoAttachedFilesAndLoggedInFeUserAndRegisteredContainsTwoAttachedFilesWithSortingSetInBackEnd() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->createLogInAndRegisterFeUser();

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$dummyFile2 = $this->testingFramework->createDummyFile();
		$dummyFileName2 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName . ',' . $dummyFileName2)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertRegExp(
			'/.*(' . preg_quote($dummyFileName) . ').*\s*' .
				'.*(' . preg_quote($dummyFileName2) . ').*/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewWithOneAttachedFileAndDisabledLimitFileDownloadToAttendeesContainsCSSClassWithFileType() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$matches = array();
		preg_match('/\.(\w+)$/', $dummyFileName, $matches);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertRegExp(
			'/class="filetype-' . $matches[1] . '">' .
				'<a href="http:\/\/[\w\d_\-\/]*' .
				str_replace('/', '\/', $dummyFileName) . '" >' .
				basename($dummyFile) . '<\/a>/',
			$this->fixture->main('', array())
		);
	}

	public function testAttachedFilesSubpartIsHiddenInSingleViewWithoutAttachedFilesAndWithLoggedInAndRegisteredFeUser() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->createLogInAndRegisterFeUser();

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
		);
	}

	public function testAttachedFilesSubpartIsHiddenInSingleViewWithAttachedFilesAndLoggedInAndUnregisteredFeUser() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->testingFramework->createAndLoginFrontEndUser();

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
		);
	}

	public function testAttachedFilesSubpartIsHiddenInSingleViewWithAttachedFilesAndNoLoggedInFeUser() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
		);
	}

	public function testAttachedFilesSubpartIsVisibleInSingleViewWithAttachedFilesAndLoggedInAndRegisteredFeUser() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 1);
		$this->createLogInAndRegisterFeUser();

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
		);
	}

	public function testAttachedFilesSubpartIsVisibleInSingleViewWithAttachedFilesAndDisabledLimitFileDownloadToAttendees() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('attached_files' => $dummyFileName)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
		);
	}

	public function testAttachedFilesSubpartIsHiddenInSingleViewWithoutAttachedFilesAndWithDisabledLimitFileDownloadToAttendees() {
		$this->fixture->setConfigurationValue('limitFileDownloadToAttendees', 0);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_ATTACHED_FILES')
		);
	}


	///////////////////////////////////////////////
	// Tests concerning places in the single view
	///////////////////////////////////////////////

	public function testSingleViewContainsTitleOfEventPlace() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM, $eventUid, $placeUid
		);

		$this->fixture->piVars['showUid'] = $eventUid;

		$this->assertContains(
			'a place',
			$this->fixture->main('', array())
		);
	}


	////////////////////////////////////////////////////
	// Tests concerning time slots in the single view.
	////////////////////////////////////////////////////

	public function testTimeSlotsSubpartIsHiddenInSingleViewWithoutTimeSlots() {
		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
		);
	}

	public function testTimeSlotsSubpartIsVisibleInSingleViewWithOneTimeSlot() {
		$timeSlotUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS, array('seminar' => $this->seminarUid)
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('timeslots' => (string) $timeSlotUid)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
		);
	}

	public function testSingleViewCanContainOneTimeSlotRoom() {
		$timeSlotUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $this->seminarUid,
				'room' => 'room 1'
			)
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('timeslots' => (string) $timeSlotUid)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			'room 1',
			$this->fixture->main('', array())
		);
	}

	public function testTimeSlotsSubpartIsVisibleInSingleViewWithTwoTimeSlots() {
		$timeSlotUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS, array('seminar' => $this->seminarUid)
		);
		$timeSlotUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS, array('seminar' => $this->seminarUid)
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('timeslots' => $timeSlotUid1.','.$timeSlotUid2)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_TIMESLOTS')
		);
	}

	public function testSingleViewCanContainTwoTimeSlotRooms() {
		$timeSlotUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $this->seminarUid,
				'room' => 'room 1'
			)
		);
		$timeSlotUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $this->seminarUid,
				'room' => 'room 2'
			)
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('timeslots' => $timeSlotUid1.','.$timeSlotUid2)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			'room 1',
			$this->fixture->main('', array())
		);
		$this->assertContains(
			'room 2',
			$this->fixture->main('', array())
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning target groups in the single view.
	///////////////////////////////////////////////////////

	public function testTargetGroupsSubpartIsHiddenInSingleViewWithoutTargetGroups() {
		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
		);
	}

	public function testTargetGroupsSubpartIsVisibleInSingleViewWithOneTargetGroup() {
		$this->addTargetGroupRelation();

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
		);
	}

	public function testSingleViewCanContainOneTargetGroupTitle() {
		$this->addTargetGroupRelation(
			array('title' => 'group 1')
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			'group 1',
			$this->fixture->main('', array())
		);
	}

	public function testTargetGroupsSubpartIsVisibleInSingleViewWithTwoTargetGroups() {
		$this->addTargetGroupRelation(
			array('title' => 'group 1')
		);
		$this->addTargetGroupRelation(
			array('title' => 'group 2')
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_TARGET_GROUPS')
		);
	}

	public function testSingleViewCanContainTwoTargetGroupTitles() {
		$this->addTargetGroupRelation(
			array('title' => 'group 1')
		);
		$this->addTargetGroupRelation(
			array('title' => 'group 2')
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$result = $this->fixture->main('', array());

		$this->assertContains(
			'group 1',
			$result
		);
		$this->assertContains(
			'group 2',
			$result
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning requirements in the single view.
	///////////////////////////////////////////////////////

	public function testSingleViewForSeminarWithoutRequirementsHidesRequirementsSubpart() {
		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
		);
	}

	public function testSingleViewForSeminarWithOneRequirementDisplaysRequirementsSubpart() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$requiredEvent = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			$requiredEvent, 'requirements'
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());

		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
		);
	}

	public function testSingleViewForSeminarWithOneRequirementLinksRequirementToItsSingleView() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$requiredEvent = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'required_foo',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			$requiredEvent, 'requirements'
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertRegExp(
			'/<a href=.*' . $requiredEvent . '.*>required_foo<\/a>/',
			$this->fixture->main('', array())
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning dependencies in the single view.
	///////////////////////////////////////////////////////

	public function testSingleViewForSeminarWithoutDependenciesHidesDependenciesSubpart() {
		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_DEPENDENCIES')
		);
	}

	public function testSingleViewForSeminarWithOneDependencyDisplaysDependenciesSubpart() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM,
			$dependingEventUid, $this->seminarUid
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->fixture->main('', array());

		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_DEPENDENCIES')
		);
	}

	public function testSingleViewForSeminarWithOneDependenciesShowsTitleOfDependency() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'depending_foo',
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM,
			$dependingEventUid, $this->seminarUid
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertContains(
			'depending_foo',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOneDependencyLinksDependencyToItsSingleView() {
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependingEventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'depending_foo',
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM,
			$dependingEventUid, $this->seminarUid
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertRegExp(
			'/<a href=.*' . $dependingEventUid . '.*>depending_foo<\/a>/',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithTwoDependenciesShowsTitleOfBothDependencies() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'dependencies' => 2,
			)
		);
		$dependingEventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'depending_foo',
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM,
			$dependingEventUid1, $this->seminarUid
		);
		$dependingEventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'depending_bar',
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM,
			$dependingEventUid2, $this->seminarUid
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertRegExp(
			'/depending_bar.*depending_foo/s',
			$this->fixture->main('', array())
		);
	}


	//////////////////////////////////////////////////////
	// Test concerning the event type in the single view
	//////////////////////////////////////////////////////

	public function testSingleViewContainsEventTypeTitleAndColonIfEventHasEventType() {
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array(
				'event_type' => $this->testingFramework->createRecord(
					SEMINARS_TABLE_EVENT_TYPES, array('title' => 'foo type')
				)
			)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertContains(
			'foo type:',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewNotContainsColonBeforeEventTitleIfEventHasNoEventType() {
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertNotRegExp(
			'/: *Test event/',
			$this->fixture->main('', array())
		);
	}


	//////////////////////////////////////////////////////
	// Test concerning the categories in the single view
	//////////////////////////////////////////////////////

	public function testSingleViewCanContainOneCategory() {
		$this->addCategoryRelation(
			array('title' => 'category 1')
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$this->assertContains(
			'category 1',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewCanContainTwoCategories() {
		$this->addCategoryRelation(
			array('title' => 'category 1')
		);
		$this->addCategoryRelation(
			array('title' => 'category 2')
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;
		$result = $this->fixture->main('', array());

		$this->assertContains(
			'category 1',
			$result
		);
		$this->assertContains(
			'category 2',
			$result
		);
	}

	public function testSingleViewShowsCategoryIcon() {
		$this->testingFramework->createDummyFile('foo_test.gif');
		$this->addCategoryRelation(
			array(
				'title' => 'category 1',
				'icon' => 'foo_test.gif',
			)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$singleCategoryWithIcon = $this->fixture->main('', array());

		$this->testingFramework->deleteDummyFile('foo_test.gif');

		$this->assertContains(
			'category 1 <img src="',
			$singleCategoryWithIcon
		);
	}

	public function testSingleViewShowsMultipleCategoriesWithIcons() {
		$this->testingFramework->createDummyFile('foo_test.gif');
		$this->testingFramework->createDummyFile('foo_test2.gif');
		$this->addCategoryRelation(
			array(
				'title' => 'category 1',
				'icon' => 'foo_test.gif',
			)
		);
		$this->addCategoryRelation(
			array(
				'title' => 'category 2',
				'icon' => 'foo_test2.gif',
			)
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$multipleCategoriesWithIcons = $this->fixture->main('', array());

		$this->testingFramework->deleteDummyFile('foo_test.gif');

		$this->assertContains(
			'category 1 <img src="',
			$multipleCategoriesWithIcons
		);

		$this->assertContains(
			'category 2 <img src="',
			$multipleCategoriesWithIcons
		);

	}

	public function testSingleViewForCategoryWithoutIconDoesNotShowCategoryIcon() {
		$this->addCategoryRelation(
			array('title' => 'category 1')
		);

		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertNotContains(
			'category 1 <img src="',
			$this->fixture->main('', array())
		);
	}


	//////////////////////////////////////////////////////////
	// Tests concerning the basic functions of the list view
	//////////////////////////////////////////////////////////

	public function testListViewShowsSingleEvents() {
		$this->assertContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewContainsEventDatesUsingTopicTitle() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'Test topic'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'title' => 'Test date'
			)
		);

		$result = $this->fixture->main('', array());
		$this->assertContains(
			'Test topic',
			$result
		);
		$this->assertNotContains(
			'Test date',
			$result
		);
	}

	public function testListViewHidesHiddenSingleEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'title' => 'Test single event',
				'hidden' => 1
			)
		);

		$this->assertNotContains(
			'Test single event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewHidesDeletedSingleEvents() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'title' => 'Test single event',
				'deleted' => 1
			)
		);

		$this->assertNotContains(
			'Test single event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewHidesHiddenEventDates() {
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'Test topic'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'title' => 'Test date',
				'hidden' => 1
			)
		);

		$this->assertNotContains(
			'Test topic',
			$this->fixture->main('', array())
		);
	}

	public function testListViewDisplaysSeminarImage() {
		$this->testingFramework->createDummyFile('test_foo.gif');

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('image' => 'test_foo.gif')
		);
		$listViewWithImage = $this->fixture->main('', array());
		$this->testingFramework->deleteDummyFile('test_foo.gif');

		$this->assertContains(
			'<img src="',
			$listViewWithImage
		);
	}

	public function testListViewForSeminarWithoutImageDoesNotDisplayImage() {
		$this->assertNotContains(
			'<img src="',
			$this->fixture->main('', array())
		);
	}

	public function testListViewForSeminarWithoutImageRemovesImageMarker() {
		$this->assertNotContains(
			'###IMAGE###',
			$this->fixture->main('', array())
		);
	}

	public function testListViewUsesTopicImage() {
		$this->testingFramework->createDummyFile('test_foo.gif');

		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'Test topic',
				'image' => 'test_foo.gif',
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid,
				'title' => 'Test date'
			)
		);

		$listViewWithImage = $this->fixture->main('', array());
		$this->testingFramework->deleteDummyFile('test_foo.gif');

		$this->assertRegExp(
			'/<img src=".*title="Test topic"/',
			$listViewWithImage
		);
	}


	/////////////////////////////////////////////////////////
	// Tests concerning the result counter in the list view
	/////////////////////////////////////////////////////////

	public function testResultCounterIsZeroForNoResults() {
		$this->fixture->setConfigurationValue(
			'pidList', $this->testingFramework->createSystemFolder()
		);
		$this->fixture->main('', array());

		$this->assertEquals(
			0,
			$this->fixture->internal['res_count']
		);
	}

	public function testResultCounterIsOneForOneResult() {
		$this->fixture->main('', array());

		$this->assertEquals(
			1,
			$this->fixture->internal['res_count']
		);
	}

	public function testResultCounterIsTwoForTwoResultsOnOnePage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Another event',
			)
		);
		$this->fixture->main('', array());

		$this->assertEquals(
			2,
			$this->fixture->internal['res_count']
		);
	}

	public function testResultCounterIsSixForSixResultsOnTwoPages() {
		for ($i = 0; $i < 5; $i++) {
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_SEMINARS,
				array(
					'pid' => $this->systemFolderPid,
					'title' => 'Another event',
				)
			);
		}
		$this->fixture->main('', array());

		$this->assertEquals(
			6,
			$this->fixture->internal['res_count']
		);
	}


	//////////////////////////////////////////////////////////
	// Tests concerning the list view, filtered by category.
	//////////////////////////////////////////////////////////

	public function testListViewContainsEventsWithoutCategoryByDefault() {
		$this->assertContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewContainsEventsWithCategoryByDefault() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid
		);

		$this->assertContains(
			'Event with category',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithCategoryExcludesEventsWithoutCategory() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->fixture->piVars['category'] = $categoryUid;

		$this->assertNotContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithCategoryContainsEventsWithSelectedCategory() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid
		);
		$this->fixture->piVars['category'] = $categoryUid;

		$this->assertContains(
			'Event with category',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithCategoryExcludesHiddenEventWithSelectedCategory() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				'hidden' => 1,
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid
		);
		$this->fixture->piVars['category'] = $categoryUid;

		$this->assertNotContains(
			'Event with category',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithCategoryExcludesDeletedEventWithSelectedCategory() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				'deleted' => 1,
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid
		);
		$this->fixture->piVars['category'] = $categoryUid;

		$this->assertNotContains(
			'Event with category',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithCategoryExcludesEventsWithNotSelectedCategory() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'another category')
		);
		$this->fixture->piVars['category'] = $categoryUid2;

		$this->assertNotContains(
			'Event with category',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithCategoryContainsEventsWithSelectedAndOtherCategory() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 2
			)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'another category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid2
		);
		$this->fixture->piVars['category'] = $categoryUid2;

		$this->assertContains(
			'Event with category',
			$this->fixture->main('', array())
		);
	}


	///////////////////////////////////////////////////////////
	// Tests concerning the list view, filtered by event type
	///////////////////////////////////////////////////////////

	public function testListViewContainsEventsWithoutEventTypeByDefault() {
		$this->assertContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewContainsEventsWithEventTypeByDefault() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'event_type' => $this->testingFramework->createRecord(
					SEMINARS_TABLE_EVENT_TYPES,
					array('title' => 'foo type')
				),
			)
		);

		$this->assertContains(
			'Event with type',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithEventTypeExcludesEventsWithoutEventType() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo type')
		);
		$this->fixture->piVars['event_type'] = array($eventTypeUid);

		$this->assertNotContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithEventTypeCanContainOneEventWithSelectedEventType() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'event_type' => $eventTypeUid,
			)
		);
		$this->fixture->piVars['event_type'] = array($eventTypeUid);

		$this->assertContains(
			'Event with type',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithEventTypeCanContainTwoEventsWithTwoDifferentSelectedEventTypes() {
		$eventTypeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type 1',
				'event_type' => $eventTypeUid1,
			)
		);
		$eventTypeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type 2',
				'event_type' => $eventTypeUid2,
			)
		);
		$this->fixture->piVars['event_type'] = array(
			$eventTypeUid1, $eventTypeUid2
		);

		$result = $this->fixture->main('', array());

		$this->assertContains(
			'Event with type 1',
			$result
		);
		$this->assertContains(
			'Event with type 2',
			$result
		);
	}

	public function testListViewWithEventTypeExcludesHiddenEventWithSelectedEventType() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'hidden' => 1,
				'event_type' => $eventTypeUid,
			)
		);
		$this->fixture->piVars['event_type'] = array($eventTypeUid);

		$this->assertNotContains(
			'Event with type',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithEventTypeExcludesDeletedEventWithSelectedEventType() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'deleted' => 1,
				'event_type' => $eventTypeUid,
			)
		);
		$this->fixture->piVars['event_type'] = array($eventTypeUid);

		$this->assertNotContains(
			'Event with type',
			$this->fixture->main('', array())
		);
	}

	public function testListViewWithEventTypeExcludesEventsWithNotSelectedEventType() {
		$eventTypeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'foo type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'event_type' => $eventTypeUid1,
			)
		);

		$eventTypeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'another eventType')
		);
		$this->fixture->piVars['event_type'] = array($eventTypeUid2);

		$this->assertNotContains(
			'Event with type',
			$this->fixture->main('', array())
		);
	}


	///////////////////////////////////////////////////
	// Tests concerning the sorting in the list view.
	///////////////////////////////////////////////////

	public function testListViewCanBeSortedByTitleAscending() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event B'
			)
		);

		$this->fixture->piVars['sort'] = 'title:0';
		$output = $this->fixture->main('', array());

		$this->assertTrue(
			strpos($output, 'Event A') < strpos($output, 'Event B')
		);
	}

	public function testListViewCanBeSortedByTitleDescending() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event B'
			)
		);

		$this->fixture->piVars['sort'] = 'title:1';
		$output = $this->fixture->main('', array());

		$this->assertTrue(
			strpos($output, 'Event B') < strpos($output, 'Event A')
		);
	}

	public function testListViewCanBeSortedByTitleAscendingWithinOneCategory() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event B'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid2, $categoryUid
		);

		$this->fixture->setConfigurationValue('sortListViewByCategory', 1);
		$this->fixture->piVars['sort'] = 'title:0';
		$output = $this->fixture->main('', array());

		$this->assertTrue(
			strpos($output, 'Event A') < strpos($output, 'Event B')
		);
	}

	public function testListViewCanBeSortedByTitleDescendingWithinOneCategory() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event B'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid2, $categoryUid
		);

		$this->fixture->setConfigurationValue('sortListViewByCategory', 1);
		$this->fixture->piVars['sort'] = 'title:1';
		$output = $this->fixture->main('', array());

		$this->assertTrue(
			strpos($output, 'Event B') < strpos($output, 'Event A')
		);
	}

	public function testListViewCategorySortingComesBeforeSortingByTitle() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'Category Y')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'Category X')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event B'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid2, $categoryUid2
		);

		$this->fixture->setConfigurationValue('sortListViewByCategory', 1);
		$this->fixture->piVars['sort'] = 'title:0';
		$output = $this->fixture->main('', array());

		$this->assertTrue(
			strpos($output, 'Event B') < strpos($output, 'Event A')
		);
	}

	public function testListViewCategorySortingHidesRepeatedCategoryNames() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'Category X')
		);

		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid1, $categoryUid
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event B'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid2, $categoryUid
		);

		$this->fixture->setConfigurationValue('sortListViewByCategory', 1);
		$this->fixture->piVars['sort'] = 'title:0';

		$this->assertEquals(
			1,
			mb_substr_count(
				$this->fixture->main('', array()),
				'Category X'
			)
		);
	}

	public function testListViewCategorySortingListsDifferentCategoryNames() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'Category Y')
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid1, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'Category X')
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				// the number of categories
				'categories' => 1,
				'title' => 'Event B'
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid2, $categoryUid2
		);

		$this->fixture->setConfigurationValue('sortListViewByCategory', 1);
		$this->fixture->piVars['sort'] = 'title:0';
		$output = $this->fixture->main('', array());

		$this->assertContains(
			'Category X',
			$output
		);
		$this->assertContains(
			'Category Y',
			$output
		);
	}


	////////////////////////////////////////////////////////////////////
	// Tests concerning the links to the single view in the list view.
	////////////////////////////////////////////////////////////////////

	public function testTeaserGetsLinkedToSingleView() {
		$this->fixture->setConfigurationValue('hideColumns', '');
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with teaser',
				'teaser' => 'Test Teaser',
			)
		);

		$this->assertRegExp(
			'/' . rawurlencode('tx_seminars_pi1[showUid]') . '=' .
				$eventUid . '" >Test Teaser<\/a>/',
			$this->fixture->main('', array())
		);
	}


	//////////////////////////////////////////////////////////
	// Tests concerning the category links in the list view.
	//////////////////////////////////////////////////////////

	public function testCategoryIsLinkedToTheFilteredListView() {
		$frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->fixture->setConfigurationValue('listPID', $frontEndPageUid);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid
		);

		$this->assertContains(
			'tx_seminars_pi1[category]='.$categoryUid,
			$this->fixture->main('', array())
		);
	}

	public function testCategoryIsNotLinkedFromSpecializedListView() {
		$frontEndPageUid = $this->testingFramework->createFrontEndPage();
		$this->fixture->setConfigurationValue('listPID', $frontEndPageUid);
		$this->fixture->setConfigurationValue('what_to_display', 'events_next_day');

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				'end_date' => ONE_WEEK,
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid
		);
		$this->fixture->createSeminar($eventUid);

		$this->assertNotContains(
			'tx_seminars_pi1[category]='.$categoryUid,
			$this->fixture->main('', array())
		);
	}


	///////////////////////////////////////////////
	// Tests concerning omitDateIfSameAsPrevious.
	///////////////////////////////////////////////

	public function testOmitDateIfSameAsPreviousOnDifferentDatesWithActiveConfig() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event title',
				'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
				'end_date' => mktime(18, 0, 0, 1, 1, 2020)
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event title',
				'begin_date' => mktime(10, 0, 0, 1, 1, 2021),
				'end_date' => mktime(18, 0, 0, 1, 1, 2021)
			)
		);

		$this->fixture->piVars['sort'] = 'date:0';
		$this->fixture->setConfigurationValue(
			'omitDateIfSameAsPrevious', 1
		);

		$output = $this->fixture->main('', array());
		$this->assertContains(
			'2020',
			$output
		);
		$this->assertContains(
			'2021',
			$output
		);
	}

	public function testOmitDateIfSameAsPreviousOnDifferentDatesWithInactiveConfig() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event title',
				'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
				'end_date' => mktime(18, 0, 0, 1, 1, 2020)
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event title',
				'begin_date' => mktime(10, 0, 0, 1, 1, 2021),
				'end_date' => mktime(18, 0, 0, 1, 1, 2021)
			)
		);

		$this->fixture->piVars['sort'] = 'date:0';
		$this->fixture->setConfigurationValue(
			'omitDateIfSameAsPrevious', 0
		);

		$output = $this->fixture->main('', array());
		$this->assertContains(
			'2020',
			$output
		);
		$this->assertContains(
			'2021',
			$output
		);
	}

	public function testOmitDateIfSameAsPreviousOnSameDatesWithActiveConfig() {
		$eventData = array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event title',
				'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
				'end_date' => mktime(18, 0, 0, 1, 1, 2020)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, $eventData
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, $eventData
		);

		$this->fixture->piVars['sort'] = 'date:0';
		$this->fixture->setConfigurationValue(
			'omitDateIfSameAsPrevious', 1
		);

		$this->assertEquals(
			1,
			mb_substr_count(
				$this->fixture->main('', array()),
				'2020'
			)
		);
	}

	public function testOmitDateIfSameAsPreviousOnSameDatesWithInactiveConfig() {
		$eventData = array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event title',
				'begin_date' => mktime(10, 0, 0, 1, 1, 2020),
				'end_date' => mktime(18, 0, 0, 1, 1, 2020)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, $eventData
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, $eventData
		);

		$this->fixture->piVars['sort'] = 'date:0';
		$this->fixture->setConfigurationValue(
			'omitDateIfSameAsPrevious', 0
		);

		$this->assertEquals(
			2,
			mb_substr_count(
				$this->fixture->main('', array()),
				'2020'
			)
		);
	}


	///////////////////////////////////////////////////////////
	// Tests concerning limiting the list view to event types
	///////////////////////////////////////////////////////////

	public function testListViewLimitedToEventTypesIgnoresEventsWithoutEventType() {
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'an event type')
		);
		$this->fixture->setConfigurationValue(
			'limitListViewToEventTypes', $eventTypeUid
		);

		$this->assertNotContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewLimitedToEventTypesContainsEventsWithMultipleSelectedEventTypes() {
		$eventTypeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'an event type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'event_type' => $eventTypeUid1,
			)
		);

		$eventTypeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'an event type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with another type',
				'event_type' => $eventTypeUid2,
			)
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToEventTypes', $eventTypeUid1 . ',' . $eventTypeUid2
		);

		$result = $this->fixture->main('', array());
		$this->assertContains(
			'Event with type',
			$result
		);
		$this->assertContains(
			'Event with another type',
			$result
		);
	}

	public function testListViewLimitedToEventTypesIgnoresEventsWithNotSelectedEventType() {
		$eventTypeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'an event type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'event_type' => $eventTypeUid1,
			)
		);

		$eventTypeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'another eventType')
		);
		$this->fixture->setConfigurationValue(
			'limitListViewToEventTypes', $eventTypeUid2
		);

		$this->assertNotContains(
			'Event with type',
			$this->fixture->main('', array())
		);
	}

	public function testListViewForSingleEventTypeOverridesLimitToEventTypes() {
		$eventTypeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'an event type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with type',
				'event_type' => $eventTypeUid1,
			)
		);

		$eventTypeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES,
			array('title' => 'an event type')
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with another type',
				'event_type' => $eventTypeUid2,
			)
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToEventTypes', $eventTypeUid1
		);
		$this->fixture->piVars['event_type'] = array($eventTypeUid2);

		$result = $this->fixture->main('', array());
		$this->assertNotContains(
			'Event with type',
			$result
		);
		$this->assertContains(
			'Event with another type',
			$result
		);
	}


	//////////////////////////////////////////////////////////
	// Tests concerning limiting the list view to categories
	//////////////////////////////////////////////////////////

	public function testListViewLimitedToCategoriesIgnoresEventsWithoutCategory() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->fixture->setConfigurationValue(
			'limitListViewToCategories', $categoryUid
		);

		$this->assertNotContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewLimitedToCategoriesContainsEventsWithMultipleSelectedCategories() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with another category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid2, $categoryUid2
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToCategories', $categoryUid1 . ',' . $categoryUid2
		);

		$result = $this->fixture->main('', array());
		$this->assertContains(
			'Event with category',
			$result
		);
		$this->assertContains(
			'Event with another category',
			$result
		);
	}

	public function testListViewLimitedToCategoriesIgnoresEventsWithNotSelectedCategory() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'another category')
		);
		$this->fixture->setConfigurationValue(
			'limitListViewToCategories', $categoryUid2
		);

		$this->assertNotContains(
			'Event with category',
			$this->fixture->main('', array())
		);
	}

	public function testListViewForSingleCategoryOverridesLimitToCategories() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid1, $categoryUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with another category',
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_CATEGORIES_MM,
			$eventUid2, $categoryUid2
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToCategories', $categoryUid1
		);
		$this->fixture->piVars['category'] = $categoryUid2;

		$result = $this->fixture->main('', array());
		$this->assertNotContains(
			'Event with category',
			$result
		);
		$this->assertContains(
			'Event with another category',
			$result
		);
	}


	//////////////////////////////////////////////////////
	// Tests concerning limiting the list view to places
	//////////////////////////////////////////////////////

	public function testListViewLimitedToPlacesExcludesEventsWithoutPlace() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'a place')
		);
		$this->fixture->setConfigurationValue(
			'limitListViewToPlaces', $placeUid
		);

		$this->assertNotContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testListViewLimitedToPlacesContainsEventsWithMultipleSelectedPlaces() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with place',
				// the number of places
				'place' => 1
			)
		);
		$placeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM,
			$eventUid1, $placeUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with another place',
				// the number of places
				'place' => 1
			)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM,
			$eventUid2, $placeUid2
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToPlaces', $placeUid1 . ',' . $placeUid2
		);

		$result = $this->fixture->main('', array());
		$this->assertContains(
			'Event with place',
			$result
		);
		$this->assertContains(
			'Event with another place',
			$result
		);
	}

	public function testListViewLimitedToPlacesExcludesEventsWithNotSelectedPlace() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with place',
				// the number of places
				'place' => 1
			)
		);
		$placeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM,
			$eventUid, $placeUid1
		);

		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'another place')
		);
		$this->fixture->setConfigurationValue(
			'limitListViewToPlaces', $placeUid2
		);

		$this->assertNotContains(
			'Event with place',
			$this->fixture->main('', array())
		);
	}

	public function testListViewLimitedToPlacesExcludesHiddenEventWithSelectedPlace() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with place',
				'hidden' => 1,
				// the number of places
				'place' => 1
			)
		);
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM,
			$eventUid, $placeUid
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToPlaces', $placeUid
		);

		$result = $this->fixture->main('', array());
		$this->assertNotContains(
			'Event with place',
			$result
		);
	}

	public function testListViewLimitedToPlacesExcludesDeletedEventWithSelectedPlace() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with place',
				'deleted' => 1,
				// the number of places
				'place' => 1
			)
		);
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM,
			$eventUid, $placeUid
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToPlaces', $placeUid
		);

		$result = $this->fixture->main('', array());
		$this->assertNotContains(
			'Event with place',
			$result
		);
	}

	public function testListViewLimitedToPlacesFromSelectorWidgetIgnoresFlexFormsValues() {
		// TODO: This needs to be changed when bug 2304 gets fixed.
		// @see https://bugs.oliverklee.com/show_bug.cgi?id=2304
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with place',
				// the number of places
				'place' => 1
			)
		);
		$placeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM, $eventUid1, $placeUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event with another place',
				// the number of places
				'place' => 1
			)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => 'a place')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM, $eventUid2, $placeUid2
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToPlaces', $placeUid1
		);
		$this->fixture->piVars['place'] = array($placeUid2);

		$result = $this->fixture->main('', array());
		$this->assertNotContains(
			'Event with place',
			$result
		);
		$this->assertContains(
			'Event with another place',
			$result
		);
	}


	//////////////////////////////////////////
	// Tests concerning the "my events" view
	//////////////////////////////////////////

	public function testMyEventsContainsTitleOfEventWithRegistrationForLoggedInUser() {
		$this->createLogInAndRegisterFeUser();
		$this->fixture->setConfigurationValue('what_to_display', 'my_events');

		$this->assertContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}

	public function testMyEventsNotContainsTitleOfEventWithoutRegistrationForLoggedInUser() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setConfigurationValue('what_to_display', 'my_events');

		$this->assertNotContains(
			'Test event',
			$this->fixture->main('', array())
		);
	}


	/////////////////////////////////////////////////////////////////////////////////
	// Tests concerning mayManagersEditTheirEvents in the "my vip events" list view
	/////////////////////////////////////////////////////////////////////////////////

	public function testEditSubpartWithMayManagersEditTheirEventsSetToFalseIsHiddenInMyVipEventsListView() {
		$this->createLogInAndAddFeUserAsVip();
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents', 0);
		$this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

		$this->fixture->main('', array());
		$this->assertFalse(
			$this->fixture->isSubpartVisible('LISTITEM_WRAPPER_EDIT')
		);
	}

	public function testEditSubpartWithMayManagersEditTheirEventsSetToTrueIsVisibleInMyVipEventsListView() {
		$this->createLogInAndAddFeUserAsVip();
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents', 1);
		$this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('LISTITEM_WRAPPER_EDIT')
		);
	}

	public function testManagedEventsViewWithMayManagersEditTheirEventsSetToTrueContainsEditLink() {
		$this->createLogInAndAddFeUserAsVip();
		$this->fixture->setConfigurationValue('mayManagersEditTheirEvents', 1);
		$this->fixture->setConfigurationValue(
			'eventEditorPID', $this->testingFramework->createFrontEndPage()
		);
		$this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

		$this->assertContains(
			'tx_seminars_pi1[action]=EDIT',
			$this->fixture->main('', array())
		);
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////
	// Tests concerning allowCsvExportOfRegistrationsInMyVipEventsView in the "my vip events" list view
	/////////////////////////////////////////////////////////////////////////////////////////////////////

	public function testRegistrationsSubpartWithAllowCsvExportOfRegistrationsInMyVipEventsViewSetToFalseIsHiddenInMyVipEventsListView() {
		$this->createLogInAndAddFeUserAsVip();

		$this->fixture->main(
			'',
			array(
				'allowCsvExportOfRegistrationsInMyVipEventsView' => 0,
				'what_to_display' => 'my_vip_events',
			)
		);
		$this->assertFalse(
			$this->fixture->isSubpartVisible('LISTITEM_WRAPPER_REGISTRATIONS')
		);
	}

	public function testRegistrationsSubpartWithAllowCsvExportOfRegistrationsInMyVipEventsViewSetToTrueIsVisibleInMyVipEventsListView() {
		$this->createLogInAndAddFeUserAsVip();
		$this->fixture->setConfigurationValue(
			'allowCsvExportOfRegistrationsInMyVipEventsView', 1
		);
		$this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

		$this->fixture->main('', array());
		$this->assertTrue(
			$this->fixture->isSubpartVisible('LISTITEM_WRAPPER_REGISTRATIONS')
		);
	}


	/////////////////////////////////////////////////////////////////
	// Tests concerning the category list in the my vip events view
	/////////////////////////////////////////////////////////////////

	public function testMyVipEventsViewShowsCategoryTitleOfEvent() {
		$this->createLogInAndAddFeUserAsVip();
		$this->fixture->setConfigurationValue('what_to_display', 'my_vip_events');

		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'category_foo')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid, $categoryUid, 'categories'
		);

		$this->assertContains(
			'category_foo',
			$this->fixture->main('', array())
		);
	}


	////////////////////////////////////
	// Tests concerning getFieldHeader
	////////////////////////////////////

	public function testGetFieldHeaderContainsLabelOfKey() {
		$this->assertContains(
			$this->fixture->translate('label_date'),
			$this->fixture->getFieldHeader('date')
		);
	}

	public function testGetFieldHeaderForSortableFieldContainsLink() {
		$this->assertContains(
			'<a',
			$this->fixture->getFieldHeader('date')
		);
	}

	public function testGetFieldHeaderForNonSortableFieldNotContainsLink() {
		$this->assertNotContains(
			'<a',
			$this->fixture->getFieldHeader('register')
		);
	}


	////////////////////////////////////////////////
	// Tests concerning the getLoginLink function.
	////////////////////////////////////////////////

	public function testGetLoginLinkWithLoggedOutUserAddsUidPiVarToUrl() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'foo',
			)
		);
		$this->testingFramework->logoutFrontEndUser();

		$this->fixture->setConfigurationValue(
			'loginPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertContains(
			rawurlencode('tx_seminars_pi1[uid]'). '=' . $eventUid,
			$this->fixture->getLoginLink(
				'foo',
				$this->testingFramework->createFrontEndPage(),
				$eventUid
			)
		);
	}


	//////////////////////////////////////////////////////
	// Tests concerning the pagination of the list view.
	//////////////////////////////////////////////////////

	public function testListViewCanContainOneItemOnTheFirstPage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event A'
			)
		);

		$this->assertContains(
			'Event A',
			$this->fixture->main('', array())
		);
	}

	public function testListViewCanContainTwoItemsOnTheFirstPage() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event B'
			)
		);

		$output = $this->fixture->main('', array());
		$this->assertContains(
			'Event A',
			$output
		);
		$this->assertContains(
			'Event B',
			$output
		);
	}

	public function testFirstPageOfListViewNotContainsItemForTheSecondPage() {
		$this->fixture->setConfigurationValue(
			'listView.', array(
				'orderBy' => 'title',
				'descFlag' => 0,
				'results_at_a_time' => 1,
				'maxPages' => 5,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event B'
			)
		);

		$this->assertNotContains(
			'Event B',
			$this->fixture->main('', array())
		);
	}

	public function testSecondPageOfListViewContainsItemForTheSecondPage() {
		$this->fixture->setConfigurationValue(
			'listView.', array(
				'orderBy' => 'title',
				'descFlag' => 0,
				'results_at_a_time' => 1,
				'maxPages' => 5,
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event A'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Event B'
			)
		);

		$this->fixture->piVars['pointer'] = 1;
		$this->assertContains(
			'Event B',
			$this->fixture->main('', array())
		);
	}


	///////////////////////////////////////////////////////
	// Tests concerning the owner data in the single view
	///////////////////////////////////////////////////////

	public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledContainsOwnerDataHeading() {
		$ownerUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 1
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertContains(
			$this->fixture->translate('label_owner'),
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledNotContainsEmptyLines() {
		$ownerUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 1
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertNotRegexp(
			'/(<p>|<br \/>)\s*<br \/>\s*(<br \/>|<\/p>)/m',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithoutOwnerAndOwnerDataEnabledNotContainsOwnerDataHeading() {
		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 1
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertNotContains(
			$this->fixture->translate('label_owner'),
			$this->fixture->main('', array())
		);

	}

	public function testSingleViewForSeminarWithOwnerAndOwnerDataDisabledNotContainsOwnerDataHeading() {
		$ownerUid = $this->testingFramework->createFrontEndUser();
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 0
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertNotContains(
			$this->fixture->translate('label_owner'),
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledContainsOwnerName() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'John Doe')
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 1
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertContains(
			'John Doe',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOwnerHtmlSpecialCharsOwnerName() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'Tom & Jerry')
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 1
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertContains(
			'Tom &amp; Jerry',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOwnerAndOwnerDataDisabledNotContainsOwnerName() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('name' => 'Jon Doe')
		);

		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 0
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertNotContains(
			'Jon Doe',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledCanContainOwnerPhone() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('telephone' => '0123 4567')
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 1
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertContains(
			'0123 4567',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledCanContainOwnerEMailAddress() {
		$ownerUid = $this->testingFramework->createFrontEndUser(
			'', array('email' => 'foo@bar.com')
		);
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS,
			$this->seminarUid,
			array('owner_feuser' => $ownerUid)
		);

		$this->fixture->setConfigurationValue(
			'showOwnerDataInSingleView', 1
		);
		$this->fixture->piVars['showUid'] = $this->seminarUid;

		$this->assertContains(
			'foo@bar.com',
			$this->fixture->main('', array())
		);
	}

	public function testSingleViewForSeminarWithOwnerAndOwnerDataEnabledCanContainImageTagForOwnerPicture() {
		$this->markTestIncomplete(
			'Currently, FE image functions cannot be unit-tested yet.'
		);
	}

	public function testSingleViewForSeminarWithOwnerWithoutImageAndOwnerDataEnabledNotContainsImageTag() {
		$this->markTestIncomplete(
			'Currently, FE image functions cannot be unit-tested yet.'
		);
	}


	///////////////////////////////////////////
	// Tests concerning the registration form
	///////////////////////////////////////////

	public function testRegistrationFormForEventWithOneNotFullfilledRequirementIsHidden() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');

		$requiredTopic = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$topic = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$date = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
				'attendees_max' => 10,
				'topic' => $topic,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topic, $requiredTopic, 'requirements'
		);
		$this->fixture->piVars['seminar'] = $date;

		$this->assertNotContains(
			$this->fixture->translate('label_your_user_data_formal'),
			$this->fixture->main('', array())
		);
	}

	public function testListOfRequirementsForEventWithOneNotFulfilledRequirementListIsShown() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');

		$requiredTopic = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$topic = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$date = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
				'attendees_max' => 10,
				'topic' => $topic,
			)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topic, $requiredTopic, 'requirements'
		);
		$this->fixture->piVars['seminar'] = $date;
		$this->fixture->main('', array());

		$this->assertTrue(
			$this->fixture->isSubpartVisible('FIELD_WRAPPER_REQUIREMENTS')
		);
	}

	public function testListOfRequirementsForEventWithOneNotFulfilledRequirementLinksTitleOfRequirement() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');
		$this->fixture->setConfigurationValue(
			'detailPID',
			$this->testingFramework->createFrontEndPage()
		);

		$topic = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$date = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
				'attendees_max' => 10,
				'topic' => $topic,
			)
		);

		$requiredTopic = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'required_foo',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topic, $requiredTopic, 'requirements'
		);
		$this->fixture->piVars['seminar'] = $date;

		$this->assertRegExp(
			'/<a href=.*' . $requiredTopic . '.*>required_foo<\/a>/',
			$this->fixture->main('', array())
		);
	}

	public function testListOfRequirementsForEventWithTwoNotFulfilledRequirementsShownsTitlesOfBothRequirements() {
		$this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setConfigurationValue('what_to_display', 'seminar_registration');

		$topic = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('object_type' => SEMINARS_RECORD_TYPE_TOPIC)
		);
		$date = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
				'attendees_max' => 10,
				'topic' => $topic,
			)
		);

		$requiredTopic1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'required_foo',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topic, $requiredTopic1, 'requirements'
		);
		$requiredTopic2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'title' => 'required_bar',
			)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS,
			$topic, $requiredTopic2, 'requirements'
		);

		$this->fixture->piVars['seminar'] = $date;

		$this->assertRegExp(
			'/required_foo.*required_bar/s',
			$this->fixture->main('', array())
		);
	}
}
?>