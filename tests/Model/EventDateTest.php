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
 * This test case holds all tests specific to event dates.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_EventDateTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Event
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_Event();
	}

	////////////////////////////////
	// Tests concerning the title.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTopicTitleReturnsTopicTitle() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('title' => 'Superhero'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
				'title' => 'Supervillain',
			)
		);

		self::assertSame(
			'Superhero',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getRawTitleWithNonEmptyTopicTitleReturnsDateTitle() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('title' => 'Superhero'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
				'title' => 'Supervillain',
			)
		);

		self::assertSame(
			'Supervillain',
			$this->fixture->getRawTitle()
		);
	}


	//////////////////////////////////
	// Tests regarding the subtitle.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getSubtitleForEventDateWithoutSubtitleReturnsAnEmptyString() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			'',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function getSubtitleForEventDateWithSubtitleReturnsSubtitle() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('subtitle' => 'sub title'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setSubtitleForEventDateSetsSubtitle() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setSubtitle('sub title');

		self::assertEquals(
			'sub title',
			$topic->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleForEventDateWithoutSubtitleReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleForEventDateWithSubtitleReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('subtitle' => 'sub title'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

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
	public function getTeaserForEventDateWithoutTeaserReturnsAnEmptyString() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			'',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function getTeaserForEventDateWithTeaserReturnsTeaser() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('teaser' => 'wow, this is teasing'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setTeaserForEventDateSetsTeaser() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setTeaser('wow, this is teasing');

		self::assertEquals(
			'wow, this is teasing',
			$topic->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserForEventDateWithoutTeaserReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserForEventDateWithTeaserReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('teaser' => 'wow, this is teasing'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getDescriptionForEventDateWithoutDescriptionReturnsAnEmptyString() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForEventDateWithDescriptionReturnsDescription() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('description' => 'this is a great event.')
			);
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setDescriptionForEventDateSetsDescription() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setDescription('this is a great event.');

		self::assertEquals(
			'this is a great event.',
			$topic->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForEventDateWithoutDescriptionReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForEventDateWithDescriptionReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('description' => 'this is a great event.')
			);
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getCreditPointsForEventDateWithoutCreditPointsReturnsZero() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			0,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function getCreditPointsForEventDateWithPositiveCreditPointsReturnsCreditPoints() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('credit_points' => 42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setCreditPointsForEventDateWithZeroCreditPointsSetsCreditPoints() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setCreditPoints(0);

		self::assertEquals(
			0,
			$topic->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsForEventDateWithPositiveCreditPointsSetsCreditPoints() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setCreditPoints(42);

		self::assertEquals(
			42,
			$topic->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsForEventDateWithoutCreditPointsReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsForEventDateWithCreditPointsReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('credit_points' => 42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getRegularPriceForEventDateWithoutRegularPriceReturnsZero() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_regular' => 0.00));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getRegularPriceForEventDateWithPositiveRegularPriceReturnsRegularPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_regular' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setRegularPriceForEventDateWithZeroRegularPriceSetsRegularPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setRegularPrice(0.00);

		self::assertEquals(
			0.00,
			$topic->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceForEventDateWithPositiveRegularPriceSetsRegularPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setRegularPrice(42.42);

		self::assertEquals(
			42.42,
			$topic->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceForEventDateWithoutRegularPriceReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceForEventDateWithRegularPriceReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_regular' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getRegularEarlyBirdPriceForEventDateWithoutRegularEarlyBirdPriceReturnsZero() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularEarlyBirdPriceForEventDateWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_regular_early' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setRegularEarlyBirdPriceForEventDateWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setRegularEarlyBirdPrice(0.00);

		self::assertEquals(
			0.00,
			$topic->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForEventDateWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setRegularEarlyBirdPrice(42.42);

		self::assertEquals(
			42.42,
			$topic->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceForEventDateWithoutRegularEarlyBirdPriceReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceForEventDateWithRegularEarlyBirdPriceReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_regular_early' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getRegularBoardPriceForEventDateWithoutRegularBoardPriceReturnsZero() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularBoardPriceForEventDateWithPositiveRegularBoardPriceReturnsRegularBoardPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_regular_board' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setRegularBoardPriceForEventDateWithZeroRegularBoardPriceSetsRegularBoardPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setRegularBoardPrice(0.00);

		self::assertEquals(
			0.00,
			$topic->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceForEventDateWithPositiveRegularBoardPriceSetsRegularBoardPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setRegularBoardPrice(42.42);

		self::assertEquals(
			42.42,
			$topic->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceForEventDateWithoutRegularBoardPriceReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceForEventDateWithRegularBoardPriceReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_regular_board' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getSpecialPriceForEventDateWithoutSpecialPriceReturnsZero() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialPriceForEventDateWithSpecialPriceReturnsSpecialPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_special' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setSpecialPriceForEventDateWithZeroSpecialPriceSetsSpecialPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setSpecialPrice(0.00);

		self::assertEquals(
			0.00,
			$topic->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceForEventDateWithPositiveSpecialPriceSetsSpecialPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setSpecialPrice(42.42);

		self::assertEquals(
			42.42,
			$topic->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceForEventDateWithoutSpecialPriceReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceForEventDateWithSpecialPriceReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_special' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

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
	public function getSpecialEarlyBirdPriceForEventDateWithoutSpecialEarlyBirdPriceReturnsZero() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_COMPLETE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialEarlyBirdPriceForEventDateWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_special_early' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setSpecialEarlyBirdPriceForEventDateWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setSpecialEarlyBirdPrice(0.00);

		self::assertEquals(
			0.00,
			$topic->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceForEventDateWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setSpecialEarlyBirdPrice(42.42);

		self::assertEquals(
			42.42,
			$topic->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceForEventDateWithoutSpecialEarlyBirdPriceReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceForEventDateWithSpecialEarlyBirdPriceReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_special_early' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getSpecialBoardPriceForEventDateWithoutSpecialBoardPriceReturnsZero() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			0.00,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsSpecialBoardPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_special_board' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setSpecialBoardPriceForEventDateWithZeroSpecialBoardPriceSetsSpecialBoardPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setSpecialBoardPrice(0.00);

		self::assertEquals(
			0.00,
			$topic->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceForEventDateWithPositiveSpecialBoardPriceSetsSpecialBoardPrice() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setSpecialBoardPrice(42.42);

		self::assertEquals(
			42.42,
			$topic->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceForEventDateWithoutSpecialBoardPriceReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('price_special_board' => 42.42));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

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
	public function getAdditionalInformationForEventDateWithoutAdditionalInformationReturnsEmptyString() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			'',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalInformationForEventDateWithAdditionalInformationReturnsAdditionalInformation() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('additional_information' => 'this is good to know')
			);
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setAdditionalInformationForEventDateSetsAdditionalInformation() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setAdditionalInformation('this is good to know');

		self::assertEquals(
			'this is good to know',
			$topic->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationForEventDateWithoutAdditionalInformationReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationForEventDateWithAdditionalInformationReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('additional_information' => 'this is good to know')
			);
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function allowsMultipleRegistrationForEventDateWithUnsetAllowsMultipleRegistrationReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->allowsMultipleRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function allowsMultipleRegistrationForEventDateWithSetAllowsMultipleRegistrationReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('allows_multiple_registrations' => TRUE)
			);
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function usesTerms2ForEventDateWithUnsetUseTerms2ReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->usesTerms2()
		);
	}

	/**
	 * @test
	 */
	public function usesTerms2ForEventDateWithSetUseTerms2ReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('use_terms_2' => TRUE));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getNotesForEventDateWithoutNotesReturnsEmptyString() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesForEventDateWithNotesReturnsNotes() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('notes' => 'Don\'t forget this.'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setNotesForEventDateSetsNotes() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setNotes('Don\'t forget this.');

		self::assertEquals(
			'Don\'t forget this.',
			$topic->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesForEventDateWithoutNotesReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesForEventDateWithNotesReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('notes' => 'Don\'t forget this.'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function getImageForEventDateWithoutImageReturnsEmptyString() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertEquals(
			'',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForEventDateWithImageReturnsImage() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('image' => 'file.jpg'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
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
	public function setImageForEventDateSetsImage() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);
		$this->fixture->setImage('file.jpg');

		self::assertEquals(
			'file.jpg',
			$topic->getImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForEventDateWithoutImageReturnsFalse() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array());
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertFalse(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForEventDateWithImageReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('image' => 'file.jpg'));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertTrue(
			$this->fixture->hasImage()
		);
	}


	/////////////////////////////////////////
	// Tests concerning the payment methods
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentMethodsReturnsPaymentMethodsFromTopic() {
		$paymentMethods = new tx_oelib_List();
		$topic = new tx_seminars_Model_Event();
		$topic->setData(array('payment_methods' => $paymentMethods));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		self::assertSame(
			$paymentMethods,
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function setPaymentMethodsThrowsException() {
		$this->setExpectedException(
			'BadMethodCallException',
			'setPaymentMethods may only be called on single events and event ' .
				'topics, but not on event dates.'
		);

		$topic = new tx_seminars_Model_Event();
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		$this->fixture->setPaymentMethods(new tx_oelib_List());
	}
}