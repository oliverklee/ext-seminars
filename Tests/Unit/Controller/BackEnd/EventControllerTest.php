<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Controller\BackEnd\EventController;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;
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
     * @var TemplateView&MockObject
     */
    private $viewMock;

    /**
     * @var EventRepository&MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var Permissions&MockObject
     */
    private $permissionsMock;

    /**
     * @var EventStatisticsCalculator&MockObject
     */
    private $eventStatisticsCalculatorMock;

    /**
     * @var CsvDownloader&MockObject
     */
    private $csvDownloaderMock;

    /**
     * @var LanguageService&MockObject
     */
    private $languageServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepositoryMock = $this->createMock(EventRepository::class);
        $this->permissionsMock = $this->createMock(Permissions::class);
        $this->eventStatisticsCalculatorMock = $this->createMock(EventStatisticsCalculator::class);
        $this->languageServiceMock = $this->createMock(LanguageService::class);
        $GLOBALS['LANG'] = $this->languageServiceMock;

        $methodsToMock = ['addFlashMessage', 'htmlResponse', 'redirect', 'redirectToUri'];
        /** @var EventController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            EventController::class,
            $methodsToMock,
            [$this->eventRepositoryMock, $this->permissionsMock, $this->eventStatisticsCalculatorMock]
        );
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

        $this->csvDownloaderMock = $this->createMock(CsvDownloader::class);
        GeneralUtility::addInstance(CsvDownloader::class, $this->csvDownloaderMock);
    }

    protected function tearDown(): void
    {
        unset($_GET['id'], $GLOBALS['LANG']);
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
    public function deleteActionAddsFlashMessage(): void
    {
        $localizedMessage = 'Event deleted!';
        $this->languageServiceMock->expects(self::once())->method('sL')
            ->with('LLL:EXT:seminars/Resources/Private/Language/locallang.xml:backEndModule.message.eventDeleted')
            ->willReturn($localizedMessage);
        $this->subject->expects(self::once())->method('addFlashMessage')->with($localizedMessage);

        $this->subject->deleteAction(15);
    }

    /**
     * @test
     */
    public function deleteActionRedirectsToModuleOverviewAction(): void
    {
        $this->subject->expects(self::once())->method('redirect')->with('overview', 'BackEnd\\Module');

        $this->subject->deleteAction(15);
    }

    /**
     * @test
     */
    public function searchActionPassesPermissionsToView(): void
    {
        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', $this->permissionsMock],
                ['pageUid', self::anything()],
                ['events', self::anything()],
                ['searchTerm', self::anything()]
            );

        $this->subject->searchAction(1, '');
    }

    /**
     * @test
     */
    public function searchActionPassesPageUidToView(): void
    {
        $pageUid = 8;

        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', $pageUid],
                ['events', self::anything()],
                ['searchTerm', self::anything()]
            );

        $this->subject->searchAction($pageUid, '');
    }

    /**
     * @test
     */
    public function searchActionPassesEventsOnPageUidWithSearchTermToView(): void
    {
        $pageUid = 8;
        $searchTerm = 'no dice';

        $events = [new SingleEvent()];
        $this->eventRepositoryMock->expects(self::once())->method('findBySearchTermInBackEndMode')
            ->with($pageUid, $searchTerm)->willReturn($events);
        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['events', $events],
                ['searchTerm', self::anything()]
            );

        $this->subject->searchAction($pageUid, $searchTerm);
    }

    /**
     * @test
     */
    public function searchActionForMissingSearchTermPassesEventsOnPageUidWithEmptySearchTermToView(): void
    {
        $pageUid = 8;

        $events = [new SingleEvent()];
        $this->eventRepositoryMock->expects(self::once())->method('findBySearchTermInBackEndMode')
            ->with($pageUid, '')->willReturn($events);
        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['events', $events],
                ['searchTerm', self::anything()]
            );

        $this->subject->searchAction($pageUid);
    }

    /**
     * @test
     */
    public function searchActionEnrichesEventsWithRawData(): void
    {
        $events = [new SingleEvent()];
        $this->eventRepositoryMock->expects(self::once())->method('findBySearchTermInBackEndMode')
            ->with(self::anything(), self::anything())->willReturn($events);
        $this->eventRepositoryMock->expects(self::once())->method('enrichWithRawData')
            ->with($events);

        $this->subject->searchAction(1, '');
    }

    /**
     * @test
     */
    public function searchActionEnrichesEventsWithStatistics(): void
    {
        $event = new SingleEvent();
        $events = [$event];
        $this->eventRepositoryMock->expects(self::once())->method('findBySearchTermInBackEndMode')
            ->with(self::anything(), self::anything())->willReturn($events);
        $this->eventStatisticsCalculatorMock->expects(self::once())->method('enrichWithStatistics')
            ->with($event);

        $this->subject->searchAction(1, '');
    }

    /**
     * @test
     */
    public function searchActionPassesTrimmedSearchTermToView(): void
    {
        $searchTerm = ' no dice ';

        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['events', self::anything()],
                ['searchTerm', \trim($searchTerm)]
            );

        $this->subject->searchAction(1, $searchTerm);
    }

    /**
     * @test
     */
    public function duplicateActionDuplicatesEvent(): void
    {
        $uid = 15;
        $this->eventRepositoryMock->expects(self::once())->method('duplicateViaDataHandler')->with($uid);

        $this->subject->duplicateAction($uid);
    }

    /**
     * @test
     */
    public function duplicateActionRedirectsToModuleOverviewAction(): void
    {
        $this->subject->expects(self::once())->method('redirect')->with('overview', 'BackEnd\\Module');

        $this->subject->duplicateAction(15);
    }
}
