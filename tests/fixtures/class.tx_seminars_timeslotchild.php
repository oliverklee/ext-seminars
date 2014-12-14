<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2013 Niels Pardon (mail@niels-pardon.de)
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
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
final class tx_seminars_timeslotchild extends tx_seminars_timeslot {
	/**
	 * Sets the place field of the time slot.
	 *
	 * @param int $place the UID of the place (has to be > 0)
	 *
	 * @return void
	 */
	public function setPlace($place) {
		$this->setRecordPropertyInteger('place', $place);
	}

	/**
	 * Sets the entry date.
	 *
	 * @param int $entryDate the entry date as a UNIX timestamp (has to be >= 0, 0 will unset the entry date)
	 *
	 * @return void
	 */
	 public function setEntryDate($entryDate) {
		$this->setRecordPropertyInteger('entry_date', $entryDate);
	 }

	/**
	 * Sets the begin date and time.
	 *
	 * @param int $beginDate the begin date as a UNIX timestamp (has to be >= 0, 0 will unset the begin date)
	 *
	 * @return void
	 */
	 public function setBeginDate($beginDate) {
		$this->setRecordPropertyInteger('begin_date', $beginDate);
	 }
}