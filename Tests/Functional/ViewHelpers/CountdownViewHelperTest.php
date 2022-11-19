<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\ViewHelpers;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\ViewHelpers\CountdownViewHelper;

/**
 * @covers \OliverKlee\Seminars\ViewHelpers\CountdownViewHelper
 */
final class CountdownViewHelperTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var positive-int
     */
    private const NOW = 1645975847;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var CountdownViewHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/SinglePage.xml');
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd(1);
        $this->initializeBackEndLanguage();

        $GLOBALS['SIM_ACCESS_TIME'] = self::NOW;

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

        $result = $this->subject->render(self::NOW + $offset);

        $expected = \sprintf(
            $this->translate('message_countdown'),
            $offset,
            $this->translate('countdown_seconds_plural')
        );
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneMinuteReturnsOneMinuteLeft(): void
    {
        $offset = Time::SECONDS_PER_MINUTE;

        $result = $this->subject->render(self::NOW + $offset);

        $expected = \sprintf(
            $this->translate('message_countdown'),
            1,
            $this->translate('countdown_minutes_singular')
        );
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoMinutesReturnsTwoMinutesLeft(): void
    {
        $offset = 2 * Time::SECONDS_PER_MINUTE;

        $result = $this->subject->render(self::NOW + $offset);

        $expected = \sprintf(
            $this->translate('message_countdown'),
            2,
            $this->translate('countdown_minutes_plural')
        );
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneHourReturnsOneHourLeft(): void
    {
        $offset = Time::SECONDS_PER_HOUR;

        $result = $this->subject->render(self::NOW + $offset);

        $expected = \sprintf(
            $this->translate('message_countdown'),
            1,
            $this->translate('countdown_hours_singular')
        );
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoHoursReturnsTwoHoursLeft(): void
    {
        $offset = 2 * Time::SECONDS_PER_HOUR;

        $result = $this->subject->render(self::NOW + $offset);

        $expected = \sprintf(
            $this->translate('message_countdown'),
            $offset / Time::SECONDS_PER_HOUR,
            $this->translate('countdown_hours_plural')
        );
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function renderWithBeginDateInOneDayReturnsOneDayLeft(): void
    {
        $result = $this->subject->render(self::NOW + Time::SECONDS_PER_DAY);

        $expected = \sprintf(
            $this->translate('message_countdown'),
            1,
            $this->translate('countdown_days_singular')
        );
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function renderWithBeginDateInTwoDaysReturnsTwoDaysLeft(): void
    {
        $offset = 2 * Time::SECONDS_PER_DAY;

        $result = $this->subject->render(self::NOW + $offset);

        $expected = \sprintf(
            $this->translate('message_countdown'),
            2,
            $this->translate('countdown_days_plural')
        );
        self::assertSame($expected, $result);
    }
}
