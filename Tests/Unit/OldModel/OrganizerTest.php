<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

final class OrganizerTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Organizer
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_OldModel_Organizer();
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
        $result = \Tx_Seminars_OldModel_Organizer::fromData([]);

        self::assertInstanceOf(\Tx_Seminars_OldModel_Organizer::class, $result);
    }
}
