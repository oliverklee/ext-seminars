<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\OldModel\LegacyOrganizer;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyOrganizer
 */
final class OrganizerTest extends UnitTestCase
{
    /**
     * @var LegacyOrganizer
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new LegacyOrganizer();
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
        $result = LegacyOrganizer::fromData([]);

        self::assertInstanceOf(LegacyOrganizer::class, $result);
    }
}
