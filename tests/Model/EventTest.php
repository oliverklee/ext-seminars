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
 * Testcase for the 'event model' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_EventTest extends tx_phpunit_testcase {
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


	/////////////////////////////////////
	// Tests regarding the record type.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getRecordTypeWithRecordTypeCompleteReturnsRecordTypeComplete() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_COMPLETE)
		);

		$this->assertEquals(
			tx_seminars_Model_Event::TYPE_COMPLETE,
			$this->fixture->getRecordType()
		);
	}

	/**
	 * @test
	 */
	public function getRecordTypeWithRecordTypeDateReturnsRecordTypeDate() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_DATE)
		);

		$this->assertEquals(
			tx_seminars_Model_Event::TYPE_DATE,
			$this->fixture->getRecordType()
		);
	}

	/**
	 * @test
	 */
	public function getRecordTypeWithRecordTypeTopicReturnsRecordTypeTopic() {
		$this->fixture->setData(
			array('object_type' => tx_seminars_Model_Event::TYPE_TOPIC)
		);

		$this->assertEquals(
			tx_seminars_Model_Event::TYPE_TOPIC,
			$this->fixture->getRecordType()
		);
	}


	////////////////////////////////
	// Tests concerning the title.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Superhero'));

		$this->assertSame(
			'Superhero',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getRawTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Superhero'));

		$this->assertSame(
			'Superhero',
			$this->fixture->getRawTitle()
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


	///////////////////////////////////////////////////
	// Tests concerning the combined single view page
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageInitiallyReturnsEmptyString() {
		$this->fixture->setData(array('categories' => new tx_oelib_List()));

		$this->assertEquals(
			'',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageForAvailableDetailsPageUidReturnsTheDetailsPageUid() {
		$this->fixture->setData(array(
			'details_page' => '5', 'categories' => new tx_oelib_List()
		));

		$this->assertEquals(
			'5',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageForAvailableDetailsPageUrlReturnsTheDetailsPageUrl() {
		$this->fixture->setData(array(
			'details_page' => 'www.example.com', 'categories' => new tx_oelib_List()
		));

		$this->assertEquals(
			'www.example.com',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageForAvailableEventTypeWithoutSingleViewPageReturnsEmptyString() {
		$eventType = new tx_seminars_Model_EventType();
		$eventType->setData(array());
		$this->fixture->setData(array(
			'event_type' => $eventType, 'categories' => new tx_oelib_List()
		));

		$this->assertEquals(
			'',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageForAvailableEventTypeWithSingleViewPageReturnsSingleViewPageFromEventType() {
		$eventType = new tx_seminars_Model_EventType();
		$eventType->setData(array('single_view_page' => 42));
		$this->fixture->setData(array(
			'event_type' => $eventType, 'categories' => new tx_oelib_List()
		));

		$this->assertEquals(
			'42',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageForAvailableCategoryWithoutSingleViewPageReturnsEmptyString() {
		$category = new tx_seminars_Model_Category();
		$category->setData(array());
		$categories = new tx_oelib_List();
		$categories->add($category);
		$this->fixture->setData(array('categories' => $categories));

		$this->assertEquals(
			'',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageForAvailableCategoryTypeWithSingleViewPageReturnsSingleViewPageFromCategory() {
		$category = new tx_seminars_Model_Category();
		$category->setData(array('single_view_page' => 42));
		$categories = new tx_oelib_List();
		$categories->add($category);
		$this->fixture->setData(array('categories' => $categories));

		$this->assertEquals(
			'42',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageForTwoAvailableCategoriesWithSingleViewPageReturnsSingleViewPageFromFirstCategory() {
		$category1 = new tx_seminars_Model_Category();
		$category1->setData(array('single_view_page' => 42));
		$category2 = new tx_seminars_Model_Category();
		$category2->setData(array('single_view_page' => 12));
		$categories = new tx_oelib_List();
		$categories->add($category1);
		$categories->add($category2);
		$this->fixture->setData(array('categories' => $categories));

		$this->assertEquals(
			'42',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function hasCombinedSingleViewPageForEmptySingleViewPageReturnsFalse() {
		$fixture = $this->getMock(
			'tx_seminars_Model_Event', array('getCombinedSingleViewPage')
		);
		$fixture->expects($this->atLeastOnce())
			->method('getCombinedSingleViewPage')->will($this->returnValue(''));

		$this->assertFalse(
			$fixture->hasCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function hasCombinedSingleViewPageForNonEmptySingleViewPageReturnsTrue() {
		$fixture = $this->getMock(
			'tx_seminars_Model_Event', array('getCombinedSingleViewPage')
		);
		$fixture->expects($this->atLeastOnce())
			->method('getCombinedSingleViewPage')->will($this->returnValue(42));

		$this->assertTrue(
			$fixture->hasCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageUsesDetailsPageInsteadOfEventTypeIfBothAreAvailable() {
		$eventType = new tx_seminars_Model_EventType();
		$eventType->setData(array('single_view_page' => 42));

		$this->fixture->setData(array(
			'details_page' => '5',
			'event_type' => $eventType,
			'categories' => new tx_oelib_List(),
		));

		$this->assertEquals(
			'5',
			$this->fixture->getCombinedSingleViewPage()
		);
	}

	/**
	 * @test
	 */
	public function getCombinedSingleViewPageUsesEventTypeInsteadOfCategoriesIfBothAreAvailable() {
		$eventType = new tx_seminars_Model_EventType();
		$eventType->setData(array('single_view_page' => 42));
		$category = new tx_seminars_Model_Category();
		$category->setData(array('single_view_page' => 91));
		$categories = new tx_oelib_List();
		$categories->add($category);

		$this->fixture->setData(array(
			'event_type' => $eventType,
			'categories' => $categories,
		));

		$this->assertEquals(
			'42',
			$this->fixture->getCombinedSingleViewPage()
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

		$this->assertSame(
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

		$this->assertSame(
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
		$this->fixture->setData(array('event_takes_place_reminder_sent' => TRUE));

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
		$this->fixture->setData(array('cancelation_deadline_reminder_sent' => TRUE));

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
		$this->fixture->setData(array('needs_registration' => TRUE));

		$this->assertTrue(
			$this->fixture->needsRegistration()
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
		$this->fixture->setData(array('queue_size' => TRUE));

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
		$this->fixture->setData(array('skip_collision_check' => TRUE));

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


	//////////////////////////////////////////
	// Tests concerning the publication hash
	//////////////////////////////////////////

	public function test_hasPublicationHashForNoPublicationHashSet_ReturnsFalse() {
		$this->fixture->setData(array('publication_hash' => ''));

		$this->assertFalse(
			$this->fixture->hasPublicationHash()
		);
	}

	public function test_hasPublicationHashForPublicationHashSet_ReturnsTrue() {
		$this->fixture->setData(array('publication_hash' => 'fooo'));

		$this->assertTrue(
			$this->fixture->hasPublicationHash()
		);
	}

	public function test_getPublicationHashForNoPublicationHashSet_ReturnsEmptyString() {
		$this->fixture->setData(array('publication_hash' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getPublicationHash()
		);
	}

	public function test_getPublicationHashForPublicationHashSet_ReturnsPublicationHash() {
		$this->fixture->setData(array('publication_hash' => 'fooo'));

		$this->assertEquals(
			'fooo',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function setPublicationHashSetsPublicationHash() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		$this->assertEquals(
			'5318761asdf35as5sad35asd35asd',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function setPublicationHashWithEmptyStringOverridesNonEmptyData() {
		$this->fixture->setData(array('publication_hash' => 'fooo'));

		$this->fixture->setPublicationHash('');

		$this->assertEquals(
			'',
			$this->fixture->getPublicationHash()
		);
	}

	public function test_purgePublicationHashForPublicationHashSetInModel_PurgesPublicationHash() {
		$this->fixture->setData(array('publication_hash' => 'fooo'));

		$this->fixture->purgePublicationHash();

		$this->assertFalse(
			$this->fixture->hasPublicationHash()
		);
	}

	public function test_purgePublicationHashForNoPublicationHashSetInModel_PurgesPublicationHash() {
		$this->fixture->setData(array('publication_hash' => ''));

		$this->fixture->purgePublicationHash();

		$this->assertFalse(
			$this->fixture->hasPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithoutPublicationHashIsTrue() {
		$this->fixture->setPublicationHash('');

		$this->assertTrue(
			$this->fixture->isPublished()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithPublicationHashIsFalse() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		$this->assertFalse(
			$this->fixture->isPublished()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning hasOfflineRegistrations
	/////////////////////////////////////////////

	public function test_hasOfflineRegistrations_ForEventWithoutOfflineRegistrations_ReturnsFalse() {
		$this->fixture->setData(array('offline_attendees' => 0));

		$this->assertFalse(
			$this->fixture->hasOfflineRegistrations()
		);
	}

	public function test_hasOfflineRegistrations_ForEventWithTwoOfflineRegistrations_ReturnsTrue() {
		$this->fixture->setData(array('offline_attendees' => 2));

		$this->assertTrue(
			$this->fixture->hasOfflineRegistrations()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning getOfflineRegistrations
	/////////////////////////////////////////////

	public function test_getOfflineRegistrations_ForEventWithoutOfflineRegistrations_ReturnsZero() {
		$this->fixture->setData(array('offline_attendees' => 0));

		$this->assertEquals(
			0,
			$this->fixture->getOfflineRegistrations()
		);
	}

	public function test_getOfflineRegistrations_ForEventWithTwoOfflineRegistrations_ReturnsTwo() {
		$this->fixture->setData(array('offline_attendees' => 2));

		$this->assertEquals(
			2,
			$this->fixture->getOfflineRegistrations()
		);
	}


	///////////////////////////////////////
	// Tests concerning the registrations
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegistrationsReturnsRegistrations() {
		$registrations = new tx_oelib_List();

		$this->fixture->setData(array('registrations' => $registrations));

		$this->assertSame(
			$registrations,
			$this->fixture->getRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationsSetsRegistrations() {
		$registrations = new tx_oelib_List();

		$this->fixture->setRegistrations($registrations);

		$this->assertSame(
			$registrations,
			$this->fixture->getRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function getRegularRegistrationsReturnsRegularRegistrations() {
		$registrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('registration_queue' => 0)
			);
		$registrations->add($registration);
		$this->fixture->setRegistrations($registrations);

		$this->assertEquals(
			$registration->getUid(),
			$this->fixture->getRegularRegistrations()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getRegularRegistrationsNotReturnsQueueRegistrations() {
		$registrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('registration_queue' => 1)
			);
		$registrations->add($registration);
		$this->fixture->setRegistrations($registrations);

		$this->assertTrue(
			$this->fixture->getRegularRegistrations()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getQueueRegistrationsReturnsQueueRegistrations() {
		$registrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('registration_queue' => 1)
			);
		$registrations->add($registration);
		$this->fixture->setRegistrations($registrations);

		$this->assertEquals(
			$registration->getUid(),
			$this->fixture->getQueueRegistrations()->getUids()
		);
	}

	/**
	 * @test
	 */
	public function getQueueRegistrationsNotReturnsRegularRegistrations() {
		$registrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('registration_queue' => 0)
			);
		$registrations->add($registration);
		$this->fixture->setRegistrations($registrations);

		$this->assertTrue(
			$this->fixture->getQueueRegistrations()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function hasQueueRegistrationsForOneQueueRegistrationReturnsTrue() {
		$registrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('registration_queue' => 1)
			);
		$registrations->add($registration);
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getQueueRegistrations')
		);
		$event->expects($this->any())->method('getQueueRegistrations')
			->will($this->returnValue($registrations));

		$this->assertTrue(
			$event->hasQueueRegistrations()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function hasQueueRegistrationsForNoQueueRegistrationReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getQueueRegistrations')
		);
		$event->expects($this->any())->method('getQueueRegistrations')
			->will($this->returnValue(new tx_oelib_List()));

		$this->assertFalse(
			$event->hasQueueRegistrations()
		);

		$event->__destruct();
	}


	//////////////////////////////////////////////////////////////////////
	// Tests concerning hasUnlimitedVacancies
	//////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForMaxAttendeesZeroReturnsTrue() {
		$this->fixture->setData(array('attendees_max' => 0));

		$this->assertTrue(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForMaxAttendeesOneReturnsFalse() {
		$this->fixture->setData(array('attendees_max' => 1));

		$this->assertFalse(
			$this->fixture->hasUnlimitedVacancies()
		);
	}


	////////////////////////////////////////
	// Tests concerning getRegisteredSeats
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegisteredSeatsForNoRegularRegistrationsReturnsZero() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegularRegistrations')
		);
		$event->setData(array());
		$event->expects($this->any())->method('getRegularRegistrations')
			->will($this->returnValue(new tx_oelib_List()));

		$this->assertEquals(
			0,
			$event->getRegisteredSeats()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegisteredSeatsCountsSingleSeatRegularRegistrations() {
		$registrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('seats' => 1)
			);
		$registrations->add($registration);
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegularRegistrations')
		);
		$event->setData(array());
		$event->expects($this->any())->method('getRegularRegistrations')
			->will($this->returnValue($registrations));

		$this->assertEquals(
			1,
			$event->getRegisteredSeats()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegisteredSeatsCountsMultiSeatRegularRegistrations() {
		$registrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('seats' => 2)
			);
		$registrations->add($registration);
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegularRegistrations')
		);
		$event->setData(array());
		$event->expects($this->any())->method('getRegularRegistrations')
			->will($this->returnValue($registrations));

		$this->assertEquals(
			2,
			$event->getRegisteredSeats()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegisteredSeatsNotCountsQueueRegistrations() {
		$queueRegistrations = new tx_oelib_List();
		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getLoadedTestingModel(
				array('seats' => 1)
			);
		$queueRegistrations->add($registration);
		$event = $this->getMock(
			'tx_seminars_Model_Event',
			array('getRegularRegistrations', 'getQueueRegistrations')
		);
		$event->setData(array());
		$event->expects($this->any())->method('getQueueRegistrations')
			->will($this->returnValue($queueRegistrations));
		$event->expects($this->any())->method('getRegularRegistrations')
			->will($this->returnValue(new tx_oelib_List()));

		$this->assertEquals(
			0,
			$event->getRegisteredSeats()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getRegisteredSeatsCountsOfflineRegistrations() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegularRegistrations')
		);
		$event->setData(array('offline_attendees' => 2));
		$event->expects($this->any())->method('getRegularRegistrations')
			->will($this->returnValue(new tx_oelib_List()));

		$this->assertEquals(
			2,
			$event->getRegisteredSeats()
		);

		$event->__destruct();
	}


	////////////////////////////////////////////
	// Tests concerning hasEnoughRegistrations
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasEnoughRegistrationsForZeroSeatsAndZeroNeededReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_min' => 0));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(0));

		$this->assertTrue(
			$event->hasEnoughRegistrations()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function hasEnoughRegistrationsForLessSeatsThanNeededReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_min' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(1));

		$this->assertFalse(
			$event->hasEnoughRegistrations()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function hasEnoughRegistrationsForAsManySeatsAsNeededReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_min' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertTrue(
			$event->hasEnoughRegistrations()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function hasEnoughRegistrationsForMoreSeatsThanNeededReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_min' => 1));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertTrue(
			$event->hasEnoughRegistrations()
		);

		$event->__destruct();
	}


	//////////////////////////////////
	// Tests concerning getVacancies
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getVacanciesForOneRegisteredAndTwoMaximumReturnsOne() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(1));

		$this->assertEquals(
			1,
			$event->getVacancies()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getVacanciesForAsManySeatsRegisteredAsMaximumReturnsZero() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertEquals(
			0,
			$event->getVacancies()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsZero() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 1));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertEquals(
			0,
			$event->getVacancies()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function getVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsZero() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(1));

		$this->assertEquals(
			0,
			$event->getVacancies()
		);

		$event->__destruct();
	}


	//////////////////////////////////
	// Tests concerning hasVacancies
	//////////////////////////////////

	/**
	 * @test
	 */
	public function hasVacanciesForOneRegisteredAndTwoMaximumReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(1));

		$this->assertTrue(
			$event->hasVacancies()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function hasVacanciesForAsManySeatsRegisteredAsMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertFalse(
			$event->hasVacancies()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function hasVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 1));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertFalse(
			$event->hasVacancies()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function hasVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(1));

		$this->assertTrue(
			$event->hasVacancies()
		);

		$event->__destruct();
	}


	////////////////////////////
	// Tests concerning isFull
	////////////////////////////

	/**
	 * @test
	 */
	public function isFullForLessSeatsThanMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(1));

		$this->assertFalse(
			$event->isFull()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function isFullForAsManySeatsAsMaximumReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertTrue(
			$event->isFull()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function isFullForMoreSeatsThanMaximumReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 1));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(2));

		$this->assertTrue(
			$event->isFull()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function isFullForZeroSeatsAndUnlimitedMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(0));

		$this->assertFalse(
			$event->isFull()
		);

		$event->__destruct();
	}

	/**
	 * @test
	 */
	public function isFullForPositiveSeatsAndUnlimitedMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects($this->any())->method('getRegisteredSeats')
			->will($this->returnValue(1));

		$this->assertFalse(
			$event->isFull()
		);

		$event->__destruct();
	}


	////////////////////////////////////////
	// Tests concerning attachRegistration
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function attachRegistrationAddsRegistration() {
		$this->fixture->setRegistrations(new tx_oelib_List());

		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array());
		$this->fixture->attachRegistration($registration);

		$this->assertTrue(
			$this->fixture->getRegistrations()->hasUid($registration->getUid())
		);
	}

	/**
	 * @test
	 */
	public function attachRegistrationNotRemovesExistingRegistration() {
		$registrations = new tx_oelib_List();
		$oldRegistration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')->getNewGhost();
		$registrations->add($oldRegistration);
		$this->fixture->setRegistrations($registrations);

		$newRegistration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array());
		$this->fixture->attachRegistration($newRegistration);

		$this->assertTrue(
			$this->fixture->getRegistrations()->hasUid($oldRegistration->getUid())
		);
	}

	/**
	 * @test
	 */
	public function attachRegistrationSetsEventForRegistration() {
		$this->fixture->setRegistrations(new tx_oelib_List());

		$registration = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Registration')
			->getLoadedTestingModel(array());
		$this->fixture->attachRegistration($registration);

		$this->assertSame(
			$this->fixture,
			$registration->getEvent()
		);
	}


	/////////////////////////////////////////
	// Tests concerning the payment methods
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPaymentMethodsReturnsPaymentMethods() {
		$paymentMethods = new tx_oelib_List();
		$this->fixture->setData(
			array('payment_methods' => $paymentMethods)
		);

		$this->assertSame(
			$paymentMethods,
			$this->fixture->getPaymentMethods()
		);
	}

	/**
	 * @test
	 */
	public function setPaymentMethodsSetsPaymentMethods() {
		$this->fixture->setData(array());

		$paymentMethods = new tx_oelib_List();
		$this->fixture->setPaymentMethods($paymentMethods);

		$this->assertSame(
			$paymentMethods,
			$this->fixture->getPaymentMethods()
		);
	}
}
?>