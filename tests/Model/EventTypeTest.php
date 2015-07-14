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
class tx_seminars_Model_EventTypeTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_EventType
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_EventType();
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
		$this->fixture->setTitle('Workshop');

		self::assertEquals(
			'Workshop',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Workshop'));

		self::assertEquals(
			'Workshop',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////////////////////
	// Tests concerning the single view page UID
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSingleViewPageUidReturnsSingleViewPageUid() {
		$this->fixture->setData(array('single_view_page' => 42));

		self::assertEquals(
			42,
			$this->fixture->getSingleViewPageUid()
		);
	}

	/**
	 * @test
	 */
	public function hasSingleViewPageUidForZeroPageUidReturnsFalse() {
		$this->fixture->setData(array('single_view_page' => 0));

		self::assertFalse(
			$this->fixture->hasSingleViewPageUid()
		);
	}

	/**
	 * @test
	 */
	public function hasSingleViewPageUidForNonZeroPageUidReturnsTrue() {
		$this->fixture->setData(array('single_view_page' => 42));

		self::assertTrue(
			$this->fixture->hasSingleViewPageUid()
		);
	}
}