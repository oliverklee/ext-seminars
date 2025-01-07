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
        $this->initializeEventTopic();
    }

    public function getStatistics(): ?EventStatistics
    {
        return null;
    }

    public function isRegistrationPossibleByDate(): bool
    {
        return false;
    }
}
