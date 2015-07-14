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

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Mapper_TimeSlotTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_Mapper_TimeSlot
	 */
	private $fixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_Mapper_TimeSlot();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidReturnsTimeSlotInstance() {
		self::assertTrue(
			$this->fixture->find(1) instanceof tx_seminars_Model_TimeSlot
		);
	}

	/**
	 * @test
	 */
	public function findWithUidOfExistingRecordReturnsRecordAsModel() {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_timeslots', array('title' => '01.02.03 04:05')
		);

		/** @var tx_seminars_Model_TimeSlot $model */
		$model = $this->fixture->find($uid);
		self::assertEquals(
			'01.02.03 04:05',
			$model->getTitle()
		);
	}


	//////////////////////////////////
	// Tests regarding the speakers.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getSpeakersReturnsListInstance() {
		$uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

		/** @var tx_seminars_Model_TimeSlot $model */
		$model = $this->fixture->find($uid);
		self::assertTrue(
			$model->getSpeakers() instanceof tx_oelib_List
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithOneSpeakerReturnsListOfSpeakers() {
		$timeSlotUid = $this->testingFramework->createRecord(
			'tx_seminars_timeslots'
		);
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_timeslots', $timeSlotUid, $speaker->getUid(), 'speakers'
		);

		/** @var tx_seminars_Model_TimeSlot $model */
		$model = $this->fixture->find($timeSlotUid);
		self::assertTrue(
			$model->getSpeakers()->first() instanceof tx_seminars_Model_Speaker
		);
	}

	/**
	 * @test
	 */
	public function getSpeakersWithOneSpeakerReturnsOneSpeaker() {
		$timeSlotUid = $this->testingFramework->createRecord(
			'tx_seminars_timeslots'
		);
		$speaker = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')
			->getNewGhost();
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_timeslots', $timeSlotUid, $speaker->getUid(), 'speakers'
		);

		/** @var tx_seminars_Model_TimeSlot $model */
		$model = $this->fixture->find($timeSlotUid);
		self::assertEquals(
			$speaker->getUid(),
			$model->getSpeakers()->getUids()
		);
	}


	///////////////////////////////
	// Tests regarding the place.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getPlaceWithoutPlaceReturnsNull() {
		$uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

		/** @var tx_seminars_Model_TimeSlot $model */
		$model = $this->fixture->find($uid);
		self::assertNull(
			$model->getPlace()
		);
	}

	/**
	 * @test
	 */
	public function getPlaceWithPlaceReturnsPlaceInstance() {
		$place = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Place')->getNewGhost();
		$timeSlotUid = $this->testingFramework->createRecord(
			'tx_seminars_timeslots', array('place' => $place->getUid())
		);

		/** @var tx_seminars_Model_TimeSlot $model */
		$model = $this->fixture->find($timeSlotUid);
		self::assertTrue(
			$model->getPlace() instanceof tx_seminars_Model_Place
		);
	}
}