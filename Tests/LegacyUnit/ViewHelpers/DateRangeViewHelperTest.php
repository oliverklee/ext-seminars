<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\ViewHelpers;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;

final class DateRangeViewHelperTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Seminars_ViewHelper_DateRange
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * @var int some random date (2001-01-01 00:00:00 UTC)
     */
    private const BEGIN_DATE = 978307200;

    /**
     * @var string
     */
    private const DATE_FORMAT_YMD = '%d.%m.%Y';

    /**
     * @var string
     */
    private const DATE_FORMAT_Y = '%Y';

    /**
     * @var string
     */
    private const DATE_FORMAT_M = '%m.';

    /**
     * @var string
     */
    private const DATE_FORMAT_MD = '%d.%m.';

    /**
     * @var string
     */
    private const DATE_FORMAT_D = '%d.';

    protected function setUp(): void
    {
        // Make sure that the test results do not depend on the machine's PHP time zone.
        \date_default_timezone_set('UTC');

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $this->configuration = new DummyConfiguration();
        $this->configuration->setAsString('dateFormatYMD', self::DATE_FORMAT_YMD);
        $this->configuration->setAsString('dateFormatY', self::DATE_FORMAT_Y);
        $this->configuration->setAsString('dateFormatM', self::DATE_FORMAT_M);
        $this->configuration->setAsString('dateFormatMD', self::DATE_FORMAT_MD);
        $this->configuration->setAsString('dateFormatD', self::DATE_FORMAT_D);

        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

        $this->subject = new \Tx_Seminars_ViewHelper_DateRange();
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
    public function renderWithTimeSpanWithBeginDateOnlyRendersOnlyBeginDate(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);

        self::assertSame(
            strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithEqualBeginAndEndDateReturnsOnlyBeginDate(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE);

        self::assertSame(
            strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateOnSameDayReturnsOnlyBeginDate(): void
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 3600);

        self::assertEquals(
            strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateOnDifferentDaysWithAbbreviateDateRangeFalseReturnsBothFullDatesSeparatedByDash(): void
    {
        $this->configuration->setAsBoolean('abbreviateDateRanges', false);

        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $endDate = self::BEGIN_DATE + (2 * 86400);
        $timeSpan->setEndDateAsUnixTimeStamp($endDate);

        self::assertEquals(
            strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateOnDifferentDaysButSameMonthWithAbbreviateDateRangeTrueReturnsOnlyDayOfBeginDateAndFullEndDateSeparatedByDash(): void
    {
        $this->configuration->setAsBoolean('abbreviateDateRanges', true);

        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $endDate = self::BEGIN_DATE + (2 * 86400);
        $timeSpan->setEndDateAsUnixTimeStamp($endDate);

        self::assertEquals(
            strftime(self::DATE_FORMAT_D, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateOnDifferentMonthWithAbbreviateDateRangeTrueReturnsDayAndMonthOfBeginDateAndFullEndDateSeparatedByDash(): void
    {
        $this->configuration->setAsBoolean('abbreviateDateRanges', true);

        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $endDate = self::BEGIN_DATE + (32 * 86400);
        $timeSpan->setEndDateAsUnixTimeStamp($endDate);

        self::assertEquals(
            strftime(self::DATE_FORMAT_MD, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateOnDifferentYearsWithAbbreviateDateRangeTrueReturnsFullBeginDateAndFullEndDateSeparatedByDash(): void
    {
        $this->configuration->setAsBoolean('abbreviateDateRanges', true);

        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $endDate = self::BEGIN_DATE + (366 * 86400);
        $timeSpan->setEndDateAsUnixTimeStamp($endDate);

        self::assertEquals(
            strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithTimeSpanWithBeginAndEndDateOnDifferentDaysWithAbbreviateDateRangeFalseReturnsBothFullDatesSeparatedBySpecifiedDash(): void
    {
        $this->configuration->setAsBoolean('abbreviateDateRanges', false);
        $dash = '#DASH#';

        /** @var \Tx_Seminars_Model_AbstractTimeSpan $timeSpan */
        $timeSpan = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $endDate = self::BEGIN_DATE + (2 * 86400);
        $timeSpan->setEndDateAsUnixTimeStamp($endDate);

        self::assertEquals(
            strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE) . $dash . strftime(self::DATE_FORMAT_YMD, $endDate),
            $this->subject->render($timeSpan, $dash)
        );
    }
}
