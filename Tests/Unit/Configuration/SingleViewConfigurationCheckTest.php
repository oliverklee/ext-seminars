<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\SingleViewConfigurationCheck;

/**
 * @covers \OliverKlee\Seminars\Configuration\SingleViewConfigurationCheck
 */
final class SingleViewConfigurationCheckTest extends UnitTestCase
{
    /**
     * @var SingleViewConfigurationCheck
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new SingleViewConfigurationCheck(new DummyConfiguration(), 'plugin.tx_seminars_pi1');
    }

    /**
     * @test
     */
    public function isConfigurationCheck(): void
    {
        self::assertInstanceOf(AbstractConfigurationCheck::class, $this->subject);
    }
}
