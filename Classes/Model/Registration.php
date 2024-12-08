<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a registration for an event.
 */
class Registration extends AbstractModel
{
    public function getFrontEndUser(): ?FrontEndUser
    {
        /** @var FrontEndUser|null $user */
        $user = $this->getAsModel('user');

        return $user;
    }

    public function setFrontEndUser(FrontEndUser $user): void
    {
        $this->set('user', $user);
    }

    public function getEvent(): Event
    {
        /** @var Event $event */
        $event = $this->getAsModel('seminar');

        return $event;
    }

    /**
     * Returns the event of this registration.
     *
     * This is an alias for `getEvent` necessary for the relation to the event.
     */
    public function getSeminar(): Event
    {
        return $this->getEvent();
    }

    public function setEvent(Event $event): void
    {
        $this->set('seminar', $event);
    }

    /**
     * Sets the event of this registration.
     *
     * This is an alias for setEvent necessary for the relation to the event.
     */
    public function setSeminar(Event $event): void
    {
        $this->setEvent($event);
    }

    public function isOnRegistrationQueue(): bool
    {
        return $this->getAsBoolean('registration_queue');
    }

    /**
     * Returns the number of registered seats of this registration.
     *
     * In older versions 0 equals 1 seat, which is deprecated.
     *
     * @return int the number of registered seats of this registration, will be >= 0
     */
    public function getSeats(): int
    {
        return $this->getAsInteger('seats');
    }
}
