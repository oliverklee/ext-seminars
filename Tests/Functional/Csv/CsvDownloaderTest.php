<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Csv;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class CsvDownloaderTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Csv_CsvDownloader
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_HeaderCollector
     */
    private $headerProxy = null;

    protected function setUp()
    {
        parent::setUp();

        $headerProxyFactory = \Tx_Oelib_HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerProxy = $headerProxyFactory->getHeaderProxy();

        $this->subject = new \Tx_Seminars_Csv_CsvDownloader();
        $this->subject->init([]);
    }

    /**
     * @test
     */
    public function createAndOutputListOfRegistrationsForInexistentEventUidSetsNotFoundHeader()
    {
        $this->subject->createAndOutputListOfRegistrations(1);

        self::assertContains('404', $this->headerProxy->getLastAddedHeader());
    }
}
