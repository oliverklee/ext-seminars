<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Model_TargetGroupTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Model_TargetGroup
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new \Tx_Seminars_Model_TargetGroup();
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
        $this->fixture->setTitle('Housewives');

        self::assertEquals(
            'Housewives',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Housewives']);

        self::assertEquals(
            'Housewives',
            $this->fixture->getTitle()
        );
    }

    /////////////////////////////////////
    // Tests concerning the minimum age
    /////////////////////////////////////

    /**
     * @test
     */
    public function getMinimumAgeWithNoMinimumAgeSetReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getMinimumAge()
        );
    }

    /**
     * @test
     */
    public function getMinimumAgeWithNonZeroMinimumAgeReturnsMinimumAge()
    {
        $this->fixture->setData(['minimum_age' => 18]);

        self::assertEquals(
            18,
            $this->fixture->getMinimumAge()
        );
    }

    /**
     * @test
     */
    public function setMinimumAgeSetsMinimumAge()
    {
        $this->fixture->setMinimumAge(18);

        self::assertEquals(
            18,
            $this->fixture->getMinimumAge()
        );
    }

    /////////////////////////////////////
    // Tests concerning the maximum age
    /////////////////////////////////////

    /**
     * @test
     */
    public function getMaximumAgeWithNoMaximumAgeSetReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getMaximumAge()
        );
    }

    /**
     * @test
     */
    public function getMaximumAgeWithNonZeroMaximumAgeReturnsMaximumAge()
    {
        $this->fixture->setData(['maximum_age' => 18]);

        self::assertEquals(
            18,
            $this->fixture->getMaximumAge()
        );
    }

    /**
     * @test
     */
    public function setMaximumAgeSetsMaximumAge()
    {
        $this->fixture->setMaximumAge(18);

        self::assertEquals(
            18,
            $this->fixture->getMaximumAge()
        );
    }
}
