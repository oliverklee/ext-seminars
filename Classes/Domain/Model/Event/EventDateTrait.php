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
}
