<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_OldModel_EventTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_seminarchild
	 */
	protected $fixture = NULL;

	/**
	 * @var tx_oelib_testingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var int
	 */
	protected $beginDate = 0;

	/**
	 * @var int
	 */
	protected $unregistrationDeadline = 0;

	/**
	 * @var int
	 */
	protected $now = 0;

	/**
	 * @var tx_seminars_FrontEnd_DefaultController
	 */
	protected $pi1 = NULL;

	/**
	 * @var int
	 */
	protected $placeRelationSorting = 1;

	protected function setUp() {
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang.xml');
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->now = $GLOBALS['SIM_EXEC_TIME'];
		$this->beginDate = ($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->unregistrationDeadline = ($this->now + tx_oelib_Time::SECONDS_PER_WEEK);

		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'deadline_unregistration' => $this->unregistrationDeadline,
				'attendees_min' => 5,
				'attendees_max' => 10,
				'object_type' => 0,
				'queue_size' => 0,
				'needs_registration' => 1,
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

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		tx_seminars_registrationmanager::purgeInstance();
	}


	/*
	 * Utility functions
	 */

	/**
	 * Creates a fake front end and a pi1 instance in $this->pi1.
	 *
	 * @param int $detailPageUid UID of the detail view page
	 *
	 * @return void
	 */
	private function createPi1($detailPageUid = 0) {
		$this->testingFramework->createFakeFrontEnd();

		$this->pi1 = new tx_seminars_FrontEnd_DefaultController();
		$this->pi1->init(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'detailPID' => $detailPageUid,
			)
		);
		$this->pi1->getTemplateCode();
	}

	/**
	 * Inserts a place record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array $placeData data of the place to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addPlaceRelation(array $placeData = array()) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_sites', $placeData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_place_mm',
			$this->fixture->getUid(), $uid, $this->placeRelationSorting
		);
		$this->placeRelationSorting++;
		$this->fixture->setNumberOfPlaces(
			$this->fixture->getNumberOfPlaces() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a target group record into the database and creates a relation to
	 * it from the fixture.
	 *
	 * @param array $targetGroupData data of the target group to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addTargetGroupRelation(array $targetGroupData = array()) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_target_groups', $targetGroupData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_target_groups_mm',
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
	 * @param array $paymentMethodData data of the payment method to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addPaymentMethodRelation(
		array $paymentMethodData = array()
	) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_payment_methods', $paymentMethodData
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_payment_methods_mm', $this->fixture->getUid(),
			$uid
		);
		$this->fixture->setNumberOfPaymentMethods(
			$this->fixture->getNumberOfPaymentMethods() + 1
		);

		return $uid;
	}

	/**
	 * Inserts an organizer record into the database and creates a relation to
	 * it from the fixture as a organizing partner.
	 *
	 * @param array $organizerData data of the organizer to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addOrganizingPartnerRelation(
		array $organizerData = array()
	) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', $organizerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizing_partners_mm',
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
	 * @param array $categoryData data of the category to add, may be empty
	 * @param int $sorting the sorting index of the category to add, must be >= 0
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addCategoryRelation(
		array $categoryData = array(), $sorting = 0
	) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_categories', $categoryData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm',
			$this->fixture->getUid(), $uid, $sorting
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
	 * @param array $organizerData data of the organizer to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addOrganizerRelation(array $organizerData = array()) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', $organizerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_organizers_mm',
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfOrganizers(
			$this->fixture->getNumberOfOrganizers() + 1
		);

		return $uid;
	}

	/**
	 * Inserts a speaker record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param array $speakerData data of the speaker to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addSpeakerRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm',
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
	 * @param array $speakerData data of the speaker to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addPartnerRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm_partners',
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
	 * @param array $speakerData data of the speaker to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addTutorRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm_tutors',
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
	 * @param array $speakerData data of the speaker to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addLeaderRelation($speakerData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_speakers', $speakerData
		);

		$this->testingFramework->createRelation(
			'tx_seminars_seminars_speakers_mm_leaders',
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
	 * @param array $eventTypeData data of the event type to add, may be empty
	 *
	 * @return int the UID of the created record, will be > 0
	 */
	private function addEventTypeRelation($eventTypeData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', $eventTypeData
		);

		$this->fixture->setEventType($uid);

		return $uid;
	}


	/*
	 * Tests for the utility functions
	 */

	/**
	 * @test
	 */
	public function createPi1CreatesFakeFrontEnd() {
		$GLOBALS['TSFE'] = NULL;

		$this->createPi1();

		self::assertNotNull(
			$GLOBALS['TSFE']
		);
	}

	/**
	 * @test
	 */
	public function createPi1CreatesPi1Instance() {
		$this->pi1 = NULL;

		$this->createPi1();

		self::assertTrue(
			$this->pi1 instanceof tx_seminars_FrontEnd_DefaultController
		);
	}

	/**
	 * @test
	 */
	public function addPlaceRelationReturnsUid() {
		$uid = $this->addPlaceRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addPlaceRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addPlaceRelation(array()),
			$this->addPlaceRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addPlaceRelationIncreasesTheNumberOfPlaces() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfPlaces()
		);

		$this->addPlaceRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfPlaces()
		);

		$this->addPlaceRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfPlaces()
		);
	}

	/**
	 * @test
	 */
	public function addPlaceRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_place_mm',
				'uid_local=' . $this->fixture->getUid()
			)
		);

		$this->addPlaceRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_place_mm',
				'uid_local=' . $this->fixture->getUid()
			)
		);

		$this->addPlaceRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_place_mm',
				'uid_local=' . $this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addCategoryRelationReturnsUid() {
		$uid = $this->addCategoryRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addCategoryRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addCategoryRelation(array()),
			$this->addCategoryRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addCategoryRelationIncreasesTheNumberOfCategories() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfCategories()
		);

		$this->addCategoryRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfCategories()
		);

		$this->addCategoryRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfCategories()
		);
	}

	/**
	 * @test
	 */
	public function addCategoryRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addCategoryRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addCategoryRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addCategoryRelationCanSetSortingInRelationTable() {
		$this->addCategoryRelation(array(), 42);
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_categories_mm',
				'uid_local=' . $this->fixture->getUid() . ' AND sorting=42'
			)
		);
	}

	/**
	 * @test
	 */
	public function addTargetGroupRelationReturnsUid() {
		self::assertTrue(
			$this->addTargetGroupRelation(array()) > 0
		);
	}

	/**
	 * @test
	 */
	public function addTargetGroupRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addTargetGroupRelation(array()),
			$this->addTargetGroupRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addTargetGroupRelationIncreasesTheNumberOfTargetGroups() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfTargetGroups()
		);

		$this->addTargetGroupRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfTargetGroups()
		);

		$this->addTargetGroupRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfTargetGroups()
		);
	}

	/**
	 * @test
	 */
	public function addTargetGroupRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_target_groups_mm',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addTargetGroupRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_target_groups_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addTargetGroupRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_target_groups_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addPaymentMethodRelationReturnsUid() {
		$uid = $this->addPaymentMethodRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addPaymentMethodRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addPaymentMethodRelation(array()),
			$this->addPaymentMethodRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addPaymentMethodRelationIncreasesTheNumberOfPaymentMethods() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfPaymentMethods()
		);

		$this->addPaymentMethodRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfPaymentMethods()
		);

		$this->addPaymentMethodRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function addOrganizingPartnerRelationReturnsUid() {
		$uid = $this->addOrganizingPartnerRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addOrganizingPartnerRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addOrganizingPartnerRelation(array()),
			$this->addOrganizingPartnerRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addOrganizingPartnerRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_organizing_partners_mm',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addOrganizingPartnerRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_organizing_partners_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addOrganizingPartnerRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_organizing_partners_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addOrganizerRelationReturnsUid() {
		$uid = $this->addOrganizerRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addOrganizerRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addOrganizerRelation(array()),
			$this->addOrganizerRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addOrganizerRelationIncreasesTheNumberOfOrganizers() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfOrganizers()
		);

		$this->addOrganizerRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfOrganizers()
		);

		$this->addOrganizerRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerRelationReturnsUid() {
		$uid = $this->addSpeakerRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addSpeakerRelation(array()),
			$this->addSpeakerRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addSpeakerRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addSpeakerRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addSpeakerRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addPartnerRelationReturnsUid() {
		$uid = $this->addPartnerRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addPartnerRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addPartnerRelation(array()),
			$this->addPartnerRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addPartnerRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_partners',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addPartnerRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_partners',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addPartnerRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_partners',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addTutorRelationReturnsUid() {
		$uid = $this->addTutorRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addTutorRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addTutorRelation(array()),
			$this->addTutorRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addTutorRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_tutors',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addTutorRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_tutors',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addTutorRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_tutors',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addLeaderRelationReturnsUid() {
		$uid = $this->addLeaderRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addLeaderRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addLeaderRelation(array()),
			$this->addLeaderRelation(array())
		);
	}

	/**
	 * @test
	 */
	public function addLeaderRelationCreatesRelations() {
		self::assertSame(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_leaders',
				'uid_local='.$this->fixture->getUid()
			)

		);

		$this->addLeaderRelation(array());
		self::assertSame(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_leaders',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addLeaderRelation(array());
		self::assertSame(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_seminars_speakers_mm_leaders',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}

	/**
	 * @test
	 */
	public function addEventTypeRelationReturnsUid() {
		$uid = $this->addEventTypeRelation(array());

		self::assertTrue(
			$uid > 0
		);
	}

	/**
	 * @test
	 */
	public function addEventTypeRelationCreatesNewUids() {
		self::assertNotSame(
			$this->addLeaderRelation(array()),
			$this->addLeaderRelation(array())
		);
	}


	/*
	 * Tests for some basic functionality
	 */

	/**
	 * @test
	 */
	public function isOk() {
		self::assertTrue(
			$this->fixture->isOk()
		);
	}


	/*
	 * Tests concerning getTitle
	 */

	/**
	 * @test
	 */
	public function getTitleForSingleEventReturnsTitle() {
		self::assertSame(
			'a test event',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForTopicReturnsTitle() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'a test topic',
			)
		);
		$topic = new tx_seminars_seminar($topicRecordUid);

		self::assertSame(
			'a test topic',
			$topic->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleForDateReturnsTopicTitle() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'title' => 'a test topic',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
				'title' => 'a test date',
			)
		);
		$date = new tx_seminars_seminar($dateRecordUid);

		self::assertSame(
			'a test topic',
			$date->getTitle()
		);
	}


	/*
	 * Tests regarding the ability to register for an event
	 */

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsTrueForEventWithFutureDate() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsTrueForEventWithFutureDateAndRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsFalseForPastEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsFalseForPastEventWithRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsFalseForCurrentlyRunningEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsFalseForCurrentlyRunningEventWithRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsFalseForEventWithoutDate() {
		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterIsTrueForEventWithoutDateAndRegistrationWithoutDateActivated() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForEventWithUnlimitedVacanciesReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setUnlimitedVacancies();

		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForCancelledEventReturnsFalse() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForEventWithoutNeedeRegistrationReturnsFalse() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(FALSE);

		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForFullyBookedEventReturnsFalse() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);

		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForEventWithRegistrationQueueAndNoRegularVacanciesReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);
		$this->fixture->setRegistrationQueue(TRUE);

		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForEventWithRegistrationQueueAndRegularVacanciesReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(5);
		$this->fixture->setRegistrationQueue(TRUE);

		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForEventWithRegistrationBeginInFutureReturnsFalse() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] + 20
		);

		self::assertFalse(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForEventWithRegistrationBeginInPastReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] - 20
		);

		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterForEventWithoutRegistrationBeginReturnsTrue() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(0);

		self::assertTrue(
			$this->fixture->canSomebodyRegister()
		);
	}


	/*
	 * Tests concerning canSomebodyRegisterMessage
	 */


	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithFutureDateReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		self::assertSame(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForPastEventReturnsSeminarRegistrationClosedMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);

		self::assertSame(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForPastEventWithRegistrationWithoutDateActivatedReturnsRegistrationDeadlineOverMessage() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 7200);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] - 3600);

		self::assertSame(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForCurrentlyRunningEventReturnsSeminarRegistrationClosesMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		self::assertSame(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForCurrentlyRunningEventWithRegistrationWithoutDateActivatedReturnsSeminarRegistrationClosesMessage() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);

		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] - 3600);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		self::assertSame(
			$this->fixture->translate('message_seminarRegistrationIsClosed'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithoutDateReturnsNoDateMessage() {
		self::assertSame(
			$this->fixture->translate('message_noDate'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithoutDateAndRegistrationWithoutDateActivatedReturnsEmptyString() {
		// Activates the configuration switch "canRegisterForEventsWithoutDate".
		$this->fixture->setAllowRegistrationForEventsWithoutDate(1);
		$this->fixture->setBeginDate(0);
		$this->fixture->setRegistrationDeadline(0);

		self::assertSame(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithUnlimitedVacanviesReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setUnlimitedVacancies();

		self::assertSame(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForCancelledEventReturnsSeminarCancelledMessage() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		self::assertSame(
			$this->fixture->translate('message_seminarCancelled'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithoutNeedeRegistrationReturnsNoRegistrationNecessaryMessage() {
		$this->fixture->setNeedsRegistration(FALSE);

		self::assertSame(
			$this->fixture->translate('message_noRegistrationNecessary'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForFullyBookedEventReturnsNoVacanciesMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);

		self::assertSame(
			$this->fixture->translate('message_noVacancies'),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForFullyBookedEventWithRegistrationQueueReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 3600);
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setNumberOfAttendances(10);
		$this->fixture->setRegistrationQueue(TRUE);

		self::assertSame(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithRegistrationBeginInFutureReturnsRegistrationOpensOnMessage() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] + 20
		);

		self::assertSame(
			sprintf(
				$this->fixture->translate('message_registrationOpensOn'),
				$this->fixture->getRegistrationBegin()
			),
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithRegistrationBeginInPastReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(
			$GLOBALS['SIM_EXEC_TIME'] - 20
		);

		self::assertSame(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}

	/**
	 * @test
	 */
	public function canSomebodyRegisterMessageForEventWithoutRegistrationBeginReturnsEmptyString() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME'] + 45);
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setRegistrationBeginDate(0);

		self::assertSame(
			'',
			$this->fixture->canSomebodyRegisterMessage()
		);
	}


	/*
	 * Tests regarding the language of an event
	 */

	/**
	 * @test
	 */
	public function getLanguageFromIsoCodeWithValidLanguage() {
		self::assertSame(
			'Deutsch',
			$this->fixture->getLanguageNameFromIsoCode('de')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageFromIsoCodeWithInvalidLanguage() {
		self::assertSame(
			'',
			$this->fixture->getLanguageNameFromIsoCode('xy')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageFromIsoCodeWithVeryInvalidLanguage() {
		self::assertSame(
			'',
			$this->fixture->getLanguageNameFromIsoCode('foobar')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageFromIsoCodeWithEmptyLanguage() {
		self::assertSame(
			'',
			$this->fixture->getLanguageNameFromIsoCode('')
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithLanguageReturnsTrue() {
		$this->fixture->setLanguage('de');
		self::assertTrue(
			$this->fixture->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithNoLanguageReturnsFalse() {
		$this->fixture->setLanguage('');
		self::assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageNameWithDefaultLanguageOnSingleEvent() {
		$this->fixture->setLanguage('de');
		self::assertSame(
			'Deutsch',
			$this->fixture->getLanguageName()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageNameWithValidLanguageOnSingleEvent() {
		$this->fixture->setLanguage('en');
		self::assertSame(
			'English',
			$this->fixture->getLanguageName()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageNameWithInvalidLanguageOnSingleEvent() {
		$this->fixture->setLanguage('xy');
		self::assertSame(
			'',
			$this->fixture->getLanguageName()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageNameWithNoLanguageOnSingleEvent() {
		$this->fixture->setLanguage('');
		self::assertSame(
			'',
			$this->fixture->getLanguageName()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageNameOnDateRecord() {
		// This was an issue with bug #1518 and #1517.
		// The method getLanguage() needs to return the language from the date
		// record instead of the topic record.
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('language' => 'de')
		);

		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
				'language' => 'it'
			)
		);

		$seminar = new tx_seminars_seminar($dateRecordUid);

		self::assertSame(
			'Italiano',
			$seminar->getLanguageName()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageOnSingleRecordThatWasADateRecord() {
		// This test comes from bug 1518 and covers the following situation:
		// We have an event record that has the topic field set as it was a
		// date record. But then it was switched to be a single event record.
		// In that case, the language from the single event record must be
		// returned, not the one from the referenced topic record.

		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('language' => 'de')
		);

		$singleRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'topic' => $topicRecordUid,
				'language' => 'it'
			)
		);

		$seminar = new tx_seminars_seminar($singleRecordUid);

		self::assertSame(
			'Italiano',
			$seminar->getLanguageName()
		);
	}


	/*
	 * Tests regarding the date fields of an event
	 */

	/**
	 * @test
	 */
	public function getBeginDateAsTimestampIsInitiallyZero() {
		self::assertSame(
			0,
			$this->fixture->getBeginDateAsTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getBeginDateAsTimestamp() {
		$this->fixture->setBeginDate($this->beginDate);
		self::assertSame(
			$this->beginDate,
			$this->fixture->getBeginDateAsTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function hasBeginDateIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasBeginDate()
		);
	}

	/**
	 * @test
	 */
	public function hasBeginDate() {
		$this->fixture->setBeginDate($this->beginDate);
		self::assertTrue(
			$this->fixture->hasBeginDate()
		);
	}

	/**
	 * @test
	 */
	public function getEndDateAsTimestampIsInitiallyZero() {
		self::assertSame(
			0,
			$this->fixture->getEndDateAsTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getEndDateAsTimestamp () {
		$this->fixture->setEndDate($this->beginDate);
		self::assertSame(
			$this->beginDate,
			$this->fixture->getEndDateAsTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function hasEndDateIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasEndDate()
		);
	}

	/**
	 * @test
	 */
	public function hasEndDate () {
		$this->fixture->setEndDate($this->beginDate);
		self::assertTrue(
			$this->fixture->hasEndDate()
		);
	}


	/*
	 * Tests regarding the registration.
	 */

	/**
	 * @test
	 */
	public function needsRegistrationForNeedsRegistrationTrueReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);

		self::assertTrue(
			$this->fixture->needsRegistration()
		);
	}

	/**
	 * @test
	 */
	public function needsRegistrationForNeedsRegistrationFalseReturnsFalse() {
		$this->fixture->setNeedsRegistration(FALSE);

		self::assertFalse(
			$this->fixture->needsRegistration()
		);
	}


	/*
	 * Tests concerning hasUnlimitedVacancies
	 */

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForNeedsRegistrationTrueAndMaxAttendeesZeroReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(0);

		self::assertTrue(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForNeedsRegistrationTrueAndMaxAttendeesOneReturnsFalse() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(1);

		self::assertFalse(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForNeedsRegistrationFalseAndMaxAttendeesZeroReturnsFalse() {
		$this->fixture->setNeedsRegistration(FALSE);
		$this->fixture->setAttendancesMax(0);

		self::assertFalse(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForNeedsRegistrationFalseAndMaxAttendeesOneReturnsFalse() {
		$this->fixture->setNeedsRegistration(FALSE);
		$this->fixture->setAttendancesMax(1);

		self::assertFalse(
			$this->fixture->hasUnlimitedVacancies()
		);
	}


	/*
	 * Tests concerning isFull
	 */

	/**
	 * @test
	 */
	public function isFullForUnlimitedVacanciesAndZeroAttendancesReturnsFalse() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(0);

		self::assertFalse(
			$this->fixture->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForUnlimitedVacanciesAndOneAttendanceReturnsFalse() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(1);

		self::assertFalse(
			$this->fixture->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForOneVacancyAndNoAttendancesReturnsFalse() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setNumberOfAttendances(0);

		self::assertFalse(
			$this->fixture->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForOneVacancyAndOneAttendanceReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setNumberOfAttendances(1);

		self::assertTrue(
			$this->fixture->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForTwoVacanciesAndOneAttendanceReturnsFalse() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNumberOfAttendances(1);

		self::assertFalse(
			$this->fixture->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForTwoVacanciesAndTwoAttendancesReturnsTrue() {
		$this->fixture->setNeedsRegistration(TRUE);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNumberOfAttendances(2);

		self::assertTrue(
			$this->fixture->isFull()
		);
	}


	/*
	 * Tests regarding the unregistration and the queue
	 */

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineAsTimestampForNonZero() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);

		self::assertSame(
			$this->unregistrationDeadline,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineAsTimestampForZero() {
		$this->fixture->setUnregistrationDeadline(0);

		self::assertSame(
			0,
			$this->fixture->getUnregistrationDeadlineAsTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineWithoutTimeForNonZero() {
		$this->fixture->setUnregistrationDeadline(1893488400);

		self::assertSame(
			'01.01.2030',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function getNonUnregistrationDeadlineWithTimeForZero() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(1);

		self::assertSame(
			'01.01.2030 10:00',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineIsEmptyForZero() {
		$this->fixture->setUnregistrationDeadline(0);

		self::assertSame(
			'',
			$this->fixture->getUnregistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasUnregistrationDeadlineIsTrueForNonZeroDeadline() {
		$this->fixture->setUnregistrationDeadline($this->unregistrationDeadline);

		self::assertTrue(
			$this->fixture->hasUnregistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasUnregistrationDeadlineIsFalseForZeroDeadline() {
		$this->fixture->setUnregistrationDeadline(0);

		self::assertFalse(
			$this->fixture->hasUnregistrationDeadline()
		);
	}


	/*
	 * Tests concerning isUnregistrationPossible()
	 */

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithNoBeginDateAndNoDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithGlobalDeadlineInFutureReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithGlobalDeadlineInPastReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(5);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_DAY);
		$this->fixture->setAttendancesMax(10);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithGlobalDeadlineReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithFutureEventDeadlineReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithPastEventDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithFutureEventDeadlineReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithPastEventDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(0);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithBothDeadlinesInFutureReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setAttendancesMax(10);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithBothDeadlinesInPastReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(2);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_DAY);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInFutureReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithoutBeginDateAndWithBothDeadlinesInPastReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setBeginDate(0);
		$this->fixture->setAttendancesMax(10);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithPassedEventUnregistrationDeadlineReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setBeginDate($this->now + 2 * tx_oelib_Time::SECONDS_PER_DAY);
		$this->fixture->setUnregistrationDeadline(
			$this->now - tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setAttendancesMax(10);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleWithNonZeroAttendancesMaxReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setAttendancesMax(10);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleForNeedsRegistrationFalseReturnsFalse() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setNeedsRegistration(FALSE);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleForEventWithEmptyWaitingListAndAllowUnregistrationWithEmptyWaitingListReturnsTrue() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setAttendancesMax(10);
		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			$this->now + tx_oelib_Time::SECONDS_PER_DAY
		);
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}


	/*
	 * Tests concerning getUnregistrationDeadlineFromModelAndConfiguration
	 */

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndNoUnregistrationDeadlineReturnsZero() {
		$this->fixture->setBeginDate(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		self::assertSame(
			0,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlineSetInEventReturnsUnregistrationDeadline() {
		$this->fixture->setBeginDate(0);
		$this->fixture->setUnregistrationDeadline($this->now);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		self::assertSame(
			$this->now,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoBeginDateAndUnregistrationDeadlinInEventAndUnregistrationDeadlineSetInConfigurationReturnsZero() {
		$this->fixture->setBeginDate(0);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline($this->now);

		self::assertSame(
			0,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlineSetInEventReturnsThisDeadline() {
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));
		$this->fixture->setUnregistrationDeadline($this->now);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		self::assertSame(
			$this->now,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndNoDeadlineConfigurationSetReturnsZero() {
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline(0);

		self::assertSame(
			0,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForNoUnregistrationDeadlineSetInEventAndDeadlineConfigurationSetReturnsCalculatedDeadline() {
		$this->fixture->setBeginDate($this->now + tx_oelib_Time::SECONDS_PER_WEEK);
		$this->fixture->setUnregistrationDeadline(0);
		$this->fixture->setGlobalUnregistrationDeadline(1);

		self::assertSame(
			$this->now + tx_oelib_Time::SECONDS_PER_WEEK - tx_oelib_Time::SECONDS_PER_DAY,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineFromModelAndConfigurationForUnregistrationDeadlinesSetInEventAndConfigurationReturnsEventsDeadline() {
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));
		$this->fixture->setUnregistrationDeadline($this->now);
		$this->fixture->setGlobalUnregistrationDeadline(1);

		self::assertSame(
			$this->now,
			$this->fixture->getUnregistrationDeadlineFromModelAndConfiguration()
		);
	}


	/*
	 * Tests concerning hasRegistrationQueue
	 */

	/**
	 * @test
	 */
	public function hasRegistrationQueueWithQueueReturnsTrue() {
		$this->fixture->setRegistrationQueue(TRUE);

		self::assertTrue(
			$this->fixture->hasRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationQueueWithoutQueueReturnsFalse() {
			$this->fixture->setRegistrationQueue(FALSE);

		self::assertFalse(
			$this->fixture->hasRegistrationQueue()
		);
	}


	/*
	 * Tests concerning getAttendancesOnRegistrationQueue
	 */

	/**
	 * @test
	 */
	public function getAttendancesOnRegistrationQueueIsInitiallyZero() {
		self::assertSame(
			0,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function getAttendancesOnRegistrationQueueForNonEmptyRegistrationQueue() {
		$this->fixture->setNumberOfAttendancesOnQueue(4);
		self::assertSame(
			4,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendancesOnRegistrationQueueIsFalseForNoRegistrations() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(FALSE);
		$this->fixture->setNumberOfAttendances(0);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		self::assertFalse(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendancesOnRegistrationQueueIsFalseForRegularRegistrationsOnly() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(FALSE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		self::assertFalse(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendancesOnRegistrationQueueIsTrueForQueueRegistrations() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(1);

		self::assertTrue(
			$this->fixture->hasAttendancesOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleIsTrueWithNonEmptyQueueByDefault() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(1);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->now + (6*tx_oelib_Time::SECONDS_PER_DAY))
		);
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleIsFalseWithEmptyQueueByDefault() {
		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->now + (6*tx_oelib_Time::SECONDS_PER_DAY))
		);
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));

		self::assertFalse(
			$this->fixture->isUnregistrationPossible()
		);
	}

	/**
	 * @test
	 */
	public function isUnregistrationPossibleIsTrueWithEmptyQueueIfAllowedByConfiguration() {
		$this->fixture->setAllowUnregistrationWithEmptyWaitingList(TRUE);

		$this->fixture->setAttendancesMax(1);
		$this->fixture->setRegistrationQueue(TRUE);
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setNumberOfAttendancesOnQueue(0);

		$this->fixture->setGlobalUnregistrationDeadline(1);
		$this->fixture->setUnregistrationDeadline(
			($this->now + (6*tx_oelib_Time::SECONDS_PER_DAY))
		);
		$this->fixture->setBeginDate(($this->now + tx_oelib_Time::SECONDS_PER_WEEK));

		self::assertTrue(
			$this->fixture->isUnregistrationPossible()
		);
	}


	/*
	 * Tests regarding the country field of the place records
	 */

	/**
	 * @test
	 */
	public function getPlacesWithCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		self::assertSame(
			array('ch'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		self::assertSame(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithCountryWithInvalidCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		self::assertSame(
			array('xy'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithCountryWithNoPlace() {
		self::assertSame(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithCountryWithDeletedPlace() {
		$this->addPlaceRelation(
			array(
				'country' => 'at',
				'deleted' => 1
			)
		);

		self::assertSame(
			array(),
			$this->fixture->getPlacesWithCountry()
		);
	}

	/**
	 * @test
	 */
	public function getPlacesWithCountryWithMultipleCountries() {
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

		self::assertSame(
			array('ch', 'de'),
			$this->fixture->getPlacesWithCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		self::assertTrue(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		self::assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithInvalicCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		// We expect a TRUE even if the country code is invalid! See function's
		// comment on this.
		self::assertTrue(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithNoPlace() {
		self::assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithMultipleCountries() {
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

		self::assertTrue(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		self::assertSame(
			'Schweiz',
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithNoCountry() {
		$this->addPlaceRelation(
			array(
				'country' => ''
			)
		);

		self::assertSame(
			'',
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithInvalidCountry() {
		$this->addPlaceRelation(
			array(
				'country' => 'xy'
			)
		);

		self::assertSame(
			'',
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithMultipleCountries() {
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

		self::assertSame(
			'Schweiz, Deutschland',
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithNoPlace() {
		self::assertSame(
			'',
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryNameFromIsoCode() {
		self::assertSame(
			'Schweiz',
			$this->fixture->getCountryNameFromIsoCode('ch')
		);

		self::assertSame(
			'',
			$this->fixture->getCountryNameFromIsoCode('xy')
		);

		self::assertSame(
			'',
			$this->fixture->getCountryNameFromIsoCode('')
		);
	}

	/**
	 * @test
	 */
	public function getRelatedMmRecordUidsWithNoPlace() {
		self::assertSame(
			array(),
			$this->fixture->getRelatedMmRecordUids('tx_seminars_seminars_place_mm')
		);
	}

	/**
	 * @test
	 */
	public function getRelatedMmRecordUidsWithOnePlace() {
		$uid = $this->addPlaceRelation(
			array(
				'country' => 'ch'
			)
		);

		self::assertSame(
			array($uid),
			$this->fixture->getRelatedMmRecordUids('tx_seminars_seminars_place_mm')
		);
	}

	/**
	 * @test
	 */
	public function getRelatedMmRecordUidsWithTwoPlaces() {
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
			'tx_seminars_seminars_place_mm'
		);
		sort($result);
		self::assertSame(
			array($uid1, $uid2),
			$result
		);
	}


	/*
	 * Tests regarding the target groups
	 */

	/**
	 * @test
	 */
	public function hasTargetGroupsIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasTargetGroups()
		);
	}

	/**
	 * @test
	 */
	public function hasTargetGroups() {
		$this->addTargetGroupRelation(array());

		self::assertTrue(
			$this->fixture->hasTargetGroups()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupNamesWithNoTargetGroup() {
		self::assertSame(
			'',
			$this->fixture->getTargetGroupNames()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupNamesWithSingleTargetGroup() {
		$title = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $title));

		self::assertSame(
			$title,
			$this->fixture->getTargetGroupNames()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupNamesWithMultipleTargetGroups() {
		$titleTargetGroup1 = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup1));

		$titleTargetGroup2 = 'TEST target group 2';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup2));

		self::assertSame(
			$titleTargetGroup1.', '.$titleTargetGroup2,
			$this->fixture->getTargetGroupNames()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsAsArrayWithNoTargetGroups() {
		self::assertSame(
			array(),
			$this->fixture->getTargetGroupsAsArray()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsAsArrayWithSingleTargetGroup() {
		$title = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $title));

		self::assertSame(
			array($title),
			$this->fixture->getTargetGroupsAsArray()
		);
	}

	/**
	 * @test
	 */
	public function getTargetGroupsAsArrayWithMultipleTargetGroups() {
		$titleTargetGroup1 = 'TEST target group 1';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup1));

		$titleTargetGroup2 = 'TEST target group 2';
		$this->addTargetGroupRelation(array('title' => $titleTargetGroup2));

		self::assertSame(
			array($titleTargetGroup1, $titleTargetGroup2),
			$this->fixture->getTargetGroupsAsArray()
		);
	}


	/*
	 * Tests regarding the payment methods
	 */

	/**
	 * @test
	 */
	public function hasPaymentMethodsReturnsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function canHaveOnePaymentMethod() {
		$this->addPaymentMethodRelation(array());

		self::assertTrue(
			$this->fixture->hasPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainWithNoPaymentMethodReturnsAnEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainWithSinglePaymentMethodReturnsASinglePaymentMethod() {
		$title = 'Test title';
		$this->addPaymentMethodRelation(array('title' => $title));

		self::assertContains(
			$title,
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainWithMultiplePaymentMethodsReturnsMultiplePaymentMethods() {
		$firstTitle = 'Payment Method 1';
		$secondTitle = 'Payment Method 2';
		$this->addPaymentMethodRelation(array('title' => $firstTitle));
		$this->addPaymentMethodRelation(array('title' => $secondTitle));

		self::assertContains(
			$firstTitle,
			$this->fixture->getPaymentMethodsPlain()
		);
		self::assertContains(
			$secondTitle,
			$this->fixture->getPaymentMethodsPlain()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithoutPaymentMethodsReturnsAnEmptyArray() {
		self::assertSame(
			array(),
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithOnePaymentMethodReturnsOnePaymentMethod() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method'));

		self::assertSame(
			array('Payment Method'),
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethods() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		self::assertSame(
			array('Payment Method 1', 'Payment Method 2'),
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsWithTwoPaymentMethodsReturnsTwoPaymentMethodsSorted() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));

		self::assertSame(
			array('Payment Method 2', 'Payment Method 1'),
			$this->fixture->getPaymentMethods()
		);
	}


	/*
	 * Tests concerning getPaymentMethodsPlainShort
	 */

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainShortWithNoPaymentMethodReturnsAnEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainShortWithSinglePaymentMethodReturnsASinglePaymentMethod() {
		$title = 'Test title';
		$this->addPaymentMethodRelation(array('title' => $title));

		self::assertContains(
			$title,
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainShortWithMultiplePaymentMethodsReturnsMultiplePaymentMethods() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		self::assertContains(
			'Payment Method 1',
			$this->fixture->getPaymentMethodsPlainShort()
		);
		self::assertContains(
			'Payment Method 2',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainShortSeparatesMultiplePaymentMethodsWithLineFeeds() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		self::assertContains(
			'Payment Method 1' . LF . 'Payment Method 2',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}

	/**
	 * @test
	 */
	public function getPaymentMethodsPlainShortDoesNotSeparateMultiplePaymentMethodsWithCarriageReturnsAndLineFeeds() {
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 1'));
		$this->addPaymentMethodRelation(array('title' => 'Payment Method 2'));

		self::assertNotContains(
			'Payment Method 1' . CRLF . 'Payment Method 2',
			$this->fixture->getPaymentMethodsPlainShort()
		);
	}


	/*
	 * Tests concerning getSinglePaymentMethodPlain
	 */

	/**
	 * @test
	 */
	public function getSinglePaymentMethodPlainWithInvalidPaymentMethodUidReturnsAnEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getSinglePaymentMethodPlain(0)
		);
	}

	/**
	 * @test
	 */
	public function getSinglePaymentMethodPlainWithValidPaymentMethodUidWithoutDescriptionReturnsTitle() {
		$title = 'Test payment method';
		$uid = $this->addPaymentMethodRelation(array('title' => $title));

		self::assertSame(
			$title . LF . LF,
			$this->fixture->getSinglePaymentMethodPlain($uid)
		);
	}

	/**
	 * @test
	 */
	public function getSinglePaymentMethodPlainWithValidPaymentMethodUidWithDescriptionReturnsTitleAndDescription() {
		$title = 'Test payment method';
		$description = 'some description';
		$uid = $this->addPaymentMethodRelation(array('title' => $title, 'description' => $description));

		self::assertSame(
			$title . ': ' . $description  . LF . LF,
			$this->fixture->getSinglePaymentMethodPlain($uid)
		);
	}

	/**
	 * @test
	 */
	public function getSinglePaymentMethodPlainWithNonExistentPaymentMethodUidReturnsAnEmptyString() {
		$uid = $this->addPaymentMethodRelation(array());

		self::assertSame(
			'',
			$this->fixture->getSinglePaymentMethodPlain($uid + 1)
		);
	}

	/**
	 * @test
	 */
	public function getSinglePaymentMethodShortWithInvalidPaymentMethodUidReturnsAnEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getSinglePaymentMethodShort(0)
		);
	}

	/**
	 * @test
	 */
	public function getSinglePaymentMethodShortWithValidPaymentMethodUidReturnsTheTitleOfThePaymentMethod() {
		$title = 'Test payment method';
		$uid = $this->addPaymentMethodRelation(array('title' => $title));

		self::assertContains(
			$title,
			$this->fixture->getSinglePaymentMethodShort($uid)
		);
	}

	/**
	 * @test
	 */
	public function getSinglePaymentMethodShortWithNonExistentPaymentMethodUidReturnsAnEmptyString() {
		$uid = $this->addPaymentMethodRelation(array());

		self::assertSame(
			'',
			$this->fixture->getSinglePaymentMethodShort($uid + 1)
		);
	}


	/*
	 * Tests regarding the event type
	 */

	/**
	 * @test
	 */
	public function setEventTypeThrowsExceptionForNegativeArgument() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'$eventType must be >= 0.'
		);

		$this->fixture->setEventType(-1);
	}

	/**
	 * @test
	 */
	public function setEventTypeIsAllowedWithZero() {
		$this->fixture->setEventType(0);
	}

	/**
	 * @test
	 */
	public function setEventTypeIsAllowedWithPositiveInteger() {
		$this->fixture->setEventType(1);
	}

	/**
	 * @test
	 */
	public function hasEventTypeInitiallyReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasEventType()
		);
	}

	/**
	 * @test
	 */
	public function hasEventTypeReturnsTrueIfSingleEventHasNonZeroEventType() {
		$this->fixture->setEventType(
			$this->testingFramework->createRecord('tx_seminars_event_types')
		);

		self::assertTrue(
			$this->fixture->hasEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeReturnsEmptyStringForSingleEventWithoutType() {
		self::assertSame(
			'',
			$this->fixture->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeReturnsTitleOfRelatedEventTypeForSingleEvent() {
		$this->fixture->setEventType(
			$this->testingFramework->createRecord(
				'tx_seminars_event_types', array('title' => 'foo type')
			)
		);

		self::assertSame(
			'foo type',
			$this->fixture->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForDateRecordReturnsTitleOfEventTypeFromTopicRecord() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'event_type' => $this->testingFramework->createRecord(
					'tx_seminars_event_types', array('title' => 'foo type')
				),
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);
		$seminar = new tx_seminars_seminar($dateRecordUid);

		self::assertSame(
			'foo type',
			$seminar->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeForTopicRecordReturnsTitleOfRelatedEventType() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'event_type' => $this->testingFramework->createRecord(
					'tx_seminars_event_types', array('title' => 'foo type')
				),
			)
		);
		$seminar = new tx_seminars_seminar($topicRecordUid);

		self::assertSame(
			'foo type',
			$seminar->getEventType()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeUidReturnsUidFromTopicRecord() {
		// This test comes from bug #1515.
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'event_type' => 99999
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
				'event_type' => 199999
			)
		);
		$seminar = new tx_seminars_seminar($dateRecordUid);

		self::assertSame(
			99999,
			$seminar->getEventTypeUid()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeUidInitiallyReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getEventTypeUid()
		);
	}

	/**
	 * @test
	 */
	public function getEventTypeUidWithEventTypeReturnsEventTypeUid() {
		$eventTypeUid = $this->addEventTypeRelation(array());
		self::assertSame(
			$eventTypeUid,
			$this->fixture->getEventTypeUid()
		);
	}


	/*
	 * Tests regarding the organizing partners
	 */

	/**
	 * @test
	 */
	public function hasOrganizingPartnersReturnsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasOrganizingPartners()
		);
	}

	/**
	 * @test
	 */
	public function canHaveOneOrganizingPartner() {
		$this->addOrganizingPartnerRelation(array());

		self::assertTrue(
			$this->fixture->hasOrganizingPartners()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfOrganizingPartnersWithNoOrganizingPartnerReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfOrganizingPartnersWithSingleOrganizingPartnerReturnsOne() {
		$this->addOrganizingPartnerRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfOrganizingPartnersWithMultipleOrganizingPartnersReturnsTwo() {
		$this->addOrganizingPartnerRelation(array());
		$this->addOrganizingPartnerRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfOrganizingPartners()
		);
	}


	/*
	 * Tests regarding the categories
	 */

	/**
	 * @test
	 */
	public function initiallyHasNoCategories() {
		self::assertFalse(
			$this->fixture->hasCategories()
		);
		self::assertSame(
			0,
			$this->fixture->getNumberOfCategories()
		);
		self::assertSame(
			array(),
			$this->fixture->getCategories()
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesCanReturnOneCategory() {
		$categoryUid = $this->addCategoryRelation(array('title' => 'Test'));

		self::assertTrue(
			$this->fixture->hasCategories()
		);
		self::assertSame(
			1,
			$this->fixture->getNumberOfCategories()
		);
		self::assertSame(
			array($categoryUid => array('title' => 'Test', 'icon' => '')),
			$this->fixture->getCategories()
		);
	}

	/**
	 * @test
	 */
	public function canHaveTwoCategories() {
		$categoryUid1 = $this->addCategoryRelation(array('title' => 'Test 1'));
		$categoryUid2 = $this->addCategoryRelation(array('title' => 'Test 2'));

		self::assertTrue(
			$this->fixture->hasCategories()
		);
		self::assertSame(
			2,
			$this->fixture->getNumberOfCategories()
		);

		$categories = $this->fixture->getCategories();

		self::assertSame(
			2,
			count($categories)
		);
		self::assertSame(
			'Test 1',
			$categories[$categoryUid1]['title']
		);
		self::assertSame(
			'Test 2',
			$categories[$categoryUid2]['title']
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesReturnsIconOfCategory() {
		$categoryUid = $this->addCategoryRelation(
			array(
				'title' => 'Test 1',
				'icon' => 'foo.gif',
			)
		);

		$categories = $this->fixture->getCategories();

		self::assertSame(
			'foo.gif',
			$categories[$categoryUid]['icon']
		);
	}

	/**
	 * @test
	 */
	public function getCategoriesReturnsCategoriesOrderedBySorting() {
		$categoryUid1 = $this->addCategoryRelation(array('title' => 'Test 1'), 2);
		$categoryUid2 = $this->addCategoryRelation(array('title' => 'Test 2'), 1);

		self::assertTrue(
			$this->fixture->hasCategories()
		);

		self::assertSame(
			array(
				$categoryUid2 => array('title' => 'Test 2', 'icon' => ''),
				$categoryUid1 => array('title' => 'Test 1', 'icon' => ''),
			),
			$this->fixture->getCategories()
		);
	}


	/*
	 * Tests regarding the time slots
	 */

	/**
	 * @test
	 */
	public function getTimeslotsAsArrayWithMarkersReturnsArraySortedByDate() {
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'seminar' => $this->fixture->getUid(),
				'begin_date' => 200,
				'room' => 'Room1'
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'seminar' => $this->fixture->getUid(),
				'begin_date' => 100,
				'room' => 'Room2'
			)
		);

		$timeSlotsWithMarkers = $this->fixture->getTimeslotsAsArrayWithMarkers();
		self::assertSame(
			$timeSlotsWithMarkers[0]['room'],
			'Room2'
		);
		self::assertSame(
			$timeSlotsWithMarkers[1]['room'],
			'Room1'
		);
	}


	/*
	 * Tests regarding the organizers
	 */

	/**
	 * @test
	 */
	public function hasOrganizersReturnsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasOrganizers()
		);
	}

	/**
	 * @test
	 */
	public function hasOrganizersReturnsFalseForStringInOrganizersField() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'organizers' => 'foo',
			)
		);
		$fixture = new tx_seminars_seminarchild($eventUid);
		$hasOrganizers = $fixture->hasOrganizers();

		self::assertFalse(
			$hasOrganizers
		);
	}

	/**
	 * @test
	 */
	public function canHaveOneOrganizer() {
		$this->addOrganizerRelation(array());

		self::assertTrue(
			$this->fixture->hasOrganizers()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfOrganizersWithNoOrganizerReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfOrganizersWithSingleOrganizerReturnsOne() {
		$this->addOrganizerRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfOrganizers()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfOrganizersWithMultipleOrganizersReturnsTwo() {
		$this->addOrganizerRelation(array());
		$this->addOrganizerRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfOrganizers()
		);
	}


	/*
	 * Tests concerning getOrganizers
	 */

	/**
	 * @test
	 */
	public function getOrganizersWithNoOrganizersReturnsEmptyString() {
		$this->createPi1();

		self::assertSame(
			'',
			$this->fixture->getOrganizers($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersForOneOrganizerReturnsOrganizerName() {
		$this->createPi1();
		$this->addOrganizerRelation(array('title' => 'foo'));

		self::assertContains(
			'foo',
			$this->fixture->getOrganizers($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersForOneOrganizerWithHomepageReturnsOrganizerLinkedToOrganizersHomepage() {
		$this->createPi1();
		$this->addOrganizerRelation(
			array(
				'title' => 'foo',
				'homepage' => 'www.bar.com',
			)
		);

		self::assertContains(
			'<a href="http://www.bar.com',
			$this->fixture->getOrganizers($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersWithTwoOrganizersReturnsBothOrganizerNames() {
		$this->createPi1();
		$this->addOrganizerRelation(array('title' => 'foo'));
		$this->addOrganizerRelation(array('title' => 'bar'));

		$organizers = $this->fixture->getOrganizers($this->pi1);

		self::assertContains(
			'foo',
			$organizers
		);
		self::assertContains(
			'bar',
			$organizers
		);
	}


	/*
	 * Tests concerning getOrganizersRaw
	 */

	/**
	 * @test
	 */
	public function getOrganizersRawWithNoOrganizersReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getOrganizersRaw()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersRawWithSingleOrganizerWithoutHomepageReturnsSingleOrganizer() {
		$organizer = array(
			'title' => 'test organizer 1',
			'homepage' => ''
		);
		$this->addOrganizerRelation($organizer);
		self::assertSame(
			$organizer['title'],
			$this->fixture->getOrganizersRaw()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersRawWithSingleOrganizerWithHomepageReturnsSingleOrganizerWithHomepage() {
		$organizer = array(
			'title' => 'test organizer 1',
			'homepage' => 'test homepage 1'
		);
		$this->addOrganizerRelation($organizer);
		self::assertSame(
			$organizer['title'].', '.$organizer['homepage'],
			$this->fixture->getOrganizersRaw()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersRawForTwoOrganizersWithoutHomepageReturnsTwoOrganizers() {
		$this->addOrganizerRelation(
			array('title' => 'test organizer 1','homepage' => '')
		);
		$this->addOrganizerRelation(
			array('title' => 'test organizer 2','homepage' => '')
		);

		self::assertContains(
			'test organizer 1',
			$this->fixture->getOrganizersRaw()
		);
		self::assertContains(
			'test organizer 2',
			$this->fixture->getOrganizersRaw()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersRawForTwoOrganizersWithHomepageReturnsTwoOrganizersWithHomepage() {
		$this->addOrganizerRelation(
			array(
				'title' => 'test organizer 1',
				'homepage' => 'test homepage 1',
			)
		);
		$this->addOrganizerRelation(
			array(
				'title' => 'test organizer 2',
				'homepage' => 'test homepage 2'
			)
		);

		self::assertContains(
			'test homepage 1',
			$this->fixture->getOrganizersRaw()
		);
		self::assertContains(
			'test homepage 2',
			$this->fixture->getOrganizersRaw()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersRawSeparatesMultipleOrganizersWithLineFeeds() {
		$this->addOrganizerRelation(array('title' => 'test organizer 1'));
		$this->addOrganizerRelation(array('title' => 'test organizer 2'));

		self::assertContains(
			'test organizer 1' . LF . 'test organizer 2',
			$this->fixture->getOrganizersRaw()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersRawDoesNotSeparateMultipleOrganizersWithCarriageReturnsAndLineFeeds() {
		$this->addOrganizerRelation(array('title' => 'test organizer 1'));
		$this->addOrganizerRelation(array('title' => 'test organizer 2'));

		self::assertNotContains(
			'test organizer 1' . CRLF . 'test organizer 2',
			$this->fixture->getOrganizersRaw()
		);
	}


	/*
	 * Tests concerning getOrganizersNameAndEmail
	 */

	/**
	 * @test
	 */
	public function getOrganizersNameAndEmailWithNoOrganizersReturnsEmptyString() {
		self::assertSame(
			array(),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersNameAndEmailWithSingleOrganizerReturnsSingleOrganizer() {
		$organizer = array(
			'title' => 'test organizer',
			'email' => 'test@organizer.org'
		);
		$this->addOrganizerRelation($organizer);
		self::assertSame(
			array('"'.$organizer['title'].'" <'.$organizer['email'].'>'),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersNameAndEmailWithMultipleOrganizersReturnsTwoOrganizers() {
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
		self::assertSame(
			array(
				'"'.$firstOrganizer['title'].'" <'.$firstOrganizer['email'].'>',
				'"'.$secondOrganizer['title'].'" <'.$secondOrganizer['email'].'>'
			),
			$this->fixture->getOrganizersNameAndEmail()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersEmailWithNoOrganizersReturnsEmptyString() {
		self::assertSame(
			array(),
			$this->fixture->getOrganizersEmail()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersEmailWithSingleOrganizerReturnsSingleOrganizer() {
		$organizer = array('email' => 'test@organizer.org');
		$this->addOrganizerRelation($organizer);
		self::assertSame(
			array($organizer['email']),
			$this->fixture->getOrganizersEmail()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersEmailWithMultipleOrganizersReturnsTwoOrganizers() {
		$firstOrganizer = array('email' => 'test1@organizer.org');
		$secondOrganizer = array('email' => 'test2@organizer.org');
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		self::assertSame(
			array($firstOrganizer['email'], $secondOrganizer['email']),
			$this->fixture->getOrganizersEmail()
		);
	}


	/*
	 * Tests concerning getOrganizersFooter
	 */

	/**
	 * @test
	 */
	public function getOrganizersFootersWithNoOrganizersReturnsEmptyArray() {
		self::assertSame(
			array(),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithSingleOrganizerReturnsSingleOrganizerFooter() {
		$organizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation($organizer);
		self::assertSame(
			array($organizer['email_footer']),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithMultipleOrganizersReturnsTwoOrganizerFooters() {
		$firstOrganizer = array('email_footer' => 'test email footer');
		$secondOrganizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation($firstOrganizer);
		$this->addOrganizerRelation($secondOrganizer);
		self::assertSame(
			array(
				$firstOrganizer['email_footer'],
				$secondOrganizer['email_footer']
			),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithSingleOrganizerWithoutEMailFooterReturnsEmptyArray() {
		$this->addOrganizerRelation();

		self::assertSame(
			array(),
			$this->fixture->getOrganizersFooter()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizersFootersWithTwoOrganizersOneWithFooterOneWithoutrReturnsOnlyTheNonEmptyFooter() {
		$secondOrganizer = array('email_footer' => 'test email footer');
		$this->addOrganizerRelation();
		$this->addOrganizerRelation($secondOrganizer);
		self::assertSame(
			array($secondOrganizer['email_footer']),
			$this->fixture->getOrganizersFooter()
		);
	}


	/*
	 * Tests concerning getFirstOrganizer
	 */

	/**
	 * @test
	 */
	public function getFirstOrganizerWithNoOrganizersReturnsNull() {
		self::assertNull(
			$this->fixture->getFirstOrganizer()
		);
	}

	/**
	 * @test
	 */
	public function getFirstOrganizerForOneOrganizerReturnsThatOrganizer() {
		$organizerUid = $this->addOrganizerRelation(array());

		self::assertSame(
			$organizerUid,
			$this->fixture->getFirstOrganizer()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getFirstOrganizerForTwoOrganizerReturnsFirstOrganizer() {
		$firstOrganizerUid = $this->addOrganizerRelation(array());
		$this->addOrganizerRelation(array());

		self::assertSame(
			$firstOrganizerUid,
			$this->fixture->getFirstOrganizer()->getUid()
		);
	}


	/*
	 * Tests concerning getAttendancesPid
	 */

	/**
	 * @test
	 */
	public function getAttendancesPidWithNoOrganizerReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getAttendancesPid()
		);
	}

	/**
	 * @test
	 */
	public function getAttendancesPidWithSingleOrganizerReturnsPid() {
		$this->addOrganizerRelation(array('attendances_pid' => 99));
		self::assertSame(
			99,
			$this->fixture->getAttendancesPid()
		);
	}

	/**
	 * @test
	 */
	public function getAttendancesPidWithMultipleOrganizerReturnsFirstPid() {
		$this->addOrganizerRelation(array('attendances_pid' => 99));
		$this->addOrganizerRelation(array('attendances_pid' => 66));
		self::assertSame(
			99,
			$this->fixture->getAttendancesPid()
		);
	}


	/*
	 * Tests regarding getOrganizerBag().
	 */

	/**
	 * @test
	 */
	public function getOrganizerBagWithoutOrganizersThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'There are no organizers related to this event.'
		);

		$this->fixture->getOrganizerBag();
	}

	/**
	 * @test
	 */
	public function getOrganizerBagWithOrganizerReturnsOrganizerBag() {
		$this->addOrganizerRelation();

		self::assertTrue(
			$this->fixture->getOrganizerBag() instanceof tx_seminars_Bag_Organizer
		);
	}


	/*
	 * Tests regarding the speakers
	 */

	/**
	 * @test
	 */
	public function getNumberOfSpeakersWithNoSpeakerReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfSpeakersWithSingleSpeakerReturnsOne() {
		$this->addSpeakerRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfSpeakersWithMultipleSpeakersReturnsTwo() {
		$this->addSpeakerRelation(array());
		$this->addSpeakerRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfSpeakers()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfPartnersWithNoPartnerReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfPartners()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfPartnersWithSinglePartnerReturnsOne() {
		$this->addPartnerRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfPartners()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfPartnersWithMultiplePartnersReturnsTwo() {
		$this->addPartnerRelation(array());
		$this->addPartnerRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfPartners()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfTutorsWithNoTutorReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfTutors()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfTutorsWithSingleTutorReturnsOne() {
		$this->addTutorRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfTutors()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfTutorsWithMultipleTutorsReturnsTwo() {
		$this->addTutorRelation(array());
		$this->addTutorRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfTutors()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfLeadersWithNoLeaderReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getNumberOfLeaders()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfLeadersWithSingleLeaderReturnsOne() {
		$this->addLeaderRelation(array());
		self::assertSame(
			1,
			$this->fixture->getNumberOfLeaders()
		);
	}

	/**
	 * @test
	 */
	public function getNumberOfLeadersWithMultipleLeadersReturnsTwo() {
		$this->addLeaderRelation(array());
		$this->addLeaderRelation(array());
		self::assertSame(
			2,
			$this->fixture->getNumberOfLeaders()
		);
	}

	/**
	 * @test
	 */
	public function hasSpeakersOfTypeIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasSpeakersOfType('speakers')
		);
		self::assertFalse(
			$this->fixture->hasSpeakersOfType('partners')
		);
		self::assertFalse(
			$this->fixture->hasSpeakersOfType('tutors')
		);
		self::assertFalse(
			$this->fixture->hasSpeakersOfType('leaders')
		);
	}

	/**
	 * @test
	 */
	public function hasSpeakersOfTypeWithSingleSpeakerOfTypeReturnsTrue() {
		$this->addSpeakerRelation(array());
		self::assertTrue(
			$this->fixture->hasSpeakersOfType('speakers')
		);

		$this->addPartnerRelation(array());
		self::assertTrue(
			$this->fixture->hasSpeakersOfType('partners')
		);

		$this->addTutorRelation(array());
		self::assertTrue(
			$this->fixture->hasSpeakersOfType('tutors')
		);

		$this->addLeaderRelation(array());
		self::assertTrue(
			$this->fixture->hasSpeakersOfType('leaders')
		);
	}

	/**
	 * @test
	 */
	public function hasSpeakersIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasSpeakers()
		);
	}

	/**
	 * @test
	 */
	public function canHaveOneSpeaker() {
		$this->addSpeakerRelation(array());
		self::assertTrue(
			$this->fixture->hasSpeakers()
		);
	}

	/**
	 * @test
	 */
	public function hasPartnersIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasPartners()
		);
	}

	/**
	 * @test
	 */
	public function canHaveOnePartner() {
		$this->addPartnerRelation(array());
		self::assertTrue(
			$this->fixture->hasPartners()
		);
	}

	/**
	 * @test
	 */
	public function hasTutorsIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasTutors()
		);
	}

	/**
	 * @test
	 */
	public function canHaveOneTutor() {
		$this->addTutorRelation(array());
		self::assertTrue(
			$this->fixture->hasTutors()
		);
	}

	/**
	 * @test
	 */
	public function hasLeadersIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasLeaders()
		);
	}

	/**
	 * @test
	 */
	public function canHaveOneLeader() {
		$this->addLeaderRelation(array());
		self::assertTrue(
			$this->fixture->hasLeaders()
		);
	}


	/*
	 * Tests concerning getSpeakersWithDescriptionRaw
	 */

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawWithNoSpeakersReturnsAnEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawReturnsTitleOfSpeaker() {
		$this->addSpeakerRelation(array('title' => 'test speaker'));

		self::assertContains(
			'test speaker',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationReturnsSpeakerWithOrganization() {
		$this->addSpeakerRelation(array('organization' => 'test organization'));

		self::assertContains(
			'test organization',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForSpeakerWithHomepageReturnsSpeakerWithHomepage() {
		$this->addSpeakerRelation(array('homepage' => 'test homepage'));

		self::assertContains(
			'test homepage',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationAndHomepageReturnsSpeakerWithOrganizationAndHomepage() {
		$this->addSpeakerRelation(
			array(
				'organization' => 'test organization',
				'homepage' => 'test homepage',
			)
		);

		self::assertRegExp(
			'/test organization.*test homepage/',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForSpeakerWithDescriptionReturnsSpeakerWithDescription() {
		$this->addSpeakerRelation(array('description' => 'test description'));

		self::assertContains(
			'test description',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForSpeakerWithOrganizationAndDescriptionReturnsOrganizationAndDescription() {
		$this->addSpeakerRelation(
			array(
				'organization' => 'foo',
				'description' => 'bar',
			)
		);
		self::assertRegExp(
			'/foo.*bar/s',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForSpeakerWithHomepageAndDescriptionReturnsHomepageAndDescription() {
		$this->addSpeakerRelation(
			array(
				'homepage' => 'test homepage',
				'description' =>  'test description',
			)
		);

		self::assertRegExp(
			'/test homepage.*test description/s',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForTwoSpeakersReturnsTwoSpeakers() {
		$this->addSpeakerRelation(array('title' => 'test speaker 1'));
		$this->addSpeakerRelation(array('title' => 'test speaker 2'));

		self::assertContains(
			'test speaker 1',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
		self::assertContains(
			'test speaker 2',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawForTwoSpeakersWithOrganizationReturnsTwoSpeakersWithOrganization() {
		$this->addSpeakerRelation(
			array('organization' => 'test organization 1')
		);
		$this->addSpeakerRelation(
			array('organization' => 'test organization 2')
		);

		self::assertContains(
			'test organization 1',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
		self::assertContains(
			'test organization 2',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawOnlyReturnsSpeakersOfGivenType() {
		$this->addSpeakerRelation(array('title' => 'test speaker'));
		$this->addPartnerRelation(array('title' => 'test partner'));

		self::assertNotContains(
			'test partner',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypePartner() {
		$this->addPartnerRelation(array('title' => 'test partner'));

		self::assertContains(
			'test partner',
			$this->fixture->getSpeakersWithDescriptionRaw('partners')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypeLeaders() {
		$this->addLeaderRelation(array('title' => 'test leader'));

		self::assertContains(
			'test leader',
			$this->fixture->getSpeakersWithDescriptionRaw('leaders')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawCanReturnSpeakersOfTypeTutors() {
		$this->addTutorRelation(array('title' => 'test tutor'));

		self::assertContains(
			'test tutor',
			$this->fixture->getSpeakersWithDescriptionRaw('tutors')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawSeparatesMultipleSpeakersWithLineFeeds() {
		$this->addSpeakerRelation(array('title' => 'foo'));
		$this->addSpeakerRelation(array('title' => 'bar'));

		self::assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawDoesNotSeparateMultipleSpeakersWithCarriageReturnsAndLineFeeds() {
		$this->addSpeakerRelation(array('title' => 'foo'));
		$this->addSpeakerRelation(array('title' => 'bar'));

		self::assertNotContains(
			'foo' . CRLF . 'bar',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawDoesNotSeparateSpeakersDescriptionAndTitleWithCarriageReturnsAndLineFeeds() {
		$this->addSpeakerRelation(
			array(
				'title' => 'foo',
				'description' => 'bar'
			)
		);

		self::assertNotRegExp(
			'/foo'. CRLF . 'bar/',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithDescriptionRawSeparatesSpeakersDescriptionAndTitleWithLineFeeds() {
		$this->addSpeakerRelation(
			array(
				'title' => 'foo',
				'description' => 'bar'
			)
		);

		self::assertRegExp(
			'/foo'. LF . 'bar/',
			$this->fixture->getSpeakersWithDescriptionRaw('speakers')
		);
	}


	/*
	 * Tests concerning getSpeakersShort
	 */

	/**
	 * @test
	 */
	public function getSpeakersShortWithNoSpeakersReturnsAnEmptyString() {
		$this->createPi1();

		self::assertSame(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);
		self::assertSame(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'partners')
		);
		self::assertSame(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'tutors')
		);
		self::assertSame(
			'',
			$this->fixture->getSpeakersShort($this->pi1, 'leaders')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersShortWithSingleSpeakersReturnsSingleSpeaker() {
		$this->createPi1();
		$speaker = array('title' => 'test speaker');

		$this->addSpeakerRelation($speaker);
		self::assertSame(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);

		$this->addPartnerRelation($speaker);
		self::assertSame(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'partners')
		);

		$this->addTutorRelation($speaker);
		self::assertSame(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'tutors')
		);

		$this->addLeaderRelation($speaker);
		self::assertSame(
			$speaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'leaders')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersShortWithMultipleSpeakersReturnsTwoSpeakers() {
		$firstSpeaker = array('title' => 'test speaker 1');
		$secondSpeaker = array('title' => 'test speaker 2');

		$this->addSpeakerRelation($firstSpeaker);
		$this->addSpeakerRelation($secondSpeaker);
		$this->createPi1();
		self::assertSame(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);

		$this->addPartnerRelation($firstSpeaker);
		$this->addPartnerRelation($secondSpeaker);
		self::assertSame(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'partners')
		);

		$this->addTutorRelation($firstSpeaker);
		$this->addTutorRelation($secondSpeaker);
		self::assertSame(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'tutors')
		);

		$this->addLeaderRelation($firstSpeaker);
		$this->addLeaderRelation($secondSpeaker);
		self::assertSame(
			$firstSpeaker['title'].', '.$secondSpeaker['title'],
			$this->fixture->getSpeakersShort($this->pi1, 'leaders')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersShortReturnsSpeakerLinkedToSpeakerHomepage() {
		$speakerWithLink = array(
			'title' => 'test speaker',
			'homepage' => 'http://www.foo.com',
		);
		$this->addSpeakerRelation($speakerWithLink);
		$this->createPi1();

		self::assertRegExp(
			'/href="http:\/\/www.foo.com".*>test speaker/',
			$this->fixture->getSpeakersShort($this->pi1, 'speakers')
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersForSpeakerWithoutHomepageReturnsSpeakerNameWithoutLinkTag() {
		$speaker = array(
			'title' => 'test speaker',
		);

		$this->addSpeakerRelation($speaker);
		$this->createPi1();

		$shortSpeakerOutput
			= $this->fixture->getSpeakersShort($this->pi1, 'speakers');

		self::assertContains(
			'test speaker',
			$shortSpeakerOutput
		);
		self::assertNotContains(
			'<a',
			$shortSpeakerOutput
		);
	}


	/*
	 * Test concerning the collision check
	 */

	/**
	 * @test
	 */
	public function eventsWithTheExactSameDateCollide() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		self::assertTrue(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	/**
	 * @test
	 */
	public function collidingEventsDoNotCollideIfCollisionSkipIsEnabledForAllEvents() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end,
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid,
			)
		);

		$this->fixture->setConfigurationValue(
			'skipRegistrationCollisionCheck', TRUE
		);

		self::assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	/**
	 * @test
	 */
	public function collidingEventsDoNoCollideIfCollisionSkipIsEnabledForThisEvent() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);
		$this->fixture->setSkipCollisionCheck(TRUE);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		self::assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}

	/**
	 * @test
	 */
	public function collidingEventsDoNoCollideIfCollisionSkipIsEnabledForAnotherEvent() {
		$frontEndUserUid = $this->testingFramework->createFrontEndUser();

		$begin = $GLOBALS['SIM_EXEC_TIME'];
		$end = $begin + 1000;

		$this->fixture->setBeginDate($begin);
		$this->fixture->setEndDate($end);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'begin_date' => $begin,
				'end_date' => $end,
				'skip_collision_check' => 1
			)
		);

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $eventUid,
				'user' => $frontEndUserUid
			)
		);

		self::assertFalse(
			$this->fixture->isUserBlocked($frontEndUserUid)
		);
	}


	/*
	 * Tests for the icons
	 */

	/**
	 * @test
	 */
	public function usesCorrectIconForSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);

		self::assertContains(
			'EventComplete.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);

		self::assertContains(
			'EventTopic.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForDateRecord() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);

		self::assertContains(
			'EventDate.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForHiddenSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);
		$this->fixture->setHidden(TRUE);

		self::assertContains(
			'EventComplete__h.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForHiddenTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);
		$this->fixture->setHidden(TRUE);

		self::assertContains(
			'EventTopic__h.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForHiddenDate() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);
		$this->fixture->setHidden(TRUE);

		self::assertContains(
			'EventDate__h.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForVisibleTimedSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		self::assertContains(
			'EventComplete.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForVisibleTimedTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		self::assertContains(
			'EventTopic.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForVisibleTimedDate() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);
		$this->fixture->setRecordStartTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		self::assertContains(
			'EventDate.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForExpiredSingleEvent() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_COMPLETE);
		$this->fixture->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		self::assertContains(
			'EventComplete__t.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForExpiredTimedTopic() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_TOPIC);
		$this->fixture->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		self::assertContains(
			'EventTopic__t.',
			$this->fixture->getRecordIcon()
		);
	}

	/**
	 * @test
	 */
	public function usesCorrectIconForExpiredTimedDate() {
		$this->fixture->setRecordType(tx_seminars_Model_Event::TYPE_DATE);
		$this->fixture->setRecordEndTime($GLOBALS['SIM_EXEC_TIME'] - 1000);

		self::assertContains(
			'EventDate__t.',
			$this->fixture->getRecordIcon()
		);
	}

	/*
	 * Tests for hasSeparateDetailsPage
	 */

	/**
	 * @test
	 */
	public function hasSeparateDetailsPageIsFalseByDefault() {
		self::assertFalse(
			$this->fixture->hasSeparateDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function hasSeparateDetailsPageReturnsTrueForInternalSeparateDetailsPage() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		self::assertTrue(
			$event->hasSeparateDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function hasSeparateDetailsPageReturnsTrueForExternalSeparateDetailsPage() {
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => 'www.test.com'
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		self::assertTrue(
			$event->hasSeparateDetailsPage()
		);
	}


	/*
	 * Tests for getDetailsPage
	 */

	/**
	 * @test
	 */
	public function getDetailsPageForNoSeparateDetailsPageSetReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function getDetailsPageForInternalSeparateDetailsPageSetReturnsThisPage() {
		$detailsPageUid = $this->testingFramework->createFrontEndPage();
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => $detailsPageUid,
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		self::assertSame(
			(string) $detailsPageUid,
			$event->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function getDetailsPageForExternalSeparateDetailsPageSetReturnsThisPage() {
		$externalUrl = 'www.test.com';
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'details_page' => $externalUrl,
			)
		);
		$event = new tx_seminars_seminarchild($eventUid);

		self::assertSame(
			$externalUrl,
			$event->getDetailsPage()
		);
	}


	/*
	 * Tests concerning getPlaceWithDetails
	 */

	/**
	 * @test
	 */
	public function getPlaceWithDetailsReturnsWillBeAnnouncedForNoPlace() {
		$this->createPi1();
		self::assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsTitleOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place'));

		self::assertContains(
			'a place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsTitleOfAllRelatedPlaces() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		self::assertContains(
			'a place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
		self::assertContains(
			'another place',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsAddressOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(
			array('title' => 'a place', 'address' => 'a street')
		);

		self::assertContains(
			'a street',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsForNonEmptyZipAndCityContainsZip() {
		$this->createPi1();
		$this->addPlaceRelation(
			array('title' => 'a place', 'zip' => '12345', 'city' => 'Hamm')
		);

		self::assertContains(
			'12345',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsForNonEmptyZipAndEmptyCityNotContainsZip() {
		$this->createPi1();
		$this->addPlaceRelation(
			array('title' => 'a place', 'zip' => '12345', 'city' => '')
		);

		self::assertNotContains(
			'12345',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsCityOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place', 'city' => 'Emden'));

		self::assertContains(
			'Emden',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsCountryOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('title' => 'a place', 'country' => 'de'));

		self::assertContains(
			'Deutschland',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsHomepageLinkOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('homepage' => 'www.test.com'));

		self::assertContains(
			' href="http://www.test.com',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsContainsDirectionsOfOnePlace() {
		$this->createPi1();
		$this->addPlaceRelation(array('directions' => 'Turn right.'));

		self::assertContains(
			'Turn right.',
			$this->fixture->getPlaceWithDetails($this->pi1)
		);
	}


	/*
	 * Tests concerning getPlaceWithDetailsRaw
	 */

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawReturnsWillBeAnnouncedForNoPlace() {
		$this->testingFramework->createFakeFrontEnd();

		self::assertContains(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsTitleOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place'));

		self::assertContains(
			'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsTitleOfAllRelatedPlaces() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		self::assertContains(
			'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
		self::assertContains(
			'another place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsAddressOfOnePlace() {
		$this->addPlaceRelation(
			array('title' => 'a place', 'address' => 'a street')
		);

		self::assertContains(
			'a street',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsCityOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place', 'city' => 'Emden'));

		self::assertContains(
			'Emden',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsCountryOfOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place', 'country' => 'de'));

		self::assertContains(
			'Deutschland',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsHomepageUrlOfOnePlace() {
		$this->addPlaceRelation(array('homepage' => 'www.test.com'));

		self::assertContains(
			'www.test.com',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawContainsDirectionsOfOnePlace() {
		$this->addPlaceRelation(array('directions' => 'Turn right.'));

		self::assertContains(
			'Turn right.',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawSeparatesMultiplePlacesWithLineFeeds() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		self::assertContains(
			'a place' . LF . 'another place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithDetailsRawDoesNotSeparateMultiplePlacesWithCarriageReturnsAndLineFeeds() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		self::assertNotContains(
			'another place' . CRLF . 'a place',
			$this->fixture->getPlaceWithDetailsRaw()
		);
	}


	/*
	 * Tests for getPlaceShort
	 */

	/**
	 * @test
	 */
	public function getPlaceShortReturnsWillBeAnnouncedForNoPlaces() {
		self::assertSame(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceShort()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceShortReturnsPlaceNameForOnePlace() {
		$this->addPlaceRelation(array('title' => 'a place'));

		self::assertSame(
			'a place',
			$this->fixture->getPlaceShort()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceShortReturnsPlaceNamesWithCommaForTwoPlaces() {
		$this->addPlaceRelation(array('title' => 'a place'));
		$this->addPlaceRelation(array('title' => 'another place'));

		self::assertContains(
			'a place',
			$this->fixture->getPlaceShort()
		);
		self::assertContains(
			', ',
			$this->fixture->getPlaceShort()
		);
		self::assertContains(
			'another place',
			$this->fixture->getPlaceShort()
		);
	}


	/*
	 * Tests concerning getPlaces
	 */

	/**
	 * @test
	 */
	public function getPlacesForEventWithNoPlacesReturnsEmptyList() {
		self::assertTrue(
			$this->fixture->getPlaces() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getPlacesForSeminarWithOnePlacesReturnsListWithPlaceModel() {
		$this->addPlaceRelation();

		self::assertTrue(
			$this->fixture->getPlaces()->first() instanceof tx_seminars_Model_place
		);
	}

	/**
	 * @test
	 */
	public function getPlacesForSeminarWithOnePlacesReturnsListWithOnePlace() {
		$this->addPlaceRelation();

		self::assertSame(
			1,
			$this->fixture->getPlaces()->count()
		);
	}


	/*
	 * Tests for attached files
	 */

	/**
	 * @test
	 */
	public function hasAttachedFilesInitiallyReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesWithOneAttachedFileReturnsTrue() {
		$this->fixture->setAttachedFiles('test.file');

		self::assertTrue(
			$this->fixture->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesWithTwoAttachedFilesReturnsTrue() {
		$this->fixture->setAttachedFiles('test.file,test_02.file');

		self::assertTrue(
			$this->fixture->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesForDateWithoutFilesAndTopicWithOneFileReturnsTrue() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => 'test.file',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => '',
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		self::assertTrue(
			$eventDate->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesForDateWithoutFilesAndTopicWithoutFilesReturnsFalse() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => '',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => '',
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		self::assertFalse(
			$eventDate->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesInitiallyReturnsAnEmptyArray() {
		$this->createPi1();

		self::assertSame(
			array(),
			$this->fixture->getAttachedFiles($this->pi1)
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithOneSetAttachedFileReturnsAttachedFileAsArrayWithCorrectFileSize() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		self::assertContains(
			'uploads/tx_seminars/' . $dummyFileName,
			$attachedFiles[0]['name']
		);

		self::assertSame(
			GeneralUtility::formatSize(filesize($dummyFile)),
			$attachedFiles[0]['size']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesForDateWithFileAndTopicWithoutFileReturnsFileFromDate() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => '',
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => $dummyFileName,
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$attachedFiles = $eventDate->getAttachedFiles($this->pi1);

		self::assertContains(
			$dummyFileName,
			$attachedFiles[0]['name']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesForDateWithoutFileAndTopicWithFileReturnsFileFromTopic() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);

		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => $dummyFileName,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => '',
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$attachedFiles = $eventDate->getAttachedFiles($this->pi1);

		self::assertContains(
			$dummyFileName,
			$attachedFiles[0]['name']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesForDateWithFileAndTopicWithFileReturnsFilesFromTopicAndThenDate() {
		$this->createPi1();

		$topicDummyFile = $this->testingFramework->createDummyFile();
		$topicDummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($topicDummyFile);
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'attached_files' => $topicDummyFileName,
			)
		);

		$dateDummyFile = $this->testingFramework->createDummyFile();
		$dateDummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dateDummyFile);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'attached_files' => $dateDummyFileName,
				'topic' => $topicRecordUid,
			)
		);
		$eventDate = new tx_seminars_seminar($dateRecordUid);

		$attachedFiles = $eventDate->getAttachedFiles($this->pi1);

		self::assertContains(
			$topicDummyFileName,
			$attachedFiles[0]['name']
		);
		self::assertContains(
			$dateDummyFileName,
			$attachedFiles[1]['name']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithTwoSetAttachedFilesReturnsAttachedFilesAsArrayWithCorrectFileSize() {
		$this->createPi1();
		$dummyFile1 = $this->testingFramework->createDummyFile();
		$dummyFileName1 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile1);
		$dummyFile2 = $this->testingFramework->createDummyFile();
		$dummyFileName2 =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile2);
		$this->fixture->setAttachedFiles($dummyFileName1 . ',' . $dummyFileName2);

		GeneralUtility::writeFile($dummyFile2, 'Test');

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		self::assertContains(
			'uploads/tx_seminars/' . $dummyFileName1,
			$attachedFiles[0]['name']
		);

		self::assertSame(
			GeneralUtility::formatSize(filesize($dummyFile1)),
			$attachedFiles[0]['size']
		);

		self::assertContains(
			'uploads/tx_seminars/' . $dummyFileName2,
			$attachedFiles[1]['name']
		);

		self::assertSame(
			GeneralUtility::formatSize(filesize($dummyFile2)),
			$attachedFiles[1]['size']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithAttachedFileWithFileEndingReturnsFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile();
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		self::assertSame(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithAttachedFileWithoutFileEndingReturnsFileTypeNone() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		self::assertSame(
			'none',
			$attachedFiles[0]['type']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithAttachedFileWithDotInFileNameReturnsCorrectFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test.test.txt');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		self::assertSame(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithAttachedFileWithFileNameStartingWithADotReturnsFileType() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('.txt');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		self::assertSame(
			'txt',
			$attachedFiles[0]['type']
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithAttachedFileWithFileNameEndingWithADotReturnsFileTypeNone() {
		$this->createPi1();
		$dummyFile = $this->testingFramework->createDummyFile('test.');
		$dummyFileName =
			$this->testingFramework->getPathRelativeToUploadDirectory($dummyFile);
		$this->fixture->setAttachedFiles($dummyFileName);

		$attachedFiles = $this->fixture->getAttachedFiles($this->pi1);

		self::assertSame(
			'none',
			$attachedFiles[0]['type']
		);
	}


	/*
	 * Tests concerning isOwnerFeUser
	 */

	/**
	 * @test
	 */
	public function isOwnerFeUserForNoOwnerReturnsFalse() {
		self::assertFalse(
			$this->fixture->isOwnerFeUser()
		);
	}

	/**
	 * @test
	 */
	public function isOwnerFeUserForLoggedInUserOtherThanOwnerReturnsFalse() {
		$this->testingFramework->createFakeFrontEnd();
		$userUid = $this->testingFramework->createAndLoginFrontEndUser();

		$this->fixture->setOwnerUid($userUid + 1);

		self::assertFalse(
			$this->fixture->isOwnerFeUser()
		);
	}

	/**
	 * @test
	 */
	public function isOwnerFeUserForLoggedInUserOtherThanOwnerReturnsTrue() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		self::assertTrue(
			$this->fixture->isOwnerFeUser()
		);
	}


	/*
	 * Tests concerning getOwner
	 */

	/**
	 * @test
	 */
	public function getOwnerForExistingOwnerReturnsFrontEndUserInstance() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		self::assertTrue(
			$this->fixture->getOwner() instanceof tx_oelib_Model_FrontEndUser
		);
	}

	/**
	 * @test
	 */
	public function getOwnerForExistingOwnerReturnsUserWithOwnersUid() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		self::assertSame(
			$ownerUid,
			$this->fixture->getOwner()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getOwnerForNoOwnerReturnsNull() {
		self::assertNull(
			$this->fixture->getOwner()
		);
	}


	/*
	 * Tests concerning hasOwner
	 */

	/**
	 * @test
	 */
	public function hasOwnerForExistingOwnerReturnsTrue() {
		$this->testingFramework->createFakeFrontEnd();
		$ownerUid = $this->testingFramework->createAndLoginFrontEndUser();
		$this->fixture->setOwnerUid($ownerUid);

		self::assertTrue(
			$this->fixture->hasOwner()
		);
	}

	/**
	 * @test
	 */
	public function hasOwnerForNoOwnerReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasOwner()
		);
	}


	/*
	 * Tests concerning getVacanciesString
	 */

	/**
	 * @test
	 */
	public function getVacanciesStringForCanceledEventWithVacanciesReturnsEmptyString() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(5);
		$this->fixture->setNumberOfAttendances(0);
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		self::assertSame(
			'',
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringWithoutRegistrationNeededReturnsEmptyString() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setNeedsRegistration(FALSE);

		self::assertSame(
			'',
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForNonZeroVacanciesBelowThresholdReturnsNumberOfVacancies() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(5);
		$this->fixture->setNumberOfAttendances(0);

		self::assertSame(
			'5',
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForNoVancanciesReturnsFullyBooked() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(5);
		$this->fixture->setNumberOfAttendances(5);

		self::assertSame(
			$this->fixture->translate('message_fullyBooked'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForVacanciesGreaterThanThresholdReturnsEnough() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 10);
		$this->fixture->setAttendancesMax(42);
		$this->fixture->setNumberOfAttendances(0);

		self::assertSame(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForVacanciesEqualToThresholdReturnsEnough() {
		$this->fixture->setConfigurationValue('showVacanciesThreshold', 42);
		$this->fixture->setAttendancesMax(42);
		$this->fixture->setNumberOfAttendances(0);

		self::assertSame(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForUnlimitedVacanciesAndZeroRegistrationsReturnsEnough() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(0);

		self::assertSame(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesStringForUnlimitedVacanciesAndOneRegistrationReturnsEnough() {
		$this->fixture->setUnlimitedVacancies();
		$this->fixture->setNumberOfAttendances(1);

		self::assertSame(
			$this->fixture->translate('message_enough'),
			$this->fixture->getVacanciesString()
		);
	}


	/*
	 * Tests concerning updatePlaceRelationsFromTimeSlots
	 */

	/**
	 * @test
	 */
	public function updatePlaceRelationsForSeminarWithoutPlacesRelatesPlaceFromTimeslotToSeminar() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(1);

		self::assertSame(
			1,
			$this->fixture->updatePlaceRelationsFromTimeSlots()
		);
	}

	/**
	 * @test
	 */
	public function updatePlaceRelationsForTwoTimeslotsWithPlacesReturnsTwo() {
		$placeUid1 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid1,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'your house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid2,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(2);

		self::assertSame(
			2,
			$this->fixture->updatePlaceRelationsFromTimeSlots()
		);
	}

	/**
	 * @test
	 */
	public function updatePlaceRelationsForSeminarWithoutPlacesCanRelateTwoPlacesFromTimeslotsToSeminar() {
		$placeUid1 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid1,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'your house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUid2,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(2);
		$this->fixture->setNumberOfPlaces(2);
		$this->fixture->updatePlaceRelationsFromTimeSlots();

		self::assertContains(
			'my house',
			$this->fixture->getPlaceShort()
		);
		self::assertContains(
			'your house',
			$this->fixture->getPlaceShort()
		);
	}

	/**
	 * @test
	 */
	public function updatePlaceRelationsOverwritesSeminarPlaceWithNonEmptyPlaceFromTimeslot() {
		$this->addPlaceRelation(array('title' => 'your house'));

		$placeUidInTimeSlot = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'my house')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'place' => $placeUidInTimeSlot,
				'seminar' => $this->fixture->getUid(),
			)
		);
		$this->fixture->setNumberOfTimeSlots(1);

		$this->fixture->updatePlaceRelationsFromTimeSlots();

		self::assertSame(
			'my house',
			$this->fixture->getPlaceShort()
		);
	}

	/**
	 * @test
	 */
	public function updatePlaceRelationsForSeminarWithOnePlaceAndTimeSlotWithNoPlaceReturnsOne() {
		$this->addPlaceRelation(array('title' => 'your house'));

		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array('seminar' => $this->fixture->getUid())
		);
		$this->fixture->setNumberOfTimeSlots(1);

		self::assertSame(
			1,
			$this->fixture->updatePlaceRelationsFromTimeSlots()
		);
	}

	/**
	 * @test
	 */
	public function updatePlaceRelationsForTimeSlotsWithNoPlaceNotOverwritesSeminarPlace() {
		$this->addPlaceRelation(array('title' => 'your house'));

		$this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array('seminar' => $this->fixture->getUid())
		);
		$this->fixture->setNumberOfTimeSlots(1);

		self::assertSame(
			'your house',
			$this->fixture->getPlaceShort()
		);
	}


	/*
	 * Tests for the getImage function
	 */

	/**
	 * @test
	 */
	public function getImageForNonEmptyImageReturnsImageFileName() {
		$this->fixture->setImage('foo.gif');

		self::assertSame(
			'foo.gif',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForEmptyImageReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getImage()
		);
	}


	/*
	 * Tests for the hasImage function
	 */

	/**
	 * @test
	 */
	public function hasImageForNonEmptyImageReturnsTrue() {
		$this->fixture->setImage('foo.gif');

		self::assertTrue(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForEmptyImageReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasImage()
		);
	}


	/*
	 * Tests for getLanguageKeySuffixForType
	 */

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeReturnsSpeakerType() {
		$this->addLeaderRelation(array());

		self::assertContains(
			'leaders_',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForMaleSpeakerReturnsMaleMarkerPart() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		self::assertContains(
			'_male',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForFemaleSpeakerReturnsFemaleMarkerPart() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);

		self::assertContains(
			'_female',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForSingleSpeakerWithoutGenderReturnsUnknownMarkerPart() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_UNKNOWN)
		);

		self::assertContains(
			'_unknown',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForSingleSpeakerReturnsSingleMarkerPart() {
		$this->addSpeakerRelation(array());

		self::assertContains(
			'_single_',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForMultipleSpeakersWithoutGenderReturnsSpeakerType() {
		$this->addSpeakerRelation(array());
		$this->addSpeakerRelation(array());

		self::assertContains(
			'speakers',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForMultipleMaleSpeakerReturnsMultipleAndMaleMarkerPart() {
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		self::assertContains(
			'_multiple_male',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForMultipleFemaleSpeakerReturnsMultipleAndFemaleMarkerPart() {
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);

		self::assertContains(
			'_multiple_female',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForMultipleSpeakersWithMixedGendersReturnsSpeakerType() {
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);
		$this->addSpeakerRelation(
			array('gender' => tx_seminars_speaker::GENDER_FEMALE)
		);

		self::assertContains(
			'speakers',
			$this->fixture->getLanguageKeySuffixForType('speakers')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForOneSpeakerWithoutGenderAndOneWithGenderReturnsSpeakerType() {
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_UNKNOWN)
		);
		$this->addLeaderRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		self::assertContains(
			'leaders',
			$this->fixture->getLanguageKeySuffixForType('leaders')
		);
	}

	/**
	 * @test
	 */
	public function getLanguageKeySuffixForTypeForSingleMaleTutorReturnsCorrespondingMarkerPart() {
		$this->addTutorRelation(
			array('gender' => tx_seminars_speaker::GENDER_MALE)
		);

		self::assertSame(
			'tutors_single_male',
			$this->fixture->getLanguageKeySuffixForType('tutors')
		);
	}


	/*
	 * Tests concerning hasRequirements
	 */

	/**
	 * @test
	 */
	public function hasRequirementsForTopicWithoutRequirementsReturnsFalse() {
		$topic = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
					'requirements' => 0,
				)
			)
		);

		self::assertFalse(
			$topic->hasRequirements()
		);
	}

	/**
	 * @test
	 */
	public function hasRequirementsForDateOfTopicWithoutRequirementsReturnsFalse() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 0,
			)
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		self::assertFalse(
			$date->hasRequirements()
		);
	}

	/**
	 * @test
	 */
	public function hasRequirementsForTopicWithOneRequirementReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		self::assertTrue(
			$topic->hasRequirements()
		);
	}

	/**
	 * @test
	 */
	public function hasRequirementsForDateOfTopicWithOneRequirementReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		self::assertTrue(
			$date->hasRequirements()
		);
	}

	/**
	 * @test
	 */
	public function hasRequirementsForTopicWithTwoRequirementsReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$requiredTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid2, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		self::assertTrue(
			$topic->hasRequirements()
		);
	}


	/*
	 * Tests concerning hasDependencies
	 */

	/**
	 * @test
	 */
	public function hasDependenciesForTopicWithoutDependenciesReturnsFalse() {
		$topic = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
					'dependencies' => 0,
				)
			)
		);

		self::assertFalse(
			$topic->hasDependencies()
		);
	}

	/**
	 * @test
	 */
	public function hasDependenciesForDateOfTopicWithoutDependenciesReturnsFalse() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 0,
			)
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		self::assertFalse(
			$date->hasDependencies()
		);
	}

	/**
	 * @test
	 */
	public function hasDependenciesForTopicWithOneDependencyReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		self::assertTrue(
			$topic->hasDependencies()
		);
	}

	/**
	 * @test
	 */
	public function hasDependenciesForDateOfTopicWithOneDependencyReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		self::assertTrue(
			$date->hasDependencies()
		);
	}

	/**
	 * @test
	 */
	public function hasDependenciesForTopicWithTwoDependenciesReturnsTrue() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 2,
			)
		);
		$dependentTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid1, $topicUid
		);
		$dependentTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid2, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$result = $topic->hasDependencies();

		self::assertTrue(
			$result
		);
	}


	/*
	 * Tests concerning getRequirements
	 */

	/**
	 * @test
	 */
	public function getRequirementsReturnsSeminarBag() {
		self::assertTrue(
			$this->fixture->getRequirements() instanceof tx_seminars_Bag_Event
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForNoRequirementsReturnsEmptyBag() {
		self::assertTrue(
			$this->fixture->getRequirements()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForOneRequirementReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$result = $topic->getRequirements();

		self::assertSame(
			1,
			$result->count()
		);
		self::assertSame(
			$requiredTopicUid,
			$result->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForDateOfTopicWithOneRequirementReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid, 'requirements'
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$result = $date->getRequirements();

		self::assertSame(
			1,
			$result->count()
		);
		self::assertSame(
			$requiredTopicUid,
			$result->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getRequirementsForTwoRequirementsReturnsBagWithTwoItems() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$requiredTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid1, 'requirements'
		);
		$requiredTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$topicUid, $requiredTopicUid2, 'requirements'
		);
		$topic = new tx_seminars_seminarchild($topicUid);
		$requirements = $topic->getRequirements();

		self::assertSame(
			2,
			$requirements->count()
		);
	}


	/*
	 * Tests concerning getDependencies
	 */

	/**
	 * @test
	 */
	public function getDependenciesReturnsSeminarBag() {
		self::assertTrue(
			$this->fixture->getDependencies() instanceof tx_seminars_Bag_Event
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForNoDependenciesReturnsEmptyBag() {
		self::assertTrue(
			$this->fixture->getDependencies()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForOneDependencyReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);

		$result = $topic->getDependencies();

		self::assertSame(
			1,
			$result->count()
		);
		self::assertSame(
			$dependentTopicUid,
			$result->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForDateOfTopicWithOneDependencyReturnsBagWithOneTopic() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 1,
			)
		);
		$dependentTopicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid, $topicUid
		);
		$date = new tx_seminars_seminarchild(
			$this->testingFramework->createRecord(
				'tx_seminars_seminars',
				array(
					'object_type' => tx_seminars_Model_Event::TYPE_DATE,
					'topic' => $topicUid,
				)
			)
		);

		$result = $date->getDependencies();

		self::assertSame(
			1,
			$result->count()
		);
		self::assertSame(
			$dependentTopicUid,
			$result->current()->getUid()
		);
	}

	/**
	 * @test
	 */
	public function getDependenciesForTwoDependenciesReturnsBagWithTwoItems() {
		$topicUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'dependencies' => 2,
			)
		);
		$dependentTopicUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid1, $topicUid
		);
		$dependentTopicUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'requirements' => 1,
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_requirements_mm',
			$dependentTopicUid2, $topicUid
		);
		$topic = new tx_seminars_seminarchild($topicUid);
		$dependencies = $topic->getDependencies();

		self::assertSame(
			2,
			$dependencies->count()
		);
	}


	/*
	 * Tests concerning isConfirmed
	 */

	/**
	 * @test
	 */
	public function isConfirmedForStatusPlannedReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_PLANNED);

		self::assertFalse(
			$this->fixture->isConfirmed()
		);
	}

	/**
	 * @test
	 */
	public function isConfirmedForStatusConfirmedReturnsTrue() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CONFIRMED);

		self::assertTrue(
			$this->fixture->isConfirmed()
		);
	}

	/**
	 * @test
	 */
	public function isConfirmedForStatusCanceledReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		self::assertFalse(
			$this->fixture->isConfirmed()
		);
	}


	/*
	 * Tests concerning isCanceled
	 */

	/**
	 * @test
	 */
	public function isCanceledForPlannedEventReturnsFalse() {
	$this->fixture->setStatus(tx_seminars_seminar::STATUS_PLANNED);

	self::assertFalse(
			$this->fixture->isCanceled()
		);
	}

	/**
	 * @test
	 */
	public function isCanceledForCanceledEventReturnsTrue() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		self::assertTrue(
			$this->fixture->isCanceled()
		);
	}

	/**
	 * @test
	 */
	public function isCanceledForConfirmedEventReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CONFIRMED);

		self::assertFalse(
			$this->fixture->isCanceled()
		);
	}


	/*
	 * Tests concerning isPlanned
	 */

	/**
	 * @test
	 */
	public function isPlannedForStatusPlannedReturnsTrue() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_PLANNED);

		self::assertTrue(
			$this->fixture->isPlanned()
		);
	}

	/**
	 * @test
	 */
	public function isPlannedForStatusConfirmedReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CONFIRMED);

		self::assertFalse(
			$this->fixture->isPlanned()
		);
	}

	/**
	 * @test
	 */
	public function isPlannedForStatusCanceledReturnsFalse() {
		$this->fixture->setStatus(tx_seminars_seminar::STATUS_CANCELED);

		self::assertFalse(
			$this->fixture->isPlanned()
		);
	}


	/*
	 * Tests concerning setEventTakesPlaceReminderSentFlag
	 */

	/**
	 * @test
	 */
	public function setEventTakesPlaceReminderSentFlagSetsFlagToTrue() {
		$this->fixture->setEventTakesPlaceReminderSentFlag();

		self::assertTrue(
			$this->fixture->getRecordPropertyBoolean(
				'event_takes_place_reminder_sent'
			)
		);
	}


	/*
	 * Tests concerning setCancelationDeadlineReminderSentFlag
	 */

	/**
	 * @test
	 */
	public function setCancellationDeadlineReminderSentFlagToTrue() {
		$this->fixture->setCancelationDeadlineReminderSentFlag();

		self::assertTrue(
			$this->fixture->getRecordPropertyBoolean(
				'cancelation_deadline_reminder_sent'
			)
		);
	}


	/*
	 * Tests concerning getCancelationDeadline
	 */

	/**
	 * @test
	 */
	public function getCancellationDeadlineForEventWithoutSpeakerReturnsBeginDateOfEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);

		self::assertSame(
			$this->fixture->getBeginDateAsTimestamp(),
			$this->fixture->getCancelationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function getCancellationDeadlineForEventWithSpeakerWithoutCancellationPeriodReturnsBeginDateOfEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 0));

		self::assertSame(
			$this->fixture->getBeginDateAsTimestamp(),
			$this->fixture->getCancelationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function getCancellationDeadlineForEventWithTwoSpeakersWithoutCancellationPeriodReturnsBeginDateOfEvent() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 0));
		$this->addSpeakerRelation(array('cancelation_period' => 0));

		self::assertSame(
			$this->fixture->getBeginDateAsTimestamp(),
			$this->fixture->getCancelationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function getCancellationDeadlineForEventWithOneSpeakersWithCancellationPeriodReturnsBeginDateMinusCancelationPeriod() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 1));

		self::assertSame(
			$GLOBALS['SIM_EXEC_TIME'] - tx_seminars_timespan::SECONDS_PER_DAY,
			$this->fixture->getCancelationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function getCancellationDeadlineForEventWithTwoSpeakersWithCancellationPeriodsReturnsBeginDateMinusBiggestCancelationPeriod() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->addSpeakerRelation(array('cancelation_period' => 21));
		$this->addSpeakerRelation(array('cancelation_period' => 42));

		self::assertSame(
			$GLOBALS['SIM_EXEC_TIME']
				- (42 * tx_seminars_timespan::SECONDS_PER_DAY),
			$this->fixture->getCancelationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function getCancellationDeadlineForEventWithoutBeginDateThrowsException() {
		$this->fixture->setBeginDate(0);

		$this->setExpectedException(
			'BadMethodCallException',
			'The event has no begin date. Please call this function only if the event has a begin date.'
		);

		$this->fixture->getCancelationDeadline();
	}


	/*
	 * Tests concerning the license expiry
	 */

	/**
	 * @test
	 */
	public function hasExpiryForNoExpiryReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasExpiry()
		);
	}

	/**
	 * @test
	 */
	public function hasExpiryForNonZeroExpiryReturnsTrue() {
		$this->fixture->setExpiry(42);

		self::assertTrue(
			$this->fixture->hasExpiry()
		);
	}

	/**
	 * @test
	 */
	public function getExpiryForNoExpiryReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getExpiry()
		);
	}

	/**
	 * @test
	 */
	public function getExpiryForNonZeroExpiryReturnsFormattedDate() {
		$this->fixture->setExpiry(mktime(0, 0, 0, 12, 31, 2000));

		self::assertSame(
			'31.12.2000',
			$this->fixture->getExpiry()
		);
	}


	/*
	 * Tests concerning getEventData
	 */

	/**
	 * @test
	 */
	public function getEventDataReturnsFormattedUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(0);
		self::assertSame(
			'01.01.2030',
			$this->fixture->getEventData('deadline_unregistration')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForShowTimeOfUnregistrationDeadlineTrueReturnsFormattedUnregistrationDeadlineWithTime() {
		$this->fixture->setUnregistrationDeadline(1893488400);
		$this->fixture->setShowTimeOfUnregistrationDeadline(1);

		self::assertSame(
			'01.01.2030 10:00',
			$this->fixture->getEventData('deadline_unregistration')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForUnregistrationDeadlineZeroReturnsEmptyString () {
		$this->fixture->setUnregistrationDeadline(0);
		self::assertSame(
			'',
			$this->fixture->getEventData('deadline_unregistration')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithMultipleLodgingsSeparatesLodgingsWithLineFeeds() {
		$lodgingUid1 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'foo')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid1
		);

		$lodgingUid2 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'bar')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid2
		);

		$this->fixture->setNumberOfLodgings(2);

		self::assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('lodgings')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithMultipleLodgingsDoesNotSeparateLodgingsWithCarriageReturnsAndLineFeeds() {
		$lodgingUid1 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'foo')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid1
		);

		$lodgingUid2 = $this->testingFramework->createRecord(
			'tx_seminars_lodgings', array('title' => 'bar')
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_lodgings_mm',
			$this->fixture->getUid(), $lodgingUid2
		);

		$this->fixture->setNumberOfLodgings(2);

		self::assertNotContains(
			'foo' . CRLF . 'bar',
			$this->fixture->getEventData('lodgings')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithCarriageReturnAndLinefeedGetsConvertedToLineFeedOnly() {
		$this->fixture->setDescription('foo'. CRLF . 'bar');

		self::assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithTwoAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed() {
		$this->fixture->setDescription('foo'. LF . LF . 'bar');

		self::assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithThreeAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed() {
		$this->fixture->setDescription('foo'. LF . LF .  LF . 'bar');

		self::assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataDataWithFourAdjacentLineFeedsReturnsStringWithOnlyOneLineFeed() {
		$this->fixture->setDescription('foo'. LF . LF .  LF . LF . 'bar');

		self::assertContains(
			'foo' . LF . 'bar',
			$this->fixture->getEventData('description')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithDateUsesHyphenAsDateSeparator() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + tx_oelib_Time::SECONDS_PER_DAY);

		self::assertContains(
			'-',
			$this->fixture->getEventData('date')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForEventWithTimeUsesHyphenAsTimeSeparator() {
		$this->fixture->setBeginDate($GLOBALS['SIM_EXEC_TIME']);
		$this->fixture->setEndDate($GLOBALS['SIM_EXEC_TIME'] + 3600);

		self::assertContains(
			'-',
			$this->fixture->getEventData('time')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataSeparatesPlacePartsByCommaAndSpace() {
		$place = array(
			'title' => 'Hotel Ibis',
			'homepage' => '',
			'address' => 'Kaiser-Karl-Ring 91',
			'city' => 'Bonn',
			'country' => '',
			'directions' => '',
		);

		$fixture = $this->getMock('tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace'), array(), '', FALSE);
		$fixture->expects(self::any())->method('getPlacesAsArray')->will(self::returnValue(array($place)));
		$fixture->expects(self::any())->method('hasPlace')->will(self::returnValue(TRUE));

		self::assertSame(
			'Hotel Ibis, Kaiser-Karl-Ring 91, Bonn',
			$fixture->getEventData('place')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataSeparatesTwoPlacesByLineFeed() {
		$place1 = array(
			'title' => 'Hotel Ibis',
			'homepage' => '',
			'address' => '',
			'city' => '',
			'country' => '',
			'directions' => '',
		);
		$place2 = array(
			'title' => 'Wasserwerk',
			'homepage' => '',
			'address' => '',
			'city' => '',
			'country' => '',
			'directions' => '',
		);

		$fixture = $this->getMock('tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace'), array(), '', FALSE);
		$fixture->expects(self::any())->method('getPlacesAsArray')->will(self::returnValue(array($place1, $place2)));
		$fixture->expects(self::any())->method('hasPlace')->will(self::returnValue(TRUE));

		self::assertSame(
			'Hotel Ibis' . LF . 'Wasserwerk',
			$fixture->getEventData('place')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForPlaceWithoutZipContainsTitleAndAddressAndCity() {
		$place = array(
			'title' => 'Hotel Ibis',
			'address' => 'Kaiser-Karl-Ring 91',
			'zip' => '',
			'city' => 'Bonn',
		);

		$fixture = $this->getMock('tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace'), array(), '', FALSE);
		$fixture->expects(self::any())->method('getPlacesAsArray')->will(self::returnValue(array($place)));
		$fixture->expects(self::any())->method('hasPlace')->will(self::returnValue(TRUE));

		self::assertSame(
			'Hotel Ibis, Kaiser-Karl-Ring 91, Bonn',
			$fixture->getEventData('place')
		);
	}

	/**
	 * @test
	 */
	public function getEventDataForPlaceWithZipContainsTitleAndAddressAndZipAndCity() {
		$place = array(
			'title' => 'Hotel Ibis',
			'address' => 'Kaiser-Karl-Ring 91',
			'zip' => '53111',
			'city' => 'Bonn',
		);

		$fixture = $this->getMock('tx_seminars_seminar', array('getPlacesAsArray', 'hasPlace'), array(), '', FALSE);
		$fixture->expects(self::any())->method('getPlacesAsArray')->will(self::returnValue(array($place)));
		$fixture->expects(self::any())->method('hasPlace')->will(self::returnValue(TRUE));

		self::assertSame(
			'Hotel Ibis, Kaiser-Karl-Ring 91, 53111 Bonn',
			$fixture->getEventData('place')
		);
	}


	/*
	 * Tests concerning dumpSeminarValues
	 */

	/**
	 * @test
	 */
	public function dumpSeminarValuesForTitleGivenReturnsTitle() {
		self::assertContains(
			$this->fixture->getTitle(),
			$this->fixture->dumpSeminarValues('title')
		);
	}

	/**
	 * @test
	 */
	public function dumpSeminarValuesForTitleGivenReturnsLabelForTitle() {
		self::assertContains(
			$this->fixture->translate('label_title'),
			$this->fixture->dumpSeminarValues('title')
		);
	}

	/**
	 * @test
	 */
	public function dumpSeminarValuesForTitleGivenReturnsTitleWithLineFeedAtEndOfLine() {
		self::assertRegexp(
			'/\n$/',
			$this->fixture->dumpSeminarValues('title')
		);
	}

	/**
	 * @test
	 */
	public function dumpSeminarValuesForTitleAndDescriptionGivenReturnsTitleAndDescription() {
		$this->fixture->setDescription('foo bar');

		self::assertRegexp(
			'/.*' . $this->fixture->getTitle() . '.*\n.*' .
				$this->fixture->getRecordPropertyString('description') .'/',
			$this->fixture->dumpSeminarValues('title,description')
		);
	}

	/**
	 * @test
	 */
	public function dumpSeminarValuesForEventWithoutDescriptionAndDescriptionGivenReturnsDescriptionLabelWithColonsAndLineFeed() {
		$this->fixture->setDescription('');

		self::assertSame(
			$this->fixture->translate('label_description') . ':' . LF,
			$this->fixture->dumpSeminarValues('description')
		);
	}

	/**
	 * @test
	 */
	public function dumpSeminarValuesForEventWithNoVacanciesAndVacanciesGivenReturnsVacanciesLabelWithNumber() {
		$this->fixture->setNumberOfAttendances(2);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNeedsRegistration(TRUE);

		self::assertSame(
			$this->fixture->translate('label_vacancies') . ': 0' . LF ,
			$this->fixture->dumpSeminarValues('vacancies')
		);
	}

	/**
	 * @test
	 */
	public function dumpSeminarValuesForEventWithOneVacancyAndVacanciesGivenReturnsNumberOfVacancies() {
		$this->fixture->setNumberOfAttendances(1);
		$this->fixture->setAttendancesMax(2);
		$this->fixture->setNeedsRegistration(TRUE);

		self::assertSame(
			$this->fixture->translate('label_vacancies') . ': 1' . LF ,
			$this->fixture->dumpSeminarValues('vacancies')
		);
	}

	/**
	 * @test
	 */
	public function dumpSeminarValuesForEventWithUnlimitedVacanciesAndVacanciesGivenReturnsVacanciesUnlimitedString() {
		$this->fixture->setUnlimitedVacancies();

		self::assertSame(
			$this->fixture->translate('label_vacancies') . ': ' .
				$this->fixture->translate('label_unlimited') . LF ,
			$this->fixture->dumpSeminarValues('vacancies')
		);
	}


	/*
	 * Tests regarding the registration begin date
	 */

	/**
	 * @test
	 */
	public function hasRegistrationBeginForNoRegistrationBeginReturnsFalse() {
		$this->fixture->setRegistrationBeginDate(0);

		self::assertFalse(
			$this->fixture->hasRegistrationBegin()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationBeginForEventWithRegistrationBeginReturnsTrue() {
		$this->fixture->setRegistrationBeginDate(42);

		self::assertTrue(
			$this->fixture->hasRegistrationBegin()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationBeginAsUnixTimestampForEventWithoutRegistrationBeginReturnsZero() {
		$this->fixture->setRegistrationBeginDate(0);

		self::assertSame(
			0,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationBeginAsUnixTimestampForEventWithRegistrationBeginReturnsRegistrationBeginAsUnixTimestamp() {
		$this->fixture->setRegistrationBeginDate(42);

		self::assertSame(
			42,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationBeginForEventWithoutRegistrationBeginReturnsEmptyString() {
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
		$this->fixture->setConfigurationValue('timeFormat', '%H:%M');

		$this->fixture->setRegistrationBeginDate(0);

		self::assertSame(
			'',
			$this->fixture->getRegistrationBegin()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationBeginForEventWithRegistrationBeginReturnsFormattedRegistrationBegin() {
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d.%m.%Y');
		$this->fixture->setConfigurationValue('timeFormat', '%H:%M');

		$this->fixture->setRegistrationBeginDate($GLOBALS['SIM_EXEC_TIME']);

		self::assertSame(
			strftime('%d.%m.%Y %H:%M', $GLOBALS['SIM_EXEC_TIME']),
			$this->fixture->getRegistrationBegin()
		);
	}


	/*
	 * Tests regarding the description.
	 */

	/**
	 * @test
	 */
	public function getDescriptionWithoutDescriptionReturnEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionSetsDescription() {
		$this->fixture->setDescription('this is a great event.');

		self::assertSame(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithoutDescriptionReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithDescriptionReturnsTrue() {
		$this->fixture->setDescription('this is a great event.');

		self::assertTrue(
			$this->fixture->hasDescription()
		);
	}


	/*
	 * Tests regarding the additional information.
	 */

	/**
	 * @test
	 */
	public function getAdditionalInformationWithoutAdditionalInformationReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function setAdditionalInformationSetsAdditionalInformation() {
		$this->fixture->setAdditionalInformation('this is good to know');

		self::assertSame(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationWithoutAdditionalInformationReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationWithAdditionalInformationReturnsTrue() {
		$this->fixture->setAdditionalInformation('this is good to know');

		self::assertTrue(
			$this->fixture->hasAdditionalInformation()
		);
	}


	/*
	 * Tests concerning getLatestPossibleRegistrationTime
	 */

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithoutAnyDatesReturnsZero() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => 0,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild($uid, array());

		self::assertSame(
			0,
			$fixture->getLatestPossibleRegistrationTime()
		);
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateReturnsBeginDate() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => $this->now,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild($uid, array());

		self::assertSame(
			$this->now,
			$fixture->getLatestPossibleRegistrationTime()
		);
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineReturnsRegistrationDeadline() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => $this->now,
				'begin_date' => $this->now + 1000,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild($uid, array());

		self::assertSame(
			$this->now,
			$fixture->getLatestPossibleRegistrationTime()
		);
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginAndEndDateAndRegistrationForStartedEventsAllowedReturnsEndDate() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => $this->now,
				'end_date' => $this->now + 1000,
			)
		);
		$fixture = new tx_seminars_seminarchild(
			$uid, array('allowRegistrationForStartedEvents' => 1)
		);

		self::assertSame(
			$this->now + 1000,
			$fixture->getLatestPossibleRegistrationTime()
		);
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndRegistrationDeadlineAndRegistrationForStartedEventsAllowedReturnsRegistrationDeadline() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => $this->now - 1000,
				'begin_date' => $this->now,
				'end_date' => $this->now + 1000,
			)
		);
		$fixture = new tx_seminars_seminarchild(
			$uid, array('allowRegistrationForStartedEvents' => 1)
		);

		self::assertSame(
			$this->now - 1000,
			$fixture->getLatestPossibleRegistrationTime()
		);
	}

	/**
	 * @test
	 */
	public function getLatestPossibleRegistrationTimeForEventWithBeginDateAndWithoutEndDateAndRegistrationForStartedEventsAllowedReturnsBeginDate() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'title' => 'a test event',
				'needs_registration' => 1,
				'deadline_registration' => 0,
				'begin_date' => $this->now,
				'end_date' => 0,
			)
		);
		$fixture = new tx_seminars_seminarchild(
			$uid, array('allowRegistrationForStartedEvents' => 1)
		);

		self::assertSame(
			$this->now,
			$fixture->getLatestPossibleRegistrationTime()
		);
	}


	/*
	 * Tests concerning hasOfflineRegistrations
	 */

	/**
	 * @test
	 */
	public function hasOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasOfflineRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function hasOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTrue() {
		$this->fixture->setOfflineRegistrationNumber(2);

		self::assertTrue(
			$this->fixture->hasOfflineRegistrations()
		);
	}


	/*
	 * Tests concerning getOfflineRegistrations
	 */

	/**
	 * @test
	 */
	public function getOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsZero() {
		self::assertSame(
			0,
			$this->fixture->getOfflineRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function getOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTwo() {
		$this->fixture->setOfflineRegistrationNumber(2);

		self::assertSame(
			2,
			$this->fixture->getOfflineRegistrations()
		);
	}


	/*
	 * Tests concerning calculateStatistics
	 */

	/**
	 * @test
	 */
	public function calculateStatisticsForEventWithOfflineRegistrationsAndRegularRegistrationsCalculatesCumulatedAttendeeNumber() {
		$this->fixture->setOfflineRegistrationNumber(1);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
			)
		);

		$this->fixture->calculateStatistics();

		self::assertSame(
			2,
			$this->fixture->getAttendances()
		);
	}

	/**
	 * @test
	 */
	public function calculateStatisticsForEventWithOnePaidRegistrationSetsOnePaidAttendance() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
				'datepaid' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$this->fixture->calculateStatistics();

		self::assertSame(
			1,
			$this->fixture->getAttendancesPaid()
		);
	}

	/**
	 * @test
	 */
	public function calculateStatisticsForEventWithTwoAttendeesOnQueueSetsTwoAttendanceOnQueue() {
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
				'registration_queue' => 1,
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->fixture->getUid(),
				'registration_queue' => 1,
			)
		);

		$this->fixture->calculateStatistics();

		self::assertSame(
			2,
			$this->fixture->getAttendancesOnRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function calculateStatisticsForEventWithOneOfflineRegistrationSetsAttendancesToOne() {
		$this->fixture->setOfflineRegistrationNumber(1);

		$this->fixture->calculateStatistics();

		self::assertSame(
			1,
			$this->fixture->getAttendances()
		);
	}


	/*
	 * Tests concerning getTopicInteger
	 */

	/**
	 * @test
	 */
	public function getTopicIntegerForSingleEventReturnsDataFromRecord() {
		$this->fixture->setRecordPropertyInteger('credit_points', 42);

		self::assertSame(
			42,
			$this->fixture->getTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function getTopicIntegerForDateReturnsDataFromTopic() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 42,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);

		$date = new tx_seminars_seminarchild($dateRecordUid);

		self::assertSame(
			42,
			$date->getTopicInteger('credit_points')
		);
	}


	/*
	 * Tests concerning hasTopicInteger
	 */

	/**
	 * @test
	 */
	public function hasTopicIntegerForSingleEventForZeroReturnsFalse() {
		$this->fixture->setRecordPropertyInteger('credit_points', 0);

		self::assertFalse(
			$this->fixture->hasTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForSingleEventForPositiveIntegerReturnsFalse() {
		$this->fixture->setRecordPropertyInteger('credit_points', 1);

		self::assertTrue(
			$this->fixture->hasTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForSingleEventForNegativeIntegerReturnsFalse() {
		$this->fixture->setRecordPropertyInteger('credit_points', -1);

		self::assertTrue(
			$this->fixture->hasTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForDateForZeroInTopicReturnsFalse() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 0,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);

		$date = new tx_seminars_seminarchild($dateRecordUid);

		self::assertFalse(
			$date->hasTopicInteger('credit_points')
		);
	}

	/**
	 * @test
	 */
	public function hasTopicIntegerForDateForPositiveIntegerInTopicReturnsTrue() {
		$topicRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 1,
			)
		);
		$dateRecordUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topicRecordUid,
			)
		);

		$date = new tx_seminars_seminarchild($dateRecordUid);

		self::assertTrue(
			$date->hasTopicInteger('credit_points')
		);
	}


	/*
	 * Tests concerning the publication state
	 */

	/**
	 * @test
	 */
	public function getPublicationHashReturnsPublicationHash() {
		$this->fixture->setRecordPropertyString(
			'publication_hash',
			'5318761asdf35as5sad35asd35asd'
		);

		self::assertSame(
			'5318761asdf35as5sad35asd35asd',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function setPublicationHashSetsPublicationHash() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		self::assertSame(
			'5318761asdf35as5sad35asd35asd',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithoutPublicationHashIsTrue() {
		$this->fixture->setPublicationHash('');

		self::assertTrue(
			$this->fixture->isPublished()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithPublicationHashIsFalse() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		self::assertFalse(
			$this->fixture->isPublished()
		);
	}


	/*
	 * Tests concerning canViewRegistrationsList
	 */

	/**
	 * Data provider for testing the canViewRegistrationsList function
	 * with default access and access only for attendees and managers.
	 *
	 * @return mixed[][] test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isRegistered] boolean: whether the logged-in user is
	 *                              registered for that event
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [whichPlugin] string: value for that parameter
	 *               [registrationsListPID] integer: value for that parameter
	 *               [registrationsVipListPID] integer: value for that parameter
	 */
	public function canViewRegistrationsListDataProvider() {
		return array(
			'seminarListWithNothingElse' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListLoggedInWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 1,
			),
			'seminarListIsVipWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'myEventsIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myEventsIsVipWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsRegisteredWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'listRegistrationsIsRegistered' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listRegistrationsIsVip' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsRegistered' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProvider
	 *
	 * @param string $expected
	 * @param bool $loggedIn
	 * @param bool $isRegistered
	 * @param bool  $isVip
	 * @param string $whichPlugin
	 * @param int $registrationsListPID
	 * @param int $registrationsVipListPID
	 *
	 * @return void
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndDefaultAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('isUserRegistered')
			->will(self::returnValue($isRegistered));
		$fixture->expects(self::any())->method('isUserVip')
			->will(self::returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		self::assertSame(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID
			)
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProvider
	 *
	 * @param bool $expected
	 * @param bool $loggedIn
	 * @param bool $isRegistered
	 * @param bool $isVip
	 * @param string $whichPlugin
	 * @param int $registrationsListPID
	 * @param int $registrationsVipListPID
	 *
	 * @return void
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndAttendeesManagersAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('isUserRegistered')
			->will(self::returnValue($isRegistered));
		$fixture->expects(self::any())->method('isUserVip')
			->will(self::returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		self::assertSame(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID,
				0, 'attendees_and_managers'
			)
		);
	}

	/**
	 * Data provider for the canViewRegistrationsForCsvExportListDataProvider
	 * test.
	 *
	 * @return bool[][] test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [allowCsvExportForVips] boolean: that configuration value
	 */
	public function canViewRegistrationsForCsvExportListDataProvider() {
		return array(
			'notLoggedInButCsvExportAllowed' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isVip' => FALSE,
				'allowCsvExportForVips' => TRUE,
			),
			'loggedInAndCsvExportAllowedButNoVip' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isVip' => FALSE,
				'allowCsvExportForVips' => TRUE,
			),
			'loggedInAndCsvExportAllowedAndVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isVip' => TRUE,
				'allowCsvExportForVips' => TRUE,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsForCsvExportListDataProvider
	 *
	 * @param bool $expected
	 * @param bool $loggedIn
	 * @param bool $isVip
	 * @param bool $allowCsvExportForVips
	 *
	 * @return void
	 */
	public function canViewRegistrationsListForCsvExport(
		$expected, $loggedIn, $isVip, $allowCsvExportForVips
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('isUserVip')
			->will(self::returnValue($isVip));
		$fixture->init(
			array('allowCsvExportForVips' => $allowCsvExportForVips)
		);

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		self::assertSame(
			$expected,
			$fixture->canViewRegistrationsList('csv_export')
		);
	}

	/**
	 * Data provider for testing the canViewRegistrationsList function
	 * with login access.
	 *
	 * @return mixed[][] test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isRegistered] boolean: whether the logged-in user is
	 *                              registered for that event
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [whichPlugin] string: value for that parameter
	 *               [registrationsListPID] integer: value for that parameter
	 *               [registrationsVipListPID] integer: value for that parameter
	 */
	public function canViewRegistrationsListDataProviderForLoggedIn() {
		return array(
			'seminarListWithNothingElse' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListLoggedInWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithVipListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 1,
			),
			'seminarListIsVipWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'myEventsIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myEventsIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsRegisteredWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsVipWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'listRegistrationsIsRegistered' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsRegistered' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProviderForLoggedIn
	 *
	 * @param bool $expected
	 * @param bool $loggedIn
	 * @param bool $isRegistered
	 * @param bool $isVip
	 * @param string $whichPlugin
	 * @param int $registrationsListPID
	 * @param int $registrationsVipListPID
	 *
	 * @return void
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndLoginAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('isUserRegistered')
			->will(self::returnValue($isRegistered));
		$fixture->expects(self::any())->method('isUserVip')
			->will(self::returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		self::assertSame(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID,
				0, 'login'
			)
		);
	}

	/**
	 * Data provider for testing the canViewRegistrationsList function
	 * with world access.
	 *
	 * @return mixed[][] test data for canViewRegistrationsList with each row
	 *               having the following elements:
	 *               [expected] boolean: expected value (TRUE or FALSE)
	 *               [loggedIn] boolean: whether a user is logged in
	 *               [isRegistered] boolean: whether the logged-in user is
	 *                              registered for that event
	 *               [isVip] boolean: whether the logged-in user is a VIP
	 *                                that event
	 *               [whichPlugin] string: value for that parameter
	 *               [registrationsListPID] integer: value for that parameter
	 *               [registrationsVipListPID] integer: value for that parameter
	 */
	public function canViewRegistrationsListDataProviderForWorld() {
		return array(
			'seminarListWithNothingElse' => array(
				'expected' => FALSE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => FALSE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListLoggedInWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsRegisteredWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'seminarListIsVipWithListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 1,
			),
			'seminarListIsVipWithoutListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'seminar_list',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'myEventsIsRegisteredWithListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myEventsIsVipWithVipListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsRegisteredWithVipListPid' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'myVipEventsIsVipWithVipListPid' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'my_vip_events',
				'registrationsListPID' => 1,
				'registrationsVipListPID' => 1,
			),
			'listRegistrationsIsRegistered' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsRegistered' => array(
				'expected' => FALSE,
				'loggedIn' => TRUE,
				'isRegistered' => TRUE,
				'isVip' => FALSE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
			'listVipRegistrationsIsVip' => array(
				'expected' => TRUE,
				'loggedIn' => TRUE,
				'isRegistered' => FALSE,
				'isVip' => TRUE,
				'whichPlugin' => 'list_vip_registrations',
				'registrationsListPID' => 0,
				'registrationsVipListPID' => 0,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider canViewRegistrationsListDataProviderForWorld
	 *
	 * @param bool $expected
	 * @param bool $loggedIn
	 * @param bool $isRegistered
	 * @param bool $isVip
	 * @param string $whichPlugin
	 * @param int $registrationsListPID
	 * @param int $registrationsVipListPID
	 *
	 * @return void
	 */
	public function canViewRegistrationsListWithNeedsRegistrationAndWorldAccess(
		$expected, $loggedIn, $isRegistered, $isVip,
		$whichPlugin, $registrationsListPID, $registrationsVipListPID
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'isUserRegistered', 'isUserVip'),
			array(),
			'',
			FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')
			->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('isUserRegistered')
			->will(self::returnValue($isRegistered));
		$fixture->expects(self::any())->method('isUserVip')
			->will(self::returnValue($isVip));

		if ($loggedIn) {
			$this->testingFramework->createFakeFrontEnd();
			$this->testingFramework->createAndLoginFrontEndUser();
		}

		self::assertSame(
			$expected,
			$fixture->canViewRegistrationsList(
				$whichPlugin, $registrationsListPID, $registrationsVipListPID,
				0, 'world'
			)
		);
	}


	/*
	 * Tests concerning canViewRegistrationsListMessage
	 */

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageWithoutNeededRegistrationReturnsNoRegistrationMessage() {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock('tx_seminars_seminar', array('needsRegistration'), array(), '', FALSE);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(FALSE));
		$fixture->init();

		self::assertSame(
			$fixture->translate('message_noRegistrationNecessary'),
			$fixture->canViewRegistrationsListMessage('list_registrations')
		);
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageForListAndNoLoginAndAttendeesAccessReturnsPleaseLoginMessage() {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock('tx_seminars_seminar', array('needsRegistration'), array(), '', FALSE);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->init();

		self::assertSame(
			$fixture->translate('message_notLoggedIn'),
			$fixture->canViewRegistrationsListMessage('list_registrations', 'attendees_and_managers')
		);
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageForListAndNoLoginAndLoginAccessReturnsPleaseLoginMessage() {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock('tx_seminars_seminar', array('needsRegistration'), array(), '', FALSE);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->init();

		self::assertSame(
			$fixture->translate('message_notLoggedIn'),
			$fixture->canViewRegistrationsListMessage('list_registrations', 'login')
		);
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageForListAndNoLoginAndWorldAccessReturnsEmptyString() {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock('tx_seminars_seminar', array('needsRegistration'), array(), '', FALSE);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->init();

		self::assertSame(
			'',
			$fixture->canViewRegistrationsListMessage('list_registrations', 'world')
		);
	}

	/**
	 * Data provider that returns all possible access level codes for the
	 * FE registration lists.
	 *
	 * @return string[][] the possible access levels, will not be empty
	 */
	public function registrationListAccessLevelsDataProvider() {
		return array(
			'attendeesAndManagers' => array('attendees_and_managers'),
			'login' => array('login'),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider registrationListAccessLevelsDataProvider
	 *
	 * @param string $accessLevel
	 *
	 * @return void
	 */
	public function canViewRegistrationsListMessageForVipListAndNoLoginReturnsPleaseLoginMessage($accessLevel) {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock('tx_seminars_seminar', array('needsRegistration'), array(), '', FALSE);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->init();

		self::assertSame(
			$fixture->translate('message_notLoggedIn'),
			$fixture->canViewRegistrationsListMessage('list_vip_registrations', $accessLevel)
		);
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function canViewRegistrationsListMessageForVipListAndWorldAccessAndNoLoginReturnsEmptyString() {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock('tx_seminars_seminar', array('needsRegistration'), array(), '', FALSE);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->init();

		self::assertSame(
			'',
			$fixture->canViewRegistrationsListMessage('list_vip_registrations', 'world')
		);
	}

	/**
	 * Data provider that returns all possible parameter combinations for
	 * canViewRegistrationsList as called from canViewRegistrationsListMessage.
	 *
	 * @return string[][] the possible parameter combinations, will not be empty
	 */
	public function registrationListParametersDataProvider() {
		return array(
			'attendeesAndManagers' => array('list_registrations', 'attendees_and_managers'),
			'login' => array('list_registrations', 'login'),
			'world' => array('list_registrations', 'world'),
			'attendeesAndManagersVip' => array('list_vip_registrations', 'attendees_and_managers'),
			'loginVip' => array('list_vip_registrations', 'login'),
			'worldVip' => array('list_vip_registrations', 'world'),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider registrationListParametersDataProvider
	 *
	 * @param string $whichPlugin
	 * @param string $accessLevel
	 *
	 * @return void
	 */
	public function canViewRegistrationsListMessageWithLoginRoutesParameters($whichPlugin, $accessLevel) {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'canViewRegistrationsList'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('canViewRegistrationsList')
			->with($whichPlugin, $accessLevel)
			->will(self::returnValue(TRUE));

		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		$fixture->canViewRegistrationsListMessage($whichPlugin, $accessLevel);
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageWithLoginAndAccessGrantedReturnsEmptyString() {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'canViewRegistrationsList'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('canViewRegistrationsList')->will(self::returnValue(TRUE));

		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		self::assertSame(
			'',
			$fixture->canViewRegistrationsListMessage('list_registrations', 'attendees_and_managers')
		);
	}

	/**
	 * @test
	 */
	public function canViewRegistrationsListMessageWithLoginAndAccessDeniedReturnsAccessDeniedMessage() {
		/** @var tx_seminars_seminar|PHPUnit_Framework_MockObject_MockObject $fixture */
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array('needsRegistration', 'canViewRegistrationsList'),
			array(), '', FALSE
		);
		$fixture->expects(self::any())->method('needsRegistration')->will(self::returnValue(TRUE));
		$fixture->expects(self::any())->method('canViewRegistrationsList')->will(self::returnValue(FALSE));

		$this->testingFramework->createFakeFrontEnd();
		$this->testingFramework->createAndLoginFrontEndUser();

		self::assertSame(
			$fixture->translate('message_accessDenied'),
			$fixture->canViewRegistrationsListMessage('list_registrations', 'attendees_and_managers')
		);
	}


	/*
	 * Tests concerning hasAnyPrice
	 */

	/**
	 * Data provider for hasAnyPriceWithDataProvider.
	 *
	 * @return bool[][] two-dimensional array with the following inner keys:
	 *               [expectedHasAnyPrice] the expected return value of hasAnyPrice
	 *               [hasPriceRegular] the return value of that function
	 *               [hasPriceSpecial] the return value of that function
	 *               [earlyBirdApplies] the return value of that function
	 *               [hasEarlyBirdPriceRegular] the return value of that function
	 *               [hasEarlyBirdPriceSpecial] the return value of that function
	 *               [hasPriceRegularBoard] the return value of that function
	 *               [hasPriceSpecialBoard] the return value of that function
	 */
	public function hasAnyPriceDataProvider() {
		return array(
			'noPriceAtAll' => array(
				'expectedHasAnyPrice' => FALSE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularPrice' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => TRUE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialPrice' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => TRUE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularEarlyBirdApplies' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => TRUE,
				'hasEarlyBirdPriceRegular' => TRUE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularEarlyBirdNotApplies' => array(
				'expectedHasAnyPrice' => FALSE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => TRUE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialEarlyBirdApplies' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => TRUE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => TRUE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialEarlyBirdNotApplies' => array(
				'expectedHasAnyPrice' => FALSE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => TRUE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'regularBoard' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => TRUE,
				'hasPriceSpecialBoard' => FALSE,
			),
			'specialBoard' => array(
				'expectedHasAnyPrice' => TRUE,
				'hasPriceRegular' => FALSE,
				'hasPriceSpecial' => FALSE,
				'earlyBirdApplies' => FALSE,
				'hasEarlyBirdPriceRegular' => FALSE,
				'hasEarlyBirdPriceSpecial' => FALSE,
				'hasPriceRegularBoard' => FALSE,
				'hasPriceSpecialBoard' => TRUE,
			),
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider hasAnyPriceDataProvider
	 *
	 * @param bool $expectedHasAnyPrice
	 *        the expected return value of hasAnyPrice
	 * @param bool $hasPriceRegular the return value of hasPriceRegular
	 * @param bool $hasPriceSpecial the return value of hasPriceRegular
	 * @param bool $earlyBirdApplies the return value of earlyBirdApplies
	 * @param bool $hasEarlyBirdPriceRegular the return value of earlyBirdApplies
	 * @param bool $hasEarlyBirdPriceSpecial
	 *        the return value of hasEarlyBirdPriceSpecial
	 * @param bool $hasPriceRegularBoard
	 *        the return value of hasPriceRegularBoard
	 * @param bool $hasPriceSpecialBoard
	 *        the return value of hasPriceSpecialBoard
	 */
	public function hasAnyPriceWithDataProvider(
		$expectedHasAnyPrice, $hasPriceRegular, $hasPriceSpecial,
		$earlyBirdApplies, $hasEarlyBirdPriceRegular, $hasEarlyBirdPriceSpecial,
		$hasPriceRegularBoard, $hasPriceSpecialBoard
	) {
		$fixture = $this->getMock(
			'tx_seminars_seminar',
			array(
				'hasPriceRegular', 'hasPriceSpecial', 'earlyBirdApplies',
				'hasEarlyBirdPriceRegular', 'hasEarlyBirdPriceSpecial',
				'hasPriceRegularBoard', 'hasPriceSpecialBoard'
			),
			array(), '', FALSE
		);

		$fixture->expects(self::any())->method('hasPriceRegular')
			->will(self::returnValue($hasPriceRegular));
		$fixture->expects(self::any())->method('hasPriceSpecial')
			->will(self::returnValue($hasPriceSpecial));
		$fixture->expects(self::any())->method('earlyBirdApplies')
			->will(self::returnValue($earlyBirdApplies));
		$fixture->expects(self::any())->method('hasEarlyBirdPriceRegular')
			->will(self::returnValue($hasEarlyBirdPriceRegular));
		$fixture->expects(self::any())->method('hasEarlyBirdPriceSpecial')
			->will(self::returnValue($hasEarlyBirdPriceSpecial));
		$fixture->expects(self::any())->method('hasPriceRegularBoard')
			->will(self::returnValue($hasPriceRegularBoard));
		$fixture->expects(self::any())->method('hasPriceSpecialBoard')
			->will(self::returnValue($hasPriceSpecialBoard));

		self::assertSame(
			$expectedHasAnyPrice,
			$fixture->hasAnyPrice()
		);
	}
}