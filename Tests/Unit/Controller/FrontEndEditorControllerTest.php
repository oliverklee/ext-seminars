<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\FrontEndEditorController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\NullEventType;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\EventTypeRepository;
use OliverKlee\Seminars\Domain\Repository\OrganizerRepository;
use OliverKlee\Seminars\Domain\Repository\SpeakerRepository;
use OliverKlee\Seminars\Domain\Repository\VenueRepository;
use OliverKlee\Seminars\Tests\Unit\Controller\Fixtures\TestingQueryResult;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * @covers \OliverKlee\Seminars\Controller\FrontEndEditorController
 */
final class FrontEndEditorControllerTest extends UnitTestCase
{
    /**
     * @var FrontEndEditorController&AccessibleMockObjectInterface&MockObject
     */
    private $subject;

    /**
     * @var ObjectProphecy<TemplateView>
     */
    private $viewProphecy;

    /**
     * @var ObjectProphecy<EventRepository>
     */
    private $eventRepositoryProphecy;

    /**
     * @var ObjectProphecy<EventTypeRepository>
     */
    private $eventTypeRepositoryProphecy;

    /**
     * @var ObjectProphecy<OrganizerRepository>
     */
    private $organizerRepositoryProphecy;

    /**
     * @var ObjectProphecy<SpeakerRepository>
     */
    private $speakerRepositoryProphecy;

    /**
     * @var ObjectProphecy<VenueRepository>
     */
    private $venueRepositoryProphecy;

    /**
     * @var Context&MockObject
     */
    private $context;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FrontEndEditorController&AccessibleMockObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(FrontEndEditorController::class, ['redirect', 'forward', 'redirectToUri']);
        $this->subject = $subject;

        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $view = $this->viewProphecy->reveal();
        $this->subject->_set('view', $view);

        $this->eventRepositoryProphecy = $this->prophesize(EventRepository::class);
        $this->subject->injectEventRepository($this->eventRepositoryProphecy->reveal());
        $this->eventTypeRepositoryProphecy = $this->prophesize(EventTypeRepository::class);
        $this->subject->injectEventTypeRepository($this->eventTypeRepositoryProphecy->reveal());
        $this->organizerRepositoryProphecy = $this->prophesize(OrganizerRepository::class);
        $this->subject->injectOrganizerRepository($this->organizerRepositoryProphecy->reveal());
        $this->speakerRepositoryProphecy = $this->prophesize(SpeakerRepository::class);
        $this->subject->injectSpeakerRepository($this->speakerRepositoryProphecy->reveal());
        $this->venueRepositoryProphecy = $this->prophesize(VenueRepository::class);
        $this->subject->injectVenueRepository($this->venueRepositoryProphecy->reveal());

