<?php
declare(strict_types = 1);

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_OldModel_TimespanTest extends TestCase
{
    /**
     * @var string
     */
    const TIME_FORMAT = '%H:%M';

    /**
     * @var \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingTimeSpan
     */
    private $subject = null;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $GLOBALS['LANG']->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');

        $this->subject = new \Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingTimeSpan(
            ['timeFormat' => self::TIME_FORMAT]
        );
    }

    /*
     * Test for getting the begin and end date.
     */

    /**
     * @test
     */
    public function initiallyHasNoDate()
    {
        self::assertFalse(
            $this->subject->hasDate()
        );
    }

    /**
     * @test
     */
    public function beginDateIsInitiallyZero()
    {
        self::assertSame(
            0,
            $this->subject->getBeginDateAsTimestamp()
        );
    }

    /**
     * @test
     */
    public function initiallyHasNoBeginDate()
    {
        self::assertFalse(
            $this->subject->hasBeginDate()
        );
    }

    /**
     * @test
     */
    public function endDateIsInitiallyZero()
    {
        self::assertSame(
            0,
            $this->subject->getEndDateAsTimestamp()
        );
    }

    /**
     * @test
     */
    public function initiallyHasNoEndDate()
    {
        self::assertFalse(
            $this->subject->hasEndDate()
        );
    }

    /**
     * @test
     */
    public function endDateForOpenEndedIsInitiallyZero()
    {
        self::assertSame(
            0,
            $this->subject->getEndDateAsTimestampEvenIfOpenEnded()
        );
    }

    /**
     * @test
     */
    public function setAndGetTheBeginDate()
    {
        $this->subject->setBeginDateAndTime(42);

        self::assertTrue(
            $this->subject->hasBeginDate()
        );
        self::assertSame(
            42,
            $this->subject->getBeginDateAsTimestamp()
        );
    }

    /**
     * @test
     */
    public function hasDateAfterSettingBeginDate()
    {
        $this->subject->setBeginDateAndTime(42);

        self::assertTrue(
            $this->subject->hasDate()
        );
    }

    /**
     * @test
     */
    public function setAndGetTheEndDate()
    {
        $this->subject->setEndDateAndTime(42);

        self::assertTrue(
            $this->subject->hasEndDate()
        );
        self::assertSame(
            42,
            $this->subject->getEndDateAsTimestamp()
        );
    }

    /**
     * @test
     */
    public function hasNoDateAfterSettingEndDate()
    {
        $this->subject->setEndDateAndTime(42);

        self::assertFalse(
            $this->subject->hasDate()
        );
    }

    /**
     * @test
     */
    public function endDateForOpenEndedIsZeroIfNoBeginDate()
    {
        $this->subject->setEndDateAndTime(42);

        self::assertSame(
            0,
            $this->subject->getEndDateAsTimestampEvenIfOpenEnded()
        );
    }

    /*
     * Test for getting the time.
     */

    /**
     * @test
     */
    public function initiallyHasNoTime()
    {
        self::assertFalse(
            $this->subject->hasTime()
        );
    }

    /**
     * @test
     */
    public function hasNoEndTimeIfEndsAtMidnight()
    {
        $this->subject->setEndDateAndTime(mktime(0, 0, 0, 1, 1, 2010));

        self::assertFalse(
            $this->subject->hasEndTime()
        );
    }

    /**
     * @test
     */
    public function hasEndTimeIfEndsDuringTheDay()
    {
        $this->subject->setEndDateAndTime(mktime(9, 0, 0, 1, 1, 2010));

        self::assertTrue(
            $this->subject->hasEndTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForNoTimeReturnsWillBeAnnouncesMessage()
    {
        self::assertSame(
            $this->subject->translate('message_willBeAnnounced'),
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeOnlyReturnsBeginTime()
    {
        $this->subject->setBeginDateAndTime(mktime(9, 50, 0, 1, 1, 2010));

        self::assertSame(
            '09:50' . ' ' . $this->subject->translate('label_hours'),
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnSameDayReturnsBothTimesWithMDashByDefault()
    {
        $this->subject->setBeginDateAndTime(mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(mktime(18, 30, 0, 1, 1, 2010));

        self::assertSame(
            '09:50&#8211;18:30' . ' ' . $this->subject->translate('label_hours'),
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnSameDayReturnsBothTimesWithProvidedDash()
    {
        $this->subject->setBeginDateAndTime(mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(mktime(18, 30, 0, 1, 1, 2010));

        self::assertSame(
            '09:50-18:30' . ' ' . $this->subject->translate('label_hours'),
            $this->subject->getTime('-')
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnDifferentDaysReturnsBothTimesWithMDashByDefault()
    {
        $this->subject->setBeginDateAndTime(mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(mktime(18, 30, 0, 1, 2, 2010));

        self::assertSame(
            '09:50&#8211;18:30' . ' ' . $this->subject->translate('label_hours'),
            $this->subject->getTime()
        );
    }

    /*
     * Test for open-endedness.
     */

    /**
     * @test
     */
    public function initiallyIsOpenEnded()
    {
        self::assertTrue(
            $this->subject->isOpenEnded()
        );
    }

    /**
     * @test
     */
    public function isOpenEndedAfterSettingOnlyTheBeginDate()
    {
        $this->subject->setBeginDateAndTime(42);

        self::assertTrue(
            $this->subject->isOpenEnded()
        );
    }

    /**
     * @test
     */
    public function isNotOpenEndedAfterSettingOnlyTheEndDateToMorning()
    {
        $this->subject->setEndDateAndTime(mktime(9, 0, 0, 1, 1, 2010));

        self::assertFalse(
            $this->subject->isOpenEnded()
        );
    }

    /**
     * @test
     */
    public function isNotOpenEndedAfterSettingBeginAndEndDateToMorning()
    {
        $this->subject->setBeginDateAndTime(mktime(8, 0, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(mktime(9, 0, 0, 1, 1, 2010));

        self::assertFalse(
            $this->subject->isOpenEnded()
        );
    }

    /**
     * @test
     */
    public function isNotOpenEndedIfEndsAtMidnight()
    {
        $this->subject->setEndDateAndTime(mktime(0, 0, 0, 1, 1, 2010));

        self::assertFalse(
            $this->subject->isOpenEnded()
        );
    }

    /*
     * Tests for getting the end date and time for open-ended events.
     */

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtOneOClock()
    {
        $this->subject->setBeginDateAndTime(mktime(1, 0, 0, 1, 1, 2010));

        self::assertTrue(
            $this->subject->isOpenEnded()
        );
        self::assertSame(
            mktime(0, 0, 0, 1, 2, 2010),
            $this->subject->getEndDateAsTimestampEvenIfOpenEnded()
        );
    }

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtMorning()
    {
        $this->subject->setBeginDateAndTime(mktime(9, 0, 0, 1, 1, 2010));

        self::assertTrue(
            $this->subject->isOpenEnded()
        );
        self::assertSame(
            mktime(0, 0, 0, 1, 2, 2010),
            $this->subject->getEndDateAsTimestampEvenIfOpenEnded()
        );
    }

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtElevenPm()
    {
        $this->subject->setBeginDateAndTime(mktime(23, 0, 0, 1, 1, 2010));

        self::assertTrue(
            $this->subject->isOpenEnded()
        );
        self::assertSame(
            mktime(0, 0, 0, 1, 2, 2010),
            $this->subject->getEndDateAsTimestampEvenIfOpenEnded()
        );
    }

    /**
     * @test
     */
    public function endDateIsMidnightIfOpenEndedStartsAtMidnight()
    {
        $this->subject->setBeginDateAndTime(mktime(0, 0, 0, 1, 1, 2010));

        self::assertTrue(
            $this->subject->isOpenEnded()
        );
        self::assertSame(
            mktime(0, 0, 0, 1, 2, 2010),
            $this->subject->getEndDateAsTimestampEvenIfOpenEnded()
        );
    }

    /*
     * Tests for for the begin date.
     */

    /**
     * @test
     */
    public function hasStartedReturnsTrueForStartedEvent()
    {
        $this->subject->setBeginDateAndTime(42);

        self::assertTrue(
            $this->subject->hasStarted()
        );
    }

    /**
     * @test
     */
    public function hasStartedReturnsFalseForUpcomingEvent()
    {
        $this->subject->setBeginDateAndTime($GLOBALS['SIM_EXEC_TIME'] + 42);

        self::assertFalse(
            $this->subject->hasStarted()
        );
    }

    /**
     * @test
     */
    public function hasStartedReturnsFalseForEventWithoutBeginDate()
    {
        $this->subject->setBeginDateAndTime(0);

        self::assertFalse(
            $this->subject->hasStarted()
        );
    }

    /*
     * Tests concerning the places.
     */

    /**
     * @test
     */
    public function numberOfPlacesIsInitiallyZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfPlaces()
        );
    }

    /**
     * @test
     */
    public function setNumberOfPlacesToZero()
    {
        $this->subject->setNumberOfPlaces(0);

        self::assertSame(
            0,
            $this->subject->getNumberOfPlaces()
        );
    }

    /**
     * @test
     */
    public function setNumberOfPlacesToPositiveInteger()
    {
        $this->subject->setNumberOfPlaces(42);

        self::assertSame(
            42,
            $this->subject->getNumberOfPlaces()
        );
    }

    /*
     * Tests for getting the room.
     */

    /**
     * @test
     */
    public function roomIsInitiallyEmpty()
    {
        self::assertSame(
            '',
            $this->subject->getRoom()
        );
    }

    /**
     * @test
     */
    public function setAndGetRoom()
    {
        $this->subject->setRoom('foo');

        self::assertSame(
            'foo',
            $this->subject->getRoom()
        );
    }
}
