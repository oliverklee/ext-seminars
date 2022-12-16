<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Csv;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;

/**
 * @covers \OliverKlee\Seminars\Csv\CsvDownloader
 */
final class CsvDownloaderTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var CsvDownloader
     */
    private $subject;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        $this->setUpExtensionConfiguration();
        $this->initializeBackEndLanguage();

        $this->subject = new CsvDownloader();
    }

    private function setUpExtensionConfiguration(): void
    {
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);
    }

    /**
     * Retrieves the localization for the given locallang key and then strips the trailing colon from it.
     *
     * @param non-empty-string $key the locallang key with the localization to remove the trailing colon from
     *
     * @return string locallang string with the removed trailing colon, will not be empty
     */
    private function localizeAndRemoveColon(string $key): string
    {
        return \rtrim($this->translate($key), ':');
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForExistentEventWithoutRegistrationsHasHeaderOnly(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $result = $this->subject->createAndOutputListOfRegistrations(1);

        $expected = $this->localizeAndRemoveColon('LGL.name') . ';' .
            $this->localizeAndRemoveColon('tx_seminars_attendances.uid') . "\r\n";

        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForBothConfigurationFieldsNotEmptyAddsSemicolonBetweenFieldsHeaders(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');

        $result = $this->subject->createAndOutputListOfRegistrations(1);

        $expected = $this->localizeAndRemoveColon('LGL.name') . ';' .
            $this->localizeAndRemoveColon('tx_seminars_attendances.address');
        self::assertStringContainsString($expected, $result);
    }
}
