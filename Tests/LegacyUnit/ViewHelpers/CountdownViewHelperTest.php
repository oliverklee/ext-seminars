<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\ViewHelpers;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CountdownViewHelperTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Seminars_ViewHelper_Countdown
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $this->subject = new \Tx_Seminars_ViewHelper_Countdown();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
    }

    /**
     * @test
     */
    public function renderWithBeginDateInThirtySecondsReturnsThirtySecondsLeft()
    {
        $offset = 30;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset,
                $this->getLanguageService()->getLL('countdown_seconds_plural')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneMinuteReturnsOneMinuteLeft()
    {
        $offset = 60;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / \Tx_Oelib_Time::SECONDS_PER_MINUTE,
                $this->getLanguageService()->getLL('countdown_minutes_singular')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoMinutesReturnsTwoMinutesLeft()
    {
        $offset = 120;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / \Tx_Oelib_Time::SECONDS_PER_MINUTE,
                $this->getLanguageService()->getLL('countdown_minutes_plural')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneHourReturnsOneHourLeft()
    {
        $offset = 3600;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / \Tx_Oelib_Time::SECONDS_PER_HOUR,
                $this->getLanguageService()->getLL('countdown_hours_singular')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoHoursReturnsTwoHoursLeft()
    {
        $offset = 7200;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / \Tx_Oelib_Time::SECONDS_PER_HOUR,
                $this->getLanguageService()->getLL('countdown_hours_plural')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneDayReturnsOneDayLeft()
    {
        $offset = 86400;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / \Tx_Oelib_Time::SECONDS_PER_DAY,
                $this->getLanguageService()->getLL('countdown_days_singular')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoDaysReturnsTwoDaysLeft()
    {
        $offset = 2 * 86400;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / \Tx_Oelib_Time::SECONDS_PER_DAY,
                $this->getLanguageService()->getLL('countdown_days_plural')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }
}
