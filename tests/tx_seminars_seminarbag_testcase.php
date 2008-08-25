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