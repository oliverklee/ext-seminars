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
class tx_seminars_Model_TargetGroupTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_TargetGroup
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_TargetGroup();
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
		$this->fixture->setTitle('Housewives');

		self::assertEquals(
			'Housewives',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Housewives'));

		self::assertEquals(
			'Housewives',
			$this->fixture->getTitle()
		);
	}


	/////////////////////////////////////
	// Tests concerning the minimum age
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getMinimumAgeWithNoMinimumAgeSetReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getMinimumAge()
		);
	}

	/**
	 * @test
	 */
	public function getMinimumAgeWithNonZeroMinimumAgeReturnsMinimumAge() {
		$this->fixture->setData(array('minimum_age' => 18));

		self::assertEquals(
			18,
			$this->fixture->getMinimumAge()
		);
	}

	/**
	 * @test
	 */
	public function setMinimumAgeSetsMinimumAge() {
		$this->fixture->setMinimumAge(18);

		self::assertEquals(
			18,
			$this->fixture->getMinimumAge()
		);
	}


	/////////////////////////////////////
	// Tests concerning the maximum age
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getMaximumAgeWithNoMaximumAgeSetReturnsZero() {
		$this->fixture->setData(array());

		self::assertEquals(
			0,
			$this->fixture->getMaximumAge()
		);
	}

	/**
	 * @test
	 */
	public function getMaximumAgeWithNonZeroMaximumAgeReturnsMaximumAge() {
		$this->fixture->setData(array('maximum_age' => 18));

		self::assertEquals(
			18,
			$this->fixture->getMaximumAge()
		);
	}

	/**
	 * @test
	 */
	public function setMaximumAgeSetsMaximumAge() {
		$this->fixture->setMaximumAge(18);

		self::assertEquals(
			18,
			$this->fixture->getMaximumAge()
		);
	}
}