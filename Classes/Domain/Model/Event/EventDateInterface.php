<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

/**
 * This interface is required for events that have dates: `SingleEvent` and `EventDate`.
 */
interface EventDateInterface
{
    public function getStart(): ?\DateTime;

    public function getEnd(): ?\DateTime;

    public function getEarlyBirdDeadline(): ?\DateTime;

    public function getRegistrationDeadline(): ?\DateTime;

    public function requiresRegistration(): bool;

    public function hasWaitingList(): bool;

    /**
     * @return 0|positive-int
     */
    public function getMinimumNumberOfRegistrations(): int;

    /**
     * @return 0|positive-int
     */
    public function getMaximumNumberOfRegistrations(): int;
}
