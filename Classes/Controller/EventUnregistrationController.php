<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Configuration\LegacyConfiguration;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for registering for events.
 */
class EventUnregistrationController extends ActionController
{
    /**
     * @var RegistrationManager
     */
    private $registrationManager;

    public function injectRegistrationManager(RegistrationManager $registrationManager): void
    {
        $this->registrationManager = $registrationManager;
    }

    /**
     * Checks whether the logged-in user is allowed to cancel the given registration.
     *
     * @IgnoreValidation("registration")
     */
    public function checkPrerequisitesAction(?Registration $registration = null): void
    {
        if (!$registration instanceof Registration) {
            $this->forwardToDenyAction('registrationMissing');
        }

        if (!$this->belongsToLoggedInUser($registration)) {
            // To avoid information disclosure, we do not tell the user about registrations that are not theirs.
            $this->forwardToDenyAction('registrationMissing');
        }

        if (!$this->isUnregistrationPossible($registration)) {
            // To avoid information disclosure, we do not tell the user about registrations that are not theirs.
            $this->forwardToDenyAction('noUnregistrationPossible');
        }

        $this->redirect('confirm', null, null, ['registration' => $registration]);
    }

    private function belongsToLoggedInUser(Registration $registration): bool
    {
        $user = $registration->getUser();
        if (!$user instanceof FrontendUser) {
            return false;
        }

        $loggedInUserUid = GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('frontend.user', 'id', 0);

        return $user->getUid() === $loggedInUserUid;
    }

    private function isUnregistrationPossible(Registration $registration): bool
    {
        $legacyRegistration = GeneralUtility::makeInstance(LegacyRegistration::class, (int)$registration->getUid());
        $legacyEvent = $legacyRegistration->getSeminarObject();

        return $legacyEvent->isUnregistrationPossible();
    }

    /**
     * This is a convenience method to simplify multiple calls.
     *
     * @param non-empty-string $warningMessageKey the key of the message to display,
     *        will automatically get prefixed with `plugin.eventUnregistration.error.`
     *
     * @return never
     */
    private function forwardToDenyAction(string $warningMessageKey): void
    {
        $this->forward('deny', null, null, ['warningMessageKey' => $warningMessageKey]);
    }

    public function denyAction(string $warningMessageKey): void
    {
        $this->view->assign('warningMessageKey', $warningMessageKey);
    }

    /**
     * Displays the unregistration form.
     *
     * @IgnoreValidation("registration")
     */
    public function confirmAction(Registration $registration): void
    {
        $this->view->assign('registration', $registration);
    }

    /**
     * Removes the provided registration and forwards to the thank-you page.
     *
     * @IgnoreValidation("registration")
     */
    public function unregisterAction(Registration $registration): void
    {
        $configuration = GeneralUtility::makeInstance(LegacyConfiguration::class);
        $this->registrationManager->removeRegistration((int)$registration->getUid(), $configuration);

        $this->redirect('thankYou', null, null, ['event' => $registration->getEvent()]);
    }

    /**
     * @IgnoreValidation("event")
     */
    public function thankYouAction(Event $event): void
    {
        $this->view->assign('event', $event);
    }
}
