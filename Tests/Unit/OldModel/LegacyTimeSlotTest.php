<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\OldModel\LegacyTimeSlot;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyTimeSlot
 */
final class LegacyTimeSlotTest extends UnitTestCase
{
    /**
     * @var LegacyTimeSlot
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LegacyTimeSlot();
    }

    /**
     * @test
     */
    public function isAbstractModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass(): void
    {
        $result = LegacyTimeSlot::fromData([]);

        self::assertInstanceOf(LegacyTimeSlot::class, $result);
    }
}
