<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\BackEnd\AbstractModule;
use OliverKlee\Seminars\BackEnd\Controller;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Tests\LegacyUnit\Support\Traits\BackEndTestsTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecySubjectInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
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
        /** @var CsvDownloader|ObjectProphecy $exporterProphecy */
        $exporterProphecy = $this->prophesize(CsvDownloader::class);
        $exporterProphecy->main()->shouldBeCalled()->willReturn($csvBody);
        /** @var CsvDownloader|ProphecySubjectInterface $exporterMock */
        $exporterMock = $exporterProphecy->reveal();
        GeneralUtility::addInstance(CsvDownloader::class, $exporterMock);

        /** @var ServerRequestInterface|ProphecySubjectInterface $requestMock */
        $requestMock = $this->prophesize(ServerRequestInterface::class)->reveal();

        /** @var StreamInterface|ObjectProphecy $bodyProphecy */
        $bodyProphecy = $this->prophesize(StreamInterface::class);
        $bodyProphecy->write($csvBody)->shouldBeCalled();
        /** @var StreamInterface|ProphecySubjectInterface $bodyMock */
        $bodyMock = $bodyProphecy->reveal();

        /** @var ResponseInterface|ObjectProphecy $responseProphecy */
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->shouldBeCalled()->willReturn($bodyMock);
        /** @var ResponseInterface|ProphecySubjectInterface $responseMock */
        $responseMock = $responseProphecy->reveal();

        $GLOBALS['_GET']['csv'] = '1';

        self::assertSame($responseMock, $this->subject->mainAction($requestMock, $responseMock));
    }
}
