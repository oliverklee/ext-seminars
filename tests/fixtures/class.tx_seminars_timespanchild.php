<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class 'tx_seminars_timespanchild' for the 'seminars' extension.
 *
 * This is mere a class used for unit tests of the 'seminars' extension. Don't
 * use it for any other purpose.
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
	 * @param array TS setup configuration array, may be empty
	 */
	public function __construct(array $configuration) {
		parent::init($configuration);
	}

	/**
	 * Sets this time span's begin date and time.
	 *
	 * @param integer begin date and time as a UNIX timestamp, may be zero
	 */
	public function setBeginDateAndTime($beginDate) {
		$this->setRecordPropertyInteger('begin_date', $beginDate);
	}

	/**
	 * Sets this time span's end date and time.
	 *
	 * @param integer end date and time as a UNIX timestamp, may be zero
	 */
	public function setEndDateAndTime($endDate) {
		$this->setRecordPropertyInteger('end_date', $endDate);
	}

	/**
	 * Sets this time span's room.
	 *
	 * @param string room name
	 */
	public function setRoom($room) {
		$this->setRecordPropertyString('room', $room);
	}

	/**
	 * Sets the number of places for this time span.
	 *
	 * @param integer the number of places that are associated with this
	 * time span
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
?>