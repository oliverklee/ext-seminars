<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use OliverKlee\Seminars\Model\AbstractTimeSpan;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\AbstractTimeSpan
 */
final class AbstractTimeSpanTest extends UnitTestCase
{
    /**
     * @var AbstractTimeSpan&MockObject
     */
    private AbstractTimeSpan $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getMockForAbstractClass(AbstractTimeSpan::class);
    }

    // Tests regarding the begin date.

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampWithoutBeginDateReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getBeginDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampWithBeginDateReturnsBeginDate(): void
    {
        $this->subject->setData(['begin_date' => 42]);

        self::assertSame(
            42,
            $this->subject->getBeginDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithNegativeTimeStampThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class,
        );
        $this->expectExceptionMessage(
            'The parameter $beginDate must be >= 0.',
        );

        $this->subject->setBeginDateAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithZeroTimeStampSetsBeginDate(): void
    {
        $this->subject->setBeginDateAsUnixTimeStamp(0);

        self::assertSame(
            0,
            $this->subject->getBeginDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithPositiveTimeStampSetsBeginDate(): void
    {
        $this->subject->setBeginDateAsUnixTimeStamp(42);

        self::assertSame(
            42,
            $this->subject->getBeginDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function hasBeginDateWithoutBeginDateReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasBeginDate(),
        );
    }

    /**
     * @test
     */
    public function hasBeginDateWithBeginDateReturnsTrue(): void
    {
        $this->subject->setBeginDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasBeginDate(),
        );
    }

    // Tests regarding the end date.

    /**
     * @test
     */
    public function getEndDateAsUnixTimeStampWithoutEndDateReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getEndDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function getEndDateAsUnixTimeStampWithEndDateReturnsEndDate(): void
    {
        $this->subject->setData(['end_date' => 42]);

        self::assertSame(
            42,
            $this->subject->getEndDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithNegativeTimeStampThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class,
        );
        $this->expectExceptionMessage(
            'The parameter $endDate must be >= 0.',
        );

        $this->subject->setEndDateAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithZeroTimeStampSetsEndDate(): void
    {
        $this->subject->setEndDateAsUnixTimeStamp(0);

        self::assertSame(
            0,
            $this->subject->getEndDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithPositiveTimeStampSetsEndDate(): void
    {
        $this->subject->setEndDateAsUnixTimeStamp(42);

        self::assertSame(
            42,
            $this->subject->getEndDateAsUnixTimeStamp(),
        );
    }

    /**
     * @test
     */
    public function hasEndDateWithoutEndDateReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEndDate(),
        );
    }

    /**
     * @test
     */
    public function hasEndDateWithEndDateReturnsTrue(): void
    {
        $this->subject->setEndDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasEndDate(),
        );
    }
}
