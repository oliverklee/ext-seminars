<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This trait provides methods that are useful for `EventTopic`s, and usually also `SingleEvent`s.
 *
 * @mixin Event
 * @mixin EventDateInterface
 */
trait EventDateTrait
{
    /**
     * @var \DateTime|null
     */
    protected $start;

    /**
     * @var \DateTime|null
     */
    protected $end;

    /**
     * @var \DateTime|null
     */
    protected $earlyBirdDeadline;

    /**
     * @var \DateTime|null
     */
    protected $registrationDeadline;

    /**
     * @var bool
     */
    protected $registrationRequired = false;

    /**
     * @var bool
     */
    protected $waitingList = false;

    /**
     * @var int
     * @phpstan-var 0|positive-int
     */
    protected $minimumNumberOfRegistrations = 0;

    /**
     * @var int
     * @phpstan-var 0|positive-int
     */
    protected $maximumNumberOfRegistrations = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\Venue>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $venues;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\Speaker>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $speakers;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\Organizer>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $organizers;

    public function __construct()
    {
        $this->venues = new ObjectStorage();
        $this->speakers = new ObjectStorage();
        $this->organizers = new ObjectStorage();
    }

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(?\DateTime $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
    }

    public function getEarlyBirdDeadline(): ?\DateTime
    {
        return $this->earlyBirdDeadline;
    }

    public function setEarlyBirdDeadline(?\DateTime $earlyBirdDeadline): void
    {
        $this->earlyBirdDeadline = $earlyBirdDeadline;
    }

    public function getRegistrationDeadline(): ?\DateTime
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(?\DateTime $registrationDeadline): void
    {
        $this->registrationDeadline = $registrationDeadline;
    }

    public function isRegistrationRequired(): bool
    {
        return $this->registrationRequired;
    }

    public function setRegistrationRequired(bool $registrationRequired): void
    {
        $this->registrationRequired = $registrationRequired;
    }

    public function hasWaitingList(): bool
    {
        return $this->waitingList;
    }

    public function setWaitingList(bool $waitingList): void
    {
        $this->waitingList = $waitingList;
    }

    /**
     * @return 0|positive-int
     */
    public function getMinimumNumberOfRegistrations(): int
    {
        return $this->minimumNumberOfRegistrations;
    }

    /**
     * @param 0|positive-int $minimumNumberOfRegistrations
     */
    public function setMinimumNumberOfRegistrations(int $minimumNumberOfRegistrations): void
    {
        $this->minimumNumberOfRegistrations = $minimumNumberOfRegistrations;
    }

    /**
     * @return 0|positive-int
     */
    public function getMaximumNumberOfRegistrations(): int
    {
        return $this->maximumNumberOfRegistrations;
    }

    /**
     * @param 0|positive-int $maximumNumberOfRegistrations
     */
    public function setMaximumNumberOfRegistrations(int $maximumNumberOfRegistrations): void
    {
        $this->maximumNumberOfRegistrations = $maximumNumberOfRegistrations;
    }

    /**
     * @return ObjectStorage<Venue>
     */
    public function getVenues(): ObjectStorage
    {
        return $this->venues;
    }

    /**
     * @param ObjectStorage<Venue> $venues
     */
    public function setVenues(ObjectStorage $venues): void
    {
        $this->venues = $venues;
    }

    /**
     * @return ObjectStorage<Speaker>
     */
    public function getSpeakers(): ObjectStorage
    {
        return $this->speakers;
    }

    /**
     * @param ObjectStorage<Speaker> $speakers
     */
    public function setSpeakers(ObjectStorage $speakers): void
    {
        $this->speakers = $speakers;
    }

    /**
     * @return ObjectStorage<Organizer>
     */
    public function getOrganizers(): ObjectStorage
    {
        return $this->organizers;
    }

    /**
     * @param ObjectStorage<Organizer> $organizers
     */
    public function setOrganizers(ObjectStorage $organizers): void
    {
        $this->organizers = $organizers;
    }

    public function getFirstOrganizer(): ?Organizer
    {
        $organizers = $this->getOrganizers();

        return \count($organizers) > 0 ? $organizers->current() : null;
    }
}
