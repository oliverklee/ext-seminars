<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingTimeSpan;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractTimeSpanTest extends UnitTestCase
{
    /**
     * @var TestingTimeSpan
     */
    private $subject = null;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->subject = new TestingTimeSpan();
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = TestingTimeSpan::fromData([]);

        self::assertInstanceOf(TestingTimeSpan::class, $result);
    }

    // Test for getting the begin and end date.

    /**
     * @test
     */
    public function hasDateInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->hasDate());
    }

    /**
     * @test
     */
    public function getBeginDateAsTimestampInitiallyReturnsZero()
    {
        self::assertSame(0, $this->subject->getBeginDateAsTimestamp());
    }

    /**
     * @test
     */
    public function hasBeginDateInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->hasBeginDate());
    }

    /**
     * @test
     */
    public function getEndDateAsTimestampInitiallyReturnsZero()
    {
        self::assertSame(0, $this->subject->getEndDateAsTimestamp());
    }

    /**
     * @test
     */
    public function hasEndDateInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->hasEndDate());
    }

    /**
     * @test
     */
    public function getEndDateAsTimestampEvenIfOpenEndedInitiallyReturnsZero()
    {
        self::assertSame(0, $this->subject->getEndDateAsTimestampEvenIfOpenEnded());
    }

    /**
     * @test
     */
    public function setBeginDateAndTimeSetsBeginDate()
    {
        $value = 42;
        $this->subject->setBeginDateAndTime($value);

        self::assertSame($value, $this->subject->getBeginDateAsTimestamp());
    }

    /**
     * @test
     */
    public function setBeginDateAndTimeMarksBeginDateAsSet()
    {
        $this->subject->setBeginDateAndTime(42);

        self::assertTrue($this->subject->hasBeginDate());
    }

    /**
     * @test
     */
    public function hasDateAfterSettingBeginDateReturnsTrue()
    {
        $this->subject->setBeginDateAndTime(42);

        self::assertTrue($this->subject->hasDate());
    }

    /**
     * @test
     */
    public function setEndDateAndTimeSetsEndDate()
    {
        $this->subject->setEndDateAndTime(42);

        self::assertSame(42, $this->subject->getEndDateAsTimestamp());
    }

    /**
     * @test
     */
    public function setEndDateAndTimeMarksEndDateAsSet()
    {
        $this->subject->setEndDateAndTime(42);

        self::assertTrue($this->subject->hasEndDate());
    }

    /**
     * @test
     */
    public function setEndDateAndTimeWithoutBegingDateNotMarksDateAsSet()
    {
        $this->subject->setEndDateAndTime(42);

        self::assertFalse($this->subject->hasDate());
    }

    /**
     * @test
     */
    public function getEndDateAsTimestampEvenIfOpenEndedAfterSettingEndDateOnlyReturnsZero()
    {
        $this->subject->setEndDateAndTime(42);

        self::assertSame(0, $this->subject->getEndDateAsTimestampEvenIfOpenEnded());
    }

    // Test for getting the time.

    /**
     * @test
     */
    public function hasTimeInitiallyReturnsZero()
    {
        self::assertFalse($this->subject->hasTime());
    }

    /**
     * @test
     */
    public function hasEndTimeWithEndTimeAtMidnightReturnsFalse()
    {
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        self::assertFalse($this->subject->hasEndTime());
    }

    /**
     * @test
     */
    public function hasEndTimeWithEndTimeDuringTheDayReturnsTrue()
    {
        $this->subject->setEndDateAndTime(\mktime(9, 0, 0, 1, 1, 2010));

        self::assertTrue($this->subject->hasEndTime());
    }

    // Test for open-ended events.

    /**
     * @test
     */
    public function isOpenEndedInitiallyReturnsTrue()
    {
        self::assertTrue($this->subject->isOpenEnded());
    }

    /**
     * @test
     */
    public function isOpenEndedAfterSettingOnlyTheBeginDateReturnsTrue()
    {
        $this->subject->setBeginDateAndTime(42);

        self::assertTrue($this->subject->isOpenEnded());
    }

    /**
     * @test
     */
    public function isOpenEndedAfterSettingOnlyTheEndDateToMorningReturnsFalse()
    {
        $this->subject->setEndDateAndTime(\mktime(9, 0, 0, 1, 1, 2010));

        self::assertFalse($this->subject->isOpenEnded());
    }

    /**
     * @test
     */
    public function isOpenEndedAfterSettingBeginAndEndDateToMorningReturnsFalse()
    {
        $this->subject->setBeginDateAndTime(\mktime(8, 0, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(9, 0, 0, 1, 1, 2010));

        self::assertFalse($this->subject->isOpenEnded());
    }

    /**
     * @test
     */
    public function isOpenEndedIfEndsAtMidnightReturnsFalse()
    {
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        self::assertFalse($this->subject->isOpenEnded());
    }

    // Tests for getting the end date and time for open-ended events.

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtOneOClock()
    {
        $this->subject->setBeginDateAndTime(\mktime(1, 0, 0, 1, 1, 2010));

        self::assertTrue($this->subject->isOpenEnded());
        self::assertSame(\mktime(0, 0, 0, 1, 2, 2010), $this->subject->getEndDateAsTimestampEvenIfOpenEnded());
    }

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtMorning()
    {
        $this->subject->setBeginDateAndTime(\mktime(9, 0, 0, 1, 1, 2010));

        self::assertTrue($this->subject->isOpenEnded());
        self::assertSame(\mktime(0, 0, 0, 1, 2, 2010), $this->subject->getEndDateAsTimestampEvenIfOpenEnded());
    }

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtElevenPm()
    {
        $this->subject->setBeginDateAndTime(\mktime(23, 0, 0, 1, 1, 2010));

        self::assertTrue($this->subject->isOpenEnded());
        self::assertSame(\mktime(0, 0, 0, 1, 2, 2010), $this->subject->getEndDateAsTimestampEvenIfOpenEnded());
    }

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtMidnight()
    {
        $this->subject->setBeginDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        self::assertTrue($this->subject->isOpenEnded());
        self::assertSame(\mktime(0, 0, 0, 1, 2, 2010), $this->subject->getEndDateAsTimestampEvenIfOpenEnded());
    }

    // Tests for for the begin date.

    /**
     * @test
     */
    public function hasStartedForStartedEventReturnsTrue()
    {
        $this->subject->setBeginDateAndTime($GLOBALS['SIM_EXEC_TIME'] - 42);

        self::assertTrue($this->subject->hasStarted());
    }

    /**
     * @test
     */
    public function hasStartedForUpcomingEventReturnsFalse()
    {
        $this->subject->setBeginDateAndTime($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertFalse($this->subject->hasStarted());
    }

    /**
     * @test
     */
    public function hasStartedForEventWithoutBeginDateReturnsFalse()
    {
        $this->subject->setBeginDateAndTime(0);

        self::assertFalse($this->subject->hasStarted());
    }

    // Tests concerning the places.

    /**
     * @test
     */
    public function numberOfPlacesInitiallyIsZero()
    {
        self::assertSame(0, $this->subject->getNumberOfPlaces());
    }

    /**
     * @test
     */
    public function setNumberOfPlacesSetsNumberOfPlace()
    {
        $value = 42;
        $this->subject->setNumberOfPlaces($value);

        self::assertSame($value, $this->subject->getNumberOfPlaces());
    }

    // Tests for getting the room.

    /**
     * @test
     */
    public function getRoomInitiallyReturnsEmptyString()
    {
        self::assertSame('', $this->subject->getRoom());
    }

    /**
     * @test
     */
    public function setRoomSetsRoom()
    {
        $value = 'the first test chamber';
        $this->subject->setRoom($value);

        self::assertSame($value, $this->subject->getRoom());
    }
}
