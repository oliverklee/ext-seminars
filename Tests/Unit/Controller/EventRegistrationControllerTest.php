<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\EventRegistrationController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Service\RegistrationGuard;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
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
     * @var RegistrationGuard&MockObject
     */
    private $registrationGuardMock;

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

        $this->registrationGuardMock = $this->createMock(RegistrationGuard::class);
        $this->subject->injectRegistrationGuard($this->registrationGuardMock);

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

        $this->subject->expects(self::once())->method('redirect')->with(null, null, null, [], $pageUid)
            ->willThrowException(new StopActionException('redirectToUri', 1476045828));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction();
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithNullEventRedirectsToMissingEventRedirectPage(): void
    {
        $pageUid = 42;
        $this->subject->_set('settings', ['pageForMissingEvent' => (string)$pageUid]);

        $this->subject->expects(self::once())->method('redirect')->with(null, null, null, [], $pageUid)
            ->willThrowException(new StopActionException('redirectToUri', 1476045828));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction(null);
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionForNoRegistrationPossibleAtAllForwardsToDenyRegistrationAction(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('isRegistrationPossibleAtAnyTimeAtAll')
            ->with($event)->willReturn(false);

        $this->subject->expects(self::once())->method('forward')
            ->with('denyRegistration', null, null, ['warningMessageKey' => 'noRegistrationPossibleAtAll'])
            ->willThrowException(new StopActionException('forward', 1476045801));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction($event);
    }

    /**
     * @test
     */
    public function denyRegistrationActionPassesProvidedWarningMessageKeyToView(): void
    {
        $warningMessageKey = 'noRegistrationPossibleAtAll';
        $this->viewMock->expects(self::once())->method('assign')->with('warningMessageKey', $warningMessageKey);

        $this->subject->denyRegistrationAction($warningMessageKey);
    }
}
