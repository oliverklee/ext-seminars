<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * Dummy event type to be used as empty option in selects.
 */
class NullEventType implements EventTypeInterface
{
    /**
     * @return 0
     */
    public function getUid(): int
    {
        return 0;
    }

    /**
     * @return ''
     */
    public function getTitle(): string
    {
        return '';
    }
}
