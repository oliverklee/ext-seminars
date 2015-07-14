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
class tx_seminars_Model_CategoryTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Category
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_Category();
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
		$this->fixture->setTitle('Lecture');

		self::assertEquals(
			'Lecture',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Lecture'));

		self::assertEquals(
			'Lecture',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////
	// Tests regarding the icon.
	//////////////////////////////

	/**
	 * @test
	 */
	public function getIconInitiallyReturnsAnEmptyString() {
		$this->fixture->setData(array());

		self::assertEquals(
			'',
			$this->fixture->getIcon()
		);
	}

	/**
	 * @test
	 */
	public function getIconWithNonEmptyIconReturnsIcon() {
		$this->fixture->setData(array('icon' => 'icon.gif'));

		self::assertEquals(
			'icon.gif',
			$this->fixture->getIcon()
		);
	}

	/**
	 * @test
	 */
	public function setIconSetsIcon() {
		$this->fixture->setIcon('icon.gif');

		self::assertEquals(
			'icon.gif',
			$this->fixture->getIcon()
		);
	}

	/**
	 * @test
	 */
	public function hasIconInitiallyReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasIcon()
		);
	}

	/**
	 * @test
	 */
	public function hasIconWithIconReturnsTrue() {
		$this->fixture->setIcon('icon.gif');

		self::assertTrue(
			$this->fixture->hasIcon()
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