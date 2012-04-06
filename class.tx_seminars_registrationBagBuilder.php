<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_registrationBagBuilder' for the 'seminars' extension.
 *
 * This builder class creates customized registrationbag objects.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_registrationBagBuilder extends tx_seminars_bagbuilder {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_registrationbag';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = SEMINARS_TABLE_ATTENDANCES;

	/**
	 * @var string the sorting field
	 */
	protected $orderBy = 'crdate';

	/**
	 * Limits the bag to the registrations of the events provided by the
	 * parameter $eventUids.
	 *
	 * @param integer the UID of the event to which the registration
	 *                selection should be limited, must be > 0
	 */
	public function limitToEvent($eventUid) {
		if ($eventUid <= 0) {
			throw new InvalidArgumentException('The parameter $eventUid must be > 0.', 1333292912);
		}

		$this->whereClauseParts['event'] = SEMINARS_TABLE_ATTENDANCES .
			'.seminar=' . $eventUid;
	}

	/**
	 * Limits the bag to paid registrations.
	 */
	public function limitToPaid() {
		$this->whereClauseParts['paid'] = SEMINARS_TABLE_ATTENDANCES .
			'.datepaid != 0';
	}

	/**
	 * Limits the bag to unpaid registrations.
	 */
	public function limitToUnpaid() {
		$this->whereClauseParts['paid'] = SEMINARS_TABLE_ATTENDANCES .
			'.datepaid = 0';
	}

	/**
	 * Removes the limitation for paid or unpaid registrations.
	 */
	public function removePaymentLimitation() {
		unset($this->whereClauseParts['paid']);
	}

	/**
	 * Limits the bag to the registrations on the registration queue.
	 */
	public function limitToOnQueue() {
		$this->whereClauseParts['queue'] = SEMINARS_TABLE_ATTENDANCES .
			'.registration_queue=1';
	}

	/**
	 * Limits the bag to the regular registrations (which are not on the
	 * registration queue).
	 */
	public function limitToRegular() {
		$this->whereClauseParts['queue'] = SEMINARS_TABLE_ATTENDANCES .
			'.registration_queue=0';
	}

	/**
	 * Removes the limitation for regular or on queue registrations.
	 */
	public function removeQueueLimitation() {
		unset($this->whereClauseParts['queue']);
	}

	/**
	 * Limits the bag to contain only registrations with seats equal or less
	 * than the seats given in the parameter $seats.
	 *
	 * @param integer the number of seats to filter for, set to 0 to remove the
	 *                limitation, must be >= 0
	 */
	public function limitToSeatsAtMost($seats = 0) {
		if ($seats < 0) {
			throw new InvalidArgumentException('The parameter $seats must be >= 0.', 1333292923);
		}

		if ($seats == 0) {
			unset($this->whereClauseParts['seats']);
			return;
		}

		$this->whereClauseParts['seats'] = SEMINARS_TABLE_ATTENDANCES .
			'.seats<=' . $seats;
	}

	/**
	 * Limits the bag to registrations to the front-end user with the UID
	 * $feUserUid.
	 *
	 * @param integer the front-end user UID to limit the bag for, must be >= 0,
	 *                set to 0 to remove the limitation
	 */
	public function limitToAttendee($frontEndUserUid) {
		if ($frontEndUserUid < 0) {
			throw new InvalidArgumentException('The parameter $frontEndUserUid must be >= 0.', 1333740128);
		}

		if ($frontEndUserUid == 0) {
			unset($this->whereClauseParts['attendee']);
			return;
		}

		$this->whereClauseParts['attendee'] = SEMINARS_TABLE_ATTENDANCES .
			'.user=' . $frontEndUserUid;
	}

	/**
	 * Sets the ORDER BY by statement for the bag to build and joins the
	 * registration results with the corresponding events.
	 *
	 * @param string the ORDER BY statement to set, may be empty
	 */
	public function setOrderByEventColumn($orderBy) {
		$this->addAdditionalTableName(SEMINARS_TABLE_SEMINARS);
		$this->whereClauseParts['orderByEvent'] = SEMINARS_TABLE_ATTENDANCES .
			'.seminar=' . SEMINARS_TABLE_SEMINARS . '.uid';
		$this->setOrderBy($orderBy);
	}

	/**
	 * Limits the bag to registrations to which a non-deleted FE user record
	 * exists.
	 */
	public function limitToExistingUsers() {
		$this->whereClauseParts['existingUsers'] = 'EXISTS (
			SELECT * FROM fe_users WHERE ' .
			' fe_users.uid='. SEMINARS_TABLE_ATTENDANCES . '.user' .
			tx_oelib_db::enableFields('fe_users') . ')';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationBagBuilder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registrationBagBuilder.php']);
}
?>