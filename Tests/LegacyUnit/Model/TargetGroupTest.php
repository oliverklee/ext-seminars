<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

class TargetGroupTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_TargetGroup
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_TargetGroup();
    }

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $title must not be empty.'
        );

        $this->subject->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('Housewives');

        self::assertEquals(
            'Housewives',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'Housewives']);

        self::assertEquals(
            'Housewives',
            $this->subject->getTitle()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getMinimumAge()
        );
    }

    /**
     * @test
     */
    public function getMinimumAgeWithNonZeroMinimumAgeReturnsMinimumAge()
    {
        $this->subject->setData(['minimum_age' => 18]);

        self::assertEquals(
            18,
            $this->subject->getMinimumAge()
        );
    }

    /**
     * @test
     */
    public function setMinimumAgeSetsMinimumAge()
    {
        $this->subject->setMinimumAge(18);

        self::assertEquals(
            18,
            $this->subject->getMinimumAge()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getMaximumAge()
        );
    }

    /**
     * @test
     */
    public function getMaximumAgeWithNonZeroMaximumAgeReturnsMaximumAge()
    {
        $this->subject->setData(['maximum_age' => 18]);

        self::assertEquals(
            18,
            $this->subject->getMaximumAge()
        );
    }

    /**
     * @test
     */
    public function setMaximumAgeSetsMaximumAge()
    {
        $this->subject->setMaximumAge(18);

        self::assertEquals(
            18,
            $this->subject->getMaximumAge()
        );
    }
}
