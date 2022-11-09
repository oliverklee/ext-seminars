<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\EventType;

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

    /**
     * @var non-empty-string
     */
    public const PRICE_STANDARD = 'price_regular';

    /**
     * @var non-empty-string
     */
    public const PRICE_EARLY_BIRD = 'price_regular_early';

    /**
     * @var non-empty-string
     */
    public const PRICE_SPECIAL = 'price_special';

    /**
     * @var non-empty-string
     */
    public const PRICE_SPECIAL_EARLY_BIRD = 'price_special_early';

    public function getInternalTitle(): string;

    public function getDisplayTitle(): string;

    public function getDescription(): string;

    public function getStandardPrice(): float;

    public function getEarlyBirdPrice(): float;

    public function getSpecialPrice(): float;

    public function getSpecialEarlyBirdPrice(): float;

    public function getEventType(): ?EventType;

    public function getOwnerUid(): int;

    public function hasAdditionalTermsAndConditions(): bool;

    public function isMultipleRegistrationPossible(): bool;
}
