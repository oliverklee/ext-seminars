<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_ViewHelper_CountdownTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_ViewHelper_Countdown
     */
    private $fixture;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Oelib_Translator
     */
    private $translator;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->translator = \Tx_Oelib_TranslatorRegistry::get('seminars');

        $this->fixture = new \Tx_Seminars_ViewHelper_Countdown();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function renderWithBeginDateInThirtySecondsReturnsThirtySecondsLeft()
    {
        $offset = 30;

        self::assertSame(
            sprintf(
                $this->translator->translate('message_countdown'),
                $offset,
                $this->translator->translate('countdown_seconds_plural')
            ),
            $this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneMinuteReturnsOneMinuteLeft()
    {
        $offset = 60;

        self::assertSame(
            sprintf(
                $this->translator->translate('message_countdown'),
                $offset,
                $this->translator->translate('countdown_minutes_singular')
            ),
            $this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoMinutesReturnsTwoMinutesLeft()
    {
        $offset = 120;

        self::assertSame(
            sprintf(
                $this->translator->translate('message_countdown'),
                $offset,
                $this->translator->translate('countdown_minutes_plural')
            ),
            $this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneHourReturnsOneHourLeft()
    {
        $offset = 3600;

        self::assertSame(
            sprintf(
                $this->translator->translate('message_countdown'),
                $offset,
                $this->translator->translate('countdown_hours_singular')
            ),
            $this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoHoursReturnsTwoHoursLeft()
    {
        $offset = 7200;

        self::assertSame(
            sprintf(
                $this->translator->translate('message_countdown'),
                $offset,
                $this->translator->translate('countdown_hours_plural')
            ),
            $this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneDayReturnsOneDayLeft()
    {
        $offset = 86400;

        self::assertSame(
            sprintf(
                $this->translator->translate('message_countdown'),
                $offset,
                $this->translator->translate('countdown_days_singular')
            ),
            $this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoDaysReturnsTwoDaysLeft()
    {
        $offset = 2 * 86400;

        self::assertSame(
            sprintf(
                $this->translator->translate('message_countdown'),
                $offset,
                $this->translator->translate('countdown_days_plural')
            ),
            $this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
        );
    }
}
