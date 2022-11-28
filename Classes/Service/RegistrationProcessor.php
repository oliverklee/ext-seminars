<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Seminars\Configuration\LegacyRegistrationConfiguration;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Takes care of enriching and processing a registration after an attendee has registered for an event.
 *
 * This is the recommended way to process a registration:
 * 1. `enrichWithMetadata`
 * 2. `calculateTotalPrice`
 * 3. `createTitle`
 * 4. `persist`
 * 5. `sendEmails`
 */
class RegistrationProcessor implements SingletonInterface
{
    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var RegistrationGuard
     */
    private $registrationGuard;

    /**
     * @var RegistrationManager
     */
    private $registrationManager;

    public function injectRegistrationRepository(RegistrationRepository $repository): void
    {
        $this->registrationRepository = $repository;
    }

    public function injectEventRepository(EventRepository $repository): void
    {
        $this->eventRepository = $repository;
    }

    public function injectFrontendUserRepository(FrontendUserRepository $repository): void
    {
        $this->frontendUserRepository = $repository;
    }

    public function injectRegistrationGuard(RegistrationGuard $registrationGuard): void
    {
        $this->registrationGuard = $registrationGuard;
    }

    public function injectRegistrationManager(RegistrationManager $registrationManager): void
    {
        $this->registrationManager = $registrationManager;
    }

    /**
     * Enriches the provided registration with the event and the user associations and a PID.
     *
     * Call this method before persisting the registration.
     *
     * @param array{registrationRecordsStorageFolder?: numeric-string} $settings
     */
    public function enrichWithMetadata(Registration $registration, Event $event, array $settings): void
    {
        $registration->setEvent($event);

        $userUid = $this->registrationGuard->getFrontEndUserUidFromSession();
        if (!\is_int($userUid)) {
            throw new \RuntimeException('No user UID found in the session.', 1668865776);
        }
        $user = $this->frontendUserRepository->findByUid($userUid);
        if (!$user instanceof FrontendUser) {
            throw new \RuntimeException('User with UID ' . $userUid . ' not found.', 1668865839);
        }
        $registration->setUser($user);

        $folderUid = (int)($settings['registrationRecordsStorageFolder'] ?? 0);
        $registration->setPid($folderUid);
    }

    public function calculateTotalPrice(Registration $registration): void
    {
        $event = $this->getEventFromRegistration($registration);
        $priceCode = $registration->getPriceCode();
        $price = $event->getAllPrices()[$priceCode] ?? null;
        if ($price instanceof Price) {
            $totalPrice = $price->getAmount() * $registration->getSeats();
            $registration->setTotalPrice($totalPrice);
        }
    }

    /**
     * @throws \RuntimeException if the given registration has no associated event
     */
    private function getEventFromRegistration(Registration $registration): Event
    {
        $event = $registration->getEvent();
        if (!$event instanceof Event) {
            throw new \RuntimeException('The registration has no associated event.', 1669023165);
        }

        return $event;
    }

    /**
     * Sets the title for the registration using the user's full name, the event title and date.
     */
    public function createTitle(Registration $registration): void
    {
        $user = $registration->getUser();
        if (!$user instanceof FrontendUser) {
            throw new \RuntimeException('The registration has no associated user.', 1669023125);
        }
        $event = $this->getEventFromRegistration($registration);

        $dateFormat = LocalizationUtility::translate('dateFormat', 'seminars');
        $startDate = $event instanceof EventDateInterface ? $event->getStart() : null;
        $formattedDate = $startDate instanceof \DateTimeInterface ? $startDate->format($dateFormat) : '';

        $title = LocalizationUtility::translate(
            'registrationTitleFormat',
            'seminars',
            [$event->getDisplayTitle(), $user->getName(), $formattedDate]
        );

        $registration->setTitle($title);
    }

    /**
     * Persists a registration and updates the `Event.registrations` counter cache.
     *
     * Call `enrichWithMetadata` first before calling this method.
     */
    public function persist(Registration $registration): void
    {
        $event = $this->getEventFromRegistration($registration);

        $this->registrationRepository->add($registration);
        $this->registrationRepository->persistAll();

        $this->eventRepository->updateRegistrationCounterCache($event);
    }

    /**
     * Sends the confirmation and notification emails for a registration. Call `persist` first.
     */
    public function sendEmails(Registration $registration): void
    {
        $registrationUid = $registration->getUid();
        if (!\is_int($registrationUid) || $registrationUid <= 0) {
            throw new \RuntimeException('The registration has not been persisted yet.', 1668939288);
        }

        $legacyRegistration = GeneralUtility::makeInstance(LegacyRegistration::class, $registrationUid);
        $this->registrationManager->setRegistration($legacyRegistration);

        $configuration = GeneralUtility::makeInstance(LegacyRegistrationConfiguration::class);

        $this->registrationManager->sendEmailsForNewRegistration($configuration);
    }
}
