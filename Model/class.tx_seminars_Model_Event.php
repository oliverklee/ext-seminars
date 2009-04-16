<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'tx_seminars_Model_Event' for the 'seminars' extension.
 *
 * This class represents an event.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_Event extends tx_seminars_Model_AbstractTimeSpan {
	/**
	 * @var integer represents the type for a single event
	 */
	const TYPE_COMPLETE = 0;

	/**
	 * @var integer represents the type for an event topic
	 */
	const TYPE_TOPIC = 1;

	/**
	 * @var integer represents the type for an event date
	 */
	const TYPE_DATE = 2;

	/**
	 * @var integer the status "planned" for an event
	 */
	const STATUS_PLANNED = 0;

	/**
	 * @var integer the status "canceled" for an event
	 */
	const STATUS_CANCELED = 1;

	/**
	 * @var integer the status "confirmed" for an event
	 */
	const STATUS_CONFIRMED = 2;

	/**
	 * Returns our topic.
	 *
	 * This method may only be called for date records.
	 *
	 * @return tx_seminars_Model_Event our topic, will be null if this event has
	 *                                 no topic
	 */
	public function getTopic() {
		if ($this->getAsInteger('object_type') != self::TYPE_DATE) {
			throw new Exception(
				'This function may only be called for date records.'
			);
		}

		return $this->getAsModel('topic');
	}

	/**
	 * Returns our subtitle.
	 *
	 * @return string our subtitle, will be empty if this event has no subtitle
	 */
	public function getSubtitle() {
		return $this->getAsString('subtitle');
	}

	/**
	 * Sets our subtitle.
	 *
	 * @param string our subtitle to set, may be empty
	 */
	public function setSubtitle($subtitle) {
		$this->setAsString('subtitle', $subtitle);
	}

	/**
	 * Returns whether this event has a subtitle.
	 *
	 * @return boolean true if this event has a subtitle, false otherwise
	 */
	public function hasSubtitle() {
		return $this->hasString('subtitle');
	}

	/**
	 * Returns our categories.
	 *
	 * @return tx_oelib_List our categories
	 */
	public function getCategories() {
		return $this->getAsList('categories');
	}

	/**
	 * Returns our teaser.
	 *
	 * @return string our teaser, might be empty
	 */
	public function getTeaser() {
		return $this->getAsString('teaser');
	}

	/**
	 * Sets our teaser.
	 *
	 * @param string our teaser, may be empty
	 */
	public function setTeaser($teaser) {
		$this->setAsString('teaser', $teaser);
	}

	/**
	 * Returns whether this event has a teaser.
	 *
	 * @return boolean true if this event has a teaser, false otherwise
	 */
	public function hasTeaser() {
		return $this->hasString('teaser');
	}

	/**
	 * Returns our description.
	 *
	 * @return string our description, might be empty
	 */
	public function getDescription() {
		return $this->getAsString('description');
	}

	/**
	 * Sets our description.
	 *
	 * @param string our description, may be empty
	 */
	public function setDescription($description) {
		$this->setAsString('description', $description);
	}

	/**
	 * Returns whether this event has a description.
	 *
	 * @return string true if this event has a description, false otherwise
	 */
	public function hasDescription() {
		return $this->hasString('description');
	}

	/**
	 * Returns our event type.
	 *
	 * @return tx_seminars_Model_EventType our event type, will be null if this
	 *                                     event has no event type
	 */
	public function getEventType() {
		return $this->getAsModel('event_type');
	}

	/**
	 * Returns our accreditation number.
	 *
	 * @return string our accreditation number, will be empty if this event has
	 *                no accreditation number
	 */
	public function getAccreditationNumber() {
		return $this->getAsString('accreditation_number');
	}

	/**
	 * Sets our accreditation number.
	 *
	 * @param string our accreditation number, may be empty
	 */
	public function setAccreditationNumber($accreditationNumber) {
		$this->setAsString('accreditation_number', $accreditationNumber);
	}

	/**
	 * Returns whether this event has an accreditation number.
	 *
	 * @return boolean true if this event has an accreditation number, false
	 *                 otherwise
	 */
	public function hasAccreditationNumber() {
		return $this->hasString('accreditation_number');
	}

	/**
	 * Returns our credit points.
	 *
	 * @return integer our credit points, will be 0 if this event has no credit
	 *                 points, will be >= 0
	 */
	public function getCreditPoints() {
		return $this->getAsInteger('credit_points');
	}

	/**
	 * Sets our credit points.
	 *
	 * @param integer our credit points, must be >= 0
	 */
	public function setCreditPoints($creditPoints) {
		if ($creditPoints < 0) {
			throw new Exception('The parameter $creditPoints must be >= 0.');
		}

		$this->setAsInteger('credit_points', $creditPoints);
	}

	/**
	 * Returns whether this event has credit points.
	 *
	 * @return boolean true if this event has credit points, false otherwise
	 */
	public function hasCreditPoints() {
		return $this->hasInteger('credit_points');
	}

	/**
	 * Returns our time-slots.
	 *
	 * @return tx_oelib_List our time-slots, will be empty if this event has no
	 *                       time-slots
	 */
	public function getTimeSlots() {
		return $this->getAsList('timeslots');
	}

	/**
	 * Returns our registration deadline as UNIX time-stamp.
	 *
	 * @return integer our registration deadline as UNIX time-stamp, will be
	 *                 0 if this event has no registration deadline, will be
	 *                 >= 0
	 */
	public function getRegistrationDeadlineAsUnixTimeStamp() {
		return $this->getAsInteger('deadline_registration');
	}

	/**
	 * Sets our registration deadline as UNIX time-stamp.
	 *
	 * @param integer our registration deadline as UNIX time-stamp, must be >= 0,
	 *                0 unsets the registration deadline
	 */
	public function setRegistrationDeadlineAsUnixTimeStamp($registrationDeadline) {
		if ($registrationDeadline < 0) {
			throw new Exception(
				'The parameter $registrationDeadline must be >= 0.'
			);
		}

		$this->setAsInteger('deadline_registration', $registrationDeadline);
	}

	/**
	 * Returns whether this event has a registration deadline set.
	 *
	 * @return boolean true if this event has a registration deadline set, false
	 *                 otherwise
	 */
	public function hasRegistrationDeadline() {
		return $this->hasInteger('deadline_registration');
	}

	/**
	 * Returns our early bird deadline as UNIX time-stamp.
	 *
	 * @return integer our early bird deadline as UNIX time-stamp, will be 0
	 *                 if this event has no early bird deadline, will be >= 0
	 */
	public function getEarlyBirdDeadlineAsUnixTimeStamp() {
		return $this->getAsInteger('deadline_early_bird');
	}

	/**
	 * Sets our early bird deadline as UNIX time-stamp.
	 *
	 * @param integer our early bird deadline as UNIX time-stamp, must be >= 0,
	 *                0 unsets the early bird deadline
	 */
	public function setEarlyBirdDeadlineAsUnixTimeStamp($earlyBirdDeadline) {
		if ($earlyBirdDeadline < 0) {
			throw new Exception('The parameter $earlyBirdDeadline must be >= 0.');
		}

		$this->setAsInteger('deadline_early_bird', $earlyBirdDeadline);
	}

	/**
	 * Returns whether this event has an early bird deadline.
	 *
	 * @return boolean true if this event has an early bird deadline, false
	 *                 otherwise
	 */
	public function hasEarlyBirdDeadline() {
		return $this->hasInteger('deadline_early_bird');
	}

	/**
	 * Returns our unregistration deadline as UNIX time-stamp.
	 *
	 * @return integer our unregistration deadline as UNIX time-stamp, will be
	 *                 0 if this event has no unregistration deadline, will be
	 *                 >= 0
	 */
	public function getUnregistrationDeadlineAsUnixTimeStamp() {
		return $this->getAsInteger('deadline_unregistration');
	}

	/**
	 * Sets our unregistration deadline as UNIX time-stamp.
	 *
	 * @param integer our unregistration deadline as UNIX time-stamp, must be
	 *                >= 0, 0 unsets the unregistration deadline
	 */
	public function setUnregistrationDeadlineAsUnixTimeStamp($unregistrationDeadline) {
		if ($unregistrationDeadline < 0) {
			throw new Exception(
				'The parameter $unregistrationDeadline must be >= 0.'
			);
		}

		$this->setAsInteger('deadline_unregistration', $unregistrationDeadline);
	}

	/**
	 * Returns whether this event has an unregistration deadline.
	 *
	 * @return integer true if this event has an unregistration deadline, false
	 *                 otherwise
	 */
	public function hasUnregistrationDeadline() {
		return $this->hasInteger('deadline_unregistration');
	}

	/**
	 * Returns our expiry as UNIX time-stamp.
	 *
	 * @return integer our expiry as UNIX time-stamp, will be 0 if this event
	 *                 has no expiry, will be >= 0
	 */
	public function getExpiryAsUnixTimeStamp() {
		return $this->getAsInteger('expiry');
	}

	/**
	 * Sets our expiry as UNIX time-stamp.
	 *
	 * @param integer our expiry as UNIX time-stamp, must be >= 0, 0 unsets the
	 *                expiry
	 */
	public function setExpiryAsUnixTimeStamp($expiry) {
		if ($expiry < 0) {
			throw new Exception('The parameter $expiry must be >= 0.');
		}

		$this->setAsInteger('expiry', $expiry);
	}

	/**
	 * Returns whether this event has an expiry.
	 *
	 * @return boolean true if this event has an expiry, false otherwise
	 */
	public function hasExpiry() {
		return $this->hasInteger('expiry');
	}

	/**
	 * Returns our details page UID.
	 *
	 * @return integer our details page UID, will be 0 if this event has no
	 *                 details page, will be >= 0
	 */
	public function getDetailsPageUid() {
		return $this->getAsInteger('details_page');
	}

	/**
	 * Sets our details page UID.
	 *
	 * @param integer our details page UID, must be >= 0
	 */
	public function setDetailsPageUid($uid) {
		if ($uid < 0) {
			throw new Exception('The parameter $uid must be >= 0.');
		}

		$this->setAsInteger('details_page', $uid);
	}

	/**
	 * Returns whether this event has a separate details page.
	 *
	 * @return boolean true if this event has a separate details page, false
	 *                 otherwise
	 */
	public function hasDetailsPage() {
		return $this->hasInteger('details_page');
	}

	/**
	 * Returns our places.
	 *
	 * @return tx_oelib_List our places, will be empty if this event has no
	 *                       places
	 */
	public function getPlaces() {
		return $this->getAsList('place');
	}

	/**
	 * Returns our lodgings.
	 *
	 * @return tx_oelib_List our lodgings, will be empty if this event has no
	 *                       lodgings
	 */
	public function getLodgings() {
		return $this->getAsList('lodgings');
	}

	/**
	 * Returns our foods.
	 *
	 * @return tx_oelib_List our foods, will be empty if this event has no
	 *                       foods
	 */
	public function getFoods() {
		return $this->getAsList('foods');
	}

	/**
	 * Returns our partners.
	 *
	 * @return tx_oelib_List our partners, will be empty if this event has no
	 *                       partners
	 */
	public function getPartners() {
		return $this->getAsList('partners');
	}

	/**
	 * Returns our tutors.
	 *
	 * @return tx_oelib_List our tutors, will be empty if this event has no
	 *                       tutors
	 */
	public function getTutors() {
		return $this->getAsList('tutors');
	}

	/**
	 * Returns our leaders.
	 *
	 * @return tx_oelib_List our leaders, will be empty if this event has no
	 *                       leaders
	 */
	public function getLeaders() {
		return $this->getAsList('leaders');
	}

	/**
	 * Returns our language.
	 *
	 * @return tx_oelib_Model_Language our language, will be null if this event
	 *                                 has no language set
	 */
	public function getLanguage() {
		if (!$this->hasLanguage()) {
			return null;
		}

		return tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Language')
			->findByIsoAlpha2Code($this->getAsString('language'));
	}

	/**
	 * Sets our language.
	 *
	 * @param tx_oelib_Model_Language our language
	 */
	public function setLanguage(tx_oelib_Model_Language $language) {
		$this->setAsString('language', $language->getIsoAlpha2Code());
	}

	/**
	 * Returns whether this event has a language.
	 *
	 * @return boolean true if this event has a language, false otherwise
	 */
	public function hasLanguage() {
		return $this->hasString('language');
	}

	/**
	 * Returns our regular price.
	 *
	 * @return float our regular price, will be 0.00 if this event has no regular
	 *               price, will be >= 0.00
	 */
	public function getRegularPrice() {
		return $this->getAsFloat('price_regular');
	}

	/**
	 * Sets our regular price.
	 *
	 * @param float our regular price, must be >= 0.00
	 */
	public function setRegularPrice($price) {
		if ($price < 0.00) {
			throw new Exception('The parameter $price must be >= 0.00.');
		}

		$this->setAsFloat('price_regular', $price);
	}

	/**
	 * Returns whether this event has a regular price.
	 *
	 * @return boolean true if this event has a regular price, false otherwise
	 */
	public function hasRegularPrice() {
		return $this->hasFloat('price_regular');
	}

	/**
	 * Returns our regular early bird price.
	 *
	 * @return float our regular early bird price, will be 0.00 if this event has
	 *               no regular early bird price, will be >= 0.00
	 */
	public function getRegularEarlyBirdPrice() {
		return $this->getAsFloat('price_regular_early');
	}

	/**
	 * Sets our regular early bird price.
	 *
	 * @param float our regular early bird price, must be >= 0.00
	 */
	public function setRegularEarlyBirdPrice($price) {
		if ($price < 0.00) {
			throw new Exception('The parameter $price must be >= 0.00.');
		}

		$this->setAsFloat('price_regular_early', $price);
	}

	/**
	 * Returns whether this event has a regular early bird price.
	 *
	 * @return boolean true if this event has a regular early bird price, false
	 *                 otherwise
	 */
	public function hasRegularEarlyBirdPrice() {
		return $this->hasFloat('price_regular_early');
	}

	/**
	 * Returns our regular board price.
	 *
	 * @return float our regular board price, will be 0.00 if this event has no
	 *               regular board price, will be >= 0.00
	 */
	public function getRegularBoardPrice() {
		return $this->getAsFloat('price_regular_board');
	}

	/**
	 * Sets our regular board price.
	 *
	 * @param float our regular board price, must be >= 0.00
	 */
	public function setRegularBoardPrice($price) {
		if ($price < 0.00) {
			throw new Exception('The parameter $price must be >= 0.00.');
		}

		$this->setAsFloat('price_regular_board', $price);
	}

	/**
	 * Returns whether this event has a regular board price.
	 *
	 * @return float true if this event has a regular board price, false
	 *               otherwise
	 */
	public function hasRegularBoadPrice() {
		return $this->hasFloat('price_regular_board');
	}

	/**
	 * Returns our special price.
	 *
	 * @return float our special price, will be 0.00 if this event has no special
	 *               price, will be >= 0.00
	 */
	public function getSpecialPrice() {
		return $this->getAsFloat('price_special');
	}

	/**
	 * Sets our special price.
	 *
	 * @param float our special price, must be >= 0.00
	 */
	public function setSpecialPrice($price) {
		if ($price < 0.00) {
			throw new Exception('The parameter $price must be >= 0.00.');
		}

		$this->setAsFloat('price_special', $price);
	}

	/**
	 * Returns whether this event has a special price.
	 *
	 * @return boolean true if this event has a special price, false otherwise
	 */
	public function hasSpecialPrice() {
		return $this->hasFloat('price_special');
	}

	/**
	 * Returns our special early bird price.
	 *
	 * @return float our special early bird price, will be 0.00 if this event has
	 *               no special early bird price, will be >= 0.00
	 */
	public function getSpecialEarlyBirdPrice() {
		return $this->getAsFloat('price_special_early');
	}

	/**
	 * Sets our special early bird price.
	 *
	 * @param float our special early bird price, must be >= 0.00
	 */
	public function setSpecialEarlyBirdPrice($price) {
		if ($price < 0.00) {
			throw new Exception('The parameter $price must be >= 0.00.');
		}

		$this->setAsFloat('price_special_early', $price);
	}

	/**
	 * Return whether this event has a special early bird price.
	 *
	 * @return boolean true if this event has a special early bird price, false
	 *                 otherwise
	 */
	public function hasSpecialEarlyBirdPrice() {
		return $this->hasFloat('price_special_early');
	}

	/**
	 * Returns our special board price.
	 *
	 * @return float our special board price, will be 0.00 if this event has no
	 *               special board price, will be >= 0.00
	 */
	public function getSpecialBoardPrice() {
		return $this->getAsFloat('price_special_board');
	}

	/**
	 * Sets our special board price.
	 *
	 * @param float our special board price, must be >= 0.00
	 */
	public function setSpecialBoardPrice($price) {
		if ($price < 0.00) {
			throw new Exception('The parameter $price must be >= 0.00.');
		}

		$this->setAsFloat('price_special_board', $price);
	}

	/**
	 * Returns whether this event has a special board price.
	 *
	 * @return boolean true if this event has a special board price, false
	 *                 otherwise
	 */
	public function hasSpecialBoardPrice() {
		return $this->hasFloat('price_special_board');
	}

	/**
	 * Returns our additional information.
	 *
	 * @return string our additional information, will be empty if this event
	 *                has no additional information
	 */
	public function getAdditionalInformation() {
		return $this->getAsString('additional_information');
	}

	/**
	 * Sets our additional information.
	 *
	 * @param string our additional information, may be empty
	 */
	public function setAdditionalInformation($additionalInformation) {
		$this->setAsString('additional_information', $additionalInformation);
	}

	/**
	 * Returns whether this event has additional information.
	 *
	 * @return boolean true if this event has additional information, false
	 *                 otherwise
	 */
	public function hasAdditionalInformation() {
		return $this->hasString('additional_information');
	}

	/**
	 * Returns our payment methods.
	 *
	 * @return tx_oelib_List our payment methods, will be empty if this event
	 *                       has no payment methods
	 */
	public function getPaymentMethods() {
		return $this->getAsList('payment_methods');
	}

	/**
	 * Returns our organizers.
	 *
	 * @return tx_oelib_List our organizers, will be empty if this event has no
	 *                       organizers
	 */
	public function getOrganizers() {
		return $this->getAsList('organizers');
	}

	/**
	 * Returns our organinzing partners.
	 *
	 * @return tx_oelib_List our organizing partners, will be empty if this event
	 *                       has no organizing partners
	 */
	public function getOrganizingPartners() {
		return $this->getAsList('organizing_partners');
	}

	/**
	 * Returns whether the "event takes place reminder" has been sent.
	 *
	 * @return boolean true if the "event takes place reminder" has been sent,
	 *                 false otherwise
	 */
	public function eventTakesPlaceReminderHasBeenSent() {
		return $this->getAsBoolean('event_takes_place_reminder_sent');
	}

	/**
	 * Returns whether the "cancelation deadline reminder" has been sent.
	 *
	 * @return boolean true if the "cancelation deadline reminder" has been sent,
	 *                 false otherwise
	 */
	public function cancelationDeadlineReminderHasBeenSent() {
		return $this->getAsBoolean('cancelation_deadline_reminder_sent');
	}

	/**
	 * Returns whether this event needs a registration.
	 *
	 * @return boolean true if this event needs a registration, false otherwise
	 */
	public function needsRegistration() {
		return $this->getAsBoolean('needs_registration');
	}

	/**
	 * Returns whether this event allows multiple registration.
	 *
	 * @return boolean true if this event allows multiple registration, false
	 *                 otherwise
	 */
	public function allowsMultipleRegistrations() {
		return $this->getAsBoolean('allows_multiple_registrations');
	}

	/**
	 * Returns our minimum attendees.
	 *
	 * @return integer our minimum attendees, will be 0 if this event has no
	 *                 minimum attendees, will be >= 0
	 */
	public function getMinimumAttendees() {
		return $this->getAsInteger('attendees_min');
	}

	/**
	 * Sets our minimum attendees.
	 *
	 * @param integer our minimum attendees, must be >= 0
	 */
	public function setMinimumAttendees($minimumAttendees) {
		if ($minimumAttendees < 0) {
			throw new Exception('The parameter $minimumAttendees must be >= 0.');
		}

		$this->setAsInteger('attendees_min', $minimumAttendees);
	}

	/**
	 * Returns whether this event has minimum attendees.
	 *
	 * @return boolean true if this event has minimum attendees, false otherwise
	 */
	public function hasMinimumAttendees() {
		return $this->hasInteger('attendees_min');
	}

	/**
	 * Returns our maximum attendees.
	 *
	 * @return integer our maximum attendees, will be 0 if this event has no
	 *                 maximum attendees and allows unlimited number of attendees,
	 *                 will be >= 0
	 */
	public function getMaximumAttendees() {
		return $this->getAsInteger('attendees_max');
	}

	/**
	 * Sets our maximum attendees.
	 *
	 * @param integer our maximum attendees, must be >= 0, 0 means an unlimited
	 *                number of attendees
	 */
	public function setMaximumAttendees($maximumAttendees) {
		if ($maximumAttendees < 0) {
			throw new Exception('The parameter $maximumAttendees must be >= 0.');
		}

		$this->setAsInteger('attendees_max', $maximumAttendees);
	}

	/**
	 * Returns whether this event has a registration queue.
	 *
	 * @return boolean true if this event has a registration queue, false
	 *                 otherwise
	 */
	public function hasRegistrationQueue() {
		return $this->getAsBoolean('queue_size');
	}

	/**
	 * Returns our target groups.
	 *
	 * @return tx_oelib_List our target groups, will be empty if this event has
	 *                       no target groups
	 */
	public function getTargetGroups() {
		return $this->getAsList('target_groups');
	}

	/**
	 * Returns whether the collision check should be skipped for this event.
	 *
	 * @return boolean true if the collision check should be skipped for this
	 *                 event, false otherwise
	 */
	public function shouldSkipCollisionCheck() {
		return $this->getAsBoolean('skip_collision_check');
	}

	/**
	 * Returns our status.
	 *
	 * @return integer our status, will be one of STATUS_PLANNED,
	 *                 STATUS_CANCELED or STATUS_CONFIRMED
	 */
	public function getStatus() {
		return $this->getAsInteger('cancelled');
	}

	/**
	 * Sets our status.
	 *
	 * @param integer our status, must be one of STATUS_PLANNED, STATUS_CANCELED,
	 *                STATUS_CONFIRMED
	 */
	public function setStatus($status) {
		if (!in_array(
			$status,
			array(
				self::STATUS_PLANNED,
				self::STATUS_CANCELED,
				self::STATUS_CONFIRMED,
			)
		)) {
			throw new Exception(
				'The parameter $status must be either STATUS_PLANNED, ' .
					'STATUS_CANCELED or STATUS_CONFIRMED'
			);
		}

		$this->setAsInteger('cancelled', $status);
	}

	/**
	 * Returns our owner.
	 *
	 * @return tx_oelib_Model_FrontEndUser our owner, will be null if this event
	 *                                     has no owner
	 */
	public function getOwner() {
		return $this->getAsModel('owner_feuser');
	}

	/**
	 * Returns our event managers.
	 *
	 * @return tx_oelib_List our event managers, will be empty if this event has
	 *                       no event managers
	 */
	public function getEventManagers() {
		return $this->getAsList('vips');
	}

	/**
	 * Returns our checkboxes.
	 *
	 * @return tx_oelib_List our checkboxes, will be empty if this event has no
	 *                       checkboxes
	 */
	public function getCheckboxes() {
		return $this->getAsList('checkboxes');
	}

	/**
	 * Returns whether this event makes use of the second terms & conditions.
	 *
	 * @return boolean true if this event makes use of the second terms &
	 *                 conditions, false otherwise
	 */
	public function usesTerms2() {
		return $this->getAsBoolean('use_terms_2');
	}

	/**
	 * Returns our notes.
	 *
	 * @return string our notes, will be empty if this event has no notes
	 */
	public function getNotes() {
		return $this->getAsString('notes');
	}

	/**
	 * Sets our notes.
	 *
	 * @param string our notes, may be empty
	 */
	public function setNotes($notes) {
		$this->setAsString('notes', $notes);
	}

	/**
	 * Returns whether this event has notes.
	 *
	 * @return boolean true if this event has notes, false otherwise
	 */
	public function hasNotes() {
		return $this->hasString('notes');
	}

	/**
	 * Returns our attached files.
	 *
	 * The returned array will be sorted like the files are sorted in the back-
	 * end form.
	 *
	 * @return array our attached file names relative to the seminars upload
	 *               directory, will be empty if this event has no attached files
	 */
	public function getAttachedFiles() {
		return $this->getAsTrimmedArray('attached_files');
	}

	/**
	 * Sets our attached files.
	 *
	 * @param array our attached file names, file names must be relative to the
	 *              seminars upload directory, may be empty
	 */
	public function setAttachedFiles(array $attachedFiles) {
		$this->setAsArray('attached_files', $attachedFiles);
	}

	/**
	 * Returns whether this event has attached files.
	 *
	 * @return boolean true if this event has attached files, false otherwise
	 */
	public function hasAttachedFiles() {
		return $this->hasString('attached_files');
	}

	/**
	 * Returns our image.
	 *
	 * @return string our image file name relative to the seminars upload
	 *                directory, will be empty if this event has no image
	 */
	public function getImage() {
		return $this->getAsString('image');
	}

	/**
	 * Sets our image.
	 *
	 * @param string our image file name, must be relative to the seminars
	 *               upload directory, may be empty
	 */
	public function setImage($image) {
		$this->setAsString('image', $image);
	}

	/**
	 * Returns whether this event has an image.
	 *
	 * @return boolean true if this event has an image, false otherwise
	 */
	public function hasImage() {
		return $this->hasString('image');
	}

	/**
	 * Returns our requirements.
	 *
	 * @return tx_oelib_List our requirements, will be empty if this event has
	 *                       no requirements
	 */
	public function getRequirements() {
		return $this->getAsList('requirements');
	}

	/**
	 * Returns our dependencies.
	 *
	 * @return tx_oelib_List our dependencies, will be empty if this event has
	 *                       no dependencies
	 */
	public function getDependencies() {
		return $this->getAsList('dependencies');
	}

	/**
	 * Checks whether this event has a begin date for the registration.
	 *
	 * @return boolean true if this event has a begin date for the registration,
	 *                 false otherwise
	 */
	public function hasRegistrationBegin() {
		return $this->hasInteger('begin_date_registration');
	}

	/**
	 * Returns the begin date for the registration of this event as UNIX
	 * time-stamp.
	 *
	 * @return integer the begin date for the registration of this event as UNIX
	 *                 time-stamp, will be 0 if no begin date for the
	 *                 registration is set
	 */
	public function getRegistrationBeginAsUnixTimestamp() {
		return $this->getAsInteger('begin_date_registration');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_Event.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/Model/class.tx_seminars_Model_Event.php']);
}
?>