<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

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

    public function getInternalTitle(): string;

    public function getDisplayTitle(): string;

    public function getDescription(): string;
}
