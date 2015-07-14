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
 * This class represents a time-slot.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_TimeSlot extends tx_seminars_Model_AbstractTimeSpan {
	/**
	 * Returns our entry date as UNIX time-stamp.
	 *
	 * @return int our entry date as UNIX time-stamp, will be >= 0,
	 *                 0 means "no entry date"
	 */
	public function getEntryDateAsUnixTimeStamp() {
		return $this->getAsInteger('entry_date');
	}

	/**
	 * Sets our entry date as UNIX time-stamp.
	 *
	 * @param int $entryDate our entry date as UNIX time-stamp, will be >= 0, 0 means "no entry date"
	 *
	 * @return void
	 */
	public function setEntryDateAsUnixTimeStamp($entryDate) {
		if ($entryDate < 0) {
			throw new InvalidArgumentException('The parameter $entryDate must be >= 0.', 1333297074);
		}

		$this->setAsInteger('entry_date', $entryDate);
	}

	/**
	 * Returns whether this time-slot has an entry date.
	 *
	 * @return bool TRUE if this time-slot has an entry date, FALSE otherwise
	 */
	public function hasEntryDate() {
		return $this->hasInteger('entry_date');
	}

	/**
	 * Returns our place.
	 *
	 * @return tx_seminars_Model_Place our place, will be NULL if this time-slot
	 *                                 has no place
	 */
	public function getPlace() {
		return $this->getAsModel('place');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/TimeSlot.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/TimeSlot.php']);
}