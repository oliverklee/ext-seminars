<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

/**
 * This class represents a single event, e.g., an event has the topic and date all in one record.
 */
class SingleEvent extends Event implements EventDateInterface, EventTopicInterface
{
    use EventTopicTrait;
    use EventDateTrait;

    public function __construct()
    {
        $this->initializeEventTopic();
        $this->initializeEventDate();
    }

    /**
     * Checks whether this `Event` subclass actually allows registration.
     *
     * @return ($this is EventDateInterface ? true : false)
     */
    public function isRegistrationPossibleForThisClass(): bool
    {
        return true;
    }
}
