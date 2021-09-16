<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures;

/**
 * This class represents a test object from the database.
 */
final class TestingTimeSpan extends \Tx_Seminars_OldModel_AbstractTimeSpan
{
    /**
     * @var bool whether to call `TemplateHelper::init()` during construction in BE mode
     */
    protected $needsTemplateHelperInitialization = false;

    /**
     * @param array $configuration
     *
     * @return void
     */
    public function overrideConfiguration(array $configuration)
    {
        $this->conf = $configuration;
    }

    /**
     * Sets this time span's begin date and time.
     *
     * @param int $beginDate begin date and time as a UNIX timestamp, may be zero
     *
     * @return void
     */
    public function setBeginDateAndTime(int $beginDate)
    {
        $this->setRecordPropertyInteger('begin_date', $beginDate);
    }

    /**
     * Sets this time span's end date and time.
     *
     * @param int $endDate end date and time as a UNIX timestamp, may be zero
     *
     * @return void
     */
    public function setEndDateAndTime(int $endDate)
    {
        $this->setRecordPropertyInteger('end_date', $endDate);
    }

    /**
     * Sets this time span's room.
     *
     * @param string $room room name
     *
     * @return void
     */
    public function setRoom(string $room)
    {
        $this->setRecordPropertyString('room', $room);
    }

    /**
     * Sets the number of places for this time span.
     *
     * @param int $places the number of places that are associated with this time span
     *
     * @return void
     */
    public function setNumberOfPlaces(int $places)
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
