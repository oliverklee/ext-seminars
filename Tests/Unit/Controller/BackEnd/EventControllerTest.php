<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use OliverKlee\Seminars\Controller\BackEnd\EventController;
use OliverKlee\Seminars\Csv\CsvDownloader;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EventController
 */
final class EventControllerTest extends UnitTestCase
{
    /**
     * @var EventController&MockObject&AccessibleObjectInterface
     */
    private $subject;

    /**
     * @var CsvDownloader&MockObject
     */
    private $csvDownloaderMock;

    /**
     * @var Response
     */
    private $response;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var EventController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            EventController::class,
            ['redirect', 'forward', 'redirectToUri']
        );
        $this->subject = $subject;

        if (\class_exists(Response::class)) {
            // 10LTS only
            $this->response = new Response();
            $this->subject->_set('response', $this->response);
        }

        $this->csvDownloaderMock = $this->createMock(CsvDownloader::class);
        GeneralUtility::addInstance(CsvDownloader::class, $this->csvDownloaderMock);
    }

    protected function tearDown(): void
    {
        if (isset($GLOBALS['_GET']) && \is_array($GLOBALS['_GET'])) {
            unset($GLOBALS['_GET']['id'], $GLOBALS['_GET']['pid'], $GLOBALS['_GET']['table']);
        }
        if (isset($GLOBALS['_POST']) && \is_array($GLOBALS['_POST'])) {
            unset($GLOBALS['_POST']['id']);
        }
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }

    /**
     * @test
     */
    public function exportCsvActionProvidesCsvDownloaderWithEventsTableName(): void
    {
        $this->subject->exportCsvAction(5);

        self::assertSame('tx_seminars_seminars', $GLOBALS['_GET']['table']);
    }

    /**
     * @test
     */
    public function exportCsvActionProvidesCsvDownloaderWithProvidedPageUid(): void
    {
        $pageUid = 9;

        $this->subject->exportCsvAction($pageUid);

        self::assertSame($pageUid, $GLOBALS['_GET']['pid']);
    }

    /**
     * @test
     */
    public function exportCsvActionReturnsCsvData(): void
    {
        $csvContent = 'foo,bar';
        $this->csvDownloaderMock->expects(self::once())->method('main')->willReturn($csvContent);

        $result = $this->subject->exportCsvAction(5);

        if ($result instanceof ResponseInterface) {
            $result = $result->getBody()->getContents();
        }
        self::assertSame($csvContent, $result);
    }

    /**
     * @test
     */
    public function exportCsvActionSetsCsvContentType(): void
    {
        $result = $this->subject->exportCsvAction(9);

        if ($result instanceof ResponseInterface) {
            // 11LTS path
            self::assertSame(
                'text/csv; header=present; charset=utf-8',
                $result->getHeaders()['Content-Type'][0]
            );
        } else {
            // 10LTS path
            self::assertContains(
                'Content-Type: text/csv; header=present; charset=utf-8',
                $this->response->getHeaders()
            );
        }
    }

    /**
     * @test
     */
    public function exportCsvActionSetsDownloadFilename(): void
    {
        $result = $this->subject->exportCsvAction(9);

        if ($result instanceof ResponseInterface) {
            // 11LTS path
            self::assertSame(
                'attachment; filename=events.csv',
                $result->getHeaders()['Content-Disposition'][0]
            );
        } else {
            // 10LTS path
            self::assertContains(
                'Content-Disposition: attachment; filename=events.csv',
                $this->response->getHeaders()
            );
        }
    }
}
