<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the 'event model' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_Event_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Event
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_Event();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	/////////////////////////////////////
	// Tests regarding isSingleEvent().
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function isSingleEventForSingleRecordReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$this->assertTrue(
			$this->fixture->isSingleEvent()
		);
	}

	/**
	 * @test
	 */
	public function isSingleEventForTopicRecordReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->isSingleEvent()
		);
	}

	/**
	 * @test
	 */
	public function isSingleEventForDateRecordReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
		);

		$this->assertFalse(
			$this->fixture->isSingleEvent()
		);
	}


	///////////////////////////////////
	// Tests regarding isEventDate().
	///////////////////////////////////

	/**
	 * @test
	 */
	public function isEventDateForSingleRecordReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$this->assertFalse(
			$this->fixture->isEventDate()
		);
	}

	/**
	 * @test
	 */
	public function isEventDateForTopicRecordReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->isEventDate()
		);
	}

	/**
	 * @test
	 */
	public function isEventDateForDateRecordReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
		);

		$this->assertTrue(
			$this->fixture->isEventDate()
		);
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function getSubtitleForEventTopicWithoutSubtitleReturnsAnEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			'',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function getSubtitleForEventTopicWithSubtitleReturnsSubtitle() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'subtitle' => 'sub title',
			)
		);

		$this->assertEquals(
			'sub title',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function setSubtitleForEventTopicSetsSubtitle() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSubtitle('sub title');

		$this->assertEquals(
			'sub title',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleForEventTopicWithoutSubtitleReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleForEventTopicWithSubtitleReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSubtitle('sub title');

		$this->assertTrue(
			$this->fixture->hasSubtitle()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasTeaser()
		);
	}

	/**
	 * @test
	 */
	public function getTeaserForEventTopicWithoutTeaserReturnsAnEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			'',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function getTeaserForEventTopicWithTeaserReturnsTeaser() {
		$this->fixture->setData(
			array(
				'teaser' => 'wow, this is teasing',
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
			)
		);

		$this->assertEquals(
			'wow, this is teasing',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function setTeaserForEventTopicSetsTeaser() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setTeaser('wow, this is teasing');

		$this->assertEquals(
			'wow, this is teasing',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserForEventTopicWithoutTeaserReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserForEventTopicWithTeaserReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'teaser' => 'wow, this is teasing',
			)
		);

		$this->assertTrue(
			$this->fixture->hasTeaser()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForEventTopicWithoutDescriptionReturnsAnEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForEventTopicWithDescriptionReturnsDescription() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'description' => 'this is a great event.',
			)
		);

		$this->assertEquals(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionForEventTopicSetsDescription() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setDescription('this is a great event.');

		$this->assertEquals(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForEventTopicWithoutDescriptionReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForEventTopicWithDescriptionReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setDescription('this is a great event.');

		$this->assertTrue(
			$this->fixture->hasDescription()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasDescription()
		);
	}


	//////////////////////////////////////////////
	// Tests regarding the accreditation number.
	//////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAccreditationNumberWithoutAccreditationNumberReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function getAccreditationNumberWithAccreditationNumberReturnsAccreditationNumber() {
		$this->fixture->setData(array('accreditation_number' => 'a1234567890'));

		$this->assertEquals(
			'a1234567890',
			$this->fixture->getAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function setAccreditationNumberSetsAccreditationNumber() {
		$this->fixture->setAccreditationNumber('a1234567890');

		$this->assertEquals(
			'a1234567890',
			$this->fixture->getAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function hasAccreditationNumberWithoutAccreditationNumberReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function hasAccreditationNumberWithAccreditationNumberReturnsTrue() {
		$this->fixture->setAccreditationNumber('a1234567890');

		$this->assertTrue(
			$this->fixture->hasAccreditationNumber()
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

		$this->assertEquals(
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

		$this->assertEquals(
			42,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsForSingleEventWithNegativeCreditPointsThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $creditPoints must be >= 0.'
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function getCreditPointsForEventTopicWithoutCreditPointsReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			0,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function getCreditPointsForEventTopicWithPositiveCreditPointsReturnsCreditPoints() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 42,
			)
		);

		$this->assertEquals(
			42,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsForEventTopicWithZeroCreditPointsSetsCreditPoints() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setCreditPoints(0);

		$this->assertEquals(
			0,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsForEventTopicWithPositiveCreditPointsSetsCreditPoints() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setCreditPoints(42);

		$this->assertEquals(
			42,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsForEventTopicWithoutCreditPointsReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsForEventTopicWithCreditPointsReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'credit_points' => 42,
			)
		);

		$this->assertTrue(
			$this->fixture->hasCreditPoints()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasCreditPoints()
		);
	}


	///////////////////////////////////////////////
	// Tests regarding the registration deadline.
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegistrationDeadlineAsUnixTimeStampWithoutRegistrationDeadlineReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineReturnsRegistrationDeadline() {
		$this->fixture->setData(array('deadline_registration' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDeadlineAsUnixTimeStampWithNegativeRegistrationDeadlineThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $registrationDeadline must be >= 0.'
		);

		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setRegistrationDeadlineAsUnixTimeStampWithZeroRegistrationDeadlineSetsRegistrationDeadline() {
		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(0);

		$this->assertEquals(
			0,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineSetsRegistrationDeadline() {
		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(42);

		$this->assertEquals(
			42,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationDeadlineWithoutRegistrationDeadlineReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasRegistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationDeadlineWithRegistrationDeadlineReturnsTrue() {
		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(42);

		$this->assertTrue(
			$this->fixture->hasRegistrationDeadline()
		);
	}


	/////////////////////////////////////////////
	// Tests regarding the early bird deadline.
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEarlyBirdDeadlineAsUnixTimeStampWithoutEarlyBirdDeadlineReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getEarlyBirdDeadlineAsUnixTimeStampWithPositiveEarlyBirdDeadlineReturnsEarlyBirdDeadline() {
		$this->fixture->setData(array('deadline_early_bird' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEarlyBirdDeadlineAsUnixTimeStampWithNegativeEarlyBirdDeadlineThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $earlyBirdDeadline must be >= 0.'
		);

		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setEarlyBirdDeadlineAsUnixTimeStampWithZeroEarlyBirdDeadlineSetsEarlyBirdDeadline() {
		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(0);

		$this->assertEquals(
			0,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEarlyBirdDeadlineWithPositiveEarlyBirdDeadlineSetsEarlyBirdDeadline() {
		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(42);

		$this->assertEquals(
			42,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasEarlyBirdDeadlineWithoutEarlyBirdDeadlineReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasEarlyBirdDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasEarlyBirdDeadlineWithEarlyBirdDeadlineReturnsTrue() {
		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(42);

		$this->assertTrue(
			$this->fixture->hasEarlyBirdDeadline()
		);
	}


	/////////////////////////////////////////////////
	// Tests regarding the unregistration deadline.
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineAsUnixTimeStampWithoutUnregistrationDeadlineReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineReturnsUnregistrationDeadline() {
		$this->fixture->setData(array('deadline_unregistration' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setUnregistrationDeadlineAsUnixTimeStampWithNegativeUnregistrationDeadlineThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $unregistrationDeadline must be >= 0.'
		);

		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setUnregistrationDeadlineAsUnixTimeStampWithZeroUnregistrationDeadlineSetsUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(0);

		$this->assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineSetsUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(42);

		$this->assertEquals(
			42,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasUnregistrationDeadlineWithoutUnregistrationDeadlineReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasUnregistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasUnregistrationDeadlineWithUnregistrationDeadlineReturnsTrue() {
		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(42);

		$this->assertTrue(
			$this->fixture->hasUnregistrationDeadline()
		);
	}


	////////////////////////////////
	// Tests regarding the expiry.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getExpiryAsUnixTimeStampWithoutExpiryReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getExpiryAsUnixTimeStampWithPositiveExpiryReturnsExpiry() {
		$this->fixture->setData(array('expiry' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setExpiryAsUnixTimeStampWithNegativeExpiryThrowsException() {
		$this->setExpectedException('Exception', '');

		$this->fixture->setExpiryAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setExpiryAsUnixTimeStampWithZeroExpirySetsExpiry() {
		$this->fixture->setExpiryAsUnixTimeStamp(0);

		$this->assertEquals(
			0,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setExpiryAsUnixTimeStampWithPositiveExpirySetsExpiry() {
		$this->fixture->setExpiryAsUnixTimeStamp(42);

		$this->assertEquals(
			42,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasExpiryWithoutExpiryReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasExpiry()
		);
	}

	/**
	 * @test
	 */
	public function hasExpiryWithExpiryReturnsTrue() {
		$this->fixture->setExpiryAsUnixTimeStamp(42);

		$this->assertTrue(
			$this->fixture->hasExpiry()
		);
	}


	//////////////////////////////////////
	// Tests regarding the details page.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getDetailsPageWithoutDetailsPageReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function getDetailsPageWithDetailsPageReturnsDetailsPage() {
		$this->fixture->setData(array('details_page' => 'http://example.com'));

		$this->assertEquals(
			'http://example.com',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function setDetailsPageSetsDetailsPage() {
		$this->fixture->setDetailsPage('http://example.com');

		$this->assertEquals(
			'http://example.com',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function hasDetailsPageWithoutDetailsPageReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function hasDetailsPageWithDetailsPageReturnsTrue() {
		$this->fixture->setDetailsPage('http://example.com');

		$this->assertTrue(
			$this->fixture->hasDetailsPage()
		);
	}


	//////////////////////////////////
	// Tests regarding our language.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getLanguageWithoutLanguageReturnsNull() {
		$this->fixture->setData(array());

		$this->assertNull(
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageWithLanguageReturnsLanguage() {
		$this->fixture->setData(array('language' => 'DE'));

		$this->assertEquals(
			tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Language')
				->findByIsoAlpha2Code('DE'),
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageSetsLanguage() {
		$language = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Language')
			->findByIsoAlpha2Code('DE');
		$this->fixture->setLanguage($language);

		$this->assertEquals(
			$language,
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithoutLanguageReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithLanguageReturnsTrue() {
		$language = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Language')
			->findByIsoAlpha2Code('DE');
		$this->fixture->setLanguage($language);

		$this->assertTrue(
			$this->fixture->hasLanguage()
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

		$this->assertEquals(
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

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceForSingleEventWithNegativeRegularPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularPriceForEventTopicWithoutRegularPriceReturnsZero() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_regular' => 0.00,
			)
		);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularPriceForEventTopicWithPositiveRegularPriceReturnsRegularPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_regular' => 42.42
			)
		);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceForEventTopicWithZeroRegularPriceSetsRegularPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setRegularPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceForEventTopicWithPositiveRegularPriceSetsRegularPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setRegularPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceForEventTopicWithoutRegularPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceForEventTopicWithRegularPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_regular' => 42.42
			)
		);

		$this->assertTrue(
			$this->fixture->hasRegularPrice()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForSingleEventWithNegativeRegularEarlyBirdPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularEarlyBirdPriceForEventTopicWithoutRegularEarlyBirdPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularEarlyBirdPriceForEventTopicWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_regular_early' => 42.42
			)
		);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForEventTopicWithNegativeRegularEarlyBirdPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setRegularEarlyBirdPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForEventTopicWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setRegularEarlyBirdPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceForEventTopicWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setRegularEarlyBirdPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceForEventTopicWithoutRegularEarlyBirdPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceForEventTopicWithRegularEarlyBirdPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_regular_early' => 42.42,
			)
		);

		$this->assertTrue(
			$this->fixture->hasRegularEarlyBirdPrice()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceForSingleEventWithNegativeRegularBoardPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
			$this->fixture->hasRegularBoadPrice()
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

		$this->assertTrue(
			$this->fixture->hasRegularBoadPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularBoardPriceForEventTopicWithoutRegularBoardPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularBoardPriceForEventTopicWithPositiveRegularBoardPriceReturnsRegularBoardPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_regular_board' => 42.42,
			)
		);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceForEventTopicWithZeroRegularBoardPriceSetsRegularBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setRegularBoardPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceForEventTopicWithPositiveRegularBoardPriceSetsRegularBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setRegularBoardPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceForEventTopicWithoutRegularBoardPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasRegularBoadPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceForEventTopicWithRegularBoardPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_regular_board' => 42.42,
			)
		);

		$this->assertTrue(
			$this->fixture->hasRegularBoadPrice()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
			$this->fixture->hasRegularBoadPrice()
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

		$this->assertTrue(
			$this->fixture->hasRegularBoadPrice()
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

		$this->assertEquals(
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

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceForSingleEventWithNegativeSpecialPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialPriceForEventTopicWithoutSpecialPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialPriceForEventTopicWithSpecialPriceReturnsSpecialPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_special' => 42.42,
			)
		);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceForEventTopicWithZeroSpecialPriceSetsSpecialPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceForEventTopicWithPositiveSpecialPriceSetsSpecialPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceForEventTopicWithoutSpecialPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceForEventTopicWithSpecialPriceReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialPrice(42.42);

		$this->assertTrue(
			$this->fixture->hasSpecialPrice()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceForSingleEventWithNegativeSpecialEarlyBirdPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialEarlyBirdPriceForEventTopicWithoutSpecialEarlyBirdPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialEarlyBirdPriceForEventTopicWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_special_early' => 42.42,
			)
		);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceForEventTopicWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialEarlyBirdPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceForEventTopicWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialEarlyBirdPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceForEventTopicWithoutSpecialEarlyBirdPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceForEventTopicWithSpecialEarlyBirdPriceReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_special_early' => 42.42,
			)
		);

		$this->assertTrue(
			$this->fixture->hasSpecialEarlyBirdPrice()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceForSingleEventWithNegativeSpecialBoardPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialBoardPriceForEventTopicWithoutSpecialBoardPriceReturnsZero() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialBoardPriceForEventTopicWithSpecialBoardPriceReturnsSpecialBoardPrice() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'price_special_board' => 42.42,
			)
		);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceForEventTopicWithZeroSpecialBoardPriceSetsSpecialBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialBoardPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceForEventTopicWithPositiveSpecialBoardPriceSetsSpecialBoardPrice() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialBoardPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceForEventTopicWithoutSpecialBoardPriceReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceForEventTopicWithSpecialBoardPriceReturnsTrue() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setSpecialBoardPrice(42.42);

		$this->assertTrue(
			$this->fixture->hasSpecialBoardPrice()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalInformationForEventTopicWithoutAdditionalInformationReturnsEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			'',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalInformationForEventTopicWithAdditionalInformationReturnsAdditionalInformation() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'additional_information' => 'this is good to know',
			)
		);

		$this->assertEquals(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function setAdditionalInformationForEventTopicSetsAdditionalInformation() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setAdditionalInformation('this is good to know');

		$this->assertEquals(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationForEventTopicWithoutAdditionalInformationReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationForEventTopicWithAdditionalInformationReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'additional_information' => 'this is good to know',
			)
		);

		$this->assertTrue(
			$this->fixture->hasAdditionalInformation()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasAdditionalInformation()
		);
	}


	//////////////////////////////////////////////////////////
	// Tests regarding eventTakesPlaceReminderHasBeenSent().
	//////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function eventTakesPlaceReminderHasBeenSentWithUnsetEventTakesPlaceReminderSentReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->eventTakesPlaceReminderHasBeenSent()
		);
	}

	/**
	 * @test
	 */
	public function eventTakesPlaceReminderHasBeenSentWithSetEventTakesPlaceReminderSentReturnsTrue() {
		$this->fixture->setData(array('event_takes_place_reminder_sent' => true));

		$this->assertTrue(
			$this->fixture->eventTakesPlaceReminderHasBeenSent()
		);
	}


	//////////////////////////////////////////////////////////////
	// Tests regarding cancelationDeadlineReminderHasBeenSent().
	//////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function cancelationDeadlineReminderHasBeenSentWithUnsetCancelationDeadlineReminderSentReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->cancelationDeadlineReminderHasBeenSent()
		);
	}

	/**
	 * @test
	 */
	public function cancelationDeadlineReminderHasBeenSentWithSetCancelationDeadlineReminderSentReturnsTrue() {
		$this->fixture->setData(array('cancelation_deadline_reminder_sent' => true));

		$this->assertTrue(
			$this->fixture->cancelationDeadlineReminderHasBeenSent()
		);
	}


	/////////////////////////////////////////
	// Tests regarding needsRegistration().
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function needsRegistrationWithUnsetNeedsRegistrationReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->needsRegistration()
		);
	}

	/**
	 * @test
	 */
	public function needsRegistrationWithSetNeedsRegistrationReturnsTrue() {
		$this->fixture->setData(array('needs_registration' => true));

		$this->assertTrue(
			$this->fixture->needsRegistration()
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

		$this->assertFalse(
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
				'allows_multiple_registrations' => true,
			)
		);

		$this->assertTrue(
			$this->fixture->allowsMultipleRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function allowsMultipleRegistrationForEventTopicWithUnsetAllowsMultipleRegistrationReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->allowsMultipleRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function allowsMultipleRegistrationForEventTopicWithSetAllowsMultipleRegistrationReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'allows_multiple_registrations' => true,
			)
		);

		$this->assertTrue(
			$this->fixture->allowsMultipleRegistrations()
		);
	}

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

		$this->assertFalse(
			$this->fixture->allowsMultipleRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function allowsMultipleRegistrationForEventDateWithSetAllowsMultipleRegistrationReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(
				array('allows_multiple_registrations' => true)
			);
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		$this->assertTrue(
			$this->fixture->allowsMultipleRegistrations()
		);
	}


	///////////////////////////////////////////
	// Tests regarding the minimum attendees.
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getMinimumAttendeesWithoutMinimumAttendeesReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function getMinimumAttendeesWithPositiveMinimumAttendeesReturnsMinimumAttendees() {
		$this->fixture->setData(array('attendees_min' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMinimumAttendeesWithNegativeMinimumAttendeesThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $minimumAttendees must be >= 0.'
		);

		$this->fixture->setMinimumAttendees(-1);
	}

	/**
	 * @test
	 */
	public function setMinimumAttendeesWithZeroMinimumAttendeesSetsMinimumAttendees() {
		$this->fixture->setMinimumAttendees(0);

		$this->assertEquals(
			0,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMinimumAttendeesWithPositiveMinimumAttendeesSetsMinimumAttendees() {
		$this->fixture->setMinimumAttendees(42);

		$this->assertEquals(
			42,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMinimumAttendeesWithoutMinimumAttendeesReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMinimumAttendeesWithMinimumAttendeesReturnsTrue() {
		$this->fixture->setMinimumAttendees(42);

		$this->assertTrue(
			$this->fixture->hasMinimumAttendees()
		);
	}


	///////////////////////////////////////////
	// Tests regarding the maximum attendees.
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function getMaximumAttendeesWithoutMaximumAttendeesReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function getMaximumAttendeesWithMaximumAttendeesReturnsMaximumAttendees() {
		$this->fixture->setData(array('attendees_max' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMaximumAttendeesWithNegativeMaximumAttendeesThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $maximumAttendees must be >= 0.'
		);

		$this->fixture->setMaximumAttendees(-1);
	}

	/**
	 * @test
	 */
	public function setMaximumAttendeesWithZeroMaximumAttendeesSetsMaximumAttendees() {
		$this->fixture->setMaximumAttendees(0);

		$this->assertEquals(
			0,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMaximumAttendeesWithPositiveAttendeesSetsMaximumAttendees() {
		$this->fixture->setMaximumAttendees(42);

		$this->assertEquals(
			42,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMaximumAttendeesWithoutMaximumAttendeesReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMaximumAttendeesWithMaximumAttendeesReturnsTrue() {
		$this->fixture->setMaximumAttendees(42);

		$this->assertTrue(
			$this->fixture->hasMaximumAttendees()
		);
	}


	////////////////////////////////////////////
	// Tests regarding hasRegistrationQueue().
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasRegistrationQueueWithoutRegistrationQueueReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationQueueWithRegistrationQueueReturnsTrue() {
		$this->fixture->setData(array('queue_size' => true));

		$this->assertTrue(
			$this->fixture->hasRegistrationQueue()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding shouldSkipCollisionCheck().
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function shouldSkipCollectionCheckWithoutSkipCollsionCheckReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->shouldSkipCollisionCheck()
		);
	}

	/**
	 * @test
	 */
	public function shouldSkipCollectionCheckWithSkipCollisionCheckReturnsTrue() {
		$this->fixture->setData(array('skip_collision_check' => true));

		$this->assertTrue(
			$this->fixture->shouldSkipCollisionCheck()
		);
	}


	////////////////////////////////
	// Tests regarding the status.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getStatusWithoutStatusReturnsStatusPlanned() {
		$this->fixture->setData(array());

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_PLANNED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function getStatusWithStatusPlannedReturnsStatusPlanned() {
		$this->fixture->setData(
			array('cancelled' => tx_seminars_Model_Event::STATUS_PLANNED)
		);

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_PLANNED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function getStatusWithStatusCanceledReturnStatusCanceled() {
		$this->fixture->setData(
			array('cancelled' => tx_seminars_Model_Event::STATUS_CANCELED)
		);

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_CANCELED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function getStatusWithStatusConfirmedReturnsStatusConfirmed() {
		$this->fixture->setData(
			array('cancelled' => tx_seminars_Model_Event::STATUS_CONFIRMED)
		);

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_CONFIRMED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function setStatusWithInvalidStatusThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The parameter $status must be either STATUS_PLANNED, ' .
				'STATUS_CANCELED or STATUS_CONFIRMED'
		);

		$this->fixture->setStatus(-1);
	}

	/**
	 * @test
	 */
	public function setStatusWithStatusPlannedSetsStatus() {
		$this->fixture->setStatus(tx_seminars_Model_Event::STATUS_PLANNED);

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_PLANNED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function setStatusWithStatusCanceledSetsStatus() {
		$this->fixture->setStatus(tx_seminars_Model_Event::STATUS_CANCELED);

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_CANCELED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function setStatusWithStatusConfirmedSetsStatus() {
		$this->fixture->setStatus(tx_seminars_Model_Event::STATUS_CONFIRMED);

		$this->assertEquals(
			tx_seminars_Model_Event::STATUS_CONFIRMED,
			$this->fixture->getStatus()
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

		$this->assertFalse(
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
				'use_terms_2' => true,
			)
		);

		$this->assertTrue(
			$this->fixture->usesTerms2()
		);
	}

	/**
	 * @test
	 */
	public function usesTerms2ForEventTopicWithUnsetUseTerms2ReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->usesTerms2()
		);
	}

	/**
	 * @test
	 */
	public function usesTerms2ForEventTopicWithSetUseTerms2ReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'use_terms_2' => true,
			)
		);

		$this->assertTrue(
			$this->fixture->usesTerms2()
		);
	}

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

		$this->assertFalse(
			$this->fixture->usesTerms2()
		);
	}

	/**
	 * @test
	 */
	public function usesTerms2ForEventDateWithSetUseTerms2ReturnsTrue() {
		$topic = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->getLoadedTestingModel(array('use_terms_2' => true));
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_DATE,
				'topic' => $topic,
			)
		);

		$this->assertTrue(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesForEventTopicWithoutNotesReturnsEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesForEventTopicWithNotesReturnsNotes() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'notes' => 'Don\'t forget this.',
			)
		);

		$this->assertEquals(
			'Don\'t forget this.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function setNotesForEventTopicSetsNotes() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setNotes('Don\'t forget this.');

		$this->assertEquals(
			'Don\'t forget this.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesForEventTopicWithoutNotesReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesForEventTopicWithNotesReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'notes' => 'Don\'t forget this.',
			)
		);

		$this->assertTrue(
			$this->fixture->hasNotes()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasNotes()
		);
	}


	////////////////////////////////////////
	// Tests regarding the attached files.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAttachedFilesWithoutAttachedFilesReturnsEmptyArray() {
		$this->fixture->setData(array());

		$this->assertEquals(
			array(),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithOneAttachedFileReturnsArrayWithAttachedFile() {
		$this->fixture->setData(array('attached_files' => 'file.txt'));

		$this->assertEquals(
			array('file.txt'),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithTwoAttachedFilesReturnsArrayWithBothAttachedFiles() {
		$this->fixture->setData(array('attached_files' => 'file.txt,file2.txt'));

		$this->assertEquals(
			array('file.txt', 'file2.txt'),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function setAttachedFilesSetsAttachedFiles() {
		$this->fixture->setAttachedFiles(array('file.txt'));

		$this->assertEquals(
			array('file.txt'),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesWithoutAttachedFilesReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesWithAttachedFileReturnsTrue() {
		$this->fixture->setAttachedFiles(array('file.txt'));

		$this->assertTrue(
			$this->fixture->hasAttachedFiles()
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForEventTopicWithoutImageReturnsEmptyString() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			'',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageForEventTopicWithImageReturnsImage() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'image' => 'file.jpg',
			)
		);

		$this->assertEquals(
			'file.jpg',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function setImageForEventTopicSetsImage() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);
		$this->fixture->setImage('file.jpg');

		$this->assertEquals(
			'file.jpg',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForEventTopicWithoutImageReturnsFalse() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertFalse(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageForEventTopicWithImageReturnsTrue() {
		$this->fixture->setData(
			array(
				'object_type' => tx_seminars_Model_Event::TYPE_TOPIC,
				'image' => 'file.jpg',
			)
		);

		$this->assertTrue(
			$this->fixture->hasImage()
		);
	}

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertFalse(
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

		$this->assertTrue(
			$this->fixture->hasImage()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding the registration begin date
	////////////////////////////////////////////////

	public function test_hasRegistrationBegin_ForNoRegistrationBegin_ReturnsFalse() {
		$this->fixture->setData(array('begin_date_registration' => 0));

		$this->assertFalse(
			$this->fixture->hasRegistrationBegin()
		);
	}

	public function test_hasRegistrationBegin_ForEventWithRegistrationBegin_ReturnsTrue() {
		$this->fixture->setData(array('begin_date_registration' => 42));

		$this->assertTrue(
			$this->fixture->hasRegistrationBegin()
		);
	}

	public function test_getRegistrationBeginAsUnixTimestamp_ForEventWithoutRegistrationBegin_ReturnsZero() {
		$this->fixture->setData(array('begin_date_registration' => 0));

		$this->assertEquals(
			0,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}

	public function test_getRegistrationBeginAsUnixTimestamp_ForEventWithRegistrationBegin_ReturnsRegistrationBeginAsUnixTimestamp() {
		$this->fixture->setData(array('begin_date_registration' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}
}
?>