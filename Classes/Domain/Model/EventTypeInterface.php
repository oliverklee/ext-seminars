<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * Common interface for `EventType` and `NullEventType`.
 */
interface EventTypeInterface
{
    /**
     * TODO: Use a native return type once we've dropped support for TYPO3 9lTS.
     *
     * @return int|null
     */
    public function getUid();

    public function getTitle(): string;
}
