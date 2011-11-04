<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2011 Niels Pardon (mail@niels-pardon.de)
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
 * @author Oliver Klee <typo3-coding@oliverklee.de>
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
	 * Returns whether this event is a single event.
	 *
	 * @return boolean TRUE if this event is a single event, FALSE otherwise
	 */
	public function isSingleEvent() {
		return ($this->getAsInteger('object_type') == self::TYPE_COMPLETE);
	}

	/**
	 * Returns whether this event is an event date.
	 *
	 * @return boolean TRUE if this event is an event date, FALSE otherwise
	 */
	public function isEventDate() {
		return ($this->getAsInteger('object_type') == self::TYPE_DATE);
	}

	/**
	 * Returns the record type of this event, which will be one of the following:
	 * - tx_seminars_Model_Event::TYPE_COMPLETE
	 * - tx_seminars_Model_Event::TYPE_TOPIC
	 * - tx_seminars_Model_Event::TYPE_DATE
	 *
	 * @return integer the record type of this event, will be one of the values
	 *                 mentioned above, will be >= 0
	 */
	public function getRecordType() {
		return $this->getAsInteger('object_type');
	}

	/**
	 * Returns our topic.
	 *
	 * This method may only be called for date records.
	 *
	 * @return tx_seminars_Model_Event our topic, will be null if this event has
	 *                                 no topic
	 */
	public function getTopic() {
		if (!$this->isEventDate()) {
			throw new Exception(
				'This function may only be called for date records.'
			);
		}

		return $this->getAsModel('topic');
	}

	/**
	 * Returns our title.
	 *
	 * @return string our title, will be empty if this event has no title
	 */
	public function getTitle() {
		return ($this->isEventDate()) ? $this->getTopic()->getTitle() : $this->getRawTitle();
	}

	/**
	 * Returns our direct title, i.e. for date records the date's title, not
	 * the topic's title.
	 *
	 * For single events and dates, this function will return the same as
	 * getTitle.
	 *
	 * @return string our title, will be empty if this event has no title
	 */
	public function getRawTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Returns our subtitle.
	 *
	 * @return string our subtitle, will be empty if this event has no subtitle
	 */
	public function getSubtitle() {
		return ($this->isEventDate())
			? $this->getTopic()->getSubtitle()
			: $this->getAsString('subtitle');
	}

	/**
	 * Sets our subtitle.
	 *
	 * @param string our subtitle to set, may be empty
	 */
	public function setSubtitle($subtitle) {
		if ($this->isEventDate()) {
			$this->getTopic()->setSubtitle($subtitle);
		} else {
			$this->setAsString('subtitle', $subtitle);
		}
	}

	/**
	 * Returns whether this event has a subtitle.
	 *
	 * @return boolean TRUE if this event has a subtitle, FALSE otherwise
	 */
	public function hasSubtitle() {
		return ($this->isEventDate())
			? $this->getTopic()->hasSubtitle()
			: $this->hasString('subtitle');
	}

	/**
	 * Returns our categories.
	 *
	 * @return tx_oelib_List our categories
	 */
	public function getCategories() {
		return ($this->isEventDate())
			? $this->getTopic()->getCategories()
			: $this->getAsList('categories');
	}

	/**
	 * Returns our teaser.
	 *
	 * @return string our teaser, might be empty
	 */
	public function getTeaser() {
		return ($this->isEventDate())
			? $this->getTopic()->getTeaser()
			: $this->getAsString('teaser');
	}

	/**
	 * Sets our teaser.
	 *
	 * @param string our teaser, may be empty
	 */
	public function setTeaser($teaser) {
		if ($this->isEventDate()) {
			$this->getTopic()->setTeaser($teaser);
		} else {
			$this->setAsString('teaser', $teaser);
		}
	}

	/**
	 * Returns whether this event has a teaser.
	 *
	 * @return boolean TRUE if this event has a teaser, FALSE otherwise
	 */
	public function hasTeaser() {
		return ($this->isEventDate())
			? $this->getTopic()->hasTeaser()
			: $this->hasString('teaser');
	}

	/**
	 * Returns our description.
	 *
	 * @return string our description, might be empty
	 */
	public function getDescription() {
		return ($this->isEventDate())
			? $this->getTopic()->getDescription()
			: $this->getAsString('description');
	}

	/**
	 * Sets our description.
	 *
	 * @param string our description, may be empty
	 */
	public function setDescription($description) {
		if ($this->isEventDate()) {
			$this->getTopic()->setDescription($description);
		} else {
			$this->setAsString('description', $description);
		}
	}

	/**
	 * Returns whether this event has a description.
	 *
	 * @return string TRUE if this event has a description, FALSE otherwise
	 */
	public function hasDescription() {
		return ($this->isEventDate())
			? $this->getTopic()->hasDescription()
			: $this->hasString('description');
	}

	/**
	 * Returns our event type.
	 *
	 * @return tx_seminars_Model_EventType our event type, will be null if this
	 *                                     event has no event type
	 */
	public function getEventType() {
		return ($this->isEventDate())
			? $this->getTopic()->getEventType()
			: $this->getAsModel('event_type');
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
	 * @return boolean TRUE if this event has an accreditation number, FALSE
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
		return ($this->isEventDate())
			? $this->getTopic()->getCreditPoints()
			: $this->getAsInteger('credit_points');
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

		if ($this->isEventDate()) {
			$this->getTopic()->setCreditPoints($creditPoints);
		} else {
			$this->setAsInteger('credit_points', $creditPoints);
		}
	}

	/**
	 * Returns whether this event has credit points.
	 *
	 * @return boolean TRUE if this event has credit points, FALSE otherwise
	 */
	public function hasCreditPoints() {
		return ($this->isEventDate())
			? $this->getTopic()->hasCreditPoints()
			: $this->hasInteger('credit_points');
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
	 * @return boolean TRUE if this event has a registration deadline set, FALSE
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
	 * @return boolean TRUE if this event has an early bird deadline, FALSE
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
	 * @return integer TRUE if this event has an unregistration deadline, FALSE
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
	 * @return boolean TRUE if this event has an expiry, FALSE otherwise
	 */
	public function hasExpiry() {
		return $this->hasInteger('expiry');
	}

	/**
	 * Returns our details page.
	 *
	 * @return integer our separate details page, will be empty if this event
	 *                 has no separate details page
	 */
	public function getDetailsPage() {
		return $this->getAsString('details_page');
	}

	/**
	 * Sets our separate details page.
	 *
	 * @param string our separate details page
	 */
	public function setDetailsPage($detailsPage) {
		$this->setAsString('details_page', $detailsPage);
	}

	/**
	 * Returns whether this event has a separate details page.
	 *
	 * @return boolean TRUE if this event has a separate details page, FALSE
	 *                 otherwise
	 */
	public function hasDetailsPage() {
		return $this->hasString('details_page');
	}

	/**
	 * Gets a separate single view page UID (or full URL) for this event,
	 * combined from the event itself, the event type and the categories.
	 *
	 * Note: This function does not take the TS setup configuration or flexform
	 * settings into account.
	 *
	 * @return string
	 *         the single view page UID/URL, will be an empty string if none
	 *         has been set
	 */
	public function getCombinedSingleViewPage() {
		$result = '';

		if ($this->hasDetailsPage()) {
			$result = $this->getDetailsPage();
		} elseif ($this->hasSingleViewPageUidFromEventType()) {
			$result = (string) $this->getSingleViewPageUidFromEventType();
		} elseif ($this->hasSingleViewPageUidFromCategories()) {
			$result = (string) $this->getSingleViewPageUidFromCategories();
		}

		return $result;
	}

	/**
	 * Checks whether this event has a separate single view page (combined
	 * from the event itself, the event type and the categories).
	 *
	 * @return boolean
	 *         TRUE if this event has a single view page set, FALSE otherwise
	 */
	public function hasCombinedSingleViewPage() {
		return ($this->getCombinedSingleViewPage() != '');
	}

	/**
	 * Gets the single view page from the event type.
	 *
	 * @return integer
	 *         the single view page UID from the event type, will be > 0 if
	 *         this event has an event type and a that type has a single view
	 *         page UID, will be 0 otherwise
	 */
	protected function getSingleViewPageUidFromEventType() {
		if (!$this->hasSingleViewPageUidFromEventType()) {
			return 0;
		}

		return $this->getEventType()->getSingleViewPageUid();
	}

	/**
	 * Checks whether this event has an event type with a non-zero single view
	 * page UID.
	 *
	 * @return boolean
	 *         TRUE if this event has an event type and if that event type has
	 *         a non-zero single view page, FALSE otherwise
	 */
	protected function hasSingleViewPageUidFromEventType() {
		return (($this->getEventType() !== null)
			&& $this->getEventType()->hasSingleViewPageUid());
	}

	/**
	 * Gets the single view page UID from the categories.
	 *
	 * This function returns the first found UID from the event categories.
	 *
	 * @return integer
	 *         the single view page UID from the categories, will be > 0 if
	 *         this event has at least one category with a single view page
	 *         UID, will be 0 otherwise
	 */
	protected function getSingleViewPageUidFromCategories() {
		$result = 0;

		foreach ($this->getCategories() as $category) {
			if ($category->hasSingleViewPageUid()) {
				$result = $category->getSingleViewPageUid();
				break;
			}
		}

		return $result;
	}

	/**
	 * Checks whether this event has at least one category with a single view
	 * page UID.
	 *
	 * @return boolean
	 *         TRUE if this event has at least one category with a single view
	 *         page UID, FALSE otherwise
	 */
	protected function hasSingleViewPageUidFromCategories() {
		return ($this->getSingleViewPageUidFromCategories() > 0);
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
	 * @return boolean TRUE if this event has a language, FALSE otherwise
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
		return ($this->isEventDate())
			? $this->getTopic()->getRegularPrice()
			: $this->getAsFloat('price_regular');
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

		if ($this->isEventDate()) {
			$this->getTopic()->setRegularPrice($price);
		} else {
			$this->setAsFloat('price_regular', $price);
		}
	}

	/**
	 * Returns whether this event has a regular price.
	 *
	 * @return boolean TRUE if this event has a regular price, FALSE otherwise
	 */
	public function hasRegularPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->hasRegularPrice()
			: $this->hasFloat('price_regular');
	}

	/**
	 * Returns our regular early bird price.
	 *
	 * @return float our regular early bird price, will be 0.00 if this event has
	 *               no regular early bird price, will be >= 0.00
	 */
	public function getRegularEarlyBirdPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->getRegularEarlyBirdPrice()
			: $this->getAsFloat('price_regular_early');
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

		if ($this->isEventDate()) {
			$this->getTopic()->setRegularEarlyBirdPrice($price);
		} else {
			$this->setAsFloat('price_regular_early', $price);
		}
	}

	/**
	 * Returns whether this event has a regular early bird price.
	 *
	 * @return boolean TRUE if this event has a regular early bird price, FALSE
	 *                 otherwise
	 */
	public function hasRegularEarlyBirdPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->hasRegularEarlyBirdPrice()
			: $this->hasFloat('price_regular_early');
	}

	/**
	 * Returns our regular board price.
	 *
	 * @return float our regular board price, will be 0.00 if this event has no
	 *               regular board price, will be >= 0.00
	 */
	public function getRegularBoardPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->getRegularBoardPrice()
			: $this->getAsFloat('price_regular_board');
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

		if ($this->isEventDate()) {
			$this->getTopic()->setRegularBoardPrice($price);
		} else {
			$this->setAsFloat('price_regular_board', $price);
		}
	}

	/**
	 * Returns whether this event has a regular board price.
	 *
	 * @return float TRUE if this event has a regular board price, FALSE
	 *               otherwise
	 */
	public function hasRegularBoardPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->hasRegularBoardPrice()
			: $this->hasFloat('price_regular_board');
	}

	/**
	 * Returns our special price.
	 *
	 * @return float our special price, will be 0.00 if this event has no special
	 *               price, will be >= 0.00
	 */
	public function getSpecialPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->getSpecialPrice()
			: $this->getAsFloat('price_special');
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

		if ($this->isEventDate()) {
			$this->getTopic()->setSpecialPrice($price);
		} else {
			$this->setAsFloat('price_special', $price);
		}
	}

	/**
	 * Returns whether this event has a special price.
	 *
	 * @return boolean TRUE if this event has a special price, FALSE otherwise
	 */
	public function hasSpecialPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->hasSpecialPrice()
			: $this->hasFloat('price_special');
	}

	/**
	 * Returns our special early bird price.
	 *
	 * @return float our special early bird price, will be 0.00 if this event has
	 *               no special early bird price, will be >= 0.00
	 */
	public function getSpecialEarlyBirdPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->getSpecialEarlyBirdPrice()
			: $this->getAsFloat('price_special_early');
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

		if ($this->isEventDate()) {
			$this->getTopic()->setSpecialEarlyBirdPrice($price);
		} else {
			$this->setAsFloat('price_special_early', $price);
		}
	}

	/**
	 * Return whether this event has a special early bird price.
	 *
	 * @return boolean TRUE if this event has a special early bird price, FALSE
	 *                 otherwise
	 */
	public function hasSpecialEarlyBirdPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->hasSpecialEarlyBirdPrice()
			: $this->hasFloat('price_special_early');
	}

	/**
	 * Returns our special board price.
	 *
	 * @return float our special board price, will be 0.00 if this event has no
	 *               special board price, will be >= 0.00
	 */
	public function getSpecialBoardPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->getSpecialBoardPrice()
			: $this->getAsFloat('price_special_board');
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

		if ($this->isEventDate()) {
			$this->getTopic()->setSpecialBoardPrice($price);
		} else {
			$this->setAsFloat('price_special_board', $price);
		}
	}

	/**
	 * Returns whether this event has a special board price.
	 *
	 * @return boolean TRUE if this event has a special board price, FALSE
	 *                 otherwise
	 */
	public function hasSpecialBoardPrice() {
		return ($this->isEventDate())
			? $this->getTopic()->hasSpecialBoardPrice()
			: $this->hasFloat('price_special_board');
	}

	/**
	 * Checks whether this event is sold with early bird prices.
	 *
	 * This will return TRUE if the event has a deadline and a price defined
	 * for early-bird registrations. If the special price (e.g. for students)
	 * is not used, then the student's early bird price is not checked.
	 *
	 * Attention: Both prices (standard and special) need to have an early bird
	 * version for this function to return TRUE (if there is a regular special
	 * price).
	 *
	 * @return boolean TRUE if an early bird deadline and early bird prices
	 *                 are set, FALSE otherwise
	 */
	public function hasEarlyBirdPrice() {
		// whether the event has regular prices set (a normal one and an early bird)
		$priceRegularIsOk = $this->hasRegularPrice()
			&& $this->hasRegularEarlyBirdPrice();

		// whether no special price is set, or both special prices
		// (normal and early bird) are set
		$priceSpecialIsOk = !$this->hasSpecialPrice()
			|| ($this->hasSpecialPrice() && $this->hasSpecialEarlyBirdPrice());

		return ($this->hasEarlyBirdDeadline()
			&& $priceRegularIsOk
			&& $priceSpecialIsOk);
	}

	/**
	 * Checks whether the latest possibility to register with early bird rebate
	 * for this event is over.
	 *
	 * The latest moment is just before a set early bird deadline.
	 *
	 * @return boolean TRUE if the deadline has passed, FALSE otherwise
	 */
	public function isEarlyBirdDeadlineOver() {
		return ($GLOBALS['SIM_EXEC_TIME']
			>= $this->getEarlyBirdDeadlineAsUnixTimeStamp());
	}


	/**
	 * Returns whether an early bird price applies.
	 *
	 * @return boolean TRUE if this event has an early bird dealine set and
	 *                 this deadline is not over yet, FALSE otherwise
	 */
	public function earlyBirdApplies() {
		return $this->hasEarlyBirdPrice() && !$this->isEarlyBirdDeadlineOver();
	}

	/**
	 * Gets the list of available prices for this event at this particular time.
	 *
	 * If there is an early-bird price available and the early-bird deadline has
	 * not passed yet, the early-bird price is used.
	 *
	 * The possible keys of the return value are:
	 * regular, regular_early, regular_board,
	 * special, special_early, special_board
	 *
	 * @return array the available prices as an associative array of floats,
	 *               will not be empty
	 */
	public function getAvailablePrices() {
		$result = array();

		$earlyBirdApplies = $this->earlyBirdApplies();

		if ($earlyBirdApplies && $this->hasRegularEarlyBirdPrice()) {
			$result['regular_early'] = $this->getRegularEarlyBirdPrice();
		} else {
			$result['regular'] = $this->getRegularPrice();
		}

		if ($this->hasSpecialPrice()) {
			if ($earlyBirdApplies && $this->hasSpecialEarlyBirdPrice()) {
				$result['special_early'] = $this->getSpecialEarlyBirdPrice();
			} else {
				$result['special'] = $this->getSpecialPrice();
			}
		}

		if ($this->hasRegularBoardPrice()) {
			$result['regular_board'] = $this->getRegularBoardPrice();
		}
		if ($this->hasSpecialBoardPrice()) {
			$result['special_board'] = $this->getSpecialBoardPrice();
		}

		return $result;
	}

	/**
	 * Returns our additional information.
	 *
	 * @return string our additional information, will be empty if this event
	 *                has no additional information
	 */
	public function getAdditionalInformation() {
		return ($this->isEventDate())
			? $this->getTopic()->getAdditionalInformation()
			: $this->getAsString('additional_information');
	}

	/**
	 * Sets our additional information.
	 *
	 * @param string our additional information, may be empty
	 */
	public function setAdditionalInformation($additionalInformation) {
		if ($this->isEventDate()) {
			$this->getTopic()->setAdditionalInformation($additionalInformation);
		} else {
			$this->setAsString('additional_information', $additionalInformation);
		}
	}

	/**
	 * Returns whether this event has additional information.
	 *
	 * @return boolean TRUE if this event has additional information, FALSE
	 *                 otherwise
	 */
	public function hasAdditionalInformation() {
		return ($this->isEventDate())
			? $this->getTopic()->hasAdditionalInformation()
			: $this->hasString('additional_information');
	}

	/**
	 * Returns our payment methods.
	 *
	 * @return tx_oelib_List our payment methods, will be empty if this event
	 *                       has no payment methods
	 */
	public function getPaymentMethods() {
		return ($this->isEventDate())
			? $this->getTopic()->getPaymentMethods()
			: $this->getAsList('payment_methods');
	}

	/**
	 * Sets our payment methods.
	 *
	 * Note: This function should only be called on topic or single event
	 * records, not on event dates.
	 *
	 * @param tx_oelib_List $paymentMethods
	 *        our payment methods, can be empty
	 */
	public function setPaymentMethods(tx_oelib_List $paymentMethods) {
		if ($this->isEventDate()) {
			throw new BadMethodCallException(
				'setPaymentMethods may only be called on single events and ' .
					'event topics, but not on event dates.'
			);
		}

		$this->set('payment_methods', $paymentMethods);
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
	 * @return boolean TRUE if the "event takes place reminder" has been sent,
	 *                 FALSE otherwise
	 */
	public function eventTakesPlaceReminderHasBeenSent() {
		return $this->getAsBoolean('event_takes_place_reminder_sent');
	}

	/**
	 * Returns whether the "cancelation deadline reminder" has been sent.
	 *
	 * @return boolean TRUE if the "cancelation deadline reminder" has been sent,
	 *                 FALSE otherwise
	 */
	public function cancelationDeadlineReminderHasBeenSent() {
		return $this->getAsBoolean('cancelation_deadline_reminder_sent');
	}

	/**
	 * Returns whether this event needs a registration.
	 *
	 * @return boolean TRUE if this event needs a registration, FALSE otherwise
	 */
	public function needsRegistration() {
		return $this->getAsBoolean('needs_registration');
	}

	/**
	 * Returns whether this event allows multiple registration.
	 *
	 * @return boolean TRUE if this event allows multiple registration, FALSE
	 *                 otherwise
	 */
	public function allowsMultipleRegistrations() {
		return ($this->isEventDate())
			? $this->getTopic()->allowsMultipleRegistrations()
			: $this->getAsBoolean('allows_multiple_registrations');
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
	 * @return boolean TRUE if this event has minimum attendees, FALSE otherwise
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
	 * Returns whether this event has maximum attendees.
	 *
	 * @return boolean TRUE if this event has maximum attendees, FALSE otherwise
	 *                 (allowing an unlimited number of attendees)
	 */
	public function hasMaximumAttendees() {
		return $this->hasInteger('attendees_max');
	}

	/**
	 * Checks whether this event has unlimited vacancies.
	 *
	 * @return boolean TRUE if this event has unlimited vacancies, FALSE
	 *                 otherwise
	 */
	public function hasUnlimitedVacancies() {
		return !$this->hasMaximumAttendees();
	}

	/**
	 * Returns whether this event has a registration queue.
	 *
	 * @return boolean TRUE if this event has a registration queue, FALSE
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
		return ($this->isEventDate())
			? $this->getTopic()->getTargetGroups()
			: $this->getAsList('target_groups');
	}

	/**
	 * Returns whether the collision check should be skipped for this event.
	 *
	 * @return boolean TRUE if the collision check should be skipped for this
	 *                 event, FALSE otherwise
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
		return ($this->isEventDate())
			? $this->getTopic()->getCheckboxes()
			: $this->getAsList('checkboxes');
	}

	/**
	 * Returns whether this event makes use of the second terms & conditions.
	 *
	 * @return boolean TRUE if this event makes use of the second terms &
	 *                 conditions, FALSE otherwise
	 */
	public function usesTerms2() {
		return ($this->isEventDate())
			? $this->getTopic()->usesTerms2()
			: $this->getAsBoolean('use_terms_2');
	}

	/**
	 * Returns our notes.
	 *
	 * @return string our notes, will be empty if this event has no notes
	 */
	public function getNotes() {
		return ($this->isEventDate())
			? $this->getTopic()->getNotes()
			: $this->getAsString('notes');
	}

	/**
	 * Sets our notes.
	 *
	 * @param string our notes, may be empty
	 */
	public function setNotes($notes) {
		if ($this->isEventDate()) {
			$this->getTopic()->setNotes($notes);
		} else {
			$this->setAsString('notes', $notes);
		}
	}

	/**
	 * Returns whether this event has notes.
	 *
	 * @return boolean TRUE if this event has notes, FALSE otherwise
	 */
	public function hasNotes() {
		return ($this->isEventDate())
			? $this->getTopic()->hasNotes()
			: $this->hasString('notes');
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
	 * @return boolean TRUE if this event has attached files, FALSE otherwise
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
		return ($this->isEventDate())
			? $this->getTopic()->getImage()
			: $this->getAsString('image');
	}

	/**
	 * Sets our image.
	 *
	 * @param string our image file name, must be relative to the seminars
	 *               upload directory, may be empty
	 */
	public function setImage($image) {
		if ($this->isEventDate()) {
			$this->getTopic()->setImage($image);
		} else {
			$this->setAsString('image', $image);
		}
	}

	/**
	 * Returns whether this event has an image.
	 *
	 * @return boolean TRUE if this event has an image, FALSE otherwise
	 */
	public function hasImage() {
		return ($this->isEventDate())
			? $this->getTopic()->hasImage()
			: $this->hasString('image');
	}

	/**
	 * Returns our requirements.
	 *
	 * @return tx_oelib_List our requirements, will be empty if this event has
	 *                       no requirements
	 */
	public function getRequirements() {
		return ($this->isEventDate())
			? $this->getTopic()->getRequirements()
			: $this->getAsList('requirements');
	}

	/**
	 * Returns our dependencies.
	 *
	 * @return tx_oelib_List our dependencies, will be empty if this event has
	 *                       no dependencies
	 */
	public function getDependencies() {
		return ($this->isEventDate())
			? $this->getTopic()->getDependencies()
			: $this->getAsList('dependencies');
	}

	/**
	 * Checks whether this event has a begin date for the registration.
	 *
	 * @return boolean TRUE if this event has a begin date for the registration,
	 *                 FALSE otherwise
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

	/**
	 * Returns the publication hash of this event.
	 *
	 * @return string the publication hash of this event, will be empty if this
	 *                event has no publication hash set
	 */
	public function getPublicationHash() {
		return $this->getAsString('publication_hash');
	}

	/**
	 * Checks whether this event has a publication hash.
	 *
	 * @return boolean TRUE if this event has a publication hash, FALSE
	 *                 otherwise
	 */
	public function hasPublicationHash() {
		return $this->hasString('publication_hash');
	}

	/**
	 * Sets this event's publication hash.
	 *
	 * @param string $hash
	 *        the publication hash, use a non-empty string to mark an event as
	 *        "not published yet" and an empty string to mark an event as
	 *        published
	 */
	public function setPublicationHash($hash) {
		$this->setAsString('publication_hash', $hash);
	}

	/**
	 * Purges the publication hash of this event.
	 */
	public function purgePublicationHash() {
		$this->setPublicationHash('');
	}

	/**
	 * Checks whether this event has been published.
	 *
	 * Note: The publication state of an event is not related to whether it is
	 * hidden or not.
	 *
	 * @return boolean TRUE if this event has been published, FALSE otherwise
	 */
	public function isPublished() {
		return !$this->hasPublicationHash();
	}

	/**
	 * Checks whether this event has any offline registrations.
	 *
	 * @return boolean TRUE if this event has at least one offline registration,
	 *                 FALSE otherwise
	 */
	public function hasOfflineRegistrations() {
		return $this->hasInteger('offline_attendees');
	}

	/**
	 * Returns the number of offline registrations for this event.
	 *
	 * @return integer the number of offline registrations for this event, will
	 *                 be 0 if this event has no offline registrations
	 */
	public function getOfflineRegistrations() {
		return $this->getAsInteger('offline_attendees');
	}

	/**
	 * Gets the registrations for this event.
	 *
	 * @return tx_oelib_List the registrations for this event (both regular and
	 *                       on the waiting list), will be empty if this event
	 *                       has no registrations
	 */
	public function getRegistrations() {
		return $this->getAsList('registrations');
	}

	/**
	 * Sets the registrations for this event.
	 *
	 * @param tx_oelib_List $registrations
	 *       the registrations for this event (both regular and on the waiting
	 *       list), may be empty
	 */
	public function setRegistrations(tx_oelib_List $registrations) {
		$this->set('registrations', $registrations);
	}

	/**
	 * Attaches a registration to this event.
	 *
	 * @param tx_seminars_Model_Registration $registration
	 *        the registration to attach
	 */
	public function attachRegistration(
		tx_seminars_Model_Registration $registration
	) {
		$registration->setEvent($this);
		$this->getRegistrations()->add($registration);
	}

	/**
	 * Gets the regular registrations for this event, ie. the registrations
	 * that are not on the waiting list.
	 *
	 * @return tx_oelib_List the regular registrations for this event, will be
	 *                       will be empty if this event no regular
	 *                       registrations
	 */
	public function getRegularRegistrations() {
		$regularRegistrations = tx_oelib_ObjectFactory::make('tx_oelib_List');

		foreach ($this->getRegistrations() as $registration) {
			if (!$registration->isOnRegistrationQueue()) {
				$regularRegistrations->add($registration);
			}
		}

		return $regularRegistrations;
	}

	/**
	 * Gets the queue registrations for this event, ie. the registrations
	 * that are no regular registrations (yet).
	 *
	 * @return tx_oelib_List the queue registrations for this event, will be
	 *                       will be empty if this event no queue registrations
	 */
	public function getQueueRegistrations() {
		$queueRegistrations = tx_oelib_ObjectFactory::make('tx_oelib_List');

		foreach ($this->getRegistrations() as $registration) {
			if ($registration->isOnRegistrationQueue()) {
				$queueRegistrations->add($registration);
			}
		}

		return $queueRegistrations;
	}

	/**
	 * Checks whether this event has any registrations on its registration
	 * queue (ie. on the waiting list).
	 *
	 * @return boolean TRUE if there is at least one registration on the queue,
	 *                 FALSE otherwise
	 */
	public function hasQueueRegistrations() {
		return !$this->getQueueRegistrations()->isEmpty();
	}

	/**
	 * Returns the number of regularly registered seats for this event.
	 *
	 * This functions counts the number of registered seats from regular
	 * registrations (but not from queue registrations) and the number of
	 * offline registrations.
	 *
	 * @return boolean the number of registered seats for this event, will
	 *                 be >= 0
	 */
	public function getRegisteredSeats() {
		$registeredSeats = $this->getOfflineRegistrations();

		foreach ($this->getRegularRegistrations() as $registration) {
			$registeredSeats += $registration->getSeats();
		}

		return $registeredSeats;
	}

	/**
	 * Checks whether this event has enough regular registrations to take place.
	 *
	 * If this event has zero as the minimum number of registrations, this
	 * function will always return TRUE.
	 *
	 * @return boolean TRUE if this event has enough regular registrations to
	 *                 to take place, FALSE otherwise
	 */
	public function hasEnoughRegistrations() {
		return ($this->getRegisteredSeats() >= $this->getMinimumAttendees());
	}

	/**
	 * Returns the number of vacancies for this event.
	 *
	 * If this event has an unlimited number of possible registrations, this
	 * function will always return zero.
	 *
	 * @return integer the number of vacancies for this event, will be >= 0
	 */
	public function getVacancies() {
		return max(
			0,
			$this->getMaximumAttendees() - $this->getRegisteredSeats()
		);
	}

	/**
	 * Checks whether this event has at least one vacancy.
	 *
	 * If this event has an unlimited number of possible registrations, this
	 * function will always return TRUE.
	 *
	 *
	 * @return boolean TRUE if this event has at least one vacancy
	 *                 FALSE otherwise
	 */
	public function hasVacancies() {
		if ($this->hasUnlimitedVacancies()) {
			return TRUE;
		}

		return ($this->getVacancies() > 0);
	}

	/**
	 * Checks whether this event is fully booked.
	 *
	 * If this event has an unlimited number of possible registrations, this
	 * function will always return FALSE.
	 *
	 * @return boolean TRUE if this event is fully booked, FALSE otherwise
	 */
	public function isFull() {
		return !$this->hasVacancies();
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/Event.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/Event.php']);
}
?>