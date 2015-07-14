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
 */
class tx_seminars_Model_TimeSlotTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_TimeSlot
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_TimeSlot();
	}

	////////////////////////////////////
	// Tests regarding the entry date.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getEntryDateAsUnixTimeStampWithoutEntryDateReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getEntryDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getEntryDateAsUnixTimeStampWithEntryDateReturnsEntryDate() {
		$this->fixture->setData(array('entry_date' => 42));

		self::assertEquals(
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

		self::assertEquals(
			0,
			$this->fixture->getEntryDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEntryDateAsUnixTimeStampWithPositiveTimeStampSetsEntryDate() {
		$this->fixture->setEntryDateAsUnixTimeStamp(42);

		self::assertEquals(
			42,
			$this->fixture->getEntryDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasEntryDateWithoutEntryDateReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasEntryDate()
		);
	}

	/**
	 * @test
	 */
	public function hasEntryDateWithEntryDateReturnsTrue() {
		$this->fixture->setEntryDateAsUnixTimeStamp(42);

		self::assertTrue(
			$this->fixture->hasEntryDate()
		);
	}
}