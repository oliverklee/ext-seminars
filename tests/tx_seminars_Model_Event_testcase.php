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


	//////////////////////////////////
	// Tests regarding the subtitle.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getSubtitleWithoutSubtitleReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function getSubtitleWithSubtitleReturnsSubtitle() {
		$this->fixture->setData(array('subtitle' => 'sub title'));

		$this->assertEquals(
			'sub title',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function setSubtitleSetsSubtitle() {
		$this->fixture->setSubtitle('sub title');

		$this->assertEquals(
			'sub title',
			$this->fixture->getSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleWithoutSubtitleReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasSubtitle()
		);
	}

	/**
	 * @test
	 */
	public function hasSubtitleWithSubtitleReturnsTrue() {
		$this->fixture->setSubtitle('sub title');

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
	public function getTeaserWithoutTeaserReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function getTeaserWithTeaserReturnsTeaser() {
		$this->fixture->setData(array('teaser' => 'wow, this is teasing'));

		$this->assertEquals(
			'wow, this is teasing',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function setTeaserSetsTeaser() {
		$this->fixture->setTeaser('wow, this is teasing');

		$this->assertEquals(
			'wow, this is teasing',
			$this->fixture->getTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserWithoutTeaserReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasTeaser()
		);
	}

	/**
	 * @test
	 */
	public function hasTeaserWithTeaserReturnsTrue() {
		$this->fixture->setTeaser('wow, this is teasing');

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
	public function getDescriptionWithoutDescriptionReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionWithDescriptionReturnsDescription() {
		$this->fixture->setData(array('description' => 'this is a great event.'));

		$this->assertEquals(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionSetsDescription() {
		$this->fixture->setDescription('this is a great event.');

		$this->assertEquals(
			'this is a great event.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithoutDescriptionReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithDescriptionReturnsTrue() {
		$this->fixture->setDescription('this is a great event.');

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
	public function getCreditPointsWithoutCreditPointsReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function getCreditPointsWithPositiveCreditPointsReturnsCreditPoints() {
		$this->fixture->setData(array('credit_points' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsWithNegativeCreditPointsThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $creditPoints must be >= 0.'
		);

		$this->fixture->setCreditPoints(-1);
	}

	/**
	 * @test
	 */
	public function setCreditPointsWithZeroCreditPointsSetsCreditPoints() {
		$this->fixture->setCreditPoints(0);

		$this->assertEquals(
			0,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function setCreditPointsWithPositiveCreditPointsSetsCreditPoints() {
		$this->fixture->setCreditPoints(42);

		$this->assertEquals(
			42,
			$this->fixture->getCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsWithoutCreditPointsReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasCreditPoints()
		);
	}

	/**
	 * @test
	 */
	public function hasCreditPointsWithCreditPointsReturnsTrue() {
		$this->fixture->setCreditPoints(42);

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
	public function getDetailsPageIdWithoutDetailsPageReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getDetailsPageId()
		);
	}

	/**
	 * @test
	 */
	public function getDetailsPageIdWithDetailsPageReturnsDetailsPageId() {
		$this->fixture->setData(array('details_page' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getDetailsPageId()
		);
	}

	/**
	 * @test
	 */
	public function setDetailsPageIdWithNegativeDetailsPageIdThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $id must be >= 0.'
		);

		$this->fixture->setDetailsPageId(-1);
	}

	/**
	 * @test
	 */
	public function setDetailsPageIdWithZeroDetailsPageIdSetsDetailsPage() {
		$this->fixture->setDetailsPageId(0);

		$this->assertEquals(
			0,
			$this->fixture->getDetailsPageId()
		);
	}

	/**
	 * @test
	 */
	public function setDetailsPageIdWithPositiveDetailsPageIdSetsDetailsPage() {
		$this->fixture->setDetailsPageId(42);

		$this->assertEquals(
			42,
			$this->fixture->getDetailsPageId()
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
		$this->fixture->setDetailsPageId(42);

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
	public function getRegularPriceWithoutRegularPriceReturnsZero() {
		$this->fixture->setData(array('price_regular' => 0.00));

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularPriceWithPositiveRegularPriceReturnsRegularPrice() {
		$this->fixture->setData(array('price_regular' => 42.42));

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceWithNegativeRegularPriceThrowsException() {
		$this->setExpectedException('Exception', '');

		$this->fixture->setRegularPrice(-1);
	}

	/**
	 * @test
	 */
	public function setRegularPriceWithZeroRegularPriceSetsRegularPrice() {
		$this->fixture->setRegularPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularPriceWithPositiveRegularPriceSetsRegularPrice() {
		$this->fixture->setRegularPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceWithoutRegularPriceReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasRegularPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularPriceWithRegularPriceReturnsTrue() {
		$this->fixture->setRegularPrice(42);

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
	public function getRegularEarlyBirdPriceWithoutRegularEarlyBirdPriceReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularEarlyBirdPriceWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice() {
		$this->fixture->setData(array('price_regular_early' => 42.42));

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceWithNegativeRegularEarlyBirdPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setRegularEarlyBirdPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$this->fixture->setRegularEarlyBirdPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularEarlyBirdPriceWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice() {
		$this->fixture->setRegularEarlyBirdPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceWithoutRegularEarlyBirdPriceReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasRegularEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularEarlyBirdPriceWithRegularEarlyBirdPriceReturnsTrue() {
		$this->fixture->setRegularEarlyBirdPrice(42.42);

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
	public function getRegularBoardPriceWithoutRegularBoardPriceReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getRegularBoardPriceWithPositiveRegularBoardPriceReturnsRegularBoardPrice() {
		$this->fixture->setData(array('price_regular_board' => 42.42));

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceWithNegativeRegularBoardPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setRegularBoardPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceWithZeroRegularBoardPriceSetsRegularBoardPrice() {
		$this->fixture->setRegularBoardPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setRegularBoardPriceWithPositiveRegularBoardPriceSetsRegularBoardPrice() {
		$this->fixture->setRegularBoardPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getRegularBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceWithoutRegularBoardPriceReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasRegularBoadPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasRegularBoardPriceWithRegularBoardPriceReturnsTrue() {
		$this->fixture->setRegularBoardPrice(42.42);

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
	public function getSpecialPriceWithoutSpecialPriceReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialPriceWithSpecialPriceReturnsSpecialPrice() {
		$this->fixture->setData(array('price_special' => 42.42));

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceWithNegativeSpecialPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setSpecialPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceWithZeroSpecialPriceSetsSpecialPrice() {
		$this->fixture->setSpecialPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialPriceWithPositiveSpecialPriceSetsSpecialPrice() {
		$this->fixture->setSpecialPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceWithoutSpecialPriceReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasSpecialPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialPriceWithSpecialPriceReturnsTrue() {
		$this->fixture->setSpecialPrice(42.42);

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
	public function getSpecialEarlyBirdPriceWithoutSpecialEarlyBirdPriceReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialEarlyBirdPriceWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice() {
		$this->fixture->setData(array('price_special_early' => 42.42));

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceWithNegativeSpecialEarlyBirdPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setSpecialEarlyBirdPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$this->fixture->setSpecialEarlyBirdPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialEarlyBirdPriceWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice() {
		$this->fixture->setSpecialEarlyBirdPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceWithoutSpecialEarlyBirdPriceReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasSpecialEarlyBirdPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialEarlyBirdPriceWithSpecialEarlyBirdPriceReturnsTrue() {
		$this->fixture->setSpecialEarlyBirdPrice(42.42);

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
	public function getSpecialBoardPriceWithoutSpecialBoardPriceReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function getSpecialBoardPriceWithSpecialBoardPriceReturnsSpecialBoardPrice() {
		$this->fixture->setData(array('price_special_board' => 42.42));

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceWithNegativeSpecialBoardPriceThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $price must be >= 0.00.'
		);

		$this->fixture->setSpecialBoardPrice(-1.00);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceWithZeroSpecialBoardPriceSetsSpecialBoardPrice() {
		$this->fixture->setSpecialBoardPrice(0.00);

		$this->assertEquals(
			0.00,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function setSpecialBoardPriceWithPositiveSpecialBoardPriceSetsSpecialBoardPrice() {
		$this->fixture->setSpecialBoardPrice(42.42);

		$this->assertEquals(
			42.42,
			$this->fixture->getSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceWithoutSpecialBoardPriceReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasSpecialBoardPrice()
		);
	}

	/**
	 * @test
	 */
	public function hasSpecialBoardPriceWithSpecialBoardPriceReturnsTrue() {
		$this->fixture->setSpecialBoardPrice(42.42);

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
	public function getAdditionalInformationWithoutAdditionalInformationReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function getAdditionalInformationWithAdditionalInformationReturnsAdditionalInformation() {
		$this->fixture->setData(array('additional_information' => 'this is good to know'));

		$this->assertEquals(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function setAdditionalInformationSetsAdditionalInformation() {
		$this->fixture->setAdditionalInformation('this is good to know');

		$this->assertEquals(
			'this is good to know',
			$this->fixture->getAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationWithoutAdditionalInformationReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAdditionalInformation()
		);
	}

	/**
	 * @test
	 */
	public function hasAdditionalInformationWithAdditionalInformationReturnsTrue() {
		$this->fixture->setAdditionalInformation('this is good to know');

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
	public function allowsMultipleRegistrationWithUnsetAllowsMultipleRegistrationReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->allowsMultipleRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function allowsMultipleRegistrationWithSetAllowsMultipleRegistrationReturnsTrue() {
		$this->fixture->setData(array('allows_multiple_registrations' => true));

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

	public function setMaximumAttendeesWithNegativeMaximumAttendeesThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $maximumAttendees must be >= 0.'
		);

		$this->fixture->setMaximumAttendees(-1);
	}

	public function setMaximumAttendeesWithZeroMaximumAttendeesSetsMaximumAttendees() {
		$this->fixture->setMaximumAttendees(0);

		$this->assertEquals(
			0,
			$this->fixture->getMaximumAttendees()
		);
	}

	public function setMaximumAttendeesWithPositiveAttendeesSetsMaximumAttendees() {
		$this->fixture->setMaximumAttendees(42);

		$this->assertEquals(
			42,
			$this->fixture->getMaximumAttendees()
		);
	}

	public function hasMaximumAttendeesWithoutMaximumAttendeesReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasMaximumAttendees()
		);
	}

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


	/////////////////////////////////
	// Tests regarding useTerms2().
	/////////////////////////////////

	/**
	 * @test
	 */
	public function useTerms2WithUnsetUseTerms2ReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->useTerms2()
		);
	}

	/**
	 * @test
	 */
	public function useTerms2WithSetUseTerms2ReturnsTrue() {
		$this->fixture->setData(array('use_terms_2' => true));

		$this->assertTrue(
			$this->fixture->useTerms2()
		);
	}


	///////////////////////////////
	// Tests regarding the notes.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getNotesWithoutNotesReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesWithNotesReturnsNotes() {
		$this->fixture->setData(array('notes' => 'Don\'t forget this.'));

		$this->assertEquals(
			'Don\'t forget this.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function setNotesSetsNotes() {
		$this->fixture->setNotes('Don\'t forget this.');

		$this->assertEquals(
			'Don\'t forget this.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesWithoutNotesReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasNotes()
		);
	}

	/**
	 * @test
	 */
	public function hasNotesWithNotesReturnsTrue() {
		$this->fixture->setNotes('Don\'t forget this.');

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
	public function getImageWithoutImageReturnsEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImageWithImageReturnsImage() {
		$this->fixture->setData(array('image' => 'file.jpg'));

		$this->assertEquals(
			'file.jpg',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function setImageSetsImage() {
		$this->fixture->setImage('file.jpg');

		$this->assertEquals(
			'file.jpg',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageWithoutImageReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasImage()
		);
	}

	/**
	 * @test
	 */
	public function hasImageWithImageReturnsTrue() {
		$this->fixture->setImage('file.jpg');

		$this->assertTrue(
			$this->fixture->hasImage()
		);
	}
}
?>