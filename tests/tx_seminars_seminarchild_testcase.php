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
/**
 * Testcase for the seminar class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'tests/fixtures/class.tx_seminars_seminarchild.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_seminarchild_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	private $beginDate;
	private $unregistrationDeadline;
	private $currentTimestamp;

	protected function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$this->currentTimestamp = time();
		$this->beginDate = ($this->currentTimestamp + ONE_WEEK);
		$this->unregistrationDeadline = ($this->currentTimestamp + ONE_WEEK);

		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'deadline_unregistration' => $this->unregistrationDeadline,
				'language' => 'de',
				'attendees_min' => 5,
				'attendees_max' => 10,
				'object_type' => 0,
				'queue_size' => 0
			)
		);

		$this->fixture = new tx_seminars_seminarchild(
			$uid,
			array(
				'dateFormatYMD' => '%d.%m.%Y',
				'timeFormat' => '%H:%M',
				'showTimeOfUnregistrationDeadline' => 0,
				'unregistrationDeadlineDaysBeforeBeginDate' => 0
			)
		);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}

	public function testIsOk() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Inserts a place record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param	array		data of the place to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will be > 0
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
	 * @param	array		data of the payment method to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will be > 0
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
	 * @param	array		data of the organizer to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will be > 0
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
	 * @param	array		data of the category to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will be > 0
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
	 * @param	array		data of the organizer to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will be > 0
	 */
	private function addOrganizerRelation(array $organizerData = array()) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_ORGANIZERS, $organizerData
		);

		$this->fixture->addOrganizer($uid);

		return $uid;
	}


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

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


	//////////////////////////////////////////////
	// Tests regarding the language of an event.
	//////////////////////////////////////////////

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

	public function testHasLanguageWithDefaultLanguage() {
		$this->assertTrue(
			$this->fixture->hasLanguage()
		);
	}

	public function testHasLanguageWithNoLanguage() {
		// unsets the language field
		$this->fixture->setEventData(
			array(
				'language' => ''
			)
		);
		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	public function testGetLanguageNameWithDefaultLanguageOnSingleEvent() {
		$this->assertEquals(
			'Deutsch',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithValidLanguageOnSingleEvent() {
		$this->fixture->setEventData(
			array(
				'language' => 'en'
			)
		);
		$this->assertEquals(
			'English',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithInvalidLanguageOnSingleEvent() {
		$this->fixture->setEventData(
			array(
				'language' => 'xy'
			)
		);
		$this->assertEquals(
			'',
			$this->fixture->getLanguageName()
		);
	}


	public function testGetLanguageNameWithNoLanguageOnSingleEvent() {
		$this->fixture->setEventData(
			array(
				'language' => ''
			)
		);
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


	/////////////////////////////////////////////////
	// Tests regarding the date fields of an event:
	/////////////////////////////////////////////////

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

	public function testGetUnregistrationDeadlineAsTimestamp() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);
		$this->assertEquals(
			$this->unregistrationDeadline,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);

		$this->fixture->setUnregistrationDeadline(0);
		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);
	}

	public function testNeedsRegistration() {
		$this->fixture->setAttendancesMax(10);
		$this->assertTrue(
			$this->fixture->needsRegistration()
		);

		$this->fixture->setAttendancesMax(0);
		$this->assertFalse(
			$this->fixture->needsRegistration()
		);
	}

	public function testHasUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);
		$this->assertTrue(
			$this->fixture->hasUnregistrationDeadline()
		);

		$this->fixture->setUnregistrationDeadline(0);
		$this->assertFalse(
			$this->fixture->hasUnregistrationDeadline()
		);
	}

	public function testGetUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->assertEquals(
			'01.01.2030',
			$this->fixture->getUnregistrationDeadline()
		);

		$this->fixture->setShowTimeOfUnregistrationDeadline(1);
		$this->assertEquals(
			'01.01.2030 10:00',
			$this->fixture->getUnregistrationDeadline()
		);

		$this->fixture->setUnregistrationDeadline(0);
		$this->assertEquals(
			'',
			$this->fixture->getUnregistrationDeadline()
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

	public function testIsUnregistrationPossibleWithNoDeadlineSet() {
		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->currentTimestamp + ONE_WEEK);
		$this->fixture->setAttendancesMax(10);

		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleWithGlobalDeadlineSet() {
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

	public function testIsUnregistrationPossibleWithEventDeadlineSet() {
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

	public function testIsUnregistrationPossibleWithBothDeadlinesSet() {
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

	public function testIsUnregistrationPossibleWithNoRegistrationNeeded() {
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->currentTimestamp + (6*ONE_DAY))
		);
		$this->fixture->setBeginDate(($this->currentTimestamp + ONE_WEEK));

		$this->assertTrue(
			$this->fixture->isUnregistrationPossible()
		);


		$this->fixture->setAttendancesMax(0);
		$this->assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	public function testIsUnregistrationPossibleWithPassedEventUnregistrationDeadlineSet() {
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

	public function testGetRegistrationQueueSize() {
		$this->fixture->setRegistrationQueueSize(10);
		$this->assertEquals(
			10,
			$this->fixture->getRegistrationQueueSize()
		);
	}

	public function testHasRegistrationQueueSize() {
		$this->fixture->setRegistrationQueueSize(5);
		$this->assertTrue(
			$this->fixture->hasRegistrationQueueSize()
		);

		$this->fixture->setRegistrationQueueSize(0);
		$this->assertFalse(
			$this->fixture->hasRegistrationQueueSize()
		);
	}

	public function testHasVacanciesOnUnregistrationQueue() {
		$this->fixture->setNumberOfAttendances(
			$this->fixture->getAttendancesMax()
		);
		$this->fixture->setRegistrationQueueSize(5);
		$this->assertTrue(
			$this->fixture->hasVacanciesOnRegistrationQueue()
		);

		$this->fixture->setRegistrationQueueSize(0);
		$this->assertFalse(
			$this->fixture->hasVacanciesOnRegistrationQueue()
		);
	}

	public function testGetVacanciesOnRegistrationQueue() {
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

	public function testGetAttendancesOnRegistrationQueue() {
		$this->fixture->setNumberOfAttendancesOnQueue(4);
		$this->assertEquals(
			4,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}


	///////////////////////////////////////////////////////////
	// Tests regarding the country field of the place records.
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


	///////////////////////////////////////////////////////////
	// Tests regarding the target groups.
	///////////////////////////////////////////////////////////

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


	///////////////////////////////////////////////////////////
	// Tests regarding the payment methods.
	///////////////////////////////////////////////////////////

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


	///////////////////////////////////////////////////////////
	// Tests regarding the organizing partners.
	///////////////////////////////////////////////////////////

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


	////////////////////////////////////
	// Tests regarding the categories.
	////////////////////////////////////

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


	////////////////////////////////////
	// Tests regarding the time-slots.
	////////////////////////////////////

	public function testGetTimeslotsAsArrayWithMarkersReturnsArraySortedByDate() {
		$firstTimeSlotUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $this->fixture->getUid(),
				'begin_date' => 200,
				'room' => 'Room1'
			)
		);
		$secondTimeSlotUid = $this->testingFramework->createRecord(
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


	///////////////////////////////////////////////////////////
	// Tests regarding the organizers.
	///////////////////////////////////////////////////////////

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
}

?>
