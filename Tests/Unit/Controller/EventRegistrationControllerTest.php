<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Controller\EventRegistrationController;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Service\OneTimeAccountConnector;
use OliverKlee\Seminars\Service\PriceFinder;
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
     * @var OneTimeAccountConnector&MockObject
     */
    private $oneTimeAccountConnectorMock;

    /**
     * @var PriceFinder&MockObject
     */
    private $priceFinderMock;

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
        $this->oneTimeAccountConnectorMock = $this->createMock(OneTimeAccountConnector::class);
        $this->subject->injectOneTimeAccountConnector($this->oneTimeAccountConnectorMock);
        $this->priceFinderMock = $this->createMock(PriceFinder::class);
        $this->subject->injectPriceFinder($this->priceFinderMock);

        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);
        $this->uriBuilderMock = $this->createMock(UriBuilder::class);
        $this->subject->_set('uriBuilder', $this->uriBuilderMock);
        $this->subject->_set('settings', []);
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
    public function checkPrerequisitesActionForUserAlreadyRegisteredForwardsToDenyRegistrationAction(): void
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
    public function checkPrerequisitesActionForFullyBookedEventForwardsToDenyRegistrationAction(): void
    {
        $userUid = 17;
        $this->registrationGuardMock->method('getFrontEndUserUidFromSession')->willReturn($userUid);

        $event = new SingleEvent();
        $this->registrationGuardMock->method('isRegistrationPossibleAtAnyTimeAtAll')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('isRegistrationPossibleByDate')->with($event)->willReturn(true);
        $this->registrationGuardMock->method('existsFrontEndUserUidInSession')->willReturn(true);
        $this->registrationGuardMock->method('isFreeFromRegistrationConflicts')
            ->with($event, $userUid)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())
            ->method('getVacancies')->with($event)->willReturn(0);

        $this->subject->expects(self::once())->method('forward')
            ->with('denyRegistration', null, null, ['warningMessageKey' => 'fullyBooked'])
            ->willThrowException(new StopActionException('forward', 1476045801));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction($event);
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionForNoProblemsAndInfiniteVacanciesRedirectsToNewActionAndPassesEvent(): void
    {
        $userUid = 17;
        $this->registrationGuardMock->method('getFrontEndUserUidFromSession')->willReturn($userUid);

        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('isRegistrationPossibleAtAnyTimeAtAll')
            ->with($event)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())->method('isRegistrationPossibleByDate')
            ->with($event)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())->method('existsFrontEndUserUidInSession')->willReturn(true);
        $this->registrationGuardMock->expects(self::once())->method('isFreeFromRegistrationConflicts')
            ->with($event, $userUid)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())
            ->method('getVacancies')->with($event)->willReturn(null);

        $this->subject->expects(self::once())->method('redirect')
            ->with('new', null, null, ['event' => $event])
            ->willThrowException(new StopActionException('redirectToUri', 1476045828));
        $this->expectException(StopActionException::class);

        $this->subject->checkPrerequisitesAction($event);
    }

    /**
     * @test
     */
    public function checkPrerequisitesActionForNoProblemsAndNonZeroVacanciesRedirectsToNewActionAndPassesEvent(): void
    {
        $userUid = 17;
        $this->registrationGuardMock->method('getFrontEndUserUidFromSession')->willReturn($userUid);

        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('isRegistrationPossibleAtAnyTimeAtAll')
            ->with($event)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())->method('isRegistrationPossibleByDate')
            ->with($event)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())->method('existsFrontEndUserUidInSession')->willReturn(true);
        $this->registrationGuardMock->expects(self::once())->method('isFreeFromRegistrationConflicts')
            ->with($event, $userUid)->willReturn(true);
        $this->registrationGuardMock->expects(self::once())
            ->method('getVacancies')->with($event)->willReturn(1);

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
    public function newActionAssertsBookableEventType(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('assertBookableEventType')->with($event);

        $this->subject->newAction(new SingleEvent(), new Registration());
    }

    /**
     * @test
     */
    public function newActionWithSingleEventPassesProvidedEventToView(): void
    {
        $event = new SingleEvent();

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', $event],
            ['registration', self::anything()],
            ['maximumBookableSeats', self::anything()],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionWithEventDatePassesProvidedEventToView(): void
    {
        $event = new EventDate();

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', $event],
            ['registration', self::anything()],
            ['maximumBookableSeats', self::anything()],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionWithNullRegistrationPassesNewRegistrationToView(): void
    {
        $registration = new Registration();
        GeneralUtility::addInstance(Registration::class, $registration);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', $registration],
            ['maximumBookableSeats', self::anything()],
            ['applicablePrices', self::anything()]
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

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', $registration],
            ['maximumBookableSeats', self::anything()],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction(new SingleEvent(), null);
    }

    /**
     * @test
     */
    public function newActionWithoutRegistrationPassesUsesFirstApplicablePriceForNewRegistration(): void
    {
        $event = new SingleEvent();
        $price1 = new Price(100.0, 'labelKey', Price::PRICE_EARLY_BIRD);
        $price2 = new Price(75.0, 'labelKey', Price::PRICE_SPECIAL_EARLY_BIRD);
        $applicablePrices = [Price::PRICE_EARLY_BIRD => $price1, Price::PRICE_SPECIAL_EARLY_BIRD => $price2];
        $this->priceFinderMock->expects(self::once())->method('findApplicablePrices')->with($event)
            ->willReturn($applicablePrices);

        $registration = new Registration();
        GeneralUtility::addInstance(Registration::class, $registration);

        $this->subject->newAction($event);

        self::assertSame($price1->getPriceCode(), $registration->getPriceCode());
    }

    /**
     * @test
     */
    public function newActionWithoutSettingForMaximumBookableSeatsAndUnlimitedVacanciesPassesTenToView(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('getVacancies')->with($event)->willReturn(null);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['maximumBookableSeats', 10],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionWithoutSettingForMaximumBookableSeatsAndMoreThanTenVacanciesPassesTenToView(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('getVacancies')->with($event)->willReturn(11);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['maximumBookableSeats', 10],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionWithoutSettingForMaximumBookableSeatsAndLessThanTenVacanciesPassesVacanciesToView(): void
    {
        $event = new SingleEvent();
        $vacancies = 9;
        $this->registrationGuardMock->expects(self::once())->method('getVacancies')
            ->with($event)->willReturn($vacancies);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['maximumBookableSeats', $vacancies],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionWithInfiniteVacanciesPassesMaximumBookableSeatsFromSettingsToView(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('getVacancies')
            ->with($event)->willReturn(null);

        $maximumBookableSeats = 15;
        $this->subject->_set('settings', ['maximumBookableSeats' => (string)$maximumBookableSeats]);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['maximumBookableSeats', $maximumBookableSeats],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionWithMoreVacanciesPassesMaximumBookableSeatsFromSettingsToView(): void
    {
        $event = new SingleEvent();
        $vacancies = 16;
        $this->registrationGuardMock->expects(self::once())->method('getVacancies')
            ->with($event)->willReturn($vacancies);

        $maximumBookableSeats = 15;
        $this->subject->_set('settings', ['maximumBookableSeats' => (string)$maximumBookableSeats]);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['maximumBookableSeats', $maximumBookableSeats],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionPassesWithFewerVacanciesPassesActualVacanciesAsBookableSeatsToView(): void
    {
        $event = new SingleEvent();
        $vacancies = 9;
        $this->registrationGuardMock->expects(self::once())->method('getVacancies')
            ->with($event)->willReturn($vacancies);

        $maximumBookableSeats = 15;
        $this->subject->_set('settings', ['maximumBookableSeats' => (string)$maximumBookableSeats]);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['maximumBookableSeats', $vacancies],
            ['applicablePrices', self::anything()]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @test
     */
    public function newActionPassesApplicablePricesToView(): void
    {
        $event = new SingleEvent();
        $applicablePrices = [new Price(0.0, 'labelKey', Price::PRICE_STANDARD)];
        $this->priceFinderMock->expects(self::once())->method('findApplicablePrices')->with($event)
            ->willReturn($applicablePrices);

        $this->viewMock->expects(self::exactly(4))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['maximumBookableSeats', self::anything()],
            ['applicablePrices', $applicablePrices]
        );

        $this->subject->newAction($event, new Registration());
    }

    /**
     * @return array<string, array{0: bool}>
     */
    public function booleanDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     * @dataProvider booleanDataProvider
     */
    public function newActionWithoutRegistrationKeepsRegisteredThemselvesUnchanged(bool $registeredThemselves): void
    {
        $this->subject->_set('settings', ['registerThemselvesDefault' => ($registeredThemselves ? '1' : '0')]);

        $registration = new Registration();
        GeneralUtility::addInstance(Registration::class, $registration);

        $this->subject->newAction(new SingleEvent());

        self::assertSame($registeredThemselves, $registration->hasRegisteredThemselves());
    }

    /**
     * @test
     */
    public function newActionWithoutRegistrationAndWithoutRegisteredThemselvesSettingSetsItToTrue(): void
    {
        $registration = new Registration();
        GeneralUtility::addInstance(Registration::class, $registration);

        $this->subject->newAction(new SingleEvent());

        self::assertTrue($registration->hasRegisteredThemselves());
    }

    /**
     * @test
     * @dataProvider booleanDataProvider
     */
    public function newActionWithoutRegistrationUsesRegisteredThemselvesFromSettings(bool $registeredThemselves): void
    {
        $registration = new Registration();
        $registration->setRegisteredThemselves($registeredThemselves);

        $this->subject->newAction(new SingleEvent(), $registration);

        self::assertSame($registeredThemselves, $registration->hasRegisteredThemselves());
    }

    /**
     * @test
     */
    public function confirmActionAssertsBookableEventType(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('assertBookableEventType')->with($event);

        $this->subject->confirmAction($event, new Registration());
    }

    /**
     * @test
     */
    public function confirmActionActionEnrichesRegistrationWithMetadata(): void
    {
        $registration = new Registration();
        $event = new SingleEvent();
        $settings = ['registration' => ['registrationRecordsStorageFolder' => '5']];
        $this->subject->_set('settings', $settings);

        $this->registrationProcesserMock->expects(self::once())->method('enrichWithMetadata')
            ->with($registration, $event, $settings);

        $this->subject->confirmAction($event, $registration);
    }

    /**
     * @test
     */
    public function confirmActionCalculatesTotalPrice(): void
    {
        $registration = new Registration();
        $this->registrationProcesserMock->expects(self::once())->method('calculateTotalPrice')
            ->with($registration);

        $this->subject->confirmAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function confirmActionWithSingleEventPassesProvidedEventToView(): void
    {
        $event = new SingleEvent();

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['event', $event],
            ['registration', self::anything()],
            ['applicablePrices', self::anything()]
        );

        $this->subject->confirmAction($event, new Registration());
    }

    /**
     * @test
     */
    public function confirmActionWithEventDatePassesProvidedEventToView(): void
    {
        $event = new EventDate();

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['event', $event],
            ['registration', self::anything()],
            ['applicablePrices', self::anything()]
        );

        $this->subject->confirmAction($event, new Registration());
    }

    /**
     * @test
     */
    public function confirmActionPassesProvidedRegistrationToView(): void
    {
        $registration = new Registration();

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', $registration],
            ['applicablePrices', self::anything()]
        );

        $this->subject->confirmAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function confirmActionPassesApplicablePricesToView(): void
    {
        $event = new SingleEvent();
        $applicablePrices = [new Price(0.0, 'labelKey', Price::PRICE_STANDARD)];
        $this->priceFinderMock->expects(self::once())->method('findApplicablePrices')->with($event)
            ->willReturn($applicablePrices);

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['event', self::anything()],
            ['registration', self::anything()],
            ['applicablePrices', $applicablePrices]
        );

        $this->subject->confirmAction($event, new Registration());
    }

    /**
     * @test
     */
    public function createActionAssertsBookableEventType(): void
    {
        $event = new SingleEvent();
        $this->registrationGuardMock->expects(self::once())->method('assertBookableEventType')->with($event);

        $this->subject->createAction($event, new Registration());
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
    public function createActionCalculatesTotalPrice(): void
    {
        $registration = new Registration();
        $this->registrationProcesserMock->expects(self::once())->method('calculateTotalPrice')
            ->with($registration);

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionCreatesRegistrationTitle(): void
    {
        $registration = new Registration();

        $this->registrationProcesserMock->expects(self::once())->method('createTitle')->with($registration);

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionWithoutUserStorageSettingCreatesAdditionalPersonsWithZeroStorageFolderUid(): void
    {
        $registration = new Registration();

        $this->registrationProcesserMock->expects(self::once())->method('createAdditionalPersons')
            ->with($registration, 0);

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionCreatesAdditionalPersonsWithUserStorageFolderUidFromSettings(): void
    {
        $folderUid = 15;
        $this->subject->_set('settings', ['additionalPersonsStorageFolder' => (string)$folderUid]);
        $registration = new Registration();

        $this->registrationProcesserMock->expects(self::once())->method('createAdditionalPersons')
            ->with($registration, $folderUid);

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionPersistsRegistration(): void
    {
        $registration = new Registration();

        $this->registrationProcesserMock->expects(self::once())->method('persist')->with($registration);

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionSendsEmail(): void
    {
        $registration = new Registration();

        $this->registrationProcesserMock->expects(self::once())->method('sendEmails')->with($registration);

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createDestroysOneTimeAccountSession(): void
    {
        $registration = new Registration();

        $this->oneTimeAccountConnectorMock->expects(self::once())->method('destroyOneTimeSession');

        $this->subject->createAction(new SingleEvent(), $registration);
    }

    /**
     * @test
     */
    public function createActionRedirectsToThankYouActionAndPassesEvent(): void
    {
        $event = new SingleEvent();

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
