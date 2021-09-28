<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\ViewHelpers;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;

final class TimeRangeViewHelperTest extends TestCase
{
    use LanguageHelper;

    /**
     * some random date (2001-01-01 00:00:00 UTC)
     *
     * @var int
     */
    const BEGIN_DATE = 978307200;

    /**
     * @var string
     */
    const TIME_FORMAT = '%H:%M';

    /**
     * @var \Tx_Seminars_ViewHelper_TimeRange
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var string
     */
    private $translatedHours = '';

    protected function setUp(): void
    {
        // Make sure that the test results do not depend on the machine's PHP time zone.
        \date_default_timezone_set('UTC');

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $configuration = new DummyConfiguration();
        $configuration->setAsString('timeFormat', self::TIME_FORMAT);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        $this->translatedHours = ' ' . $this->getLanguageService()->getLL('label_hours');

        $this->subject = new \Tx_Seminars_ViewHelper_TimeRange();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithNoDatesReturnMessageWillBeAnnounced(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setData([]);

        self::assertSame(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginDateWithZeroHoursReturnsMessageWillBeAnnounced(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);

        self::assertSame(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginDateOnlyReturnsTimePortionOfBeginDate(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Time::SECONDS_PER_HOUR);

        self::assertSame(
            \strftime(self::TIME_FORMAT, self::BEGIN_DATE + Time::SECONDS_PER_HOUR) . $this->translatedHours,
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithEqualBeginAndEndTimestampsReturnsOnlyTimePortionOfBeginDate(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Time::SECONDS_PER_HOUR);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + Time::SECONDS_PER_HOUR);

        self::assertSame(
            \strftime(self::TIME_FORMAT, self::BEGIN_DATE + Time::SECONDS_PER_HOUR) . $this->translatedHours,
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateReturnsTimePortionsOfBeginDateAndEndDate(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Time::SECONDS_PER_HOUR);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 2 * Time::SECONDS_PER_HOUR);

        self::assertSame(
            strftime(self::TIME_FORMAT, self::BEGIN_DATE + Time::SECONDS_PER_HOUR) . '&#8211;' .
            strftime(
                self::TIME_FORMAT,
                self::BEGIN_DATE + 2 * Time::SECONDS_PER_HOUR
            ) . $this->translatedHours,
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateReturnsTimePortionsOfBeginDateAndEndDateSeparatedBySpecifiedDash(): void
    {
        $dash = '#DASH#';
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + Time::SECONDS_PER_HOUR);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 2 * Time::SECONDS_PER_HOUR);

        self::assertSame(
            strftime(self::TIME_FORMAT, self::BEGIN_DATE + Time::SECONDS_PER_HOUR) . $dash .
            strftime(
                self::TIME_FORMAT,
                self::BEGIN_DATE + 2 * Time::SECONDS_PER_HOUR
            ) . $this->translatedHours,
            $this->subject->render($timeSpan, $dash)
        );
    }
}
