<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars')
	.'tests/fixtures/class.tx_seminars_seminarchild.php');

class tx_seminars_seminarchild_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $beginDate;
	private $unregistrationDeadline;
	private $currentTimestamp;

	protected function setUp() {
		$this->fixture = new tx_seminars_seminarchild(
			array(
				'dateFormatYMD' => '%d.%m.%Y',
				'timeFormat' => '%H:%M',
				'showTimeOfUnregistrationDeadline' => 0,
				'unregistrationDeadlineDaysBeforeBeginDate' => 0
			)
		);

		$this->currentTimestamp = time();
		$this->beginDate = ($this->currentTimestamp + ONE_WEEK);
		$this->unregistrationDeadline = ($this->currentTimestamp + ONE_WEEK);

		$this->fixture->setEventData(
			array(
				'uid' => 1,
				'begin_date' => $this->beginDate,
				'deadline_unregistration' => $this->unregistrationDeadline,
				'language' => 'de',
				'attendees_min' => 5,
				'attendees_max' => 10,
				'object_type' => 0,
				'queue_size' => 0
			)
		);
	}

	protected function tearDown() {
		unset($this->fixture);
	}


	public function testIsOk() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	//////////////////////////////////////////////
	// Tests regarding the language of an event:
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
		// unset the language field
		$this->fixture->setEventData(
			array(
				'language' => ''
			)
		);
		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	public function testGetLanguageNameWithDefaultLanguage() {
		$this->assertEquals(
			'Deutsch',
			$this->fixture->getLanguageName()
		);
	}

	public function testGetLanguageNameWithValidLanguage() {
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

	public function testGetLanguageNameWithInvalidLanguage() {
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


	public function testGetLanguageNameWithNoLanguage() {
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


	/////////////////////////////////////////////////
	// Tests regarding the date fields of an event:
	/////////////////////////////////////////////////

	public function testGetBeginDateAsTimestamp() {
		$this->fixture->setBeginDate($this->beginDate);
		$this->assertEquals(
			$this->beginDate,
			$this->fixture->getBeginDateAsTimestamp()
		);

		$this->fixture->setBeginDate(0);
		$this->assertEquals(
			0,
			$this->fixture->getBeginDateAsTimestamp()
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
}

?>
