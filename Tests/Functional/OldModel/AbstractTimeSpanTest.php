<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\OldModel;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingTimeSpan;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;

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
        $this->subject->overrideConfiguration(['timeFormat' => self::TIME_FORMAT]);
    }

    /*
     * Test for getting the time.
     */

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
}
