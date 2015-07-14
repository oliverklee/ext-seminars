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
 * This test case holds all tests specific to single events.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_SingleEventTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Event
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_Event();
	}

	//////////////////////////////////
	// Tests regarding the subtitle.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getSubtitleForSingleEventWithoutSubtitleReturnsAnEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			'',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function getSubtitleForSingleEventWithSubtitleReturnsSubtitle() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'subtitle' => 'sub title',
			)
		);

		self::assertEquals(
			'sub title',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function setSubtitleForSingleEventSetsSubtitle() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSubtitle('sub title');

		self::assertEquals(
			'sub title',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleForSingleEventWithoutSubtitleReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		self::assertFalse(
			$this->fixture->hasSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleForSingleEventWithSubtitleReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSubtitle('sub title');

		self::assertTrue(
			$this->fixture->hasSubtitle()
		);
	}


	////////////////////////////////
	// Tests regarding the teaser.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getTeaserForSingleEventWithoutTeaserReturnsAnEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			'',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function getTeaserForSingleEventWithTeaserReturnsTeaser() {
		$this->fixture->setData(
			array(
				'teaser' => 'wow, this is teasing',
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
			)
		);

		self::assertEquals(
			'wow, this is teasing',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function setTeaserForSingleEventSetsTeaser() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setTeaser('wow, this is teasing');

		self::assertEquals(
			'wow, this is teasing',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserForSingleEventWithoutTeaserReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserForSingleEventWithTeaserReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'teaser' => 'wow, this is teasing',
			)
		);

		self::assertTrue(
			$this->fixture->hasTeaser()
		);
	}


	/////////////////////////////////////
	// Tests regarding the description.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getDescriptionForSingleEventWithoutDescriptionReturnsAnEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForSingleEventWithDescriptionReturnsDescription() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'description' => 'this is a great event.',
			)
		);

		self::assertEquals(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionForSingleEventSetsDescription() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setDescription('this is a great event.');

		self::assertEquals(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForSingleEventWithoutDescriptionReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForSingleEventWithDescriptionReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'description' => 'this is a great event.',
			)
		);

		self::assertTrue(
			$this->fixture->hasDescription()
		);
	}


	///////////////////////////////////////
	// Tests regarding the credit points.
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getCreditPointsForSingleEventWithoutCreditPointsReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			0,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function getCreditPointsForSingleEventWithPositiveCreditPointsReturnsCreditPoints() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'credit_points' => 42,
			)
		);

		self::assertEquals(
			42,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsForSingleEventWithNegativeCreditPointsThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $creditPoints must be >= 0.'
		);
		$this->fixture->setData(array());

		$this->fixture->setCreditPoints(-1);
	}

	/**
	 * @test
	 */
	public function setCreditPointsForSingleEventWithZeroCreditPointsSetsCreditPoints() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setCreditPoints(0);

		self::assertEquals(
			0,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsForSingleEventWithPositiveCreditPointsSetsCreditPoints() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setCreditPoints(42);

		self::assertEquals(
			42,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsForSingleEventWithoutCreditPointsReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsForSingleEventWithCreditPointsReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'credit_points' => 42,
			)
		);

		self::assertTrue(
			$this->fixture->hasCreditPoints()
		);
	}


	///////////////////////////////////////
	// Tests regarding the regular price.
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegularPriceForSingleEventWithoutRegularPriceReturnsZero() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_regular' => 0.00,
			)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularPriceForSingleEventWithPositiveRegularPriceReturnsRegularPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_regular' => 42.42
			)
		);

		self::assertEquals(
			42.42,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceForSingleEventWithNegativeRegularPriceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setRegularPrice(-1);
	}

	/**
	 * @test
	 */
	public function setRegularPriceForSingleEventWithZeroRegularPriceSetsRegularPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setRegularPrice(0.00);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceForSingleEventWithPositiveRegularPriceSetsRegularPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setRegularPrice(42.42);

		self::assertEquals(
			42.42,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceForSingleEventWithoutRegularPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceForSingleEventWithRegularPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_regular' => 42.42,
			)
		);

		self::assertTrue(
			$this->fixture->hasRegularPrice()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding the regular early bird price.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegularEarlyBirdPriceForSingleEventWithoutRegularEarlyBirdPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularEarlyBirdPriceForSingleEventWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_regular_early' => 42.42
			)
		);

		self::assertEquals(
			42.42,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForSingleEventWithNegativeRegularEarlyBirdPriceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setRegularEarlyBirdPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForSingleEventWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setRegularEarlyBirdPrice(0.00);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForSingleEventWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setRegularEarlyBirdPrice(42.42);

		self::assertEquals(
			42.42,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceForSingleEventWithoutRegularEarlyBirdPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceForSingleEventWithRegularEarlyBirdPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_regular_early' => 42.42,
			)
		);

		self::assertTrue(
			$this->fixture->hasRegularEarlyBirdPrice()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding the regular board price.
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegularBoardPriceForSingleEventWithoutRegularBoardPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularBoardPriceForSingleEventWithPositiveRegularBoardPriceReturnsRegularBoardPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_regular_board' => 42.42,
			)
		);

		self::assertEquals(
			42.42,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceForSingleEventWithNegativeRegularBoardPriceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setRegularBoardPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceForSingleEventWithZeroRegularBoardPriceSetsRegularBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setRegularBoardPrice(0.00);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceForSingleEventWithPositiveRegularBoardPriceSetsRegularBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setRegularBoardPrice(42.42);

		self::assertEquals(
			42.42,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceForSingleEventWithoutRegularBoardPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceForSingleEventWithRegularBoardPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_regular_board' => 42.42,
			)
		);

		self::assertTrue(
			$this->fixture->hasRegularBoardPrice()
		);
	}


	///////////////////////////////////////
	// Tests regarding the special price.
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getSpecialPriceForSingleEventWithoutSpecialPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialPriceForSingleEventWithSpecialPriceReturnsSpecialPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_special' => 42.42,
			)
		);

		self::assertEquals(
			42.42,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceForSingleEventWithNegativeSpecialPriceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setSpecialPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceForSingleEventWithZeroSpecialPriceSetsSpecialPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialPrice(0.00);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceForSingleEventWithPositiveSpecialPriceSetsSpecialPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialPrice(42.42);

		self::assertEquals(
			42.42,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceForSingleEventWithoutSpecialPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceForSingleEventWithSpecialPriceReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialPrice(42.42);

		self::assertTrue(
			$this->fixture->hasSpecialPrice()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding the special early bird price.
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSpecialEarlyBirdPriceForSingleEventWithoutSpecialEarlyBirdPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialEarlyBirdPriceForSingleEventWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_special_early' => 42.42,
			)
		);

		self::assertEquals(
			42.42,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceForSingleEventWithNegativeSpecialEarlyBirdPriceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setSpecialEarlyBirdPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceForSingleEventWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialEarlyBirdPrice(0.00);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceForSingleEventWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialEarlyBirdPrice(42.42);

		self::assertEquals(
			42.42,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceForSingleEventWithoutSpecialEarlyBirdPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceForSingleEventWithSpecialEarlyBirdPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_special_early' => 42.42,
			)
		);

		self::assertTrue(
			$this->fixture->hasSpecialEarlyBirdPrice()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding the special board price.
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getSpecialBoardPriceForSingleEventWithoutSpecialBoardPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialBoardPriceForSingleEventWithSpecialBoardPriceReturnsSpecialBoardPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'price_special_board' => 42.42,
			)
		);

		self::assertEquals(
			42.42,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceForSingleEventWithNegativeSpecialBoardPriceThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setSpecialBoardPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceForSingleEventWithZeroSpecialBoardPriceSetsSpecialBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialBoardPrice(0.00);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceForSingleEventWithPositiveSpecialBoardPriceSetsSpecialBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialBoardPrice(42.42);

		self::assertEquals(
			42.42,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceForSingleEventWithoutSpecialBoardPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceForSingleEventWithSpecialBoardPriceReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setSpecialBoardPrice(42.42);

		self::assertTrue(
			$this->fixture->hasSpecialBoardPrice()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding the additional information.
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAdditionalInformationForSingleEventWithoutAdditionalInformationReturnsEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			'',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalInformationForSingleEventWithAdditionalInformationReturnsAdditionalInformation() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'additional_information' => 'this is good to know',
			)
		);

		self::assertEquals(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function setAdditionalInformationForSingleEventSetsAdditionalInformation() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setAdditionalInformation('this is good to know');

		self::assertEquals(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationForSingleEventWithoutAdditionalInformationReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationForSingleEventWithAdditionalInformationReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'additional_information' => 'this is good to know',
			)
		);

		self::assertTrue(
			$this->fixture->hasAdditionalInformation()
		);
	}


	//////////////////////////////////////////////////
	// Tests regarding allowsMultipleRegistration().
	//////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function allowsMultipleRegistrationForSingleEventWithUnsetAllowsMultipleRegistrationReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->allowsMultipleRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function allowsMultipleRegistrationForSingleEventWithSetAllowsMultipleRegistrationReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'allows_multiple_registrations' => TRUE,
			)
		);

		self::assertTrue(
			$this->fixture->allowsMultipleRegistrations()
		);
	}


	//////////////////////////////////
	// Tests regarding usesTerms2().
	//////////////////////////////////

	/**
	 * @test
	 */
	public function usesTerms2ForSingleEventWithUnsetUseTerms2ReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->usesTerms2()
		);
	}

	/**
	 * @test
	 */
	public function usesTerms2ForSingleEventWithSetUseTerms2ReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'use_terms_2' => TRUE,
			)
		);

		self::assertTrue(
			$this->fixture->usesTerms2()
		);
	}


	///////////////////////////////
	// Tests regarding the notes.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getNotesForSingleEventWithoutNotesReturnsEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesForSingleEventWithNotesReturnsNotes() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'notes' => 'Don\'t forget this.',
			)
		);

		self::assertEquals(
			'Don\'t forget this.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function setNotesForSingleEventSetsNotes() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setNotes('Don\'t forget this.');

		self::assertEquals(
			'Don\'t forget this.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesForSingleEventWithoutNotesReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesForSingleEventWithNotesReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'notes' => 'Don\'t forget this.',
			)
		);

		self::assertTrue(
			$this->fixture->hasNotes()
		);
	}


	///////////////////////////////
	// Tests regarding the image.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getImageForSingleEventWithoutImageReturnsEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertEquals(
			'',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForSingleEventWithImageReturnsImage() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'image' => 'file.jpg',
			)
		);

		self::assertEquals(
			'file.jpg',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function setImageForSingleEventSetsImage() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);
		$this->fixture->setImage('file.jpg');

		self::assertEquals(
			'file.jpg',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForSingleEventWithoutImageReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		self::assertFalse(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForSingleEventWithImageReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'image' => 'file.jpg',
			)
		);

		self::assertTrue(
			$this->fixture->hasImage()
		);
	}
}