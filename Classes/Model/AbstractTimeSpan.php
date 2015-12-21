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
 * This abstract class represents a time span.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class tx_seminars_Model_AbstractTimeSpan extends tx_oelib_Model implements tx_seminars_Interface_Titled {
	/**
	 * Returns our title.
	 *
	 * @return string our title, will not be empty
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Sets our title.
	 *
	 * @param string $title our title to set, must not be empty
	 *
	 * @return void
	 */
	public function setTitle($title) {
		if ($title == '') {
			throw new InvalidArgumentException('The parameter $title must not be empty.', 1333293446);
		}

		$this->setAsString('title', $title);
	}

	/**
	 * Returns our begin date as UNIX time-stamp.
	 *
	 * @return int our begin date as UNIX time-stamp, will be >= 0,
	 *                 0 means "no begin date"
	 */
	public function getBeginDateAsUnixTimeStamp() {
		return $this->getAsInteger('begin_date');
	}

	/**
	 * Sets our begin date as UNIX time-stamp.
	 *
	 * @param int $beginDate our begin date as UNIX time-stamp, must be >= 0, 0 means "no begin date"
	 *
	 * @return void
	 */
	public function setBeginDateAsUnixTimeStamp($beginDate) {
		if ($beginDate < 0) {
			throw new InvalidArgumentException('The parameter $beginDate must be >= 0.', 1333293455);
		}

		$this->setAsInteger('begin_date', $beginDate);
	}

	/**
	 * Returns whether this time-span has a begin date.
	 *
	 * @return bool TRUE if this time-span has a begin date, FALSE otherwise
	 */
	public function hasBeginDate() {
		return $this->hasInteger('begin_date');
	}

	/**
	 * Returns our end date as UNIX time-stamp.
	 *
	 * @return int our end date as UNIX time-stamp, will be >= 0,
	 *                 0 means "no end date"
	 */
	public function getEndDateAsUnixTimeStamp() {
		return $this->getAsInteger('end_date');
	}

	/**
	 * Sets our end date as UNIX time-stamp.
	 *
	 * @param int $endDate our end date as UNIX time-stamp, must be >= 0, 0 means "no end date"
	 *
	 * @return void
	 */
	public function setEndDateAsUnixTimeStamp($endDate) {
		if ($endDate < 0) {
			throw new InvalidArgumentException('The parameter $endDate must be >= 0.', 1333293465);
		}

		$this->setAsInteger('end_date', $endDate);
	}

	/**
	 * Returns whether this time-span has an end date.
	 *
	 * @return bool TRUE if this time-span has an end date, FALSE otherwise
	 */
	public function hasEndDate() {
		return $this->hasInteger('end_date');
	}

	/**
	 * Returns our speakers.
	 *
	 * @return tx_oelib_List our speakers, will be empty if this time-span has
	 *                       no speakers
	 */
	public function getSpeakers() {
		return $this->getAsList('speakers');
	}

	/**
	 * Returns our room.
	 *
	 * @return string our room, will be empty if this time-span has no place
	 */
	public function getRoom() {
		return $this->getAsString('room');
	}

	/**
	 * Sets our room.
	 *
	 * @param string $room our room, may be empty
	 *
	 * @return void
	 */
	public function setRoom($room) {
		$this->setAsString('room', $room);
	}

	/**
	 * Returns whether this time-span has a room.
	 *
	 * @return bool TRUE if this time-span has a room, FALSE otherwise
	 */
	public function hasRoom() {
		return $this->hasString('room');
	}
}