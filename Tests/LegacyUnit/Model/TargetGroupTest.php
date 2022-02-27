<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\TargetGroup;
use PHPUnit\Framework\TestCase;

final class TargetGroupTest extends TestCase
{
    /**
     * @var TargetGroup
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new TargetGroup();
    }

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException(): void
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
    public function setTitleSetsTitle(): void
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
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
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
    public function getMinimumAgeWithNoMinimumAgeSetReturnsZero(): void
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
    public function getMinimumAgeWithNonZeroMinimumAgeReturnsMinimumAge(): void
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
    public function setMinimumAgeSetsMinimumAge(): void
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
    public function getMaximumAgeWithNoMaximumAgeSetReturnsZero(): void
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
    public function getMaximumAgeWithNonZeroMaximumAgeReturnsMaximumAge(): void
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
    public function setMaximumAgeSetsMaximumAge(): void
    {
        $this->subject->setMaximumAge(18);

        self::assertEquals(
            18,
            $this->subject->getMaximumAge()
        );
    }
}
