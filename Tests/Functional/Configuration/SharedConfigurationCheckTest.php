<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Configuration;

use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\SharedConfigurationCheck;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Configuration\SharedConfigurationCheck
 */
final class SharedConfigurationCheckTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    private SharedConfigurationCheck $subject;

    private DummyConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = new DummyConfiguration();

        $this->subject = new SharedConfigurationCheck($this->configuration, 'plugin.tx_seminars');
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
        self::assertArrayHasKey(0, $result);
        self::assertStringContainsString('plugin.tx_seminars', $result[0]);
    }

    /**
     * @test
     */
    public function checkWithRegistrationEnabledCreatesErrors(): void
    {
        $this->configuration->setAsBoolean('enableRegistration', true);

        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertArrayHasKey(0, $result);
        self::assertStringContainsString('plugin.tx_seminars', $result[0]);
    }
}
