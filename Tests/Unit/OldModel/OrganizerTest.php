<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * @covers \Tx_Seminars_OldModel_Organizer
 */
final class OrganizerTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Organizer
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new \Tx_Seminars_OldModel_Organizer();
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
        $result = \Tx_Seminars_OldModel_Organizer::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Organizer::class, $result);
    }
}
