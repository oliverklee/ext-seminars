<?php

declare(strict_types=1);

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This abstract class represents a time span.
 */
abstract class Tx_Seminars_Model_AbstractTimeSpan extends AbstractModel
{
    /**
     * @return int our begin date as UNIX time-stamp, will be >= 0, 0 means "no begin date"
     */
    public function getBeginDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('begin_date');
    }

    /**
     * @param int $beginDate our begin date as UNIX time-stamp, must be >= 0, 0 means "no begin date"
     */
    public function setBeginDateAsUnixTimeStamp(int $beginDate): void
    {
        if ($beginDate < 0) {
            throw new \InvalidArgumentException('The parameter $beginDate must be >= 0.', 1333293455);
        }

        $this->setAsInteger('begin_date', $beginDate);
    }

    /**
     * @return bool TRUE if this time-span has a begin date, FALSE otherwise
     */
    public function hasBeginDate(): bool
    {
        return $this->hasInteger('begin_date');
    }

    /**
     * @return int our end date as UNIX time-stamp, will be >= 0, 0 means "no end date"
     */
    public function getEndDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('end_date');
    }

    /**
     * @param int $endDate our end date as UNIX time-stamp, must be >= 0, 0 means "no end date"
     */
    public function setEndDateAsUnixTimeStamp(int $endDate): void
    {
        if ($endDate < 0) {
            throw new \InvalidArgumentException('The parameter $endDate must be >= 0.', 1333293465);
        }

        $this->setAsInteger('end_date', $endDate);
    }

    public function hasEndDate(): bool
    {
        return $this->hasInteger('end_date');
    }

    /**
     * @return Collection<\Tx_Seminars_Model_Speaker>
     */
    public function getSpeakers(): Collection
    {
        /** @var Collection<\Tx_Seminars_Model_Speaker> $speakers */
        $speakers = $this->getAsCollection('speakers');

        return $speakers;
    }

    /**
     * @return string our room, will be empty if this time-span has no place
     */
    public function getRoom(): string
    {
        return $this->getAsString('room');
    }

    /**
     * @param string $room our room, may be empty
     */
    public function setRoom(string $room): void
    {
        $this->setAsString('room', $room);
    }

    public function hasRoom(): bool
    {
        return $this->hasString('room');
    }
}