        $this->context = $this->createMock(Context::class);
        GeneralUtility::setSingletonInstance(Context::class, $this->context);
    }

    protected function tearDown(): void
    {
        // purge FIFO buffer
        GeneralUtility::makeInstance(SingleEvent::class);
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
    public function indexActionsAssignsEventsOwnedByLoggedInUserToView(): void
    {
        $ownerUid = 42;
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($ownerUid);

        $events = [new SingleEvent()];
        $this->eventRepositoryProphecy->findSingleEventsByOwnerUid($ownerUid)->willReturn($events);

        $this->viewProphecy->assign('events', $events)->shouldBeCalled();

        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function editActionAssignsProvidedEventToView(): void
    {
        $event = new SingleEvent();
        $this->viewProphecy->assign('event', $event)->shouldBeCalled();

        $this->stubAuxiliaryRecordAssignments();

        $this->subject->editAction($event);
    }

    private function stubAuxiliaryRecordAssignments(): void
    {
        $this->eventTypeRepositoryProphecy->findAllPlusNullEventType()->willReturn([]);
        $this->viewProphecy->assign('eventTypes', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('organizers', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('speakers', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('venues', Argument::any())->shouldBeCalled();
    }

    /**
     * @test
     */
    public function editActionAssignsAuxiliaryRecordsToView(): void
    {
        $event = new SingleEvent();
        $this->viewProphecy->assign('event', Argument::any())->shouldBeCalled();

        /** @var array<int, EventType> $eventTypes */
        $eventTypes = [new NullEventType()];
        $this->eventTypeRepositoryProphecy->findAllPlusNullEventType()->willReturn($eventTypes);
        $this->viewProphecy->assign('eventTypes', $eventTypes)->shouldBeCalled();

        /** @var TestingQueryResult<Organizer> $organizers */
        $organizers = new TestingQueryResult();
        $this->organizerRepositoryProphecy->findAll()->willReturn($organizers);
        $this->viewProphecy->assign('organizers', $organizers)->shouldBeCalled();

        /** @var TestingQueryResult<Speaker> $speakers */
        $speakers = new TestingQueryResult();
        $this->speakerRepositoryProphecy->findAll()->willReturn($speakers);
        $this->viewProphecy->assign('speakers', $speakers)->shouldBeCalled();

        /** @var TestingQueryResult<Venue> $venues */
        $venues = new TestingQueryResult();
        $this->venueRepositoryProphecy->findAll()->willReturn($venues);
        $this->viewProphecy->assign('venues', $venues)->shouldBeCalled();

        $this->subject->editAction($event);
    }

    /**
     * @test
     */
    public function editActionWithEventFromOtherUserThrowsException(): void
    {
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(1);
        $event = new SingleEvent();
        $event->setOwnerUid(2);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->editAction($event);
    }

    /**
     * @test
     */
    public function editActionWithEventWithoutOwnerThrowsException(): void
    {
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(1);
        $event = new SingleEvent();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->editAction($event);
    }

    /**
     * @test
     */
    public function updateActionPersistsProvidedEvent(): void
    {
        $event = new SingleEvent();
        $this->eventRepositoryProphecy->update($event)->shouldBeCalled();
        $this->eventRepositoryProphecy->persistAll()->shouldBeCalled();

        $this->subject->updateAction($event);
    }

    /**
     * @test
     */
    public function updateActionRedirectsToIndexAction(): void
    {
        $event = new SingleEvent();
        $this->subject->expects(self::once())->method('redirect')->with('index');

        $this->subject->updateAction($event);
    }

    /**
     * @test
     */
    public function updateActionWithEventFromOtherUserThrowsException(): void
    {
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(1);
        $event = new SingleEvent();
        $event->setOwnerUid(2);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->updateAction($event);
    }

    /**
     * @test
     */
    public function updateActionWithEventWithoutOwnerThrowsException(): void
    {
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(1);
        $event = new SingleEvent();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->updateAction($event);
    }

    /**
     * @test
     */
    public function newActionWithEventAssignsProvidedEventToView(): void
    {
        $event = new SingleEvent();
        $this->viewProphecy->assign('event', $event)->shouldBeCalled();
        $this->stubAuxiliaryRecordAssignments();

        $this->subject->newAction($event);
    }

    /**
     * @test
     */
    public function newActionWithNullEventAssignsNewEventToView(): void
    {
        $event = new SingleEvent();
        GeneralUtility::addInstance(SingleEvent::class, $event);
        $this->viewProphecy->assign('event', $event)->shouldBeCalled();
        $this->stubAuxiliaryRecordAssignments();

        $this->subject->newAction(null);
    }

    /**
     * @test
     */
    public function newActionWithoutEventAssignsNewEventToView(): void
    {
        $event = new SingleEvent();
        GeneralUtility::addInstance(SingleEvent::class, $event);
        $this->viewProphecy->assign('event', $event)->shouldBeCalled();
        $this->stubAuxiliaryRecordAssignments();

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionAssignsAuxiliaryRecordsToView(): void
    {
        $event = new SingleEvent();
        $this->viewProphecy->assign('event', Argument::any())->shouldBeCalled();

        /** @var array<int, EventType> $eventTypes */
        $eventTypes = [new NullEventType()];
        $this->eventTypeRepositoryProphecy->findAllPlusNullEventType()->willReturn($eventTypes);
        $this->viewProphecy->assign('eventTypes', $eventTypes)->shouldBeCalled();

        /** @var TestingQueryResult<Organizer> $organizers */
        $organizers = new TestingQueryResult();
        $this->organizerRepositoryProphecy->findAll()->willReturn($organizers);
        $this->viewProphecy->assign('organizers', $organizers)->shouldBeCalled();

        /** @var TestingQueryResult<Speaker> $speakers */
        $speakers = new TestingQueryResult();
        $this->speakerRepositoryProphecy->findAll()->willReturn($speakers);
        $this->viewProphecy->assign('speakers', $speakers)->shouldBeCalled();

        /** @var TestingQueryResult<Venue> $venues */
        $venues = new TestingQueryResult();
        $this->venueRepositoryProphecy->findAll()->willReturn($venues);
        $this->viewProphecy->assign('venues', $venues)->shouldBeCalled();

        $this->subject->newAction($event);
    }

    /**
     * @test
     */
    public function createActionSetsCurrentUserAsOwner(): void
    {
        $ownerUid = 42;
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($ownerUid);
        $event = new SingleEvent();

        $this->subject->createAction($event);

        self::assertSame($ownerUid, $event->getOwnerUid());
    }

    /**
     * @test
     */
    public function createActionWithPageUidInConfigurationSetsProvidedPageUid(): void
    {
        $pageUid = 42;
        $event = new SingleEvent();
        $this->subject->_set('settings', ['folderForCreatedEvents' => (string)$pageUid]);

        $this->subject->createAction($event);

        self::assertSame($pageUid, $event->getPid());
    }

    /**
     * @test
     */
    public function createActionWithoutPageUidInConfigurationSetsZeroPageUid(): void
    {
        $event = new SingleEvent();

        $this->subject->createAction($event);

        self::assertSame(0, $event->getPid());
    }

    /**
     * @test
     */
    public function createActionPersistsEvent(): void
    {
        $event = new SingleEvent();

        $this->eventRepositoryProphecy->add($event)->shouldBeCalled();
        $this->eventRepositoryProphecy->persistAll()->shouldBeCalled();

        $this->subject->createAction($event);
    }

    /**
     * @test
     */
    public function createActionRedirectsToIndexAction(): void
    {
        $event = new SingleEvent();

        $this->subject->expects(self::once())->method('redirect')->with('index');

        $this->subject->createAction($event);
    }
}
