<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\FrontEndUserGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\FrontEndUserGroup
 */
final class FrontEndUserGroupTest extends TestCase
{
    /**
     * @var FrontEndUserGroup the object to test
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUserGroup();
    }

    //////////////////////////////////////////////////
    // Tests concerning the event record storage PID
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function hasEventRecordPidForNoPidSetReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function hasEventRecordPidForPidSetReturnsTrue(): void
    {
        $this->subject->setData(['tx_seminars_events_pid' => 42]);

        self::assertTrue(
            $this->subject->hasEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordPidForNoPidSetReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordPidForPidSetReturnsThisPid(): void
    {
        $this->subject->setData(['tx_seminars_events_pid' => 42]);

        self::assertEquals(
            42,
            $this->subject->getEventRecordPid()
        );
    }
}
