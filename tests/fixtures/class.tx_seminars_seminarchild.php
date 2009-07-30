<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Niels Pardon (mail@niels-pardon.de)
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
	/**
	 * @var string same as class name
	 */
	public $prefixId = 'tx_seminars_seminarchild';
	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'tests/fixtures/class.tx_seminars_seminarchild.php';

	/**
	 * The constructor.
	 *
	 * @param integer the UID of the event to retrieve from the DB, must
	 *                be > 0
	 * @param array TS setup configuration array, may be empty
	 */
	public function __construct($uid, array $configuration = array()) {
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
		$this->setRecordPropertyInteger(
			'deadline_unregistration', $unregistrationDeadline
		);
	}

	/**
	 * Sets the configuration value "allowRegistrationForEventsWithoutDate".
	 *
	 * @param integer whether this option is enabled or not, value must be
	 * either 0 or 1
	 */
	public function setAllowRegistrationForEventsWithoutDate($value) {
		$this->setConfigurationValue(
			'allowRegistrationForEventsWithoutDate', $value
		);
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
	 * Sets whether the event has a registration queue.
	 *
	 * @param boolean whether the event should have a registration queue
	 */
	public function setRegistrationQueue($hasRegistrationQueue) {
		$this->setRecordPropertyBoolean('queue_size', $hasRegistrationQueue);
	}

	/**
	 * Sets the number of attendances.
	 *
	 * @param integer the number of attendances, must be >= 0
	 */
	public function setNumberOfAttendances($number) {
		$this->numberOfAttendances = $number;
		$this->statisticsHaveBeenCalculated = true;
	}

	/**
	 * Sets the number of attendances on the registration queue.
	 *
	 * @param integer the number of attendances on the registration queue, must
	 *                be >= 0
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
	 * @param integer the number of places that are associated with this event,
	 *                must be >= 0
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
	 * @param integer the number of target groups that are associated with this
	 *                event, must be >= 0
	 */
	public function setNumberOfTargetGroups($targetGroups) {
		$this->setRecordPropertyInteger('target_groups', $targetGroups);
	}

	/**
	 * Sets the number of payment methods for this record.
	 *
	 * TODO: This function needs to be removed once the data type of the
	 * payment methods field was changed to an unsigned integer and we may use
	 * the function createRelationAndUpdateCounter() of the testing framework.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=2948
	 *
	 * @param integer the number of payment methods that are associated with
	 *                this event, must be >= 0
	 */
	public function setNumberOfPaymentMethods($paymentMethods) {
		$this->setRecordPropertyInteger('payment_methods', $paymentMethods);
	}

	/**
	 * Sets the number of organizing partners for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of organizing partners.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of organizing partners that are associated
	 *                with this event, must be >= 0
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
	 * @param integer the number of categories that are associated with this
	 *                event, must be >= 0
	 */
	public function setNumberOfCategories($number) {
		$this->setRecordPropertyInteger('categories', $number);
	}

	/**
	 * Sets the number of organizers for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of organizers.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of organizers that are associated with this
	 *                event, must be >= 0
	 */
	public function setNumberOfOrganizers($number) {
		$this->setRecordPropertyInteger('organizers', $number);
	}

	/**
	 * Sets the number of speakers for this record.
	 *
	 * TODO: This function needs to be removed once the testing framework
	 * can update the counter for the number of speakers.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1403
	 *
	 * @param integer the number of speakers that are associated with this
	 *                event, must be >= 0
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
	 * @param integer the number of partners that are associated with this
	 *                event, must be >= 0
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
	 * @param integer the number of tutors that are associated with this event,
	 *                must be >= 0
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
	 * @param integer the number of leaders that are associated with this event,
	 *                must be >= 0
	 */
	public function setNumberOfLeaders($number) {
		$this->setRecordPropertyInteger('leaders', $number);
	}

	/**
	 * Sets whether the collision check should be skipped for this event.
	 *
	 * @param boolean whether the collision check should be skipped for this
	 *                event
	 */
	public function setSkipCollisionCheck($skipIt) {
		$this->setRecordPropertyBoolean('skip_collision_check', $skipIt);
	}

	/**
	 * Sets the record type for this event record.
	 *
	 * @param integer the record type for this event record, must be either
	 *                SEMINARS_RECORD_TYPE_COMPLETE, SEMINARS_RECORD_TYPE_TOPIC
	 *                or SEMINARS_RECORD_TYPE_DATE
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
	 * @param integer this record's start time as a UNIX timestamp, set to 0 to
	 *                set no start time
	 */
	public function setRecordStartTime($timeStamp) {
		$this->setRecordPropertyInteger('starttime', $timeStamp);
	}

	/**
	 * Sets this record's end timestamp (concerning the visibility in TYPO3).
	 *
	 * @param integer this record's end time as a UNIX timestamp, set to 0 to
	 *                set no start time
	 */
	public function setRecordEndTime($timeStamp) {
		$this->setRecordPropertyInteger('endtime', $timeStamp);
	}

 	/**
	 * Sets the TypoScript configuration for the parameter
	 * allowUnregistrationWithEmptyWaitingList.
	 *
	 * @param boolean whether unregistration is possible even when the waiting
	 *                list is empty
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
	 * Sets this event's license expiry.
	 *
	 * @param integer the license expiry as a timestamp, may be 0
	 */
	public function setExpiry($expiry) {
		$this->setRecordPropertyInteger('expiry', $expiry);
	}

	/**
	 * Gets our place (or places) with address and links as HTML, not RTE'ed yet,
	 * separated by LF.
	 *
	 * Returns a localized string "will be announced" if the seminar has no
	 * places set.
	 *
	 * @return string our places description (or '' if there is an error)
	 */
	public function getPlaceWithDetailsRaw() {
		return parent::getPlaceWithDetailsRaw();
	}

	/**
	 * Sets the number of lodgings for this record.
	 *
	 * @param integer the number of lodgings that are associated with this event,
	 *                must be >= 0
	 */
	public function setNumberOfLodgings($lodgings) {
		$this->setRecordPropertyInteger('lodgings', $lodgings);
	}

	/**
	 * Gets our speaker (or speakers), as HTML with details and URLs, but not
	 * RTE'ed yet.
	 * Returns an empty string if this event doesn't have any speakers.
	 *
	 * As speakers can be related to this event as speakers, partners, tutors or
	 * leaders, the type relation can be specified. The default is "speakers".
	 *
	 * @param string the relation in which the speakers stand to this event:
	 *               "speakers" (default), "partners", "tutors" or "leaders"
	 *
	 * @return string our speakers (or '' if there is an error)
	 */
	public function getSpeakersWithDescriptionRaw($speakerRelation = 'speakers') {
		return parent::getSpeakersWithDescriptionRaw($speakerRelation);
	}

	/**
	 * Gets our allowed payment methods, just as plain text separated by LF,
	 * without the detailed description.
	 * Returns an empty string if this seminar doesn't have any payment methods.
	 *
	 * @return string our payment methods as plain text (or '' if there
	 *                is an error)
	 */
	public function getPaymentMethodsPlainShort() {
		return parent::getPaymentMethodsPlainShort();
	}

	/**
	 * Gets our organizer's names (and URLs), separated by LF.
	 *
	 * @return string names and homepages of our organizers or an
	 *                empty string if there are no organizers
	 */
	public function getOrganizersRaw() {
		return parent::getOrganizersRaw();
	}


	/**
	 * Sets whether registration is needed.
	 *
	 * @param boolean whether registration is needed
	 */
	public function setNeedsRegistration($needsRegistration) {
		$this->setRecordPropertyBoolean(
			'needs_registration', $needsRegistration
		);
	}

	/**
	 * Sets the registration deadline.
	 *
	 * @param integer the registration deadline as timestamp, set to 0 to unset
	 *                the registration deadline
	 */
	public function setRegistrationDeadline($registrationDeadline) {
		$this->setRecordPropertyInteger(
			'deadline_registration', $registrationDeadline
		);
	}

	/**
	 * Sets the seminar to have unlimitedVacancies by setting needs_registration
	 * to 1 and attendees_max to 0.
	 */
	public function setUnlimitedVacancies() {
		$this->setNeedsRegistration(true);
		$this->setAttendancesMax(0);
	}

	/**
	 * Sets the registration begin date.
	 *
	 * @param integer the registration begin date as time-stamp, set to 0 to
	 *                unset the registration begin date
	 */
	public function setRegistrationBeginDate($registrationBeginDate) {
		$this->setRecordPropertyInteger(
			'begin_date_registration', $registrationBeginDate
		);
	}

	/**
	 * Sets the number of offline registrations.
	 *
	 * @param integer
	 *        $offlineRegistrations the number of offline registrations for this
	 *        event, must be >= 0
	 */
	public function setOfflineRegistrationNumber($offlineRegistrations) {
		$this->setRecordPropertyInteger(
			'offline_attendees', $offlineRegistrations
		);
	}
}
?>