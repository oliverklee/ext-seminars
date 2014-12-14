<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_OldModel_TimespanTest extends tx_phpunit_testcase {
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_timespanchild(array());
	}

	/////////////////////////////////////////////
	// Test for getting the begin and end date.
	/////////////////////////////////////////////

	public function testInitiallyHasNoDate() {
		$this->assertFalse(
			$this->fixture->hasDate()
		);
	}

	public function testBeginDateIsInitiallyZero() {
		$this->assertEquals(
			0, $this->fixture->getBeginDateAsTimestamp()
		);
	}

	public function testInitiallyHasNoBeginDate() {
		$this->assertFalse(
			$this->fixture->hasBeginDate()
		);
	}

	public function testEndDateIsInitiallyZero() {
		$this->assertEquals(
			0, $this->fixture->getEndDateAsTimestamp()
		);
	}

	public function testInitiallyHasNoEndDate() {
		$this->assertFalse(
			$this->fixture->hasEndDate()
		);
	}

	public function testEndDateForOpenEndedIsInitiallyZero() {
		$this->assertEquals(
			0, $this->fixture->getEndDateAsTimestampEvenIfOpenEnded()
		);
	}

	public function testSetAndGetTheBeginDate() {
		$this->fixture->setBeginDateAndTime(42);

		$this->assertTrue(
			$this->fixture->hasBeginDate()
		);
		$this->assertEquals(
			42, $this->fixture->getBeginDateAsTimestamp()
		);
	}

	public function testHasDateAfterSettingBeginDate() {
		$this->fixture->setBeginDateAndTime(42);

		$this->assertTrue(
			$this->fixture->hasDate()
		);
	}

	public function testSetAndGetTheEndDate() {
		$this->fixture->setEndDateAndTime(42);

		$this->assertTrue(
			$this->fixture->hasEndDate()
		);
		$this->assertEquals(
			42, $this->fixture->getEndDateAsTimestamp()
		);
	}

	public function testHasNoDateAfterSettingEndDate() {
		$this->fixture->setEndDateAndTime(42);

		$this->assertFalse(
			$this->fixture->hasDate()
		);
	}

	public function testEndDateForOpenEndedIsZeroIfNoBeginDate() {
		$this->fixture->setEndDateAndTime(42);

		$this->assertEquals(
			0, $this->fixture->getEndDateAsTimestampEvenIfOpenEnded()
		);
	}


	///////////////////////////////
	// Test for getting the time.
	///////////////////////////////

	public function testInitiallyHasNoTime() {
		$this->assertFalse(
			$this->fixture->hasTime()
		);
	}

	public function testHasNoEndTimeIfEndsAtMidnight() {
		$this->fixture->setEndDateAndTime(mktime(0, 0, 0, 1, 1, 2010));

		$this->assertFalse(
			$this->fixture->hasEndTime()
		);
	}

	public function testHasEndTimeIfEndsDuringTheDay() {
		$this->fixture->setEndDateAndTime(mktime(9, 0, 0, 1, 1, 2010));

		$this->assertTrue(
			$this->fixture->hasEndTime()
		);
	}


	/////////////////////////////
	// Test for open-endedness.
	/////////////////////////////

	public function testInitiallyIsOpenEnded() {
		$this->assertTrue(
			$this->fixture->isOpenEnded()
		);
	}

	public function testIsOpenEndedAfterSettingOnlyTheBeginDate() {
		$this->fixture->setBeginDateAndTime(42);

		$this->assertTrue(
			$this->fixture->isOpenEnded()
		);
	}

	public function testIsNotOpenEndedAfterSettingOnlyTheEndDateToMorning() {
		$this->fixture->setEndDateAndTime(
			mktime(9, 0, 0, 1, 1, 2010)
		);

		$this->assertFalse(
			$this->fixture->isOpenEnded()
		);
	}

	public function testIsNotOpenEndedAfterSettingBeginAndEndDateToMorning() {
		$this->fixture->setBeginDateAndTime(
			mktime(8, 0, 0, 1, 1, 2010)
		);

		$this->fixture->setEndDateAndTime(
			mktime(9, 0, 0, 1, 1, 2010)
		);

		$this->assertFalse(
			$this->fixture->isOpenEnded()
		);
	}

	public function testIsNotOpenEndedIfEndsAtMidnight() {
		$this->fixture->setEndDateAndTime(
			mktime(0, 0, 0, 1, 1, 2010)
		);

		$this->assertFalse(
			$this->fixture->isOpenEnded()
		);
	}


	///////////////////////////////////////////////////////////////////
	// Tests for getting the end date and time for open-ended events.
	///////////////////////////////////////////////////////////////////

	public function testEndDateIsMidnightIfOpenEndedStartsAtOneOClock() {
		$this->fixture->setBeginDateAndTime(
			mktime(1, 0, 0, 1, 1, 2010)
		);

		$this->assertTrue(
			$this->fixture->isOpenEnded()
		);
		$this->assertEquals(
			mktime(0, 0, 0, 1, 2, 2010),
			$this->fixture->getEndDateAsTimestampEvenIfOpenEnded()
		);
	}

	public function testEndDateIsMidnightIfOpenEndedStartsAtMorning() {
		$this->fixture->setBeginDateAndTime(
			mktime(9, 0, 0, 1, 1, 2010)
		);

		$this->assertTrue(
			$this->fixture->isOpenEnded()
		);
		$this->assertEquals(
			mktime(0, 0, 0, 1, 2, 2010),
			$this->fixture->getEndDateAsTimestampEvenIfOpenEnded()
		);
	}

	public function testEndDateIsMidnightIfOpenEndedStartsAtElevenPm() {
		$this->fixture->setBeginDateAndTime(
			mktime(23, 0, 0, 1, 1, 2010)
		);

		$this->assertTrue(
			$this->fixture->isOpenEnded()
		);
		$this->assertEquals(
			mktime(0, 0, 0, 1, 2, 2010),
			$this->fixture->getEndDateAsTimestampEvenIfOpenEnded()
		);
	}

	public function testEndDateIsMidnightIfOpenEndedStartsAtMidnight() {
		$this->fixture->setBeginDateAndTime(
			mktime(0, 0, 0, 1, 1, 2010)
		);

		$this->assertTrue(
			$this->fixture->isOpenEnded()
		);
		$this->assertEquals(
			mktime(0, 0, 0, 1, 2, 2010),
			$this->fixture->getEndDateAsTimestampEvenIfOpenEnded()
		);
	}


	///////////////////////////////////////////////////////////////////
	// Tests for for the begin date.
	///////////////////////////////////////////////////////////////////

	public function testHasStartedReturnsTrueForStartedEvent() {
		$this->fixture->setBeginDateAndTime(42);

		$this->assertTrue(
			$this->fixture->hasStarted()
		);
	}

	public function testHasStartedReturnsFalseForUpcomingEvent() {
		$this->fixture->setBeginDateAndTime($GLOBALS['SIM_EXEC_TIME'] + 42);

		$this->assertFalse(
			$this->fixture->hasStarted()
		);
	}

	public function testHasStartedReturnsFalseForEventWithoutBeginDate() {
		$this->fixture->setBeginDateAndTime(0);

		$this->assertFalse(
			$this->fixture->hasStarted()
		);
	}


	/////////////////////////////////
	// Tests concerning the places.
	/////////////////////////////////

	public function testNumberOfPlacesIsInitiallyZero() {
		$this->assertEquals(
			0, $this->fixture->getNumberOfPlaces()
		);
	}

	public function testSetNumberOfPlacesToZero() {
		$this->fixture->setNumberOfPlaces(0);

		$this->assertEquals(
			0, $this->fixture->getNumberOfPlaces()
		);
	}

	public function testSetNumberOfPlacesToPositiveInteger() {
		$this->fixture->setNumberOfPlaces(42);

		$this->assertEquals(
			42, $this->fixture->getNumberOfPlaces()
		);
	}


	////////////////////////////////
	// Tests for getting the room.
	////////////////////////////////

	public function testRoomIsInitiallyEmpty() {
		$this->assertSame(
			'', $this->fixture->getRoom()
		);
	}

	public function testSetAndGetRoom() {
		$this->fixture->setRoom('foo');

		$this->assertEquals(
			'foo', $this->fixture->getRoom()
		);
	}
}