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
	.'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars')
	.'tests/fixtures/class.tx_seminars_seminarchild.php');
require_once(t3lib_extMgm::extPath('oelib')
	.'tests/fixtures/class.tx_oelib_testingframework.php');

class tx_seminars_seminarchild_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	private $beginDate;
	private $unregistrationDeadline;
	private $currentTimestamp;

	protected function setUp() {
		$this->testingFramework
			= new tx_oelib_testingframework('tx_seminars');

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
	 * Inserts a place records into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param	array		data of the place to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will be 0 if an error
	 * 						has occurred
	 */
	private function addPlaceRelation(array $placeData) {
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


	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testAddPlaceRelationReturnsUid() {
		$this->assertTrue(
			$this->addPlaceRelation(array()) > 0
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
}

?>
