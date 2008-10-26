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
require_once(t3lib_extMgm::extPath('seminars') . 'tests/fixtures/class.tx_seminars_timeslotchild.php');

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_testingFramework.php');

/**
 * Testcase for the timeslot class in the 'seminars' extensions.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_timeslotchild_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$fixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $seminarUid,
				'entry_date' => 0,
				'place' => 0
			)
		);

		$this->fixture = new tx_seminars_timeslotchild($fixtureUid);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////////////////
	// Tests for creating time slot objects.
	//////////////////////////////////////////

	public function testCreateFromUid() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}


	/////////////////////////////////////
	// Tests for the time slot's sites.
	/////////////////////////////////////

	public function testPlaceIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getPlace()
		);
	}

	public function testHasPlaceInitiallyReturnsFalse() {
		$this->assertFalse(
			$this->fixture->hasPlace()
		);
	}

	public function testGetPlaceReturnsUidOfPlaceSetViaSetPlace() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES
		);
		$this->fixture->setPlace($placeUid);

		$this->assertEquals(
			$placeUid,
			$this->fixture->getPlace()
		);
	}

	public function testHasPlaceReturnsTrueIfPlaceIsSet() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES
		);
		$this->fixture->setPlace($placeUid);

		$this->assertTrue(
			$this->fixture->hasPlace()
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
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('title' => 'a place')
		);
		$this->fixture->setPlace($placeUid);

		$this->assertEquals(
			'a place',
			$this->fixture->getPlaceShort()
		);
	}

	public function testGetPlaceShortThrowsExceptionForInexistentPlaceUid() {
		$placeUid = $this->testingFramework->createRecord(SEMINARS_TABLE_SITES);
		$this->setExpectedException(
			'Exception', 'The related place with the UID ' . $placeUid .
				' could not be found in the DB.'
		);

		$this->fixture->setPlace($placeUid);
		$this->testingFramework->deleteRecord(SEMINARS_TABLE_SITES, $placeUid);

		$this->fixture->getPlaceShort();
	}

	public function testGetPlaceShortThrowsExceptionForDeletedPlace() {
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('deleted' => 1)
		);
		$this->setExpectedException(
			'Exception', 'The related place with the UID ' . $placeUid .
				' could not be found in the DB.'
		);

		$this->fixture->setPlace($placeUid);

		$this->fixture->getPlaceShort();
	}


	//////////////////////////////////////////
	// Tests for the time slot's entry date.
	//////////////////////////////////////////

	public function testHasEntryDateIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasEntryDate()
		);
	}

	public function testHasEntryDate() {
		$this->fixture->setEntryDate(42);
		$this->assertTrue(
			$this->fixture->hasEntryDate()
		);
	}
}
?>