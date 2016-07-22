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
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_EventTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_Event
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new Tx_Seminars_Model_Event();
    }

    /////////////////////////////////////
    // Tests regarding isSingleEvent().
    /////////////////////////////////////

    /**
     * @test
     */
    public function isSingleEventForSingleRecordReturnsTrue()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertTrue(
            $this->fixture->isSingleEvent()
        );
    }

    /**
     * @test
     */
    public function isSingleEventForTopicRecordReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->isSingleEvent()
        );
    }

    /**
     * @test
     */
    public function isSingleEventForDateRecordReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_DATE]
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
    public function isEventDateForSingleRecordReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->fixture->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForTopicRecordReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForDateRecordWithTopicReturnsTrue()
    {
        $this->fixture->setData([
            'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
            'topic' => new \Tx_Seminars_Model_Event(),
        ]);

        self::assertTrue(
            $this->fixture->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForDateRecordWithoutTopicReturnsFalse()
    {
        $this->fixture->setData([
            'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
            'topic' => null,
        ]);

        self::assertFalse(
            $this->fixture->isEventDate()
        );
    }


    /////////////////////////////////////
    // Tests regarding the record type.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getRecordTypeWithRecordTypeCompleteReturnsRecordTypeComplete()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            Tx_Seminars_Model_Event::TYPE_COMPLETE,
            $this->fixture->getRecordType()
        );
    }

    /**
     * @test
     */
    public function getRecordTypeWithRecordTypeDateReturnsRecordTypeDate()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_DATE]
        );

        self::assertEquals(
            Tx_Seminars_Model_Event::TYPE_DATE,
            $this->fixture->getRecordType()
        );
    }

    /**
     * @test
     */
    public function getRecordTypeWithRecordTypeTopicReturnsRecordTypeTopic()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            Tx_Seminars_Model_Event::TYPE_TOPIC,
            $this->fixture->getRecordType()
        );
    }

    ////////////////////////////////
    // Tests concerning the title.
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Superhero']);

        self::assertSame(
            'Superhero',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getRawTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Superhero']);

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
    public function getAccreditationNumberWithoutAccreditationNumberReturnsAnEmptyString()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function getAccreditationNumberWithAccreditationNumberReturnsAccreditationNumber()
    {
        $this->fixture->setData(['accreditation_number' => 'a1234567890']);

        self::assertEquals(
            'a1234567890',
            $this->fixture->getAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function setAccreditationNumberSetsAccreditationNumber()
    {
        $this->fixture->setAccreditationNumber('a1234567890');

        self::assertEquals(
            'a1234567890',
            $this->fixture->getAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function hasAccreditationNumberWithoutAccreditationNumberReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function hasAccreditationNumberWithAccreditationNumberReturnsTrue()
    {
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
    public function getRegistrationDeadlineAsUnixTimeStampWithoutRegistrationDeadlineReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineReturnsRegistrationDeadline()
    {
        $this->fixture->setData(['deadline_registration' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineAsUnixTimeStampWithNegativeRegistrationDeadlineThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The parameter $registrationDeadline must be >= 0.'
        );

        $this->fixture->setRegistrationDeadlineAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineAsUnixTimeStampWithZeroRegistrationDeadlineSetsRegistrationDeadline()
    {
        $this->fixture->setRegistrationDeadlineAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineSetsRegistrationDeadline()
    {
        $this->fixture->setRegistrationDeadlineAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->fixture->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationDeadlineWithoutRegistrationDeadlineReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasRegistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationDeadlineWithRegistrationDeadlineReturnsTrue()
    {
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
    public function getEarlyBirdDeadlineAsUnixTimeStampWithoutEarlyBirdDeadlineReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getEarlyBirdDeadlineAsUnixTimeStampWithPositiveEarlyBirdDeadlineReturnsEarlyBirdDeadline()
    {
        $this->fixture->setData(['deadline_early_bird' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineAsUnixTimeStampWithNegativeEarlyBirdDeadlineThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The parameter $earlyBirdDeadline must be >= 0.'
        );

        $this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineAsUnixTimeStampWithZeroEarlyBirdDeadlineSetsEarlyBirdDeadline()
    {
        $this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineWithPositiveEarlyBirdDeadlineSetsEarlyBirdDeadline()
    {
        $this->fixture->setEarlyBirdDeadlineAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->fixture->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdDeadlineWithoutEarlyBirdDeadlineReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasEarlyBirdDeadline()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdDeadlineWithEarlyBirdDeadlineReturnsTrue()
    {
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
    public function getUnregistrationDeadlineAsUnixTimeStampWithoutUnregistrationDeadlineReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineReturnsUnregistrationDeadline()
    {
        $this->fixture->setData(['deadline_unregistration' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setUnregistrationDeadlineAsUnixTimeStampWithNegativeUnregistrationDeadlineThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The parameter $unregistrationDeadline must be >= 0.'
        );

        $this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setUnregistrationDeadlineAsUnixTimeStampWithZeroUnregistrationDeadlineSetsUnregistrationDeadline()
    {
        $this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineSetsUnregistrationDeadline()
    {
        $this->fixture->setUnregistrationDeadlineAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->fixture->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineWithoutUnregistrationDeadlineReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasUnregistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineWithUnregistrationDeadlineReturnsTrue()
    {
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
    public function getExpiryAsUnixTimeStampWithoutExpiryReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getExpiryAsUnixTimeStampWithPositiveExpiryReturnsExpiry()
    {
        $this->fixture->setData(['expiry' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setExpiryAsUnixTimeStampWithNegativeExpiryThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->fixture->setExpiryAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setExpiryAsUnixTimeStampWithZeroExpirySetsExpiry()
    {
        $this->fixture->setExpiryAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->fixture->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setExpiryAsUnixTimeStampWithPositiveExpirySetsExpiry()
    {
        $this->fixture->setExpiryAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->fixture->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasExpiryWithoutExpiryReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasExpiry()
        );
    }

    /**
     * @test
     */
    public function hasExpiryWithExpiryReturnsTrue()
    {
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
    public function getDetailsPageWithoutDetailsPageReturnsEmptyString()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function getDetailsPageWithDetailsPageReturnsDetailsPage()
    {
        $this->fixture->setData(['details_page' => 'http://example.com']);

        self::assertEquals(
            'http://example.com',
            $this->fixture->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function setDetailsPageSetsDetailsPage()
    {
        $this->fixture->setDetailsPage('http://example.com');

        self::assertEquals(
            'http://example.com',
            $this->fixture->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasDetailsPageWithoutDetailsPageReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasDetailsPageWithDetailsPageReturnsTrue()
    {
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
    public function getCombinedSingleViewPageInitiallyReturnsEmptyString()
    {
        $this->fixture->setData(['categories' => new Tx_Oelib_List()]);

        self::assertEquals(
            '',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableDetailsPageUidReturnsTheDetailsPageUid()
    {
        $this->fixture->setData([
            'details_page' => '5', 'categories' => new Tx_Oelib_List(),
        ]);

        self::assertEquals(
            '5',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableDetailsPageUrlReturnsTheDetailsPageUrl()
    {
        $this->fixture->setData([
            'details_page' => 'www.example.com', 'categories' => new Tx_Oelib_List(),
        ]);

        self::assertEquals(
            'www.example.com',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableEventTypeWithoutSingleViewPageReturnsEmptyString()
    {
        $eventType = new Tx_Seminars_Model_EventType();
        $eventType->setData([]);
        $this->fixture->setData([
            'event_type' => $eventType, 'categories' => new Tx_Oelib_List(),
        ]);

        self::assertEquals(
            '',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableEventTypeWithSingleViewPageReturnsSingleViewPageFromEventType()
    {
        $eventType = new Tx_Seminars_Model_EventType();
        $eventType->setData(['single_view_page' => 42]);
        $this->fixture->setData([
            'event_type' => $eventType, 'categories' => new Tx_Oelib_List(),
        ]);

        self::assertEquals(
            '42',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableCategoryWithoutSingleViewPageReturnsEmptyString()
    {
        $category = new Tx_Seminars_Model_Category();
        $category->setData([]);
        $categories = new Tx_Oelib_List();
        $categories->add($category);
        $this->fixture->setData(['categories' => $categories]);

        self::assertEquals(
            '',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableCategoryTypeWithSingleViewPageReturnsSingleViewPageFromCategory()
    {
        $category = new Tx_Seminars_Model_Category();
        $category->setData(['single_view_page' => 42]);
        $categories = new Tx_Oelib_List();
        $categories->add($category);
        $this->fixture->setData(['categories' => $categories]);

        self::assertEquals(
            '42',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForTwoAvailableCategoriesWithSingleViewPageReturnsSingleViewPageFromFirstCategory()
    {
        $category1 = new Tx_Seminars_Model_Category();
        $category1->setData(['single_view_page' => 42]);
        $category2 = new Tx_Seminars_Model_Category();
        $category2->setData(['single_view_page' => 12]);
        $categories = new Tx_Oelib_List();
        $categories->add($category1);
        $categories->add($category2);
        $this->fixture->setData(['categories' => $categories]);

        self::assertEquals(
            '42',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function hasCombinedSingleViewPageForEmptySingleViewPageReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getCombinedSingleViewPage']
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
    public function hasCombinedSingleViewPageForNonEmptySingleViewPageReturnsTrue()
    {
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getCombinedSingleViewPage']
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
    public function getCombinedSingleViewPageUsesDetailsPageInsteadOfEventTypeIfBothAreAvailable()
    {
        $eventType = new Tx_Seminars_Model_EventType();
        $eventType->setData(['single_view_page' => 42]);

        $this->fixture->setData([
            'details_page' => '5',
            'event_type' => $eventType,
            'categories' => new Tx_Oelib_List(),
        ]);

        self::assertEquals(
            '5',
            $this->fixture->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageUsesEventTypeInsteadOfCategoriesIfBothAreAvailable()
    {
        $eventType = new Tx_Seminars_Model_EventType();
        $eventType->setData(['single_view_page' => 42]);
        $category = new Tx_Seminars_Model_Category();
        $category->setData(['single_view_page' => 91]);
        $categories = new Tx_Oelib_List();
        $categories->add($category);

        $this->fixture->setData([
            'event_type' => $eventType,
            'categories' => $categories,
        ]);

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
    public function getLanguageWithoutLanguageReturnsNull()
    {
        $this->fixture->setData([]);

        self::assertNull(
            $this->fixture->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageWithLanguageReturnsLanguage()
    {
        $this->fixture->setData(['language' => 'DE']);

        /** @var Tx_Oelib_Mapper_Language $mapper */
        $mapper = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Language::class);
        self::assertSame(
            $mapper->findByIsoAlpha2Code('DE'),
            $this->fixture->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageSetsLanguage()
    {
        /** @var Tx_Oelib_Mapper_Language $mapper */
        $mapper = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Language::class);
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
    public function hasLanguageWithoutLanguageReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithLanguageReturnsTrue()
    {
        /** @var Tx_Oelib_Mapper_Language $mapper */
        $mapper = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Language::class);
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
    public function eventTakesPlaceReminderHasBeenSentWithUnsetEventTakesPlaceReminderSentReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->eventTakesPlaceReminderHasBeenSent()
        );
    }

    /**
     * @test
     */
    public function eventTakesPlaceReminderHasBeenSentWithSetEventTakesPlaceReminderSentReturnsTrue()
    {
        $this->fixture->setData(['event_takes_place_reminder_sent' => true]);

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
    public function cancelationDeadlineReminderHasBeenSentWithUnsetCancelationDeadlineReminderSentReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->cancelationDeadlineReminderHasBeenSent()
        );
    }

    /**
     * @test
     */
    public function cancelationDeadlineReminderHasBeenSentWithSetCancelationDeadlineReminderSentReturnsTrue()
    {
        $this->fixture->setData(['cancelation_deadline_reminder_sent' => true]);

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
    public function needsRegistrationWithUnsetNeedsRegistrationReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->needsRegistration()
        );
    }

    /**
     * @test
     */
    public function needsRegistrationWithSetNeedsRegistrationReturnsTrue()
    {
        $this->fixture->setData(['needs_registration' => true]);

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
    public function getMinimumAttendeesWithoutMinimumAttendeesReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function getMinimumAttendeesWithPositiveMinimumAttendeesReturnsMinimumAttendees()
    {
        $this->fixture->setData(['attendees_min' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMinimumAttendeesWithNegativeMinimumAttendeesThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The parameter $minimumAttendees must be >= 0.'
        );

        $this->fixture->setMinimumAttendees(-1);
    }

    /**
     * @test
     */
    public function setMinimumAttendeesWithZeroMinimumAttendeesSetsMinimumAttendees()
    {
        $this->fixture->setMinimumAttendees(0);

        self::assertEquals(
            0,
            $this->fixture->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMinimumAttendeesWithPositiveMinimumAttendeesSetsMinimumAttendees()
    {
        $this->fixture->setMinimumAttendees(42);

        self::assertEquals(
            42,
            $this->fixture->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMinimumAttendeesWithoutMinimumAttendeesReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMinimumAttendeesWithMinimumAttendeesReturnsTrue()
    {
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
    public function getMaximumAttendeesWithoutMaximumAttendeesReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function getMaximumAttendeesWithMaximumAttendeesReturnsMaximumAttendees()
    {
        $this->fixture->setData(['attendees_max' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMaximumAttendeesWithNegativeMaximumAttendeesThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The parameter $maximumAttendees must be >= 0.'
        );

        $this->fixture->setMaximumAttendees(-1);
    }

    /**
     * @test
     */
    public function setMaximumAttendeesWithZeroMaximumAttendeesSetsMaximumAttendees()
    {
        $this->fixture->setMaximumAttendees(0);

        self::assertEquals(
            0,
            $this->fixture->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMaximumAttendeesWithPositiveAttendeesSetsMaximumAttendees()
    {
        $this->fixture->setMaximumAttendees(42);

        self::assertEquals(
            42,
            $this->fixture->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMaximumAttendeesWithoutMaximumAttendeesReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMaximumAttendeesWithMaximumAttendeesReturnsTrue()
    {
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
    public function hasRegistrationQueueWithoutRegistrationQueueReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationQueueWithRegistrationQueueReturnsTrue()
    {
        $this->fixture->setData(['queue_size' => true]);

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
    public function shouldSkipCollectionCheckWithoutSkipCollsionCheckReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->shouldSkipCollisionCheck()
        );
    }

    /**
     * @test
     */
    public function shouldSkipCollectionCheckWithSkipCollisionCheckReturnsTrue()
    {
        $this->fixture->setData(['skip_collision_check' => true]);

        self::assertTrue(
            $this->fixture->shouldSkipCollisionCheck()
        );
    }

    /*
     * Tests regarding the status.
     */

    /**
     * @test
     */
    public function getStatusWithoutStatusReturnsStatusPlanned()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            Tx_Seminars_Model_Event::STATUS_PLANNED,
            $this->fixture->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusPlannedReturnsStatusPlanned()
    {
        $this->fixture->setData(
            ['cancelled' => Tx_Seminars_Model_Event::STATUS_PLANNED]
        );

        self::assertEquals(
            Tx_Seminars_Model_Event::STATUS_PLANNED,
            $this->fixture->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusCanceledReturnStatusCanceled()
    {
        $this->fixture->setData(
            ['cancelled' => Tx_Seminars_Model_Event::STATUS_CANCELED]
        );

        self::assertEquals(
            Tx_Seminars_Model_Event::STATUS_CANCELED,
            $this->fixture->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusConfirmedReturnsStatusConfirmed()
    {
        $this->fixture->setData(
            ['cancelled' => Tx_Seminars_Model_Event::STATUS_CONFIRMED]
        );

        self::assertEquals(
            Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            $this->fixture->getStatus()
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function setStatusWithInvalidStatusThrowsException()
    {
        $this->fixture->setStatus(-1);
    }

    /**
     * @test
     */
    public function setStatusWithStatusPlannedSetsStatus()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertEquals(
            Tx_Seminars_Model_Event::STATUS_PLANNED,
            $this->fixture->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithStatusCanceledSetsStatus()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertEquals(
            Tx_Seminars_Model_Event::STATUS_CANCELED,
            $this->fixture->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithStatusConfirmedSetsStatus()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertEquals(
            Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            $this->fixture->getStatus()
        );
    }

    /**
     * @test
     */
    public function isPlannedForPlannedStatusReturnsTrue()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertTrue($this->fixture->isPlanned());
    }

    /**
     * @test
     */
    public function isPlannedForCanceledStatusReturnsFalse()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse($this->fixture->isPlanned());
    }

    /**
     * @test
     */
    public function isPlannedForConfirmedStatusReturnsFalse()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse($this->fixture->isPlanned());
    }

    /**
     * @test
     */
    public function isCanceledForPlannedStatusReturnsFalse()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse($this->fixture->isCanceled());
    }

    /**
     * @test
     */
    public function isCanceledForCanceledStatusReturnsTrue()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertTrue($this->fixture->isCanceled());
    }

    /**
     * @test
     */
    public function isCanceledForConfirmedStatusReturnsFalse()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse($this->fixture->isCanceled());
    }

    /**
     * @test
     */
    public function isConfirmedForPlannedStatusReturnsFalse()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse($this->fixture->isConfirmed());
    }

    /**
     * @test
     */
    public function isConfirmedForCanceledStatusReturnsFalse()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse($this->fixture->isConfirmed());
    }

    /**
     * @test
     */
    public function isConfirmedForConfirmedStatusReturnsTrue()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertTrue($this->fixture->isConfirmed());
    }

    /**
     * @test
     */
    public function cancelCanMakePlannedEventCanceled()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->fixture->cancel();

        self::assertTrue($this->fixture->isCanceled());
    }

    /**
     * @test
     */
    public function cancelCanMakeConfirmedEventCanceled()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        $this->fixture->cancel();

        self::assertTrue($this->fixture->isCanceled());
    }

    /**
     * @test
     */
    public function cancelForCanceledEventNotThrowsException()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        $this->fixture->cancel();
    }

    /**
     * @test
     */
    public function confirmCanMakePlannedEventConfirmed()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->fixture->confirm();

        self::assertTrue($this->fixture->isConfirmed());
    }

    /**
     * @test
     */
    public function confirmCanMakeCanceledEventConfirmed()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CANCELED);

        $this->fixture->confirm();

        self::assertTrue($this->fixture->isConfirmed());
    }

    /**
     * @test
     */
    public function confirmForConfirmedEventNotThrowsException()
    {
        $this->fixture->setStatus(Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        $this->fixture->confirm();
    }

    ////////////////////////////////////////
    // Tests regarding the attached files.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getAttachedFilesWithoutAttachedFilesReturnsEmptyArray()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            [],
            $this->fixture->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function getAttachedFilesWithOneAttachedFileReturnsArrayWithAttachedFile()
    {
        $this->fixture->setData(['attached_files' => 'file.txt']);

        self::assertEquals(
            ['file.txt'],
            $this->fixture->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function getAttachedFilesWithTwoAttachedFilesReturnsArrayWithBothAttachedFiles()
    {
        $this->fixture->setData(['attached_files' => 'file.txt,file2.txt']);

        self::assertEquals(
            ['file.txt', 'file2.txt'],
            $this->fixture->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function setAttachedFilesSetsAttachedFiles()
    {
        $this->fixture->setAttachedFiles(['file.txt']);

        self::assertEquals(
            ['file.txt'],
            $this->fixture->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithoutAttachedFilesReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithAttachedFileReturnsTrue()
    {
        $this->fixture->setAttachedFiles(['file.txt']);

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
    public function hasRegistrationBeginForNoRegistrationBeginReturnsFalse()
    {
        $this->fixture->setData(['begin_date_registration' => 0]);

        self::assertFalse(
            $this->fixture->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationBeginForEventWithRegistrationBeginReturnsTrue()
    {
        $this->fixture->setData(['begin_date_registration' => 42]);

        self::assertTrue(
            $this->fixture->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithoutRegistrationBeginReturnsZero()
    {
        $this->fixture->setData(['begin_date_registration' => 0]);

        self::assertEquals(
            0,
            $this->fixture->getRegistrationBeginAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithRegistrationBeginReturnsRegistrationBeginAsUnixTimestamp()
    {
        $this->fixture->setData(['begin_date_registration' => 42]);

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
    public function hasPublicationHashForNoPublicationHashSetReturnsFalse()
    {
        $this->fixture->setData(['publication_hash' => '']);

        self::assertFalse(
            $this->fixture->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function hasPublicationHashForPublicationHashSetReturnsTrue()
    {
        $this->fixture->setData(['publication_hash' => 'fooo']);

        self::assertTrue(
            $this->fixture->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function getPublicationHashForNoPublicationHashSetReturnsEmptyString()
    {
        $this->fixture->setData(['publication_hash' => '']);

        self::assertEquals(
            '',
            $this->fixture->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function getPublicationHashForPublicationHashSetReturnsPublicationHash()
    {
        $this->fixture->setData(['publication_hash' => 'fooo']);

        self::assertEquals(
            'fooo',
            $this->fixture->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function setPublicationHashSetsPublicationHash()
    {
        $this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertEquals(
            '5318761asdf35as5sad35asd35asd',
            $this->fixture->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function setPublicationHashWithEmptyStringOverridesNonEmptyData()
    {
        $this->fixture->setData(['publication_hash' => 'fooo']);

        $this->fixture->setPublicationHash('');

        self::assertEquals(
            '',
            $this->fixture->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function purgePublicationHashForPublicationHashSetInModelPurgesPublicationHash()
    {
        $this->fixture->setData(['publication_hash' => 'fooo']);

        $this->fixture->purgePublicationHash();

        self::assertFalse(
            $this->fixture->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function purgePublicationHashForNoPublicationHashSetInModelPurgesPublicationHash()
    {
        $this->fixture->setData(['publication_hash' => '']);

        $this->fixture->purgePublicationHash();

        self::assertFalse(
            $this->fixture->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithoutPublicationHashIsTrue()
    {
        $this->fixture->setPublicationHash('');

        self::assertTrue(
            $this->fixture->isPublished()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithPublicationHashIsFalse()
    {
        $this->fixture->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertFalse(
            $this->fixture->isPublished()
        );
    }

    /*
     * Tests concerning the offline registrations
     */

    /**
     * @test
     */
    public function hasOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsFalse()
    {
        $this->fixture->setData(['offline_attendees' => 0]);

        self::assertFalse(
            $this->fixture->hasOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTrue()
    {
        $this->fixture->setData(['offline_attendees' => 2]);

        self::assertTrue(
            $this->fixture->hasOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsZero()
    {
        $this->fixture->setData(['offline_attendees' => 0]);

        self::assertEquals(
            0,
            $this->fixture->getOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTwo()
    {
        $this->fixture->setData(['offline_attendees' => 2]);

        self::assertEquals(
            2,
            $this->fixture->getOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function setOfflineRegistrationsSetsOfflineRegistrations()
    {
        $numberOfOfflineRegistrations = 2;
        $this->fixture->setData(['offline_attendees' => 0]);

        $this->fixture->setOfflineRegistrations($numberOfOfflineRegistrations);

        self::assertSame(
            $numberOfOfflineRegistrations,
            $this->fixture->getOfflineRegistrations()
        );
    }

    ///////////////////////////////////////
    // Tests concerning the registrations
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getRegistrationsReturnsRegistrations()
    {
        $registrations = new Tx_Oelib_List();

        $this->fixture->setData(['registrations' => $registrations]);

        self::assertSame(
            $registrations,
            $this->fixture->getRegistrations()
        );
    }

    /**
     * @test
     */
    public function setRegistrationsSetsRegistrations()
    {
        $registrations = new Tx_Oelib_List();

        $this->fixture->setRegistrations($registrations);

        self::assertSame(
            $registrations,
            $this->fixture->getRegistrations()
        );
    }

    /**
     * @test
     */
    public function getRegularRegistrationsReturnsRegularRegistrations()
    {
        $registrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 0]
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
    public function getRegularRegistrationsNotReturnsQueueRegistrations()
    {
        $registrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 1]
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
    public function getQueueRegistrationsReturnsQueueRegistrations()
    {
        $registrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 1]
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
    public function getQueueRegistrationsNotReturnsRegularRegistrations()
    {
        $registrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 0]
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
    public function hasQueueRegistrationsForOneQueueRegistrationReturnsTrue()
    {
        $registrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 1]
            );
        $registrations->add($registration);
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getQueueRegistrations']
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
    public function hasQueueRegistrationsForNoQueueRegistrationReturnsFalse()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getQueueRegistrations']
        );
        $event->expects(self::any())->method('getQueueRegistrations')
            ->will(self::returnValue(new Tx_Oelib_List()));

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
    public function hasUnlimitedVacanciesForMaxAttendeesZeroReturnsTrue()
    {
        $this->fixture->setData(['attendees_max' => 0]);

        self::assertTrue(
            $this->fixture->hasUnlimitedVacancies()
        );
    }

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForMaxAttendeesOneReturnsFalse()
    {
        $this->fixture->setData(['attendees_max' => 1]);

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
    public function getRegisteredSeatsForNoRegularRegistrationsReturnsZero()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegularRegistrations']
        );
        $event->setData([]);
        $event->expects(self::any())->method('getRegularRegistrations')
            ->will(self::returnValue(new Tx_Oelib_List()));

        self::assertEquals(
            0,
            $event->getRegisteredSeats()
        );
    }

    /**
     * @test
     */
    public function getRegisteredSeatsCountsSingleSeatRegularRegistrations()
    {
        $registrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['seats' => 1]
            );
        $registrations->add($registration);
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegularRegistrations']
        );
        $event->setData([]);
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
    public function getRegisteredSeatsCountsMultiSeatRegularRegistrations()
    {
        $registrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['seats' => 2]
            );
        $registrations->add($registration);
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegularRegistrations']
        );
        $event->setData([]);
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
    public function getRegisteredSeatsNotCountsQueueRegistrations()
    {
        $queueRegistrations = new Tx_Oelib_List();
        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['seats' => 1]
            );
        $queueRegistrations->add($registration);
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['getRegularRegistrations', 'getQueueRegistrations']
        );
        $event->setData([]);
        $event->expects(self::any())->method('getQueueRegistrations')
            ->will(self::returnValue($queueRegistrations));
        $event->expects(self::any())->method('getRegularRegistrations')
            ->will(self::returnValue(new Tx_Oelib_List()));

        self::assertEquals(
            0,
            $event->getRegisteredSeats()
        );
    }

    /**
     * @test
     */
    public function getRegisteredSeatsCountsOfflineRegistrations()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegularRegistrations']
        );
        $event->setData(['offline_attendees' => 2]);
        $event->expects(self::any())->method('getRegularRegistrations')
            ->will(self::returnValue(new Tx_Oelib_List()));

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
    public function hasEnoughRegistrationsForZeroSeatsAndZeroNeededReturnsTrue()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 0]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(0));

        self::assertTrue(
            $event->hasEnoughRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForLessSeatsThanNeededReturnsFalse()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 2]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(1));

        self::assertFalse(
            $event->hasEnoughRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForAsManySeatsAsNeededReturnsTrue()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 2]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(2));

        self::assertTrue(
            $event->hasEnoughRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForMoreSeatsThanNeededReturnsTrue()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 1]);
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
    public function getVacanciesForOneRegisteredAndTwoMaximumReturnsOne()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
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
    public function getVacanciesForAsManySeatsRegisteredAsMaximumReturnsZero()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
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
    public function getVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsZero()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 1]);
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
    public function getVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsZero()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
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
    public function hasVacanciesForOneRegisteredAndTwoMaximumReturnsTrue()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(1));

        self::assertTrue(
            $event->hasVacancies()
        );
    }

    /**
     * @test
     */
    public function hasVacanciesForAsManySeatsRegisteredAsMaximumReturnsFalse()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(2));

        self::assertFalse(
            $event->hasVacancies()
        );
    }

    /**
     * @test
     */
    public function hasVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsFalse()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 1]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(2));

        self::assertFalse(
            $event->hasVacancies()
        );
    }

    /**
     * @test
     */
    public function hasVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsTrue()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
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
    public function isFullForLessSeatsThanMaximumReturnsFalse()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(1));

        self::assertFalse(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForAsManySeatsAsMaximumReturnsTrue()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(2));

        self::assertTrue(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForMoreSeatsThanMaximumReturnsTrue()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 1]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(2));

        self::assertTrue(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForZeroSeatsAndUnlimitedMaximumReturnsFalse()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
        $event->expects(self::any())->method('getRegisteredSeats')
            ->will(self::returnValue(0));

        self::assertFalse(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForPositiveSeatsAndUnlimitedMaximumReturnsFalse()
    {
        $event = $this->getMock(
            Tx_Seminars_Model_Event::class, ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
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
    public function attachRegistrationAddsRegistration()
    {
        $this->fixture->setRegistrations(new Tx_Oelib_List());

        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)
            ->getLoadedTestingModel([]);
        $this->fixture->attachRegistration($registration);

        self::assertTrue(
            $this->fixture->getRegistrations()->hasUid($registration->getUid())
        );
    }

    /**
     * @test
     */
    public function attachRegistrationNotRemovesExistingRegistration()
    {
        $registrations = new Tx_Oelib_List();
        $oldRegistration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)->getNewGhost();
        $registrations->add($oldRegistration);
        $this->fixture->setRegistrations($registrations);

        $newRegistration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)
            ->getLoadedTestingModel([]);
        $this->fixture->attachRegistration($newRegistration);

        self::assertTrue(
            $this->fixture->getRegistrations()->hasUid($oldRegistration->getUid())
        );
    }

    /**
     * @test
     */
    public function attachRegistrationSetsEventForRegistration()
    {
        $this->fixture->setRegistrations(new Tx_Oelib_List());

        $registration = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Registration::class)
            ->getLoadedTestingModel([]);
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
    public function getPaymentMethodsReturnsPaymentMethods()
    {
        $paymentMethods = new Tx_Oelib_List();
        $this->fixture->setData(
            ['payment_methods' => $paymentMethods]
        );

        self::assertSame(
            $paymentMethods,
            $this->fixture->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodsSetsPaymentMethods()
    {
        $this->fixture->setData([]);

        $paymentMethods = new Tx_Oelib_List();
        $this->fixture->setPaymentMethods($paymentMethods);

        self::assertSame(
            $paymentMethods,
            $this->fixture->getPaymentMethods()
        );
    }

    /*
     * Tests regarding the flag for organizers having been notified about enough attendees.
     */

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesByDefaultReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesReturnsFalseValueFromDatabase()
    {
        $this->fixture->setData(['organizers_notified_about_minimum_reached' => 1]);

        self::assertTrue(
            $this->fixture->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function setOrganizersBeenNotifiedAboutEnoughAttendeesMarksItAsTrue()
    {
        $this->fixture->setData([]);

        $this->fixture->setOrganizersBeenNotifiedAboutEnoughAttendees();

        self::assertTrue(
            $this->fixture->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /*
     * Tests regarding the flag for organizers having been notified about enough attendees.
     */

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsByDefaultReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsReturnsTrueValueFromDatabase()
    {
        $this->fixture->setData(
            ['mute_notification_emails' => 1]
        );

        self::assertTrue(
            $this->fixture->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function muteNotificationEmailsSetsShouldMute()
    {
        $this->fixture->muteNotificationEmails();

        self::assertTrue(
            $this->fixture->shouldMuteNotificationEmails()
        );
    }

    /*
     * Tests regarding the flag for automatic cancelation/confirmation
     */

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelByDefaultReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->shouldAutomaticallyConfirmOrCancel()
        );
    }

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelReturnsTrueValueFromDatabase()
    {
        $this->fixture->setData(
            ['automatic_confirmation_cancelation' => 1]
        );

        self::assertTrue(
            $this->fixture->shouldAutomaticallyConfirmOrCancel()
        );
    }

    /*
     * Tests concerning the organizers
     */

    /**
     * @test
     */
    public function getOrganizersGetsOrganizers()
    {
        $organizers = new \Tx_Oelib_List();
        $this->fixture->setData(['organizers' => $organizers]);

        $result = $this->fixture->getOrganizers();

        self::assertSame($organizers, $result);
    }

    /**
     * @test
     */
    public function getFirstOrganizerForNoOrganizersReturnsNull()
    {
        $organizers = new \Tx_Oelib_List();
        $this->fixture->setData(['organizers' => $organizers]);

        $result = $this->fixture->getFirstOrganizer();

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function getFirstOrganizerForOneOrganizerReturnsThatOrganizer()
    {
        $organizer = new \Tx_Seminars_Model_Organizer();
        $organizers = new \Tx_Oelib_List();
        $organizers->add($organizer);
        $this->fixture->setData(['organizers' => $organizers]);

        $result = $this->fixture->getFirstOrganizer();

        self::assertSame($organizer, $result);
    }

    /**
     * @test
     */
    public function getFirstOrganizerForTwoOrganizersReturnsFirstOrganizer()
    {
        $firstOrganizer = new \Tx_Seminars_Model_Organizer();
        $organizers = new \Tx_Oelib_List();
        $organizers->add($firstOrganizer);
        $organizers->add(new \Tx_Seminars_Model_Organizer());
        $this->fixture->setData(['organizers' => $organizers]);

        $result = $this->fixture->getFirstOrganizer();

        self::assertSame($firstOrganizer, $result);
    }
}
