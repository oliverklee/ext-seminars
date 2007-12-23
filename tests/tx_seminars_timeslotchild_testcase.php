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
 * Testcase for the timeslot class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 */

require_once(t3lib_extMgm::extPath('seminars')
	.'tests/fixtures/class.tx_seminars_timeslotchild.php');

class tx_seminars_timeslotchild_testcase extends tx_phpunit_testcase {
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_timeslotchild(array());
	}

	protected function tearDown() {
		unset($this->fixture);
	}


	public function testIsOk() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}

	public function testPlaceIsInitiallyZero() {
		$this->assertEquals(
			0,
			$this->fixture->getPlace()
		);
	}

	public function testInitiallyHasNoPlace() {
		$this->assertFalse(
			$this->fixture->hasPlace()
		);
	}

	public function testSetAndGetPlace() {
		$this->fixture->setPlace(100000);

		$this->assertEquals(
			100000,
			$this->fixture->getPlace()
		);
	}

	public function testHasPlace() {
		$this->fixture->setPlace(100000);

		$this->assertTrue(
			$this->fixture->hasPlace()
		);
	}

	public function testHasEntryDateIsInitiallyFalse() {
		$this->assertFalse(
			$this->fixture->hasEntryDate()
		);
	}

	public function testHasEntryDate() {
		$this->fixture->setEntryDate(42);
		$this->assertTrue(
			$this->fixture->hasEntryDate()
		);
	}
}

?>
