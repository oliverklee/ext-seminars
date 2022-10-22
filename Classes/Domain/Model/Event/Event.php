<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Abstract base class for different event types (single events, event topics, event dates).
 *
 * Mostly, this class exists in order to get single-table inheritance working. In the code,
 * please use `EventInterface` as much as possible instead.
 */
abstract class Event extends AbstractEntity implements EventInterface
{
    use EventTrait;

    /**
     * Checks whether this `Event` subclass actually allows registration.
     *
     * @return ($this is EventDateInterface ? true : false)
     */
    abstract public function isRegistrationPossibleForThisClass(): bool;

    /**
     * @var \DateTime|null
     */
    protected $creationDate;

    /**
     * @var \DateTime|null
     */
    protected $changeDate;

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getChangeDate(): ?\DateTime
    {
        return $this->changeDate;
    }

    public function setChangeDate(\DateTime $creationDate): void
    {
        $this->changeDate = $creationDate;
    }
>>>>>>> 8085100b ([FEATURE] Add `Event.creationDate` and `.changeDate`)
}
