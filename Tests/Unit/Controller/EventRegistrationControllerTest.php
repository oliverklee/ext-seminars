<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\EventRegistrationController;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * @covers \OliverKlee\Seminars\Controller\EventRegistrationController
 */
final class EventRegistrationControllerTest extends UnitTestCase
{
    /**
     * @var EventRegistrationController&MockObject&AccessibleMockObjectInterface
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

    protected function setUp(): void
    {
        parent::setUp();

        /** @var EventRegistrationController&AccessibleMockObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            EventRegistrationController::class,
            ['redirect', 'forward', 'redirectToUri']
        );
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

        $this->eventRepositoryMock = $this->createMock(EventRepository::class);
        $this->subject->injectEventRepository($this->eventRepositoryMock);
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
    public function checkPrerequisitesActionWithoutEventRedirectsToMissingEventRedirectPage(): void
    {
        $pageUid = 42;
        $this->subject->_set('settings', ['pageForMissingEvent' => (string)$pageUid]);

        $this->subject->expects(self::once())->method('redirect')->with(null, null, null, [], $pageUid);

        $this->subject->checkPrerequisitesAction();
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithNullEventRedirectsToMissingEventRedirectPage(): void
    {
        $pageUid = 42;
        $this->subject->_set('settings', ['pageForMissingEvent' => (string)$pageUid]);

        $this->subject->expects(self::once())->method('redirect')->with(null, null, null, [], $pageUid);

        $this->subject->checkPrerequisitesAction(null);
    }
}
