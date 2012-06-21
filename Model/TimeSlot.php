<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_Model_TimeSlot' for the 'seminars' extension.
 *
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
	 * @return integer our entry date as UNIX time-stamp, will be >= 0,
	 *                 0 means "no entry date"
	 */
	public function getEntryDateAsUnixTimeStamp() {
		return $this->getAsInteger('entry_date');
	}

	/**
	 * Sets our entry date as UNIX time-stamp.
	 *
	 * @param integer $entryDate our entry date as UNIX time-stamp, will be >= 0, 0 means "no entry date"
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
	 * @return boolean TRUE if this time-slot has an entry date, FALSE otherwise
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
?>