<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\EventType;

/**
 * This class represents a date for an event that has an association to a topic.
 */
class EventDate extends Event implements EventDateInterface
{
    use EventTrait;
    use EventDateTrait;

    /**
     * @var \OliverKlee\Seminars\Domain\Model\Event\EventTopic|null
     */
    protected $topic;

    public function getTopic(): ?EventTopic
    {
        return $this->topic;
    }

    public function setTopic(EventTopic $topic): void
    {
        $this->topic = $topic;
    }

    public function getDisplayTitle(): string
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic ? $topic->getDisplayTitle() : '';
    }

    public function getDescription(): string
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic ? $topic->getDescription() : '';
    }

    public function getStandardPrice(): float
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic ? $topic->getStandardPrice() : 0.0;
    }

    public function getEarlyBirdPrice(): float
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic ? $topic->getEarlyBirdPrice() : 0.0;
    }

    public function getSpecialPrice(): float
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic ? $topic->getSpecialPrice() : 0.0;
    }

    public function getSpecialEarlyBirdPrice(): float
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic ? $topic->getSpecialEarlyBirdPrice() : 0.0;
    }

    public function getEventType(): ?EventType
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic ? $topic->getEventType() : null;
    }

    public function hasAdditionalTermsAndConditions(): bool
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic && $topic->hasAdditionalTermsAndConditions();
    }

    public function isMultipleRegistrationPossible(): bool
    {
        $topic = $this->getTopic();

        return $topic instanceof EventTopic && $topic->isMultipleRegistrationPossible();
    }
}
