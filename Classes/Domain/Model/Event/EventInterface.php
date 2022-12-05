<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\Price;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This interface is required for all kinds of events: `SingleEvent`, `EventTopic`, and `EventDate`.
 */
interface EventInterface
{
    /**
     * @var int
     */
    public const TYPE_SINGLE_EVENT = 0;

    /**
     * @var int
     */
    public const TYPE_EVENT_TOPIC = 1;

    /**
     * @var int
     */
    public const TYPE_EVENT_DATE = 2;

    /**
     * @var int
     */
    public const STATUS_PLANNED = 0;

    /**
     * @var int
     */
    public const STATUS_CANCELED = 1;

    /**
     * @var int
     */
    public const STATUS_CONFIRMED = 2;

    public function getInternalTitle(): string;

    public function getDisplayTitle(): string;

    public function getDescription(): string;

    public function getStandardPrice(): float;

    public function getEarlyBirdPrice(): float;

    public function getSpecialPrice(): float;

    public function getSpecialEarlyBirdPrice(): float;

    public function getEventType(): ?EventType;

    public function getOwnerUid(): int;

    public function hasAdditionalTerms(): bool;

    public function isMultipleRegistrationPossible(): bool;

    /**
     * @return ObjectStorage<PaymentMethod>
     */
    public function getPaymentMethods(): ObjectStorage;

    /**
     * Returns true if the standard price is 0.0. (In this case, all other prices are irrelevant.)
     */
    public function isFreeOfCharge(): bool;

    /**
     * Returns all prices, event if they might not be applicable right now (e.g. also always the early bird prices if
     * they are non-zero).
     *
     * If this event is free of charge, the result will be only the standard price with a total amount of zero.
     *
     * @return array<Price::PRICE_*, Price>
     */
    public function getAllPrices(): array;

    /**
     * @param Price::PRICE_* $priceCode
     *
     * @throws \UnexpectedValueException if there is no price with that code
     */
    public function getPriceByPriceCode(string $priceCode): Price;

    /**
     * Returns the raw data as it is stored in the database.
     *
     * @return array<string, string|int|float|null>|null
     *
     * @internal
     */
    public function getRawData(): ?array;
}
