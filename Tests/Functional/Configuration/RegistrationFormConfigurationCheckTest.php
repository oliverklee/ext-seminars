<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\RegistrationFormConfigurationCheck;

/**
 * @covers \OliverKlee\Seminars\Configuration\RegistrationFormConfigurationCheck
 */
final class RegistrationFormConfigurationCheckTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var RegistrationFormConfigurationCheck
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

        $this->subject = new RegistrationFormConfigurationCheck($this->configuration, 'plugin.tx_seminars_pi1');
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
    public function checkWithRegistrationEnabledCreatesErrors(): void
    {
        $this->configuration->setAsBoolean('enableRegistration', true);

        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertArrayHasKey(1, $result);
        self::assertStringContainsString('plugin.tx_seminars_pi1', $result[1]);
    }

    /**
     * @test
     */
    public function checkWithAdditionalAttendeesEnabledCreatesErrors(): void
    {
        $this->configuration->setAsBoolean('createAdditionalAttendeesAsFrontEndUsers', true);

        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertArrayHasKey(1, $result);
        self::assertStringContainsString('plugin.tx_seminars_pi1', $result[1]);
    }
}
