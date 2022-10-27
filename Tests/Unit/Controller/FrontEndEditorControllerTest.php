<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\FrontEndEditorController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getAccessibleMock(
            FrontEndEditorController::class,
            ['redirect', 'forward', 'redirectToUri']
        );

        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $view = $this->viewProphecy->reveal();
        $this->subject->_set('view', $view);

        $this->eventRepositoryProphecy = $this->prophesize(EventRepository::class);
        $this->subject->injectEventRepository($this->eventRepositoryProphecy->reveal());
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
        $context = $this->createMock(Context::class);
        $context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($ownerUid);
        GeneralUtility::setSingletonInstance(Context::class, $context);

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
}
