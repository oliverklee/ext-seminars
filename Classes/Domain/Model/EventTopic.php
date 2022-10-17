<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * This class represents a topic for an event. It can be referenced by multiple event dates.
 */
class EventTopic extends Event
{
    use EventTrait;
}
