<?php
namespace OliverKlee\Seminars\Tests\Unit\BackEnd;

use OliverKlee\Seminars\BackEnd\AbstractModule;
use OliverKlee\Seminars\BackEnd\Controller;
use OliverKlee\Seminars\Tests\Unit\Support\Traits\BackEndTestsTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecySubjectInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ControllerTest extends \Tx_Phpunit_TestCase
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
        static::assertInstanceOf(AbstractModule::class, $this->subject);
    }

    /**
     * @test
     */
    public function mainActionWithCsvFlagReturnsCsvDownload()
    {
        $csvBody = 'foo;bar';
        /** @var \Tx_Seminars_Csv_CsvDownloader|ObjectProphecy $exporterProphecy */
        $exporterProphecy = $this->prophesize(\Tx_Seminars_Csv_CsvDownloader::class);
        $exporterProphecy->main()->shouldBeCalled()->willReturn($csvBody);
        /** @var \Tx_Seminars_Csv_CsvDownloader|ProphecySubjectInterface $exporterMock */
        $exporterMock = $exporterProphecy->reveal();
        GeneralUtility::addInstance(\Tx_Seminars_Csv_CsvDownloader::class, $exporterMock);

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

        static::assertSame($responseMock, $this->subject->mainAction($requestMock, $responseMock));
    }
}
