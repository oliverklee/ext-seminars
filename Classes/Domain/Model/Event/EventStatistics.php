<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

/**
 * The registration statistics for a single event.
 */
class EventStatistics
{
    /**
     * @var int
     * @phpstan-var 0|positive-int
     */
    private $regularSeatsCountFromRegistrations;

    /**
     * @var int
     * @phpstan-var 0|positive-int
     */
    private $offlineRegistrationsCount;

    /**
     * @var int
     * @phpstan-var 0|positive-int
     */
    private $waitingListSeatsCount;

    /**
     * @var int
     * @phpstan-var 0|positive-int
     */
    private $minimumRequiredSeats;

    /**
     * @var int
     * @phpstan-var 0|positive-int
     */
    private $seatsLimit;

    /**
     * @param 0|positive-int $regularSeatsCountFromRegistrations
     * @param 0|positive-int $offlineRegistrationsCount
     * @param 0|positive-int $waitingListSeatsCount
     * @param 0|positive-int $minimumRequiredSeats
     * @param 0|positive-int $seatsLimit
     */
    public function __construct(
        int $regularSeatsCountFromRegistrations,
        int $offlineRegistrationsCount,
        int $waitingListSeatsCount,
        int $minimumRequiredSeats,
        int $seatsLimit
    ) {
        $this->regularSeatsCountFromRegistrations = $regularSeatsCountFromRegistrations;
        $this->offlineRegistrationsCount = $offlineRegistrationsCount;
        $this->waitingListSeatsCount = $waitingListSeatsCount;
        $this->minimumRequiredSeats = $minimumRequiredSeats;
        $this->seatsLimit = $seatsLimit;
    }

    /**
     * Returns the number of regular seats from registrations and offline registrations.
     *
     * @return 0|positive-int
     */
    public function getRegularSeatsCount(): int
    {
        return $this->regularSeatsCountFromRegistrations + $this->offlineRegistrationsCount;
    }

    /**
     * Returns the number of seats on the waiting list (not counting offline registrations, we are expected to always be
     * regular registrations).
     *
     * @return 0|positive-int
     */
    public function getWaitingListSeatsCount(): int
    {
        return $this->waitingListSeatsCount;
    }

    /**
     * Returns how many seats need to be registered so that the event can take place.
     *
     * @return 0|positive-int
     */
    public function getMinimumRequiredSeats(): int
    {
        return $this->minimumRequiredSeats;
    }

    /**
     * Returns the number of maximum bookable regular seats.
     *
     * @return 0|positive-int
     */
    public function getSeatsLimit(): int
    {
        return $this->seatsLimit;
    }

    public function hasSeatsLimit(): bool
    {
        return $this->seatsLimit > 0;
    }

    /**
     * Checks whether the event has enough registrations to take place.
     */
    public function hasEnoughRegistrations(): bool
    {
        return $this->getRegularSeatsCount() >= $this->getMinimumRequiredSeats();
    }

    /**
     * @return 0|positive-int|null the number of available seats, will be `null` if there is no seats limit
     */
    public function getVacancies(): ?int
    {
        if (!$this->hasSeatsLimit()) {
            return null;
        }

        return \max(0, $this->getSeatsLimit() - $this->getRegularSeatsCount());
    }

    public function isFullyBooked(): bool
    {
        return $this->getVacancies() === 0;
    }

    /**
     * Checks whether the event has at least one regular registration (not counting offline registrations).
     */
    public function hasExportableRegularRegistrations(): bool
    {
        return $this->regularSeatsCountFromRegistrations > 0;
    }
}
