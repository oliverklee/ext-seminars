<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\ViewHelpers;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Model\AbstractTimeSpan;
use OliverKlee\Seminars\ViewHelpers\DateRangeViewHelper;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\ViewHelpers\DateRangeViewHelper
 */
final class DateRangeViewHelperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var positive-int some random date (2001-01-01 00:00:00 UTC)
     */
    private const BEGIN_DATE = 978307200;

    private DateRangeViewHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/AdminBackEndUser.csv');
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
            ->createFromUserPreferences($this->setUpBackendUser(1));

        // Make sure that the test results do not depend on the machine's PHP time zone.
        \date_default_timezone_set('UTC');

        $configuration = new DummyConfiguration(['dateFormatYMD' => '%d.%m.%Y']);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);

        $this->subject = new DateRangeViewHelper();
    }

    /**
     * @test
     */
    public function renderWithNoDatesReturnsWillBeAnnounced(): void
    {
        $timeSpan = $this->getMockForAbstractClass(AbstractTimeSpan::class);
        $timeSpan->setData([]);

        self::assertSame(
            LocalizationUtility::translate('message_willBeAnnounced', 'seminars'),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginDateOnlyRendersOnlyBeginDate(): void
    {
        $timeSpan = $this->getMockForAbstractClass(AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);

        self::assertSame(
            \date('d.m.Y', self::BEGIN_DATE),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithEqualBeginAndEndDateReturnsOnlyBeginDate(): void
    {
        $timeSpan = $this->getMockForAbstractClass(AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE);

        self::assertSame(
            \date('d.m.Y', self::BEGIN_DATE),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginAndEndDateOnSameDayReturnsOnlyBeginDate(): void
    {
        $timeSpan = $this->getMockForAbstractClass(AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 3600);

        self::assertSame(
            \date('d.m.Y', self::BEGIN_DATE),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginAndEndDateOnDifferentDaysReturnsBothFullDatesSeparatedByDash(): void
    {
        $timeSpan = $this->getMockForAbstractClass(AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $endDate = self::BEGIN_DATE + (2 * 86400);
        $timeSpan->setEndDateAsUnixTimeStamp($endDate);

        self::assertSame(
            \date('d.m.Y', self::BEGIN_DATE) . '&#8211;' . \date('d.m.Y', $endDate),
            $this->subject->render($timeSpan)
        );
    }

    /**
     * @test
     */
    public function renderWithBeginAndEndDateOnDifferentDaysReturnsBothFullDatesSeparatedBySpecifiedDash(): void
    {
        $dash = '#DASH#';

        $timeSpan = $this->getMockForAbstractClass(AbstractTimeSpan::class);
        $timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
        $endDate = self::BEGIN_DATE + (2 * 86400);
        $timeSpan->setEndDateAsUnixTimeStamp($endDate);

        self::assertSame(
            \date('d.m.Y', self::BEGIN_DATE) . $dash . \date('d.m.Y', $endDate),
            $this->subject->render($timeSpan, $dash)
        );
    }
}
