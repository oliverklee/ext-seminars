<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\TimeSlot;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\AbstractTimeSpan
 * @covers \OliverKlee\Seminars\Model\TimeSlot
 */
final class TimeSlotTest extends TestCase
{
    /**
     * @var TimeSlot
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new TimeSlot();
    }

    ////////////////////////////////////
    // Tests regarding the entry date.
    ////////////////////////////////////

    /**
     * @test
     */
    public function getEntryDateAsUnixTimeStampWithoutEntryDateReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getEntryDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getEntryDateAsUnixTimeStampWithEntryDateReturnsEntryDate(): void
    {
        $this->subject->setData(['entry_date' => 42]);

        self::assertEquals(
            42,
            $this->subject->getEntryDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEntryDateAsUnixTimeStampWithNegativeTimeStampThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $entryDate must be >= 0.'
        );

        $this->subject->setEntryDateAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setEntryDateAsUnixTimeStampWithZeroTimeStampSetsEntryDate(): void
    {
        $this->subject->setEntryDateAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->subject->getEntryDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEntryDateAsUnixTimeStampWithPositiveTimeStampSetsEntryDate(): void
    {
        $this->subject->setEntryDateAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->subject->getEntryDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasEntryDateWithoutEntryDateReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEntryDate()
        );
    }

    /**
     * @test
     */
    public function hasEntryDateWithEntryDateReturnsTrue(): void
    {
        $this->subject->setEntryDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasEntryDate()
        );
    }

    // Tests for the seminar association.

    /**
     * @test
     */
    public function getSeminarByDefaultReturnsNull(): void
    {
        $this->subject->setData([]);

        self::assertNull($this->subject->getSeminar());
    }

    /**
     * @test
     */
    public function setSeminarSetsSeminar(): void
    {
        $seminar = new Event();

        $this->subject->setSeminar($seminar);

        self::assertSame($seminar, $this->subject->getSeminar());
    }
}
