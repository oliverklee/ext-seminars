<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\EventRegistrationController;
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

    /**
     * @test
     */
    public function denyRegistrationActionPassesProvidedWarningMessageKeyToView(): void
    {
        $warningMessageKey = 'not-possible';
        $this->viewMock->expects(self::once())->method('assign')->with('warningMessageKey', $warningMessageKey);

        $this->subject->denyRegistrationAction($warningMessageKey);
    }
}
