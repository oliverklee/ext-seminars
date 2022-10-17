<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * This interface represents a event.
 */
interface EventInterface
{
    public function getInternalTitle(): string;
}
