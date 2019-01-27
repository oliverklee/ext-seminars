<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Model_AbstractTimeSpanTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Model_AbstractTimeSpan
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = $this->getMockForAbstractClass(\Tx_Seminars_Model_AbstractTimeSpan::class);
    }

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $title must not be empty.'
        );

        $this->fixture->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->fixture->setTitle('Superhero');

        self::assertEquals(
            'Superhero',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Superhero']);

        self::assertEquals(
            'Superhero',
            $this->fixture->getTitle()
        );
    }

    ////////////////////////////////////
    // Tests regarding the begin date.
    ////////////////////////////////////

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampWithoutBeginDateReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getBeginDateAsUnixTimeStampWithBeginDateReturnsBeginDate()
    {
        $this->fixture->setData(['begin_date' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithNegativeTimeStampThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $beginDate must be >= 0.'
        );

        $this->fixture->setBeginDateAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithZeroTimeStampSetsBeginDate()
    {
        $this->fixture->setBeginDateAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->fixture->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setBeginDateAsUnixTimeStampWithPositiveTimeStampSetsBeginDate()
    {
        $this->fixture->setBeginDateAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->fixture->getBeginDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasBeginDateWithoutBeginDateReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasBeginDate()
        );
    }

    /**
     * @test
     */
    public function hasBeginDateWithBeginDateReturnsTrue()
    {
        $this->fixture->setBeginDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->fixture->hasBeginDate()
        );
    }

    //////////////////////////////////
    // Tests regarding the end date.
    //////////////////////////////////

    /**
     * @test
     */
    public function getEndDateAsUnixTimeStampWithoutEndDateReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getEndDateAsUnixTimeStampWithEndDateReturnsEndDate()
    {
        $this->fixture->setData(['end_date' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithNegativeTimeStampThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $endDate must be >= 0.'
        );

        $this->fixture->setEndDateAsUnixTimeStamp(-1);
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithZeroTimeStampSetsEndDate()
    {
        $this->fixture->setEndDateAsUnixTimeStamp(0);

        self::assertEquals(
            0,
            $this->fixture->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function setEndDateAsUnixTimeStampWithPositiveTimeStampSetsEndDate()
    {
        $this->fixture->setEndDateAsUnixTimeStamp(42);

        self::assertEquals(
            42,
            $this->fixture->getEndDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function hasEndDateWithoutEndDateReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasEndDate()
        );
    }

    /**
     * @test
     */
    public function hasEndDateWithEndDateReturnsTrue()
    {
        $this->fixture->setEndDateAsUnixTimeStamp(42);

        self::assertTrue(
            $this->fixture->hasEndDate()
        );
    }

    //////////////////////////////
    // Tests regarding the room.
    //////////////////////////////

    /**
     * @test
     */
    public function getRoomWithoutRoomReturnsAnEmptyString()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            '',
            $this->fixture->getRoom()
        );
    }

    /**
     * @test
     */
    public function getRoomWithRoomReturnsRoom()
    {
        $this->fixture->setData(['room' => 'cuby']);

        self::assertEquals(
            'cuby',
            $this->fixture->getRoom()
        );
    }

    /**
     * @test
     */
    public function setRoomSetsRoom()
    {
        $this->fixture->setRoom('cuby');

        self::assertEquals(
            'cuby',
            $this->fixture->getRoom()
        );
    }

    /**
     * @test
     */
    public function hasRoomWithoutRoomReturnsFalse()
    {
        $this->fixture->setData([]);

        self::assertFalse(
            $this->fixture->hasRoom()
        );
    }

    /**
     * @test
     */
    public function hasRoomWithRoomReturnsTrue()
    {
        $this->fixture->setRoom('cuby');

        self::assertTrue(
            $this->fixture->hasRoom()
        );
    }
}
