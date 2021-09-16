<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractTimeSpanTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_AbstractTimeSpan&MockObject
     */
    private $subject;

    protected function setUp()
    {
        /** @var \Tx_Seminars_Model_AbstractTimeSpan&MockObject $subject */
        $subject = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
        $this->subject = $subject;
    }

    // Tests regarding the begin date.

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampWithoutBeginDateReturnsZero()
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampWithBeginDateReturnsBeginDate()
    {
        $this->subject->setData(['begin_date' => 42]);

        self::assertEquals(
            42,
            $this->subject->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithNegativeTimeStampThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $beginDate must be >= 0.'
        );

        $this->subject->setBeginDateAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithZeroTimeStampSetsBeginDate()
    {
        $this->subject->setBeginDateAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->subject->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithPositiveTimeStampSetsBeginDate()
    {
        $this->subject->setBeginDateAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->subject->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasBeginDateWithoutBeginDateReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasBeginDate()
        );
    }

    /**
     * @test
     */
    public function hasBeginDateWithBeginDateReturnsTrue()
    {
        $this->subject->setBeginDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasBeginDate()
        );
    }

    // Tests regarding the end date.

    /**
     * @test
     */
    public function getEndDateAsUnixTimeStampWithoutEndDateReturnsZero()
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getEndDateAsUnixTimeStampWithEndDateReturnsEndDate()
    {
        $this->subject->setData(['end_date' => 42]);

        self::assertEquals(
            42,
            $this->subject->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithNegativeTimeStampThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $endDate must be >= 0.'
        );

        $this->subject->setEndDateAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithZeroTimeStampSetsEndDate()
    {
        $this->subject->setEndDateAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->subject->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithPositiveTimeStampSetsEndDate()
    {
        $this->subject->setEndDateAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->subject->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasEndDateWithoutEndDateReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEndDate()
        );
    }

    /**
     * @test
     */
    public function hasEndDateWithEndDateReturnsTrue()
    {
        $this->subject->setEndDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->subject->hasEndDate()
        );
    }

    // Tests regarding the room.

    /**
     * @test
     */
    public function getRoomWithoutRoomReturnsAnEmptyString()
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getRoom()
        );
    }

    /**
     * @test
     */
    public function getRoomWithRoomReturnsRoom()
    {
        $this->subject->setData(['room' => 'cuby']);

        self::assertEquals(
            'cuby',
            $this->subject->getRoom()
        );
    }

    /**
     * @test
     */
    public function setRoomSetsRoom()
    {
        $this->subject->setRoom('cuby');

        self::assertEquals(
            'cuby',
            $this->subject->getRoom()
        );
    }

    /**
     * @test
     */
    public function hasRoomWithoutRoomReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasRoom()
        );
    }

    /**
     * @test
     */
    public function hasRoomWithRoomReturnsTrue()
    {
        $this->subject->setRoom('cuby');

        self::assertTrue(
            $this->subject->hasRoom()
        );
    }
}
