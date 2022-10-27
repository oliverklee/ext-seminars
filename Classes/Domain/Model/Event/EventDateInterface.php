<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This interface is required for events that have dates: `SingleEvent` and `EventDate`.
 */
interface EventDateInterface
{
    public function getStart(): ?\DateTime;

    public function getEnd(): ?\DateTime;

    public function getEarlyBirdDeadline(): ?\DateTime;

    public function getRegistrationDeadline(): ?\DateTime;

    public function isRegistrationRequired(): bool;

    public function hasWaitingList(): bool;

    /**
     * @return 0|positive-int
     */
    public function getMinimumNumberOfRegistrations(): int;

    /**
     * @return 0|positive-int
     */
    public function getMaximumNumberOfRegistrations(): int;

    /**
     * @return ObjectStorage<Venue>
     */
    public function getVenues(): ObjectStorage;

    /**
     * @return ObjectStorage<Speaker>
     */
    public function getSpeakers(): ObjectStorage;

    /**
     * @return ObjectStorage<Organizer>
     */
    public function getOrganizers(): ObjectStorage;

    /**
     * @deprecated will be removed in seminars 6.0.
     */
    public function getFirstOrganizer(): ?Organizer;

    /**
     * Alias for `getFirstOrganizer()`.
     */
    public function getOrganizer(): ?Organizer;
}
