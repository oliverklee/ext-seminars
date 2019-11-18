<?php

declare(strict_types=1);

/**
 * This abstract class represents a time span.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class Tx_Seminars_Model_AbstractTimeSpan extends \Tx_Oelib_Model implements \Tx_Seminars_Interface_Titled
{
    /**
     * Returns our title.
     *
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * Sets our title.
     *
     * @param string $title our title to set, must not be empty
     *
     * @return void
     */
    public function setTitle(string $title)
    {
        if ($title == '') {
            throw new \InvalidArgumentException('The parameter $title must not be empty.', 1333293446);
        }

        $this->setAsString('title', $title);
    }

    /**
     * Returns our begin date as UNIX time-stamp.
     *
     * @return int our begin date as UNIX time-stamp, will be >= 0,
     *                 0 means "no begin date"
     */
    public function getBeginDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('begin_date');
    }

    /**
     * Sets our begin date as UNIX time-stamp.
     *
     * @param int $beginDate our begin date as UNIX time-stamp, must be >= 0, 0 means "no begin date"
     *
     * @return void
     */
    public function setBeginDateAsUnixTimeStamp(int $beginDate)
    {
        if ($beginDate < 0) {
            throw new \InvalidArgumentException('The parameter $beginDate must be >= 0.', 1333293455);
        }

        $this->setAsInteger('begin_date', $beginDate);
    }

    /**
     * Returns whether this time-span has a begin date.
     *
     * @return bool TRUE if this time-span has a begin date, FALSE otherwise
     */
    public function hasBeginDate(): bool
    {
        return $this->hasInteger('begin_date');
    }

    /**
     * Returns our end date as UNIX time-stamp.
     *
     * @return int our end date as UNIX time-stamp, will be >= 0,
     *                 0 means "no end date"
     */
    public function getEndDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('end_date');
    }

    /**
     * Sets our end date as UNIX time-stamp.
     *
     * @param int $endDate our end date as UNIX time-stamp, must be >= 0, 0 means "no end date"
     *
     * @return void
     */
    public function setEndDateAsUnixTimeStamp(int $endDate)
    {
        if ($endDate < 0) {
            throw new \InvalidArgumentException('The parameter $endDate must be >= 0.', 1333293465);
        }

        $this->setAsInteger('end_date', $endDate);
    }

    /**
     * Returns whether this time-span has an end date.
     *
     * @return bool TRUE if this time-span has an end date, FALSE otherwise
     */
    public function hasEndDate(): bool
    {
        return $this->hasInteger('end_date');
    }

    /**
     * Returns our speakers.
     *
     * @return \Tx_Oelib_List our speakers, will be empty if this time-span has
     *                       no speakers
     */
    public function getSpeakers(): \Tx_Oelib_List
    {
        return $this->getAsList('speakers');
    }

    /**
     * Returns our room.
     *
     * @return string our room, will be empty if this time-span has no place
     */
    public function getRoom(): string
    {
        return $this->getAsString('room');
    }

    /**
     * Sets our room.
     *
     * @param string $room our room, may be empty
     *
     * @return void
     */
    public function setRoom(string $room)
    {
        $this->setAsString('room', $room);
    }

    /**
     * Returns whether this time-span has a room.
     *
     * @return bool
     */
    public function hasRoom(): bool
    {
        return $this->hasString('room');
    }
}
