<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Mario Rimann (typo3-coding@rimann.org)
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
 * @author		Mario Rimann <typo3-coding@rimann.org>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_seminarbag.php');

require_once(t3lib_extMgm::extPath('oelib').'tests/fixtures/class.tx_oelib_testingframework.php');

class tx_seminars_seminarbag_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingframework('tx_seminars');
		$this->fixture = new tx_seminars_seminarbag();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}

	public function testGetAdditionalQueryForPlaceIsEmptyWithNoPlace() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForPlace(array())
		);
	}

/* The following unit tests are disabled until they can be really tested. For
 * this, the testing framework for generating dummy records needs to be set up
 * first. See https://bugs.oliverklee.com/show_bug.cgi?id=1237

	public function testGetAdditionalQueryForPlaceWithOneValidPlace() {
		$this->assertEquals(
			' AND tx_seminars_seminars.uid IN(EVENT_UID)',
			$this->fixture->getAdditionalQueryForPlace(
				array(VALID_PLACE_UID)
			)
		);
	}

	public function testGetAdditionalQueryForPlaceWithMultipleValidPlaces() {
		$this->assertEquals(
			' AND tx_seminars_seminars.uid IN(EVENT_UID, SECOND_EVENT_UID)',
			$this->fixture->getAdditionalQueryForPlace(
				array(VALID_PLACE_UID, SECOND_VALID_PLACE_UID)
			)
		);
	}
*/

	public function testGetAdditionalQueryForPlaceIsEemptyWithInvalidPlace() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForPlace(
				array(70685)
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

	public function testGetAdditionalQueryForEventTypeIsEmptyWithNoEventType() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForEventType(array())
		);
	}

	public function testGetAdditionalQueryForEventTypeWithOneValidEventType() {
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.event_type IN(3)',
			$this->fixture->getAdditionalQueryForEventType(array(3))
		);
	}

	public function testGetAdditionalQueryForEventTypeWithTwoValidEventTypes() {
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.event_type IN(1,42)',
			$this->fixture->getAdditionalQueryForEventType(array(1, 42))
		);
	}

	public function testGetAdditionalQueryForEventTypeWithEvilData() {
		// Querying this method with evil data should theoretically return an
		// empty string. But as we intval() the input values, and zero is an allowed
		// value for the event type, the returned string will not be empty as the
		// evil data has an integer value of zero.
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.event_type IN(0)',
			$this->fixture->getAdditionalQueryForEventType(
				array('; DELETE FROM '.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;')
			)
		);
	}

	public function testGetAdditionalQueryForEventTypeWithUidZero() {
		// This covers a special case: If no event type is set, the value of the
		// field in the database is set to zero by default. So zero is an allowed
		// value for the event type.
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.event_type IN(0)',
			$this->fixture->getAdditionalQueryForEventType(array(0))
		);
	}

	public function testGetAdditionalQueryForLanguageIsEmptyWithNoLanguage() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForLanguage(array())
		);
	}

	public function testGetAdditionalQueryForLanguageWithOneValidLanguage() {
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.language IN(\'de\')',
			$this->fixture->getAdditionalQueryForLanguage(array('de'))
		);
	}

	public function testGetAdditionalQueryForLanguageWithTwoValidLanguages() {
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.language IN(\'de\',\'en\')',
			$this->fixture->getAdditionalQueryForLanguage(array('de', 'en'))
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

	public function testGetAdditionalQueryForCountryIsEmptyWithNoCountry() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForCountry(array())
		);
	}

/* The following unit tests are disabled until they can be really tested. For
 * this, the testing framework for generating dummy records needs to be set up
 * first. See https://bugs.oliverklee.com/show_bug.cgi?id=1237

	public function testGetAdditionalQueryForCountryWithOneValidCountry() {
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN(EVENT_UID)',
			$this->fixture->getAdditionalQueryForCountry(array('ch'))
		);
	}

	public function testGetAdditionalQueryForCountryWithMultipleValidCountries() {
		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN(EVENT_UID, SECOND_EVENT_UID)',
			$this->fixture->getAdditionalQueryForCountry(array('ch', 'de'))
		);
	}
*/
	public function testGetAdditionalQueryForCountryIsEmptyWithEvilData() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForCountry(
				array('; DELETE FROM '.SEMINARS_TABLE_SEMINARS.' WHERE 1=1;')
			)
		);
	}

	public function testGetAdditionalQueryForCityWithOneCity() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$placeUid =	$this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'Basel')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForCity(
				array('Basel')
			)
		);
	}

	public function testGetAdditionalQueryForCityWithTwoCities() {
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$placeUid =	$this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES,
			array('city' => 'Basel')
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SITES_MM,
			$eventUid,
			$placeUid
		);

		$this->assertEquals(
			' AND '.SEMINARS_TABLE_SEMINARS.'.uid IN('.$eventUid.')',
			$this->fixture->getAdditionalQueryForCity(
				array('Basel', 'Zürich')
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
