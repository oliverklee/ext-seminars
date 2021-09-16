<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

final class TimeSlotTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_TimeSlot
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_OldModel_TimeSlot();
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = \Tx_Seminars_OldModel_TimeSlot::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_TimeSlot::class, $result);
    }
}
