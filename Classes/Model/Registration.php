<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a registration for an event.
 */
class Registration extends AbstractModel
{
    /**
     * @return string the title of this registration, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

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
     * @return int<0, max> the number of registered seats of this registration, will be >= 0
     */
    public function getSeats(): int
    {
        return $this->getAsNonNegativeInteger('seats');
    }

    /**
     * Returns whether the front-end user registered themselves.
     */
    public function hasRegisteredThemselves(): bool
    {
        return $this->getAsBoolean('registered_themselves');
    }

    /**
     * @return string the names of the attendees of this registration separated by CRLF, might be empty
     */
    public function getAttendeesNames(): string
    {
        return $this->getAsString('attendees_names');
    }

    /**
     * @param string $attendeesNames the names of the attendees of this registration to set separated
     *        by CRLF, may be empty
     */
    public function setAttendeesNames(string $attendeesNames): void
    {
        $this->setAsString('attendees_names', $attendeesNames);
    }

    /**
     * Gets the additional persons (FE users) attached to this registration.
     *
     * @return Collection<FrontEndUser>
     */
    public function getAdditionalPersons(): Collection
    {
        /** @var Collection<FrontEndUser> $additionalPersons */
        $additionalPersons = $this->getAsCollection('additional_persons');

        return $additionalPersons;
    }

    /**
     * Sets the additional persons attached to this registration.
     *
     * @param Collection<FrontEndUser> $persons
     */
    public function setAdditionalPersons(Collection $persons): void
    {
        $this->set('additional_persons', $persons);
    }
}
