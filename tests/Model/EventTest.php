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
class tx_seminars_Model_EventTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Event
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_Event();
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

		self::assertTrue(
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

		self::assertFalse(
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

		self::assertFalse(
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

		self::assertFalse(
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

		self::assertFalse(
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

		self::assertTrue(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertSame(
			'Superhero',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getRawTitleWithNonEmptyTitleReturnsTitle() {
		$this->fixture->setData(array('title' => 'Superhero'));

		self::assertSame(
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

		self::assertEquals(
			'',
			$this->fixture->getAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function getAccreditationNumberWithAccreditationNumberReturnsAccreditationNumber() {
		$this->fixture->setData(array('accreditation_number' => 'a1234567890'));

		self::assertEquals(
			'a1234567890',
			$this->fixture->getAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function setAccreditationNumberSetsAccreditationNumber() {
		$this->fixture->setAccreditationNumber('a1234567890');

		self::assertEquals(
			'a1234567890',
			$this->fixture->getAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function hasAccreditationNumberWithoutAccreditationNumberReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasAccreditationNumber()
		);
	}

	/**
	 * @test
	 */
	public function hasAccreditationNumberWithAccreditationNumberReturnsTrue() {
		$this->fixture->setAccreditationNumber('a1234567890');

		self::assertTrue(
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

		self::assertEquals(
			0,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineReturnsRegistrationDeadline() {
		$this->fixture->setData(array('deadline_registration' => 42));

		self::assertEquals(
			42,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDeadlineAsUnixTimeStampWithNegativeRegistrationDeadlineThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $registrationDeadline must be >= 0.'
		);

		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setRegistrationDeadlineAsUnixTimeStampWithZeroRegistrationDeadlineSetsRegistrationDeadline() {
		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(0);

		self::assertEquals(
			0,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineSetsRegistrationDeadline() {
		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(42);

		self::assertEquals(
			42,
			$this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationDeadlineWithoutRegistrationDeadlineReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasRegistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationDeadlineWithRegistrationDeadlineReturnsTrue() {
		$this->fixture->setRegistrationDeadlineAsUnixTimeStamp(42);

		self::assertTrue(
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

		self::assertEquals(
			0,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getEarlyBirdDeadlineAsUnixTimeStampWithPositiveEarlyBirdDeadlineReturnsEarlyBirdDeadline() {
		$this->fixture->setData(array('deadline_early_bird' => 42));

		self::assertEquals(
			42,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEarlyBirdDeadlineAsUnixTimeStampWithNegativeEarlyBirdDeadlineThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $earlyBirdDeadline must be >= 0.'
		);

		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setEarlyBirdDeadlineAsUnixTimeStampWithZeroEarlyBirdDeadlineSetsEarlyBirdDeadline() {
		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(0);

		self::assertEquals(
			0,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setEarlyBirdDeadlineWithPositiveEarlyBirdDeadlineSetsEarlyBirdDeadline() {
		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(42);

		self::assertEquals(
			42,
			$this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasEarlyBirdDeadlineWithoutEarlyBirdDeadlineReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasEarlyBirdDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasEarlyBirdDeadlineWithEarlyBirdDeadlineReturnsTrue() {
		$this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(42);

		self::assertTrue(
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

		self::assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineReturnsUnregistrationDeadline() {
		$this->fixture->setData(array('deadline_unregistration' => 42));

		self::assertEquals(
			42,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setUnregistrationDeadlineAsUnixTimeStampWithNegativeUnregistrationDeadlineThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $unregistrationDeadline must be >= 0.'
		);

		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setUnregistrationDeadlineAsUnixTimeStampWithZeroUnregistrationDeadlineSetsUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(0);

		self::assertEquals(
			0,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineSetsUnregistrationDeadline() {
		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(42);

		self::assertEquals(
			42,
			$this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasUnregistrationDeadlineWithoutUnregistrationDeadlineReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasUnregistrationDeadline()
		);
	}

	/**
	 * @test
	 */
	public function hasUnregistrationDeadlineWithUnregistrationDeadlineReturnsTrue() {
		$this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(42);

		self::assertTrue(
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

		self::assertEquals(
			0,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function getExpiryAsUnixTimeStampWithPositiveExpiryReturnsExpiry() {
		$this->fixture->setData(array('expiry' => 42));

		self::assertEquals(
			42,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setExpiryAsUnixTimeStampWithNegativeExpiryThrowsException() {
		$this->setExpectedException('InvalidArgumentException');

		$this->fixture->setExpiryAsUnixTimeStamp(-1);
	}

	/**
	 * @test
	 */
	public function setExpiryAsUnixTimeStampWithZeroExpirySetsExpiry() {
		$this->fixture->setExpiryAsUnixTimeStamp(0);

		self::assertEquals(
			0,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function setExpiryAsUnixTimeStampWithPositiveExpirySetsExpiry() {
		$this->fixture->setExpiryAsUnixTimeStamp(42);

		self::assertEquals(
			42,
			$this->fixture->getExpiryAsUnixTimeStamp()
		);
	}

	/**
	 * @test
	 */
	public function hasExpiryWithoutExpiryReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasExpiry()
		);
	}

	/**
	 * @test
	 */
	public function hasExpiryWithExpiryReturnsTrue() {
		$this->fixture->setExpiryAsUnixTimeStamp(42);

		self::assertTrue(
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

		self::assertEquals(
			'',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function getDetailsPageWithDetailsPageReturnsDetailsPage() {
		$this->fixture->setData(array('details_page' => 'http://example.com'));

		self::assertEquals(
			'http://example.com',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function setDetailsPageSetsDetailsPage() {
		$this->fixture->setDetailsPage('http://example.com');

		self::assertEquals(
			'http://example.com',
			$this->fixture->getDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function hasDetailsPageWithoutDetailsPageReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasDetailsPage()
		);
	}

	/**
	 * @test
	 */
	public function hasDetailsPageWithDetailsPageReturnsTrue() {
		$this->fixture->setDetailsPage('http://example.com');

		self::assertTrue(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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
		$fixture->expects(self::atLeastOnce())
			->method('getCombinedSingleViewPage')->will(self::returnValue(''));

		self::assertFalse(
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
		$fixture->expects(self::atLeastOnce())
			->method('getCombinedSingleViewPage')->will(self::returnValue(42));

		self::assertTrue(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertNull(
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function getLanguageWithLanguageReturnsLanguage() {
		$this->fixture->setData(array('language' => 'DE'));

		/** @var tx_oelib_Mapper_Language $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Language');
		self::assertSame(
			$mapper->findByIsoAlpha2Code('DE'),
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function setLanguageSetsLanguage() {
		/** @var tx_oelib_Mapper_Language $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Language');
		$language = $mapper->findByIsoAlpha2Code('DE');
		$this->fixture->setLanguage($language);

		self::assertSame(
			$language,
			$this->fixture->getLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithoutLanguageReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasLanguage()
		);
	}

	/**
	 * @test
	 */
	public function hasLanguageWithLanguageReturnsTrue() {
		/** @var tx_oelib_Mapper_Language $mapper */
		$mapper = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Language');
		$language = $mapper->findByIsoAlpha2Code('DE');
		$this->fixture->setLanguage($language);

		self::assertTrue(
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

		self::assertFalse(
			$this->fixture->eventTakesPlaceReminderHasBeenSent()
		);
	}

	/**
	 * @test
	 */
	public function eventTakesPlaceReminderHasBeenSentWithSetEventTakesPlaceReminderSentReturnsTrue() {
		$this->fixture->setData(array('event_takes_place_reminder_sent' => TRUE));

		self::assertTrue(
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

		self::assertFalse(
			$this->fixture->cancelationDeadlineReminderHasBeenSent()
		);
	}

	/**
	 * @test
	 */
	public function cancelationDeadlineReminderHasBeenSentWithSetCancelationDeadlineReminderSentReturnsTrue() {
		$this->fixture->setData(array('cancelation_deadline_reminder_sent' => TRUE));

		self::assertTrue(
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

		self::assertFalse(
			$this->fixture->needsRegistration()
		);
	}

	/**
	 * @test
	 */
	public function needsRegistrationWithSetNeedsRegistrationReturnsTrue() {
		$this->fixture->setData(array('needs_registration' => TRUE));

		self::assertTrue(
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

		self::assertEquals(
			0,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function getMinimumAttendeesWithPositiveMinimumAttendeesReturnsMinimumAttendees() {
		$this->fixture->setData(array('attendees_min' => 42));

		self::assertEquals(
			42,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMinimumAttendeesWithNegativeMinimumAttendeesThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $minimumAttendees must be >= 0.'
		);

		$this->fixture->setMinimumAttendees(-1);
	}

	/**
	 * @test
	 */
	public function setMinimumAttendeesWithZeroMinimumAttendeesSetsMinimumAttendees() {
		$this->fixture->setMinimumAttendees(0);

		self::assertEquals(
			0,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMinimumAttendeesWithPositiveMinimumAttendeesSetsMinimumAttendees() {
		$this->fixture->setMinimumAttendees(42);

		self::assertEquals(
			42,
			$this->fixture->getMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMinimumAttendeesWithoutMinimumAttendeesReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasMinimumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMinimumAttendeesWithMinimumAttendeesReturnsTrue() {
		$this->fixture->setMinimumAttendees(42);

		self::assertTrue(
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

		self::assertEquals(
			0,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function getMaximumAttendeesWithMaximumAttendeesReturnsMaximumAttendees() {
		$this->fixture->setData(array('attendees_max' => 42));

		self::assertEquals(
			42,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMaximumAttendeesWithNegativeMaximumAttendeesThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $maximumAttendees must be >= 0.'
		);

		$this->fixture->setMaximumAttendees(-1);
	}

	/**
	 * @test
	 */
	public function setMaximumAttendeesWithZeroMaximumAttendeesSetsMaximumAttendees() {
		$this->fixture->setMaximumAttendees(0);

		self::assertEquals(
			0,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function setMaximumAttendeesWithPositiveAttendeesSetsMaximumAttendees() {
		$this->fixture->setMaximumAttendees(42);

		self::assertEquals(
			42,
			$this->fixture->getMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMaximumAttendeesWithoutMaximumAttendeesReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasMaximumAttendees()
		);
	}

	/**
	 * @test
	 */
	public function hasMaximumAttendeesWithMaximumAttendeesReturnsTrue() {
		$this->fixture->setMaximumAttendees(42);

		self::assertTrue(
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

		self::assertFalse(
			$this->fixture->hasRegistrationQueue()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationQueueWithRegistrationQueueReturnsTrue() {
		$this->fixture->setData(array('queue_size' => TRUE));

		self::assertTrue(
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

		self::assertFalse(
			$this->fixture->shouldSkipCollisionCheck()
		);
	}

	/**
	 * @test
	 */
	public function shouldSkipCollectionCheckWithSkipCollisionCheckReturnsTrue() {
		$this->fixture->setData(array('skip_collision_check' => TRUE));

		self::assertTrue(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
			tx_seminars_Model_Event::STATUS_CONFIRMED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function setStatusWithInvalidStatusThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
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

		self::assertEquals(
			tx_seminars_Model_Event::STATUS_PLANNED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function setStatusWithStatusCanceledSetsStatus() {
		$this->fixture->setStatus(tx_seminars_Model_Event::STATUS_CANCELED);

		self::assertEquals(
			tx_seminars_Model_Event::STATUS_CANCELED,
			$this->fixture->getStatus()
		);
	}

	/**
	 * @test
	 */
	public function setStatusWithStatusConfirmedSetsStatus() {
		$this->fixture->setStatus(tx_seminars_Model_Event::STATUS_CONFIRMED);

		self::assertEquals(
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

		self::assertEquals(
			array(),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithOneAttachedFileReturnsArrayWithAttachedFile() {
		$this->fixture->setData(array('attached_files' => 'file.txt'));

		self::assertEquals(
			array('file.txt'),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function getAttachedFilesWithTwoAttachedFilesReturnsArrayWithBothAttachedFiles() {
		$this->fixture->setData(array('attached_files' => 'file.txt,file2.txt'));

		self::assertEquals(
			array('file.txt', 'file2.txt'),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function setAttachedFilesSetsAttachedFiles() {
		$this->fixture->setAttachedFiles(array('file.txt'));

		self::assertEquals(
			array('file.txt'),
			$this->fixture->getAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesWithoutAttachedFilesReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasAttachedFiles()
		);
	}

	/**
	 * @test
	 */
	public function hasAttachedFilesWithAttachedFileReturnsTrue() {
		$this->fixture->setAttachedFiles(array('file.txt'));

		self::assertTrue(
			$this->fixture->hasAttachedFiles()
		);
	}


	////////////////////////////////////////////////
	// Tests regarding the registration begin date
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasRegistrationBeginForNoRegistrationBeginReturnsFalse() {
		$this->fixture->setData(array('begin_date_registration' => 0));

		self::assertFalse(
			$this->fixture->hasRegistrationBegin()
		);
	}

	/**
	 * @test
	 */
	public function hasRegistrationBeginForEventWithRegistrationBeginReturnsTrue() {
		$this->fixture->setData(array('begin_date_registration' => 42));

		self::assertTrue(
			$this->fixture->hasRegistrationBegin()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationBeginAsUnixTimestampForEventWithoutRegistrationBeginReturnsZero() {
		$this->fixture->setData(array('begin_date_registration' => 0));

		self::assertEquals(
			0,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}

	/**
	 * @test
	 */
	public function getRegistrationBeginAsUnixTimestampForEventWithRegistrationBeginReturnsRegistrationBeginAsUnixTimestamp() {
		$this->fixture->setData(array('begin_date_registration' => 42));

		self::assertEquals(
			42,
			$this->fixture->getRegistrationBeginAsUnixTimestamp()
		);
	}


	//////////////////////////////////////////
	// Tests concerning the publication hash
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasPublicationHashForNoPublicationHashSetReturnsFalse() {
		$this->fixture->setData(array('publication_hash' => ''));

		self::assertFalse(
			$this->fixture->hasPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function hasPublicationHashForPublicationHashSetReturnsTrue() {
		$this->fixture->setData(array('publication_hash' => 'fooo'));

		self::assertTrue(
			$this->fixture->hasPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function getPublicationHashForNoPublicationHashSetReturnsEmptyString() {
		$this->fixture->setData(array('publication_hash' => ''));

		self::assertEquals(
			'',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function getPublicationHashForPublicationHashSetReturnsPublicationHash() {
		$this->fixture->setData(array('publication_hash' => 'fooo'));

		self::assertEquals(
			'fooo',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function setPublicationHashSetsPublicationHash() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		self::assertEquals(
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

		self::assertEquals(
			'',
			$this->fixture->getPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function purgePublicationHashForPublicationHashSetInModelPurgesPublicationHash() {
		$this->fixture->setData(array('publication_hash' => 'fooo'));

		$this->fixture->purgePublicationHash();

		self::assertFalse(
			$this->fixture->hasPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function purgePublicationHashForNoPublicationHashSetInModelPurgesPublicationHash() {
		$this->fixture->setData(array('publication_hash' => ''));

		$this->fixture->purgePublicationHash();

		self::assertFalse(
			$this->fixture->hasPublicationHash()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithoutPublicationHashIsTrue() {
		$this->fixture->setPublicationHash('');

		self::assertTrue(
			$this->fixture->isPublished()
		);
	}

	/**
	 * @test
	 */
	public function isPublishedForEventWithPublicationHashIsFalse() {
		$this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

		self::assertFalse(
			$this->fixture->isPublished()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning hasOfflineRegistrations
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsFalse() {
		$this->fixture->setData(array('offline_attendees' => 0));

		self::assertFalse(
			$this->fixture->hasOfflineRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function hasOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTrue() {
		$this->fixture->setData(array('offline_attendees' => 2));

		self::assertTrue(
			$this->fixture->hasOfflineRegistrations()
		);
	}


	/////////////////////////////////////////////
	// Tests concerning getOfflineRegistrations
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsZero() {
		$this->fixture->setData(array('offline_attendees' => 0));

		self::assertEquals(
			0,
			$this->fixture->getOfflineRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function getOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTwo() {
		$this->fixture->setData(array('offline_attendees' => 2));

		self::assertEquals(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertEquals(
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

		self::assertTrue(
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

		self::assertEquals(
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

		self::assertTrue(
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
		$event->expects(self::any())->method('getQueueRegistrations')
			->will(self::returnValue($registrations));

		self::assertTrue(
			$event->hasQueueRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function hasQueueRegistrationsForNoQueueRegistrationReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getQueueRegistrations')
		);
		$event->expects(self::any())->method('getQueueRegistrations')
			->will(self::returnValue(new tx_oelib_List()));

		self::assertFalse(
			$event->hasQueueRegistrations()
		);
	}


	//////////////////////////////////////////////////////////////////////
	// Tests concerning hasUnlimitedVacancies
	//////////////////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForMaxAttendeesZeroReturnsTrue() {
		$this->fixture->setData(array('attendees_max' => 0));

		self::assertTrue(
			$this->fixture->hasUnlimitedVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasUnlimitedVacanciesForMaxAttendeesOneReturnsFalse() {
		$this->fixture->setData(array('attendees_max' => 1));

		self::assertFalse(
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
		$event->expects(self::any())->method('getRegularRegistrations')
			->will(self::returnValue(new tx_oelib_List()));

		self::assertEquals(
			0,
			$event->getRegisteredSeats()
		);
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
		$event->expects(self::any())->method('getRegularRegistrations')
			->will(self::returnValue($registrations));

		self::assertEquals(
			1,
			$event->getRegisteredSeats()
		);
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
		$event->expects(self::any())->method('getRegularRegistrations')
			->will(self::returnValue($registrations));

		self::assertEquals(
			2,
			$event->getRegisteredSeats()
		);
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
		$event->expects(self::any())->method('getQueueRegistrations')
			->will(self::returnValue($queueRegistrations));
		$event->expects(self::any())->method('getRegularRegistrations')
			->will(self::returnValue(new tx_oelib_List()));

		self::assertEquals(
			0,
			$event->getRegisteredSeats()
		);
	}

	/**
	 * @test
	 */
	public function getRegisteredSeatsCountsOfflineRegistrations() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegularRegistrations')
		);
		$event->setData(array('offline_attendees' => 2));
		$event->expects(self::any())->method('getRegularRegistrations')
			->will(self::returnValue(new tx_oelib_List()));

		self::assertEquals(
			2,
			$event->getRegisteredSeats()
		);
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
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(0));

		self::assertTrue(
			$event->hasEnoughRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function hasEnoughRegistrationsForLessSeatsThanNeededReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_min' => 2));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(1));

		self::assertFalse(
			$event->hasEnoughRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function hasEnoughRegistrationsForAsManySeatsAsNeededReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_min' => 2));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertTrue(
			$event->hasEnoughRegistrations()
		);
	}

	/**
	 * @test
	 */
	public function hasEnoughRegistrationsForMoreSeatsThanNeededReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_min' => 1));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertTrue(
			$event->hasEnoughRegistrations()
		);
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
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(1));

		self::assertEquals(
			1,
			$event->getVacancies()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesForAsManySeatsRegisteredAsMaximumReturnsZero() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertEquals(
			0,
			$event->getVacancies()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsZero() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 1));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertEquals(
			0,
			$event->getVacancies()
		);
	}

	/**
	 * @test
	 */
	public function getVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsZero() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(1));

		self::assertEquals(
			0,
			$event->getVacancies()
		);
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
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(1));

		self::assertTrue(
			$event->hasVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasVacanciesForAsManySeatsRegisteredAsMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertFalse(
			$event->hasVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 1));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertFalse(
			$event->hasVacancies()
		);
	}

	/**
	 * @test
	 */
	public function hasVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(1));

		self::assertTrue(
			$event->hasVacancies()
		);
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
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(1));

		self::assertFalse(
			$event->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForAsManySeatsAsMaximumReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 2));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertTrue(
			$event->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForMoreSeatsThanMaximumReturnsTrue() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 1));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(2));

		self::assertTrue(
			$event->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForZeroSeatsAndUnlimitedMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(0));

		self::assertFalse(
			$event->isFull()
		);
	}

	/**
	 * @test
	 */
	public function isFullForPositiveSeatsAndUnlimitedMaximumReturnsFalse() {
		$event = $this->getMock(
			'tx_seminars_Model_Event', array('getRegisteredSeats')
		);
		$event->setData(array('attendees_max' => 0));
		$event->expects(self::any())->method('getRegisteredSeats')
			->will(self::returnValue(1));

		self::assertFalse(
			$event->isFull()
		);
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

		self::assertTrue(
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

		self::assertTrue(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
			$paymentMethods,
			$this->fixture->getPaymentMethods()
		);
	}
}