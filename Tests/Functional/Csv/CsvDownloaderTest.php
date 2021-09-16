<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Csv;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class CsvDownloaderTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var CsvDownloader
     */
    private $subject = null;

    /**
     * @var HeaderCollector
     */
    private $headerProxy = null;

    /**
     * @var Configuration
     */
    private $configuration = null;

    protected function setUp()
    {
        parent::setUp();

        $headerProxyFactory = HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerProxy = $headerProxyFactory->getHeaderCollector();

        $this->setUpBackendUserFromFixture(1);
        $this->setUpExtensionConfiguration();
        $this->initializeBackEndLanguage();

        $this->subject = new CsvDownloader();
        $this->subject->init([]);
    }

    /**
     * @return void
     */
    private function setUpExtensionConfiguration()
    {
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new Configuration());
        $this->configuration = new Configuration();
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);
    }

    /**
     * Retrieves the localization for the given locallang key and then strips the trailing colon from it.
     *
     * @param string $key the locallang key with the localization to remove the trailing colon from
     *
     * @return string locallang string with the removed trailing colon, will not be empty
     */
    private function localizeAndRemoveColon(string $key): string
    {
        return \rtrim($this->getLanguageService()->getLL($key), ':');
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForInexistentEventUidSetsNotFoundHeader()
    {
        $this->subject->createAndOutputListOfRegistrations(1);

        self::assertStringContainsString('404', $this->headerProxy->getLastAddedHeader());
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForInexistentEventUidReturnsNotFoundMessage()
    {
        $result = $this->subject->createAndOutputListOfRegistrations(1);

        self::assertSame($this->getLanguageService()->getLL('message_404'), $result);
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForExistentEventWithoutRegistrationsHasHeaderOnly()
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
    public function createListOfRegistrationsForBothConfigurationFieldsNotEmptyAddsSemicolonBetweenFieldsHeaders()
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
