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
class tx_seminars_OldModel_TimeSlotTest extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework
			= new tx_oelib_testingFramework('tx_seminars');

		$seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$fixtureUid = $this->testingFramework->createRecord(
			'tx_seminars_timeslots',
			array(
				'seminar' => $seminarUid,
				'entry_date' => 0,
				'place' => 0
			)
		);

		$this->fixture = new tx_seminars_timeslotchild($fixtureUid);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	//////////////////////////////////////////
	// Tests for creating time slot objects.
	//////////////////////////////////////////

	public function testCreateFromUid() {
		self::assertTrue(
			$this->fixture->isOk()
		);
	}


	/////////////////////////////////////
	// Tests for the time slot's sites.
	/////////////////////////////////////

	public function testPlaceIsInitiallyZero() {
		self::assertEquals(
			0,
			$this->fixture->getPlace()
		);
	}

	public function testHasPlaceInitiallyReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasPlace()
		);
	}

	public function testGetPlaceReturnsUidOfPlaceSetViaSetPlace() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites'
		);
		$this->fixture->setPlace($placeUid);

		self::assertEquals(
			$placeUid,
			$this->fixture->getPlace()
		);
	}

	public function testHasPlaceReturnsTrueIfPlaceIsSet() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites'
		);
		$this->fixture->setPlace($placeUid);

		self::assertTrue(
			$this->fixture->hasPlace()
		);
	}


	////////////////////////////
	// Tests for getPlaceShort
	////////////////////////////

	public function testGetPlaceShortReturnsWillBeAnnouncedForNoPlaces() {
		self::assertEquals(
			$this->fixture->translate('message_willBeAnnounced'),
			$this->fixture->getPlaceShort()
		);
	}

	public function testGetPlaceShortReturnsPlaceNameForOnePlace() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('title' => 'a place')
		);
		$this->fixture->setPlace($placeUid);

		self::assertEquals(
			'a place',
			$this->fixture->getPlaceShort()
		);
	}

	public function testGetPlaceShortThrowsExceptionForInexistentPlaceUid() {
		$placeUid = $this->testingFramework->createRecord('tx_seminars_sites');
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'The related place with the UID ' . $placeUid . ' could not be found in the DB.'
		);

		$this->fixture->setPlace($placeUid);
		$this->testingFramework->deleteRecord('tx_seminars_sites', $placeUid);

		$this->fixture->getPlaceShort();
	}

	public function testGetPlaceShortThrowsExceptionForDeletedPlace() {
		$placeUid = $this->testingFramework->createRecord(
			'tx_seminars_sites',
			array('deleted' => 1)
		);
		$this->setExpectedException(
			'tx_oelib_Exception_NotFound',
			'The related place with the UID ' . $placeUid . ' could not be found in the DB.'
		);

		$this->fixture->setPlace($placeUid);

		$this->fixture->getPlaceShort();
	}


	//////////////////////////////////////////
	// Tests for the time slot's entry date.
	//////////////////////////////////////////

	public function testHasEntryDateIsInitiallyFalse() {
		self::assertFalse(
			$this->fixture->hasEntryDate()
		);
	}

	public function testHasEntryDate() {
		$this->fixture->setEntryDate(42);
		self::assertTrue(
			$this->fixture->hasEntryDate()
		);
	}

	public function testGetEntryDateWithBeginDateOnSameDayAsEntryDateReturnsTime() {
		// chosen randomly 2001-01-01 13:01
		$time = 978354060;
		$this->fixture->setEntryDate($time);
		$this->fixture->setBeginDate($time);
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d - %m - %Y');
		$this->fixture->setConfigurationValue('timeFormat', '%H:%M');

		self::assertEquals(
			strftime('%H:%M', $time),
			$this->fixture->getEntryDate()
		);
	}

	public function testGetEntryDateWithBeginDateOnDifferentDayAsEntryDateReturnsTimeAndDate() {
		// chosen randomly 2001-01-01 13:01
		$time = 978354060;
		$this->fixture->setEntryDate($time);
		$this->fixture->setBeginDate($time + 86400);
		$this->fixture->setConfigurationValue('dateFormatYMD', '%d - %m - %Y');
		$this->fixture->setConfigurationValue('timeFormat', '%H:%M');

		self::assertEquals(
			strftime('%d - %m - %Y %H:%M', $time),
			$this->fixture->getEntryDate()
		);
	}
}