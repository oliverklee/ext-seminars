<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Hooks\Interfaces\DateTimeSpan;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingTimeSpan;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractTimeSpanTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var string
     */
    const TIME_FORMAT = '%H:%M';

    /**
     * @var string
     */
    const DATE_FORMAT_YMD = '%d.%m.%Y';

    /**
     * @var string
     */
    const DATE_FORMAT_MD = '%d.%m.';

    /**
     * @var string
     */
    const DATE_FORMAT_D = '%d.';

    /**
     * @var string
     */
    const DATE_FORMAT_M = '%m';

    /**
     * @var string
     */
    const DATE_FORMAT_Y = '%Y';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var TestingTimeSpan
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->initializeBackEndLanguage();

        $this->subject = new TestingTimeSpan();
        $this->subject->overrideConfiguration([
            'timeFormat' => self::TIME_FORMAT,
            'dateFormatYMD' => self::DATE_FORMAT_YMD,
            'dateFormatMD' => self::DATE_FORMAT_MD,
            'dateFormatD' => self::DATE_FORMAT_D,
            'dateFormatM' => self::DATE_FORMAT_M,
            'dateFormatY' => self::DATE_FORMAT_Y,
            'abbreviateDateRanges' => 1,
        ]);
    }

    // Test for getting the time.

    /**
     * @test
     */
    public function getTimeForNoTimeReturnsWillBeAnnouncesMessage()
    {
        self::assertSame(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeOnlyReturnsBeginTime()
    {
        $this->subject->setBeginDateAndTime(\mktime(9, 50, 0, 1, 1, 2010));

        self::assertSame(
            '09:50' . ' ' . $this->getLanguageService()->getLL('label_hours'),
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnSameDayReturnsBothTimesWithMDashByDefault()
    {
        $this->subject->setBeginDateAndTime(\mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(18, 30, 0, 1, 1, 2010));

        self::assertSame(
            '09:50&#8211;18:30' . ' ' . $this->getLanguageService()->getLL('label_hours'),
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnSameDayReturnsBothTimesWithProvidedDash()
    {
        $this->subject->setBeginDateAndTime(\mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(18, 30, 0, 1, 1, 2010));

        self::assertSame(
            '09:50-18:30' . ' ' . $this->getLanguageService()->getLL('label_hours'),
            $this->subject->getTime('-')
        );
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnDifferentDaysReturnsBothTimesWithMDashByDefault()
    {
        $this->subject->setBeginDateAndTime(\mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(18, 30, 0, 1, 2, 2010));

        self::assertSame(
            '09:50&#8211;18:30' . ' ' . $this->getLanguageService()->getLL('label_hours'),
            $this->subject->getTime()
        );
    }

    /**
     * @test
     */
    public function getTimeForNoTimeNotCallsHook()
    {
        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getTime();
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeOnlyNotCallsHook()
    {
        $this->subject->setBeginDateAndTime(\mktime(9, 50, 0, 1, 1, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getTime();
    }

    /**
     * @test
     */
    public function getTimeForEndTimeOnlyNotCallsHook()
    {
        $this->subject->setEndDateAndTime(\mktime(18, 30, 0, 1, 1, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getTime();
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnSameTimeNotCallsHook()
    {
        $this->subject->setBeginDateAndTime(\mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(9, 50, 0, 1, 3, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getTime();
    }

    /**
     * @test
     */
    public function getTimeForBeginTimeAndEndTimeOnDifferentTimesCallsHookAndUsesModifiedValue()
    {
        $modifiedValue = 'modified';

        $this->subject->setBeginDateAndTime(\mktime(9, 50, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(18, 30, 0, 1, 1, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::once())->method('modifyTimeSpan')
            ->with('09:50&#8211;18:30', $this->subject, '&#8211;')
            ->willReturn($modifiedValue);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        self::assertSame(
            $modifiedValue . ' ' . $this->getLanguageService()->getLL('label_hours'),
            $this->subject->getTime()
        );
    }

    // Test for getting the date.

    /**
     * @test
     */
    public function getDateForNoDateReturnsWillBeAnnouncedMessage()
    {
        self::assertSame(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getDate()
        );
    }

    /**
     * @test
     */
    public function getDateForBeginDateReturnsBeginDate()
    {
        $this->subject->setBeginDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        self::assertSame(
            '01.01.2010',
            $this->subject->getDate()
        );
    }

    /**
     * @test
     */
    public function getDateForEndDateReturnsWillBeAnnouncedMessage()
    {
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        self::assertSame(
            $this->getLanguageService()->getLL('message_willBeAnnounced'),
            $this->subject->getDate()
        );
    }

    /**
     * @test
     */
    public function getDateForBeginDateAndEndDateOnSameDayReturnsBeginDate()
    {
        $this->subject->setBeginDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        self::assertSame(
            '01.01.2010',
            $this->subject->getDate()
        );
    }

    /**
     * @test
     */
    public function getDateForBeginDateAndEndDateOnDifferentDaysReturnsDateSpan()
    {
        $this->subject->setBeginDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 3, 2010));

        self::assertSame(
            '01.&#8211;03.01.2010',
            $this->subject->getDate()
        );
    }

    /**
     * @test
     */
    public function getDateForNoDateNotCallsHook()
    {
        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getDate();
    }

    /**
     * @test
     */
    public function getDateForBeginDateNotCallsHook()
    {
        $this->subject->setBeginDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getDate();
    }

    /**
     * @test
     */
    public function getDateForEndDateNotCallsHook()
    {
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getDate();
    }

    /**
     * @test
     */
    public function getDateForBeginDateAndEndDateOnSameDayNotCallsHook()
    {
        $this->subject->setBeginDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::never())->method('modifyDateSpan');
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->getDate();
    }

    /**
     * @test
     */
    public function getDateForBeginDateAndEndDateOnDifferentDaysCallsHookAndUsesModifiedValue()
    {
        $modifiedValue = 'modified';

        $this->subject->setBeginDateAndTime(\mktime(0, 0, 0, 1, 1, 2010));
        $this->subject->setEndDateAndTime(\mktime(0, 0, 0, 1, 3, 2010));

        $hook = $this->createMock(DateTimeSpan::class);
        $hook->expects(self::once())->method('modifyDateSpan')
            ->with('01.&#8211;03.01.2010', $this->subject, '&#8211;')
            ->willReturn($modifiedValue);
        $hook->expects(self::never())->method('modifyTimeSpan');

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][DateTimeSpan::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        self::assertSame($modifiedValue, $this->subject->getDate());
    }
}
