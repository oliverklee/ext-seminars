<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\MyVipEventsConfigurationCheck;

/**
 * @covers \OliverKlee\Seminars\Configuration\MyVipEventsConfigurationCheck
 */
final class MyVipEventsConfigurationCheckTest extends UnitTestCase
{
    /**
     * @var MyVipEventsConfigurationCheck
     */
    private $subject;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = new DummyConfiguration();

        $this->subject = new MyVipEventsConfigurationCheck($this->configuration, 'plugin.tx_seminars_pi1');
    }

    /**
     * @test
     */
    public function isConfigurationCheck(): void
    {
        self::assertInstanceOf(AbstractConfigurationCheck::class, $this->subject);
    }

    /**
     * @test
     */
    public function checkWithEmptyConfigurationCreatesErrors(): void
    {
        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertNotSame([], $result);
    }

    /**
     * @test
     */
    public function checkWithEmptyConfigurationUsesProvidedNamespaceForErrors(): void
    {
        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertArrayHasKey(1, $result);
        self::assertStringContainsString('plugin.tx_seminars_pi1', $result[1]);
    }

    /**
     * @test
     */
    public function checkWithEmptyConfigurationWithManagerPermissionsEnabledUsesProvidedNamespaceForErrors(): void
    {
        $this->configuration->setAsBoolean('mayManagersEditTheirEvents', true);

        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertNotSame([], $result);
    }
}
