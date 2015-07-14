<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class tx_seminars_timespanchild extends tx_seminars_timespan {
	/** same as class name */
	public $prefixId = 'tx_seminars_timespanchild';
	/**  path to this script relative to the extension dir */
	public $scriptRelPath
		= 'tests/fixtures/class.tx_seminars_timespanchild.php';

	/** string with the name of the SQL table this class corresponds to */
	public $tableName = 'tx_seminars_unit_testing';
	/** associative array with the values from/for the DB */
	public $recordData = array(
		'begin_date' => 0,
		'end_date' => 0,
		'room' => ''
	);

	/**
	 * The constructor.
	 *
	 * @param array $configuration TS setup configuration array, may be empty
	 */
	public function __construct(array $configuration) {
		parent::init($configuration);
	}

	/**
	 * Sets this time span's begin date and time.
	 *
	 * @param int $beginDate begin date and time as a UNIX timestamp, may be zero
	 *
	 * @return void
	 */
	public function setBeginDateAndTime($beginDate) {
		$this->setRecordPropertyInteger('begin_date', $beginDate);
	}

	/**
	 * Sets this time span's end date and time.
	 *
	 * @param int $endDate end date and time as a UNIX timestamp, may be zero
	 *
	 * @return void
	 */
	public function setEndDateAndTime($endDate) {
		$this->setRecordPropertyInteger('end_date', $endDate);
	}

	/**
	 * Sets this time span's room.
	 *
	 * @param string $room room name
	 *
	 * @return void
	 */
	public function setRoom($room) {
		$this->setRecordPropertyString('room', $room);
	}

	/**
	 * Sets the number of places for this time span.
	 *
	 * @param int $places the number of places that are associated with this time span
	 *
	 * @return void
	 */
	public function setNumberOfPlaces($places) {
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
	public function getPlaceShort() {
		return '';
	}
}