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
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\FrontEndEditorController
 */
final class FrontEndEditorControllerTest extends UnitTestCase
{
    /**
     * @var bool
     */
    protected $resetSingletonInstances = true;

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
     * @var Context
     */
    private $context;

    protected function setUp(): void
    {
        parent::setUp();

        $methodsToMock = ['htmlResponse', 'redirect', 'redirectToUri'];
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $methodsToMock[] = 'forward';
        }
        /** @var FrontEndEditorController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(FrontEndEditorController::class, $methodsToMock);
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

        $this->eventRepositoryMock = $this->createMock(EventRepository::class);
        $this->subject->injectEventRepository($this->eventRepositoryMock);
        $this->eventTypeRepositoryMock = $this->createMock(EventTypeRepository::class);
        $this->subject->injectEventTypeRepository($this->eventTypeRepositoryMock);
        $this->organizerRepositoryMock = $this->createMock(OrganizerRepository::class);
        $this->subject->injectOrganizerRepository($this->organizerRepositoryMock);
        $this->speakerRepositoryMock = $this->createMock(SpeakerRepository::class);
        $this->subject->injectSpeakerRepository($this->speakerRepositoryMock);
        $this->venueRepositoryMock = $this->createMock(VenueRepository::class);
        $this->subject->injectVenueRepository($this->venueRepositoryMock);

        $this->context = GeneralUtility::makeInstance(Context::class);
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
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

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
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

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
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

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
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

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
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => $ownerUid];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

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
