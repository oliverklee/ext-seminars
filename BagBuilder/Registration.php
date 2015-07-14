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
 * This builder class creates customized registration bag objects.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_BagBuilder_Registration extends tx_seminars_BagBuilder_Abstract {
	/**
	 * @var string class name of the bag class that will be built
	 */
	protected $bagClassName = 'tx_seminars_Bag_Registration';

	/**
	 * @var string the table name of the bag to build
	 */
	protected $tableName = 'tx_seminars_attendances';

	/**
	 * @var string the sorting field
	 */
	protected $orderBy = 'crdate';

	/**
	 * Limits the bag to the registrations of the events provided by the
	 * parameter $eventUids.
	 *
	 * @param int $eventUid the UID of the event to which the registration selection should be limited, must be > 0
	 *
	 * @return void
	 */
	public function limitToEvent($eventUid) {
		if ($eventUid <= 0) {
			throw new InvalidArgumentException('The parameter $eventUid must be > 0.', 1333292912);
		}

		$this->whereClauseParts['event'] = 'tx_seminars_attendances' .
			'.seminar=' . $eventUid;
	}

	/**
	 * Limits the bag to paid registrations.
	 *
	 * @return void
	 */
	public function limitToPaid() {
		$this->whereClauseParts['paid'] = 'tx_seminars_attendances' .
			'.datepaid <> 0';
	}

	/**
	 * Limits the bag to unpaid registrations.
	 *
	 * @return void
	 */
	public function limitToUnpaid() {
		$this->whereClauseParts['paid'] = 'tx_seminars_attendances' .
			'.datepaid = 0';
	}

	/**
	 * Removes the limitation for paid or unpaid registrations.
	 *
	 * @return void
	 */
	public function removePaymentLimitation() {
		unset($this->whereClauseParts['paid']);
	}

	/**
	 * Limits the bag to the registrations on the registration queue.
	 *
	 * @return void
	 */
	public function limitToOnQueue() {
		$this->whereClauseParts['queue'] = 'tx_seminars_attendances' .
			'.registration_queue=1';
	}

	/**
	 * Limits the bag to the regular registrations (which are not on the
	 * registration queue).
	 *
	 * @return void
	 */
	public function limitToRegular() {
		$this->whereClauseParts['queue'] = 'tx_seminars_attendances' .
			'.registration_queue=0';
	}

	/**
	 * Removes the limitation for regular or on queue registrations.
	 *
	 * @return void
	 */
	public function removeQueueLimitation() {
		unset($this->whereClauseParts['queue']);
	}

	/**
	 * Limits the bag to contain only registrations with seats equal or less
	 * than the seats given in the parameter $seats.
	 *
	 * @param int $seats the number of seats to filter for, set to 0 to remove the limitation, must be >= 0
	 *
	 * @return void
	 */
	public function limitToSeatsAtMost($seats = 0) {
		if ($seats < 0) {
			throw new InvalidArgumentException('The parameter $seats must be >= 0.', 1333292923);
		}

		if ($seats == 0) {
			unset($this->whereClauseParts['seats']);
			return;
		}

		$this->whereClauseParts['seats'] = 'tx_seminars_attendances' .
			'.seats<=' . $seats;
	}

	/**
	 * Limits the bag to registrations to the front-end user $user.
	 *
	 * These registration will either be those for which $user has signed up
	 * himself, or for which they have been entered as "additional registered
	 * persons".
	 *
	 * @param tx_seminars_Model_FrontEndUser $user
	 *        the front-end user to limit the bag for, set to NULL to remove the
	 *        limitation
	 *
	 * @return void
	 */
	public function limitToAttendee(
		tx_seminars_Model_FrontEndUser $user = NULL
	) {
		if ($user === NULL) {
			unset($this->whereClauseParts['attendee']);
			return;
		}

		$whereClause = 'tx_seminars_attendances.user = ' . $user->getUid();
		if ($user->getRegistration() !== NULL) {
			$whereClause .= ' OR tx_seminars_attendances.uid = ' .
				$user->getRegistration()->getUid();
		}

		$this->whereClauseParts['attendee'] = $whereClause;
	}

	/**
	 * Sets the ORDER BY by statement for the bag to build and joins the
	 * registration results with the corresponding events.
	 *
	 * @param string $orderBy the ORDER BY statement to set, may be empty
	 *
	 * @return void
	 */
	public function setOrderByEventColumn($orderBy) {
		$this->addAdditionalTableName('tx_seminars_seminars');
		$this->whereClauseParts['orderByEvent'] = 'tx_seminars_attendances' .
			'.seminar = tx_seminars_seminars.uid';
		$this->setOrderBy($orderBy);
	}

	/**
	 * Limits the bag to registrations to which a non-deleted FE user record
	 * exists.
	 *
	 * @return void
	 */
	public function limitToExistingUsers() {
		$this->whereClauseParts['existingUsers'] = 'EXISTS (
			SELECT * FROM fe_users WHERE ' .
			' fe_users.uid = tx_seminars_attendances.user' .
			tx_oelib_db::enableFields('fe_users') . ')';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Registration.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/BagBuilder/Registration.php']);
}