<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the 'time-slot mapper' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Mapper_TimeSlot_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_Mapper_TimeSlot
	 */
	private $fixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_Mapper_TimeSlot();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////
	// Tests concerning find
	//////////////////////////

	/**
	 * @test
	 */
	public function findWithUidReturnsTimeSlotInstance() {
		$this->assertTrue(
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

		$this->assertEquals(
			'01.02.03 04:05',
			$this->fixture->find($uid)->getTitle()
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

		$this->assertTrue(
			$this->fixture->find($uid)->getSpeakers() instanceof tx_oelib_List
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

		$this->assertTrue(
			$this->fixture->find($timeSlotUid)->getSpeakers()->first()
				instanceof tx_seminars_Model_Speaker
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

		$this->assertEquals(
			$speaker->getUid(),
			$this->fixture->find($timeSlotUid)->getSpeakers()->getUids()
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

		$this->assertNull(
			$this->fixture->find($uid)->getPlace()
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

		$this->assertTrue(
			$this->fixture->find($timeSlotUid)->getPlace()
				instanceof tx_seminars_Model_Place
		);
	}
}
?>