<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class TimeSlotTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_TimeSlot
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_TimeSlot();
    }

    ////////////////////////////////////
    // Tests regarding the entry date.
    ////////////////////////////////////

    /**
     * @test
     */
    public function getEntryDateAsUnixTimeStampWithoutEntryDateReturnsZero()
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
    public function getEntryDateAsUnixTimeStampWithEntryDateReturnsEntryDate()
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
    public function setEntryDateAsUnixTimeStampWithNegativeTimeStampThrowsException()
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
    public function setEntryDateAsUnixTimeStampWithZeroTimeStampSetsEntryDate()
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
    public function setEntryDateAsUnixTimeStampWithPositiveTimeStampSetsEntryDate()
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
    public function hasEntryDateWithoutEntryDateReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEntryDate()
        );
    }

    /**
     * @test
     */
    public function hasEntryDateWithEntryDateReturnsTrue()
    {
        $this->subject->setEntryDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasEntryDate()
        );
    }

    /*
     * Tests for the seminar association.
     */

    /**
     * @test
     */
    public function getSeminarByDefaultReturnsNull()
    {
        $this->subject->setData([]);

        self::assertNull($this->subject->getSeminar());
    }

    /**
     * @test
     */
    public function setSeminarSetsSeminar()
    {
        $seminar = new \Tx_Seminars_Model_Event();

        $this->subject->setSeminar($seminar);

        self::assertSame($seminar, $this->subject->getSeminar());
    }
}
