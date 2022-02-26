<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\ViewHelpers;

use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\ViewHelpers\CountdownViewHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\ViewHelpers\CountdownViewHelper
 */
final class CountdownViewHelperTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var CountdownViewHelper
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $this->subject = new CountdownViewHelper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();
    }

    /**
     * @test
     */
    public function renderWithBeginDateInThirtySecondsReturnsThirtySecondsLeft(): void
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
    public function renderWithBeginDateInOneMinuteReturnsOneMinuteLeft(): void
    {
        $offset = 60;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / Time::SECONDS_PER_MINUTE,
                $this->getLanguageService()->getLL('countdown_minutes_singular')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoMinutesReturnsTwoMinutesLeft(): void
    {
        $offset = 120;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / Time::SECONDS_PER_MINUTE,
                $this->getLanguageService()->getLL('countdown_minutes_plural')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneHourReturnsOneHourLeft(): void
    {
        $offset = 3600;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / Time::SECONDS_PER_HOUR,
                $this->getLanguageService()->getLL('countdown_hours_singular')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoHoursReturnsTwoHoursLeft(): void
    {
        $offset = 7200;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / Time::SECONDS_PER_HOUR,
                $this->getLanguageService()->getLL('countdown_hours_plural')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneDayReturnsOneDayLeft(): void
    {
        $offset = 86400;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / Time::SECONDS_PER_DAY,
                $this->getLanguageService()->getLL('countdown_days_singular')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoDaysReturnsTwoDaysLeft(): void
    {
        $offset = 2 * 86400;

        self::assertSame(
            \sprintf(
                $this->getLanguageService()->getLL('message_countdown'),
                $offset / Time::SECONDS_PER_DAY,
                $this->getLanguageService()->getLL('countdown_days_plural')
            ),
            $this->subject->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }
}
