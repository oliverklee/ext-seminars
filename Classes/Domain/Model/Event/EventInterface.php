<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

/**
 * This interface represents a event.
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
}
