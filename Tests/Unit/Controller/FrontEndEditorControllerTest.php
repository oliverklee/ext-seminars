<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use OliverKlee\Seminars\Controller\FrontEndEditorController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventTypeInterface;
use OliverKlee\Seminars\Domain\Model\NullEventType;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\EventTypeRepository;
use OliverKlee\Seminars\Domain\Repository\OrganizerRepository;
use OliverKlee\Seminars\Domain\Repository\SpeakerRepository;
use OliverKlee\Seminars\Domain\Repository\VenueRepository;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\FrontEndEditorController
 */
final class FrontEndEditorControllerTest extends UnitTestCase
{
    /**
     * @var FrontEndEditorController&MockObject&AccessibleObjectInterface
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
     * @var EventTypeRepository&MockObject
     */
    private $eventTypeRepositoryMock;

    /**
     * @var OrganizerRepository&MockObject
     */
    private $organizerRepositoryMock;

    /**
     * @var SpeakerRepository&MockObject
     */
    private $speakerRepositoryMock;

    /**
     * @var VenueRepository&MockObject
     */
    private $venueRepositoryMock;

    /**
     * @var Context&MockObject
     */
    private $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepositoryMock = $this->createMock(EventRepository::class);
        $this->eventTypeRepositoryMock = $this->createMock(EventTypeRepository::class);
        $this->organizerRepositoryMock = $this->createMock(OrganizerRepository::class);
        $this->speakerRepositoryMock = $this->createMock(SpeakerRepository::class);
        $this->venueRepositoryMock = $this->createMock(VenueRepository::class);

        $methodsToMock = ['htmlResponse', 'redirect', 'redirectToUri'];
        /** @var FrontEndEditorController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            FrontEndEditorController::class,
            $methodsToMock,
            [
                $this->eventRepositoryMock,
                $this->eventTypeRepositoryMock,
                $this->organizerRepositoryMock,
                $this->speakerRepositoryMock,
                $this->venueRepositoryMock,
            ]
        );
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

        $this->context = $this->createMock(Context::class);
        GeneralUtility::setSingletonInstance(Context::class, $this->context);
    }

    protected function tearDown(): void
    {
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
        $this->eventRepositoryMock->method('findSingleEventsByOwnerUid')->with($ownerUid)->willReturn($events);

        $this->viewMock->expects(self::once())->method('assign')->with('events', $events);

        $this->subject->indexAction();
    }

    /**
     * @test
     */
    public function editActionAssignsProvidedEventToView(): void
    {
        $event = new SingleEvent();
        $this->eventTypeRepositoryMock->method('findAllPlusNullEventType')->willReturn([]);

        $this->viewMock->expects(self::atLeast(5))->method('assign')->withConsecutive(
            ['event', $event],
            ['eventTypes', self::anything()],
            ['organizers', self::anything()],
            ['speakers', self::anything()],
            ['venues', self::anything()]
        );

        $this->subject->editAction($event);
    }

    /**
     * @test
     */
    public function editActionAssignsAuxiliaryRecordsToView(): void
    {
        $event = new SingleEvent();

        /** @var list<EventTypeInterface> $eventTypes */
        $eventTypes = [new NullEventType()];
        $this->eventTypeRepositoryMock->method('findAllPlusNullEventType')->willReturn($eventTypes);

        $organizers = $this->createStub(QueryResultInterface::class);
        $this->organizerRepositoryMock->method('findAll')->willReturn($organizers);

        $speakers = $this->createStub(QueryResultInterface::class);
        $this->speakerRepositoryMock->method('findAll')->willReturn($speakers);

        $venues = $this->createStub(QueryResultInterface::class);
        $this->venueRepositoryMock->method('findAll')->willReturn($venues);

        $this->viewMock->expects(self::atLeast(5))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['eventTypes', $eventTypes],
            ['organizers', $organizers],
            ['speakers', $speakers],
            ['venues', $venues]
        );

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
        $this->eventRepositoryMock->expects(self::once())->method('update')->with($event);
        $this->eventRepositoryMock->expects(self::once())->method('persistAll');

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
        $this->eventTypeRepositoryMock->method('findAllPlusNullEventType')->willReturn([]);

        $this->viewMock->expects(self::atLeast(5))->method('assign')->withConsecutive(
            ['event', $event],
            ['eventTypes', self::anything()],
            ['organizers', self::anything()],
            ['speakers', self::anything()],
            ['venues', self::anything()]
        );

        $this->subject->newAction($event);
    }

    /**
     * @test
     */
    public function newActionWithNullEventAssignsNewEventToView(): void
    {
        $event = new SingleEvent();
        GeneralUtility::addInstance(SingleEvent::class, $event);
        $this->eventTypeRepositoryMock->method('findAllPlusNullEventType')->willReturn([]);

        $this->viewMock->expects(self::atLeast(5))->method('assign')->withConsecutive(
            ['event', $event],
            ['eventTypes', self::anything()],
            ['organizers', self::anything()],
            ['speakers', self::anything()],
            ['venues', self::anything()]
        );

        $this->subject->newAction(null);
    }

    /**
     * @test
     */
    public function newActionWithoutEventAssignsNewEventToView(): void
    {
        $event = new SingleEvent();
        GeneralUtility::addInstance(SingleEvent::class, $event);
        $this->eventTypeRepositoryMock->method('findAllPlusNullEventType')->willReturn([]);

        $this->viewMock->expects(self::atLeast(5))->method('assign')->withConsecutive(
            ['event', $event],
            ['eventTypes', self::anything()],
            ['organizers', self::anything()],
            ['speakers', self::anything()],
            ['venues', self::anything()]
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionAssignsAuxiliaryRecordsToView(): void
    {
        $event = new SingleEvent();

        /** @var list<EventTypeInterface> $eventTypes */
        $eventTypes = [new NullEventType()];
        $this->eventTypeRepositoryMock->method('findAllPlusNullEventType')->willReturn($eventTypes);

        $organizers = $this->createStub(QueryResultInterface::class);
        $this->organizerRepositoryMock->method('findAll')->willReturn($organizers);

        $speakers = $this->createStub(QueryResultInterface::class);
        $this->speakerRepositoryMock->method('findAll')->willReturn($speakers);

        $venues = $this->createStub(QueryResultInterface::class);
        $this->venueRepositoryMock->method('findAll')->willReturn($venues);

        $this->viewMock->expects(self::atLeast(5))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['eventTypes', $eventTypes],
            ['organizers', $organizers],
            ['speakers', $speakers],
            ['venues', $venues]
        );

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

        $this->eventRepositoryMock->expects(self::once())->method('add')->with($event);
        $this->eventRepositoryMock->expects(self::once())->method('persistAll');

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
