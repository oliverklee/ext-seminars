<?php
declare(strict_types = 1);

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingTimeSpan extends \Tx_Seminars_OldModel_AbstractTimeSpan
{
    /**
     * @var string
     *
     * string with the name of the SQL table this class corresponds to
     */
    public $tableName = 'tx_seminars_unit_testing';

    /**
     * @var array
     *
     * associative array with the values from/for the DB
     */
    public $recordData = [
        'begin_date' => 0,
        'end_date' => 0,
        'room' => '',
    ];

    /**
     * The constructor.
     *
     * @param array $configuration TS setup configuration array, may be empty
     */
    public function __construct(array $configuration)
    {
        $this->init($configuration);
    }

    /**
     * Sets this time span's begin date and time.
     *
     * @param int $beginDate begin date and time as a UNIX timestamp, may be zero
     *
     * @return void
     */
    public function setBeginDateAndTime($beginDate)
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
    public function setEndDateAndTime($endDate)
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
    public function setRoom($room)
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
    public function setNumberOfPlaces($places)
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
