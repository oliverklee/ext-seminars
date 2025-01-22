<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This interface is required for events that have dates: `SingleEvent` and `EventDate`.
 */
interface EventDateInterface
{
    /**
     * @var int<0, 2>
     */
    public const EVENT_FORMAT_ON_SITE = 0;

    /**
     * @var int<0, 2>
     */
    public const EVENT_FORMAT_HYBRID = 1;

    /**
     * @var int<0, 2>
     */
    public const EVENT_FORMAT_ONLINE = 2;

    public function getStart(): ?\DateTime;

    public function getEnd(): ?\DateTime;

    public function getRegistrationStart(): ?\DateTime;

    public function getEarlyBirdDeadline(): ?\DateTime;

    public function getRegistrationDeadline(): ?\DateTime;

    public function isRegistrationRequired(): bool;

    public function hasWaitingList(): bool;

    /**
     * @return int<0, max>
     */
    public function getMinimumNumberOfRegistrations(): int;

    /**
     * @return int<0, max>
     */
    public function getMaximumNumberOfRegistrations(): int;

    /**
     * @return ObjectStorage<Venue>
     */
    public function getVenues(): ObjectStorage;

    public function hasExactlyOneVenue(): bool;

    /**
     * @throws \RuntimeException if there are no venues
     */
    public function getFirstVenue(): Venue;

    /**
     * @return ObjectStorage<Speaker>
     */
    public function getSpeakers(): ObjectStorage;

    /**
     * @return ObjectStorage<Organizer>
     */
    public function getOrganizers(): ObjectStorage;

    /**
     * @throws \UnexpectedValueException if there are no organizers
     */
    public function getFirstOrganizer(): Organizer;

    public function getNumberOfOfflineRegistrations(): int;

    /**
     * @return EventInterface::STATUS_*
     */
    public function getStatus(): int;

    public function isCanceled(): bool;

    /**
     * @return ObjectStorage<AccommodationOption>
     */
    public function getAccommodationOptions(): ObjectStorage;

    /**
     * @return ObjectStorage<FoodOption>
     */
    public function getFoodOptions(): ObjectStorage;

    /**
     * @return ObjectStorage<RegistrationCheckbox>
     */
    public function getRegistrationCheckboxes(): ObjectStorage;

    /**
     * Returns all prices, event if they might not be applicable right now (e.g. also always the early bird prices if
     * they are non-zero).
     *
     * If this event is free of charge, the result will be only the standard price with a total amount of zero.
     *
     * @return array<Price::PRICE_*, Price>
     */
    public function getAllPrices(): array;

    public function allowsUnlimitedRegistrations(): bool;

    /**
     * @internal
     */
    public function setStatistics(EventStatistics $statistics): void;

    /**
     * @return EventDateInterface::EVENT_FORMAT_*
     */
    public function getEventFormat(): int;

    /**
     * @param EventDateInterface::EVENT_FORMAT_* $eventFormat
     */
    public function setEventFormat(int $eventFormat): void;

    public function isAtLeastPartiallyOnSite(): bool;

    public function isAtLeastPartiallyOnline(): bool;

    public function getWebinarUrl(): string;

    public function setWebinarUrl(string $webinarUrl): void;

    public function hasUsableWebinarUrl(): bool;

    public function getAdditionalEmailText(): string;
}
