<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
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
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_PlaceTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Place
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_Place();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	///////////////////////////////
	// Tests regarding the title.
	///////////////////////////////

	/**
	 * @test
	 */
	public function setTitleWithEmptyTitleThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $title must not be empty.'
		);

		$this->fixture->setTitle('');
	}

	/**
	 * @test
	 */
	public function setTitleSetsTitle() {
		$this->fixture->setTitle('Nice place');

		$this->assertEquals(
			'Nice place',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Nice place'));

		$this->assertEquals(
			'Nice place',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////////
	// Tests regarding the address.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getAddressWithoutAddressReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function getAddressWithNonEmptyAddressReturnsAddress() {
		$this->fixture->setData(array('address' => 'Backstreet 42'));

		$this->assertEquals(
			'Backstreet 42',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setAddressSetsAddress() {
		$this->fixture->setAddress('Backstreet 42');

		$this->assertEquals(
			'Backstreet 42',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasAddressWithoutAddressReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasAddressWithNonEmptyAddressReturnsTrue() {
		$this->fixture->setAddress('Backstreet 42');

		$this->assertTrue(
			$this->fixture->hasAddress()
		);
	}


	//////////////////////////////////
	// Tests regarding the ZIP code.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getZipWithNonEmptyZipReturnsZip() {
		$this->fixture->setData(array('zip' => '13373'));

		$this->assertEquals(
			'13373',
			$this->fixture->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setZipSetsZip() {
		$this->fixture->setZip('13373');

		$this->assertEquals(
			'13373',
			$this->fixture->getZip()
		);
	}

	/**
	 * @test
	 */
	public function hasZipWithNonEmptyZipReturnsTrue() {
		$this->fixture->setData(array('zip' => '13373'));

		$this->assertTrue(
			$this->fixture->hasZip()
		);
	}

	/**
	 * @test
	 */
	public function hasZipWithEmptyZipReturnsFalse() {
		$this->fixture->setData(array('zip' => ''));

		$this->assertFalse(
			$this->fixture->hasZip()
		);
	}


	//////////////////////////////
	// Tests regarding the city.
	//////////////////////////////

	/**
	 * @test
	 */
	public function setCityWithEmptyCityThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $city must not be empty.'
		);

		$this->fixture->setCity('');
	}

	/**
	 * @test
	 */
	public function setCitySetsCity() {
		$this->fixture->setCity('Hicksville');

		$this->assertEquals(
			'Hicksville',
			$this->fixture->getCity()
		);
	}

	/**
	 * @test
	 */
	public function getCityWithNonEmptyCityReturnsCity() {
		$this->fixture->setData(array('city' => 'Hicksville'));

		$this->assertEquals(
			'Hicksville',
			$this->fixture->getCity()
		);
	}


	/////////////////////////////////
	// Tests regarding the country.
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getCountryWithoutCountryReturnsNull() {
		$this->fixture->setData(array());

		$this->assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithInvalidCountryCodeReturnsNull() {
		$this->fixture->setData(array('country' => '0'));

		$this->assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithCountryReturnsCountryInstance() {
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$this->fixture->setData(array('country' => $country->getIsoAlpha2Code()));

		$this->assertTrue(
			$this->fixture->getCountry() instanceof tx_oelib_Model_Country
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithCountryReturnsCountryAsModel() {
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$this->fixture->setData(array('country' => $country->getIsoAlpha2Code()));

		$this->assertSame(
			$country,
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setCountrySetsCountry() {
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$this->fixture->setCountry($country);

		$this->assertSame(
			$country,
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function countryCanBeSetToNull() {
		$this->fixture->setCountry(NULL);

		$this->assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithoutCountryReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithInvalidCountryReturnsFalse() {
		$this->fixture->setData(array('country' => '0'));

		$this->assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithCountryReturnsTrue() {
		$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')->find(54);
		$this->fixture->setCountry($country);

		$this->assertTrue(
			$this->fixture->hasCountry()
		);
	}


	//////////////////////////////////
	// Tests regarding the homepage.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getHomepageWithoutHomepageReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageWithNonEmptyHomepageReturnsHomepage() {
		$this->fixture->setData(array('homepage' => 'http://example.com'));

		$this->assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function setHomepageSetsHomepage() {
		$this->fixture->setHomepage('http://example.com');

		$this->assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageWithoutHomepageReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageWithNonEmptyHomepageReturnsTrue() {
		$this->fixture->setHomepage('http://example.com');

		$this->assertTrue(
			$this->fixture->hasHomepage()
		);
	}


	//////////////////////////////////
	// Tests regarding the directions.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getDirectionsWithoutDirectionsReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getDirections()
		);
	}

	/**
	 * @test
	 */
	public function getDirectionsWithNonEmptyDirectionsReturnsDirections() {
		$this->fixture->setData(array('directions' => 'left, right, straight'));

		$this->assertEquals(
			'left, right, straight',
			$this->fixture->getDirections()
		);
	}

	/**
	 * @test
	 */
	public function setDirectionsSetsDirections() {
		$this->fixture->setDirections('left, right, straight');

		$this->assertEquals(
			'left, right, straight',
			$this->fixture->getDirections()
		);
	}

	/**
	 * @test
	 */
	public function hasDirectionsWithoutDirectionsReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasDirections()
		);
	}

	/**
	 * @test
	 */
	public function hasDirectionsWithNonEmptyDirectionsReturnsTrue() {
		$this->fixture->setDirections('left, right, straight');

		$this->assertTrue(
			$this->fixture->hasDirections()
		);
	}


	//////////////////////////////
	// Tests regarding the notes
	//////////////////////////////

	/**
	 * @test
	 */
	public function getNotesWithoutNotesReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesWithNonEmptyNotesReturnsNotes() {
		$this->fixture->setData(array('notes' => 'Nothing of interest.'));

		$this->assertEquals(
			'Nothing of interest.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function setNotesSetsNotes() {
		$this->fixture->setNotes('Nothing of interest.');

		$this->assertEquals(
			'Nothing of interest.',
			$this->fixture->getNotes()
		);
	}
}