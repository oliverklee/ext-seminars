<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractModule;
use OliverKlee\Seminars\BackEnd\Controller;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ControllerTest extends TestCase
{
    use BackEndTestsTrait;

    /**
     * @var Controller
     */
    private $subject = null;

    protected function setUp()
    {
        $this->unifyTestingEnvironment();

        $this->subject = new Controller();
    }

    protected function tearDown()
    {
        $this->restoreOriginalEnvironment();
    }

    /**
     * @test
     */
    public function isAbstractModule()
    {
        self::assertInstanceOf(AbstractModule::class, $this->subject);
    }

    /**
     * @test
     */
    public function mainActionWithCsvFlagReturnsCsvDownload()
    {
        $csvBody = 'foo;bar';
        $exporterProphecy = $this->prophesize(CsvDownloader::class);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $exporterProphecy->main()->shouldBeCalled()->willReturn($csvBody);
        /** @var CsvDownloader $exporterMock */
        $exporterMock = $exporterProphecy->reveal();
        GeneralUtility::addInstance(CsvDownloader::class, $exporterMock);

        /** @var ServerRequestInterface $requestMock */
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();

        $bodyProphecy = $this->prophesize(StreamInterface::class);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $bodyProphecy->write($csvBody)->shouldBeCalled();
        /** @var StreamInterface $bodyMock */
        $bodyMock = $bodyProphecy->reveal();

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $responseProphecy->getBody()->shouldBeCalled()->willReturn($bodyMock);
        /** @var ResponseInterface $responseMock */
        $responseMock = $responseProphecy->reveal();

        $GLOBALS['_GET']['csv'] = '1';

        self::assertSame($responseMock, $this->subject->mainAction($requestMock, $responseMock));
    }
}
