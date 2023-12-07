<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\RegistrationListConfigurationCheck;

/**
 * @covers \OliverKlee\Seminars\Configuration\RegistrationListConfigurationCheck
 */
final class RegistrationListConfigurationCheckTest extends UnitTestCase
{
    /**
     * @var RegistrationListConfigurationCheck
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RegistrationListConfigurationCheck(new DummyConfiguration(), 'plugin.tx_seminars_pi1');
    }

    /**
     * @test
     */
    public function isConfigurationCheck(): void
    {
        self::assertInstanceOf(AbstractConfigurationCheck::class, $this->subject);
    }
}
