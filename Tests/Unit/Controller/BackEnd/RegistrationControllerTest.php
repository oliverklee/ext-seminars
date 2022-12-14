<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Controller\BackEnd\RegistrationController;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Model\Registration;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EventStatisticsTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\PageUidTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\PermissionsTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\RegistrationController
 */
final class RegistrationControllerTest extends UnitTestCase
{
    /**
     * @var RegistrationController&MockObject&AccessibleMockObjectInterface
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
        /** @var RegistrationController&AccessibleMockObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            RegistrationController::class,
            ['redirect', 'forward', 'redirectToUri']
        );
        $this->subject = $subject;

        if (\class_exists(Response::class)) {
            // 10LTS only
            $this->response = new Response();
        }
        $this->subject->_set('response', $this->response);
        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);
        $this->permissionsMock = $this->createMock(Permissions::class);
        $this->subject->injectPermissions($this->permissionsMock);
        $this->registrationRepositoryMock = $this->createMock(RegistrationRepository::class);
        $this->subject->injectRegistrationRepository($this->registrationRepositoryMock);
        $this->eventStatisticsCalculatorMock = $this->createMock(EventStatisticsCalculator::class);
        $this->subject->injectEventStatisticsCalculator($this->eventStatisticsCalculatorMock);

        $this->csvDownloaderMock = $this->createMock(CsvDownloader::class);
        GeneralUtility::addInstance(CsvDownloader::class, $this->csvDownloaderMock);
        ConfigurationRegistry::getInstance()
            ->set('plugin.tx_seminars', new DummyConfiguration(['charsetForCsv' => 'utf-8']));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['_GET']['id'], $GLOBALS['_GET']['pid'], $GLOBALS['_GET']['table'], $GLOBALS['_GET']['eventUid'], $GLOBALS['_POST']['id']);
        ConfigurationRegistry::purgeInstance();
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
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        self::assertSame($pageUid, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function pageUidIsTakenFromPostId(): void
    {
        $pageUid = 15;
        $GLOBALS['_POST']['id'] = (string)$pageUid;

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
        $event = $this->createMock(EventTopic::class);
        $event->method('getUid')->willReturn(5);

        $this->registrationRepositoryMock->expects(self::never())->method('findRegularRegistrationsByEvent')
            ->with(self::anything());
        $this->registrationRepositoryMock->expects(self::never())->method('findWaitingListRegistrationsByEvent')
            ->with(self::anything());

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionEnrichesProvidedEventWithStatistics(): void
    {
        $event = $this->buildSingleEventMockWithUid(5);

        $this->eventStatisticsCalculatorMock->expects(self::once())->method('enrichWithStatistics')->with($event);

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionPassesPermissionsToView(): void
    {
        $event = $this->buildSingleEventMockWithUid(5);

        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', $this->permissionsMock],
                ['pageUid', self::anything()],
                ['event', self::anything()],
                ['regularRegistrations', self::anything()]
            );

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionPassesPageUidToView(): void
    {
        $pageUid = 8;
        $GLOBALS['_GET']['id'] = (string)$pageUid;
        $event = $this->buildSingleEventMockWithUid(5);

        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', $pageUid],
                ['event', self::anything()],
                ['regularRegistrations', self::anything()]
            );

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionPassesProvidedEventToView(): void
    {
        $event = $this->buildSingleEventMockWithUid(5);

        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['event', $event],
                ['regularRegistrations', self::anything()]
            );

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionPassesRegularRegistrationsForProvidedEventToView(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $regularRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findRegularRegistrationsByEvent')
            ->with($eventUid)->willReturn($regularRegistrations);

        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['event', self::anything()],
                ['regularRegistrations', $regularRegistrations]
            );

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionEnrichesRegularRegistrationsWithRawData(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $regularRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findRegularRegistrationsByEvent')
            ->with($eventUid)->willReturn($regularRegistrations);
        $this->registrationRepositoryMock->expects(self::once())->method('enrichWithRawData')
            ->with($regularRegistrations);

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionForEventWithWaitingListPassesWaitingRegistrationsForProvidedEventToView(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
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

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionForEventWithWaitingListEnrichesWaitingListRegistrationsWithRawData(): void
    {
        $eventUid = 5;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $event->method('hasWaitingList')->willReturn(true);
        $waitingListRegistrations = [new Registration()];

        $this->registrationRepositoryMock->expects(self::once())->method('findWaitingListRegistrationsByEvent')
            ->with($eventUid)->willReturn($waitingListRegistrations);
        $this->registrationRepositoryMock->expects(self::exactly(2))->method('enrichWithRawData')
            ->withConsecutive([self::anything()], [$waitingListRegistrations]);

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function showForEventActionForEventWithoutWaitingListDoesNotQueryForWaitingList(): void
    {
        $event = $this->buildSingleEventMockWithUid(5);
        $event->method('hasWaitingList')->willReturn(false);

        $this->registrationRepositoryMock->expects(self::never())->method('findWaitingListRegistrationsByEvent')
            ->with(self::anything());

        $this->subject->showForEventAction($event);
    }

    /**
     * @test
     */
    public function exportCsvForEventActionProvidesCsvDownloaderWithEventsTableName(): void
    {
        $this->subject->exportCsvForEventAction($this->buildSingleEventMockWithUid(5));

        self::assertSame('tx_seminars_attendances', $GLOBALS['_GET']['table']);
    }

    /**
     * @test
     */
    public function exportCsvForEventActionProvidesCsvDownloaderWithUidOfProvidedEvent(): void
    {
        $eventUid = 9;
        $event = $this->buildSingleEventMockWithUid($eventUid);

        $this->subject->exportCsvForEventAction($event);

        self::assertSame($eventUid, $GLOBALS['_GET']['eventUid']);
    }

    /**
     * @test
     */
    public function exportCsvForEventActionReturnsCsvData(): void
    {
        $csvContent = 'foo,bar';
        $this->csvDownloaderMock->expects(self::once())->method('main')->willReturn($csvContent);

        $result = $this->subject->exportCsvForEventAction($this->buildSingleEventMockWithUid(5));

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
        $result = $this->subject->exportCsvForEventAction($this->buildSingleEventMockWithUid(5));

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
        $result = $this->subject->exportCsvForEventAction($this->buildSingleEventMockWithUid(5));

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
}
