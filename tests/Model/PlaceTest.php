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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_PlaceTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Place
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_Place();
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

		self::assertEquals(
			'Nice place',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Nice place'));

		self::assertEquals(
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

		self::assertEquals(
			'',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function getAddressWithNonEmptyAddressReturnsAddress() {
		$this->fixture->setData(array('address' => 'Backstreet 42'));

		self::assertEquals(
			'Backstreet 42',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setAddressSetsAddress() {
		$this->fixture->setAddress('Backstreet 42');

		self::assertEquals(
			'Backstreet 42',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasAddressWithoutAddressReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasAddressWithNonEmptyAddressReturnsTrue() {
		$this->fixture->setAddress('Backstreet 42');

		self::assertTrue(
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

		self::assertEquals(
			'13373',
			$this->fixture->getZip()
		);
	}

	/**
	 * @test
	 */
	public function setZipSetsZip() {
		$this->fixture->setZip('13373');

		self::assertEquals(
			'13373',
			$this->fixture->getZip()
		);
	}

	/**
	 * @test
	 */
	public function hasZipWithNonEmptyZipReturnsTrue() {
		$this->fixture->setData(array('zip' => '13373'));

		self::assertTrue(
			$this->fixture->hasZip()
		);
	}

	/**
	 * @test
	 */
	public function hasZipWithEmptyZipReturnsFalse() {
		$this->fixture->setData(array('zip' => ''));

		self::assertFalse(
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

		self::assertEquals(
			'Hicksville',
			$this->fixture->getCity()
		);
	}

	/**
	 * @test
	 */
	public function getCityWithNonEmptyCityReturnsCity() {
		$this->fixture->setData(array('city' => 'Hicksville'));

		self::assertEquals(
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

		self::assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithInvalidCountryCodeReturnsNull() {
		$this->fixture->setData(array('country' => '0'));

		self::assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithCountryReturnsCountryInstance() {
		/** @var tx_oelib_Mapper_Country $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country');
		/** @var tx_oelib_Model_Country $country */
		$country = $mapper->find(54);
		$this->fixture->setData(array('country' => $country->getIsoAlpha2Code()));

		self::assertTrue(
			$this->fixture->getCountry() instanceof tx_oelib_Model_Country
		);
	}

	/**
	 * @test
	 */
	public function getCountryWithCountryReturnsCountryAsModel() {
		/** @var tx_oelib_Mapper_Country $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country');
		/** @var tx_oelib_Model_Country $country */
		$country = $mapper->find(54);
		$this->fixture->setData(array('country' => $country->getIsoAlpha2Code()));

		self::assertSame(
			$country,
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function setCountrySetsCountry() {
		/** @var tx_oelib_Mapper_Country $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country');
		/** @var tx_oelib_Model_Country $country */
		$country = $mapper->find(54);
		$this->fixture->setCountry($country);

		self::assertSame(
			$country,
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function countryCanBeSetToNull() {
		$this->fixture->setCountry(NULL);

		self::assertNull(
			$this->fixture->getCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithoutCountryReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithInvalidCountryReturnsFalse() {
		$this->fixture->setData(array('country' => '0'));

		self::assertFalse(
			$this->fixture->hasCountry()
		);
	}

	/**
	 * @test
	 */
	public function hasCountryWithCountryReturnsTrue() {
		/** @var tx_oelib_Mapper_Country $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country');
		/** @var tx_oelib_Model_Country $country */
		$country = $mapper->find(54);
		$this->fixture->setCountry($country);

		self::assertTrue(
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

		self::assertEquals(
			'',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageWithNonEmptyHomepageReturnsHomepage() {
		$this->fixture->setData(array('homepage' => 'http://example.com'));

		self::assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function setHomepageSetsHomepage() {
		$this->fixture->setHomepage('http://example.com');

		self::assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageWithoutHomepageReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageWithNonEmptyHomepageReturnsTrue() {
		$this->fixture->setHomepage('http://example.com');

		self::assertTrue(
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

		self::assertEquals(
			'',
			$this->fixture->getDirections()
		);
	}

	/**
	 * @test
	 */
	public function getDirectionsWithNonEmptyDirectionsReturnsDirections() {
		$this->fixture->setData(array('directions' => 'left, right, straight'));

		self::assertEquals(
			'left, right, straight',
			$this->fixture->getDirections()
		);
	}

	/**
	 * @test
	 */
	public function setDirectionsSetsDirections() {
		$this->fixture->setDirections('left, right, straight');

		self::assertEquals(
			'left, right, straight',
			$this->fixture->getDirections()
		);
	}

	/**
	 * @test
	 */
	public function hasDirectionsWithoutDirectionsReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasDirections()
		);
	}

	/**
	 * @test
	 */
	public function hasDirectionsWithNonEmptyDirectionsReturnsTrue() {
		$this->fixture->setDirections('left, right, straight');

		self::assertTrue(
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

		self::assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesWithNonEmptyNotesReturnsNotes() {
		$this->fixture->setData(array('notes' => 'Nothing of interest.'));

		self::assertEquals(
			'Nothing of interest.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function setNotesSetsNotes() {
		$this->fixture->setNotes('Nothing of interest.');

		self::assertEquals(
			'Nothing of interest.',
			$this->fixture->getNotes()
		);
	}
}