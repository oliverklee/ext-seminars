<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
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
     * @var \DateTimeImmutable|null
     */
    protected $start;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $end;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $registrationStart;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $earlyBirdDeadline;

    /**
     * @var \DateTimeImmutable|null
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

    /**
     * @var int
     */
    protected $numberOfOfflineRegistrations = 0;

    /**
     * @var int
     * @phpstan-var EventInterface::STATUS_*
     */
    protected $status = EventInterface::STATUS_PLANNED;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\AccommodationOption>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $accommodationOptions;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\FoodOption>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $foodOptions;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Seminars\Domain\Model\RegistrationCheckbox>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $registrationCheckboxes;

    private function initializeEventDate(): void
    {
        $this->venues = new ObjectStorage();
        $this->speakers = new ObjectStorage();
        $this->organizers = new ObjectStorage();
        $this->accommodationOptions = new ObjectStorage();
        $this->foodOptions = new ObjectStorage();
        $this->registrationCheckboxes = new ObjectStorage();
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(?\DateTimeImmutable $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?\DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeImmutable $end): void
    {
        $this->end = $end;
    }

    public function getRegistrationStart(): ?\DateTimeImmutable
    {
        return $this->registrationStart;
    }

    public function setRegistrationStart(?\DateTimeImmutable $registrationStart): void
    {
        $this->registrationStart = $registrationStart;
    }

    public function getEarlyBirdDeadline(): ?\DateTimeImmutable
    {
        return $this->earlyBirdDeadline;
    }

    public function setEarlyBirdDeadline(?\DateTimeImmutable $earlyBirdDeadline): void
    {
        $this->earlyBirdDeadline = $earlyBirdDeadline;
    }

    public function getRegistrationDeadline(): ?\DateTimeImmutable
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(?\DateTimeImmutable $registrationDeadline): void
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
        $organizers->rewind();

        return \count($organizers) > 0 ? $organizers->current() : null;
    }

    public function getNumberOfOfflineRegistrations(): int
    {
        return $this->numberOfOfflineRegistrations;
    }

    public function setNumberOfOfflineRegistrations(int $numberOfOfflineRegistrations): void
    {
        $this->numberOfOfflineRegistrations = $numberOfOfflineRegistrations;
    }

    /**
     * @return EventInterface::STATUS_*
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param EventInterface::STATUS_* $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function isCanceled(): bool
    {
        return $this->status === EventInterface::STATUS_CANCELED;
    }

    /**
     * @return ObjectStorage<AccommodationOption>
     */
    public function getAccommodationOptions(): ObjectStorage
    {
        return $this->accommodationOptions;
    }

    /**
     * @param ObjectStorage<AccommodationOption> $accommodationOptions
     */
    public function setAccommodationOptions(ObjectStorage $accommodationOptions): void
    {
        $this->accommodationOptions = $accommodationOptions;
    }

    /**
     * @return ObjectStorage<FoodOption>
     */
    public function getFoodOptions(): ObjectStorage
    {
        return $this->foodOptions;
    }

    /**
     * @param ObjectStorage<FoodOption> $foodOptions
     */
    public function setFoodOptions(ObjectStorage $foodOptions): void
    {
        $this->foodOptions = $foodOptions;
    }

    /**
     * @return ObjectStorage<RegistrationCheckbox>
     */
    public function getRegistrationCheckboxes(): ObjectStorage
    {
        return $this->registrationCheckboxes;
    }

    /**
     * @param ObjectStorage<RegistrationCheckbox> $registrationCheckboxes
     */
    public function setRegistrationCheckboxes(ObjectStorage $registrationCheckboxes): void
    {
        $this->registrationCheckboxes = $registrationCheckboxes;
    }

    public function allowsUnlimitedRegistrations(): bool
    {
        return $this->isRegistrationRequired() && $this->getMaximumNumberOfRegistrations() === 0;
    }
}
