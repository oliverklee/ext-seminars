<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\EventRegistrationController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Service\RegistrationGuard;
use OliverKlee\Seminars\Service\RegistrationProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
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
     * @var RegistrationProcessor&MockObject
     */
    private $registrationProcesserMock;

    /**
     * @var TemplateView&MockObject
     */
    private $viewMock;

    /**
     * @var UriBuilder&MockObject
     */
    private $uriBuilderMock;

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
        $this->registrationProcesserMock = $this->createMock(RegistrationProcessor::class);
        $this->subject->injectRegistrationProcessor($this->registrationProcesserMock);

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);
        $this->uriBuilderMock = $this->createMock(UriBuilder::class);
        $this->subject->_set('uriBuilder', $this->uriBuilderMock);
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
    public function checkPrerequisitesActionForNoRegistrationPossibleAtTheMomentForwardsToDenyRegistrationAction(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->method('isRegistrationPossibleAtAnyTimeAtAll')
            ->with($event)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())->method('isRegistrationPossibleByDate')
            ->with($event)->willReturn(false);

        $this->subject->expects(self::once())->method('forward')
            ->with('denyRegistration', null, null, ['warningMessageKey' => 'noRegistrationPossibleAtTheMoment'])
            ->willThrowException(new StopActionException('forward', 1476045801));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction($event);
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionUserAlreadyRegisteredForwardsToDenyRegistrationAction(): void
    {
        $userUid = 17;
        $this->registrationGuardMock->method('getFrontEndUserUidFromSession')->willReturn($userUid);

        $event = new SingleEvent();
        $this->registrationGuardMock->method('isRegistrationPossibleAtAnyTimeAtAll')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('isRegistrationPossibleByDate')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('existsFrontEndUserUidInSession')->willReturn(true);
        $this->registrationGuardMock->expects(self::once())
            ->method('isFreeFromRegistrationConflicts')->with($event, $userUid)->willReturn(false);

        $this->subject->expects(self::once())->method('forward')
            ->with('denyRegistration', null, null, ['warningMessageKey' => 'alreadyRegistered'])
            ->willThrowException(new StopActionException('forward', 1476045801));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction($event);
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionForNoProblemsRedirectsToNewActionAndPassesEvent(): void
    {
        $userUid = 17;
        $this->registrationGuardMock->method('getFrontEndUserUidFromSession')->willReturn($userUid);

        $event = new SingleEvent();
        $this->registrationGuardMock->method('isRegistrationPossibleAtAnyTimeAtAll')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('isRegistrationPossibleByDate')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('existsFrontEndUserUidInSession')->willReturn(true);
        $this->registrationGuardMock->method('isFreeFromRegistrationConflicts')
            ->with($event, $userUid)->willReturn(true);

        $this->subject->expects(self::once())->method('redirect')
            ->with('new', null, null, ['event' => $event])
            ->willThrowException(new StopActionException('redirectToUri', 1476045828));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction($event);
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionForNoUserInSessionRedirectsLoginPageWithRedirectUrl(): void
    {
        $eventUid = 5;
        $event = $this->createMock(SingleEvent::class);
        $event->method('getUid')->willReturn($eventUid);
        $this->registrationGuardMock->method('isRegistrationPossibleAtAnyTimeAtAll')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('isRegistrationPossibleByDate')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('existsFrontEndUserUidInSession')->willReturn(false);

        $redirectUrl = 'https://example.com/current-page';
        $loginPageUrl = 'https://example.com/login-with-event-uid';
        $loginPageUid = 17;
        $this->subject->_set('settings', ['loginPage' => (string)$loginPageUid]);

        $this->uriBuilderMock->expects(self::exactly(2))->method('reset')->willReturnSelf();
        $this->uriBuilderMock->expects(self::exactly(2))->method('setCreateAbsoluteUri')->with(true)->willReturnSelf();
        $this->uriBuilderMock->expects(self::once())->method('setTargetPageUid')->with($loginPageUid)->willReturnSelf();
        $this->uriBuilderMock->expects(self::exactly(2))->method('setArguments')->withConsecutive(
            [['tx_seminars_eventregistration[event]' => $eventUid]],
            [['redirect_url' => $redirectUrl]]
        )->willReturnSelf();
        $this->uriBuilderMock->expects(self::exactly(2))->method('buildFrontendUri')
            ->willReturnOnConsecutiveCalls($redirectUrl, $loginPageUrl);

        $this->subject->expects(self::once())->method('redirectToUri')
            ->with($loginPageUrl)
            ->willThrowException(new StopActionException('redirectToUri', 1476045828));
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

    /**
     * @test
     */
    public function newActionPassesProvidedEventToView(): void
    {
        $event = new SingleEvent();

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['event', $event],
            ['registration', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionPassesProvidedRegistrationToView(): void
    {
        $registration = new Registration();

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', $registration]
        );

        $this->subject->newAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function newActionWithNullRegistrationPassesNewRegistrationToView(): void
    {
        $registration = new Registration();
        GeneralUtility::addInstance(Registration::class, $registration);

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', $registration]
        );

        $this->subject->newAction(new SingleEvent(), null);
    }

    /**
     * @test
     */
    public function newActionWithoutRegistrationPassesNewRegistrationToView(): void
    {
        $registration = new Registration();
        GeneralUtility::addInstance(Registration::class, $registration);

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', $registration]
        );

        $this->subject->newAction(new SingleEvent(), null);
    }

    /**
     * @test
     */
    public function confirmActionPassesProvidedEventToView(): void
    {
        $event = new SingleEvent();

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['event', $event],
            ['registration', self::anything()]
        );

        $this->subject->confirmAction($event, new Registration());
    }

    /**
     * @test
     */
    public function confirmActionPassesProvidedRegistrationToView(): void
    {
        $registration = new Registration();

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', $registration]
        );

        $this->subject->confirmAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionEnrichesRegistrationWithMetadata(): void
    {
        $registration = new Registration();
        $event = new SingleEvent();
        $settings = ['registration' => ['registrationRecordsStorageFolder' => '5']];
        $this->subject->_set('settings', $settings);

        $this->registrationProcesserMock->expects(self::once())->method('enrichWithMetadata')
            ->with($registration, $event, $settings);

        $this->subject->createAction($event, $registration);
    }

    /**
     * @test
     */
    public function createActionPersistsRegistration(): void
    {
        $registration = new Registration();
        $this->subject->_set('settings', []);

        $this->registrationProcesserMock->expects(self::once())->method('persist')->with($registration);

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionRedirectsToThankYouActionAndPassesEvent(): void
    {
        $event = new SingleEvent();
        $this->subject->_set('settings', []);

        $this->subject->expects(self::once())->method('redirect')
            ->with('thankYou', null, null, ['event' => $event])
            ->willThrowException(new StopActionException('redirectToUri', 1476045828));
        $this->expectException(StopActionException::class);

        $this->subject->createAction($event, new Registration());
    }

    /**
     * @test
     */
    public function thankYouActionPassesProvidedEventToView(): void
    {
        $event = new SingleEvent();

        $this->viewMock->expects(self::once())->method('assign')->with('event', $event);

        $this->subject->thankYouAction($event);
    }
}
