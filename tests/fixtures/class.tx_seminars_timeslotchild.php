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