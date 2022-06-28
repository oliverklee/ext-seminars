<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\CsvExportConfigurationCheck;

/**
 * @covers \OliverKlee\Seminars\Configuration\CsvExportConfigurationCheck
 */
final class CsvExportConfigurationCheckTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var CsvExportConfigurationCheck
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CsvExportConfigurationCheck(new DummyConfiguration(), 'plugin.tx_seminars');
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
}
