<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Configuration\LegacyConfiguration;
use OliverKlee\Seminars\Controller\EventUnregistrationController;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\EventUnregistrationController
 */
final class EventUnregistrationControllerTest extends UnitTestCase
{
    /**
     * @var EventUnregistrationController&MockObject&AccessibleObjectInterface
     */
    private $subject;

    /**
     * @var TemplateView&MockObject
     */
    private $viewMock;

    /**
     * @var RegistrationManager&MockObject
     */
    private $registrationManagerMock;

    /**
     * @var Context&MockObject
     */
    private $context;

    /**
     * @var LegacyRegistration&MockObject
     */
    private $legacyRegistrationMock;

    /**
     * @var LegacyConfiguration&MockObject
     */
    private $legacyConfigurationMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registrationManagerMock = $this->createMock(RegistrationManager::class);

        /** @var EventUnregistrationController&AccessibleObjectInterface&MockObject $subject */
        $subject = $this->getAccessibleMock(
            EventUnregistrationController::class,
            ['htmlResponse', 'redirect', 'redirectToUri'],
            [$this->registrationManagerMock]
        );
        $this->subject = $subject;

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

        $this->context = $this->createMock(Context::class);
        GeneralUtility::setSingletonInstance(Context::class, $this->context);
        $this->legacyRegistrationMock = $this->createMock(LegacyRegistration::class);
        GeneralUtility::addInstance(LegacyRegistration::class, $this->legacyRegistrationMock);
        $this->legacyConfigurationMock = $this->createMock(LegacyConfiguration::class);
        GeneralUtility::addInstance(LegacyConfiguration::class, $this->legacyConfigurationMock);
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
    public function checkPrerequisitesActionWithoutRegistrationForwardsToDenyAction(): void
    {
        $result = $this->subject->checkPrerequisitesAction();

        self::assertInstanceOf(ForwardResponse::class, $result);
        self::assertSame('deny', $result->getActionName());
        self::assertSame(['warningMessageKey' => 'registrationMissing'], $result->getArguments());
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithNullRegistrationForwardsToDenyAction(): void
    {
        $result = $this->subject->checkPrerequisitesAction(null);

        self::assertInstanceOf(ForwardResponse::class, $result);
        self::assertSame('deny', $result->getActionName());
        self::assertSame(['warningMessageKey' => 'registrationMissing'], $result->getArguments());
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithRegistrationWithoutUserForwardsToDenyAction(): void
    {
        $result = $this->subject->checkPrerequisitesAction(new Registration());

        self::assertInstanceOf(ForwardResponse::class, $result);
        self::assertSame('deny', $result->getActionName());
        self::assertSame(['warningMessageKey' => 'registrationMissing'], $result->getArguments());
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithoutLoginForwardsToDenyAction(): void
    {
        $user = $this->createMock(FrontendUser::class);
        $user->method('getUid')->willReturn(3);
        $registration = new Registration();
        $registration->setUser($user);
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn(0);

        $result = $this->subject->checkPrerequisitesAction($registration);

        self::assertInstanceOf(ForwardResponse::class, $result);
        self::assertSame('deny', $result->getActionName());
        self::assertSame(['warningMessageKey' => 'registrationMissing'], $result->getArguments());
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithRegistrationByAnotherUserForwardsToDenyAction(): void
    {
        $registeredUserUid = 3;
        $user = $this->createMock(FrontendUser::class);
        $user->method('getUid')->willReturn($registeredUserUid);
        $registration = new Registration();
        $registration->setUser($user);

        $loggedInUserUid = 15;
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($loggedInUserUid);

        $result = $this->subject->checkPrerequisitesAction($registration);

        self::assertInstanceOf(ForwardResponse::class, $result);
        self::assertSame('deny', $result->getActionName());
        self::assertSame(['warningMessageKey' => 'registrationMissing'], $result->getArguments());
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithRegistrationNotPossibleForwardsToDenyAction(): void
    {
        $registeredUserUid = 3;
        $user = $this->createMock(FrontendUser::class);
        $user->method('getUid')->willReturn($registeredUserUid);
        $registration = new Registration();
        $registration->setUser($user);
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($registeredUserUid);

        $legacyEvent = $this->createMock(LegacyEvent::class);
        $this->legacyRegistrationMock->expects(self::once())->method('getSeminarObject')->willReturn($legacyEvent);
        $legacyEvent->expects(self::once())->method('isUnregistrationPossible')->willReturn(false);

        $result = $this->subject->checkPrerequisitesAction($registration);

        self::assertInstanceOf(ForwardResponse::class, $result);
        self::assertSame('deny', $result->getActionName());
        self::assertSame(['warningMessageKey' => 'noUnregistrationPossible'], $result->getArguments());
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionWithRegistrationPossibleRedirectsToConfirmAction(): void
    {
        $registeredUserUid = 3;
        $user = $this->createMock(FrontendUser::class);
        $user->method('getUid')->willReturn($registeredUserUid);
        $registration = new Registration();
        $registration->setUser($user);
        $this->context->method('getPropertyFromAspect')->with('frontend.user', 'id')->willReturn($registeredUserUid);

        $legacyEvent = $this->createMock(LegacyEvent::class);
        $this->legacyRegistrationMock->expects(self::once())->method('getSeminarObject')->willReturn($legacyEvent);
        $legacyEvent->expects(self::once())->method('isUnregistrationPossible')->willReturn(true);

        $this->subject->expects(self::once())->method('redirect')
            ->with('confirm', null, null, ['registration' => $registration]);

        $this->subject->checkPrerequisitesAction($registration);
    }

    /**
     * @test
     */
    public function denyActionPassesProvidedWarningMessageKeyToView(): void
    {
        $warningMessageKey = 'registrationMissing';
        $this->viewMock->expects(self::once())->method('assign')->with('warningMessageKey', $warningMessageKey);

        $this->subject->denyAction($warningMessageKey);
    }

    /**
     * @test
     */
    public function confirmActionPassesProvidedRegistrationToView(): void
    {
        $registration = new Registration();

        $this->viewMock->expects(self::once())->method('assign')->with('registration', $registration);

        $this->subject->confirmAction($registration);
    }

    /**
     * @test
     */
    public function unregisterActionRemovesRegistration(): void
    {
        $registrationUid = 4;
        $registration = $this->createMock(Registration::class);
        $registration->method('getUid')->willReturn($registrationUid);

        $this->registrationManagerMock->expects(self::once())
            ->method('removeRegistration')->with($registrationUid, $this->legacyConfigurationMock);

        $this->subject->unregisterAction($registration);
    }

    /**
     * @test
     */
    public function unregisterActionRedirectsToThankYouActionWithEventFromRegistration(): void
    {
        $registration = new Registration();
        $event = new SingleEvent();
        $registration->setEvent($event);

        $this->subject->expects(self::once())->method('redirect')
            ->with('thankYou', null, null, ['event' => $event]);

        $this->subject->unregisterAction($registration);
    }

    /**
     * @test
     */
    public function thankYouActionPassesProvidedRegistrationToView(): void
    {
        $event = new SingleEvent();

        $this->viewMock->expects(self::once())->method('assign')->with('event', $event);

        $this->subject->thankYouAction($event);
    }
}
