<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_seminar.php');

/**
 * Class 'tx_seminars_seminarchild' for the 'seminars' extension.
 *
 * This is mere a class used for unit tests of the 'seminars' extension. Don't
 * use it for any other purpose.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
final class tx_seminars_seminarchild extends tx_seminars_seminar {
	public $prefixId = 'tx_seminars_seminarchild';
	public $scriptRelPath
		= 'tests/fixtures/class.tx_seminars_seminarchild.php';

	/**
	 * The constructor.
	 *
	 * @param integer the UID of the event to retrieve from the DB, must
	 * be > 0
	 * @param array TS setup configuration array, may be empty
	 */
	public function __construct($uid, array $configuration) {
		parent::__construct($uid);

		$this->conf = $configuration;
	}

	/**
	 * Sets the event data.
	 *
	 * @param array event data array
	 */
	public function setEventData(array $eventData) {
		$this->recordData = $eventData;
		$this->isInDb = true;
	}

	/**
	 * Sets the event's unregistration deadline.
	 *
	 * @param integer unregistration deadline as UNIX timestamp
	 */
	public function setUnregistrationDeadline($unregistrationDeadline) {
		$this->setRecordPropertyInteger('deadline_unregistration', $unregistrationDeadline);
	}

	/**
	 * Sets the configuration value "allowRegistrationForEventsWithoutDate".
	 *
	 * @param integer whether this option is enabled or not, value must be
	 * either 0 or 1
	 */
	public function setAllowRegistrationForEventsWithoutDate($value) {
		$this->setConfigurationValue('allowRegistrationForEventsWithoutDate', $value);
	}

	/**
	 * Sets the event's begin date.
	 *
	 * @param integer begin date as UNIX timestamp (has to be >= 0, 0 will
	 * unset the begin date)
	 */
	public function setBeginDate($beginDate) {
		$this->setRecordPropertyInteger('begin_date', $beginDate);
	}

	/**
	 * Sets the event's end date.
	 *
	 * @param integer end date as UNIX timestamp (has to be >= 0, 0 will
	 * unset the end date)
	 */
	public function setEndDate($endDate) {
		$this->setRecordPropertyInteger('end_date', $endDate);
	}

	/**
	 * Sets the event's maximum number of attendances.
	 *
	 * @param integer maximum attendances number
	 */
	public function setAttendancesMax($attendancesMax) {
		$this->setRecordPropertyInteger('attendees_max', $attendancesMax);
	}

	/**
	 * Sets the configuration for showTimeOfUnregistrationDeadline.
	 *
	 * @param integer value for showTimeOfUnregistrationDeadline (0 or 1)
	 */
	public function setShowTimeOfUnregistrationDeadline($value) {
		$this->setConfigurationValue(
			'showTimeOfUnregistrationDeadline', $value
		);
	}

	/**
	 * Sets the TypoScript configuration for the parameter
	 * unregistrationDeadlineDaysBeforeBeginDate.
	 *
	 * @param integer days before the begin date until unregistration
	 * should be possible
	 */
	public function setGlobalUnregistrationDeadline($days) {
		$this->setConfigurationValue(
			'unregistrationDeadlineDaysBeforeBeginDate', $days
		);
	}

	/**
	 * Sets the registration queue size.
	 *
	 * @param integer size of the registration queue
	 */
	public function setRegistrationQueueSize($size) {
		$this->setRecordPropertyInteger('queue_size', $size);
	}

	/**
	 * Sets the number of attendances.
	 *
	 * @param integer number of attendances
	 */
	public function setNumberOfAttendances($number) {
		$this->numberOfAttendances = $number;
		$this->statisticsHaveBeenCalculated = true;
	}

	/**
	 * Sets the number of attendances on the registration queue.
	 *
	 * @param integer number of attendances on the registration queue
	 */
	public function setNumberOfAttendancesOnQueue($number) {
		$this->numberOfAttendancesOnQueue = $number;
		$this->statisticsHaveBeenCalculated = true;
	}

	/**
	 * Sets the number of places for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of places.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of places that are associated with this
	 * event
	 */
	public function setNumberOfPlaces($places) {
		$this->setRecordPropertyInteger('place', $places);
	}

	/**
	 * Sets the number of target groups for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of target groups.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of target groups that are associated with
	 * this event
	 */
	public function setNumberOfTargetGroups($targetGroups) {
		$this->setRecordPropertyInteger('target_groups', $targetGroups);
	}

	/**
	 * Adds a payment method to this event.
	 *
	 * @param integer the UID of the payment method to add
	 */
	public function addPaymentMethod($uid) {
		if ($uid == 0) {
			return;
		}

		$paymentMethods = t3lib_div::trimExplode(
			',',
			$this->getPaymentMethodsUids(),
			1
		);

		if (!in_array($uid, $paymentMethods)) {
			$paymentMethods[] = $uid;
		}

		$this->setRecordPropertyString(
			'payment_methods',
			implode(',', $paymentMethods)
		);
	}

	/**
	 * Sets the number of organizing partners for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of organizing partners.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of organizing partners that are
	 * associated with this event, must be >= 0
	 */
	public function setNumberOfOrganizingPartners($numberOfOrganizingPartners) {
		$this->setRecordPropertyInteger(
			'organizing_partners', $numberOfOrganizingPartners
		);
	}

	/**
	 * Sets the number of categories for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of categories.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of categories that are associated with
	 * this event
	 */
	public function setNumberOfCategories($number) {
		$this->setRecordPropertyInteger('categories', $number);
	}

	/**
	 * Adds an organizer to this event.
	 *
	 * @param integer the UID of the organizer to add, must not be 0
	 */
	public function addOrganizer($uid) {
		if ($uid == 0) {
			throw new Exception('UID must not be 0.');
		}

		$organizers = t3lib_div::trimExplode(
			',',
			$this->getOrganizersUids(),
			1
		);

		if (!in_array($uid, $organizers)) {
			$organizers[] = $uid;
		}

		$this->setRecordPropertyString(
			'organizers',
			implode(',', $organizers)
		);
	}

	/**
	 * Sets the number of speakers for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of speakers.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of speakers that are associated with
	 * this event
	 */
	public function setNumberOfSpeakers($number) {
		$this->setRecordPropertyInteger('speakers', $number);
	}

	/**
	 * Sets the number of partners for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of partners.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of partners that are associated with
	 * this event
	 */
	public function setNumberOfPartners($number) {
		$this->setRecordPropertyInteger('partners', $number);
	}

	/**
	 * Sets the number of tutors for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of tutors.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of tutors that are associated with
	 * this event
	 */
	public function setNumberOfTutors($number) {
		$this->setRecordPropertyInteger('tutors', $number);
	}

	/**
	 * Sets the number of leaders for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of leaders.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of leaders that are associated with
	 * this event
	 */
	public function setNumberOfLeaders($number) {
		$this->setRecordPropertyInteger('leaders', $number);
	}

	/**
	 * Sets whether the collision check should be skipped for this event.
	 *
	 * @param boolean whether the collision check should be skipped for
	 * this event
	 */
	public function setSkipCollisionCheck($skipIt) {
		$this->setRecordPropertyBoolean('skip_collision_check', $skipIt);
	}

	/**
	 * Sets the record type for this event record.
	 *
	 * @param integer the record type for this event record, must be
	 * either SEMINARS_RECORD_TYPE_COMPLETE,
	 * SEMINARS_RECORD_TYPE_TOPIC or
	 * SEMINARS_RECORD_TYPE_DATE
	 */
	public function setRecordType($recordType) {
		$this->setRecordPropertyInteger('object_type', $recordType);
	}

	/**
	 * Sets the "hidden" flag of this record (concerning the visibility in
	 * TYPO3).
	 *
	 * @param boolean whether this record should be marked as hidden
	 */
	public function setHidden($hidden) {
		$this->setRecordPropertyBoolean('hidden', $hidden);
	}

	/**
	 * Sets this record's start timestamp (concerning the visibility in TYPO3).
	 *
	 * @param integer this record's start time as a UNIX timestamp,
	 * set to 0 to set no start time
	 */
	public function setRecordStartTime($timeStamp) {
		$this->setRecordPropertyInteger('starttime', $timeStamp);
	}

	/**
	 * Sets this record's end timestamp (concerning the visibility in TYPO3).
	 *
	 * @param integer this record's end time as a UNIX timestamp,
	 * set to 0 to set no start time
	 */
	public function setRecordEndTime($timeStamp) {
		$this->setRecordPropertyInteger('endtime', $timeStamp);
	}

 	/**
	 * Sets the TypoScript configuration for the parameter
	 * allowUnregistrationWithEmptyWaitingList.
	 *
	 * @param boolean whether unregistration is possible even when the
	 * waiting list is empty
	 */
	public function setAllowUnregistrationWithEmptyWaitingList($isAllowed) {
		$this->setConfigurationValue(
			'allowUnregistrationWithEmptyWaitingList', intval($isAllowed)
		);
	}

	/**
	 * Sets the UID of the owner FE user.
	 *
	 * @param integer the UID of the owner FE user, must be >= 0
	 */
	public function setOwnerUid($ownerUid) {
		$this->setRecordPropertyInteger('owner_feuser', $ownerUid);
	}

	/**
	 * Sets the number of time slots.
	 *
	 * @param integer the number of time slots for this event, must be >= 0
	 */
	public function setNumberOfTimeSlots($numberOfTimeSlots) {
		$this->setRecordPropertyInteger('timeslots', $numberOfTimeSlots);
	}

	/**
	 * Sets the file name of the image.
	 *
	 * @param string the name of the image, must not be empty
	 */
	public function setImage($fileName) {
		$this->setRecordPropertyString('image', $fileName);
	}

	/**
	 * Sets whether multiple registrations are allowed.
	 *
	 * @param boolean whether multiple registrations should be allowed
	 */
	public function setAllowsMultipleRegistrations($allowMultipleRegistrations) {
		$this->setRecordPropertyBoolean(
			'allows_multiple_registrations',
			$allowMultipleRegistrations
		);
	}

	/**
	 * Sets whether this event is canceled.
	 *
	 * @param boolean whether the event has been canceled
	 */
	public function setCanceled($isCanceled) {
		$this->setRecordPropertyBoolean('cancelled', $isCanceled);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminarst/tests/fixtures/class.tx_seminars_seminarchild.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/tests/fixtures/class.tx_seminars_seminarchild.php']);
}
?>