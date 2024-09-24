<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\MyVipEventsConfigurationCheck;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Configuration\MyVipEventsConfigurationCheck
 */
final class MyVipEventsConfigurationCheckTest extends UnitTestCase
{
    private MyVipEventsConfigurationCheck $subject;

    private DummyConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

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
}
