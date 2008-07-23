<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Mario Rimann (typo3-coding@rimann.org)
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
 * Testcase for the seminarbag class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_seminarbag_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$this->fixture = new tx_seminars_seminarbag();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////////////////////////
	// Tests for the basic bag functionality.
	///////////////////////////////////////////

	public function testBagCanHaveAtLeastOneElement() {
		// This test needs a special fixture.
		unset($this->fixture);
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array('title' => 'test event')
		);
		$this->fixture = new tx_seminars_seminarbag('uid='.$uid);

		$this->assertGreaterThan(
			0, $this->fixture->getObjectCountWithoutLimit()
		);

		$this->assertNotNull(
			$this->fixture->getCurrent()
		);
		$this->assertTrue(
			$this->fixture->getCurrent()->isOk()
		);
	}


	/////////////////////////////////////////
	// Tests for queries about event sites.
	/////////////////////////////////////////

	public function testGetAdditionalQueryForPlaceIsEmptyWithNoPlace() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForPlace(array())
		);
	}

	public function testGetAdditionalQueryForPlaceWithOneValidPlace() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid, $siteUid
		);

		$this->assertEquals(
			' AND tx_seminars_seminars.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForPlace(
				array($siteUid)
			)
		);
	}

	public function testGetAdditionalQueryForPlaceWithMultipleValidPlaces() {
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1, $siteUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid2, $siteUid2
		);

		$this->assertEquals(
			' AND tx_seminars_seminars.uid IN('.$eventUid1.','.$eventUid2.')',
			$this->fixture->getAdditionalQueryForPlace(
				array($siteUid1, $siteUid2)
			)
		);
	}

	public function testGetAdditionalQueryForPlaceIsEmptyWithInvalidPlace() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForPlace(
				array(706851)
			)
		);
	}

	public function testGetAdditionalQueryForPlaceIsEmptyWithEvilData() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForPlace(
				array('; DELETE FROM '.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;')
			)
		);
	}


	/////////////////////////////////////////
	// Tests for queries about event types.
	/////////////////////////////////////////

	public function testGetAdditionalQueryForEventTypeIsEmptyWithNoEventType() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForEventType(array())
		);
	}

	public function testGetAdditionalQueryForEventTypeWithOneValidEventType() {
		$eventTypeUid = $this->testingFramework->createRecord(SEMINARS_TABLE_EVENT_TYPES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $eventTypeUid
			)
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForEventType(array($eventTypeUid))
		);
	}

	public function testGetAdditionalQueryForEventTypeWithTwoValidEventTypes() {
		$eventTypeUid = $this->testingFramework->createRecord(SEMINARS_TABLE_EVENT_TYPES);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $eventTypeUid
			)
		);

		$eventTypeUid2 = $this->testingFramework->createRecord(SEMINARS_TABLE_EVENT_TYPES);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => $eventTypeUid2
			)
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.','.$eventUid2.')',
			$this->fixture->getAdditionalQueryForEventType(array($eventTypeUid, $eventTypeUid2))
		);
	}

	public function testGetAdditionalQueryForEventTypeWithEvilData() {
		// Querying this method with evil data should theoretically return an
		// empty string. But as we intval() the input values, and zero is an
		// allowed value for the event type, the returned string will not be
		// empty as the evil data has an integer value of zero.
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => 0
			)
		);
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForEventType(
				array('; DELETE FROM '.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;'),
				true
			)
		);
	}

	public function testGetAdditionalQueryForEventTypeWithUidZero() {
		// This covers a special case: If no event type is set, the value of the
		// field in the database is set to zero by default. So zero is an
		// allowed value for the event type.
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_COMPLETE,
				'event_type' => 0
			)
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForEventType(array(0), true)
		);
	}

	public function testGetAdditionalQueryForEventTypeReturnsDateUid() {
		// This covers the case of date records. We should get the UID of the
		// date record(s), the tested method must fetch the data from the topic
		// record through a JOIN in the database query.
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES
		);
		$eventTopicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'event_type' => $eventTypeUid
			)
		);
		$eventDateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $eventTopicUid
			)
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventDateUid.')',
			$this->fixture->getAdditionalQueryForEventType(array($eventTypeUid))
		);
	}

	public function testGetAdditionalQueryForEventTypeReturnsOnlyDummyRecords() {
		// This test covers a special case in which real records were found
		// even if the "dummyRecordsOnly" flag was set. For this we have to
		// change a dummy record into a real record and back to a dummy record
		// manually - the testing Framework prevents us from doing this via
		// createRecord() or changeRecord().
		// See https://bugs.oliverklee.com/show_bug.cgi?id=1578
		$topicUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_TOPIC,
				'event_type' => 0
			)
		);

		// Changes the dummy record into a real record.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			SEMINARS_TABLE_SEMINARS,
			'uid='.$topicUid.' AND is_dummy_record=1',
			array('is_dummy_record' => 0)
		);
		if (!$dbResult) {
			$this->fail('There was an error with the database query.');
		}

		$dateUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS,
			array(
				'object_type' => SEMINARS_RECORD_TYPE_DATE,
				'topic' => $topicUid
			)
		);

		// Stores the result of the method that is being tested.
		$additionalQuery = $this->fixture->getAdditionalQueryForEventType(
			array(0),
			true
		);

		// Restores our dummy topic record so that it is found by the cleanUp
		// function.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			SEMINARS_TABLE_SEMINARS,
			'uid='.$topicUid,
			array('is_dummy_record' => 1)
		);
		if (!$dbResult) {
			$this->fail('There was an error with the database query.');
		}

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$dateUid.')',
			$additionalQuery
		);
	}



	/////////////////////////////////////////////
	// Tests for queries about event languages.
	/////////////////////////////////////////////

	public function testGetAdditionalQueryForLanguageIsEmptyWithNoLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForLanguage(array())
		);
	}

	public function testGetAdditionalQueryForLanguageWithOneLanguage() {
		// We're using "xy" as language code instead of a real one to avoid
		// problems with real event records / site records.
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.language IN(\'xy\')',
			$this->fixture->getAdditionalQueryForLanguage(array('xy'))
		);
	}

	public function testGetAdditionalQueryForLanguageWithTwoLanguages() {
		// We're using "xy" as language code instead of a real one to avoid
		// problems with real event records / site records.
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.language IN(\'xy\',\'yx\')',
			$this->fixture->getAdditionalQueryForLanguage(array('xy', 'yx'))
		);
	}

	public function testGetAdditionalQueryForLanguageIsEmptyWithEvilData() {
		// We're just checking whether the evil data is escaped. The list will
		// be empty, but the data in the database will not be harmed.
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.language IN(\'; DELETE FROM '
				.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;\')',
			$this->fixture->getAdditionalQueryForLanguage(
				array('; DELETE FROM '.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;')
			)
		);
	}


	/////////////////////////////////////////////
	// Tests for queries about event countries.
	/////////////////////////////////////////////

	public function testGetAdditionalQueryForCountryIsEmptyWithNoCountry() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForCountry(array())
		);
	}

	public function testGetAdditionalQueryForCountryWithOneCountry() {
		// We're using "xy" as country code instead of a real one to avoid
		// problems with real event records / site records.
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'xy')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid, $siteUid
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForCountry(array('xy'))
		);
	}

	public function testGetAdditionalQueryForCountryWithMultipleCountries() {
		// We're using "xy" as country code instead of a real one to avoid
		// problems with real event records / site records.
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$siteUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'xy')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid1, $siteUid1
		);

		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$siteUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('country' => 'yx')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid2, $siteUid2
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid1.','.$eventUid2.')',
			$this->fixture->getAdditionalQueryForCountry(array('xy', 'yx'))
		);
	}

	public function testGetAdditionalQueryForCountryIsEmptyWithEvilData() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForCountry(
				array('; DELETE FROM '.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;')
			)
		);
	}


	//////////////////////////////////////////
	// Tests for queries about event cities.
	//////////////////////////////////////////

	public function testGetAdditionalQueryForCityWithOneCity() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$placeUid =	$this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'TESTCity')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForCity(
				array('TESTCity')
			)
		);
	}

	public function testGetAdditionalQueryForCityWithTwoCities() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$placeUid =	$this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'TESTCity')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForCity(
				array('TESTCity', 'OtherTestCity')
			)
		);
	}

	public function testGetAdditionalQueryForCityIsEmptyWithNoCity() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForCity(
				array()
			)
		);
	}

	public function testGetAdditionalQueryForCityIsEmptyWithEvilData() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForCity(
				array('; DELETE FROM '.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;')
			)
		);
	}


	//////////////////////////////////////////
	// Tests for general form data handling.
	//////////////////////////////////////////

	public function testRemoveDummyOptionFromFormData() {
		$this->assertEquals(
			array('CH', 'DE'),
			$this->fixture->removeDummyOptionFromFormData(
				array('none', 'CH', 'DE')
			)
		);
	}

	public function testRemoveDummyOptionFromFormDataWithDummyNotFirstElement() {
		$this->assertEquals(
			array('CH', 'DE'),
			$this->fixture->removeDummyOptionFromFormData(
				array('CH', 'none', 'DE')
			)
		);
	}

	public function testRemoveDummyOptionFromFormDataWithEmptyFormData() {
		$this->assertEquals(
			array(),
			$this->fixture->removeDummyOptionFromFormData(
				array()
			)
		);
	}

	public function testRemoveDummyOptionFromFormDataWithValueZero() {
		$this->assertEquals(
			array(0),
			$this->fixture->removeDummyOptionFromFormData(
				array(0)
			)
		);
	}
}
?>