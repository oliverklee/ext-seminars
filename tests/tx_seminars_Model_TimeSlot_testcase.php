<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the 'time-slot model' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_TimeSlot_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_TimeSlot
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_TimeSlot();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////////
	// Tests regarding the entry date.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getEntryDateAsUnixTimeStampWithoutEntryDateReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getEntryDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getEntryDateAsUnixTimeStampWithEntryDateReturnsEntryDate() {
		$this->fixture->setData(array('entry_date' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getEntryDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEntryDateAsUnixTimeStampWithNegativeTimeStampThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $entryDate must be >= 0.'
		);

		$this->fixture->setEntryDateAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setEntryDateAsUnixTimeStampWithZeroTimeStampSetsEntryDate() {
		$this->fixture->setEntryDateAsUnixTimeStamp(0);

		$this->assertEquals(
			0,
			$this->fixture->getEntryDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEntryDateAsUnixTimeStampWithPositiveTimeStampSetsEntryDate() {
		$this->fixture->setEntryDateAsUnixTimeStamp(42);

		$this->assertEquals(
			42,
			$this->fixture->getEntryDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasEntryDateWithoutEntryDateReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasEntryDate()
		);
	}

	/**
	 * @test
	 */
	public function hasEntryDateWithEntryDateReturnsTrue() {
		$this->fixture->setEntryDateAsUnixTimeStamp(42);

		$this->assertTrue(
			$this->fixture->hasEntryDate()
		);
	}
}
?>