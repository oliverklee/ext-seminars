<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EventTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Event
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * @var int
     */
    protected $now = 1424751343;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = $this->now;
        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $this->configuration = new \Tx_Oelib_Configuration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);

        $this->subject = new \Tx_Seminars_Model_Event();
    }

    protected function tearDown()
    {
        \Tx_Oelib_ConfigurationRegistry::purgeInstance();
    }

    /////////////////////////////////////
    // Tests regarding isSingleEvent().
    /////////////////////////////////////

    /**
     * @test
     */
    public function isSingleEventForSingleRecordReturnsTrue()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertTrue(
            $this->subject->isSingleEvent()
        );
    }

    /**
     * @test
     */
    public function isSingleEventForTopicRecordReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->subject->isSingleEvent()
        );
    }

    /**
     * @test
     */
    public function isSingleEventForDateRecordReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]
        );

        self::assertFalse(
            $this->subject->isSingleEvent()
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
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForTopicRecordReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->subject->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForDateRecordWithTopicReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => new \Tx_Seminars_Model_Event(),
            ]
        );

        self::assertTrue(
            $this->subject->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForDateRecordWithoutTopicReturnsFalse()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => null,
            ]
        );

        self::assertFalse(
            $this->subject->isEventDate()
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
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            $this->subject->getRecordType()
        );
    }

    /**
     * @test
     */
    public function getRecordTypeWithRecordTypeDateReturnsRecordTypeDate()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Event::TYPE_DATE,
            $this->subject->getRecordType()
        );
    }

    /**
     * @test
     */
    public function getRecordTypeWithRecordTypeTopicReturnsRecordTypeTopic()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Event::TYPE_TOPIC,
            $this->subject->getRecordType()
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
        $this->subject->setData(['title' => 'Superhero']);

        self::assertSame(
            'Superhero',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getRawTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'Superhero']);

        self::assertSame(
            'Superhero',
            $this->subject->getRawTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('Superhero');

        self::assertSame('Superhero', $this->subject->getTitle());
    }

    /*
     * Tests regarding the time zone.
     */

    /**
     * @test
     */
    public function getTimeZoneInitiallyReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertSame('', $this->subject->getTimeZone());
    }

    /**
     * @test
     */
    public function getTimeZoneReturnsTimeZone()
    {
        $value = 'Europe/Berlin';
        $this->subject->setData(['time_zone' => $value]);

        self::assertSame($value, $this->subject->getTimeZone());
    }

    /**
     * @test
     */
    public function setTimeZoneSetsTimeZone()
    {
        $this->subject->setData([]);

        $value = 'Europe/Berlin';
        $this->subject->setTimeZone($value);

        self::assertSame($value, $this->subject->getTimeZone());
    }

    //////////////////////////////////////////////
    // Tests regarding the accreditation number.
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function getAccreditationNumberWithoutAccreditationNumberReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function getAccreditationNumberWithAccreditationNumberReturnsAccreditationNumber()
    {
        $this->subject->setData(['accreditation_number' => 'a1234567890']);

        self::assertEquals(
            'a1234567890',
            $this->subject->getAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function setAccreditationNumberSetsAccreditationNumber()
    {
        $this->subject->setAccreditationNumber('a1234567890');

        self::assertEquals(
            'a1234567890',
            $this->subject->getAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function hasAccreditationNumberWithoutAccreditationNumberReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function hasAccreditationNumberWithAccreditationNumberReturnsTrue()
    {
        $this->subject->setAccreditationNumber('a1234567890');

        self::assertTrue(
            $this->subject->hasAccreditationNumber()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineReturnsRegistrationDeadline()
    {
        $this->subject->setData(['deadline_registration' => 42]);

        self::assertEquals(
            42,
            $this->subject->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineAsUnixTimeStampWithNegativeRegistrationDeadlineThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $registrationDeadline must be >= 0.'
        );

        $this->subject->setRegistrationDeadlineAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineAsUnixTimeStampWithZeroRegistrationDeadlineSetsRegistrationDeadline()
    {
        $this->subject->setRegistrationDeadlineAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->subject->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineSetsRegistrationDeadline()
    {
        $this->subject->setRegistrationDeadlineAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->subject->getRegistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationDeadlineWithoutRegistrationDeadlineReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasRegistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationDeadlineWithRegistrationDeadlineReturnsTrue()
    {
        $this->subject->setRegistrationDeadlineAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasRegistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithoutAnyDatesReturnsZero()
    {
        $this->subject->setData(
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        self::assertSame(0, $this->subject->getLatestPossibleRegistrationTimeAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithBeginDateReturnsBeginDate()
    {
        $beginDate = 1524751343;
        $endDate = $beginDate + 100000;
        $this->subject->setData(
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => $beginDate,
                'end_date' => $endDate,
            ]
        );

        self::assertSame($beginDate, $this->subject->getLatestPossibleRegistrationTimeAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithRegistrationDeadlineReturnsRegistrationDeadline()
    {
        $beginDate = 1524751343;
        $endDate = $beginDate + 100000;
        $registrationDeadline = 1524741343;
        $this->subject->setData(
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => $registrationDeadline,
                'begin_date' => $beginDate,
                'end_date' => $endDate,
            ]
        );

        self::assertSame($registrationDeadline, $this->subject->getLatestPossibleRegistrationTimeAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithBeginAndEndAndLateRegistrationReturnsEndDate()
    {
        $this->configuration->setAsBoolean('allowRegistrationForStartedEvents', true);

        $beginDate = 1524751343;
        $endDate = $beginDate + 100000;
        $this->subject->setData(
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => $beginDate,
                'end_date' => $endDate,
            ]
        );

        self::assertSame($endDate, $this->subject->getLatestPossibleRegistrationTimeAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithDeadlineAndLateRegistrationReturnsDeadline()
    {
        $this->configuration->setAsBoolean('allowRegistrationForStartedEvents', true);
        $beginDate = 1524751343;
        $endDate = $beginDate + 100000;
        $registrationDeadline = 1524741343;
        $this->subject->setData(
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => $registrationDeadline,
                'begin_date' => $beginDate,
                'end_date' => $endDate,
            ]
        );

        self::assertSame($registrationDeadline, $this->subject->getLatestPossibleRegistrationTimeAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithBeginAndWithoutEndLateAllowedReturnsBeginDate()
    {
        $this->configuration->setAsBoolean('allowRegistrationForStartedEvents', true);
        $beginDate = $this->now - 100;
        $this->subject->setData(
            [
                'title' => 'a test event',
                'needs_registration' => 1,
                'deadline_registration' => 0,
                'begin_date' => $beginDate,
                'end_date' => 0,
            ]
        );

        self::assertSame($beginDate, $this->subject->getLatestPossibleRegistrationTimeAsUnixTimeStamp());
    }

    /////////////////////////////////////////////
    // Tests regarding the early bird deadline.
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getEarlyBirdDeadlineAsUnixTimeStampWithoutEarlyBirdDeadlineReturnsZero()
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getEarlyBirdDeadlineAsUnixTimeStampWithPositiveEarlyBirdDeadlineReturnsEarlyBirdDeadline()
    {
        $this->subject->setData(['deadline_early_bird' => 42]);

        self::assertEquals(
            42,
            $this->subject->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineAsUnixTimeStampWithNegativeEarlyBirdDeadlineThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $earlyBirdDeadline must be >= 0.'
        );

        $this->subject->setEarlyBirdDeadlineAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineAsUnixTimeStampWithZeroEarlyBirdDeadlineSetsEarlyBirdDeadline()
    {
        $this->subject->setEarlyBirdDeadlineAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->subject->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEarlyBirdDeadlineWithPositiveEarlyBirdDeadlineSetsEarlyBirdDeadline()
    {
        $this->subject->setEarlyBirdDeadlineAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->subject->getEarlyBirdDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdDeadlineWithoutEarlyBirdDeadlineReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEarlyBirdDeadline()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdDeadlineWithEarlyBirdDeadlineReturnsTrue()
    {
        $this->subject->setEarlyBirdDeadlineAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasEarlyBirdDeadline()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineReturnsUnregistrationDeadline()
    {
        $this->subject->setData(['deadline_unregistration' => 42]);

        self::assertEquals(
            42,
            $this->subject->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setUnregistrationDeadlineAsUnixTimeStampWithNegativeUnregistrationDeadlineThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $unregistrationDeadline must be >= 0.'
        );

        $this->subject->setUnregistrationDeadlineAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setUnregistrationDeadlineAsUnixTimeStampWithZeroUnregistrationDeadlineSetsUnregistrationDeadline()
    {
        $this->subject->setUnregistrationDeadlineAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->subject->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineSetsUnregistrationDeadline()
    {
        $this->subject->setUnregistrationDeadlineAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->subject->getUnregistrationDeadlineAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineWithoutUnregistrationDeadlineReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasUnregistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineWithUnregistrationDeadlineReturnsTrue()
    {
        $this->subject->setUnregistrationDeadlineAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasUnregistrationDeadline()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getExpiryAsUnixTimeStampWithPositiveExpiryReturnsExpiry()
    {
        $this->subject->setData(['expiry' => 42]);

        self::assertEquals(
            42,
            $this->subject->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setExpiryAsUnixTimeStampWithNegativeExpiryThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setExpiryAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setExpiryAsUnixTimeStampWithZeroExpirySetsExpiry()
    {
        $this->subject->setExpiryAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->subject->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setExpiryAsUnixTimeStampWithPositiveExpirySetsExpiry()
    {
        $this->subject->setExpiryAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->subject->getExpiryAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasExpiryWithoutExpiryReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasExpiry()
        );
    }

    /**
     * @test
     */
    public function hasExpiryWithExpiryReturnsTrue()
    {
        $this->subject->setExpiryAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasExpiry()
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
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function getDetailsPageWithDetailsPageReturnsDetailsPage()
    {
        $this->subject->setData(['details_page' => 'http://example.com']);

        self::assertEquals(
            'http://example.com',
            $this->subject->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function setDetailsPageSetsDetailsPage()
    {
        $this->subject->setDetailsPage('http://example.com');

        self::assertEquals(
            'http://example.com',
            $this->subject->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasDetailsPageWithoutDetailsPageReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasDetailsPageWithDetailsPageReturnsTrue()
    {
        $this->subject->setDetailsPage('http://example.com');

        self::assertTrue(
            $this->subject->hasDetailsPage()
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
        $this->subject->setData(['categories' => new \Tx_Oelib_List()]);

        self::assertEquals(
            '',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableDetailsPageUidReturnsTheDetailsPageUid()
    {
        $this->subject->setData(
            [
                'details_page' => '5',
                'categories' => new \Tx_Oelib_List(),
            ]
        );

        self::assertEquals(
            '5',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableDetailsPageUrlReturnsTheDetailsPageUrl()
    {
        $this->subject->setData(
            [
                'details_page' => 'www.example.com',
                'categories' => new \Tx_Oelib_List(),
            ]
        );

        self::assertEquals(
            'www.example.com',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableEventTypeWithoutSingleViewPageReturnsEmptyString()
    {
        $eventType = new \Tx_Seminars_Model_EventType();
        $eventType->setData([]);
        $this->subject->setData(
            [
                'event_type' => $eventType,
                'categories' => new \Tx_Oelib_List(),
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableEventTypeWithSingleViewPageReturnsSingleViewPageFromEventType()
    {
        $eventType = new \Tx_Seminars_Model_EventType();
        $eventType->setData(['single_view_page' => 42]);
        $this->subject->setData(
            [
                'event_type' => $eventType,
                'categories' => new \Tx_Oelib_List(),
            ]
        );

        self::assertEquals(
            '42',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableCategoryWithoutSingleViewPageReturnsEmptyString()
    {
        $category = new \Tx_Seminars_Model_Category();
        $category->setData([]);
        $categories = new \Tx_Oelib_List();
        $categories->add($category);
        $this->subject->setData(['categories' => $categories]);

        self::assertEquals(
            '',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableCategoryTypeWithSingleViewPageReturnsSingleViewPageFromCategory()
    {
        $category = new \Tx_Seminars_Model_Category();
        $category->setData(['single_view_page' => 42]);
        $categories = new \Tx_Oelib_List();
        $categories->add($category);
        $this->subject->setData(['categories' => $categories]);

        self::assertEquals(
            '42',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForTwoAvailableCategoriesWithSingleViewPageReturnsSingleViewPageFromFirstCategory()
    {
        $category1 = new \Tx_Seminars_Model_Category();
        $category1->setData(['single_view_page' => 42]);
        $category2 = new \Tx_Seminars_Model_Category();
        $category2->setData(['single_view_page' => 12]);
        $categories = new \Tx_Oelib_List();
        $categories->add($category1);
        $categories->add($category2);
        $this->subject->setData(['categories' => $categories]);

        self::assertEquals(
            '42',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function hasCombinedSingleViewPageForEmptySingleViewPageReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getCombinedSingleViewPage']
        );
        $subject->expects(self::atLeastOnce())
            ->method('getCombinedSingleViewPage')->willReturn('');

        self::assertFalse(
            $subject->hasCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function hasCombinedSingleViewPageForNonEmptySingleViewPageReturnsTrue()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getCombinedSingleViewPage']
        );
        $subject->expects(self::atLeastOnce())
            ->method('getCombinedSingleViewPage')->willReturn(42);

        self::assertTrue(
            $subject->hasCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageUsesDetailsPageInsteadOfEventTypeIfBothAreAvailable()
    {
        $eventType = new \Tx_Seminars_Model_EventType();
        $eventType->setData(['single_view_page' => 42]);

        $this->subject->setData(
            [
                'details_page' => '5',
                'event_type' => $eventType,
                'categories' => new \Tx_Oelib_List(),
            ]
        );

        self::assertEquals(
            '5',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageUsesEventTypeInsteadOfCategoriesIfBothAreAvailable()
    {
        $eventType = new \Tx_Seminars_Model_EventType();
        $eventType->setData(['single_view_page' => 42]);
        $category = new \Tx_Seminars_Model_Category();
        $category->setData(['single_view_page' => 91]);
        $categories = new \Tx_Oelib_List();
        $categories->add($category);

        $this->subject->setData(
            [
                'event_type' => $eventType,
                'categories' => $categories,
            ]
        );

        self::assertEquals(
            '42',
            $this->subject->getCombinedSingleViewPage()
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
        $this->subject->setData([]);

        self::assertNull(
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageWithLanguageReturnsLanguage()
    {
        $this->subject->setData(['language' => 'DE']);

        /** @var \Tx_Oelib_Mapper_Language $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Language::class);
        self::assertSame(
            $mapper->findByIsoAlpha2Code('DE'),
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageSetsLanguage()
    {
        /** @var \Tx_Oelib_Mapper_Language $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Language::class);
        $language = $mapper->findByIsoAlpha2Code('DE');
        $this->subject->setLanguage($language);

        self::assertSame(
            $language,
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithoutLanguageReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithLanguageReturnsTrue()
    {
        /** @var \Tx_Oelib_Mapper_Language $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Language::class);
        $language = $mapper->findByIsoAlpha2Code('DE');
        $this->subject->setLanguage($language);

        self::assertTrue(
            $this->subject->hasLanguage()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->eventTakesPlaceReminderHasBeenSent()
        );
    }

    /**
     * @test
     */
    public function eventTakesPlaceReminderHasBeenSentWithSetEventTakesPlaceReminderSentReturnsTrue()
    {
        $this->subject->setData(['event_takes_place_reminder_sent' => true]);

        self::assertTrue(
            $this->subject->eventTakesPlaceReminderHasBeenSent()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->cancelationDeadlineReminderHasBeenSent()
        );
    }

    /**
     * @test
     */
    public function cancelationDeadlineReminderHasBeenSentWithSetCancelationDeadlineReminderSentReturnsTrue()
    {
        $this->subject->setData(['cancelation_deadline_reminder_sent' => true]);

        self::assertTrue(
            $this->subject->cancelationDeadlineReminderHasBeenSent()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->needsRegistration()
        );
    }

    /**
     * @test
     */
    public function needsRegistrationWithSetNeedsRegistrationReturnsTrue()
    {
        $this->subject->setData(['needs_registration' => true]);

        self::assertTrue(
            $this->subject->needsRegistration()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function getMinimumAttendeesWithPositiveMinimumAttendeesReturnsMinimumAttendees()
    {
        $this->subject->setData(['attendees_min' => 42]);

        self::assertEquals(
            42,
            $this->subject->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMinimumAttendeesWithNegativeMinimumAttendeesThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $minimumAttendees must be >= 0.'
        );

        $this->subject->setMinimumAttendees(-1);
    }

    /**
     * @test
     */
    public function setMinimumAttendeesWithZeroMinimumAttendeesSetsMinimumAttendees()
    {
        $this->subject->setMinimumAttendees(0);

        self::assertEquals(
            0,
            $this->subject->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMinimumAttendeesWithPositiveMinimumAttendeesSetsMinimumAttendees()
    {
        $this->subject->setMinimumAttendees(42);

        self::assertEquals(
            42,
            $this->subject->getMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMinimumAttendeesWithoutMinimumAttendeesReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMinimumAttendeesWithMinimumAttendeesReturnsTrue()
    {
        $this->subject->setMinimumAttendees(42);

        self::assertTrue(
            $this->subject->hasMinimumAttendees()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function getMaximumAttendeesWithMaximumAttendeesReturnsMaximumAttendees()
    {
        $this->subject->setData(['attendees_max' => 42]);

        self::assertEquals(
            42,
            $this->subject->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMaximumAttendeesWithNegativeMaximumAttendeesThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $maximumAttendees must be >= 0.'
        );

        $this->subject->setMaximumAttendees(-1);
    }

    /**
     * @test
     */
    public function setMaximumAttendeesWithZeroMaximumAttendeesSetsMaximumAttendees()
    {
        $this->subject->setMaximumAttendees(0);

        self::assertEquals(
            0,
            $this->subject->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function setMaximumAttendeesWithPositiveAttendeesSetsMaximumAttendees()
    {
        $this->subject->setMaximumAttendees(42);

        self::assertEquals(
            42,
            $this->subject->getMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMaximumAttendeesWithoutMaximumAttendeesReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMaximumAttendeesWithMaximumAttendeesReturnsTrue()
    {
        $this->subject->setMaximumAttendees(42);

        self::assertTrue(
            $this->subject->hasMaximumAttendees()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationQueueWithRegistrationQueueReturnsTrue()
    {
        $this->subject->setData(['queue_size' => true]);

        self::assertTrue(
            $this->subject->hasRegistrationQueue()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->shouldSkipCollisionCheck()
        );
    }

    /**
     * @test
     */
    public function shouldSkipCollectionCheckWithSkipCollisionCheckReturnsTrue()
    {
        $this->subject->setData(['skip_collision_check' => true]);

        self::assertTrue(
            $this->subject->shouldSkipCollisionCheck()
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
        $this->subject->setData([]);

        self::assertEquals(
            \Tx_Seminars_Model_Event::STATUS_PLANNED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusPlannedReturnsStatusPlanned()
    {
        $this->subject->setData(
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Event::STATUS_PLANNED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusCanceledReturnStatusCanceled()
    {
        $this->subject->setData(
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Event::STATUS_CANCELED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusConfirmedReturnsStatusConfirmed()
    {
        $this->subject->setData(
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED]
        );

        self::assertEquals(
            \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithInvalidStatusThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setStatus(-1);
    }

    /**
     * @test
     */
    public function setStatusWithStatusPlannedSetsStatus()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertEquals(
            \Tx_Seminars_Model_Event::STATUS_PLANNED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithStatusCanceledSetsStatus()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertEquals(
            \Tx_Seminars_Model_Event::STATUS_CANCELED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithStatusConfirmedSetsStatus()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertEquals(
            \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function isPlannedForPlannedStatusReturnsTrue()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertTrue($this->subject->isPlanned());
    }

    /**
     * @test
     */
    public function isPlannedForCanceledStatusReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse($this->subject->isPlanned());
    }

    /**
     * @test
     */
    public function isPlannedForConfirmedStatusReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse($this->subject->isPlanned());
    }

    /**
     * @test
     */
    public function isCanceledForPlannedStatusReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function isCanceledForCanceledStatusReturnsTrue()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertTrue($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function isCanceledForConfirmedStatusReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertFalse($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function isConfirmedForPlannedStatusReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        self::assertFalse($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function isConfirmedForCanceledStatusReturnsFalse()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        self::assertFalse($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function isConfirmedForConfirmedStatusReturnsTrue()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        self::assertTrue($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function cancelCanMakePlannedEventCanceled()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->subject->cancel();

        self::assertTrue($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function cancelCanMakeConfirmedEventCanceled()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        $this->subject->cancel();

        self::assertTrue($this->subject->isCanceled());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function cancelForCanceledEventNotThrowsException()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        $this->subject->cancel();
    }

    /**
     * @test
     */
    public function confirmCanMakePlannedEventConfirmed()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->subject->confirm();

        self::assertTrue($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function confirmCanMakeCanceledEventConfirmed()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        $this->subject->confirm();

        self::assertTrue($this->subject->isConfirmed());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function confirmForConfirmedEventNotThrowsException()
    {
        $this->subject->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        $this->subject->confirm();
    }

    ////////////////////////////////////////
    // Tests regarding the attached files.
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getAttachedFilesWithoutAttachedFilesReturnsEmptyArray()
    {
        $this->subject->setData([]);

        self::assertEquals(
            [],
            $this->subject->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function getAttachedFilesWithOneAttachedFileReturnsArrayWithAttachedFile()
    {
        $this->subject->setData(['attached_files' => 'file.txt']);

        self::assertEquals(
            ['file.txt'],
            $this->subject->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function getAttachedFilesWithTwoAttachedFilesReturnsArrayWithBothAttachedFiles()
    {
        $this->subject->setData(['attached_files' => 'file.txt,file2.txt']);

        self::assertEquals(
            ['file.txt', 'file2.txt'],
            $this->subject->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function setAttachedFilesSetsAttachedFiles()
    {
        $this->subject->setAttachedFiles(['file.txt']);

        self::assertEquals(
            ['file.txt'],
            $this->subject->getAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithoutAttachedFilesReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAttachedFiles()
        );
    }

    /**
     * @test
     */
    public function hasAttachedFilesWithAttachedFileReturnsTrue()
    {
        $this->subject->setAttachedFiles(['file.txt']);

        self::assertTrue(
            $this->subject->hasAttachedFiles()
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
        $this->subject->setData(['begin_date_registration' => 0]);

        self::assertFalse(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationBeginForEventWithRegistrationBeginReturnsTrue()
    {
        $this->subject->setData(['begin_date_registration' => 42]);

        self::assertTrue(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithoutRegistrationBeginReturnsZero()
    {
        $this->subject->setData(['begin_date_registration' => 0]);

        self::assertEquals(
            0,
            $this->subject->getRegistrationBeginAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithRegistrationBeginReturnsRegistrationBeginAsUnixTimestamp()
    {
        $this->subject->setData(['begin_date_registration' => 42]);

        self::assertEquals(
            42,
            $this->subject->getRegistrationBeginAsUnixTimestamp()
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
        $this->subject->setData(['publication_hash' => '']);

        self::assertFalse(
            $this->subject->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function hasPublicationHashForPublicationHashSetReturnsTrue()
    {
        $this->subject->setData(['publication_hash' => 'fooo']);

        self::assertTrue(
            $this->subject->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function getPublicationHashForNoPublicationHashSetReturnsEmptyString()
    {
        $this->subject->setData(['publication_hash' => '']);

        self::assertEquals(
            '',
            $this->subject->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function getPublicationHashForPublicationHashSetReturnsPublicationHash()
    {
        $this->subject->setData(['publication_hash' => 'fooo']);

        self::assertEquals(
            'fooo',
            $this->subject->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function setPublicationHashSetsPublicationHash()
    {
        $this->subject->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertEquals(
            '5318761asdf35as5sad35asd35asd',
            $this->subject->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function setPublicationHashWithEmptyStringOverridesNonEmptyData()
    {
        $this->subject->setData(['publication_hash' => 'fooo']);

        $this->subject->setPublicationHash('');

        self::assertEquals(
            '',
            $this->subject->getPublicationHash()
        );
    }

    /**
     * @test
     */
    public function purgePublicationHashForPublicationHashSetInModelPurgesPublicationHash()
    {
        $this->subject->setData(['publication_hash' => 'fooo']);

        $this->subject->purgePublicationHash();

        self::assertFalse(
            $this->subject->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function purgePublicationHashForNoPublicationHashSetInModelPurgesPublicationHash()
    {
        $this->subject->setData(['publication_hash' => '']);

        $this->subject->purgePublicationHash();

        self::assertFalse(
            $this->subject->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithoutPublicationHashIsTrue()
    {
        $this->subject->setPublicationHash('');

        self::assertTrue(
            $this->subject->isPublished()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithPublicationHashIsFalse()
    {
        $this->subject->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertFalse(
            $this->subject->isPublished()
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
        $this->subject->setData(['offline_attendees' => 0]);

        self::assertFalse(
            $this->subject->hasOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTrue()
    {
        $this->subject->setData(['offline_attendees' => 2]);

        self::assertTrue(
            $this->subject->hasOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsZero()
    {
        $this->subject->setData(['offline_attendees' => 0]);

        self::assertEquals(
            0,
            $this->subject->getOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTwo()
    {
        $this->subject->setData(['offline_attendees' => 2]);

        self::assertEquals(
            2,
            $this->subject->getOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function setOfflineRegistrationsSetsOfflineRegistrations()
    {
        $numberOfOfflineRegistrations = 2;
        $this->subject->setData(['offline_attendees' => 0]);

        $this->subject->setOfflineRegistrations($numberOfOfflineRegistrations);

        self::assertSame(
            $numberOfOfflineRegistrations,
            $this->subject->getOfflineRegistrations()
        );
    }

    /*
     * Tests concerning the registrations
     */

    /**
     * @test
     */
    public function getRegistrationsReturnsRegistrations()
    {
        $registrations = new \Tx_Oelib_List();

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(
            $registrations,
            $this->subject->getRegistrations()
        );
    }

    /**
     * @test
     */
    public function setRegistrationsSetsRegistrations()
    {
        $registrations = new \Tx_Oelib_List();

        $this->subject->setRegistrations($registrations);

        self::assertSame(
            $registrations,
            $this->subject->getRegistrations()
        );
    }

    /**
     * @test
     */
    public function getRegularRegistrationsReturnsRegularRegistrations()
    {
        $registrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 0]
            );
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertEquals(
            $registration->getUid(),
            $this->subject->getRegularRegistrations()->getUids()
        );
    }

    /**
     * @test
     */
    public function getRegularRegistrationsNotReturnsQueueRegistrations()
    {
        $registrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 1]
            );
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue(
            $this->subject->getRegularRegistrations()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getQueueRegistrationsReturnsQueueRegistrations()
    {
        $registrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 1]
            );
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertEquals(
            $registration->getUid(),
            $this->subject->getQueueRegistrations()->getUids()
        );
    }

    /**
     * @test
     */
    public function getQueueRegistrationsNotReturnsRegularRegistrations()
    {
        $registrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 0]
            );
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue(
            $this->subject->getQueueRegistrations()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function hasQueueRegistrationsForOneQueueRegistrationReturnsTrue()
    {
        $registrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['registration_queue' => 1]
            );
        $registrations->add($registration);
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getQueueRegistrations']
        );
        $event->method('getQueueRegistrations')
            ->willReturn($registrations);

        self::assertTrue(
            $event->hasQueueRegistrations()
        );
    }

    /**
     * @test
     */
    public function getRegistrationsAfterLastDigestReturnsNewerRegistrations()
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(1);

        $registrations = new \Tx_Oelib_List();
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(['crdate' => 2]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertContains($registration, $this->subject->getRegistrationsAfterLastDigest());
    }

    /**
     * @test
     */
    public function getRegistrationsAfterLastDigestNotReturnsOlderRegistrations()
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(2);

        $registrations = new \Tx_Oelib_List();
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(['crdate' => 1]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue($this->subject->getRegistrationsAfterLastDigest()->isEmpty());
    }

    /**
     * @test
     */
    public function getRegistrationsAfterLastDigestNotReturnsRegistrationsExactlyAtDigestDate()
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(1);

        $registrations = new \Tx_Oelib_List();
        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(['crdate' => 1]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue($this->subject->getRegistrationsAfterLastDigest()->isEmpty());
    }

    /**
     * @test
     */
    public function hasQueueRegistrationsForNoQueueRegistrationReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getQueueRegistrations']
        );
        $event->method('getQueueRegistrations')
            ->willReturn(new \Tx_Oelib_List());

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
        $this->subject->setData(['attendees_max' => 0]);

        self::assertTrue(
            $this->subject->hasUnlimitedVacancies()
        );
    }

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForMaxAttendeesOneReturnsFalse()
    {
        $this->subject->setData(['attendees_max' => 1]);

        self::assertFalse(
            $this->subject->hasUnlimitedVacancies()
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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegularRegistrations']
        );
        $event->setData([]);
        $event->method('getRegularRegistrations')
            ->willReturn(new \Tx_Oelib_List());

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
        $registrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['seats' => 1]
            );
        $registrations->add($registration);
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegularRegistrations']
        );
        $event->setData([]);
        $event->method('getRegularRegistrations')
            ->willReturn($registrations);

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
        $registrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['seats' => 2]
            );
        $registrations->add($registration);
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegularRegistrations']
        );
        $event->setData([]);
        $event->method('getRegularRegistrations')
            ->willReturn($registrations);

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
        $queueRegistrations = new \Tx_Oelib_List();
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getLoadedTestingModel(
                ['seats' => 1]
            );
        $queueRegistrations->add($registration);
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegularRegistrations', 'getQueueRegistrations']
        );
        $event->setData([]);
        $event->method('getQueueRegistrations')
            ->willReturn($queueRegistrations);
        $event->method('getRegularRegistrations')
            ->willReturn(new \Tx_Oelib_List());

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegularRegistrations']
        );
        $event->setData(['offline_attendees' => 2]);
        $event->method('getRegularRegistrations')
            ->willReturn(new \Tx_Oelib_List());

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 0]);
        $event->method('getRegisteredSeats')
            ->willReturn(0);

        self::assertTrue(
            $event->hasEnoughRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForLessSeatsThanNeededReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(1);

        self::assertFalse(
            $event->hasEnoughRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForAsManySeatsAsNeededReturnsTrue()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

        self::assertTrue(
            $event->hasEnoughRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasEnoughRegistrationsForMoreSeatsThanNeededReturnsTrue()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_min' => 1]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(1);

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 1]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
        $event->method('getRegisteredSeats')
            ->willReturn(1);

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(1);

        self::assertTrue(
            $event->hasVacancies()
        );
    }

    /**
     * @test
     */
    public function hasVacanciesForAsManySeatsRegisteredAsMaximumReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

        self::assertFalse(
            $event->hasVacancies()
        );
    }

    /**
     * @test
     */
    public function hasVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 1]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

        self::assertFalse(
            $event->hasVacancies()
        );
    }

    /**
     * @test
     */
    public function hasVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsTrue()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
        $event->method('getRegisteredSeats')
            ->willReturn(1);

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
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(1);

        self::assertFalse(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForAsManySeatsAsMaximumReturnsTrue()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 2]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

        self::assertTrue(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForMoreSeatsThanMaximumReturnsTrue()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 1]);
        $event->method('getRegisteredSeats')
            ->willReturn(2);

        self::assertTrue(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForZeroSeatsAndUnlimitedMaximumReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
        $event->method('getRegisteredSeats')
            ->willReturn(0);

        self::assertFalse(
            $event->isFull()
        );
    }

    /**
     * @test
     */
    public function isFullForPositiveSeatsAndUnlimitedMaximumReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|MockObject $event */
        $event = $this->createPartialMock(
            \Tx_Seminars_Model_Event::class,
            ['getRegisteredSeats']
        );
        $event->setData(['attendees_max' => 0]);
        $event->method('getRegisteredSeats')
            ->willReturn(1);

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
        $this->subject->setRegistrations(new \Tx_Oelib_List());

        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class)
            ->getLoadedTestingModel([]);
        $this->subject->attachRegistration($registration);

        self::assertTrue(
            $this->subject->getRegistrations()->hasUid($registration->getUid())
        );
    }

    /**
     * @test
     */
    public function attachRegistrationNotRemovesExistingRegistration()
    {
        $registrations = new \Tx_Oelib_List();
        $oldRegistration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getNewGhost();
        $registrations->add($oldRegistration);
        $this->subject->setRegistrations($registrations);

        /** @var \Tx_Seminars_Model_Registration $newRegistration */
        $newRegistration = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Registration::class)
            ->getLoadedTestingModel([]);
        $this->subject->attachRegistration($newRegistration);

        self::assertTrue(
            $this->subject->getRegistrations()->hasUid($oldRegistration->getUid())
        );
    }

    /**
     * @test
     */
    public function attachRegistrationSetsEventForRegistration()
    {
        $this->subject->setRegistrations(new \Tx_Oelib_List());

        /** @var \Tx_Seminars_Model_Registration $registration */
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)
            ->getLoadedTestingModel([]);
        $this->subject->attachRegistration($registration);

        self::assertSame(
            $this->subject,
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
        $paymentMethods = new \Tx_Oelib_List();
        $this->subject->setData(
            ['payment_methods' => $paymentMethods]
        );

        self::assertSame(
            $paymentMethods,
            $this->subject->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodsSetsPaymentMethods()
    {
        $this->subject->setData([]);

        $paymentMethods = new \Tx_Oelib_List();
        $this->subject->setPaymentMethods($paymentMethods);

        self::assertSame(
            $paymentMethods,
            $this->subject->getPaymentMethods()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesReturnsFalseValueFromDatabase()
    {
        $this->subject->setData(['organizers_notified_about_minimum_reached' => 1]);

        self::assertTrue(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function setOrganizersBeenNotifiedAboutEnoughAttendeesMarksItAsTrue()
    {
        $this->subject->setData([]);

        $this->subject->setOrganizersBeenNotifiedAboutEnoughAttendees();

        self::assertTrue(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsReturnsTrueValueFromDatabase()
    {
        $this->subject->setData(
            ['mute_notification_emails' => 1]
        );

        self::assertTrue(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function muteNotificationEmailsSetsShouldMute()
    {
        $this->subject->muteNotificationEmails();

        self::assertTrue(
            $this->subject->shouldMuteNotificationEmails()
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
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->shouldAutomaticallyConfirmOrCancel()
        );
    }

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelReturnsTrueValueFromDatabase()
    {
        $this->subject->setData(
            ['automatic_confirmation_cancelation' => 1]
        );

        self::assertTrue(
            $this->subject->shouldAutomaticallyConfirmOrCancel()
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
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getOrganizers();

        self::assertSame($organizers, $result);
    }

    /**
     * @test
     */
    public function getFirstOrganizerForNoOrganizersReturnsNull()
    {
        $organizers = new \Tx_Oelib_List();
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getFirstOrganizer();

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
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getFirstOrganizer();

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
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getFirstOrganizer();

        self::assertSame($firstOrganizer, $result);
    }

    /*
     * Tests concerning getAttendeeNames
     */

    /**
     * @test
     */
    public function getAttendeeNamesForNoRegistrationsReturnsEmptyArray()
    {
        $this->subject->setData(['registrations' => new \Tx_Oelib_List()]);

        self::assertSame([], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationWithRegisteredThemselvesReturnsThatName()
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData(['first_name' => $firstName, 'last_name' => $lastName]);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => true,
                'additional_persons' => new \Tx_Oelib_List(),
            ]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([$firstName . ' ' . $lastName], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationOnlyWithoutRegisteredThemselvesReturnsEmptyArray()
    {
        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData([]);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            ['user' => $user, 'registered_themselves' => false, 'additional_persons' => new \Tx_Oelib_List()]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationReturnsAdditionalAttendeeNamesFromAttachedUsers()
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData([]);

        $additionalPerson = new \Tx_Seminars_Model_FrontEndUser();
        $additionalPerson->setData(['first_name' => $firstName, 'last_name' => $lastName]);
        $additionalPersons = new \Tx_Oelib_List();
        $additionalPersons->add($additionalPerson);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            ['user' => $user, 'registered_themselves' => false, 'additional_persons' => $additionalPersons]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([$firstName . ' ' . $lastName], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationForAttachedUsersIgnoresFreeTextNames()
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData([]);

        $additionalPerson = new \Tx_Seminars_Model_FrontEndUser();
        $additionalPerson->setData(['first_name' => $firstName, 'last_name' => $lastName]);
        $additionalPersons = new \Tx_Oelib_List();
        $additionalPersons->add($additionalPerson);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => false,
                'additional_persons' => $additionalPersons,
                'attendees_names' => 'Jane Doe',
            ]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([$firstName . ' ' . $lastName], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationReturnsAdditionalAttendeeNamesFromFreeTextField()
    {
        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData([]);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => false,
                'additional_persons' => new \Tx_Oelib_List(),
                'attendees_names' => 'Jane Doe' . CRLF . 'John Doe',
            ]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(['Jane Doe', 'John Doe'], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesSortsNamesFromRegisteredThemselvesByFullName()
    {
        $registrations = new \Tx_Oelib_List();

        $firstName1 = 'Oliver';
        $lastName1 = 'Klee';
        $user1 = new \Tx_Seminars_Model_FrontEndUser();
        $user1->setData(['first_name' => $firstName1, 'last_name' => $lastName1]);
        $registration1 = new \Tx_Seminars_Model_Registration();
        $registration1->setData(
            [
                'user' => $user1,
                'registered_themselves' => true,
                'additional_persons' => new \Tx_Oelib_List(),
            ]
        );
        $registrations->add($registration1);

        $firstName2 = 'Jane';
        $lastName2 = 'Wolowitz';
        $user2 = new \Tx_Seminars_Model_FrontEndUser();
        $user2->setData(['first_name' => $firstName2, 'last_name' => $lastName2]);
        $registration2 = new \Tx_Seminars_Model_Registration();
        $registration2->setData(
            ['user' => $user2, 'registered_themselves' => true, 'additional_persons' => new \Tx_Oelib_List()]
        );
        $registrations->add($registration2);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(
            [
                $firstName2 . ' ' . $lastName2,
                $firstName1 . ' ' . $lastName1,
            ],
            $this->subject->getAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getAttendeeNamesSortsNamesFromAdditionalAttendeesFromUsersByFullName()
    {
        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData([]);
        $additionalPersons = new \Tx_Oelib_List();

        $firstName1 = 'Oliver';
        $lastName1 = 'Klee';
        $additionalPerson1 = new \Tx_Seminars_Model_FrontEndUser();
        $additionalPerson1->setData(['first_name' => $firstName1, 'last_name' => $lastName1]);
        $additionalPersons->add($additionalPerson1);

        $firstName2 = 'Jane';
        $lastName2 = 'Wolowitz';
        $additionalPerson2 = new \Tx_Seminars_Model_FrontEndUser();
        $additionalPerson2->setData(['first_name' => $firstName2, 'last_name' => $lastName2]);
        $additionalPersons->add($additionalPerson2);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            ['user' => $user, 'registered_themselves' => false, 'additional_persons' => $additionalPersons]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(
            [
                $firstName2 . ' ' . $lastName2,
                $firstName1 . ' ' . $lastName1,
            ],
            $this->subject->getAttendeeNames()
        );
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationSortAdditionalAttendeeNamesFromFreeTextField()
    {
        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData([]);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => false,
                'additional_persons' => new \Tx_Oelib_List(),
                'attendees_names' => 'John Doe' . CRLF . 'Jane Doe',
            ]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(['Jane Doe', 'John Doe'], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesAfterLastDigestUsesNewerRegistration()
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData(['first_name' => $firstName, 'last_name' => $lastName]);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => true,
                'additional_persons' => new \Tx_Oelib_List(),
                'crdate' => 2,
            ]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations, 'date_of_last_registration_digest' => 1]);

        self::assertSame(
            [$firstName . ' ' . $lastName],
            $this->subject->getAttendeeNamesAfterLastDigest()
        );
    }

    /**
     * @test
     */
    public function getAttendeeNamesAfterLastDigestIgnoresOlderRegistration()
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new \Tx_Seminars_Model_FrontEndUser();
        $user->setData(['first_name' => $firstName, 'last_name' => $lastName]);

        $registration = new \Tx_Seminars_Model_Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => true,
                'additional_persons' => new \Tx_Oelib_List(),
                'crdate' => 1,
            ]
        );
        $registrations = new \Tx_Oelib_List();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations, 'date_of_last_registration_digest' => 2]);

        self::assertSame([], $this->subject->getAttendeeNamesAfterLastDigest());
    }

    /*
     * Tests concerning "price on request"
     */

    public function getPriceOnRequestByDefaultReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getPriceOnRequestReturnsPriceOnRequest()
    {
        $this->subject->setData(['price_on_request' => true]);

        self::assertTrue($this->subject->getPriceOnRequest());
    }

    /*
     * Tests regarding the date of the last registration digest email
     */

    /**
     * @test
     */
    public function getDateOfLastRegistrationDigestEmailAsUnixTimeStampWithoutDateReturnsZero()
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getDateOfLastRegistrationDigestEmailAsUnixTimeStampWithPositiveDateReturnsIt()
    {
        $this->subject->setData(['date_of_last_registration_digest' => 42]);

        self::assertSame(42, $this->subject->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStampWithNegativeDateThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStampWithZeroDateSetsIt()
    {
        $this->subject->setData(['date_of_last_registration_digest' => 42]);
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(0);

        self::assertSame(0, $this->subject->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStampWithPositiveDateSetsIs()
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(42);

        self::assertSame(42, $this->subject->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /*
     * Tests concerning the dates
     */

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampByDefaultReturnsZero()
    {
        $this->subject->setData([]);

        self::assertSame(0, $this->subject->getBeginDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampReturnsBeginDate()
    {
        $timeStamp = 455456;
        $this->subject->setData(['begin_date' => $timeStamp]);

        self::assertSame($timeStamp, $this->subject->getBeginDateAsUnixTimeStamp());
    }
}
