<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

/**
 * This class represents a single event, e.g., an event has the topic and date all in one record.
 */
class SingleEvent extends Event implements EventDateInterface, EventTopicInterface
{
    use EventTrait;
    use EventTopicTrait;
    use EventDateTrait;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function initializeObject(): void
    {
        $this->initializeEventTopic();
        $this->initializeEventDate();
    }
}
