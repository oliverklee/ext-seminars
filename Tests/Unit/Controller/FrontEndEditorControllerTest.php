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
use OliverKlee\Seminars\Seo\SlugGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\HtmlResponse;
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
    use RedirectMockTrait;

    protected bool $resetSingletonInstances = true;

    /**
     * @var FrontEndEditorController&MockObject&AccessibleObjectInterface
     */
    private $subject;

    /**
     * @var TemplateView&MockObject
     */
    private TemplateView $viewMock;

    /**
     * @var EventRepository&MockObject
     */
    private EventRepository $eventRepositoryMock;

    /**
     * @var EventTypeRepository&MockObject
     */
    private EventTypeRepository $eventTypeRepositoryMock;

    /**
     * @var OrganizerRepository&MockObject
     */
    private OrganizerRepository $organizerRepositoryMock;

    /**
     * @var SpeakerRepository&MockObject
     */
    private SpeakerRepository $speakerRepositoryMock;

    /**
     * @var VenueRepository&MockObject
     */
    private VenueRepository $venueRepositoryMock;

    /**
     * @var SlugGenerator&Stub
     */
    private SlugGenerator $slugGeneratorStub;

    private Context $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepositoryMock = $this->createMock(EventRepository::class);
        $this->eventTypeRepositoryMock = $this->createMock(EventTypeRepository::class);
        $this->organizerRepositoryMock = $this->createMock(OrganizerRepository::class);
        $this->speakerRepositoryMock = $this->createMock(SpeakerRepository::class);
        $this->venueRepositoryMock = $this->createMock(VenueRepository::class);
        $this->slugGeneratorStub = $this->createStub(SlugGenerator::class);

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
                $this->slugGeneratorStub,
            ]
        );
        $this->subject = $subject;

        $responseStub = $this->createStub(HtmlResponse::class);
        $this->subject->method('htmlResponse')->willReturn($responseStub);

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

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
    public function editSingleEventActionAssignsAuxiliaryRecordsToView(): void
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

        $this->subject->editSingleEventAction($event);
    }

    /**
     * @test
     */
    public function updateSingleEventActionRedirectsToIndexAction(): void
    {
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);

        $this->mockRedirect('index');

        $this->subject->updateSingleEventAction($event);
    }

    /**
     * @test
     */
    public function updateSingleEventActionWithEventFromOtherUserThrowsException(): void
    {
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

        $event = new SingleEvent();
        $event->setOwnerUid(2);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->updateSingleEventAction($event);
    }

    /**
     * @test
     */
    public function updateSingleEventActionWithEventWithoutOwnerThrowsException(): void
    {
        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->user = ['uid' => 1];
        $this->context->setAspect('frontend.user', new UserAspect($userAuthentication));

        $event = new SingleEvent();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->subject->updateSingleEventAction($event);
    }

    /**
     * @test
     */
    public function newSingleEventActionWithNullEventAssignsNewEventToView(): void
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

        $this->subject->newSingleEventAction(null);
    }

    /**
     * @test
     */
    public function newSingleEventActionWithoutEventAssignsNewEventToView(): void
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

        $this->subject->newSingleEventAction();
    }

    /**
     * @test
     */
    public function newSingleEventActionAssignsAuxiliaryRecordsToView(): void
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

        $this->subject->newSingleEventAction($event);
    }

    /**
     * @test
     */
    public function createSingleEventActionWithPageUidInConfigurationSetsProvidedPageUid(): void
    {
        $pageUid = 42;
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);
        $this->subject->_set('settings', ['folderForCreatedEvents' => (string)$pageUid]);

        $this->stubRedirect();

        $this->subject->createSingleEventAction($event);

        self::assertSame($pageUid, $event->getPid());
    }

    /**
     * @test
     */
    public function createSingleEventActionWithoutPageUidInConfigurationSetsZeroPageUid(): void
    {
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);

        $this->stubRedirect();

        $this->subject->createSingleEventAction($event);

        self::assertSame(0, $event->getPid());
    }

    /**
     * @test
     */
    public function createSingleEventActionRedirectsToIndexAction(): void
    {
        $event = new SingleEvent();
        $event->_setProperty('uid', 1);

        $this->mockRedirect('index');

        $this->subject->createSingleEventAction($event);
    }
}
