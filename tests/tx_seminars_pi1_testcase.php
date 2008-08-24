<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(PATH_tslib . 'class.tslib_content.php');
require_once(PATH_tslib . 'class.tslib_feuserauth.php');
require_once(PATH_t3lib . 'class.t3lib_timetrack.php');

require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_registrationmanager.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the pi1 class in the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_pi1_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** the UID of a seminar to which the fixture relates */
	private $seminarUid;

	/** PID of a dummy system folder */
	private $systemFolderPid = 0;

	/** the number of target groups for the current event record */
	private $numberOfTargetGroups = 0;

	public function setUp() {
		// Bolsters up the fake front end.
		$GLOBALS['TSFE']->tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
		$GLOBALS['TSFE']->tmpl->flattenSetup(array(), '', false);
		$GLOBALS['TSFE']->tmpl->init();
		$GLOBALS['TSFE']->tmpl->getCurrentPageData();

		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->systemFolderPid = $this->testingFramework->createSystemFolder();
		$this->seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Test event'
			)
		);

		$this->fixture = new tx_seminars_pi1();
		$this->fixture->fakeFrontend();

		$this->fixture->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
				'what_to_display' => 'seminar_list',
				'eventType' => 'Workshop',
				'pidList' => $this->systemFolderPid,
				'pages' => $this->systemFolderPid,
				'recursive' => 1
			)
		);
		$this->fixture->getTemplateCode();
		$this->fixture->setLabels();
		$this->fixture->createHelperObjects();
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'eventType', 'Workshop'
		);
		$this->fixture->getConfigGetter()->setConfigurationValue(
			'dateFormatYMD', '%d.%m.%Y'
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Initializes '$GLOBALS['TSFE']->sys_page', '$GLOBALS['TT']' and
	 * '$this->cObj' as these objects are needed but only initialized
	 * automatically if TYPO3_MODE is 'FE'.
	 * This will allow the FE templating functions to be used even without the
	 * FE.
	 */
	 private function fakeFrontend() {
	 	if (!is_object($GLOBALS['TT'])) {
	 		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');
	 	}

	 	if (!is_object($GLOBALS['TSFE']->sys_page)) {
	 		$GLOBALS['TSFE']->sys_page
	 			= t3lib_div::makeInstance('t3lib_pageSelect');
	 	}

		if (!is_object($this->fixture->cObj)) {
			$this->fixture->cObj = t3lib_div::makeInstance('tslib_cObj');
			$this->fixture->cObj->start('');
		}
	 }

	/**
	 * Inserts a target group record into the database and creates a relation to
	 * it from the event with the UID store in $this->seminarUid.
	 *
	 * @param	array		data of the target group to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will be > 0
	 */
	private function addTargetGroupRelation(array $targetGroupData = array()) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TARGET_GROUPS, $targetGroupData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TARGET_GROUPS_MM,
			$this->seminarUid, $uid
		);

		$this->numberOfTargetGroups++;
		$this->testingFramework->changeRecord(
			SEMINARS_TABLE_SEMINARS, $this->seminarUid,
			array('target_groups' => $this->numberOfTargetGroups)
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
				SEMINARS_TABLE_TARGET_GROUPS_MM,
				'uid_local='.$this->seminarUid
			)

		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TARGET_GROUPS_MM,
				'uid_local='.$this->seminarUid
			)
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TARGET_GROUPS_MM,
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

	public function testEventTypeInConfigurationIsSet() {
		$this->assertEquals(
			'Workshop',
			$this->fixture->getConfigGetter()->getConfValueString('eventType')
		);
	}


	/////////////////////////////////////////
	// Test concerning the search function.
	/////////////////////////////////////////

	public function testSearchWhereCreatesAnEmptyStringForEmptySearchWords() {
		$this->assertEquals(
			'', $this->fixture->searchWhere('')
		);
	}

	public function testSearchWhereCreatesSomethingForNonEmptySearchWords() {
		$this->assertNotEquals(
			'', $this->fixture->searchWhere('foo')
		);
		$this->assertContains(
			'foo', $this->fixture->searchWhere('foo')
		);
	}

	public function testSearchWhereCreatesAndOnlyAtTheStartForOneSearchWord() {
		$this->assertTrue(
			strpos($this->fixture->searchWhere('foo'), ' AND ') === 0
		);
	}

	public function testSearchWhereForDefaultEventTypeContainsEventType0() {
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('Workshop')
		);
	}

	public function testSearchWhereForPartOfDefaultEventTypeContainsEventType0() {
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('Work')
		);
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('ork')
		);
		$this->assertContains(
			'.event_type=0', $this->fixture->searchWhere('hop')
		);
	}

	public function testSearchWhereForDifferenteFromEventTypeNotContainsEventType0() {
		$this->assertNotContains(
			'.event_type=0', $this->fixture->searchWhere('Seminar')
		);
	}

	public function testAddEmptyOptionIfNeededDisabled() {
		$allLanguages = array(
			'DE' => 'Deutsch',
			'FR' => 'French'
		);
		$this->fixture->addEmptyOptionIfNeeded($allLanguages);
		$this->assertEquals(
			array(
				'DE' => 'Deutsch',
				'FR' => 'French'
			),
			$allLanguages
		);
	}

	public function testAddEmptyOptionIfNeededActivated() {
		$this->fixture->setConfigurationValue(
			'showEmptyEntryInOptionLists', '1'
		);

		$allOptions = array(
			'DE' => 'Deutsch',
			'FR' => 'French'
		);
		$this->fixture->addEmptyOptionIfNeeded($allOptions);
		$this->assertEquals(
			array(
				'none' => $this->fixture->translate('label_selector_pleaseChoose'),
				'DE' => 'Deutsch',
				'FR' => 'French'
			),
			$allOptions
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
			SEMINARS_TABLE_SITES_MM, $eventUid, $placeUid
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
			$eventUid, $categoryUid1
		);

		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'another category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM,
			$eventUid, $categoryUid2
		);
		$this->fixture->piVars['category'] = $categoryUid2;

		$this->assertContains(
			'Event with category',
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
			$eventUid2, $categoryUid
		);

		$this->fixture->setConfigurationValue('sortListViewByCategory', 1);
		$this->fixture->piVars['sort'] = 'title:0';

		$this->assertEquals(
			1,
			substr_count(
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
			SEMINARS_TABLE_CATEGORIES_MM,
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
				// the number of categories
				'categories' => 1
			)
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'a category')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM,
			$eventUid, $categoryUid
		);

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
			substr_count(
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
			substr_count(
				$this->fixture->main('', array()),
				'2020'
			)
		);
	}


	/////////////////////////////////
	// Tests for the category list.
	/////////////////////////////////

	public function testCategoryListCanBeEmpty() {
		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$this->fixture->setConfigurationValue('pages', $otherSystemFolderUid);

		$output = $this->fixture->createCategoryList();

		$this->assertNotContains(
			'<table',
			$output
		);
		$this->assertContains(
			$this->fixture->translate('label_no_categories'),
			$output
		);
	}

	public function testCategoryListCanContainOneCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCategoryListCanContainTwoCategoryTitles() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'first category')
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'second category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 2
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid2
		);

		$output = $this->fixture->createCategoryList();
		$this->assertContains(
			'first category',
			$output
		);
		$this->assertContains(
			'second category',
			$output
		);
	}

	public function testCategoryListIsSortedAlphabetically() {
		$categoryUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'category B')
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'category A')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 2
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid1
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid2
		);

		$output = $this->fixture->createCategoryList();
		$this->assertTrue(
			strpos($output, 'category A') < strpos($output, 'category B')
		);
	}

	public function testCategoryListUsesRecursion() {
		$systemSubFolderUid = $this->testingFramework->createSystemFolder(
			$this->systemFolderPid
		);
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $systemSubFolderUid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCategoryListIgnoresOtherSysFolders() {
		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $otherSystemFolderUid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCategoryListCanReadFromAllSystemFolders() {
		$this->fixture->setConfigurationValue('pages', '');

		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $otherSystemFolderUid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCategoryListIgnoresCanceledEvents() {
		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1,
				'cancelled' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCategoryUsesEventsFromSelectedTimeFrames() {
		$this->fixture->setConfigurationValue(
			'timeframeInList', 'currentAndUpcoming'
		);

		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'end_date' => mktime() + 2000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCategoryIgnoresEventsFromDeselectedTimeFrames() {
		$this->fixture->setConfigurationValue(
			'timeframeInList', 'currentAndUpcoming'
		);

		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() - 2000,
				'end_date' => mktime() - 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->createCategoryList()
		);
	}

	public function testCategoryListContainsLinksToListPageLimitedToCategory() {
		$this->fixture->setConfigurationValue(
			'listPID', $this->testingFramework->createFrontEndPage()
		);

		$categoryUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_CATEGORIES,
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => mktime() + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_CATEGORIES_MM, $eventUid, $categoryUid
		);

		$this->assertContains(
			'tx_seminars_pi1[category]='.$categoryUid,
			$this->fixture->createCategoryList()
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
			SEMINARS_TABLE_SITES_MM,
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
			SEMINARS_TABLE_SITES_MM,
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
}
?>