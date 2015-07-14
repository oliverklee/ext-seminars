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
class tx_seminars_Model_AbstractTimeSpanTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_TestingTimeSpan
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_tests_fixtures_TestingTimeSpan();
	}

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
		$this->fixture->setTitle('Superhero');

		self::assertEquals(
			'Superhero',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Superhero'));

		self::assertEquals(
			'Superhero',
			$this->fixture->getTitle()
		);
	}


	////////////////////////////////////
	// Tests regarding the begin date.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getBeginDateAsUnixTimeStampWithoutBeginDateReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getBeginDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getBeginDateAsUnixTimeStampWithBeginDateReturnsBeginDate() {
		$this->fixture->setData(array('begin_date' => 42));

		self::assertEquals(
			42,
			$this->fixture->getBeginDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setBeginDateAsUnixTimeStampWithNegativeTimeStampThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $beginDate must be >= 0.'
		);

		$this->fixture->setBeginDateAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setBeginDateAsUnixTimeStampWithZeroTimeStampSetsBeginDate() {
		$this->fixture->setBeginDateAsUnixTimeStamp(0);

		self::assertEquals(
			0,
			$this->fixture->getBeginDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setBeginDateAsUnixTimeStampWithPositiveTimeStampSetsBeginDate() {
		$this->fixture->setBeginDateAsUnixTimeStamp(42);

		self::assertEquals(
			42,
			$this->fixture->getBeginDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasBeginDateWithoutBeginDateReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasBeginDate()
		);
	}

	/**
	 * @test
	 */
	public function hasBeginDateWithBeginDateReturnsTrue() {
		$this->fixture->setBeginDateAsUnixTimeStamp(42);

		self::assertTrue(
			$this->fixture->hasBeginDate()
		);
	}


	//////////////////////////////////
	// Tests regarding the end date.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getEndDateAsUnixTimeStampWithoutEndDateReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getEndDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getEndDateAsUnixTimeStampWithEndDateReturnsEndDate() {
		$this->fixture->setData(array('end_date' => 42));

		self::assertEquals(
			42,
			$this->fixture->getEndDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEndDateAsUnixTimeStampWithNegativeTimeStampThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $endDate must be >= 0.'
		);

		$this->fixture->setEndDateAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setEndDateAsUnixTimeStampWithZeroTimeStampSetsEndDate() {
		$this->fixture->setEndDateAsUnixTimeStamp(0);

		self::assertEquals(
			0,
			$this->fixture->getEndDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEndDateAsUnixTimeStampWithPositiveTimeStampSetsEndDate() {
		$this->fixture->setEndDateAsUnixTimeStamp(42);

		self::assertEquals(
			42,
			$this->fixture->getEndDateAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasEndDateWithoutEndDateReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasEndDate()
		);
	}

	/**
	 * @test
	 */
	public function hasEndDateWithEndDateReturnsTrue() {
		$this->fixture->setEndDateAsUnixTimeStamp(42);

		self::assertTrue(
			$this->fixture->hasEndDate()
		);
	}


	//////////////////////////////
	// Tests regarding the room.
	//////////////////////////////

	/**
	 * @test
	 */
	public function getRoomWithoutRoomReturnsAnEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getRoom()
		);
	}

	/**
	 * @test
	 */
	public function getRoomWithRoomReturnsRoom() {
		$this->fixture->setData(array('room' => 'cuby'));

		self::assertEquals(
			'cuby',
			$this->fixture->getRoom()
		);
	}

	/**
	 * @test
	 */
	public function setRoomSetsRoom() {
		$this->fixture->setRoom('cuby');

		self::assertEquals(
			'cuby',
			$this->fixture->getRoom()
		);
	}

	/**
	 * @test
	 */
	public function hasRoomWithoutRoomReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasRoom()
		);
	}

	/**
	 * @test
	 */
	public function hasRoomWithRoomReturnsTrue() {
		$this->fixture->setRoom('cuby');

		self::assertTrue(
			$this->fixture->hasRoom()
		);
	}
}