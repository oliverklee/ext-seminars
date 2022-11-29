<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Service\OneTimeAccountConnector;
use OliverKlee\Seminars\Service\PriceFinder;
use OliverKlee\Seminars\Service\RegistrationGuard;
use OliverKlee\Seminars\Service\RegistrationProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for registering for events.
 */
class EventRegistrationController extends ActionController
{
    /**
     * @var RegistrationGuard
     */
    private $registrationGuard;

    /**
     * @var RegistrationProcessor
     */
    private $registrationProcessor;

    /**
     * @var OneTimeAccountConnector
     */
    private $oneTimeAccountConnector;

    /**
     * @var PriceFinder
     */
    private $priceFinder;

    public function injectRegistrationGuard(RegistrationGuard $registrationGuard): void
    {
        $this->registrationGuard = $registrationGuard;
    }

    public function injectRegistrationProcessor(RegistrationProcessor $processor): void
    {
        $this->registrationProcessor = $processor;
    }

    public function injectOneTimeAccountConnector(OneTimeAccountConnector $connector): void
    {
        $this->oneTimeAccountConnector = $connector;
    }

    public function injectPriceFinder(PriceFinder $priceFinder): void
    {
        $this->priceFinder = $priceFinder;
    }

    /**
     * Checks that the user can register for the provided event, and redirects or forwards to the corresponding next
     * action.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function checkPrerequisitesAction(?Event $event = null): void
    {
        if (!$event instanceof Event) {
            $this->redirectToPageForNoEvent();
        }
        if (!$this->registrationGuard->isRegistrationPossibleAtAnyTimeAtAll($event)) {
            $this->forwardToDenyAction('noRegistrationPossibleAtAll');
        }
        \assert($event instanceof SingleEvent || $event instanceof EventDate);
        if (!$this->registrationGuard->isRegistrationPossibleByDate($event)) {
            $this->forwardToDenyAction('noRegistrationPossibleAtTheMoment');
        }
        if (!$this->registrationGuard->existsFrontEndUserUidInSession()) {
            $this->redirectToLoginPage($event);
        }
        $userUid = $this->registrationGuard->getFrontEndUserUidFromSession();
        if (!$this->registrationGuard->isFreeFromRegistrationConflicts($event, $userUid)) {
            $this->forwardToDenyAction('alreadyRegistered');
        }
        $vacancies = $this->registrationGuard->getVacancies($event);
        if ($vacancies === 0) {
            $this->forwardToDenyAction('fullyBooked');
        }

        $this->redirect('new', null, null, ['event' => $event]);
    }

    /**
     * @return never
     */
    private function redirectToPageForNoEvent(): void
    {
        $pageUid = (int)($this->settings['pageForMissingEvent'] ?? 0);
        $this->redirect(null, null, null, [], $pageUid);
    }

    /**
     * This is a convenience method to simplify multiple calls.
     *
     * @param non-empty-string $warningMessageKey the key of the message to display,
     *        will automatically get prefixed with `plugin.eventRegistration.error.`
     *
     * @return never
     */
    private function forwardToDenyAction(string $warningMessageKey): void
    {
        $this->forward('denyRegistration', null, null, ['warningMessageKey' => $warningMessageKey]);
    }

    public function denyRegistrationAction(string $warningMessageKey): void
    {
        $this->view->assign('warningMessageKey', $warningMessageKey);
    }

    /**
     * @return never
     */
    private function redirectToLoginPage(Event $event): void
    {
        // In order to shorten the URL by removing redundant arguments, we are not using `$uriBuilder->uriFor()` here.
        $redirectUrl = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
            ->setArguments(['tx_seminars_eventregistration[event]' => $event->getUid()])
            ->buildFrontendUri();

        $loginPageUid = (int)($this->settings['loginPage'] ?? 0);
        $loginPageUrlWithRedirect = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
            ->setTargetPageUid($loginPageUid)->setArguments(['redirect_url' => $redirectUrl])
            ->buildFrontendUri();

        $this->redirectToUri($loginPageUrlWithRedirect);
    }

    /**
     * Displays the event registration form.
     *
     * @Extbase\IgnoreValidation("event")
     * @Extbase\IgnoreValidation("registration")
     */
    public function newAction(Event $event, ?Registration $registration = null): void
    {
        $this->registrationGuard->assertBookableEventType($event);
        \assert($event instanceof EventDateInterface);

        $this->view->assign('event', $event);

        if ($registration instanceof Registration) {
            $newRegistration = $registration;
        } else {
            $newRegistration = GeneralUtility::makeInstance(Registration::class);
            $newRegistration->setRegisteredThemselves((bool)($this->settings['registerThemselvesDefault'] ?? true));
        }
        $this->view->assign('registration', $newRegistration);

        $maximumBookableSeats = (int)($this->settings['maximumBookableSeats'] ?? 10);
        $vacancies = $this->registrationGuard->getVacancies($event);
        if (\is_int($vacancies)) {
            $maximumBookableSeats = \min($maximumBookableSeats, $vacancies);
        }
        $this->view->assign('maximumBookableSeats', $maximumBookableSeats);
        $this->view->assign('applicablePrices', $this->priceFinder->findApplicablePrices($event));
    }

    /**
     * Displays the confirmation page of the event registration form.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function confirmAction(Event $event, Registration $registration): void
    {
        $this->registrationGuard->assertBookableEventType($event);
        \assert($event instanceof EventDateInterface);

        $this->registrationProcessor->enrichWithMetadata($registration, $event, $this->settings);
        $this->registrationProcessor->calculateTotalPrice($registration);

        $this->view->assign('event', $event);
        $this->view->assign('registration', $registration);
        $this->view->assign('applicablePrices', $this->priceFinder->findApplicablePrices($event));
    }

    /**
     * Creates the registration and redirects to the thank-you action.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function createAction(Event $event, Registration $registration): void
    {
        $this->registrationGuard->assertBookableEventType($event);
        \assert($event instanceof EventDateInterface);

        $this->registrationProcessor->enrichWithMetadata($registration, $event, $this->settings);
        $this->registrationProcessor->calculateTotalPrice($registration);
        $this->registrationProcessor->createTitle($registration);
        $this->registrationProcessor->persist($registration);
        $this->registrationProcessor->sendEmails($registration);

        $this->oneTimeAccountConnector->destroyOneTimeSession();

        $this->redirect('thankYou', null, null, ['event' => $event]);
    }

    /**
     * Displays the thank-you page.
     *
     * @Extbase\IgnoreValidation("event")
     */
    public function thankYouAction(Event $event): void
    {
        $this->view->assign('event', $event);
    }
}
