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

require_once(t3lib_extMgm::extPath('seminars')
	.'class.tx_seminars_seminarbag.php');

class tx_seminars_seminarbag_testcase extends tx_phpunit_testcase {
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_seminarbag();
	}

	protected function tearDown() {
		unset($this->fixture);
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
				array('; DELETE FROM tx_seminars_seminars WHERE 1=1;')
			)
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
			' AND tx_seminars_seminars.language IN(\'de\')',
			$this->fixture->getAdditionalQueryForLanguage(array('de'))
		);
	}

	public function testGetAdditionalQueryForLanguageWithTwoValidLanguages() {
		$this->assertEquals(
			' AND tx_seminars_seminars.language IN(\'de\',\'en\')',
			$this->fixture->getAdditionalQueryForLanguage(array('de', 'en'))
		);
	}

	public function testGetAdditionalQueryForLanguageIsEmptyWithEvilData() {
		// We're just checking whether the evil data is escaped. The list will
		// be empty, but the data in the database will not be harmed.
		$this->assertEquals(
			' AND tx_seminars_seminars.language IN(\'; DELETE FROM '
				.'tx_seminars_seminars WHERE 1=1;\')',
			$this->fixture->getAdditionalQueryForLanguage(
				array('; DELETE FROM tx_seminars_seminars WHERE 1=1;')
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
			' AND tx_seminars_seminars.uid IN(EVENT_UID)',
			$this->fixture->getAdditionalQueryForCountry(array('ch'))
		);
	}

	public function testGetAdditionalQueryForCountryWithMultipleValidCountries() {
		$this->assertEquals(
			' AND tx_seminars_seminars.uid IN(EVENT_UID, SECOND_EVENT_UID)',
			$this->fixture->getAdditionalQueryForCountry(array('ch', 'de'))
		);
	}
*/
	public function testGetAdditionalQueryForCountryIsEmptyWithEvilData() {
		$this->assertEquals(
			'',
			$this->fixture->getAdditionalQueryForCountry(
				array('; DELETE FROM tx_seminars_seminars WHERE 1=1;')
			)
		);
	}
}

?>
