<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\SharedConfigurationCheck;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Configuration\SharedConfigurationCheck
 */
final class SharedConfigurationCheckTest extends UnitTestCase
{
    /**
     * @var SharedConfigurationCheck
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = new DummyConfiguration();

        $this->subject = new SharedConfigurationCheck($configuration, 'plugin.tx_seminars');
    }

    /**
     * @test
     */
    public function isConfigurationCheck(): void
    {
        self::assertInstanceOf(AbstractConfigurationCheck::class, $this->subject);
    }
}
