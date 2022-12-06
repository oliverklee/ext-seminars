<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventStatistics;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Calculates the statistics for events.
 */
class EventStatisticsCalculator
{
    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    public function injectRegistrationRepository(RegistrationRepository $repository): void
    {
        $this->registrationRepository = $repository;
    }

    /**
     * Calculates and sets `$event.statistics` if the event allows registration in general.
     */
    public function enrichWithStatistics(Event $event): void
    {
        if (!$event instanceof EventDateInterface || !$event->isRegistrationRequired()) {
            return;
        }

        $eventUid = $event->getUid();
        // This mostly is for making unit tests less of a hassle.
        if (\is_int($eventUid)) {
            $regularSeatsFromRegistrations = $this->registrationRepository->countRegularSeatsByEvent($eventUid);
            if ($event->hasWaitingList()) {
                $waitingListSeats = $this->registrationRepository->countWaitingListSeatsByEvent($eventUid);
            } else {
                $waitingListSeats = 0;
            }
        } else {
            $regularSeatsFromRegistrations = 0;
            $waitingListSeats = 0;
        }

        $statistics = GeneralUtility::makeInstance(
            EventStatistics::class,
            $regularSeatsFromRegistrations,
            $event->getNumberOfOfflineRegistrations(),
            $waitingListSeats,
            $event->getMinimumNumberOfRegistrations(),
            $event->getMaximumNumberOfRegistrations()
        );
        $event->setStatistics($statistics);
    }
}
