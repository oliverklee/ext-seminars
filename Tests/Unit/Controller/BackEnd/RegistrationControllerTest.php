<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Controller\BackEnd\RegistrationController;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Model\Registration;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EventStatisticsTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\PageUidTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\PermissionsTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\RegistrationController
 */
final class RegistrationControllerTest extends UnitTestCase
{
    /**
     * @var RegistrationController&MockObject&AccessibleObjectInterface
     */
    private $subject;

    /**
     * @var TemplateView&MockObject
     */
    private $viewMock;

    /**
     * @var Permissions&MockObject
     */
    private $permissionsMock;

    /**
     * @var RegistrationRepository&MockObject
     */
    private $registrationRepositoryMock;

    /**
     * @var EventRepository&MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EventStatisticsCalculator&MockObject
     */
    private $eventStatisticsCalculatorMock;

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

        $this->registrationRepositoryMock = $this->createMock(RegistrationRepository::class);
        $this->eventRepositoryMock = $this->createMock(EventRepository::class);

        /** @var RegistrationController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            RegistrationController::class,
            ['redirect', 'forward', 'redirectToUri'],
            [$this->registrationRepositoryMock, $this->eventRepositoryMock]
        );
        $this->subject = $subject;

        if (\class_exists(Response::class)) {
            // 10LTS only
            $this->response = new Response();
            $this->subject->_set('response', $this->response);
        }
        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);
        $this->permissionsMock = $this->createMock(Permissions::class);
        $this->subject->injectPermissions($this->permissionsMock);
        $this->eventStatisticsCalculatorMock = $this->createMock(EventStatisticsCalculator::class);
        $this->subject->injectEventStatisticsCalculator($this->eventStatisticsCalculatorMock);

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
     * @param positive-int $eventUid
     *
     * @return SingleEvent&MockObject
     */
    private function buildSingleEventMockWithUid(int $eventUid): SingleEvent
    {
        $event = $this->createMock(SingleEvent::class);
        $event->method('getUid')->willReturn($eventUid);

        return $event;
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
    public function pageUidIsTakenFromGetId(): void
    {
        $pageUid = 15;
        $_GET['id'] = (string)$pageUid;

        self::assertSame($pageUid, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function pageUidIsTakenFromPostId(): void
    {
        $pageUid = 15;
        $_GET['id'] = (string)$pageUid;

        self::assertSame($pageUid, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function pageUidForNoIdInRequestIsZero(): void
    {
        self::assertSame(0, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function showForEventActionWithTopicDoesNotQueryForRegistrations(): void
    {
        $eventUid = 5;
        $event = $this->createMock(EventTopic::class);
        $event->method('getUid')->willReturn($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);

        $this->registrationRepositoryMock->expects(self::never())->method('findRegularRegistrationsByEvent')
            ->with(self::anything());
        $this->registrationRepositoryMock->expects(self::never())->method('findWaitingListRegistrationsByEvent')
            ->with(self::anything());

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionEnrichesProvidedEventWithStatistics(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);

        $this->eventStatisticsCalculatorMock->expects(self::once())->method('enrichWithStatistics')->with($event);

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionPassesPermissionsToView(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);

        $this->viewMock->expects(self::exactly(5))->method('assign')
            ->withConsecutive(
                ['permissions', $this->permissionsMock],
                ['pageUid', self::anything()],
                ['event', self::anything()],
                ['regularRegistrations', self::anything()],
                ['waitingListRegistrations', self::anything()]
            );

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionPassesPageUidToView(): void
    {
        $pageUid = 8;
        $_GET['id'] = (string)$pageUid;
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);

        $this->viewMock->expects(self::exactly(5))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', $pageUid],
                ['event', self::anything()],
                ['regularRegistrations', self::anything()],
                ['waitingListRegistrations', self::anything()]
            );

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionPassesProvidedEventToView(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);

        $this->viewMock->expects(self::exactly(5))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['event', $event],
                ['regularRegistrations', self::anything()],
                ['waitingListRegistrations', self::anything()]
            );

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionForEventNotFoundThrowsException(): void
    {
        $eventUid = 5;
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1698859637);
        $this->expectExceptionMessage('Event with UID 5 not found.');

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionPassesRegularRegistrationsForProvidedEventToView(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);
        $regularRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findRegularRegistrationsByEvent')
            ->with($eventUid)->willReturn($regularRegistrations);

        $this->viewMock->expects(self::exactly(5))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['event', self::anything()],
                ['regularRegistrations', $regularRegistrations],
                ['waitingListRegistrations', self::anything()]
            );

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionEnrichesRegularRegistrationsWithRawData(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);
        $regularRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findRegularRegistrationsByEvent')
            ->with($eventUid)->willReturn($regularRegistrations);
        $this->registrationRepositoryMock->expects(self::exactly(2))->method('enrichWithRawData')
            ->withConsecutive([$regularRegistrations], [self::anything()]);

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionForEventWithWaitingListPassesWaitingRegistrationsForProvidedEventToView(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);
        $event->method('hasWaitingList')->willReturn(true);
        $waitingListRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findWaitingListRegistrationsByEvent')
            ->with($eventUid)->willReturn($waitingListRegistrations);

        $this->viewMock->expects(self::exactly(5))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['event', self::anything()],
                ['regularRegistrations', self::anything()],
                ['waitingListRegistrations', $waitingListRegistrations]
            );

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionForEventWithoutWaitingListPassesWaitingRegistrationsForProvidedEventToView(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);
        $event->method('hasWaitingList')->willReturn(false);
        $waitingListRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findWaitingListRegistrationsByEvent')
            ->with($eventUid)->willReturn($waitingListRegistrations);

        $this->viewMock->expects(self::exactly(5))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['event', self::anything()],
                ['regularRegistrations', self::anything()],
                ['waitingListRegistrations', $waitingListRegistrations]
            );

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionForEventWithWaitingListEnrichesWaitingListRegistrationsWithRawData(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);
        $event->method('hasWaitingList')->willReturn(true);
        $waitingListRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findWaitingListRegistrationsByEvent')
            ->with($eventUid)->willReturn($waitingListRegistrations);
        $this->registrationRepositoryMock->expects(self::exactly(2))->method('enrichWithRawData')
            ->withConsecutive([self::anything()], [$waitingListRegistrations]);

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function showForEventActionForEventWithoutWaitingListEnrichesWaitingListRegistrationsWithRawData(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->eventRepositoryMock->expects(self::once())
            ->method('findOneByUidForBackend')->with($eventUid)->willReturn($event);
        $event->method('hasWaitingList')->willReturn(false);
        $waitingListRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findWaitingListRegistrationsByEvent')
            ->with($eventUid)->willReturn($waitingListRegistrations);
        $this->registrationRepositoryMock->expects(self::exactly(2))->method('enrichWithRawData')
            ->withConsecutive([self::anything()], [$waitingListRegistrations]);

        $this->subject->showForEventAction($eventUid);
    }

    /**
     * @test
     */
    public function exportCsvForEventActionProvidesCsvDownloaderWithRegistrationsTableName(): void
    {
        $this->subject->exportCsvForEventAction(5);

        self::assertSame('tx_seminars_attendances', $_GET['table'] ?? null);
    }

    /**
     * @test
     */
    public function exportCsvForEventActionProvidesCsvDownloaderWithUidOfProvidedPage(): void
    {
        $eventUid = 9;

        $this->subject->exportCsvForEventAction($eventUid);

        self::assertSame($eventUid, $_GET['eventUid'] ?? null);
    }

    /**
     * @test
     */
    public function exportCsvForEventActionReturnsCsvData(): void
    {
        $csvContent = 'foo,bar';
        $this->csvDownloaderMock->expects(self::once())->method('main')->willReturn($csvContent);

        $result = $this->subject->exportCsvForEventAction(5);

        if ($result instanceof ResponseInterface) {
            $result = $result->getBody()->getContents();
        }
        self::assertSame($csvContent, $result);
    }

    /**
     * @test
     */
    public function exportCsvForEventActionSetsCsvContentType(): void
    {
        $result = $this->subject->exportCsvForEventAction(5);

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
    public function exportCsvForEventActionSetsDownloadFilename(): void
    {
        $result = $this->subject->exportCsvForEventAction(5);

        if ($result instanceof ResponseInterface) {
            // 11LTS path
            self::assertSame(
                'attachment; filename=registrations.csv',
                $result->getHeaders()['Content-Disposition'][0]
            );
        } else {
            // 10LTS path
            self::assertContains(
                'Content-Disposition: attachment; filename=registrations.csv',
                $this->response->getHeaders()
            );
        }
    }

    /**
     * @test
     */
    public function exportCsvForPageUidActionProvidesCsvDownloaderWithRegistrationsTableName(): void
    {
        $this->subject->exportCsvForPageUidAction(12);

        self::assertSame('tx_seminars_attendances', $_GET['table'] ?? null);
    }

    /**
     * @test
     */
    public function exportCsvForPageUidActionProvidesCsvDownloaderWithProvidedPageUid(): void
    {
        $pageUid = 9;

        $this->subject->exportCsvForPageUidAction($pageUid);

        self::assertSame($pageUid, $_GET['pid'] ?? null);
    }

    /**
     * @test
     */
    public function exportCsvForPageUidActionReturnsCsvData(): void
    {
        $csvContent = 'foo,bar';
        $this->csvDownloaderMock->expects(self::once())->method('main')->willReturn($csvContent);

        $result = $this->subject->exportCsvForPageUidAction(12);

        if ($result instanceof ResponseInterface) {
            $result = $result->getBody()->getContents();
        }
        self::assertSame($csvContent, $result);
    }

    /**
     * @test
     */
    public function exportCsvForPageUidActionSetsCsvContentType(): void
    {
        $result = $this->subject->exportCsvForPageUidAction(12);

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
    public function exportCsvForPageUidActionSetsDownloadFilename(): void
    {
        $result = $this->subject->exportCsvForPageUidAction(12);

        if ($result instanceof ResponseInterface) {
            // 11LTS path
            self::assertSame(
                'attachment; filename=registrations.csv',
                $result->getHeaders()['Content-Disposition'][0]
            );
        } else {
            // 10LTS path
            self::assertContains(
                'Content-Disposition: attachment; filename=registrations.csv',
                $this->response->getHeaders()
            );
        }
    }

    /**
     * @test
     */
    public function deleteActionDeletesEvent(): void
    {
        $uid = 15;
        $this->registrationRepositoryMock->expects(self::once())->method('deleteViaDataHandler')->with($uid);

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
