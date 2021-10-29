<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractModule;
use OliverKlee\Seminars\BackEnd\Controller;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ControllerTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var Controller
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->unifyTestingEnvironment();

        $this->subject = new Controller();
    }

    protected function tearDown(): void
    {
        // Manually purge the TYPO3 FIFO queue
        GeneralUtility::makeInstance(CsvDownloader::class);
        $this->restoreOriginalEnvironment();
    }

    /**
     * @test
     */
    public function isAbstractModule(): void
    {
        self::assertInstanceOf(AbstractModule::class, $this->subject);
    }

    /**
     * @test
     */
    public function mainActionWithCsvFlagReturnsCsvDownload(): void
    {
        $csvBody = 'foo;bar';
        $exporterProphecy = $this->prophesize(CsvDownloader::class);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $exporterProphecy->main()->shouldBeCalled()->willReturn($csvBody);
        /** @var CsvDownloader $exporterMock */
        $exporterMock = $exporterProphecy->reveal();
        GeneralUtility::addInstance(CsvDownloader::class, $exporterMock);

        $GLOBALS['_GET']['csv'] = '1';

        $response = $this->subject->mainAction();

        self::assertSame($csvBody, (string)$response->getBody());
    }
}
