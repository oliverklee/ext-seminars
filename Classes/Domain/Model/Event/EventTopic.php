<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

/**
 * This class represents a topic for an event. It can be referenced by multiple event dates.
 */
class EventTopic extends Event implements EventTopicInterface
{
    use EventTrait;
    use EventTopicTrait;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function initializeObject(): void
    {
        $this->initializeEventTopic();
    }

    /**
     * @internal
     */
    public function getStatistics(): ?EventStatistics
    {
        return null;
    }
}
