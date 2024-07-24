<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use OliverKlee\Seminars\Controller\BackEnd\EventController;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
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
     * @var EventRepository&MockObject
     */
    private $eventRepositoryMock;

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

        $this->eventRepositoryMock = $this->createMock(EventRepository::class);

        /** @var EventController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            EventController::class,
            ['redirect', 'forward', 'redirectToUri'],
            [$this->eventRepositoryMock]
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
        unset($_GET['id'], $_GET['pid'], $_GET['table']);
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

        self::assertSame('tx_seminars_seminars', $_GET['table']);
    }

    /**
     * @test
     */
    public function exportCsvActionProvidesCsvDownloaderWithProvidedPageUid(): void
    {
        $pageUid = 9;

        $this->subject->exportCsvAction($pageUid);

        self::assertSame($pageUid, $_GET['pid']);
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

    /**
     * @test
     */
    public function hideActionHidesEvent(): void
    {
        $uid = 15;
        $this->eventRepositoryMock->expects(self::once())->method('hideViaDataHandler')->with($uid);

        $this->subject->hideAction($uid);
    }

    /**
     * @test
     */
    public function hideActionRedirectsToModuleOverviewAction(): void
    {
        $this->subject->expects(self::once())->method('redirect')->with('overview', 'BackEnd\\Module');

        $this->subject->hideAction(15);
    }

    /**
     * @test
     */
    public function unhideActionUnhidesEvent(): void
    {
        $uid = 15;
        $this->eventRepositoryMock->expects(self::once())->method('unhideViaDataHandler')->with($uid);

        $this->subject->unhideAction($uid);
    }

    /**
     * @test
     */
    public function unhideActionRedirectsToModuleOverviewAction(): void
    {
        $this->subject->expects(self::once())->method('redirect')->with('overview', 'BackEnd\\Module');

        $this->subject->unhideAction(15);
    }

    /**
     * @test
     */
    public function deleteActionDeletesEvent(): void
    {
        $uid = 15;
        $this->eventRepositoryMock->expects(self::once())->method('deleteViaDataHandler')->with($uid);

        $this->subject->deleteAction($uid);
    }

    /**
     * @test
     */
    public function deleteActionRedirectsToModuleOverviewAction(): void
    {
        $this->subject->expects(self::once())->method('redirect')->with('overview', 'BackEnd\\Module');

        $this->subject->deleteAction(15);
    }
}
