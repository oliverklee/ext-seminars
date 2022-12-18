<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * Common interface for `EventType` and `NullEventType`.
 */
interface EventTypeInterface
{
    public function getUid(): ?int;

    public function getTitle(): string;
}
