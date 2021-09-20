<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\SharedConfigurationCheck;

/**
 * @covers \OliverKlee\Seminars\Configuration\SharedConfigurationCheck
 */
final class SharedConfigurationCheckTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var SharedConfigurationCheck
     */
    private $subject;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

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
        $this->configuration->setAsInteger('enableRegistration', 1);

        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertArrayHasKey(0, $result);
        self::assertStringContainsString('plugin.tx_seminars', $result[0]);
    }
}
