<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures;

use OliverKlee\Seminars\OldModel\AbstractTimeSpan;

/**
 * This class represents a test object from the database.
 */
final class TestingTimeSpan extends AbstractTimeSpan
{
    /**
     * Sets this time span's begin date and time.
     *
     * @param int $beginDate begin date and time as a UNIX timestamp, may be zero
     */
    public function setBeginDateAndTime(int $beginDate): void
    {
        $this->setRecordPropertyInteger('begin_date', $beginDate);
    }

    /**
     * Sets this time span's end date and time.
     *
     * @param int $endDate end date and time as a UNIX timestamp, may be zero
     */
    public function setEndDateAndTime(int $endDate): void
    {
        $this->setRecordPropertyInteger('end_date', $endDate);
    }

    /**
     * Sets this time span's room.
     *
     * @param string $room room name
     */
    public function setRoom(string $room): void
    {
        $this->setRecordPropertyString('room', $room);
    }

    /**
     * Sets the number of places for this time span.
     *
     * @param int $places the number of places that are associated with this time span
     */
    public function setNumberOfPlaces(int $places): void
    {
        $this->setRecordPropertyInteger('place', $places);
    }

    /**
     * Returns always an empty string.
     *
     * This function is just a dummy because the implementations of this
     * abstract function can differ widely.
     *
     * @return string always an empty string
     */
    public function getPlaceShort(): string
    {
        return '';
    }
}
