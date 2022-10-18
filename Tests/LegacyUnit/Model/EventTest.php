<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\LanguageMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\Category;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\EventType;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Organizer;
use OliverKlee\Seminars\Model\PaymentMethod;
use OliverKlee\Seminars\Model\Registration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Event
 */
final class EventTest extends TestCase
{
    /**
     * @var Event
     */
    private $subject;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * @var int
     */
    protected $now = 1424751343;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = $this->now;
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);

        $this->subject = new Event();
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();
    }

    /////////////////////////////////////
    // Tests regarding isSingleEvent().
    /////////////////////////////////////

    /**
     * @test
     */
    public function isSingleEventForSingleRecordReturnsTrue(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertTrue(
            $this->subject->isSingleEvent()
        );
    }

    /**
     * @test
     */
    public function isSingleEventForTopicRecordReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertFalse(
            $this->subject->isSingleEvent()
        );
    }

    /**
     * @test
     */
    public function isSingleEventForDateRecordReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_EVENT_DATE]
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
    public function isEventDateForSingleRecordReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForTopicRecordReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertFalse(
            $this->subject->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForDateRecordWithTopicReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
                'topic' => new Event(),
            ]
        );

        self::assertTrue(
            $this->subject->isEventDate()
        );
    }

    /**
     * @test
     */
    public function isEventDateForDateRecordWithoutTopicReturnsFalse(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_EVENT_DATE,
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
    public function getRecordTypeWithRecordTypeCompleteReturnsRecordTypeComplete(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            EventInterface::TYPE_SINGLE_EVENT,
            $this->subject->getRecordType()
        );
    }

    /**
     * @test
     */
    public function getRecordTypeWithRecordTypeDateReturnsRecordTypeDate(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_EVENT_DATE]
        );

        self::assertEquals(
            EventInterface::TYPE_EVENT_DATE,
            $this->subject->getRecordType()
        );
    }

    /**
     * @test
     */
    public function getRecordTypeWithRecordTypeTopicReturnsRecordTypeTopic(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertEquals(
            EventInterface::TYPE_EVENT_TOPIC,
            $this->subject->getRecordType()
        );
    }

    ////////////////////////////////
    // Tests concerning the title.
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
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
    public function getRawTitleWithNonEmptyTitleReturnsTitle(): void
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
    public function setTitleWithEmptyTitleThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $this->subject->setTitle('Superhero');

        self::assertSame('Superhero', $this->subject->getTitle());
    }

    //////////////////////////////////////////////
    // Tests regarding the accreditation number.
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function getAccreditationNumberWithoutAccreditationNumberReturnsAnEmptyString(): void
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
    public function getAccreditationNumberWithAccreditationNumberReturnsAccreditationNumber(): void
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
    public function setAccreditationNumberSetsAccreditationNumber(): void
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
    public function hasAccreditationNumberWithoutAccreditationNumberReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAccreditationNumber()
        );
    }

    /**
     * @test
     */
    public function hasAccreditationNumberWithAccreditationNumberReturnsTrue(): void
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
    public function getRegistrationDeadlineAsUnixTimeStampWithoutRegistrationDeadlineReturnsZero(): void
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
    public function getRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineReturnsRegistrationDeadline(): void
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
    public function setRegistrationDeadlineAsUnixTimeStampWithNegativeRegistrationDeadlineThrowsException(): void
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
    public function setRegistrationDeadlineAsUnixTimeStampWithZeroRegistrationDeadlineSetsRegistrationDeadline(): void
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
    public function setRegistrationDeadlineAsUnixTimeStampWithPositiveRegistrationDeadlineSetsRegistrationDeadline(): void
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
    public function hasRegistrationDeadlineWithoutRegistrationDeadlineReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasRegistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationDeadlineWithRegistrationDeadlineReturnsTrue(): void
    {
        $this->subject->setRegistrationDeadlineAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasRegistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithoutAnyDatesReturnsZero(): void
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
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithBeginDateReturnsBeginDate(): void
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
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithRegistrationDeadlineReturnsRegistrationDeadline(): void
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
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithBeginAndEndAndLateRegistrationReturnsEndDate(): void
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
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithDeadlineAndLateRegistrationReturnsDeadline(): void
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
    public function getLatestPossibleRegistrationTimeAsUnixTimeStampWithBeginAndWithoutEndLateAllowedReturnsBeginDate(): void
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
    public function getEarlyBirdDeadlineAsUnixTimeStampWithoutEarlyBirdDeadlineReturnsZero(): void
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
    public function getEarlyBirdDeadlineAsUnixTimeStampWithPositiveEarlyBirdDeadlineReturnsEarlyBirdDeadline(): void
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
    public function setEarlyBirdDeadlineAsUnixTimeStampWithNegativeEarlyBirdDeadlineThrowsException(): void
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
    public function setEarlyBirdDeadlineAsUnixTimeStampWithZeroEarlyBirdDeadlineSetsEarlyBirdDeadline(): void
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
    public function setEarlyBirdDeadlineWithPositiveEarlyBirdDeadlineSetsEarlyBirdDeadline(): void
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
    public function hasEarlyBirdDeadlineWithoutEarlyBirdDeadlineReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEarlyBirdDeadline()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdDeadlineWithEarlyBirdDeadlineReturnsTrue(): void
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
    public function getUnregistrationDeadlineAsUnixTimeStampWithoutUnregistrationDeadlineReturnsZero(): void
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
    public function getUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineReturnsUnregistrationDeadline(): void
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
    public function setUnregistrationDeadlineAsUnixTimeStampWithNegativeUnregistrationDeadlineThrowsException(): void
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
    public function setUnregistrationDeadlineAsUnixTimeStampWithZeroUnregistrationDeadlineSetsUnregistrationDeadline(): void
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
    public function setUnregistrationDeadlineAsUnixTimeStampWithPositiveUnregistrationDeadlineSetsUnregistrationDeadline(): void
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
    public function hasUnregistrationDeadlineWithoutUnregistrationDeadlineReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasUnregistrationDeadline()
        );
    }

    /**
     * @test
     */
    public function hasUnregistrationDeadlineWithUnregistrationDeadlineReturnsTrue(): void
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
    public function getExpiryAsUnixTimeStampWithoutExpiryReturnsZero(): void
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
    public function getExpiryAsUnixTimeStampWithPositiveExpiryReturnsExpiry(): void
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
    public function setExpiryAsUnixTimeStampWithNegativeExpiryThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setExpiryAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setExpiryAsUnixTimeStampWithZeroExpirySetsExpiry(): void
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
    public function setExpiryAsUnixTimeStampWithPositiveExpirySetsExpiry(): void
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
    public function hasExpiryWithoutExpiryReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasExpiry()
        );
    }

    /**
     * @test
     */
    public function hasExpiryWithExpiryReturnsTrue(): void
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
    public function getDetailsPageWithoutDetailsPageReturnsEmptyString(): void
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
    public function getDetailsPageWithDetailsPageReturnsDetailsPage(): void
    {
        $this->subject->setData(['details_page' => 'https://example.com']);

        self::assertEquals(
            'https://example.com',
            $this->subject->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function setDetailsPageSetsDetailsPage(): void
    {
        $this->subject->setDetailsPage('https://example.com');

        self::assertEquals(
            'https://example.com',
            $this->subject->getDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasDetailsPageWithoutDetailsPageReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDetailsPage()
        );
    }

    /**
     * @test
     */
    public function hasDetailsPageWithDetailsPageReturnsTrue(): void
    {
        $this->subject->setDetailsPage('https://example.com');

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
    public function getCombinedSingleViewPageInitiallyReturnsEmptyString(): void
    {
        $this->subject->setData(['categories' => new Collection()]);

        self::assertEquals(
            '',
            $this->subject->getCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageForAvailableDetailsPageUidReturnsTheDetailsPageUid(): void
    {
        $this->subject->setData(
            [
                'details_page' => '5',
                'categories' => new Collection(),
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
    public function getCombinedSingleViewPageForAvailableDetailsPageUrlReturnsTheDetailsPageUrl(): void
    {
        $this->subject->setData(
            [
                'details_page' => 'www.example.com',
                'categories' => new Collection(),
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
    public function getCombinedSingleViewPageForAvailableEventTypeWithoutSingleViewPageReturnsEmptyString(): void
    {
        $eventType = new EventType();
        $eventType->setData([]);
        $this->subject->setData(
            [
                'event_type' => $eventType,
                'categories' => new Collection(),
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
    public function getCombinedSingleViewPageForAvailableEventTypeWithSingleViewPageReturnsSingleViewPageFromEventType(): void
    {
        $eventType = new EventType();
        $eventType->setData(['single_view_page' => 42]);
        $this->subject->setData(
            [
                'event_type' => $eventType,
                'categories' => new Collection(),
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
    public function getCombinedSingleViewPageForAvailableCategoryWithoutSingleViewPageReturnsEmptyString(): void
    {
        $category = new Category();
        $category->setData([]);
        $categories = new Collection();
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
    public function getCombinedSingleViewPageForAvailableCategoryTypeWithSingleViewPageReturnsSingleViewPageFromCategory(): void
    {
        $category = new Category();
        $category->setData(['single_view_page' => 42]);
        $categories = new Collection();
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
    public function getCombinedSingleViewPageForTwoAvailableCategoriesWithSingleViewPageReturnsSingleViewPageFromFirstCategory(): void
    {
        $category1 = new Category();
        $category1->setData(['single_view_page' => 42]);
        $category2 = new Category();
        $category2->setData(['single_view_page' => 12]);
        $categories = new Collection();
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
    public function hasCombinedSingleViewPageForEmptySingleViewPageReturnsFalse(): void
    {
        $subject = $this->createPartialMock(
            Event::class,
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
    public function hasCombinedSingleViewPageForNonEmptySingleViewPageReturnsTrue(): void
    {
        $subject = $this->createPartialMock(
            Event::class,
            ['getCombinedSingleViewPage']
        );
        $subject->expects(self::atLeastOnce())
            ->method('getCombinedSingleViewPage')->willReturn('42');

        self::assertTrue(
            $subject->hasCombinedSingleViewPage()
        );
    }

    /**
     * @test
     */
    public function getCombinedSingleViewPageUsesDetailsPageInsteadOfEventTypeIfBothAreAvailable(): void
    {
        $eventType = new EventType();
        $eventType->setData(['single_view_page' => 42]);

        $this->subject->setData(
            [
                'details_page' => '5',
                'event_type' => $eventType,
                'categories' => new Collection(),
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
    public function getCombinedSingleViewPageUsesEventTypeInsteadOfCategoriesIfBothAreAvailable(): void
    {
        $eventType = new EventType();
        $eventType->setData(['single_view_page' => 42]);
        $category = new Category();
        $category->setData(['single_view_page' => 91]);
        $categories = new Collection();
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
    public function getLanguageWithoutLanguageReturnsNull(): void
    {
        $this->subject->setData([]);

        self::assertNull(
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageWithLanguageReturnsLanguage(): void
    {
        $this->subject->setData(['language' => 'DE']);

        self::assertSame(
            MapperRegistry::get(LanguageMapper::class)->findByIsoAlpha2Code('DE'),
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageSetsLanguage(): void
    {
        $language = MapperRegistry::get(LanguageMapper::class)->findByIsoAlpha2Code('DE');
        $this->subject->setLanguage($language);

        self::assertSame(
            $language,
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithoutLanguageReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithLanguageReturnsTrue(): void
    {
        $language = MapperRegistry::get(LanguageMapper::class)->findByIsoAlpha2Code('DE');
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
    public function eventTakesPlaceReminderHasBeenSentWithUnsetEventTakesPlaceReminderSentReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->eventTakesPlaceReminderHasBeenSent()
        );
    }

    /**
     * @test
     */
    public function eventTakesPlaceReminderHasBeenSentWithSetEventTakesPlaceReminderSentReturnsTrue(): void
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
    public function cancelationDeadlineReminderHasBeenSentWithUnsetCancelationDeadlineReminderSentReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->cancelationDeadlineReminderHasBeenSent()
        );
    }

    /**
     * @test
     */
    public function cancelationDeadlineReminderHasBeenSentWithSetCancelationDeadlineReminderSentReturnsTrue(): void
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
    public function needsRegistrationWithUnsetNeedsRegistrationReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->needsRegistration()
        );
    }

    /**
     * @test
     */
    public function needsRegistrationWithSetNeedsRegistrationReturnsTrue(): void
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
    public function getMinimumAttendeesWithoutMinimumAttendeesReturnsZero(): void
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
    public function getMinimumAttendeesWithPositiveMinimumAttendeesReturnsMinimumAttendees(): void
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
    public function setMinimumAttendeesWithNegativeMinimumAttendeesThrowsException(): void
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
    public function setMinimumAttendeesWithZeroMinimumAttendeesSetsMinimumAttendees(): void
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
    public function setMinimumAttendeesWithPositiveMinimumAttendeesSetsMinimumAttendees(): void
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
    public function hasMinimumAttendeesWithoutMinimumAttendeesReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasMinimumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMinimumAttendeesWithMinimumAttendeesReturnsTrue(): void
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
    public function getMaximumAttendeesWithoutMaximumAttendeesReturnsZero(): void
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
    public function getMaximumAttendeesWithMaximumAttendeesReturnsMaximumAttendees(): void
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
    public function setMaximumAttendeesWithNegativeMaximumAttendeesThrowsException(): void
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
    public function setMaximumAttendeesWithZeroMaximumAttendeesSetsMaximumAttendees(): void
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
    public function setMaximumAttendeesWithPositiveAttendeesSetsMaximumAttendees(): void
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
    public function hasMaximumAttendeesWithoutMaximumAttendeesReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasMaximumAttendees()
        );
    }

    /**
     * @test
     */
    public function hasMaximumAttendeesWithMaximumAttendeesReturnsTrue(): void
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
    public function hasRegistrationQueueWithoutRegistrationQueueReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasRegistrationQueue()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationQueueWithRegistrationQueueReturnsTrue(): void
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
    public function shouldSkipCollectionCheckWithoutSkipCollsionCheckReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->shouldSkipCollisionCheck()
        );
    }

    /**
     * @test
     */
    public function shouldSkipCollectionCheckWithSkipCollisionCheckReturnsTrue(): void
    {
        $this->subject->setData(['skip_collision_check' => true]);

        self::assertTrue(
            $this->subject->shouldSkipCollisionCheck()
        );
    }

    // Tests regarding the status.

    /**
     * @test
     */
    public function getStatusWithoutStatusReturnsStatusPlanned(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            Event::STATUS_PLANNED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusPlannedReturnsStatusPlanned(): void
    {
        $this->subject->setData(
            ['cancelled' => Event::STATUS_PLANNED]
        );

        self::assertEquals(
            Event::STATUS_PLANNED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusCanceledReturnStatusCanceled(): void
    {
        $this->subject->setData(
            ['cancelled' => Event::STATUS_CANCELED]
        );

        self::assertEquals(
            Event::STATUS_CANCELED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function getStatusWithStatusConfirmedReturnsStatusConfirmed(): void
    {
        $this->subject->setData(
            ['cancelled' => Event::STATUS_CONFIRMED]
        );

        self::assertEquals(
            Event::STATUS_CONFIRMED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithInvalidStatusThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // @phpstan-ignore-next-line We are explicitly testing with a contract violation here.
        $this->subject->setStatus(-1);
    }

    /**
     * @test
     */
    public function setStatusWithStatusPlannedSetsStatus(): void
    {
        $this->subject->setStatus(Event::STATUS_PLANNED);

        self::assertEquals(
            Event::STATUS_PLANNED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithStatusCanceledSetsStatus(): void
    {
        $this->subject->setStatus(Event::STATUS_CANCELED);

        self::assertEquals(
            Event::STATUS_CANCELED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function setStatusWithStatusConfirmedSetsStatus(): void
    {
        $this->subject->setStatus(Event::STATUS_CONFIRMED);

        self::assertEquals(
            Event::STATUS_CONFIRMED,
            $this->subject->getStatus()
        );
    }

    /**
     * @test
     */
    public function isPlannedForPlannedStatusReturnsTrue(): void
    {
        $this->subject->setStatus(Event::STATUS_PLANNED);

        self::assertTrue($this->subject->isPlanned());
    }

    /**
     * @test
     */
    public function isPlannedForCanceledStatusReturnsFalse(): void
    {
        $this->subject->setStatus(Event::STATUS_CANCELED);

        self::assertFalse($this->subject->isPlanned());
    }

    /**
     * @test
     */
    public function isPlannedForConfirmedStatusReturnsFalse(): void
    {
        $this->subject->setStatus(Event::STATUS_CONFIRMED);

        self::assertFalse($this->subject->isPlanned());
    }

    /**
     * @test
     */
    public function isCanceledForPlannedStatusReturnsFalse(): void
    {
        $this->subject->setStatus(Event::STATUS_PLANNED);

        self::assertFalse($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function isCanceledForCanceledStatusReturnsTrue(): void
    {
        $this->subject->setStatus(Event::STATUS_CANCELED);

        self::assertTrue($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function isCanceledForConfirmedStatusReturnsFalse(): void
    {
        $this->subject->setStatus(Event::STATUS_CONFIRMED);

        self::assertFalse($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function isConfirmedForPlannedStatusReturnsFalse(): void
    {
        $this->subject->setStatus(Event::STATUS_PLANNED);

        self::assertFalse($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function isConfirmedForCanceledStatusReturnsFalse(): void
    {
        $this->subject->setStatus(Event::STATUS_CANCELED);

        self::assertFalse($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function isConfirmedForConfirmedStatusReturnsTrue(): void
    {
        $this->subject->setStatus(Event::STATUS_CONFIRMED);

        self::assertTrue($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function cancelCanMakePlannedEventCanceled(): void
    {
        $this->subject->setStatus(Event::STATUS_PLANNED);

        $this->subject->cancel();

        self::assertTrue($this->subject->isCanceled());
    }

    /**
     * @test
     */
    public function cancelCanMakeConfirmedEventCanceled(): void
    {
        $this->subject->setStatus(Event::STATUS_CONFIRMED);

        $this->subject->cancel();

        self::assertTrue($this->subject->isCanceled());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function cancelForCanceledEventNotThrowsException(): void
    {
        $this->subject->setStatus(Event::STATUS_CANCELED);

        $this->subject->cancel();
    }

    /**
     * @test
     */
    public function confirmCanMakePlannedEventConfirmed(): void
    {
        $this->subject->setStatus(Event::STATUS_PLANNED);

        $this->subject->confirm();

        self::assertTrue($this->subject->isConfirmed());
    }

    /**
     * @test
     */
    public function confirmCanMakeCanceledEventConfirmed(): void
    {
        $this->subject->setStatus(Event::STATUS_CANCELED);

        $this->subject->confirm();

        self::assertTrue($this->subject->isConfirmed());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function confirmForConfirmedEventNotThrowsException(): void
    {
        $this->subject->setStatus(Event::STATUS_CONFIRMED);

        $this->subject->confirm();
    }

    ////////////////////////////////////////////////
    // Tests regarding the registration begin date
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function hasRegistrationBeginForNoRegistrationBeginReturnsFalse(): void
    {
        $this->subject->setData(['begin_date_registration' => 0]);

        self::assertFalse(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function hasRegistrationBeginForEventWithRegistrationBeginReturnsTrue(): void
    {
        $this->subject->setData(['begin_date_registration' => 42]);

        self::assertTrue(
            $this->subject->hasRegistrationBegin()
        );
    }

    /**
     * @test
     */
    public function getRegistrationBeginAsUnixTimestampForEventWithoutRegistrationBeginReturnsZero(): void
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
    public function getRegistrationBeginAsUnixTimestampForEventWithRegistrationBeginReturnsRegistrationBeginAsUnixTimestamp(): void
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
    public function hasPublicationHashForNoPublicationHashSetReturnsFalse(): void
    {
        $this->subject->setData(['publication_hash' => '']);

        self::assertFalse(
            $this->subject->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function hasPublicationHashForPublicationHashSetReturnsTrue(): void
    {
        $this->subject->setData(['publication_hash' => 'fooo']);

        self::assertTrue(
            $this->subject->hasPublicationHash()
        );
    }

    /**
     * @test
     */
    public function getPublicationHashForNoPublicationHashSetReturnsEmptyString(): void
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
    public function getPublicationHashForPublicationHashSetReturnsPublicationHash(): void
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
    public function setPublicationHashSetsPublicationHash(): void
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
    public function setPublicationHashWithEmptyStringOverridesNonEmptyData(): void
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
    public function purgePublicationHashForPublicationHashSetInModelPurgesPublicationHash(): void
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
    public function purgePublicationHashForNoPublicationHashSetInModelPurgesPublicationHash(): void
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
    public function isPublishedForEventWithoutPublicationHashIsTrue(): void
    {
        $this->subject->setPublicationHash('');

        self::assertTrue(
            $this->subject->isPublished()
        );
    }

    /**
     * @test
     */
    public function isPublishedForEventWithPublicationHashIsFalse(): void
    {
        $this->subject->setPublicationHash('5318761asdf35as5sad35asd35asd');

        self::assertFalse(
            $this->subject->isPublished()
        );
    }

    // Tests concerning the offline registrations

    /**
     * @test
     */
    public function hasOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsFalse(): void
    {
        $this->subject->setData(['offline_attendees' => 0]);

        self::assertFalse(
            $this->subject->hasOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function hasOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTrue(): void
    {
        $this->subject->setData(['offline_attendees' => 2]);

        self::assertTrue(
            $this->subject->hasOfflineRegistrations()
        );
    }

    /**
     * @test
     */
    public function getOfflineRegistrationsForEventWithoutOfflineRegistrationsReturnsZero(): void
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
    public function getOfflineRegistrationsForEventWithTwoOfflineRegistrationsReturnsTwo(): void
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
    public function setOfflineRegistrationsSetsOfflineRegistrations(): void
    {
        $numberOfOfflineRegistrations = 2;
        $this->subject->setData(['offline_attendees' => 0]);

        $this->subject->setOfflineRegistrations($numberOfOfflineRegistrations);

        self::assertSame(
            $numberOfOfflineRegistrations,
            $this->subject->getOfflineRegistrations()
        );
    }

    // Tests concerning the registrations

    /**
     * @test
     */
    public function getRegistrationsReturnsRegistrations(): void
    {
        $registrations = new Collection();

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(
            $registrations,
            $this->subject->getRegistrations()
        );
    }

    /**
     * @test
     */
    public function setRegistrationsSetsRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $this->subject->setRegistrations($registrations);

        self::assertSame(
            $registrations,
            $this->subject->getRegistrations()
        );
    }

    /**
     * @test
     */
    public function getRegularRegistrationsReturnsRegularRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 0]);
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
    public function getRegularRegistrationsNotReturnsQueueRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 1]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue(
            $this->subject->getRegularRegistrations()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getQueueRegistrationsReturnsQueueRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 1]);
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
    public function getQueueRegistrationsNotReturnsRegularRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 0]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue(
            $this->subject->getQueueRegistrations()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function hasQueueRegistrationsForOneQueueRegistrationReturnsTrue(): void
    {
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 1]);
        $registrations->add($registration);
        $event = $this->createPartialMock(
            Event::class,
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
    public function getRegistrationsAfterLastDigestReturnsNewerRegistrations(): void
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(1);

        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = new Registration();
        $registration->setData(['crdate' => 2]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertContains($registration, $this->subject->getRegistrationsAfterLastDigest());
    }

    /**
     * @test
     */
    public function getRegistrationsAfterLastDigestNotReturnsOlderRegistrations(): void
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(2);

        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = new Registration();
        $registration->setData(['crdate' => 1]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue($this->subject->getRegistrationsAfterLastDigest()->isEmpty());
    }

    /**
     * @test
     */
    public function getRegistrationsAfterLastDigestNotReturnsRegistrationsExactlyAtDigestDate(): void
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(1);

        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = new Registration();
        $registration->setData(['crdate' => 1]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue($this->subject->getRegistrationsAfterLastDigest()->isEmpty());
    }

    /**
     * @test
     */
    public function hasQueueRegistrationsForNoQueueRegistrationReturnsFalse(): void
    {
        $event = $this->createPartialMock(
            Event::class,
            ['getQueueRegistrations']
        );
        $event->method('getQueueRegistrations')
            ->willReturn(new Collection());

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
    public function hasUnlimitedVacanciesForMaxAttendeesZeroReturnsTrue(): void
    {
        $this->subject->setData(['attendees_max' => 0]);

        self::assertTrue(
            $this->subject->hasUnlimitedVacancies()
        );
    }

    /**
     * @test
     */
    public function hasUnlimitedVacanciesForMaxAttendeesOneReturnsFalse(): void
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
    public function getRegisteredSeatsForNoRegularRegistrationsReturnsZero(): void
    {
        $event = $this->createPartialMock(
            Event::class,
            ['getRegularRegistrations']
        );
        $event->setData([]);
        $event->method('getRegularRegistrations')
            ->willReturn(new Collection());

        self::assertEquals(
            0,
            $event->getRegisteredSeats()
        );
    }

    /**
     * @test
     */
    public function getRegisteredSeatsCountsSingleSeatRegularRegistrations(): void
    {
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['seats' => 1]);
        $registrations->add($registration);
        $event = $this->createPartialMock(
            Event::class,
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
    public function getRegisteredSeatsCountsMultiSeatRegularRegistrations(): void
    {
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['seats' => 2]);
        $registrations->add($registration);
        $event = $this->createPartialMock(
            Event::class,
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
    public function getRegisteredSeatsNotCountsQueueRegistrations(): void
    {
        $queueRegistrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['seats' => 1]);
        $queueRegistrations->add($registration);
        $event = $this->createPartialMock(
            Event::class,
            ['getRegularRegistrations', 'getQueueRegistrations']
        );
        $event->setData([]);
        $event->method('getQueueRegistrations')
            ->willReturn($queueRegistrations);
        $event->method('getRegularRegistrations')
            ->willReturn(new Collection());

        self::assertEquals(
            0,
            $event->getRegisteredSeats()
        );
    }

    /**
     * @test
     */
    public function getRegisteredSeatsCountsOfflineRegistrations(): void
    {
        $event = $this->createPartialMock(
            Event::class,
            ['getRegularRegistrations']
        );
        $event->setData(['offline_attendees' => 2]);
        $event->method('getRegularRegistrations')
            ->willReturn(new Collection());

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
    public function hasEnoughRegistrationsForZeroSeatsAndZeroNeededReturnsTrue(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function hasEnoughRegistrationsForLessSeatsThanNeededReturnsFalse(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function hasEnoughRegistrationsForAsManySeatsAsNeededReturnsTrue(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function hasEnoughRegistrationsForMoreSeatsThanNeededReturnsTrue(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function getVacanciesForOneRegisteredAndTwoMaximumReturnsOne(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function getVacanciesForAsManySeatsRegisteredAsMaximumReturnsZero(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function getVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsZero(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function getVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsZero(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function hasVacanciesForOneRegisteredAndTwoMaximumReturnsTrue(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function hasVacanciesForAsManySeatsRegisteredAsMaximumReturnsFalse(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function hasVacanciesForAsMoreSeatsRegisteredThanMaximumReturnsFalse(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function hasVacanciesForNonZeroSeatsRegisteredAndUnlimitedVacanciesReturnsTrue(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function isFullForLessSeatsThanMaximumReturnsFalse(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function isFullForAsManySeatsAsMaximumReturnsTrue(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function isFullForMoreSeatsThanMaximumReturnsTrue(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function isFullForZeroSeatsAndUnlimitedMaximumReturnsFalse(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function isFullForPositiveSeatsAndUnlimitedMaximumReturnsFalse(): void
    {
        $event = $this->createPartialMock(
            Event::class,
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
    public function attachRegistrationAddsRegistration(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $this->subject->setRegistrations($registrations);

        $registration = MapperRegistry::get(RegistrationMapper::class)->getLoadedTestingModel([]);
        $this->subject->attachRegistration($registration);

        self::assertTrue(
            $this->subject->getRegistrations()->hasUid($registration->getUid())
        );
    }

    /**
     * @test
     */
    public function attachRegistrationNotRemovesExistingRegistration(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $oldRegistration = MapperRegistry::get(RegistrationMapper::class)->getNewGhost();
        $registrations->add($oldRegistration);
        $this->subject->setRegistrations($registrations);

        $newRegistration = MapperRegistry::get(RegistrationMapper::class)->getLoadedTestingModel([]);
        $this->subject->attachRegistration($newRegistration);

        self::assertTrue(
            $this->subject->getRegistrations()->hasUid($oldRegistration->getUid())
        );
    }

    /**
     * @test
     */
    public function attachRegistrationSetsEventForRegistration(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $this->subject->setRegistrations($registrations);

        $registration = MapperRegistry::get(RegistrationMapper::class)->getLoadedTestingModel([]);
        $this->subject->attachRegistration($registration);

        self::assertSame(
            $this->subject,
            $registration->getEvent()
        );
    }

    // Tests concerning the payment methods

    /**
     * @test
     */
    public function getPaymentMethodsReturnsPaymentMethods(): void
    {
        $paymentMethods = new Collection();
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
    public function setPaymentMethodsSetsPaymentMethods(): void
    {
        $this->subject->setData([]);

        /** @var Collection<PaymentMethod> $paymentMethods */
        $paymentMethods = new Collection();
        $this->subject->setPaymentMethods($paymentMethods);

        self::assertSame(
            $paymentMethods,
            $this->subject->getPaymentMethods()
        );
    }

    // Tests regarding the flag for organizers having been notified about enough attendees.

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesByDefaultReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendeesReturnsFalseValueFromDatabase(): void
    {
        $this->subject->setData(['organizers_notified_about_minimum_reached' => 1]);

        self::assertTrue(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    /**
     * @test
     */
    public function setOrganizersBeenNotifiedAboutEnoughAttendeesMarksItAsTrue(): void
    {
        $this->subject->setData([]);

        $this->subject->setOrganizersBeenNotifiedAboutEnoughAttendees();

        self::assertTrue(
            $this->subject->haveOrganizersBeenNotifiedAboutEnoughAttendees()
        );
    }

    // Tests regarding the flag for organizers having been notified about enough attendees.

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsByDefaultReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    /**
     * @test
     */
    public function shouldMuteNotificationEmailsReturnsTrueValueFromDatabase(): void
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
    public function muteNotificationEmailsSetsShouldMute(): void
    {
        $this->subject->muteNotificationEmails();

        self::assertTrue(
            $this->subject->shouldMuteNotificationEmails()
        );
    }

    // Tests regarding the flag for automatic cancelation/confirmation

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelByDefaultReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->shouldAutomaticallyConfirmOrCancel()
        );
    }

    /**
     * @test
     */
    public function shouldAutomaticallyConfirmOrCancelReturnsTrueValueFromDatabase(): void
    {
        $this->subject->setData(
            ['automatic_confirmation_cancelation' => 1]
        );

        self::assertTrue(
            $this->subject->shouldAutomaticallyConfirmOrCancel()
        );
    }

    // Tests concerning the organizers

    /**
     * @test
     */
    public function getOrganizersGetsOrganizers(): void
    {
        $organizers = new Collection();
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getOrganizers();

        self::assertSame($organizers, $result);
    }

    /**
     * @test
     */
    public function getFirstOrganizerForNoOrganizersReturnsNull(): void
    {
        $organizers = new Collection();
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getFirstOrganizer();

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function getFirstOrganizerForOneOrganizerReturnsThatOrganizer(): void
    {
        $organizer = new Organizer();
        $organizers = new Collection();
        $organizers->add($organizer);
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getFirstOrganizer();

        self::assertSame($organizer, $result);
    }

    /**
     * @test
     */
    public function getFirstOrganizerForTwoOrganizersReturnsFirstOrganizer(): void
    {
        $firstOrganizer = new Organizer();
        $organizers = new Collection();
        $organizers->add($firstOrganizer);
        $organizers->add(new Organizer());
        $this->subject->setData(['organizers' => $organizers]);

        $result = $this->subject->getFirstOrganizer();

        self::assertSame($firstOrganizer, $result);
    }

    // Tests concerning getAttendeeNames

    /**
     * @test
     */
    public function getAttendeeNamesForNoRegistrationsReturnsEmptyArray(): void
    {
        $this->subject->setData(['registrations' => new Collection()]);

        self::assertSame([], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationWithRegisteredThemselvesReturnsThatName(): void
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new FrontEndUser();
        $user->setData(['first_name' => $firstName, 'last_name' => $lastName]);

        $registration = new Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => true,
                'additional_persons' => new Collection(),
            ]
        );
        $registrations = new Collection();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([$firstName . ' ' . $lastName], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationOnlyWithoutRegisteredThemselvesReturnsEmptyArray(): void
    {
        $user = new FrontEndUser();
        $user->setData([]);

        $registration = new Registration();
        $registration->setData(
            ['user' => $user, 'registered_themselves' => false, 'additional_persons' => new Collection()]
        );
        $registrations = new Collection();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationReturnsAdditionalAttendeeNamesFromAttachedUsers(): void
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new FrontEndUser();
        $user->setData([]);

        $additionalPerson = new FrontEndUser();
        $additionalPerson->setData(['first_name' => $firstName, 'last_name' => $lastName]);
        $additionalPersons = new Collection();
        $additionalPersons->add($additionalPerson);

        $registration = new Registration();
        $registration->setData(
            ['user' => $user, 'registered_themselves' => false, 'additional_persons' => $additionalPersons]
        );
        $registrations = new Collection();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([$firstName . ' ' . $lastName], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationForAttachedUsersIgnoresFreeTextNames(): void
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new FrontEndUser();
        $user->setData([]);

        $additionalPerson = new FrontEndUser();
        $additionalPerson->setData(['first_name' => $firstName, 'last_name' => $lastName]);
        $additionalPersons = new Collection();
        $additionalPersons->add($additionalPerson);

        $registration = new Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => false,
                'additional_persons' => $additionalPersons,
                'attendees_names' => 'Jane Doe',
            ]
        );
        $registrations = new Collection();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame([$firstName . ' ' . $lastName], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesForRegistrationReturnsAdditionalAttendeeNamesFromFreeTextField(): void
    {
        $user = new FrontEndUser();
        $user->setData([]);

        $registration = new Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => false,
                'additional_persons' => new Collection(),
                'attendees_names' => "Jane Doe\r\nJohn Doe",
            ]
        );
        $registrations = new Collection();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(['Jane Doe', 'John Doe'], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesSortsNamesFromRegisteredThemselvesByFullName(): void
    {
        $registrations = new Collection();

        $firstName1 = 'Oliver';
        $lastName1 = 'Klee';
        $user1 = new FrontEndUser();
        $user1->setData(['first_name' => $firstName1, 'last_name' => $lastName1]);
        $registration1 = new Registration();
        $registration1->setData(
            [
                'user' => $user1,
                'registered_themselves' => true,
                'additional_persons' => new Collection(),
            ]
        );
        $registrations->add($registration1);

        $firstName2 = 'Jane';
        $lastName2 = 'Wolowitz';
        $user2 = new FrontEndUser();
        $user2->setData(['first_name' => $firstName2, 'last_name' => $lastName2]);
        $registration2 = new Registration();
        $registration2->setData(
            ['user' => $user2, 'registered_themselves' => true, 'additional_persons' => new Collection()]
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
    public function getAttendeeNamesSortsNamesFromAdditionalAttendeesFromUsersByFullName(): void
    {
        $user = new FrontEndUser();
        $user->setData([]);
        $additionalPersons = new Collection();

        $firstName1 = 'Oliver';
        $lastName1 = 'Klee';
        $additionalPerson1 = new FrontEndUser();
        $additionalPerson1->setData(['first_name' => $firstName1, 'last_name' => $lastName1]);
        $additionalPersons->add($additionalPerson1);

        $firstName2 = 'Jane';
        $lastName2 = 'Wolowitz';
        $additionalPerson2 = new FrontEndUser();
        $additionalPerson2->setData(['first_name' => $firstName2, 'last_name' => $lastName2]);
        $additionalPersons->add($additionalPerson2);

        $registration = new Registration();
        $registration->setData(
            ['user' => $user, 'registered_themselves' => false, 'additional_persons' => $additionalPersons]
        );
        $registrations = new Collection();
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
    public function getAttendeeNamesForRegistrationSortAdditionalAttendeeNamesFromFreeTextField(): void
    {
        $user = new FrontEndUser();
        $user->setData([]);

        $registration = new Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => false,
                'additional_persons' => new Collection(),
                'attendees_names' => "John Doe\r\nJane Doe",
            ]
        );
        $registrations = new Collection();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations]);

        self::assertSame(['Jane Doe', 'John Doe'], $this->subject->getAttendeeNames());
    }

    /**
     * @test
     */
    public function getAttendeeNamesAfterLastDigestUsesNewerRegistration(): void
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new FrontEndUser();
        $user->setData(['first_name' => $firstName, 'last_name' => $lastName]);

        $registration = new Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => true,
                'additional_persons' => new Collection(),
                'crdate' => 2,
            ]
        );
        $registrations = new Collection();
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
    public function getAttendeeNamesAfterLastDigestIgnoresOlderRegistration(): void
    {
        $firstName = 'Oliver';
        $lastName = 'Klee';

        $user = new FrontEndUser();
        $user->setData(['first_name' => $firstName, 'last_name' => $lastName]);

        $registration = new Registration();
        $registration->setData(
            [
                'user' => $user,
                'registered_themselves' => true,
                'additional_persons' => new Collection(),
                'crdate' => 1,
            ]
        );
        $registrations = new Collection();
        $registrations->add($registration);

        $this->subject->setData(['registrations' => $registrations, 'date_of_last_registration_digest' => 2]);

        self::assertSame([], $this->subject->getAttendeeNamesAfterLastDigest());
    }

    // Tests concerning "price on request"

    public function getPriceOnRequestByDefaultReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse($this->subject->getPriceOnRequest());
    }

    /**
     * @test
     */
    public function getPriceOnRequestReturnsPriceOnRequest(): void
    {
        $this->subject->setData(['price_on_request' => true]);

        self::assertTrue($this->subject->getPriceOnRequest());
    }

    // Tests regarding the date of the last registration digest email

    /**
     * @test
     */
    public function getDateOfLastRegistrationDigestEmailAsUnixTimeStampWithoutDateReturnsZero(): void
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
    public function getDateOfLastRegistrationDigestEmailAsUnixTimeStampWithPositiveDateReturnsIt(): void
    {
        $this->subject->setData(['date_of_last_registration_digest' => 42]);

        self::assertSame(42, $this->subject->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStampWithNegativeDateThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStampWithZeroDateSetsIt(): void
    {
        $this->subject->setData(['date_of_last_registration_digest' => 42]);
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(0);

        self::assertSame(0, $this->subject->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setDateOfLastRegistrationDigestEmailAsUnixTimeStampWithPositiveDateSetsIs(): void
    {
        $this->subject->setDateOfLastRegistrationDigestEmailAsUnixTimeStamp(42);

        self::assertSame(42, $this->subject->getDateOfLastRegistrationDigestEmailAsUnixTimeStamp());
    }

    // Tests concerning the dates

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampByDefaultReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(0, $this->subject->getBeginDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampReturnsBeginDate(): void
    {
        $timeStamp = 455456;
        $this->subject->setData(['begin_date' => $timeStamp]);

        self::assertSame($timeStamp, $this->subject->getBeginDateAsUnixTimeStamp());
    }
}
