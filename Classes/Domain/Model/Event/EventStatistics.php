<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

/**
 * The registration statistics for a single event.
 */
class EventStatistics
{
    /**
     * @phpstan-var int<0, max>
     */
    private int $regularSeatsCountFromRegistrations;

    /**
     * @phpstan-var int<0, max>
     */
    private int $offlineRegistrationsCount;

    /**
     * @phpstan-var int<0, max>
     */
    private int $waitingListSeatsCount;

    /**
     * @phpstan-var int<0, max>
     */
    private int $minimumRequiredSeats;

    /**
     * @phpstan-var int<0, max>
     */
    private int $seatsLimit;

    /**
     * @param int<0, max> $regularSeatsCountFromRegistrations
     * @param int<0, max> $offlineRegistrationsCount
     * @param int<0, max> $waitingListSeatsCount
     * @param int<0, max> $minimumRequiredSeats
     * @param int<0, max> $seatsLimit
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
     * @return int<0, max>
     */
    public function getRegularSeatsCount(): int
    {
        return $this->regularSeatsCountFromRegistrations + $this->offlineRegistrationsCount;
    }

    /**
     * Returns the number of seats on the waiting list (not counting offline registrations, we are expected to always be
     * regular registrations).
     *
     * @return int<0, max>
     */
    public function getWaitingListSeatsCount(): int
    {
        return $this->waitingListSeatsCount;
    }

    /**
     * Returns how many seats need to be registered so that the event can take place.
     *
     * @return int<0, max>
     */
    public function getMinimumRequiredSeats(): int
    {
        return $this->minimumRequiredSeats;
    }

    /**
     * Returns the number of maximum bookable regular seats.
     *
     * @return int<0, max>
     */
    public function getSeatsLimit(): int
    {
        return $this->seatsLimit;
    }

    public function hasUnlimitedSeats(): bool
    {
        return $this->getSeatsLimit() === 0;
    }

    /**
     * Checks whether the event has enough registrations to take place.
     */
    public function hasEnoughRegistrations(): bool
    {
        return $this->getRegularSeatsCount() >= $this->getMinimumRequiredSeats();
    }

    /**
     * @return int<0, max>|null the number of available seats, will be `null` if there is no seats limit
     */
    public function getVacancies(): ?int
    {
        if ($this->hasUnlimitedSeats()) {
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
