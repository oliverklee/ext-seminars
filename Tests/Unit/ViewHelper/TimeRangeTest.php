<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_ViewHelper_TimeRangeTest extends Tx_Phpunit_TestCase
{
    /**
     * some random date (2001-01-01 00:00:00)
     *
     * @var int
     */
    const BEGIN_DATE = 978303600;

    /**
     * @var string
     */
    const TIME_FORMAT = '%H:%M';

    /**
     * @var Tx_Seminars_ViewHelper_TimeRange
     */
    private $subject = null;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * @var Tx_Oelib_Translator
     */
    private $translator = null;

    /**
     * @var string
     */
    private $translatedHours = '';

    protected function setUp()
    {
        $this->testingFramework    = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->configuration = new Tx_Oelib_Configuration();
        $this->configuration->setAsString('timeFormat', self::TIME_FORMAT);

        Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

        $this->translator = Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars');
        $this->translatedHours = ' ' . $this->translator->translate('label_hours');

        $this->subject = new Tx_Seminars_ViewHelper_TimeRange();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithNoDatesReturnMessageWillBeAnnounced()
    {
        /** @var Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setData([]);

        self::assertSame(
            $this->translator->translate('message_willBeAnnounced'),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginDateWithZeroHoursReturnsMessageWillBeAnnounced()
    {
        /** @var Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);

        self::assertSame(
            $this->translator->translate('message_willBeAnnounced'),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginDateOnlyReturnsTimePortionOfBeginDate()
    {
        /** @var Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR);

        self::assertSame(
            strftime(self::TIME_FORMAT, self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithEqualBeginAndEndTimestampsReturnsOnlyTimePortionOfBeginDate()
    {
        /** @var Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR);

        self::assertSame(
            strftime(self::TIME_FORMAT, self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateReturnsTimePortionsOfBeginDateAndEndDate()
    {
        /** @var Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 2 * Tx_Oelib_Time::SECONDS_PER_HOUR);

        self::assertSame(
            strftime(self::TIME_FORMAT, self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR) . '&#8211;' .
                strftime(self::TIME_FORMAT, self::BEGIN_DATE + 2 * Tx_Oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateReturnsTimePortionsOfBeginDateAndEndDateSeparatedBySpecifiedDash()
    {
        $dash = '#DASH#';
        /** @var Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 2 * Tx_Oelib_Time::SECONDS_PER_HOUR);

        self::assertSame(
            strftime(self::TIME_FORMAT, self::BEGIN_DATE + Tx_Oelib_Time::SECONDS_PER_HOUR) . $dash .
                strftime(self::TIME_FORMAT, self::BEGIN_DATE + 2 * Tx_Oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
            $this->subject->render($timeSpan, $dash)
        );
    }
}
