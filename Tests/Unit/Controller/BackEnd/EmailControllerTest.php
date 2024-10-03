<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\GeneralEventMailForm;
use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Controller\BackEnd\EmailController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Tests\Unit\Controller\RedirectMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\BackEnd\EmailController
 * @covers \OliverKlee\Seminars\Controller\BackEnd\PermissionsTrait
 */
final class EmailControllerTest extends UnitTestCase
{
    use BackEndControllerTestHelper;
    use RedirectMockTrait;

    protected bool $resetSingletonInstances = true;

    /**
     * @var EmailController&MockObject&AccessibleObjectInterface
     */
    private EmailController $subject;

    /**
     * @var TemplateView&MockObject
     */
    private TemplateView $viewMock;

    /**
     * @var Permissions&MockObject
     */
    private Permissions $permissionsMock;

    /**
     * @var GeneralEventMailForm&MockObject
     */
    private GeneralEventMailForm $emailServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $moduleTemplateFactory = $this->createModuleTemplateFactory();
        $methodsToMock = ['htmlResponse', 'redirect', 'redirectToUri'];
        /** @var EmailController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(EmailController::class, $methodsToMock, [$moduleTemplateFactory]);
        $this->subject = $subject;

        $request = $this->createStub(ServerRequest::class);
        $this->subject->_set('request', $request);

        $responseStub = $this->createStub(HtmlResponse::class);
        $this->subject->method('htmlResponse')->willReturn($responseStub);

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->viewMock->method('render')->willReturn('rendered view');
        $this->subject->_set('view', $this->viewMock);

        $this->permissionsMock = $this->createMock(Permissions::class);
        $this->subject->injectPermissions($this->permissionsMock);

        $this->emailServiceMock = $this->createMock(GeneralEventMailForm::class);
        GeneralUtility::addInstance(GeneralEventMailForm::class, $this->emailServiceMock);
    }

    public function tearDown(): void
    {
        unset($_POST['subject'], $_POST['emailBody'], $GLOBALS['LANG'], $GLOBALS['BE_USER']);
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @param positive-int $eventUid
     *
     * @return SingleEvent&MockObject
     */
    private function buildSingleEventMockWithUid(int $eventUid): SingleEvent
    {
        $event = $this->createMock(SingleEvent::class);
        $event->method('getUid')->willReturn($eventUid);

        return $event;
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
    public function composeActionWithoutReadPermissionsForEventsThrowsException(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(false);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1671020157);
        $this->expectExceptionMessage('Missing read permissions for events.');

        $this->subject->composeAction(new SingleEvent(), 1);
    }

    /**
     * @test
     */
    public function composeActionWithoutReadPermissionsForRegistrationsThrowsException(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1671020198);
        $this->expectExceptionMessage('Missing read permissions for registrations.');

        $this->subject->composeAction(new SingleEvent(), 1);
    }

    /**
     * @test
     */
    public function composeActionReturnsHtmlResponse(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $event = new SingleEvent();
        $result = $this->subject->composeAction($event, 1);

        self::assertInstanceOf(HtmlResponse::class, $result);
    }

    /**
     * @test
     */
    public function composeActionPassesProvidedEventToView(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $event = new SingleEvent();
        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', $event],
            ['pageUid', self::anything()],
            ['subject', ''],
            ['body', self::anything()]
        );

        $this->subject->composeAction($event, 1);
    }

    /**
     * @test
     */
    public function composeActionPassesProvidedPageUidToView(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $pageUid = 5;
        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['pageUid', $pageUid],
            ['subject', ''],
            ['body', self::anything()]
        );

        $this->subject->composeAction(new SingleEvent(), $pageUid);
    }

    /**
     * @test
     */
    public function composeActionWithoutSubjectOrBodyPassesEmptySubjectToView(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['pageUid', self::anything()],
            ['subject', ''],
            ['body', self::anything()]
        );

        $this->subject->composeAction(new SingleEvent(), 1);
    }

    /**
     * @test
     */
    public function composeActionWithoutSubjectOrBodyPassesEmptyBodyToView(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['pageUid', self::anything()],
            ['subject', self::anything()],
            ['body', '']
        );

        $this->subject->composeAction(new SingleEvent(), 1);
    }

    /**
     * @test
     */
    public function composeActionPassesProvidedSubjectToView(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $subject = 'Test subject';
        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['pageUid', self::anything()],
            ['subject', $subject],
            ['body', self::anything()]
        );

        $this->subject->composeAction(new SingleEvent(), 1, $subject, '');
    }

    /**
     * @test
     */
    public function composeActionPassesProvidedBodyToView(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $body = 'Test body';
        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['pageUid', self::anything()],
            ['subject', self::anything()],
            ['body', $body]
        );

        $this->subject->composeAction(new SingleEvent(), 1, '', $body);
    }

    /**
     * @test
     */
    public function sendActionWithoutReadPermissionsForEventsThrowsException(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(false);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1671020157);
        $this->expectExceptionMessage('Missing read permissions for events.');

        $this->subject->sendAction(new SingleEvent(), '', '');
    }

    /**
     * @test
     */
    public function sendActionWithoutReadPermissionsForRegistrationsThrowsException(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1671020198);
        $this->expectExceptionMessage('Missing read permissions for registrations.');

        $this->subject->sendAction(new SingleEvent(), '', '');
    }

    /**
     * @test
     */
    public function sendActionSetsProvidedSubjectAndBody(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);
        $this->stubRedirect();

        $eventUid = 9;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $subject = 'email subject';
        $body = 'email body';

        $this->emailServiceMock->expects(self::once())->method('setPostData')
            ->with(['subject' => $subject, 'messageBody' => $body]);

        $this->subject->sendAction($event, $subject, $body);
    }

    /**
     * @test
     */
    public function sendActionSendsEmail(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);
        $this->stubRedirect();

        $eventUid = 9;
        $event = $this->buildSingleEventMockWithUid($eventUid);
        $this->emailServiceMock->expects(self::once())->method('sendEmailToAttendees');

        $this->subject->sendAction($event, 'email subject', 'email body');
    }

    /**
     * @test
     */
    public function sendActionRedirectsToOverview(): void
    {
        $this->permissionsMock->method('hasReadAccessToEvents')->willReturn(true);
        $this->permissionsMock->method('hasReadAccessToRegistrations')->willReturn(true);

        $eventUid = 9;
        $event = $this->buildSingleEventMockWithUid($eventUid);

        $this->mockRedirect('overview', 'BackEnd\\Module');

        if ((new Typo3Version())->getMajorVersion() < 12) {
            $this->subject->sendAction($event, '', '');
        } else {
            $result = $this->subject->sendAction($event, '', '');
            self::assertInstanceOf(RedirectResponse::class, $result);
        }
    }
}
