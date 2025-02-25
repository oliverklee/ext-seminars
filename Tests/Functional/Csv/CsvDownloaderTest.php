<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Middleware\ResponseHeadersModifier;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Csv\CsvDownloader
 */
final class CsvDownloaderTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private CsvDownloader $subject;

    private DummyConfiguration $configuration;

    private ResponseHeadersModifier $responseHeadersModifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpExtensionConfiguration();

        $this->responseHeadersModifier = new ResponseHeadersModifier();
        GeneralUtility::setSingletonInstance(ResponseHeadersModifier::class, $this->responseHeadersModifier);

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
     * @test
     */
    public function createAndOutputListOfRegistrationsForExistentEventSetsResponseHeaderContentType(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $this->subject->createAndOutputListOfRegistrations(1);

        $headers = $this->responseHeadersModifier->getOverrideHeaders();
        self::assertSame('text/csv; header=present; charset=utf-8', $headers['Content-type']);
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForExistentEventSetsResponseHeaderContentDisposition(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'uid');

        $this->subject->createAndOutputListOfRegistrations(1);

        $headers = $this->responseHeadersModifier->getOverrideHeaders();
        self::assertSame('attachment; filename=registrations.csv', $headers['Content-disposition']);
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

        $expected = "fe_users.name;tx_seminars_attendances.uid\r\n";
        self::assertSame($expected, $result);
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForBothConfigurationFieldsNotEmptyAddsSemicolonBetweenFieldHeaders(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');
        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');

        $result = $this->subject->createAndOutputListOfRegistrations(1);

        $expected = 'fe_users.name;tx_seminars_attendances.address';
        self::assertStringContainsString($expected, $result);
    }

    /**
     * @test
     */
    public function createListOfRegistrationsForZeroEventUidAndNoPageUidThrowsException(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1390320210);
        $this->expectExceptionMessage('No event UID or page UID set');

        $this->subject->createAndOutputListOfRegistrations(0);
    }

    /**
     * @test
     */
    public function createListOfRegistrationsPageReturnsExportsRegistrationsOnTheGivenPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');

        $result = $this->subject->createAndOutputListOfRegistrations(0, 1);

        self::assertStringContainsString('at home', $result);
    }

    /**
     * @test
     */
    public function createListOfRegistrationsPageReturnsIgnoresRegistrationsOnOtherPage(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventsAndRegistrations.xml');

        $this->configuration->setAsString('fieldsFromAttendanceForCsv', 'address');
        $this->configuration->setAsString('fieldsFromFeUserForCsv', 'name');

        $result = $this->subject->createAndOutputListOfRegistrations(0, 2);

        self::assertStringNotContainsString('at home', $result);
    }
}
