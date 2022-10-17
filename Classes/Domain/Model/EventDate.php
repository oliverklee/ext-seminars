<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * This class represents a date for an event that has an association to a topic.
 */
class EventDate extends Event
{
    use EventTrait;
}
