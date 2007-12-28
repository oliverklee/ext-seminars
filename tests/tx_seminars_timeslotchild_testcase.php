<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'tests/fixtures/class.tx_seminars_timeslotchild.php');

require_once(t3lib_extMgm::extPath('oelib').'tests/fixtures/class.tx_oelib_testingframework.php');

class tx_seminars_timeslotchild_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	public function setUp() {
		$this->testingFramework
			= new tx_oelib_testingframework('tx_seminars');

		$seminarUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$fixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_TIME_SLOTS,
			array(
				'seminar' => $seminarUid,
				'entry_date' => 0,
				'place' => 0
			)
		);

		$this->fixture = new tx_seminars_timeslotchild($fixtureUid);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	//////////////////////////////////////////
	// Tests for creating time slot objects.
	//////////////////////////////////////////

	public function testCreateFromUid() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}


	/////////////////////////////////////
	// Tests for the time slot's sites.
	/////////////////////////////////////

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
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES
		);
		$this->fixture->setPlace($siteUid);

		$this->assertEquals(
			$siteUid,
			$this->fixture->getPlace()
		);
	}

	public function testHasPlace() {
		$siteUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES
		);
		$this->fixture->setPlace($siteUid);

		$this->assertTrue(
			$this->fixture->hasPlace()
		);
	}


	//////////////////////////////////////////
	// Tests for the time slot's entry date.
	//////////////////////////////////////////

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
