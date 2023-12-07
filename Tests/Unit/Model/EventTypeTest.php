<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Model\EventType;

/**
 * @covers \OliverKlee\Seminars\Model\EventType
 */
final class EventTypeTest extends UnitTestCase
{
    /**
     * @var EventType
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EventType();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'Workshop']);

        self::assertSame(
            'Workshop',
            $this->subject->getTitle()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning the single view page UID
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function getSingleViewPageUidReturnsSingleViewPageUid(): void
    {
        $this->subject->setData(['single_view_page' => 42]);

        self::assertSame(
            42,
            $this->subject->getSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForZeroPageUidReturnsFalse(): void
    {
        $this->subject->setData(['single_view_page' => 0]);

        self::assertFalse(
            $this->subject->hasSingleViewPageUid()
        );
    }

    /**
     * @test
     */
    public function hasSingleViewPageUidForNonZeroPageUidReturnsTrue(): void
    {
        $this->subject->setData(['single_view_page' => 42]);

        self::assertTrue(
            $this->subject->hasSingleViewPageUid()
        );
    }
}
