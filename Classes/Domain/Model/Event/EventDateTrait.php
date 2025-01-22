<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This trait provides methods that are useful for `EventTopic`s, and usually also `SingleEvent`s.
 *
 * @phpstan-require-extends Event
 * @phpstan-require-implements EventDateInterface
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
    protected $registrationStart;

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
     * @phpstan-var int<0, max>
     */
    protected $minimumNumberOfRegistrations = 0;

    /**
     * @var int
     * @phpstan-var int<0, max>
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

    /**
     * @var EventStatistics|null
     * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
     * @internal
     */
    protected $statistics;

    /**
     * @var int
     * @phpstan-var EventDateInterface::EVENT_FORMAT_*
     */
    protected $eventFormat = EventDateInterface::EVENT_FORMAT_ON_SITE;

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 255})
     */
    protected $webinarUrl = '';

    /**
     * @var string
     * @Extbase\Validate("StringLength", options={"maximum": 2048})
     */
    protected $additionalEmailText = '';

    /**
     * @var list<EventDateInterface::EVENT_FORMAT_*>
     */
    private static $partiallyOnSiteEventFormats = [
        EventDateInterface::EVENT_FORMAT_ON_SITE,
        EventDateInterface::EVENT_FORMAT_HYBRID,
    ];

    /**
     * @var list<EventDateInterface::EVENT_FORMAT_*>
     */
    private static $partiallyOnlineEventFormats = [
        EventDateInterface::EVENT_FORMAT_HYBRID,
        EventDateInterface::EVENT_FORMAT_ONLINE,
    ];

    private function initializeEventDate(): void
    {
        $this->venues = new ObjectStorage();
        $this->speakers = new ObjectStorage();
        $this->organizers = new ObjectStorage();
        $this->accommodationOptions = new ObjectStorage();
        $this->foodOptions = new ObjectStorage();
        $this->registrationCheckboxes = new ObjectStorage();
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

    public function getRegistrationStart(): ?\DateTime
    {
        return $this->registrationStart;
    }

    public function setRegistrationStart(?\DateTime $registrationStart): void
    {
        $this->registrationStart = $registrationStart;
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
     * @return int<0, max>
     */
    public function getMinimumNumberOfRegistrations(): int
    {
        return $this->minimumNumberOfRegistrations;
    }

    /**
     * @param int<0, max> $minimumNumberOfRegistrations
     */
    public function setMinimumNumberOfRegistrations(int $minimumNumberOfRegistrations): void
    {
        $this->minimumNumberOfRegistrations = $minimumNumberOfRegistrations;
    }

    /**
     * @return int<0, max>
     */
    public function getMaximumNumberOfRegistrations(): int
    {
        return $this->maximumNumberOfRegistrations;
    }

    /**
     * @param int<0, max> $maximumNumberOfRegistrations
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

    public function hasExactlyOneVenue(): bool
    {
        return \count($this->getVenues()) === 1;
    }

    /**
     * @throws \RuntimeException if there are no venues
     */
    public function getFirstVenue(): Venue
    {
        $venues = $this->getVenues()->getArray();
        $firstVenue = $venues[0] ?? null;
        if (!$firstVenue instanceof Venue) {
            throw new \RuntimeException('This event does not have any venues.', 1726226635);
        }

        return $firstVenue;
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

    /**
     * @throws \UnexpectedValueException if there are no organizers
     */
    public function getFirstOrganizer(): Organizer
    {
        $organizers = $this->getOrganizers();
        if (\count($organizers) === 0) {
            throw new \UnexpectedValueException('This event does not have any organizers.', 1724277439);
        }

        $organizers->rewind();
        return $organizers->current();
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

    /**
     * @internal
     */
    public function getStatistics(): ?EventStatistics
    {
        return $this->statistics;
    }

    /**
     * @internal
     */
    public function setStatistics(EventStatistics $statistics): void
    {
        $this->statistics = $statistics;
    }

    /**
     * @return EventDateInterface::EVENT_FORMAT_*
     */
    public function getEventFormat(): int
    {
        return $this->eventFormat;
    }

    /**
     * @param EventDateInterface::EVENT_FORMAT_* $eventFormat
     */
    public function setEventFormat(int $eventFormat): void
    {
        $this->eventFormat = $eventFormat;
    }

    public function isAtLeastPartiallyOnSite(): bool
    {
        return \in_array($this->getEventFormat(), self::$partiallyOnSiteEventFormats, true);
    }

    public function isAtLeastPartiallyOnline(): bool
    {
        return \in_array($this->getEventFormat(), self::$partiallyOnlineEventFormats, true);
    }

    public function getWebinarUrl(): string
    {
        return $this->webinarUrl;
    }

    public function setWebinarUrl(string $webinarUrl): void
    {
        $this->webinarUrl = $webinarUrl;
    }

    /**
     * Checks if this event is at least partially online and has a non-empty webinar URL.
     */
    public function hasUsableWebinarUrl(): bool
    {
        return $this->isAtLeastPartiallyOnline() && $this->getWebinarUrl() !== '';
    }

    public function getAdditionalEmailText(): string
    {
        return $this->additionalEmailText;
    }

    public function setAdditionalEmailText(string $additionalEmailText): void
    {
        $this->additionalEmailText = $additionalEmailText;
    }
}
