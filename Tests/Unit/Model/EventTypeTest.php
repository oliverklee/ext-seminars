<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_EventTypeTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_EventType
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new Tx_Seminars_Model_EventType();
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
        $this->fixture->setTitle('Workshop');

        self::assertEquals(
            'Workshop',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Workshop']);

        self::assertEquals(
            'Workshop',
            $this->fixture->getTitle()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning the single view page UID
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function getSingleViewPageUidReturnsSingleViewPageUid()
    {
        $this->fixture->setData(['single_view_page' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForZeroPageUidReturnsFalse()
    {
        $this->fixture->setData(['single_view_page' => 0]);

        self::assertFalse(
            $this->fixture->hasSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForNonZeroPageUidReturnsTrue()
    {
        $this->fixture->setData(['single_view_page' => 42]);

        self::assertTrue(
            $this->fixture->hasSingleViewPageUid()
        );
    }
}
