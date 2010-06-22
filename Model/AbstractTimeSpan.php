<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_Model_AbstractTimeSpan' for the 'seminars' extension.
 *
 * This abstract class represents a time span.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class tx_seminars_Model_AbstractTimeSpan extends tx_oelib_Model {
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
	 * @param string our title to set, must not be empty
	 */
	public function setTitle($title) {
		if ($title == '') {
			throw new Exception('The parameter $title must not be empty.');
		}

		$this->setAsString('title', $title);
	}

	/**
	 * Returns our begin date as UNIX time-stamp.
	 *
	 * @return integer our begin date as UNIX time-stamp, will be >= 0,
	 *                 0 means "no begin date"
	 */
	public function getBeginDateAsUnixTimeStamp() {
		return $this->getAsInteger('begin_date');
	}

	/**
	 * Sets our begin date as UNIX time-stamp.
	 *
	 * @param integer our begin date as UNIX time-stamp, must be >= 0,
	 *                0 means "no begin date"
	 */
	public function setBeginDateAsUnixTimeStamp($beginDate) {
		if ($beginDate < 0) {
			throw new Exception('The parameter $beginDate must be >= 0.');
		}

		$this->setAsInteger('begin_date', $beginDate);
	}

	/**
	 * Returns whether this time-span has a begin date.
	 *
	 * @return boolean TRUE if this time-span has a begin date, FALSE otherwise
	 */
	public function hasBeginDate() {
		return $this->hasInteger('begin_date');
	}

	/**
	 * Returns our end date as UNIX time-stamp.
	 *
	 * @return integer our end date as UNIX time-stamp, will be >= 0,
	 *                 0 means "no end date"
	 */
	public function getEndDateAsUnixTimeStamp() {
		return $this->getAsInteger('end_date');
	}

	/**
	 * Sets our end date as UNIX time-stamp.
	 *
	 * @param integer our end date as UNIX time-stamp, must be >= 0,
	 *                0 means "no end date"
	 */
	public function setEndDateAsUnixTimeStamp($endDate) {
		if ($endDate < 0) {
			throw new Exception('The parameter $endDate must be >= 0.');
		}

		$this->setAsInteger('end_date', $endDate);
	}

	/**
	 * Returns whether this time-span has an end date.
	 *
	 * @return boolean TRUE if this time-span has an end date, FALSE otherwise
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
	 * @param string our room, may be empty
	 */
	public function setRoom($room) {
		$this->setAsString('room', $room);
	}

	/**
	 * Returns whether this time-span has a room.
	 *
	 * @return boolean TRUE if this time-span has a room, FALSE otherwise
	 */
	public function hasRoom() {
		return $this->hasString('room');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/AbstractTimeSpan.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/AbstractTimeSpan.php']);
}
?>