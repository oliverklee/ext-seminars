<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Controller\BackEnd\ModuleController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\ModuleController
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EventStatisticsTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\PageUidTrait
 * @covers \OliverKlee\Seminars\Controller\BackEnd\PermissionsTrait
 */
final class ModuleControllerTest extends UnitTestCase
{
    /**
     * @var ModuleController&MockObject&AccessibleObjectInterface
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
     * @var RegistrationRepository&MockObject
     */
    private $registrationRepositoryMock;

    /**
     * @var EventStatisticsCalculator&MockObject
     */
    private $eventStatisticsCalculatorMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepositoryMock = $this->createMock(EventRepository::class);
        $this->registrationRepositoryMock = $this->createMock(RegistrationRepository::class);

        /** @var ModuleController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            ModuleController::class,
            ['redirect', 'forward', 'redirectToUri'],
            [$this->eventRepositoryMock, $this->registrationRepositoryMock]
        );
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);
        $this->permissionsMock = $this->createMock(Permissions::class);
        $this->subject->injectPermissions($this->permissionsMock);
        $this->eventStatisticsCalculatorMock = $this->createMock(EventStatisticsCalculator::class);
        $this->subject->injectEventStatisticsCalculator($this->eventStatisticsCalculatorMock);
    }

    protected function tearDown(): void
    {
        if (isset($GLOBALS['_GET']) && \is_array($GLOBALS['_GET'])) {
            unset($GLOBALS['_GET']['id'], $GLOBALS['_GET']['pid'], $GLOBALS['_GET']['table']);
        }
        if (isset($GLOBALS['_POST']) && \is_array($GLOBALS['_POST'])) {
            unset($GLOBALS['_POST']['id']);
        }

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
    public function overviewActionPassesPermissionsToView(): void
    {
        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', $this->permissionsMock],
                ['pageUid', self::anything()],
                ['events', self::anything()],
                ['numberOfRegistrations', self::anything()]
            );

        $this->subject->overviewAction();
    }

    /**
     * @test
     */
    public function overviewActionPassesPageUidToView(): void
    {
        $pageUid = 8;
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', $pageUid],
                ['events', self::anything()],
                ['numberOfRegistrations', self::anything()]
            );

        $this->subject->overviewAction();
    }

    /**
     * @test
     */
    public function overviewActionEnrichesEventsWithRawData(): void
    {
        $events = [new SingleEvent()];
        $this->eventRepositoryMock->expects(self::once())->method('findByPageUidInBackEndMode')
            ->with(self::anything())->willReturn($events);
        $this->eventRepositoryMock->expects(self::once())->method('enrichWithRawData')
            ->with($events);

        $this->subject->overviewAction();
    }

    /**
     * @test
     */
    public function overviewActionEnrichesEventsWithStatistics(): void
    {
        $event = new SingleEvent();
        $events = [$event];
        $this->eventRepositoryMock->expects(self::once())->method('findByPageUidInBackEndMode')
            ->with(self::anything())->willReturn($events);
        $this->eventStatisticsCalculatorMock->expects(self::once())->method('enrichWithStatistics')
            ->with($event);

        $this->subject->overviewAction();
    }

    /**
     * @test
     */
    public function overviewActionPassesEventsOnPageUidToView(): void
    {
        $pageUid = 8;
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        $events = [new SingleEvent()];
        $this->eventRepositoryMock->expects(self::once())->method('findByPageUidInBackEndMode')
            ->with($pageUid)->willReturn($events);
        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['events', $events],
                ['numberOfRegistrations', self::anything()]
            );

        $this->subject->overviewAction();
    }

    /**
     * @test
     */
    public function overviewActionPassesNumberOfRegistrationsOnPageUidToView(): void
    {
        $pageUid = 8;
        $GLOBALS['_GET']['id'] = (string)$pageUid;

        $numberOfRegistrations = 42;
        $this->registrationRepositoryMock->expects(self::once())->method('countRegularRegistrationsByPageUid')
            ->with($pageUid)->willReturn($numberOfRegistrations);
        $this->viewMock->expects(self::exactly(4))->method('assign')
            ->withConsecutive(
                ['permissions', self::anything()],
                ['pageUid', self::anything()],
                ['events', self::anything()],
                ['numberOfRegistrations', $numberOfRegistrations]
            );

        $this->subject->overviewAction();
    }
}
