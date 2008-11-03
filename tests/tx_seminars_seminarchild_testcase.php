<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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
require_once(t3lib_extMgm::extPath('seminars') . 'pi1/class.tx_seminars_pi1.php');
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_seminarchild.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the seminar class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_seminarchild_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_seminarchild
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer
	 */
	private $beginDate;
	/**
	 * @var integer
	 */
	private $unregistrationDeadline;
	/**
	 * @var integer
	 */
	private $currentTimestamp;

	/**
	 * @var tx_seminars_pi1
	 */
	private $pi1;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->currentTimestamp = time();
		$this->beginDate = ($this->currentTimestamp + ONE_WEEK);
		$this->unregistrationDeadline = ($this->currentTimestamp + ONE_WEEK);

		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'deadline_unregistration' => $this->unregistrationDeadline,
				'attendees_min' => 5,
				'attendees_max' => 10,
				'object_type' => 0,
				'queue_size' => 0,
			)
		);

		$this->fixture = new tx_seminars_seminarchild(
			$uid,
			array(
				'dateFormatYMD' => '%d.%m.%Y',
				'timeFormat' => '%H:%M',
				'showTimeOfUnregistrationDeadline' => 0,
				'unregistrationDeadlineDaysBeforeBeginDate' => 0,
			)
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		if ($this->pi1) {
			$this->pi1->__destruct();
		}
		$this->fixture->__destruct();
		unset($this->fixture, $this->pi1, $this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates a fake front end and a pi1 instance in $this->pi1.
	 *
	 * @param integer UID of the detail view page
	 */
	private function createPi1($detailPageUid = 0) {
		$this->testingFramework->createFakeFrontEnd();

		$this->pi1 = new tx_seminars_pi1();
		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
				'detailPID' => $detailPageUid,
			)
		);
		$this->pi1->getTemplateCode();
	}

	/**
	 * Inserts a place record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array data of the place to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addPlaceRelation(array $placeData = array()) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, $placeData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfPlaces(
			$this->fixture->getNumberOfPlaces() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a target group record into the database and creates a relation to
	 * it from the fixture.
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
			SEMINARS_TABLE_TARGET_GROUPS_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfTargetGroups(
			$this->fixture->getNumberOfTargetGroups() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a payment method record into the database and creates a relation
	 * to it from the fixture.
	 *
	 * @param array data of the payment method to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addPaymentMethodRelation(
		array $paymentMethodData = array()
	) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_PAYMENT_METHODS, $paymentMethodData
		);

		$this->fixture->addPaymentMethod($uid);

		return $uid;
	}

	/**
	 * Inserts an organizer record into the database and creates a relation to
	 * it from the fixture as a organizing partner.
	 *
	 * @param array data of the organizer to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addOrganizingPartnerRelation(
		array $organizerData = array()
	) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS, $organizerData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_ORGANIZING_PARTNERS_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfOrganizingPartners(
			$this->fixture->getNumberOfOrganizingPartners() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a category record into the database and creates a relation to it
	 * from the fixture.
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
			SEMINARS_TABLE_CATEGORIES_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfCategories(
			$this->fixture->getNumberOfCategories() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a organizer record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array data of the organizer to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addOrganizerRelation(array $organizerData = array()) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS, $organizerData
		);

		$this->fixture->addOrganizer($uid);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addSpeakerRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS, $speakerData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SPEAKERS_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfSpeakers(
			$this->fixture->getNumberOfSpeakers() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture as partner.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addPartnerRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS, $speakerData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_PARTNERS_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfPartners(
			$this->fixture->getNumberOfPartners() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture as tutor.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addTutorRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS, $speakerData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_TUTORS_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfTutors(
			$this->fixture->getNumberOfTutors() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture as leader.
	 *
	 * @param array data of the speaker to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addLeaderRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS, $speakerData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_LEADERS_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfLeaders(
			$this->fixture->getNumberOfLeaders() + 1
		);

		return $uid;
	}

	/**
	 * Inserts an event type record into the database and creates a relation to
	 * it from the fixture.
	 *
	 * @param array data of the event type to add, may be empty
	 *
	 * @return integer the UID of the created record, will be > 0
	 */
	private function addEventTypeRelation($eventTypeData) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES, $eventTypeData
		);

		$this->fixture->setEventType($uid);

		return $uid;
	}


	////////////////////////////////////
	// Tests for the utility functions
	////////////////////////////////////

	public function testCreatePi1CreatesFakeFrontEnd() {
		$GLOBALS['TSFE'] = null;

		$this->createPi1();

		$this->assertNotNull(
			$GLOBALS['TSFE']
		);
	}

	public function testCreatePi1CreatesPi1Instance() {
		$this->pi1 = null;

		$this->createPi1();

		$this->assertTrue(
			$this->pi1 instanceof tx_seminars_pi1
		);
	}

	public function testAddPlaceRelationReturnsUid() {
		$uid = $this->addPlaceRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddPlaceRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addPlaceRelation(array()),
			$this->addPlaceRelation(array())
		);
	}

	public function testAddPlaceRelationIncreasesTheNumberOfPlaces() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfPlaces()
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfPlaces()
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfPlaces()
		);
	}

	public function testAddPlaceRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SITES_MM, 'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SITES_MM, 'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addPlaceRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SITES_MM, 'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddCategoryRelationReturnsUid() {
		$uid = $this->addCategoryRelation(array());

		$this->assertTrue(
			$uid > 0
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
			$this->fixture->getNumberOfCategories()
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfCategories()
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfCategories()
		);
	}

	public function testAddCategoryRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_CATEGORIES_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_CATEGORIES_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addCategoryRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_CATEGORIES_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

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
			$this->fixture->getNumberOfTargetGroups()
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfTargetGroups()
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfTargetGroups()
		);
	}

	public function testAddTargetGroupRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TARGET_GROUPS_MM,
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TARGET_GROUPS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addTargetGroupRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TARGET_GROUPS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddPaymentMethodRelationReturnsUid() {
		$uid = $this->addPaymentMethodRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddPaymentMethodRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addPaymentMethodRelation(array()),
			$this->addPaymentMethodRelation(array())
		);
	}

	public function testAddPaymentMethodRelationIncreasesTheNumberOfPaymentMethods() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfPaymentMethods()
		);

		$this->addPaymentMethodRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfPaymentMethods()
		);

		$this->addPaymentMethodRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfPaymentMethods()
		);
	}

	public function testAddOrganizingPartnerRelationReturnsUid() {
		$uid = $this->addOrganizingPartnerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddOrganizingPartnerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addOrganizingPartnerRelation(array()),
			$this->addOrganizingPartnerRelation(array())
		);
	}

	public function testAddOrganizingPartnerRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_ORGANIZING_PARTNERS_MM,
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_ORGANIZING_PARTNERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_ORGANIZING_PARTNERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddOrganizerRelationReturnsUid() {
		$uid = $this->addOrganizerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddOrganizerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addOrganizerRelation(array()),
			$this->addOrganizerRelation(array())
		);
	}

	public function testAddOrganizerRelationIncreasesTheNumberOfOrganizers() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfOrganizers()
		);

		$this->addOrganizerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfOrganizers()
		);

		$this->addOrganizerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	public function testAddSpeakerRelationReturnsUid() {
		$uid = $this->addSpeakerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddSpeakerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addSpeakerRelation(array()),
			$this->addSpeakerRelation(array())
		);
	}

	public function testAddSpeakerRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SPEAKERS_MM,
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addSpeakerRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SPEAKERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addSpeakerRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SPEAKERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddPartnerRelationReturnsUid() {
		$uid = $this->addPartnerRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddPartnerRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addPartnerRelation(array()),
			$this->addPartnerRelation(array())
		);
	}

	public function testAddPartnerRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_PARTNERS_MM,
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_PARTNERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_PARTNERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddTutorRelationReturnsUid() {
		$uid = $this->addTutorRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddTutorRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addTutorRelation(array()),
			$this->addTutorRelation(array())
		);
	}

	public function testAddTutorRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TUTORS_MM,
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addTutorRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TUTORS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addTutorRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_TUTORS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddLeaderRelationReturnsUid() {
		$uid = $this->addLeaderRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddLeaderRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addLeaderRelation(array()),
			$this->addLeaderRelation(array())
		);
	}

	public function testAddLeaderRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_LEADERS_MM,
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addLeaderRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_LEADERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addLeaderRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_LEADERS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	public function testAddEventTypeRelationReturnsUid() {
		$uid = $this->addEventTypeRelation(array());

		$this->assertTrue(
			$uid > 0
		);
	}

	public function testAddEventTypeRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addLeaderRelation(array()),
			$this->addLeaderRelation(array())
		);
	}


	///////////////////////////////////////
	// Tests for some basic functionality
	///////////////////////////////////////

	public function testIsOk() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testGetEventData() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(0);
		$this->assertEquals(
			'01.01.2030',
			$this->fixture->getEventData('deadline_unregistration')
		);

		$this->fixture->setShowTimeOfUnregistrationDeadline(1);
		$this->assertEquals(
			'01.01.2030 10:00',
			$this->fixture->getEventData('deadline_unregistration')
		);

		$this->fixture->setUnregistrationDeadline(0);
		$this->assertEquals(
			'',
			$this->fixture->getEventData('deadline_unregistration')
		);
	}


	/////////////////////////////////////////////////////////
	// Tests regarding the ability to register for an event
	/////////////////////////////////////////////////////////

	public function testCanSomebodyRegisterIsTrueForEventWithFutureDate() {
		$this->fixture->setBeginDate(time() + 3600);
		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsTrueForEventWithFutureDateAndRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate(time() + 3600);
		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForPastEvent() {
		$this->fixture->setBeginDate(time() - 7200);
		$this->fixture->setEndDate(time() - 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForPastEventWithRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate(time() - 7200);
		$this->fixture->setEndDate(time() - 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForCurrentlyRunningEvent() {
		$this->fixture->setBeginDate(time() - 3600);
		$this->fixture->setEndDate(time() + 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsFalseForCurrentlyRunningEventWithRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate(time() - 3600);
		$this->fixture->setEndDate(time() + 3600);
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}


	public function testCanSomebodyRegisterIsFalseForEventWithoutDate() {
		$this->assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	public function testCanSomebodyRegisterIsTrueForEventWithoutDateAndRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding the language of an event
	/////////////////////////////////////////////

	public function testGetLanguageFromIsoCodeWithValidLanguage() {
		$this->assertEquals(
			'Deutsch',
			$this->fixture->getLanguageNameFromIsoCode('de')
		);
	}

	public function testGetLanguageFromIsoCodeWithInvalidLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getLanguageNameFromIsoCode('xy')
		);
	}

	public function testGetLanguageFromIsoCodeWithVeryInvalidLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getLanguageNameFromIsoCode('foobar')
		);
	}

	public function testGetLanguageFromIsoCodeWithEmptyLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getLanguageNameFromIsoCode('')
		);
	}

	public function testHasLanguageWithLanguageReturnsTrue() {
		$this->fixture->setLanguage('de');
		$this->assertTrue(
			$this->fixture->hasLanguage()
		);
	}

	public function testHasLanguageWithNoLanguageReturnsFalse() {
		$this->fixture->setLanguage('');
		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	public function testGetLanguageNameWithDefaultLanguageOnSingleEvent() {
		$this->fixture->setLanguage('de');
		$this->assertEquals(
			'Deutsch',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithValidLanguageOnSingleEvent() {
		$this->fixture->setLanguage('en');
		$this->assertEquals(
			'English',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithInvalidLanguageOnSingleEvent() {
		$this->fixture->setLanguage('xy');
		$this->assertEquals(
			'',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithNoLanguageOnSingleEvent() {
		$this->fixture->setLanguage('');
		$this->assertEquals(
			'',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameOnDateRecord() {
		// This was an issue with bug #1518 and #1517.
		// The method getLanguage() needs to return the language from the date
		// record instead of the topic record.
		$topicRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'de')
		);

		$dateRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicRecordUid,
				'language' => 'it'
			)
		);

		$seminar = new tx_seminars_seminar($dateRecordUid);

		$this->assertEquals(
			'Italiano',
			$seminar->getLanguageName()
		);
	}

	public function testGetLanguageOnSingleRecordThatWasADateRecord() {
		// This test comes from bug 1518 and covers the following situation:
		// We have an event record that has the topic field set as it was a
		// date record. But then it was switched to be a single event record.
		// In that case, the language from the single event record must be
		// returned, not the one from the referenced topic record.

		$topicRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('language' => 'de')
		);

		$singleRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'topic' => $topicRecordUid,
				'language' => 'it'
			)
		);

		$seminar = new tx_seminars_seminar($singleRecordUid);

		$this->assertEquals(
			'Italiano',
			$seminar->getLanguageName()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding the date fields of an event
	////////////////////////////////////////////////

	public function testGetBeginDateAsTimestampIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getBeginDateAsTimestamp()
		);
	}

	public function testGetBeginDateAsTimestamp() {
		$this->fixture->setBeginDate($this->beginDate);
		$this->assertEquals(
			$this->beginDate,
			$this->fixture->getBeginDateAsTimestamp()
		);
	}

	public function testHasBeginDateIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasBeginDate()
		);
	}

	public function testHasBeginDate() {
		$this->fixture->setBeginDate($this->beginDate);
		$this->assertTrue(
			$this->fixture->hasBeginDate()
		);
	}

	public function testGetEndDateAsTimestampIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getEndDateAsTimestamp()
		);
	}

	public function testGetEndDateAsTimestamp () {
		$this->fixture->setEndDate($this->beginDate);
		$this->assertEquals(
			$this->beginDate,
			$this->fixture->getEndDateAsTimestamp()
		);
	}

	public function testHasEndDateIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasEndDate()
		);
	}

	public function testHasEndDate () {
		$this->fixture->setEndDate($this->beginDate);
		$this->assertTrue(
			$this->fixture->hasEndDate()
		);
	}


	//////////////////////////////////////
	// Tests regarding the registration.
	//////////////////////////////////////

	public function testNeedsRegistrationIsTrueWithNonZeroMaxAttendances() {
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->needsRegistration()
		);
	}

	public function testNeedsRegistrationIsFalseWithZeroMaxAttendances() {
		$this->fixture->setAttendancesMax(0);

		$this->assertFalse(
			$this->fixture->needsRegistration()
		);
	}


	/////////////////////////////////////////////////////
	// Tests regarding the unregistration and the queue
	/////////////////////////////////////////////////////

	public function testGetUnregistrationDeadlineAsTimestampForNonZero() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);

		$this->assertEquals(
			$this->unregistrationDeadline,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);
	}

	public function testGetUnregistrationDeadlineAsTimestampForZero() {
		$this->fixture->setUnregistrationDeadline(0);

		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);
	}

	public function testGetUnregistrationDeadlineWithoutTimeForNonZero() {
		$this->fixture->setUnregistrationDeadline(1893488400);

		$this->assertEquals(
			'01.01.2030',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	public function testGetNonUnregistrationDeadlineWithTimeForZero() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(1);

		$this->assertEquals(
			'01.01.2030 10:00',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	public function testGetUnregistrationDeadlineIsEmptyForZero() {
		$this->fixture->setUnregistrationDeadline(0);

		$this->assertEquals(
			'',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	public function testHasUnregistrationDeadlineIsTrueForNonZeroDeadline() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);

		$this->assertTrue(
			$this->fixture->hasUnregistrationDeadline()
		);
	}

	public function testHasUnregistrationDeadlineIsFalseForZeroDeadline() {
		$this->fixture->setUnregistrationDeadline(0);

		$this->assertFalse(
			$this->fixture->hasUnregistrationDeadline()
		);
	}

	public function testIsUnregistrationPossibleIsFalseWithNoDeadlineSet() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithGlobalDeadlineSet() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);


		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setBeginDate($this->currentTimestamp);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithEventDeadlineSet() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);


		$this->fixture->setBeginDate($this->currentTimestamp);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp - ONE_DAY)
		);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithBothDeadlinesSet() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);


		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp - ONE_DAY)
		);
		$this->fixture->setBeginDate($this->currentTimestamp);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsFalseWithPassedEventUnregistrationDeadlineSet() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setBeginDate($this->currentTimestamp + (2*ONE_DAY));
		$this->fixture->setUnregistrationDeadline(
			$this->currentTimestamp - ONE_DAY
		);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithNonZeroAttendancesMax() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setAttendancesMax(10);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate(($this->currentTimestamp + ONE_WEEK));

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsFalseWithZeroAttendancesMax() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setAttendancesMax(0);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate(($this->currentTimestamp + ONE_WEEK));

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testGetRegistrationQueueSizeForNonZero() {
		$this->fixture->setRegistrationQueueSize(10);

		$this->assertEquals(
			10,
			$this->fixture->getRegistrationQueueSize()
		);
	}

	public function testGetRegistrationQueueSizeForZero() {
		$this->fixture->setRegistrationQueueSize(0);

		$this->assertEquals(
			0,
			$this->fixture->getRegistrationQueueSize()
		);
	}

	public function testHasRegistrationQueueSizeIsTrueForNonZero() {
		$this->fixture->setRegistrationQueueSize(5);

		$this->assertTrue(
			$this->fixture->hasRegistrationQueueSize()
		);
	}

	public function testHasZeroRegistrationQueueSizeIsFalseForZero() {
		$this->fixture->setRegistrationQueueSize(0);

		$this->assertFalse(
			$this->fixture->hasRegistrationQueueSize()
		);
	}

	public function testHasVacanciesOnRegistrationQueueIsTrueForNonEmptyQueue() {
		$this->fixture->setNumberOfAttendances(
			$this->fixture->getAttendancesMax()
		);
		$this->fixture->setRegistrationQueueSize(5);

		$this->assertTrue(
			$this->fixture->hasVacanciesOnRegistrationQueue()
		);
	}

	public function testHasVacanciesOnRegistrationQueueIsFalseForEmptyQueue() {
		$this->fixture->setNumberOfAttendances(
			$this->fixture->getAttendancesMax()
		);
		$this->fixture->setRegistrationQueueSize(0);

		$this->assertFalse(
			$this->fixture->hasVacanciesOnRegistrationQueue()
		);
	}

	public function testGetVacanciesOnRegistrationQueueForNonEmptyRegistrationQueue() {
		$this->fixture->setNumberOfAttendances(
			$this->fixture->getAttendancesMax()
		);
		$this->fixture->setRegistrationQueueSize(5);

		$this->assertEquals(
			5,
			$this->fixture->getVacanciesOnRegistrationQueue()
		);
	}

	public function testGetAttendancesOnRegistrationQueueIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	public function testGetAttendancesOnRegistrationQueueForNonEmptyRegistrationQueue() {
		$this->fixture->setNumberOfAttendancesOnQueue(4);
		$this->assertEquals(
			4,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	public function testHasAttendancesOnRegistrationQueueIsFalseForNoRegistrations() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueueSize(0);
		$this->fixture->setNumberOfAttendances(0);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->assertFalse(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	public function testHasAttendancesOnRegistrationQueueIsFalseForRegularRegistrationsOnly() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueueSize(0);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->assertFalse(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	public function testHasAttendancesOnRegistrationQueueIsTrueForQueueRegistrations() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueueSize(1);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(1);

		$this->assertTrue(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithNonEmptyQueueByDefault() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueueSize(1);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(1);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate(($this->currentTimestamp + ONE_WEEK));

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsFalseWithEmptyQueueByDefault() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueueSize(1);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate(($this->currentTimestamp + ONE_WEEK));

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleIsTrueWithEmptyQueueIfAllowedByConfiguration() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(true);

		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueueSize(1);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate(($this->currentTimestamp + ONE_WEEK));

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}


	///////////////////////////////////////////////////////////
	// Tests regarding the country field of the place records
	///////////////////////////////////////////////////////////

	public function testGetPlacesWithCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertEquals(
			array('ch'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		$this->assertEquals(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithInvalidCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		$this->assertEquals(
			array('xy'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithNoPlace() {
		$this->assertEquals(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithDeletedPlace() {
		$this->addPlaceRelation(
			array(
				'country' => 'at',
				'deleted' => 1
			)
		);

		$this->assertEquals(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testGetPlacesWithCountryWithMultipleCountries() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$this->assertEquals(
			array('ch', 'de'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	public function testHasCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithInvalicCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		// We expect a true even if the country code is invalid! See function's
		// comment on this.
		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithNoPlace() {
		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	public function testHasCountryWithMultipleCountries() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}

	public function testGetCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertEquals(
			'Schweiz',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithInvalidCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithMultipleCountries() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$this->assertEquals(
			'Schweiz, Deutschland',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryWithNoPlace() {
		$this->assertEquals(
			'',
			$this->fixture->getCountry()
		);
	}

	public function testGetCountryNameFromIsoCode() {
		$this->assertEquals(
			'Schweiz',
			$this->fixture->getCountryNameFromIsoCode('ch')
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountryNameFromIsoCode('xy')
		);

		$this->assertEquals(
			'',
			$this->fixture->getCountryNameFromIsoCode('')
		);
	}

	public function testGetRelatedMmRecordUidsWithNoPlace() {
		$this->assertEquals(
			array(),
			$this->fixture->getRelatedMmRecordUids(SEMINARS_TABLE_SITES_MM)
		);
	}

	public function testGetRelatedMmRecordUidsWithOnePlace() {
		$uid = $this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		$this->assertEquals(
			array($uid),
			$this->fixture->getRelatedMmRecordUids(SEMINARS_TABLE_SITES_MM)
		);
	}

	public function testGetRelatedMmRecordUidsWithTwoPlaces() {
		$uid1 = $this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);
		$uid2 = $this->addPlaceRelation(
			array(
				'country' => 'de'
			)
		);

		$result = $this->fixture->getRelatedMmRecordUids(
			SEMINARS_TABLE_SITES_MM
		);
		sort($result);
		$this->assertEquals(
			array($uid1, $uid2),
			$result
		);
	}


	//////////////////////////////////////
	// Tests regarding the target groups
	//////////////////////////////////////

	public function testHasTargetGroupsIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasTargetGroups()
		);
	}

	public function testHasTargetGroups() {
		$this->addTargetGroupRelation(array());

		$this->assertTrue(
			$this->fixture->hasTargetGroups()
		);
	}

	public function testGetTargetGroupNamesWithNoTargetGroup() {
		$this->assertEquals(
			'',
			$this->fixture->getTargetGroupNames()
		);
	}

	public function testGetTargetGroupNamesWithSingleTargetGroup() {
		$title = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $title));

		$this->assertEquals(
			$title,
			$this->fixture->getTargetGroupNames()
		);
	}

	public function testGetTargetGroupNamesWithMultipleTargetGroups() {
		$titleTargetGroup1 = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup1));

		$titleTargetGroup2 = 'TEST target group 2';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup2));

		$this->assertEquals(
			$titleTargetGroup1.', '.$titleTargetGroup2,
			$this->fixture->getTargetGroupNames()
		);
	}

	public function testGetTargetGroupsAsArrayWithNoTargetGroups() {
		$this->assertEquals(
			array(),
			$this->fixture->getTargetGroupsAsArray()
		);
	}

	public function testGetTargetGroupsAsArrayWithSingleTargetGroup() {
		$title = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $title));

		$this->assertEquals(
			array($title),
			$this->fixture->getTargetGroupsAsArray()
		);
	}

	public function testGetTargetGroupsAsArrayWithMultipleTargetGroups() {
		$titleTargetGroup1 = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup1));

		$titleTargetGroup2 = 'TEST target group 2';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup2));

		$this->assertEquals(
			array($titleTargetGroup1, $titleTargetGroup2),
			$this->fixture->getTargetGroupsAsArray()
		);
	}


	////////////////////////////////////////
	// Tests regarding the payment methods
	////////////////////////////////////////

	public function testHasPaymentMethodsReturnsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasPaymentMethods()
		);
	}

	public function testCanHaveOnePaymentMethod() {
		$this->addPaymentMethodRelation(array());

		$this->assertTrue(
			$this->fixture->hasPaymentMethods()
		);
	}

	public function testGetPaymentMethodsUidsWithNoPaymentMethodReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getPaymentMethodsUids()
		);
	}

	public function testGetPaymentMethodsUidsWithSinglePaymentMethodReturnsASingleUid() {
		$uid = $this->addPaymentMethodRelation(array());

		$this->assertEquals(
			$uid,
			$this->fixture->getPaymentMethodsUids()
		);
	}

	public function testGetPaymentMethodsUidsWithMultiplePaymentMethodsReturnsMultipleUidsSeparatedByComma() {
		$firstUid = $this->addPaymentMethodRelation(array());
		$secondUid = $this->addPaymentMethodRelation(array());

		$this->assertEquals(
			$firstUid.','.$secondUid,
			$this->fixture->getPaymentMethodsUids()
		);
	}

	public function testGetPaymentMethodsPlainWithNoPaymentMethodReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	public function testGetPaymentMethodsPlainWithSinglePaymentMethodReturnsASinglePaymentMethod() {
		$title = 'Test title';
		$this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	public function testGetPaymentMethodsPlainWithMultiplePaymentMethodsReturnsMultiplePaymentMethods() {
		$firstTitle = 'Payment Method 1';
		$secondTitle = 'Payment Method 2';
		$this->addPaymentMethodRelation(array('title' => $firstTitle));
		$this->addPaymentMethodRelation(array('title' => $secondTitle));

		$this->assertContains(
			$firstTitle,
			$this->fixture->getPaymentMethodsPlain()
		);
		$this->assertContains(
			$secondTitle,
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	public function testGetPaymentMethodsPlainShortWithNoPaymentMethodReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	public function testGetPaymentMethodsPlainShortWithSinglePaymentMethodReturnsASinglePaymentMethod() {
		$title = 'Test title';
		$this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	public function testGetPaymentMethodsPlainShortWithMultiplePaymentMethodsReturnsMultiplePaymentMethods() {
		$firstTitle = 'Payment Method 1';
		$secondTitle = 'Payment Method 2';
		$this->addPaymentMethodRelation(array('title' => $firstTitle));
		$this->addPaymentMethodRelation(array('title' => $secondTitle));

		$this->assertContains(
			$firstTitle,
			$this->fixture->getPaymentMethodsPlainShort()
		);
		$this->assertContains(
			$secondTitle,
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	public function testGetSinglePaymentMethodPlainWithInvalidPaymentMethodUidReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodPlain(0)
		);
	}

	public function testGetSinglePaymentMethodPlainWithValidPaymentMethodUidReturnsTheTitleOfThePaymentMethod() {
		$title = 'Test payment method';
		$uid = $this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getSinglePaymentMethodPlain($uid)
		);
	}

	public function testGetSinglePaymentMethodPlainWithNonExistentPaymentMethodUidReturnsAnEmptyString() {
		$uid = $this->addPaymentMethodRelation(array());

		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodPlain($uid + 1)
		);
	}

	public function testGetSinglePaymentMethodShortWithInvalidPaymentMethodUidReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodShort(0)
		);
	}

	public function testGetSinglePaymentMethodShortWithValidPaymentMethodUidReturnsTheTitleOfThePaymentMethod() {
		$title = 'Test payment method';
		$uid = $this->addPaymentMethodRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getSinglePaymentMethodShort($uid)
		);
	}

	public function testGetSinglePaymentMethodShortWithNonExistentPaymentMethodUidReturnsAnEmptyString() {
		$uid = $this->addPaymentMethodRelation(array());

		$this->assertEquals(
			'',
			$this->fixture->getSinglePaymentMethodShort($uid + 1)
		);
	}


	///////////////////////////////////
	// Tests regarding the event type
	///////////////////////////////////

	public function testSetEventTypeThrowsExceptionForNegativeArgument() {
		$this->setExpectedException(
			'Exception', '$eventType must be >= 0.'
		);

		$this->fixture->setEventType(-1);
	}

	public function testSetEventTypeIsAllowedWithZero() {
		$this->fixture->setEventType(0);
	}

	public function testSetEventTypeIsAllowedWithPositiveInteger() {
		$this->fixture->setEventType(1);
	}

	public function testHasEventTypeInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasEventType()
		);
	}

	public function testHasEventTypeReturnsTrueIfSingleEventHasNonZeroEventType() {
		$this->fixture->setEventType(
			$this->testingFramework->createRecord(SEMINARS_TABLE_EVENT_TYPES)
		);

		$this->assertTrue(
			$this->fixture->hasEventType()
		);
	}

	public function testGetEventTypeReturnsEmptyStringForSingleEventWithoutType() {
		$this->assertEquals(
			'',
			$this->fixture->getEventType()
		);
	}

	public function testGetEventTypeReturnsTitleOfRelatedEventTypeForSingleEvent() {
		$this->fixture->setEventType(
			$this->testingFramework->createRecord(
				SEMINARS_TABLE_EVENT_TYPES, array('title' => 'foo type')
			)
		);

		$this->assertEquals(
			'foo type',
			$this->fixture->getEventType()
		);
	}

	public function testGetEventTypeForDateRecordReturnsTitleOfEventTypeFromTopicRecord() {
		$topicRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'event_type' => $this->testingFramework->createRecord(
					SEMINARS_TABLE_EVENT_TYPES, array('title' => 'foo type')
				),
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);
		$seminar = new tx_seminars_seminar($dateRecordUid);

		$this->assertEquals(
			'foo type',
			$seminar->getEventType()
		);
	}

	public function testGetEventTypeForTopicRecordReturnsTitleOfRelatedEventType() {
		$topicRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'event_type' => $this->testingFramework->createRecord(
					SEMINARS_TABLE_EVENT_TYPES, array('title' => 'foo type')
				),
			)
		);
		$seminar = new tx_seminars_seminar($topicRecordUid);

		$this->assertEquals(
			'foo type',
			$seminar->getEventType()
		);
	}

	public function testGetEventTypeUidReturnsUidFromTopicRecord() {
		// This test comes from bug #1515.
		$topicRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'event_type' => 99999
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicRecordUid,
				'event_type' => 199999
			)
		);
		$seminar = new tx_seminars_seminar($dateRecordUid);

		$this->assertEquals(
			99999,
			$seminar->getEventTypeUid()
		);
	}

	public function testGetEventTypeUidInitiallyReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getEventTypeUid()
		);
	}

	public function testGetEventTypeUidWithEventTypeReturnsEventTypeUid() {
		$eventTypeUid = $this->addEventTypeRelation(array());
		$this->assertEquals(
			$eventTypeUid,
			$this->fixture->getEventTypeUid()
		);
	}


	////////////////////////////////////////////
	// Tests regarding the organizing partners
	////////////////////////////////////////////

	public function testHasOrganizingPartnersReturnsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasOrganizingPartners()
		);
	}

	public function testCanHaveOneOrganizingPartner() {
		$this->addOrganizingPartnerRelation(array());

		$this->assertTrue(
			$this->fixture->hasOrganizingPartners()
		);
	}

	public function testGetNumberOfOrganizingPartnersWithNoOrganizingPartnerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}

	public function testGetNumberOfOrganizingPartnersWithSingleOrganizingPartnerReturnsOne() {
		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}

	public function testGetNumberOfOrganizingPartnersWithMultipleOrganizingPartnersReturnsTwo() {
		$this->addOrganizingPartnerRelation(array());
		$this->addOrganizingPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}


	///////////////////////////////////
	// Tests regarding the categories
	///////////////////////////////////

	public function testInitiallyHasNoCategories() {
		$this->assertFalse(
			$this->fixture->hasCategories()
		);
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfCategories()
		);
		$this->assertEquals(
			array(),
			$this->fixture->getCategories()
		);
	}

	public function testCanHaveOneCategory() {
		$categoryUid = $this->addCategoryRelation(array('title' => 'Test'));

		$this->assertTrue(
			$this->fixture->hasCategories()
		);
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfCategories()
		);
		$this->assertEquals(
			array($categoryUid => 'Test'),
			$this->fixture->getCategories()
		);
	}

	public function testCanHaveTwoCategories() {
		$categoryUid1 = $this->addCategoryRelation(array('title' => 'Test 1'));
		$categoryUid2 = $this->addCategoryRelation(array('title' => 'Test 2'));

		$this->assertTrue(
			$this->fixture->hasCategories()
		);
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfCategories()
		);

		$categories = $this->fixture->getCategories();

		$this->assertEquals(
			2,
			count($categories)
		);
		$this->assertEquals(
			'Test 1',
			$categories[$categoryUid1]
		);
		$this->assertEquals(
			'Test 2',
			$categories[$categoryUid2]
		);
	}


	///////////////////////////////////
	// Tests regarding the time slotd
	///////////////////////////////////

	public function testGetTimeslotsAsArrayWithMarkersReturnsArraySortedByDate() {
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $this->fixture->getUid(),
				'begin_date' => 200,
				'room' => 'Room1'
			)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $this->fixture->getUid(),
				'begin_date' => 100,
				'room' => 'Room2'
			)
		);

		$timeSlotsWithMarkers = $this->fixture->getTimeslotsAsArrayWithMarkers();
		$this->assertEquals(
			$timeSlotsWithMarkers[0]['room'],
			'Room2'
		);
		$this->assertEquals(
			$timeSlotsWithMarkers[1]['room'],
			'Room1'
		);
	}


	///////////////////////////////////
	// Tests regarding the organizers
	///////////////////////////////////

	public function testHasOrganizersReturnsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasOrganizers()
		);
	}

	public function testCanHaveOneOrganizer() {
		$this->addOrganizerRelation(array());

		$this->assertTrue(
			$this->fixture->hasOrganizers()
		);
	}

	public function testGetNumberOfOrganizersWithNoOrganizerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	public function testGetNumberOfOrganizersWithSingleOrganizerReturnsOne() {
		$this->addOrganizerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	public function testGetNumberOfOrganizersWithMultipleOrganizersReturnsTwo() {
		$this->addOrganizerRelation(array());
		$this->addOrganizerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	public function testGetOrganizersRawWithNoOrganizersReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawWithSingleOrganizerWithoutHomepageReturnsSingleOrganizer() {
		$organizer = array(
			'title' => 'test organizer 1',
			'homepage' => ''
		);
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			$organizer['title'],
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawWithSingleOrganizerWithHomepageReturnsSingleOrganizerWithHomepage() {
		$organizer = array(
			'title' => 'test organizer 1',
			'homepage' => 'test homepage 1'
		);
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			$organizer['title'].', '.$organizer['homepage'],
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawWithMultipleOrganizersWithoutHomepageReturnsTwoOrganizers() {
		$firstOrganizer = array(
			'title' => 'test organizer 1',
			'homepage' => ''
		);
		$secondOrganizer = array(
			'title' => 'test organizer 2',
			'homepage' => ''
		);
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			$firstOrganizer['title'].CRLF.$secondOrganizer['title'],
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersRawWithMultipleOrganizersWithHomepageReturnsTwoOrganizersWithHomepage() {
		$firstOrganizer = array(
			'title' => 'test organizer 1',
			'homepage' => 'test homepage 1'
		);
		$secondOrganizer = array(
			'title' => 'test organizer 2',
			'homepage' => 'test homepage 2'
		);
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			$firstOrganizer['title'].', '.$firstOrganizer['homepage'].CRLF
			.$secondOrganizer['title'].', '.$secondOrganizer['homepage'],
			$this->fixture->getOrganizersRaw()
		);
	}

	public function testGetOrganizersNameAndEmailWithNoOrganizersReturnsEmptyString() {
		$this->assertEquals(
			array(),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	public function testGetOrganizersNameAndEmailWithSingleOrganizerReturnsSingleOrganizer() {
		$organizer = array(
			'title' => 'test organizer',
			'email' => 'test@organizer.org'
		);
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			array('"'.$organizer['title'].'" <'.$organizer['email'].'>'),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	public function testGetOrganizersNameAndEmailWithMultipleOrganizersReturnsTwoOrganizers() {
		$firstOrganizer = array(
			'title' => 'test organizer 1',
			'email' => 'test1@organizer.org'
		);
		$secondOrganizer = array(
			'title' => 'test organizer 2',
			'email' => 'test2@organizer.org'
		);
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			array(
				'"'.$firstOrganizer['title'].'" <'.$firstOrganizer['email'].'>',
				'"'.$secondOrganizer['title'].'" <'.$secondOrganizer['email'].'>'
			),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	public function testGetOrganizersEmailWithNoOrganizersReturnsEmptyString() {
		$this->assertEquals(
			array(),
			$this->fixture->getOrganizersEmail()
		);
	}

	public function testGetOrganizersEmailWithSingleOrganizerReturnsSingleOrganizer() {
		$organizer = array('email' => 'test@organizer.org');
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			array($organizer['email']),
			$this->fixture->getOrganizersEmail()
		);
	}

	public function testGetOrganizersEmailWithMultipleOrganizersReturnsTwoOrganizers() {
		$firstOrganizer = array('email' => 'test1@organizer.org');
		$secondOrganizer = array('email' => 'test2@organizer.org');
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			array($firstOrganizer['email'], $secondOrganizer['email']),
			$this->fixture->getOrganizersEmail()
		);
	}

	public function testGetOrganizersFootersWithNoOrganizersReturnsEmptyString() {
		$this->assertEquals(
			array(),
			$this->fixture->getOrganizersFooter()
		);
	}

	public function testGetOrganizersFootersWithSingleOrganizerReturnsSingleOrganizer() {
		$organizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation($organizer);
		$this->assertEquals(
			array($organizer['email_footer']),
			$this->fixture->getOrganizersFooter()
		);
	}

	public function testGetOrganizersFootersWithMultipleOrganizersReturnsTwoOrganizers() {
		$firstOrganizer = array('email_footer' => 'test email footer');
		$secondOrganizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		$this->assertEquals(
			array(
				$firstOrganizer['email_footer'],
				$secondOrganizer['email_footer']
			),
			$this->fixture->getOrganizersFooter()
		);
	}

	public function testGetAttendancesPidWithNoOrganizerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getAttendancesPid()
		);
	}

	public function testGetAttendancesPidWithSingleOrganizerReturnsPid() {
		$this->addOrganizerRelation(array('attendances_pid' => 99));
		$this->assertEquals(
			99,
			$this->fixture->getAttendancesPid()
		);
	}

	public function testGetAttendancesPidWithMultipleOrganizerReturnsFirstPid() {
		$this->addOrganizerRelation(array('attendances_pid' => 99));
		$this->addOrganizerRelation(array('attendances_pid' => 66));
		$this->assertEquals(
			99,
			$this->fixture->getAttendancesPid()
		);
	}


	/////////////////////////////////
	// Tests regarding the speakers
	/////////////////////////////////

	public function testGetNumberOfSpeakersWithNoSpeakerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	public function testGetNumberOfSpeakersWithSingleSpeakerReturnsOne() {
		$this->addSpeakerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	public function testGetNumberOfSpeakersWithMultipleSpeakersReturnsTwo() {
		$this->addSpeakerRelation(array());
		$this->addSpeakerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	public function testGetNumberOfPartnersWithNoPartnerReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfPartners()
		);
	}

	public function testGetNumberOfPartnersWithSinglePartnerReturnsOne() {
		$this->addPartnerRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfPartners()
		);
	}

	public function testGetNumberOfPartnersWithMultiplePartnersReturnsTwo() {
		$this->addPartnerRelation(array());
		$this->addPartnerRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfPartners()
		);
	}

	public function testGetNumberOfTutorsWithNoTutorReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfTutors()
		);
	}

	public function testGetNumberOfTutorsWithSingleTutorReturnsOne() {
		$this->addTutorRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfTutors()
		);
	}

	public function testGetNumberOfTutorsWithMultipleTutorsReturnsTwo() {
		$this->addTutorRelation(array());
		$this->addTutorRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfTutors()
		);
	}

	public function testGetNumberOfLeadersWithNoLeaderReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfLeaders()
		);
	}

	public function testGetNumberOfLeadersWithSingleLeaderReturnsOne() {
		$this->addLeaderRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfLeaders()
		);
	}

	public function testGetNumberOfLeadersWithMultipleLeadersReturnsTwo() {
		$this->addLeaderRelation(array());
		$this->addLeaderRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfLeaders()
		);
	}

	public function testHasSpeakersOfTypeIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('speakers')
		);
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('partners')
		);
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('tutors')
		);
		$this->assertFalse(
			$this->fixture->hasSpeakersOfType('leaders')
		);
	}

	public function testHasSpeakersOfTypeWithSingleSpeakerOfTypeReturnsTrue() {
		$this->addSpeakerRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('speakers')
		);

		$this->addPartnerRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('partners')
		);

		$this->addTutorRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('tutors')
		);

		$this->addLeaderRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakersOfType('leaders')
		);
	}

	public function testHasSpeakersIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasSpeakers()
		);
	}

	public function testCanHaveOneSpeaker() {
		$this->addSpeakerRelation(array());
		$this->assertTrue(
			$this->fixture->hasSpeakers()
		);
	}

	public function testHasPartnersIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasPartners()
		);
	}

	public function testCanHaveOnePartner() {
		$this->addPartnerRelation(array());
		$this->assertTrue(
			$this->fixture->hasPartners()
		);
	}

	public function testHasTutorsIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasTutors()
		);
	}

	public function testCanHaveOneTutor() {
		$this->addTutorRelation(array());
		$this->assertTrue(
			$this->fixture->hasTutors()
		);
	}

	public function testHasLeadersIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasLeaders()
		);
	}

	public function testCanHaveOneLeader() {
		$this->addLeaderRelation(array());
		$this->assertTrue(
			$this->fixture->hasLeaders()
		);
	}

	public function testGetSpeakersWithDescriptionRawWithNoSpeakersReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerReturnsSingleSpeaker() {
		$speaker = array('title' => 'test speaker');

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerWithOrganizationReturnsSingleSpeakerWithOrganization() {
		$speaker = array(
			'title' => 'test speaker',
			'organization' => 'test organization'
		);

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerWithHomepageReturnsSingleSpeakerWithHomepage() {
		$speaker = array(
			'title' => 'test speaker',
			'homepage' => 'test homepage'
		);

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerWithOrganizationAndHomepageReturnsSingleSpeakerWithOrganizationAndHomepage() {
		$speaker = array(
			'title' => 'test speaker',
			'organization' => 'test organization',
			'homepage' => 'test homepage'
		);

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerWithDescriptionReturnsSingleSpeakerWithDescription() {
		$speaker = array(
			'title' => 'test speaker',
			'description' => 'test description'
		);

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerWithOrganizationAndDescriptionReturnsSingleSpeakerWithOrganizationAndDescription() {
		$speaker = array(
			'title' => 'test speaker',
			'organization' => 'test organization',
			'description' => 'test description'
		);

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerWithHomepageAndDescriptionReturnsSingleSpeakerWithHomepageAndDescription() {
		$speaker = array(
			'title' => 'test speaker',
			'homepage' => 'test homepage',
			'description' =>  'test description'
		);

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['homepage'].CRLF
				.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithSingleSpeakerWithOrganizationAndHomepageAndDescriptionReturnsSingleSpeakerWithOrganizationAndHomepageAndDescription() {
		$speaker = array(
			'title' => 'test speaker',
			'organization' => 'test organization',
			'homepage' => 'test homepage',
			'description' => 'test description'
		);

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'].', '.$speaker['organization']
				.', '.$speaker['homepage'].CRLF.$speaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersReturnsTwoSpeakers() {
		$firstSpeaker = array('title' => 'test speaker 1');
		$secondSpeaker = array('title' => 'test speaker 2');

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$secondSpeaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$secondSpeaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$secondSpeaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$secondSpeaker['title'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersWithOrganizationReturnsTwoSpeakersWithOrganization() {
		$firstSpeaker = array(
			'title' => 'test speaker 1',
			'organization' => 'test organization 1'
		);
		$secondSpeaker = array(
			'title' => 'test speaker 2',
			'organization' => 'test organization 2'
		);

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersWithHomepageReturnsTwoSpeakersWithHomepage() {
		$firstSpeaker = array(
			'title' => 'test speaker 1',
			'homepage' => 'test homepage 1'
		);
		$secondSpeaker = array(
			'title' => 'test speaker 2',
			'homepage' => 'test homepage 2'
		);

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersWithOrganizationAndHomepageReturnsTwoSpeakersWithOrganizationAndHomepage() {
		$firstSpeaker = array(
			'title' => 'test speaker 1',
			'organization' => 'test organization 1',
			'homepage' => 'test homepage 1'
		);
		$secondSpeaker = array(
			'title' => 'test speaker 2',
			'organization' => 'test organization 2',
			'homepage' => 'test homepage 2'
		);

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersWithDescriptionReturnsTwoSpeakersWithDescription() {
		$firstSpeaker = array(
			'title' => 'test speaker 1',
			'description' => 'test description 1'
		);
		$secondSpeaker = array(
			'title' => 'test speaker 2',
			'description' => 'test description 2'
		);

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersWithOrganizationAndDescriptionReturnsTwoSpeakersWithOrganizationAndDescription() {
		$firstSpeaker = array(
			'title' => 'test speaker 1',
			'organization' => 'test organization 1',
			'description' => 'test description 1'
		);
		$secondSpeaker = array(
			'title' => 'test speaker 2',
			'organization' => 'test organization 2',
			'description' => 'test description 2'
		);

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersWithHomepageAndDescriptionReturnsTwoSpeakersWithHomepageAndDescription() {
		$firstSpeaker = array(
			'title' => 'test speaker 1',
			'homepage' => 'test homepage 1',
			'description' =>  'test description 1'
		);
		$secondSpeaker = array(
			'title' => 'test speaker 2',
			'homepage' => 'test homepage 2',
			'description' =>  'test description 2'
		);

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['homepage'].CRLF
				.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['homepage'].CRLF
				.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersWithDescriptionRawWithMultipleSpeakersWithOrganizationAndHomepageAndDescriptionReturnsTwoSpeakersWithOrganizationAndHomepageAndDescription() {
		$firstSpeaker = array(
			'title' => 'test speaker 1',
			'organization' => 'test organization 1',
			'homepage' => 'test homepage 1',
			'description' => 'test description 1'
		);
		$secondSpeaker = array(
			'title' => 'test speaker 2',
			'organization' => 'test organization 2',
			'homepage' => 'test homepage 2',
			'description' => 'test description 2'
		);

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$firstSpeaker['organization']
				.', '.$firstSpeaker['homepage'].CRLF.$firstSpeaker['description'].CRLF
				.$secondSpeaker['title'].', '.$secondSpeaker['organization']
				.', '.$secondSpeaker['homepage'].CRLF.$secondSpeaker['description'].CRLF,
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	public function testGetSpeakersShortWithNoSpeakersReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort('speakers')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort('partners')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort('tutors')
		);
		$this->assertEquals(
			'',
			$this->fixture->getSpeakersShort('leaders')
		);
	}

	public function testGetSpeakersShortWithSingleSpeakersReturnsSingleSpeaker() {
		$speaker = array('title' => 'test speaker');

		$this->addSpeakerRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort('speakers')
		);

		$this->addPartnerRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort('partners')
		);

		$this->addTutorRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort('tutors')
		);

		$this->addLeaderRelation($speaker);
		$this->assertEquals(
			$speaker['title'],
			$this->fixture->getSpeakersShort('leaders')
		);
	}

	public function testGetSpeakersShortWithMultipleSpeakersReturnsTwoSpeakers() {
		$firstSpeaker = array('title' => 'test speaker 1');
		$secondSpeaker = array('title' => 'test speaker 2');

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort('speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort('partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort('tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		$this->assertEquals(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort('leaders')
		);
	}


	////////////////////////////////////////
	// Test concerning the collision check
	////////////////////////////////////////

	public function testEventsWithTheExactSameDateCollide() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup()
		);

		$begin = mktime();
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $begin,
				'end_date' => $end
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		$this->assertTrue(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	public function testCollidingEventsDoNotCollideIfCollisionSkipIsEnabledForAllEvents() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup()
		);

		$begin = mktime();
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $begin,
				'end_date' => $end,
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid,
			)
		);

		$this->fixture->setConfigurationValue(
			'skipRegistrationCollisionCheck', true
		);

		$this->assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	public function testCollidingEventsDoNoCollideIfCollisionSkipIsEnabledForThisEvent() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup()
		);

		$begin = mktime();
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);
		$this->fixture->setSkipCollisionCheck(true);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $begin,
				'end_date' => $end
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		$this->assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	public function testCollidingEventsDoNoCollideIfCollisionSkipIsEnabledForAnotherEvent() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup()
		);

		$begin = mktime();
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'begin_date' => $begin,
				'end_date' => $end,
				'skip_collision_check' => 1
			)
		);

		$this->testingFramework->createRecord(
			SEMINARS_TABLE_ATTENDANCES,
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		$this->assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}


	////////////////////////
	// Tests for the icons
	////////////////////////

	public function testUsesCorrectIconForSingleEvent() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_COMPLETE);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForTopic() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_TOPIC);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForDate() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_DATE);

		$this->assertContains(
			'icon_tx_seminars_seminars_date.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForHiddenSingleEvent() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_COMPLETE);
		$this->fixture->setHidden(true);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete__h.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForHiddenTopic() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_TOPIC);
		$this->fixture->setHidden(true);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic__h.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForHiddenDate() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_DATE);
		$this->fixture->setHidden(true);

		$this->assertContains(
			'icon_tx_seminars_seminars_date__h.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForVisibleTimedSingleEvent() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_COMPLETE);
		$this->fixture->setRecordStartTime(mktime() - 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForVisibleTimedTopic() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_TOPIC);
		$this->fixture->setRecordStartTime(mktime() - 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForVisibleTimedDate() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_DATE);
		$this->fixture->setRecordStartTime(mktime() - 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_date.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForExpiredSingleEvent() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_COMPLETE);
		$this->fixture->setRecordEndTime(mktime() - 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForExpiredTimedTopic() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_TOPIC);
		$this->fixture->setRecordEndTime(mktime() - 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForExpiredTimedDate() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_DATE);
		$this->fixture->setRecordEndTime(mktime() - 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_date__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForStillInvisibleTimedSingleEvent() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_COMPLETE);
		$this->fixture->setRecordStartTime(mktime() + 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_complete__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForStillInvisibleTimedTopic() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_TOPIC);
		$this->fixture->setRecordStartTime(mktime() + 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_topic__t.',
			$this->fixture->getRecordIcon()
		);
	}

	public function testUsesCorrectIconForStillInvisibleTimedDate() {
		$this->fixture->setRecordType(SEMINARS_RECORD_TYPE_DATE);
		$this->fixture->setRecordStartTime(mktime() + 1000);

		$this->assertContains(
			'icon_tx_seminars_seminars_date__t.',
			$this->fixture->getRecordIcon()
		);
	}


	/////////////////////////////////////
	// Tests for hasSeparateDetailsPage
	/////////////////////////////////////

	public function testHasSeparateDetailsPageIsFalseByDefault() {
		$this->assertFalse(
			$this->fixture->hasSeparateDetailsPage()
		);
	}

	public function testHasSeparateDetailsPageReturnsTrueForInternalSeparateDetailsPage() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertTrue(
			$event->hasSeparateDetailsPage()
		);
	}

	public function testHasSeparateDetailsPageReturnsTrueForExternalSeparateDetailsPage() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com'
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertTrue(
			$event->hasSeparateDetailsPage()
		);
	}


	///////////////////////////////////////////////
	// Tests for getDetailedViewLinkConfiguration
	///////////////////////////////////////////////

	public function testGetDetailedViewLinkConfigurationReturnsGeneralDetailsAndSeminarParameterPageByDefault() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$this->createPi1($detailsPageUid);

		$this->assertEquals(
			array(
				'parameter' => $detailsPageUid,
				'additionalParams' => '&tx_seminars_pi1%5BshowUid%5D=' .
					$this->fixture->getUid(),
			),
			$this->fixture->getDetailedViewLinkConfiguration($this->pi1)
		);
	}

	public function testGetDetailedViewLinkConfigurationReturnsInternalDetailsPageIfSetWithoutSeminarParameter() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$this->createPi1($detailsPageUid);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertEquals(
			array(
				'parameter' => (string) $detailsPageUid
			),
			$event->getDetailedViewLinkConfiguration($this->pi1)
		);
	}

	public function testGetDetailedViewLinkConfigurationReturnsExternalDetailsPageIfSetWithoutSeminarParameter() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$this->createPi1($detailsPageUid);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com'
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertEquals(
			array(
				'parameter' => 'www.test.com'
			),
			$event->getDetailedViewLinkConfiguration($this->pi1)
		);
	}


	////////////////////////////////////
	// Tests for the detailed view URL
	////////////////////////////////////

	public function testGetDetailedViewUrlCanReturnAbsoluteUrlStartingWithHttp() {
		$this->createPi1();

		$this->assertRegExp(
			'/^http:\/\/./',
			$this->fixture->getDetailedViewUrl($this->pi1, true)
		);
	}

	public function testGetDetailedViewUrlReturnsUrlWithEncodedBrackets() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$this->createPi1($detailsPageUid);

		$this->assertContains(
			'%5BshowUid%5D',
			$this->fixture->getDetailedViewUrl($this->pi1)
		);

		$this->assertNotContains(
			'[showUid]',
			$this->fixture->getDetailedViewUrl($this->pi1)
		);
	}

	public function testGetDetailedViewUrlCanReturnRelativeUrlNotStartingWithHttp() {
		$this->createPi1();

		$this->assertNotContains(
			'http://',
			$this->fixture->getDetailedViewUrl($this->pi1, false)
		);
	}

	public function testGetDetailedViewUrlWithSeparateDetailsPageOmitsShowUidParameter() {
		$this->createPi1();
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertNotContains(
			'showUid',
			$event->getDetailedViewUrl($this->pi1, false)
		);
	}

	public function testGetDetailedViewUrlReturnsUrlOfSeparateInternalDetailsPageIfSet() {
		$this->createPi1();
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertContains(
			'?id=' . $detailsPageUid,
			$event->getDetailedViewUrl($this->pi1, false)
		);
	}

	public function testGetDetailedViewUrlReturnsUrlOfSeparateExternalDetailsPageIfSet() {
		$this->createPi1();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com'
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertEquals(
			'http://www.test.com',
			$event->getDetailedViewUrl($this->pi1, false)
		);
	}

	public function testGetDetailedViewUrlReturnsUrlOfSeparateExternalDetailsPageIfSetWithTarget() {
		$this->createPi1();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com foo'
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertEquals(
			'http://www.test.com',
			$event->getDetailedViewUrl($this->pi1, false)
		);
	}


	//////////////////////////////////
	// Tests for getLinkedFieldValue
	//////////////////////////////////

	public function testGetLinkedFieldValueForTitleCreatesLinkTag() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$this->createPi1($detailsPageUid);

		$this->assertContains(
			'<a ',
			$this->fixture->getLinkedFieldValue($this->pi1, 'title')
		);
	}

	public function testGetLinkedFieldValueForTitleCreatesRelativeLink() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$this->createPi1($detailsPageUid);

		$this->assertNotContains(
			'http',
			$this->fixture->getLinkedFieldValue($this->pi1, 'title')
		);
	}

	public function testGetLinkedFieldValueForTitleLinksToSeparateInternalDetailsPageIfSet() {
		$this->createPi1();
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertContains(
			'?id=' . $detailsPageUid,
			$event->getLinkedFieldValue($this->pi1, 'title')
		);
	}

	public function testGetLinkedFieldValueForTitleLinksToSeparateExternalDetailsPageIfSet() {
		$this->createPi1();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com'
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$this->assertContains(
			'http://www.test.com',
			$event->getLinkedFieldValue($this->pi1, 'title')
		);
	}

	public function testGetLinkedFieldValueForTitleLinksToSeparateInternalDetailsPageWithTargetIfSet() {
		$this->createPi1();
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid . ' foo'
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$result = $event->getLinkedFieldValue($this->pi1, 'title');

		$this->assertContains(
			'?id=' . $detailsPageUid,
			$result
		);
		$this->assertContains(
			'target="foo"',
			$result
		);
	}

	public function testGetLinkedFieldValueForTitleLinksToSeparateExternalDetailsPageWithTargetIfSet() {
		$this->createPi1();
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com foo'
			)
		);
		$event = new tx_seminars_seminarchild(
			$eventUid, array()
		);

		$result = $event->getLinkedFieldValue($this->pi1, 'title');

		$this->assertContains(
			'http://www.test.com',
			$result
		);
		$this->assertContains(
			'target="foo"',
			$result
		);
	}


	/////////////////////////////////////////
	// Tests concerning getPlaceWithDetails
	/////////////////////////////////////////

	public function testGetPlaceWithDetailsReturnsWillBeAnnouncedForNoPlace() {
		$this->createPi1();
		$this->assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	public function testGetPlaceWithDetailsContainsTitleOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	public function testGetPlaceWithDetailsContainsTitleOfAllRelatedPlaces() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
		$this->assertContains(
			'another place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	public function testGetPlaceWithDetailsContainsAddressOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(
			array('title' => 'a place', 'address' => 'a street')
		);

		$this->assertContains(
			'a street',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	public function testGetPlaceWithDetailsContainsCityOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place', 'city' => 'Emden'));

		$this->assertContains(
			'Emden',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	public function testGetPlaceWithDetailsContainsCountryOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place', 'country' => 'de'));

		$this->assertContains(
			'Deutschland',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	public function testGetPlaceWithDetailsContainsHomepageLinkOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('homepage' => 'www.test.com'));

		$this->assertContains(
			' href="http://www.test.com',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	public function testGetPlaceWithDetailsContainsDirectionsOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('directions' => 'Turn right.'));

		$this->assertContains(
			'Turn right.',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}


	////////////////////////////////////////////
	// Tests concerning getPlaceWithDetailsRaw
	////////////////////////////////////////////

	public function testGetPlaceWithDetailsRawReturnsWillBeAnnouncedForNoPlace() {
		$this->testingFramework->createFakeFrontEnd();

		$this->assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	public function testGetPlaceWithDetailsRawContainsTitleOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	public function testGetPlaceWithDetailsRawContainsTitleOfAllRelatedPlaces() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
		$this->assertContains(
			'another place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	public function testGetPlaceWithDetailsRawContainsAddressOfOnePlace() {
		$this->addPlaceRelation(
			array('title' => 'a place', 'address' => 'a street')
		);

		$this->assertContains(
			'a street',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	public function testGetPlaceWithDetailsRawContainsCityOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place', 'city' => 'Emden'));

		$this->assertContains(
			'Emden',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	public function testGetPlaceWithDetailsRawContainsCountryOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place', 'country' => 'de'));

		$this->assertContains(
			'Deutschland',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	public function testGetPlaceWithDetailsRawContainsHomepageUrlOfOnePlace() {
		$this->addPlaceRelation(array('homepage' => 'www.test.com'));

		$this->assertContains(
			'www.test.com',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	public function testGetPlaceWithDetailsRawContainsDirectionsOfOnePlace() {
		$this->addPlaceRelation(array('directions' => 'Turn right.'));

		$this->assertContains(
			'Turn right.',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}


	////////////////////////////
	// Tests for getPlaceShort
	////////////////////////////

	public function testGetPlaceShortReturnsWillBeAnnouncedForNoPlaces() {
		$this->assertEquals(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceShort()
		);
	}

	public function testGetPlaceShortReturnsPlaceNameForOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place'));

		$this->assertEquals(
			'a place',
			$this->fixture->getPlaceShort()
		);
	}

	public function testGetPlaceShortReturnsPlaceNamesWithCommaForTwoPlaces() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		$this->assertContains(
			'a place',
			$this->fixture->getPlaceShort()
		);
		$this->assertContains(
			', ',
			$this->fixture->getPlaceShort()
		);
		$this->assertContains(
			'another place',
			$this->fixture->getPlaceShort()
		);
	}


	/////////////////////////////
	// Tests for attached files
	/////////////////////////////

	public function testHasAttachedFilesInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasAttachedFiles()
		);
	}

	public function testHasAttachedFilesWithOneAttachedFileReturnsTrue() {
		$this->fixture->setAttachedFiles('test.file');

		$this->assertTrue(
			$this->fixture->hasAttachedFiles()
		);
	}

	public function testHasAttachedFilesWithTwoAttachedFilesReturnsTrue() {
		$this->fixture->setAttachedFiles('test.file,test_02.file');

		$this->assertTrue(
			$this->fixture->hasAttachedFiles()
		);
	}

	public function testGetAttachedFilesInitiallyReturnsAnEmptyArray() {
		$this->createPi1();

		$this->assertEquals(
			array(),
			$this->fixture->getAttachedFiles($this->pi1)
		);
	}

	public function testGetAttachedFilesWithOneSetAttachedFileReturnsAttachedFileAsArrayWithCorrectFileSize() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertContains(
			'uploads/tx_seminars/' . $dummyFileName,
			$attachedFiles[0]['name']
		);

		$this->assertEquals(
			t3lib_div::formatSize(filesize($dummyFile)),
			$attachedFiles[0]['size']
		);
	}

	public function testGetAttachedFilesWithTwoSetAttachedFilesReturnsAttachedFilesAsArrayWithCorrectFileSize() {
		$this->createPi1();
		$dummyFile1 = $this->testingFramework->createDummyFile();
		$dummyFileName1 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile1);
		$dummyFile2 = $this->testingFramework->createDummyFile();
		$dummyFileName2 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);
		$this->fixture->setAttachedFiles($dummyFileName1 . ',' . $dummyFileName2);

		t3lib_div::writeFile($dummyFile2, 'Test');

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertContains(
			'uploads/tx_seminars/' . $dummyFileName1,
			$attachedFiles[0]['name']
		);

		$this->assertEquals(
			t3lib_div::formatSize(filesize($dummyFile1)),
			$attachedFiles[0]['size']
		);

		$this->assertContains(
			'uploads/tx_seminars/' . $dummyFileName2,
			$attachedFiles[1]['name']
		);

		$this->assertEquals(
			t3lib_div::formatSize(filesize($dummyFile2)),
			$attachedFiles[1]['size']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithFileEndingReturnsFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithoutFileEndingReturnsFileTypeNone() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'none',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithDotInFileNameReturnsCorrectFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test.test.txt');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithFileNameStartingWithADotReturnsFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('.txt');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	public function testGetAttachedFilesWithAttachedFileWithFileNameEndingWithADotReturnsFileTypeNone() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test.');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		$this->assertEquals(
			'none',
			$attachedFiles[0]['type']
		);
	}


	///////////////////////////////////
	// Tests concerning isOwnerFeUser
	///////////////////////////////////

	public function testIsOwnerFeUserForNoOwnerReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isOwnerFeUser()
		);
	}

	public function testIsOwnerFeUserForLoggedInUserOtherThanOwnerReturnsFalse() {
		$this->testingFramework->createFakeFrontEnd();
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setOwnerUid($userUid + 1);

		$this->assertFalse(
			$this->fixture->isOwnerFeUser()
		);
	}

	public function testIsOwnerFeUserForLoggedInUserOtherThanOwnerReturnsTrue() {
		$this->testingFramework->createFakeFrontEnd();
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setOwnerUid($userUid);

		$this->assertTrue(
			$this->fixture->isOwnerFeUser()
		);
	}
}
?>