<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Controller\BackEnd\EventController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EventController
 */
final class EventControllerTest extends UnitTestCase
{
    /**
     * @var EventController&MockObject&AccessibleMockObjectInterface
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
     * @var EventRepository&MockObject
     */
    private $eventRepositoryMock;

    /**
     * @var EventStatisticsCalculator&MockObject
     */
    private $eventStatisticsCalculatorMock;

    protected function setUp(): void
    {
        /** @var EventController&AccessibleMockObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            EventController::class,
            ['redirect', 'forward', 'redirectToUri']
        );
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);
        $this->permissionsMock = $this->createMock(Permissions::class);
        $this->subject->injectPermissions($this->permissionsMock);
        $this->eventRepositoryMock = $this->createMock(EventRepository::class);
        $this->subject->injectEventRepository($this->eventRepositoryMock);
        $this->eventStatisticsCalculatorMock = $this->createMock(EventStatisticsCalculator::class);
        $this->subject->injectEventStatisticsCalculator($this->eventStatisticsCalculatorMock);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['_GET']['id'], $GLOBALS['_POST']['id']);
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
    public function indexActionPassesPermissionsToView(): void
    {
        $this->viewMock->expects(self::exactly(3))->method('assign')
            ->withConsecutive(
                ['permissions', $this->permissionsMock],
                ['pageUid', self::anything()],
                ['events', self::anything()]
            );

        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function indexActionPassesPageUidToView(): void
    {
        $pageUid = 8;
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        $this->viewMock->expects(self::exactly(3))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', $pageUid],
                ['events', self::anything()]
            );

        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function indexActionEnrichesEventsWithRawData(): void
    {
        $events = [new SingleEvent()];
        $this->eventRepositoryMock->expects(self::once())->method('findByPageUidInBackEndMode')
            ->with(self::anything())->willReturn($events);
        $this->eventRepositoryMock->expects(self::once())->method('enrichWithRawData')
            ->with($events);

        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function indexActionEnrichesEventsWithStatistics(): void
    {
        $event = new SingleEvent();
        $events = [$event];
        $this->eventRepositoryMock->expects(self::once())->method('findByPageUidInBackEndMode')
            ->with(self::anything())->willReturn($events);
        $this->eventStatisticsCalculatorMock->expects(self::once())->method('enrichWithStatistics')
            ->with($event);

        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function indexActionPassesEventsOnPageUidToView(): void
    {
        $pageUid = 8;
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        $events = [new SingleEvent()];
        $this->eventRepositoryMock->expects(self::once())->method('findByPageUidInBackEndMode')
            ->with($pageUid)->willReturn($events);
        $this->viewMock->expects(self::exactly(3))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['events', $events]
            );

        $this->subject->indexAction();
    }
}
