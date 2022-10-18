<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model\Event;

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
    protected $requiresRegistration = false;

    /**
     * @var bool
     */
    protected $hasWaitingList = false;

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

    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    public function setStart(\DateTime $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    public function setEnd(\DateTime $end): void
    {
        $this->end = $end;
    }

    public function getEarlyBirdDeadline(): ?\DateTime
    {
        return $this->earlyBirdDeadline;
    }

    public function setEarlyBirdDeadline(\DateTime $earlyBirdDeadline): void
    {
        $this->earlyBirdDeadline = $earlyBirdDeadline;
    }

    public function getRegistrationDeadline(): ?\DateTime
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(\DateTime $registrationDeadline): void
    {
        $this->registrationDeadline = $registrationDeadline;
    }

    public function requiresRegistration(): bool
    {
        return $this->requiresRegistration;
    }

    public function setRequiresRegistration(bool $requiresRegistration): void
    {
        $this->requiresRegistration = $requiresRegistration;
    }

    public function hasWaitingList(): bool
    {
        return $this->hasWaitingList;
    }

    public function setHasWaitingList(bool $hasWaitingList): void
    {
        $this->hasWaitingList = $hasWaitingList;
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
}
