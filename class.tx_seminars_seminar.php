<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2010 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

require_once(t3lib_extMgm::extPath('static_info_tables') . 'pi1/class.tx_staticinfotables_pi1.php');

/**
 * Class 'tx_seminars_seminar' for the 'seminars' extension.
 *
 * This class represents a seminar (or similar event).
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_seminar extends tx_seminars_timespan {
	/** @var string same as class name */
	public $prefixId = 'tx_seminars_seminar';
	/**  @var string path to this script relative to the extension dir */
	public $scriptRelPath = 'class.tx_seminars_seminar.php';

	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = SEMINARS_TABLE_SEMINARS;

	/** @var integer the number of all attendances */
	protected $numberOfAttendances = 0;

	/** @var integer the number of paid attendances */
	protected $numberOfAttendancesPaid = 0;

	/** @var integer the number of attendances on the registration queue */
	protected $numberOfAttendancesOnQueue = 0;

	/** @var boolean if the statistics have been already calculated */
	protected $statisticsHaveBeenCalculated = FALSE;

	/**
	 * @var tx_seminars_seminar The related topic record as a reference
	 * to the object.
	 *
	 * This will be null if we are not a date record.
	 */
	private $topic = null;

	/**
	 * @var integer the different statuses of an event.
	 */
	const STATUS_PLANNED = 0,
		STATUS_CANCELED = 1,
		STATUS_CONFIRMED = 2;

	/**
	 * The constructor. Creates a seminar instance from a DB record.
	 *
	 * By default, the process of creating a seminar object from a hidden record
	 * fails. If we need the seminar object although it's hidden, the parameter
	 * $allowHiddenRecords should be set to TRUE.
	 *
	 * @param integer The UID of the seminar to retrieve from the DB.
	 *                This parameter will be ignored if $dbResult is provided.
	 * @param pointer MySQL result pointer (of SELECT query)/DBAL object.
	 *                If this parameter is provided, $uid will be ignored.
	 * @param boolean whether it is possible to create a seminar object from
	 *                a hidden record
	 */
	public function __construct(
		$uid, $dbResult = FALSE, $allowHiddenRecords = FALSE
	) {
		parent::__construct($uid, $dbResult, $allowHiddenRecords);

		// For date records: Create a reference to the topic record.
		if ($this->isEventDate()) {
			$this->topic = $this->retrieveTopic();
			// To avoid infinite loops, null out $this->topic if it is a date
			// record, too. Date records that fail the check isTopicOkay()
			// are used as a complete event record.
			if ($this->isTopicOkay() && $this->topic->isEventDate()) {
				$this->topic = null;
			}
		} else {
			$this->topic = null;
		}
	}

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->topic);
		parent::__destruct();
	}

	/**
	 * Checks certain fields to contain pausible values. Example: The registration
	 * deadline must not be later than the event's starting time.
	 *
	 * This function is used in order to check values entered in the TCE forms
	 * in the TYPO3 back end.
	 *
	 * @param string the name of the field to check
	 * @param string the value that was entered in the TCE form that
	 *               needs to be validated
	 *
	 * @return array associative array containing the field "status" and
	 *               "newValue" (if needed)
	 */
	private function validateTceValues($fieldName, $value) {
		$result = array(
			'status' => TRUE
		);

		switch($fieldName) {
			case 'deadline_registration':
				// Check that the registration deadline is not later than the
				// begin date.
				if ($value > $this->getBeginDateAsTimestamp()) {
					$result['status'] = FALSE;
					$result['newValue'] = 0;
				}
				break;
			case 'deadline_early_bird':
				// Check that the early-bird deadline is
				// a) not later than the begin date
				// b) not later than the registration deadline (if set).
				if ($value > $this->getBeginDateAsTimestamp()
					|| ($this->getRecordPropertyInteger('deadline_registration')
					&& ($value > $this->getRecordPropertyInteger('deadline_registration')))
				) {
					$result['status'] = FALSE;
					$result['newValue'] = 0;
				}
				break;
			case 'price_regular_early':
				// Check that the regular early bird price is not higher than
				// the regular price for this event.
				if ($value > $this->getRecordPropertyDecimal('price_regular')) {
					$result['status'] = FALSE;
					$result['newValue'] = '0.00';
				}
				break;
			case 'price_special_early':
				// Check that the special early bird price is not higher than
				// the special price for this event.
				if ($value > $this->getRecordPropertyDecimal('price_special')) {
					$result['status'] = FALSE;
					$result['newValue'] = '0.00';
				}
				break;
			default:
				// no action if no case is matched
				break;
		}

		return $result;
	}

	/**
	 * Returns an associative array, containing fieldname/value pairs that need
	 * to be updated in the database. Update means "reset to zero/empty" so far.
	 *
	 * This function is used in order to check values entered in the TCE forms
	 * in the TYPO3 back end. It is called through a hook in the TCE class.
	 *
	 * @param array associative array containing the values entered in the TCE
	 *              form (as a reference, may not be null)
	 *
	 * @return array associative array containing data to update the database
	 *               entry of this event, may be empty
	 */
	public function getUpdateArray(array &$fieldArray) {
		$updateArray = array();
		$fieldNamesToCheck = array(
			'deadline_registration',
			'deadline_early_bird',
			'price_regular_early',
			'price_special_early'
		);

		foreach($fieldNamesToCheck as $currentFieldName) {
			$result = $this->validateTceValues(
				$currentFieldName,
				$fieldArray[$currentFieldName]
			);
			if (!$result['status']) {
				$updateArray[$currentFieldName] = $result['newValue'];
			}
		}

		if ($this->hasTimeslots()) {
			$updateArray['begin_date'] = $this->getBeginDateAsTimestamp();
			$updateArray['end_date'] = $this->getEndDateAsTimestamp();
			$updateArray['place'] = $this->updatePlaceRelationsFromTimeSlots();
		}

		return $updateArray;
	}

	/**
	 * Creates a hyperlink to this seminar details page. The content of the
	 * provided fieldname will be fetched from the event record, wrapped with
	 * link tags and returned as a link to the detailed page.
	 *
	 * If $this->conf['detailPID'] (and the corresponding flexforms value) is
	 * not set or 0, the link will point to the list view page.
	 *
	 * @param object a tslib_pibase object for a live page
	 * @param string the name of the field to retrieve and wrap, must not
	 *               be empty
	 *
	 * @return string HTML code for the link to the event details page
	 */
	public function getLinkedFieldValue(tslib_pibase $plugin, $fieldName) {
		$linkedText = '';

		// Certain fields can be retrieved 1:1 from the database, some need
		// to be fetched by a special getter function.
		switch ($fieldName) {
			case 'date':
				$linkedText = $this->getDate();
				break;
			default:
				$linkedText = htmlspecialchars($this->getTopicString($fieldName));
				break;
		}

		return $plugin->cObj->typoLink(
			$linkedText,
			$this->getDetailedViewLinkConfiguration($plugin)
		);
	}

	/**
	 * Gets our topic's title. For date records, this will return the
	 * corresponding topic record's title.
	 *
	 * @return string our topic title (or '' if there is an error)
	 */
	public function getTitle() {
		return $this->getTopicString('title');
	}

	/**
	 * Gets our direct title. Even for date records, this will return our
	 * direct title (which is visible in the back end) instead of the
	 * corresponding topic record's title.
	 *
	 * @return string our direct title (or '' if there is an error)
	 */
	public function getRealTitle() {
		return parent::getTitle();
	}

	/**
	 * Gets our subtitle.
	 *
	 * @return string our seminar subtitle (or '' if there is an error)
	 */
	public function getSubtitle() {
		return $this->getTopicString('subtitle');
	}

	/**
	 * Checks whether we have a subtitle.
	 *
	 * @return boolean TRUE if we have a non-empty subtitle, FALSE otherwise.
	 */
	public function hasSubtitle() {
		return $this->hasTopicString('subtitle');
	}

	/**
	 * Gets our description.
	 *
	 * @return string our seminar description (or '' if there is an error)
	 */
	public function getDescription() {
		return $this->getTopicString('description');
	}

	/**
	 * Sets the description of the event.
	 *
	 * @param string the description for this event, may be empty
	 */
	public function setDescription($description) {
		$this->setRecordPropertyString('description', $description);
	}

	/**
	 * Checks whether we have a description.
	 *
	 * @return boolean TRUE if we have a non-empty description, FALSE otherwise.
	 */
	public function hasDescription() {
		return $this->hasTopicString('description');
	}

	/**
	 * Gets the additional information.
	 *
	 * Note: This function does not htmlspecialchar its return value.
	 *
	 * @return string HTML code of the additional information (or '' if there is
	 *                an error)
	 */
	public function getAdditionalInformation() {
		return $this->getTopicString('additional_information');
	}

	/**
	 * Sets our additional information.
	 *
	 * @param string our additional information, may be empty
	 */
	public function setAdditionalInformation($additionalInformation) {
		$this->setRecordPropertyString(
			'additional_information', $additionalInformation
		);
	}

	/**
	 * Checks whether we have additional information for this event.
	 *
	 * @return boolean TRUE if we have additional information (field not empty),
	 *                 FALSE otherwise.
	 */
	public function hasAdditionalInformation() {
		return $this->hasTopicString('additional_information');
	}

	/**
	 * Gets the unique seminar title, consisting of the seminar title and the
	 * date (comma-separated).
	 *
	 * If the seminar has no date, just the title is returned.
	 *
	 * Note: This function does not htmlspecialchar its return value.
	 *
	 * @param string $dash the character used to separate start date and end date
	 *
	 * @return string the unique seminar title (or '' if there is an error)
	 */
	public function getTitleAndDate($dash = '–') {
		$date = $this->hasDate() ? ', '.$this->getDate($dash) : '';

		return $this->getTitle().$date;
	}

	/**
	 * Gets the accreditation number (which actually is a string, not an
	 * integer).
	 *
	 * @return string the accreditation number (may be empty)
	 */
	public function getAccreditationNumber() {
		return $this->getRecordPropertyString('accreditation_number');
	}

	/**
	 * Checks whether we have an accreditation number set.
	 *
	 * @return boolean TRUE if we have a non-empty accreditation number, FALSE otherwise.
	 */
	public function hasAccreditationNumber() {
		return $this->hasRecordPropertyString('accreditation_number');
	}

	/**
	 * Gets the number of credit points for this seminar
	 * (or an empty string if it is not set yet).
	 *
	 * @return string the number of credit points or a an empty string if it is
	 *                0
	 */
	public function getCreditPoints() {
		return $this->hasCreditPoints()
			? $this->getTopicInteger('credit_points') : '';
	}

	/**
	 * Checks whether this seminar has a non-zero number of credit
	 * points assigned.
	 *
	 * @return boolean TRUE if the seminar has credit points assigned,
	 *                 FALSE otherwise.
	 */
	public function hasCreditPoints() {
		return $this->hasTopicInteger('credit_points');
	}

	/**
	 * Gets our place (or places), complete as RTE'ed HTML with address and
	 * links. Returns a localized string "will be announced" if the seminar has
	 * no places set.
	 *
	 * @param tx_oelib_templatehelper the current FE plugin
	 *
	 * @return string our places description (or '' if there is an error)
	 */
	public function getPlaceWithDetails(tx_oelib_templatehelper $plugin) {
		if (!$this->hasPlace()) {
			$plugin->setMarker(
				'message_will_be_announced',
				$this->translate('message_willBeAnnounced')
			);
			return $plugin->getSubpart('PLACE_LIST_EMPTY');
		}

		$result = '';

		foreach ($this->getPlacesAsArray() as $place) {
			$name = htmlspecialchars($place['title']);
			if ($place['homepage'] != '') {
				$name = $plugin->cObj->getTypoLink(
					$name,
					$place['homepage'],
					array(),
					$plugin->getConfValueString('externalLinkTarget')
				);
			}
			$plugin->setMarker('place_item_title', $name);

			$descriptionParts = array();
			if ($place['address'] != '') {
				$descriptionParts[] = htmlspecialchars(str_replace(CR, ',', $place['address']));
			}
			if ($place['city'] != '') {
				$descriptionParts[] = htmlspecialchars($place['city']);
			}
			if ($place['country'] != '') {
				$countryName = $this->getCountryNameFromIsoCode(
					$place['country']
				);
				if ($countryName != '') {
					$descriptionParts[] = htmlspecialchars($countryName);
				}
			}

			$description = implode(', ', $descriptionParts);
			if ($place['directions'] != '') {
				$description .= $plugin->pi_RTEcssText($place['directions']);
			}
			$plugin->setMarker('place_item_description', $description);

			$result .= $plugin->getSubpart('PLACE_LIST_ITEM');
		}

		$plugin->setMarker('place_list_content', $result);

		return $plugin->getSubpart('PLACE_LIST_COMPLETE');
	}

	/**
	 * Checks whether the current event has at least one place set, and if
	 * this/these pace(s) have a country set.
	 * Returns a boolean TRUE if at least one of the set places has a
	 * country set, returns FALSE otherwise.
	 *
	 * IMPORTANT: This function does not check whether the saved ISO code is
	 * valid at all. As this field is filled through the BE from a prefilled
	 * list, this should never be an issue at all.
	 *
	 * @return boolean whether at least one place with country are set
	 *                 for the current event
	 */
	public function hasCountry() {
		return $this->hasPlace()
			&& (boolean) count($this->getPlacesWithCountry());
	}

	/**
	 * Returns an array of two-char ISO codes of countries for this event.
	 * These are fetched from the referenced place records of this event. If no
	 * place is set, or the set place(s) don't have any country set, an empty
	 * array will be returned.
	 *
	 * @return array the list of ISO codes for the countries of this
	 *               event, may be empty
	 */
	public function getPlacesWithCountry() {
		if (!$this->hasPlace()) {
			return array();
		}

		$countries = array();

		$countryData = tx_oelib_db::selectMultiple(
			'country',
			SEMINARS_TABLE_SITES . ' LEFT JOIN ' .
				SEMINARS_TABLE_SEMINARS_SITES_MM . ' ON ' . SEMINARS_TABLE_SITES .
				'.uid = ' . SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_foreign' .
				' LEFT JOIN ' . SEMINARS_TABLE_SEMINARS . ' ON ' .
				SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_local = ' .
				SEMINARS_TABLE_SEMINARS . '.uid',
			SEMINARS_TABLE_SEMINARS . '.uid = ' . $this->getUid() .
				' AND ' . SEMINARS_TABLE_SITES . '.country != ""' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_SITES),
			'country'
		);

		foreach ($countryData as $country) {
			$countries[] = $country['country'];
		}

		return $countries;
	}

	/**
	 * Returns a comma-separated list of country names that were set in the
	 * place record(s).
	 * If no places are set, or no countries are selected in the set places,
	 * an empty string will be returned.
	 *
	 * @return string comma-separated list of countries for this event,
	 *                may be empty
	 */
	public function getCountry() {
		if (!$this->hasCountry()) {
			return '';
		}

		$countryList = array();

		// Fetches the countries from the corresponding place records, may be
		// an empty array.
		$countries = $this->getPlacesWithCountry();
		// Get the real country names from the ISO codes.
		foreach ($countries as $currentCountry) {
			$countryList[] = $this->getCountryNameFromIsoCode(
				$currentCountry
			);
		}

		// Makes sure that each country is exactly once in the array and then
		// returns this list.
		$countryListUnique = array_unique($countryList);
		return implode(', ', $countryListUnique);
	}

	/**
	 * Returns a comma-separated list of city names that were set in the place
	 * record(s).
	 * If no places are set, or no cities are selected in the set places, an
	 * empty string will be returned.
	 *
	 * @return string comma-separated list of cities for this event, may be
	 *                empty
	 */
	public function getCities() {
		if (!$this->hasCities()) {
			return '';
		}

		$cityList = $this->getCitiesFromPlaces();

		// Makes sure that each city is exactly once in the array and then
		// returns this list.
		$cityListUnique = array_unique($cityList);
		return implode(', ', $cityListUnique);
	}

	/**
	 * Checks whether the current event has at least one place set, and if
	 * this/these pace(s) have a city set.
	 * Returns a boolean TRUE if at least one of the set places has a
	 * city set, returns FALSE otherwise.
	 *
	 * @return boolean whether at least one place with city are set for the
	 *                 current event
	 */
	public function hasCities() {
		return $this->hasPlace() && (count($this->getCitiesFromPlaces() > 0));
	}

	/**
	 * Returns an array of city names for this event.
	 * These are fetched from the referenced place records of this event. If no
	 * place is set, or the set place(s) don't have any city set, an empty
	 * array will be returned.
	 *
	 * @return array the list of city names for this event, may be empty
	 */
	public function getCitiesFromPlaces() {
		$cities = array();

		// Fetches the city name from the corresponding place record(s).
		$cityData = tx_oelib_db::selectMultiple(
			'city',
			SEMINARS_TABLE_SITES . ' LEFT JOIN ' . SEMINARS_TABLE_SEMINARS_SITES_MM .
				' ON ' . SEMINARS_TABLE_SITES . '.uid = ' .
				SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_foreign',
			'uid_local = ' . $this->getUid(),
			'uid_foreign'
		);

		foreach ($cityData as $city) {
			$cities[] = $city['city'];
		}

		return $cities;
	}

	/**
	 * Returns the name of the requested country from the static info tables.
	 * If the country with this ISO code could not be found in the database,
	 * an empty string is returned instead.
	 *
	 * @param string the ISO 3166-1 alpha-2 code of the country, must
	 *               not be empty
	 *
	 * @return string the short local name of the country or an empty
	 *                string if the country could not be found
	 */
	public function getCountryNameFromIsoCode($isoCode) {
		// Sanitizes the provided parameter against SQL injection as this
		// function can be used for searching.
		$isoCode = $GLOBALS['TYPO3_DB']->quoteStr($isoCode, 'static_countries');

		try {
			$dbResultRow = tx_oelib_db::selectSingle(
				'cn_short_local',
				'static_countries',
				'cn_iso_2 = "' . $isoCode . '"'
			);
			$countryName = $dbResultRow['cn_short_local'];
		} catch (tx_oelib_Exception_EmptyQueryResult $exception) {
			$countryName = '';
		}

		return $countryName;
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
	protected function getPlaceWithDetailsRaw() {
		if (!$this->hasPlace()) {
			return $this->translate('message_willBeAnnounced');
		}

		$placeTexts = array();

		foreach ($this->getPlacesAsArray() as $place) {
			$placeText = $place['title'];
			if ($place['homepage'] != '') {
				$placeText .= LF . $place['homepage'];
			}

			$descriptionParts = array();
			if ($place['address'] != '') {
				$descriptionParts[] = str_replace(CR, ',', $place['address']);
			}
			if ($place['city'] != '') {
				$descriptionParts[] = $place['city'];
			}
			if ($place['country'] != '') {
				$countryName = $this->getCountryNameFromIsoCode(
					$place['country']
				);
				if ($countryName != '') {
					$descriptionParts[] = $countryName;
				}
			}

			if (!empty($descriptionParts)) {
				$placeText .= ', ' . implode(', ', $descriptionParts);
			}
			if ($place['directions'] != '') {
				$placeText .= LF . str_replace(CR, ', ', $place['directions']);
			}

			$placeTexts[] = $placeText;
		}

		return implode(LF, $placeTexts);
	}

	/**
	 * Gets all places that are related to this event as an array.
	 *
	 * The array will be two-dimensional: The first dimensional is just numeric.
	 * The second dimension is associative with the following keys:
	 * title, address, city, country, homepage, directions
	 *
	 * @return array all places as a two-dimensional array, will be empty
	 *               if there are no places assigned
	 */
	protected function getPlacesAsArray() {
		return tx_oelib_db::selectMultiple(
			'title, address, city, country, homepage, directions',
			SEMINARS_TABLE_SITES . ', ' . SEMINARS_TABLE_SEMINARS_SITES_MM,
			'uid_local = ' . $this->getUid() . ' AND uid = uid_foreign' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_SITES)
		);
	}

	/**
	 * Gets our place (or places) as a plain text list (just the names).
	 * Returns a localized string "will be announced" if the seminar has no
	 * places set.
	 *
	 * Note: This function does not htmlspecialchar the place titles.
	 *
	 * @return string our places list (or '' if there is an error)
	 */
	public function getPlaceShort() {
		if (!$this->hasPlace()) {
			return $this->translate('message_willBeAnnounced');
		}

		$result = array();

		$places = tx_oelib_db::selectMultiple(
			'title',
			SEMINARS_TABLE_SITES . ', ' . SEMINARS_TABLE_SEMINARS_SITES_MM,
			'uid_local = ' . $this->getUid() . ' AND uid = uid_foreign' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_SITES)
		);
		foreach ($places as $place) {
			$result[] = $place['title'];
		}

		return implode(', ', $result);
	}

	/**
	 * Creates and returns a speakerbag object.
	 *
	 * @param string the relation in which the speakers stand to this event:
	 *               "speakers" (default), "partners", "tutors" or "leaders"
	 *
	 * @return tx_seminars_speakerbag a speakerbag object
	 */
	private function getSpeakerBag($speakerRelation = 'speakers') {
		switch ($speakerRelation) {
			case 'partners':
				$mmTable = SEMINARS_TABLE_SEMINARS_PARTNERS_MM;
				break;
			case 'tutors':
				$mmTable = SEMINARS_TABLE_SEMINARS_TUTORS_MM;
				break;
			case 'leaders':
				$mmTable = SEMINARS_TABLE_SEMINARS_LEADERS_MM;
				break;
			case 'speakers':
				// The fallthrough is intended.
			default:
				$mmTable = SEMINARS_TABLE_SEMINARS_SPEAKERS_MM;
				break;
		}

		return tx_oelib_ObjectFactory::make(
			'tx_seminars_speakerbag',
			$mmTable . '.uid_local = ' . $this->getUid() . ' AND ' .
				SEMINARS_TABLE_SPEAKERS . '.uid = ' . $mmTable . '.uid_foreign',
			$mmTable
		);
	}

	/**
	 * Gets our speaker (or speakers), complete as RTE'ed HTML with details and
	 * links.
	 * Returns an empty paragraph if this seminar doesn't have any speakers.
	 *
	 * As speakers can be related to this event as speakers, partners, tutors or
	 * leaders, the type relation can be specified. The default is "speakers".
	 *
	 * @param tslib_pibase the live pibase object
	 * @param string the relation in which the speakers stand to this event:
	 *               "speakers" (default), "partners", "tutors" or "leaders"
	 *
	 * @return string our speakers (or '' if there is an error)
	 */
	public function getSpeakersWithDescription(
		tslib_pibase $plugin, $speakerRelation = 'speakers'
	) {
		if (!$this->hasSpeakersOfType($speakerRelation)){
			return '';
		}

		$result = '';

		foreach ($this->getSpeakerBag($speakerRelation) as $speaker) {
			$name = $speaker->getLinkedTitle($plugin);
			if ($speaker->hasOrganization()) {
				$name .= ', ' . htmlspecialchars($speaker->getOrganization());
			}
			$plugin->setMarker('speaker_item_title', $name);

			$description = '';
			if ($speaker->hasDescription()) {
				$description = $speaker->getDescription($plugin);
			}
			$plugin->setMarker(
				'speaker_item_description',
				$description
			);

			$result .= $plugin->getSubpart(
				'SPEAKER_LIST_ITEM'
			);
		}

		return $result;
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
	protected function getSpeakersWithDescriptionRaw($speakerRelation = 'speakers') {
		if (!$this->hasSpeakersOfType($speakerRelation)) {
			return '';
		}

		$result = '';

		foreach ($this->getSpeakerBag($speakerRelation) as $speaker) {
			$result .= $speaker->getTitle();
			if ($speaker->hasOrganization()) {
				$result .= ', ' . $speaker->getOrganization();
			}
			if ($speaker->hasHomepage()) {
				$result .= ', ' . $speaker->getHomepage();
			}
			$result .= LF;

			if ($speaker->hasDescription()) {
				$result .= $speaker->getDescriptionRaw() . LF;
			}
		}

		return $result;
	}

	/**
	 * Gets our speaker (or speakers) as a list (just their names),
	 * linked to their homepage, if the speaker (or speakers) has one.
	 * Returns an empty string if this seminar doesn't have any speakers.
	 *
	 * As speakers can be related to this event as speakers, partners, tutors or
	 * leaders, the type relation can be specified. The default is "speakers".
	 *
	 * @param tx_oelib_templatehelper the live pibase object
	 * @param string the relation in which the speakers stand to this event:
	 *               "speakers" (default), "partners", "tutors" or "leaders"
	 *
	 * @return string our speakers list, will be empty if an error occurred
	 *                during processing
	 */
	public function getSpeakersShort(
		tx_oelib_templatehelper $plugin,
		$speakerRelation = 'speakers'
	) {
		if (!$this->hasSpeakersOfType($speakerRelation)) {
			return '';
		}

		$result = array();

		foreach ($this->getSpeakerBag($speakerRelation) as $speaker) {
			$result[] = $speaker->getLinkedTitle($plugin);
		}

		return implode(', ', $result);
	}

	/**
	 * Gets the number of speakers associated with this event.
	 *
	 * @return integer the number of speakers associated with this event,
	 *                 will be >= 0
	 */
	public function getNumberOfSpeakers() {
		return $this->getRecordPropertyInteger('speakers');
	}

	/**
	 * Gets the number of partners associated with this event.
	 *
	 * @return integer the number of partners associated with this event,
	 *                 will be >= 0
	 */
	public function getNumberOfPartners() {
		return $this->getRecordPropertyInteger('partners');
	}

	/**
	 * Gets the number of tutors associated with this event.
	 *
	 * @return integer the number of tutors associated with this event,
	 *                 will be >= 0
	 */
	public function getNumberOfTutors() {
		return $this->getRecordPropertyInteger('tutors');
	}

	/**
	 * Gets the number of leaders associated with this event.
	 *
	 * @return integer the number of leaders associated with this event,
	 *                 will be >= 0
	 */
	public function getNumberOfLeaders() {
		return $this->getRecordPropertyInteger('leaders');
	}

	/**
	 * Checks whether we have speaker relations of the specified type set.
	 *
	 * @param string the relation in which the speakers stand to this event:
	 *               "speakers" (default), "partners", "tutors" or "leaders"
	 *
	 * @return boolean TRUE if we have any speakers of the specified type
	 *                 related to this event, FALSE otherwise.
	 */
	public function hasSpeakersOfType($speakerRelation = 'speakers') {
		$hasSpeakers = FALSE;

		switch ($speakerRelation) {
			case 'partners':
				$hasSpeakers = $this->hasPartners();
				break;
			case 'tutors':
				$hasSpeakers = $this->hasTutors();
				break;
			case 'leaders':
				$hasSpeakers = $this->hasLeaders();
				break;
			case 'speakers':
				// The fallthrough is intended.
			default:
				$hasSpeakers = $this->hasSpeakers();
				break;
		}

		return $hasSpeakers;
	}

	/**
	 * Checks whether we have any speakers set.
	 *
	 * @return boolean TRUE if we have any speakers related to this event,
	 *                 FALSE otherwise
	 */
	public function hasSpeakers() {
		return $this->hasRecordPropertyInteger('speakers');
	}

	/**
	 * Checks whether we have any partners set.
	 *
	 * @return boolean TRUE if we have any partners related to this event,
	 *                 FALSE otherwise
	 */
	public function hasPartners() {
		return $this->hasRecordPropertyInteger('partners');
	}

	/**
	 * Checks whether we have any tutors set.
	 *
	 * @return boolean TRUE if we have any tutors related to this event,
	 *                 FALSE otherwise
	 */
	public function hasTutors() {
		return $this->hasRecordPropertyInteger('tutors');
	}

	/**
	 * Checks whether we have any leaders set.
	 *
	 * @return boolean TRUE if we have any leaders related to this event,
	 *                 FALSE otherwise
	 */
	public function hasLeaders() {
		return $this->hasRecordPropertyInteger('leaders');
	}

	/**
	 * Returns the language key suffix for the speaker headings.
	 *
	 * @param string the type to determine the gender and number of, must be
	 *               'speakers', 'tutors', 'leaders' or 'partners'
	 *
	 * @return string header marker for speaker heading will be
	 *                'type_number_gender'. Number will be 'single' or
	 *                'multiple' and gender will be 'male', 'female' or 'mixed'.
	 *                The only exception is multiple speakers and mixed genders,
	 *                then the result will be the input value.
	 *                Will be empty if no speaker of the given type exists for
	 *                this seminar.
	 */
	public function getLanguageKeySuffixForType($speakerType) {
		if (!$this->hasSpeakersOfType($speakerType)) {
			return '';
		}

		$result = $speakerType;
		$hasMaleSpeakers = FALSE;
		$hasFemaleSpeakers = FALSE;
		$hasMultipleSpeakers = FALSE;

		$speakers = $this->getSpeakerBag($speakerType);
		if ($speakers->count() > 1) {
			$hasMultipleSpeakers = TRUE;
			$result .= '_multiple';
		} else {
			$result .= '_single';
		}

		foreach ($speakers as $speaker) {
			switch ($speaker->getGender()) {
				case tx_seminars_speaker::GENDER_MALE:
					$hasMaleSpeakers = TRUE;
					break;
				case tx_seminars_speaker::GENDER_FEMALE:
					$hasFemaleSpeakers = TRUE;
					break;
				default:
					$hasMaleSpeakers = TRUE;
					$hasFemaleSpeakers = TRUE;
			}
		}

		if ($hasMaleSpeakers && !$hasFemaleSpeakers) {
			$result .= '_male';
		} elseif (!$hasMaleSpeakers && $hasFemaleSpeakers) {
			$result .= '_female';
		} elseif ($hasMultipleSpeakers) {
			$result =  $speakerType;
		} else {
			$result .= '_unknown';
		}

		return $result;
	}

	/**
	 * Checks whether we have a language set.
	 *
	 * @return boolean TRUE if we have a language set for this event,
	 *                 FALSE otherwise
	 */
	public function hasLanguage() {
		return $this->hasRecordPropertyString('language');
	}

	/**
	 * Returns the localized name of the language for this event. In the case
	 * that no language is selected, an empty string will be returned.
	 *
	 * @return string the localized name of the language of this event or
	 *                an empty string if no language is set
	 */
	public function getLanguageName() {
		$language = '';
		if ($this->hasLanguage()) {
			$language = $this->getLanguageNameFromISOCode(
				$this->getRecordPropertyString('language')
			);
		}
		return $language;
	}

	/**
	 * Returns the language ISO code for this event. In the case that no
	 * language is selected, an empty string will be returned.
	 *
	 * @return string the ISO code of the language of this event or an
	 *                empty string if no language is set
	 */
	public function getLanguage() {
		return $this->getRecordPropertyString('language');
	}

	/**
	 * Sets the language ISO code for this event.
	 *
	 * @param string the ISO code of the language for this event to set,
	 *               may be empty
	 */
	public function setLanguage($language) {
		$this->setRecordPropertyString('language', $language);
	}

	/**
	 * Gets our regular price as a string containing amount and currency. If
	 * no regular price has been set, either "free" or "to be announced" will
	 * be returned, depending on the TS variable showToBeAnnouncedForEmptyPrice.
	 *
	 * @return string the regular seminar price
	 */
	public function getPriceRegular() {
		if ($this->hasPriceRegular()) {
			$result = $this->formatPrice($this->getPriceRegularAmount());
		} else {
			$result =
				($this->getConfValueBoolean('showToBeAnnouncedForEmptyPrice'))
				? $this->translate('message_willBeAnnounced')
				: $this->translate('message_forFree');
		}

		return $result;
	}

	/**
	 * Gets our regular price as a decimal.
	 *
	 * @return decimal the regular event price
	 */
	private function getPriceRegularAmount() {
		return $this->getTopicDecimal('price_regular');
	}

	/**
	 * Returns the price, formatted as configured in TS.
	 * The price must be supplied as integer or floating point value.
	 *
	 * @param string the price
	 *
	 * @return string the price, formatted as in configured in TS
	 */
	public function formatPrice($value) {
		$priceViewHelper = tx_oelib_ObjectFactory::make(
			'tx_oelib_ViewHelper_Price'
		);
		$priceViewHelper->setCurrencyFromIsoAlpha3Code(
			tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars')
				->getAsString('currency')
		);
		$priceViewHelper->setValue($value);

		return $priceViewHelper->render();
	}

	/**
	 * Returns the current regular price for this event.
	 * If there is a valid early bird offer, this price will be returned,
	 * otherwise the default price.
	 *
	 * @return string the price and the currency
	 */
	public function getCurrentPriceRegular() {
		return ($this->earlyBirdApplies())
			? $this->getEarlyBirdPriceRegular()
			: $this->getPriceRegular();
	}

	/**
	 * Returns the current price for this event.
	 * If there is a valid early bird offer, this price will be returned, the
	 * default special price otherwise.
	 *
	 * @return string the price and the currency
	 */
	public function getCurrentPriceSpecial() {
		return ($this->earlyBirdApplies())
			? $this->getEarlyBirdPriceSpecial()
			: $this->getPriceSpecial();
	}

	/**
	 * Gets our regular price during the early bird phase as a string containing
	 * amount and currency.
	 *
	 * @return string the regular early bird event price
	 */
	public function getEarlyBirdPriceRegular() {
		return $this->hasEarlyBirdPriceRegular()
			? $this->formatPrice($this->getEarlyBirdPriceRegularAmount()) : '';
	}

	/**
	 * Gets our regular price during the early bird phase as a decimal.
	 *
	 * If there is no regular early bird price, this function returns "0.00".
	 *
	 * @return decimal the regular early bird event price
	 */
	private function getEarlyBirdPriceRegularAmount() {
		return $this->getTopicDecimal('price_regular_early');
	}

	/**
	 * Gets our special price during the early bird phase as a string containing
	 * amount and currency.
	 *
	 * @return string the regular early bird event price
	 */
	public function getEarlyBirdPriceSpecial() {
		return $this->hasEarlyBirdPriceSpecial()
			? $this->formatPrice($this->getEarlyBirdPriceSpecialAmount()) : '';
	}

	/**
	 * Gets our special price during the early bird phase as a decimal.
	 *
	 * If there is no special price during the early bird phase, this function
	 * returns "0.00".
	 *
	 * @return decimal the special event price during the early bird phase
	 */
	private function getEarlyBirdPriceSpecialAmount() {
		return $this->getTopicDecimal('price_special_early');
	}

	/**
	 * Checks whether this seminar has a non-zero regular price set.
	 *
	 * @return boolean TRUE if the seminar has a non-zero regular price,
	 *                 FALSE if it is free.
	 */
	public function hasPriceRegular() {
		return $this->hasTopicDecimal('price_regular');
	}

	/**
	 * Checks whether this seminar has a non-zero regular early bird price set.
	 *
	 * @return boolean TRUE if the seminar has a non-zero regular early
	 *                 bird price, FALSE otherwise
	 */
	protected function hasEarlyBirdPriceRegular() {
		return $this->hasTopicDecimal('price_regular_early');
	}

	/**
	 * Checks whether this seminar has a non-zero special early bird price set.
	 *
	 * @return boolean TRUE if the seminar has a non-zero special early
	 *                 bird price, FALSE otherwise
	 */
	protected function hasEarlyBirdPriceSpecial() {
		return $this->hasTopicDecimal('price_special_early');
	}

	/**
	 * Checks whether this event has a deadline for the early bird prices set.
	 *
	 * @return boolean TRUE if the event has an early bird deadline set,
	 *                 FALSE if not
	 */
	private function hasEarlyBirdDeadline() {
		return $this->hasRecordPropertyInteger('deadline_early_bird');
	}

	/**
	 * Returns whether an early bird price applies.
	 *
	 * @return boolean TRUE if this event has an early bird dealine set and
	 *                 this deadline is not over yet
	 */
	protected function earlyBirdApplies() {
		return ($this->hasEarlyBirdPrice() && !$this->isEarlyBirdDeadlineOver());
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
	 *                 are set
	 */
	public function hasEarlyBirdPrice() {
		// whether the event has regular prices set (a normal one and an early bird)
		$priceRegularIsOk = $this->hasPriceRegular()
			&& $this->hasEarlyBirdPriceRegular();

		// whether no special price is set, or both special prices
		// (normal and early bird) are set
		$priceSpecialIsOk = !$this->hasPriceSpecial()
			|| ($this->hasPriceSpecial() && $this->hasEarlyBirdPriceSpecial());

		return ($this->hasEarlyBirdDeadline()
			&& $priceRegularIsOk
			&& $priceSpecialIsOk);
	}

	/**
	 * Gets our special price as a string containing amount and currency.
	 * Returns an empty string if there is no special price set.
	 *
	 * @return string the special event price
	 */
	public function getPriceSpecial() {
		return $this->hasPriceSpecial()
			? $this->formatPrice($this->getPriceSpecialAmount()) : '';
	}

	/**
	 * Gets our special price as a decimal.
	 *
	 * If there is no special price, this function returns "0.00".
	 *
	 * @return decimal the special event price
	 */
	private function getPriceSpecialAmount() {
		return $this->getTopicDecimal('price_special');
	}

	/**
	 * Checks whether this seminar has a non-zero special price set.
	 *
	 * @return boolean TRUE if the seminar has a non-zero special price,
	 *                 FALSE if it is free.
	 */
	public function hasPriceSpecial() {
		return $this->hasTopicDecimal('price_special');
	}

	/**
	 * Gets our regular price (including full board) as a string containing
	 * amount and currency. Returns an empty string if there is no regular price
	 * (including full board) set.
	 *
	 * @return string the regular event price (including full board)
	 */
	public function getPriceRegularBoard() {
		return $this->hasPriceRegularBoard()
			? $this->formatPrice($this->getPriceRegularBoardAmount()) : '';
	}

	/**
	 * Gets our regular price (including full board) as a decimal.
	 *
	 * If there is no regular price (including full board), this function
	 * returns "0.00".
	 *
	 * @return decimal the regular event price (including full board)
	 */
	private function getPriceRegularBoardAmount() {
		return $this->getTopicDecimal('price_regular_board');
	}

	/**
	 * Checks whether this event has a non-zero regular price (including full
	 * board) set.
	 *
	 * @return boolean TRUE if the event has a non-zero regular price
	 *                 (including full board), FALSE otherwise
	 */
	public function hasPriceRegularBoard() {
		return $this->hasTopicDecimal('price_regular_board');
	}

	/**
	 * Gets our special price (including full board) as a string containing
	 * amount and currency. Returns an empty string if there is no special price
	 * (including full board) set.
	 *
	 * @return string the special event price (including full board)
	 */
	public function getPriceSpecialBoard() {
		return $this->hasPriceSpecialBoard()
			? $this->formatPrice($this->getPriceSpecialBoardAmount()) : '';
	}

	/**
	 * Gets our special price (including full board) as a decimal.
	 *
	 * If there is no special price (including full board), this function
	 * returns "0.00".
	 *
	 * @return decimal the special event price (including full board)
	 */
	private function getPriceSpecialBoardAmount() {
		return $this->getTopicDecimal('price_special_board');
	}

	/**
	 * Checks whether this event has a non-zero special price (including full
	 * board) set.
	 *
	 * @return boolean TRUE if the event has a non-zero special price
	 *                 (including full board), FALSE otherwise
	 */
	public function hasPriceSpecialBoard() {
		return $this->hasTopicDecimal('price_special_board');
	}

	/**
	 * Gets the titles of allowed payment methods for this event.
	 *
	 * @return array our payment method titles, will be an empty array if there
	 *               are no payment methods
	 */
	public function getPaymentMethods() {
		if (!$this->hasPaymentMethods()) {
			return array();
		}

		$rows = tx_oelib_db::selectMultiple(
			'title',
			'tx_seminars_payment_methods, tx_seminars_seminars_payment_methods_mm',
			'tx_seminars_payment_methods.uid = ' .
				'tx_seminars_seminars_payment_methods_mm.uid_foreign ' .
				'AND tx_seminars_seminars_payment_methods_mm.uid_local=' .
				$this->getTopicUid() .
				tx_oelib_db::enableFields('tx_seminars_payment_methods'),
			'',
			'tx_seminars_seminars_payment_methods_mm.sorting'
		);

		$result = array();

		foreach ($rows as $row) {
			$result[] = $row['title'];
		}

		return $result;
	}

	/**
	 * Gets our allowed payment methods, just as plain text,
	 * including the detailed description.
	 * Returns an empty string if this seminar doesn't have any payment methods.
	 *
	 * @return string our payment methods as plain text (or '' if
	 *                there is an error)
	 */
	public function getPaymentMethodsPlain() {
		if (!$this->hasPaymentMethods()) {
			return '';
		}

		$rows = tx_oelib_db::selectMultiple(
			'title, description',
			'tx_seminars_payment_methods, tx_seminars_seminars_payment_methods_mm',
			'tx_seminars_payment_methods.uid = ' .
				'tx_seminars_seminars_payment_methods_mm.uid_foreign ' .
				'AND tx_seminars_seminars_payment_methods_mm.uid_local=' .
				$this->getTopicUid() .
				tx_oelib_db::enableFields('tx_seminars_payment_methods'),
			'',
			'tx_seminars_seminars_payment_methods_mm.sorting'
		);

		$result = '';

		foreach ($rows as $row) {
			$result .= $row['title'].': ';
			$result .= $row['description'].LF.LF;
		}

		return $result;
	}

	/**
	 * Gets our allowed payment methods, just as plain text separated by LF,
	 * without the detailed description.
	 * Returns an empty string if this seminar doesn't have any payment methods.
	 *
	 * @return string our payment methods as plain text (or '' if there
	 *                is an error)
	 */
	protected function getPaymentMethodsPlainShort() {
		if (!$this->hasPaymentMethods()) {
			return '';
		}

		return implode(LF, $this->getPaymentMethods());
	}

	/**
	 * Get a single payment method, just as plain text, including the detailed description.
	 *
	 * Returns an empty string if the corresponding payment method could not be retrieved.
	 *
	 * @param integer $paymentMethodUid the UID of a single payment method, must not be zero
	 *
	 * @return string the selected payment method as plain text (or '' if there is an error)
	 */
	public function getSinglePaymentMethodPlain($paymentMethodUid) {
		if ($paymentMethodUid <= 0) {
			return '';
		}

		$dbResultPaymentMethod = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title, description',
			'tx_seminars_payment_methods',
			'uid = ' . $paymentMethodUid . tx_oelib_db::enableFields('tx_seminars_payment_methods')
		);
		if ($dbResultPaymentMethod === FALSE) {
			return '';
		}

		// We expect just one result.
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultPaymentMethod);
		if ($row === FALSE) {
			return '';
		}

		$title = (string) $row['title'];
		$description = (string) $row['description'];

		$result = $title;
		if ($description !== '') {
			$result .= ': ' . $description;
		}
		$result .= LF . LF;

		return $result;
	}

	/**
	 * Get a single payment method, just as plain text, without the detailed description.
	 *
	 * Returns an empty string if the corresponding payment method could not be retrieved.
	 *
	 * @param integer $paymentMethodUid the UID of a single payment method, must not be zero
	 *
	 * @return string the selected payment method as plain text (or '' if there is an error)
	 */
	public function getSinglePaymentMethodShort($paymentMethodUid) {
		if ($paymentMethodUid <= 0) {
			return '';
		}

		$dbResultPaymentMethod = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			'tx_seminars_payment_methods',
			'uid = ' . $paymentMethodUid . tx_oelib_db::enableFields('tx_seminars_payment_methods')
		);
		if ($dbResultPaymentMethod === FALSE) {
			return '';
		}

		// We expect just one result.
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResultPaymentMethod);
		if ($row === FALSE) {
			return '';
		}

		return $row['title'];
	}

	/**
	 * Checks whether this seminar has any payment methods set.
	 *
	 * @return boolean TRUE if the seminar has any payment methods, FALSE
	 *                 if it is free
	 */
	public function hasPaymentMethods() {
		return $this->hasTopicInteger('payment_methods');
	}

	/**
	 * Gets the number of available payment methods.
	 *
	 * @return integer the number of available payment methods, might 0
	 */
	public function getNumberOfPaymentMethods() {
		return $this->getTopicInteger('payment_methods');
	}

	/**
	 * Returns the name of the requested language from the static info tables.
	 * If no language with this ISO code could not be found in the database,
	 * an empty string is returned instead.
	 *
	 * @param string $isoCode the ISO 639 alpha-2 code of the language
	 *
	 * @return string the short local name of the language or an empty string if the language could not be found
	 */
	public function getLanguageNameFromIsoCode($isoCode) {
		$languageName = '';
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'lg_name_local',
			'static_languages',
			'lg_iso_2="'.$isoCode.'"'
		);
		if ($dbResult !== FALSE) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$languageName = (string) $row['lg_name_local'];
		}

		return $languageName;
	}

	/**
	 * Returns the type of the record. This is one out of the following values:
	 * 0 = single event (and default value of older records)
	 * 1 = multiple event topic record
	 * 2 = multiple event date record
	 *
	 * @return integer the record type
	 */
	public function getRecordType() {
		return $this->getRecordPropertyInteger('object_type');
	}

	/**
	 * Checks whether this seminar has an event type set.
	 *
	 * @return boolean TRUE if the seminar has an event type set, otherwise
	 *                 FALSE
	 */
	public function hasEventType() {
		return $this->hasTopicInteger('event_type');
	}

	/**
	 * Returns the UID of the event type that was selected for this event. If no
	 * event type has been set, 0 will be returned.
	 *
	 * @return integer UID of the event type for this event or 0 if no
	 *                 event type is set
	 */
	public function getEventTypeUid() {
		return $this->getTopicInteger('event_type');
	}

	/**
	 * Returns the event type as a string (e.g. "Workshop" or "Lecture").
	 * If the seminar has a event type selected, that one is returned.
	 * Otherwise, an empty string will be returned.
	 *
	 * @return string the type of this event, will be empty if this event
	 *                does not have a type
	 */
	public function getEventType() {
		if (!$this->hasEventType()) {
			return '';
		}

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			SEMINARS_TABLE_EVENT_TYPES,
			'uid = ' . $this->getTopicInteger('event_type') .
				tx_oelib_db::enableFields(SEMINARS_TABLE_EVENT_TYPES),
			'',
			'',
			'1'
		);

		if (!$dbResult) {
			throw new tx_oelib_Exception_Database(DATABASE_QUERY_ERROR, 1333291814);
		}
		$dbResultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		if (!$dbResultRow) {
			throw new tx_oelib_Exception_Database(DATABASE_RESULT_ERROR, 1333291826);
		}

		return $dbResultRow['title'];
	}

	/**
	 * Sets the event type for this event.
	 *
	 * @param integer the UID of the event type to set, must be >= 0
	 */
	public function setEventType($eventType) {
		if ($eventType < 0) {
			throw new InvalidArgumentException('$eventType must be >= 0.', 1333291840);
		}

		$this->setRecordPropertyInteger('event_type', $eventType);
	}

	/**
	 * Gets the minimum number of attendances required for this event
	 * (ie. how many registrations are needed so this event can take place).
	 *
	 * @return integer the minimum number of attendances, will be >= 0
	 */
	public function getAttendancesMin() {
		return $this->getRecordPropertyInteger('attendees_min');
	}

	/**
	 * Gets the maximum number of attendances for this event
	 * (the total number of seats for this event).
	 *
	 * @return integer the maximum number of attendances, will be >= 0
	 */
	public function getAttendancesMax() {
		return $this->getRecordPropertyInteger('attendees_max');
	}

	/**
	 * Gets the number of attendances for this seminar
	 * (currently the paid attendances as well as the unpaid ones).
	 *
	 * @return integer the number of attendances, will be >= 0
	 */
	public function getAttendances() {
		if (!$this->statisticsHaveBeenCalculated) {
			$this->calculateStatistics();
		}

		return $this->numberOfAttendances;
	}

	/**
	 * Checks whether there is at least one registration for this event
	 * (counting the paid attendances as well as the unpaid ones).
	 *
	 * @return boolean TRUE if there is at least one registration for this
	 *                 event, FALSE otherwise
	 */
	public function hasAttendances() {
		return (boolean) $this->getAttendances();
	}

	/**
	 * Gets the number of paid attendances for this seminar.
	 *
	 * @return integer the number of paid attendances, will be >= 0
	 */
	public function getAttendancesPaid() {
		if (!$this->statisticsHaveBeenCalculated) {
			$this->calculateStatistics();
		}

		return $this->numberOfAttendancesPaid;
	}

	/**
	 * Gets the number of attendances that are not paid yet
	 *
	 * @return integer the number of attendances that are not paid yet,
	 *                 will be >= 0
	 */
	public function getAttendancesNotPaid() {
		return ($this->getAttendances() - $this->getAttendancesPaid());
	}

	/**
	 * Gets the number of vacancies for this seminar.
	 *
	 * @return integer the number of vacancies (will be 0 if the seminar
	 *                 is overbooked)
	 */
	public function getVacancies() {
		return max(0, $this->getAttendancesMax() - $this->getAttendances());
	}

	/**
	 * Gets the number of vacancies for this seminar. If there are at least as
	 * many vacancies as configured as "showVacanciesThreshold" or this event
	 * has an unlimited number of vacancies, a localized string "enough" is
	 * returned instead. If there are no vacancies, a localized string
	 * "fully booked" is returned.
	 *
	 * If this seminar does not require a registration or has been canceled, an
	 * empty string is returned.
	 *
	 * @return string string showing the number of vacancies, may be empty
	 */
	public function getVacanciesString() {
		if ($this->isCanceled() || !$this->needsRegistration()) {
			return '';
		}

		if ($this->hasUnlimitedVacancies()) {
			return $this->translate('message_enough');
		}

		$vacancies = $this->getVacancies();
		$vacanciesTreshold
			= $this->getConfValueInteger('showVacanciesThreshold');

		if ($vacancies == 0) {
			$result = $this->translate('message_fullyBooked');
		} elseif ($vacancies >= $vacanciesTreshold) {
			$result = $this->translate('message_enough');
		} else {
			$result = $vacancies;
		}

		return $result;
	}

	/**
	 * Checks whether this seminar still has vacancies (is not full yet).
	 *
	 * @return boolean TRUE if the seminar has vacancies, FALSE if it is full
	 */
	public function hasVacancies() {
		return !$this->isFull();
	}

	/**
	 * Checks whether this seminar already is full.
	 *
	 * @return boolean TRUE if the seminar is full, FALSE if it still has
	 *                 vacancies or if there are unlimited vacancies
	 */
	public function isFull() {
		return !$this->hasUnlimitedVacancies()
			&& (($this->getAttendances() >= $this->getAttendancesMax())
		);
	}

	/**
	 * Checks whether this seminar has enough attendances to take place.
	 *
	 * @return boolean TRUE if the seminar has enough attendances,
	 *                 FALSE otherwise
	 */
	public function hasEnoughAttendances() {
		return ($this->getAttendances() >= $this->getAttendancesMin());
	}

	/**
	 * Returns TRUE if this seminar has at least one target group, FALSE
	 * otherwise.
	 *
	 * @return boolean TRUE if this seminar has at least one target group,
	 *                 FALSE otherwise
	 */
	public function hasTargetGroups() {
		return $this->hasTopicInteger('target_groups');
	}

	/**
	 * Returns a string of our event's target group titles separated by a comma
	 * (or an empty string if there aren't any).
	 *
	 * @return string the target group titles of this seminar separated by
	 *                a comma (or an empty string)
	 */
	public function getTargetGroupNames() {
		if (!$this->hasTargetGroups()) {
			return '';
		}

		$result = array();

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			SEMINARS_TABLE_TARGET_GROUPS . '.title',
			SEMINARS_TABLE_TARGET_GROUPS . ', ' . SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM,
			SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM . '.uid_local=' .
				$this->getTopicUid() . ' AND ' . SEMINARS_TABLE_TARGET_GROUPS .
				'.uid=' . SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM . '.uid_foreign' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_TARGET_GROUPS),
			'',
			SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM . '.sorting'
		);

		if ($dbResult) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result[] = $row['title'];
			}
		}

		return implode(', ', $result);
	}

	/**
	 * Returns an array of our events's target group titles (or an empty array
	 * if there aren't any).
	 *
	 * @return array the target groups of this event (or an empty array)
	 */
	public function getTargetGroupsAsArray() {
		if (!$this->hasTargetGroups()) {
			return array();
		}

		$result = array();

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			SEMINARS_TABLE_TARGET_GROUPS . '.title',
			SEMINARS_TABLE_TARGET_GROUPS . ', '.SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM,
			SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM . '.uid_local=' .
				$this->getTopicUid() . ' AND ' .
				SEMINARS_TABLE_TARGET_GROUPS . '.uid=' .
				SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM . '.uid_foreign' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_TARGET_GROUPS),
			'',
			SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM . '.sorting'
		);

		if ($dbResult) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result[] = $row['title'];
			}
		}

		return $result;
	}

	/**
	 * Gets the number of target groups associated with this event.
	 *
	 * @return integer the number of target groups associated with this
	 *                 event, will be >= 0
	 */
	public function getNumberOfTargetGroups() {
		return $this->getRecordPropertyInteger('target_groups');
	}


	/**
	 * Returns the latest date/time to register for a seminar.
	 * This is either the registration deadline (if set) or the begin date of an
	 * event.
	 *
	 * @return integer the latest possible moment to register for a seminar
	 */
	public function getLatestPossibleRegistrationTime() {
		if ($this->hasRegistrationDeadline()) {
			return $this->getRecordPropertyInteger('deadline_registration');
		}
		if (!$this->getConfValueBoolean('allowRegistrationForStartedEvents')) {
			return $this->getBeginDateAsTimestamp();
		}

		return ($this->hasEndDate()
			? $this->getEndDateAsTimestamp()
			: $this->getBeginDateAsTimestamp());
	}

	/**
	 * Returns the latest date/time to register with early bird rebate for an
	 * event. The latest time to register with early bird rebate is exactly at
	 * the early bird deadline.
	 *
	 * @return integer the latest possible moment to register with early
	 *                 bird discount for an event
	 */
	private function getLatestPossibleEarlyBirdRegistrationTime() {
		return $this->getRecordPropertyInteger('deadline_early_bird');
	}

	/**
	 * Returns the seminar registration deadline: the date and also the time
	 * (depending on the TS variable showTimeOfRegistrationDeadline).
	 * The returned string is formatted using the format configured in
	 * dateFormatYMD and timeFormat.
	 *
	 * This function will return an empty string if this event does not have a
	 * registration deadline.
	 *
	 * @return string the date + time of the deadline or an empty string
	 *                if this event has no registration deadline
	 */
	public function getRegistrationDeadline() {
		$result = '';

		if ($this->hasRegistrationDeadline()) {
			$result = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$this->getRecordPropertyInteger('deadline_registration')
			);
			if ($this->getConfValueBoolean('showTimeOfRegistrationDeadline')) {
				$result .= strftime(
					' '.$this->getConfValueString('timeFormat'),
					$this->getRecordPropertyInteger('deadline_registration')
				);
			}
		}

		return $result;
	}

	/**
	 * Checks whether this seminar has a deadline for registration set.
	 *
	 * @return boolean TRUE if the seminar has a datetime set.
	 */
	public function hasRegistrationDeadline() {
		return $this->hasRecordPropertyInteger('deadline_registration');
	}

	/**
	 * Returns the early bird deadline.
	 * The returned string is formatted using the format configured in
	 * dateFormatYMD and timeFormat.
	 *
	 * The TS parameter 'showTimeOfEarlyBirdDeadline' controls if the time
	 * should also be returned in addition to the date.
	 *
	 * This function will return an empty string if this event does not have an
	 * early-bird deadline.
	 *
	 * @return string the date and time of the early bird deadline or an
	 *                early string if this event has no early-bird deadline
	 */
	public function getEarlyBirdDeadline() {
		$result = '';

		if ($this->hasEarlyBirdDeadline()) {
			$result = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$this->getRecordPropertyInteger('deadline_early_bird')
			);
			if ($this->getConfValueBoolean('showTimeOfEarlyBirdDeadline')) {
				$result .= strftime(
					' '.$this->getConfValueString('timeFormat'),
					$this->getRecordPropertyInteger('deadline_early_bird')
				);
			}
		}

		return $result;
	}

	/**
	 * Returns the seminar unregistration deadline: the date and also the time
	 * (depending on the TS variable showTimeOfUnregistrationDeadline).
	 * The returned string is formatted using the format configured in
	 * dateFormatYMD and timeFormat.
	 *
	 * This function will return an empty string if this event does not have a
	 * unregistration deadline.
	 *
	 * @return string the date + time of the deadline or an empty string
	 *                if this event has no unregistration deadline
	 */
	public function getUnregistrationDeadline() {
		$result = '';

		if ($this->hasUnregistrationDeadline()) {
			$result = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$this->getRecordPropertyInteger('deadline_unregistration')
			);
			if ($this->getConfValueBoolean('showTimeOfUnregistrationDeadline')) {
				$result .= strftime(
					' '.$this->getConfValueString('timeFormat'),
					$this->getRecordPropertyInteger('deadline_unregistration')
				);
			}
		}

		return $result;
	}

	/**
	 * Checks whether this seminar has a deadline for unregistration set.
	 *
	 * @return boolean TRUE if the seminar has a unregistration deadline set.
	 */
	public function hasUnregistrationDeadline() {
		return $this->hasRecordPropertyInteger('deadline_unregistration');
	}

	/**
	 * Gets the event's unregistration deadline as UNIX timestamp. Will be 0
	 * if the event has no unregistration deadline set.
	 *
	 * @return integer the unregistration deadline as UNIX timestamp
	 */
	public function getUnregistrationDeadlineAsTimestamp() {
		return $this->getRecordPropertyInteger('deadline_unregistration');
	}

	/**
	 * Creates an organizerbag object and returns it.
	 * Throws an exception if there are no organizers related to this event.
	 *
	 * @return tx_seminars_organizerbag an organizerbag object
	 */
	public function getOrganizerBag() {
		if (!$this->hasOrganizers()) {
			throw new BadMethodCallException('There are no organizers related to this event.', 1333291857);
		}

		$builder = tx_oelib_ObjectFactory::make('tx_seminars_OrganizerBagBuilder');
		$builder->limitToEvent($this->getUid());

		return $builder->build();
	}

	/**
	 * Gets our organizers (as HTML code with hyperlinks to their homepage, if
	 * they have any).
	 *
	 * @param tslib_pibase a tslib_pibase object for a live page
	 *
	 * @return string the hyperlinked names of our organizers
	 */
	public function getOrganizers(tslib_pibase $plugin) {
		if (!$this->hasOrganizers()) {
			return '';
		}

		$result = array();

		$organizers = $this->getOrganizerBag();
		foreach ($organizers as $organizer) {
			$result[] = $plugin->cObj->getTypoLink(
				htmlspecialchars($organizer->getName()),
				$organizer->getHomepage(),
				array(),
				$plugin->getConfValueString('externalLinkTarget')
			);
			$organizer->__destruct();
		}

		return implode(', ', $result);
	}

	/**
	 * Gets our organizer's names (and URLs), separated by LF.
	 *
	 * @return string names and homepages of our organizers or an
	 *                empty string if there are no organizers
	 */
	protected function getOrganizersRaw() {
		if (!$this->hasOrganizers()) {
			return '';
		}

		$result = array();

		$organizers = $this->getOrganizerBag();
		foreach ($organizers as $organizer) {
			$result[] = $organizer->getName()
				.($organizer->hasHomepage() ? ', '.$organizer->getHomepage() : '');
		}
		$organizers->__destruct();

		return implode(LF, $result);
	}

	/**
	 * Gets our organizers' names and e-mail addresses in the format
	 * '"John Doe" <john.doe@example.com>'.
	 *
	 * The name is not encoded yet.
	 *
	 * @return array the organizers' names and e-mail addresses
	 */
	public function getOrganizersNameAndEmail() {
		if (!$this->hasOrganizers()) {
			return array();
		}

		$result = array();

		$organizers = $this->getOrganizerBag();
		foreach ($organizers as $organizer) {
			$result[] = '"' . $organizer->getName() . '"' .
				' <' . $organizer->getEMailAddress() . '>';
		}
		$organizers->__destruct();

		return $result;
	}

	/**
	 * Gets our organizers' e-mail addresses in the format
	 * "john.doe@example.com".
	 *
	 * @return array the organizers' e-mail addresses
	 */
	public function getOrganizersEmail() {
		if (!$this->hasOrganizers()) {
			return array();
		}

		$result = array();

		$organizers = $this->getOrganizerBag();
		foreach ($organizers as $organizer) {
			$result[] = $organizer->getEMailAddress();
		}
		$organizers->__destruct();

		return $result;
	}

	/**
	 * Gets our organizers' e-mail footers.
	 *
	 * @return array the organizers' e-mail footers, will be empty if no
	 *               organzier was set, or all organizers have no e-mail footer
	 */
	public function getOrganizersFooter() {
		if (!$this->hasOrganizers()) {
			return array();
		}

		$result = array();

		$organizers = $this->getOrganizerBag();
		foreach ($organizers as $organizer) {
			$emailFooter = $organizer->getEmailFooter();
			if ($emailFooter != '') {
				$result[] = $emailFooter;
			}
		}
		$organizers->__destruct();

		return $result;
	}

	/**
	 * Checks whether we have any organizers set, but does not check the
	 * validity of that entry.
	 *
	 * @return boolean TRUE if we have any organizers related to this
	 *                 seminar, FALSE otherwise
	 */
	public function hasOrganizers() {
		return $this->hasRecordPropertyInteger('organizers');
	}

	/**
	 * Gets the number of organizers.
	 *
	 * @return integer the number of organizers, might be 0
	 */
	public function getNumberOfOrganizers() {
		return $this->getRecordPropertyInteger('organizers');
	}

	/**
	 * Gets our organizing partners comma-separated (as HTML code with
	 * hyperlinks to their homepage, if they have any).
	 *
	 * Returns an empty string if this event has no organizing partners or
	 * something went wrong with the database query.
	 *
	 * @param tslib_pibase a tslib_pibase object for a live page
	 *
	 * @return string the hyperlinked names of our organizing partners,
	 *                or an empty string
	 */
	public function getOrganizingPartners(tslib_pibase $plugin) {
		if (!$this->hasOrganizingPartners()) {
			return '';
		}
		$result = array();

		$organizerBag = tx_oelib_ObjectFactory::make(
			'tx_seminars_organizerbag',
			SEMINARS_TABLE_SEMINARS_ORGANIZING_PARTNERS_MM . '.uid_local=' .
				$this->getUid() . ' AND ' .
				SEMINARS_TABLE_SEMINARS_ORGANIZING_PARTNERS_MM . '.uid_foreign=' .
				SEMINARS_TABLE_ORGANIZERS . '.uid',
			SEMINARS_TABLE_SEMINARS_ORGANIZING_PARTNERS_MM
		);

		foreach ($organizerBag as $organizer) {
			$result[] = $plugin->cObj->getTypoLink(
				$organizer->getName(),
				$organizer->getHomepage(),
				array(),
				$plugin->getConfValueString('externalLinkTarget')
			);
		}

		return implode(', ', $result);
	}

	/**
	 * Checks whether we have any organizing partners set.
	 *
	 * @return boolean TRUE if we have any organizing partners related to
	 *                 this event, FALSE otherwise
	 */
	public function hasOrganizingPartners() {
		return $this->hasRecordPropertyInteger('organizing_partners');
	}

	/**
	 * Gets the number of organizing partners associated with this event.
	 *
	 * @return integer the number of organizing partners associated with
	 *                 this event, will be >= 0
	 */
	public function getNumberOfOrganizingPartners() {
		return $this->getRecordPropertyInteger('organizing_partners');
	}

	/**
	 * Gets the URL to the detailed view of this seminar.
	 *
	 * If $this->conf['detailPID'] (and the corresponding flexforms value) is
	 * not set or 0, the link will use the current page's PID.
	 *
	 * @param tslib_pibase a plugin object for a live page
	 * @param boolean TRUE to create a full URL including the host instead
	 *                of just a URI without the host
	 *
	 * @return string URL of the seminar details page
	 */
	public function getDetailedViewUrl(
		tslib_pibase $plugin, $createFullUrl = TRUE
	) {
		$result = $plugin->cObj->typoLink_URL(
			$this->getDetailedViewLinkConfiguration($plugin)
		);

		// XXX We need to do this workaround of manually encoding brackets in
		// the URL due to a bug in the TYPO3 core:
		// http://bugs.typo3.org/view.php?id=3808
		$result = preg_replace(
			array('/\[/', '/\]/'),
			array('%5B', '%5D'),
			$result
		);

		if ($createFullUrl) {
			$result = t3lib_div::locationHeaderUrl($result);
		}

		return $result;
	}

	/**
	 * Gets the TYPO3 link configuration for this event's detail page. This will
	 * be either the general details page or the separate details page for this
	 * event.
	 *
	 * @param tslib_pibase a plugin object for a live page
	 *
	 * @return array configuration for cObj->typoLink/typoLink_URL,
	 *               will not be empty
	 */
	public function getDetailedViewLinkConfiguration(tslib_pibase $plugin) {
		return ($this->hasSeparateDetailsPage())
			? array(
				'parameter' => $this->getDetailsPage()
			)
			: array(
				'parameter' => $plugin->getConfValueInteger('detailPID'),
				'additionalParams' => t3lib_div::implodeArrayForUrl(
					'tx_seminars_pi1',
					array('showUid' => $this->getUid()),
					'',
					FALSE,
					TRUE
				)
			);
	}

	/**
	 * Checks whether this event has a separate details page set (which may be
	 * an internal or external URL).
	 *
	 * @return boolean TRUE if this event has a separate details page,
	 *                 FALSE otherwirse
	 */
	public function hasSeparateDetailsPage() {
		return $this->hasRecordPropertyString('details_page');
	}

	/**
	 * Returns this event's separate details page URL (which may be
	 * internal or external) or page ID.
	 *
	 * @return string the URL to this events separate details page, will be
	 *                empty if this event has no separate details page set
	 */
	public function getDetailsPage() {
		return $this->getRecordPropertyString('details_page');
	}

	/**
	 * Gets a plain text list of property values (if they exist),
	 * formatted as strings (and nicely lined up) in the following format:
	 *
	 * key1: value1
	 *
	 * @param string comma-separated list of key names
	 *
	 * @return string formatted output (may be empty)
	 */
	public function dumpSeminarValues($keysList) {
		$keys = t3lib_div::trimExplode(',', $keysList, TRUE);
		$keysWithLabels = array();

		$maxLength = 0;
		foreach ($keys as $currentKey) {
			$loweredKey = strtolower($currentKey);
			$currentLabel = $this->translate('label_'.$currentKey);
			$keysWithLabels[$loweredKey] = $currentLabel;
			$maxLength = max(
				$maxLength,
				$this->charsetConversion->strlen(
					$this->renderCharset, $currentLabel
				)
			);
		}
		$result = '';
		foreach ($keysWithLabels as $currentKey => $currentLabel) {
			switch ($currentKey) {
				case 'date':
					$value = $this->getDate('-');
					break;
				case 'place':
					$value = $this->getPlaceShort();
					break;
				case 'price_regular':
					$value = $this->getPriceRegular();
					break;
				case 'price_regular_early':
					$value = $this->getEarlyBirdPriceRegular();
					break;
				case 'price_special':
					$value = $this->getPriceSpecial();
					break;
				case 'price_special_early':
					$value = $this->getEarlyBirdPriceSpecial();
					break;
				case 'speakers':
					$value = $this->getSpeakersShort($this);
					break;
				case 'time':
					$value = $this->getTime('-');
					break;
				case 'titleanddate':
					$value = $this->getTitleAndDate('-');
					break;
				case 'event_type':
					$value = $this->getEventType();
					break;
				case 'vacancies':
					if ($this->hasUnlimitedVacancies()) {
						$value = $this->translate('label_unlimited');
					} else {
						$value = (string) $this->getVacancies();
					}
					break;
				case 'title':
					$value = $this->getTitle();
					break;
				case 'attendees':
					$value = $this->getAttendances();
					break;
				case 'enough_attendees':
					$value = ($this->hasEnoughAttendances())
						? $this->translate('label_yes')
						: $this->translate('label_no');
					break;
				case 'is_full':
					$value = ($this->isFull())
						? $this->translate('label_yes')
						: $this->translate('label_no');
					break;
				default:
					$value = $this->getRecordPropertyString($currentKey);
					break;
			}

			// Check whether there is a value to display. If not, we don't use
			// the padding and break the line directly after the label.
			if ($value != '') {
				$result .= str_pad(
					$currentLabel.': ',
					$maxLength + 2,
					' '
				).$value.LF;
			} else {
				$result .= $currentLabel.':'.LF;
			}
		}

		return $result;
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * @param integer UID of the FE user to check, must be > 0
	 *
	 * @return boolean TRUE if the user already is registered, FALSE otherwise
	 */
	public function isUserRegistered($feUserUid) {
		$result = FALSE;

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS num',
			SEMINARS_TABLE_ATTENDANCES,
			'seminar=' . $this->getUid() . ' AND user=' . $feUserUid .
				tx_oelib_db::enableFields(SEMINARS_TABLE_ATTENDANCES)
		);

		if ($dbResult) {
			$numberOfRegistrations = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
			$result = ($numberOfRegistrations['num'] > 0);
		}

		return $result;
	}

	/**
	 * Checks whether a certain user already is registered for this seminar.
	 *
	 * @param integer UID of the FE user to check, must be > 0
	 *
	 * @return string empty string if everything is OK, else a localized
	 *                error message
	 */
	public function isUserRegisteredMessage($feUserUid) {
		return ($this->isUserRegistered($feUserUid))
			? $this->translate('message_alreadyRegistered') : '';
	}

	/**
	 * Checks whether a certain user is entered as a default VIP for all events
	 * but also checks whether this user is entered as a VIP for this event,
	 * ie. he/she is allowed to view the list of registrations for this event.
	 *
	 * @param integer UID of the FE user to check, must be > 0
	 * @param integer UID of the default event VIP front-end user group
	 *
	 * @return boolean TRUE if the user is a VIP for this seminar,
	 *                 FALSE otherwise
	 */
	public function isUserVip($feUserUid, $defaultEventVipsFeGroupID) {
		$result = FALSE;
		$isDefaultVip = ($defaultEventVipsFeGroupID != 0)
			&& tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()
			&& tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser()
				->hasGroupMembership($defaultEventVipsFeGroupID);

		if ($isDefaultVip) {
			$result = TRUE;
		} else {
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'COUNT(*) AS num',
				SEMINARS_TABLE_SEMINARS_MANAGERS_MM,
				'uid_local=' . $this->getUid() . ' AND uid_foreign=' . $feUserUid
			);

			if ($dbResult) {
				$numberOfVips = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
				$result = ($numberOfVips['num'] > 0);
			}
		}

		return $result;
	}

	/**
	 * Checks whether a FE user is logged in and whether he/she may view this
	 * seminar's registrations list or see a link to it.
	 * This function can be used to check whether
	 * a) a link may be created to the page with the list of registrations
	 *    (for $whichPlugin = (seminar_list|my_events|my_vip_events))
	 * b) the user is allowed to view the list of registrations
	 *    (for $whichPlugin = (list_registrations|list_vip_registrations))
	 * c) the user is allowed to export the list of registrations as CSV
	 *    ($whichPlugin = csv_export)
	 *
	 * @param string the 'what_to_display' value, specifying the type of
	 *               plugin: (seminar_list|my_events|my_vip_events
	 *               |list_registrations|list_vip_registrations)
	 * @param integer the value of the registrationsListPID parameter
	 *                (only relevant for (seminar_list|my_events|my_vip_events))
	 * @param integer the value of the registrationsVipListPID parameter
	 *                (only relevant for (seminar_list|my_events|my_vip_events))
	 * @param integer the value of the defaultEventVipsGroupID parameter
	 *                (only relevant for (list_vip_registration|my_vip_events))
	 *
	 * @return boolean TRUE if a FE user is logged in and the user may view
	 *                 the registrations list or may see a link to that
	 *                 page, FALSE otherwise
	 */
	public function canViewRegistrationsList(
		$whichPlugin, $registrationsListPID = 0, $registrationsVipListPID = 0,
		$defaultEventVipsFeGroupID = 0
	) {
		$result = FALSE;

		if ($this->needsRegistration()
			&& tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()
		) {
			$currentUserUid = $this->getFeUserUid();
			switch ($whichPlugin) {
				case 'seminar_list':
					// In the standard list view, we could have any kind of link.
					$result = $this->canViewRegistrationsList(
							'my_events',
							$registrationsListPID)
						|| $this->canViewRegistrationsList(
							'my_vip_events',
							0,
							$registrationsVipListPID,
							$defaultEventVipsFeGroupID);
					break;
				case 'my_events':
					$result = $this->isUserRegistered($currentUserUid)
						&& ((boolean) $registrationsListPID);
					break;
				case 'my_vip_events':
					$result = $this->isUserVip(
							$currentUserUid,
							$defaultEventVipsFeGroupID)
						&& ((boolean) $registrationsVipListPID);
					break;
				case 'list_registrations':
					$result = $this->isUserRegistered($currentUserUid);
					break;
				case 'list_vip_registrations':
					$result = $this->isUserVip(
						$currentUserUid, $defaultEventVipsFeGroupID
					);
					break;
				case 'csv_export':
					$result = $this->isUserVip(
						$currentUserUid, $defaultEventVipsFeGroupID
					) && $this->getConfValueBoolean('allowCsvExportForVips');
					break;
				default:
					// For all other plugins, we don't grant access.
					break;
			}
		}

		return $result;
	}

	/**
	 * Checks whether a FE user is logged in and whether he/she may view this
	 * seminar's registrations list.
	 * This function is intended to be used from the registrations list,
	 * NOT to check whether a link to that list should be shown.
	 *
	 * @param string the 'what_to_display' value, specifying the type
	 *               of plugin: (list_registrations|list_vip_registrations)
	 *
	 * @return string empty string if everything is OK, otherwise a
	 *                localized error message
	 */
	public function canViewRegistrationsListMessage($whichPlugin) {
		$result = '';

		if (!$this->needsRegistration()) {
			$result = $this->translate('message_noRegistrationNecessary');
		} elseif (!tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()) {
			$result = $this->translate('message_notLoggedIn');
		} elseif (!$this->canViewRegistrationsList($whichPlugin)) {
			$result = $this->translate('message_accessDenied');
		}

		return $result;
	}

	/**
	 * Checks whether it is possible at all to register for this seminar,
	 * ie. it needs registration at all,
	 *     has not been canceled,
	 *     has a date set (or registration for events without a date is allowed),
	 *     has not begun yet,
	 *     the registration deadline is not over yet,
	 *     and there are still vacancies.
	 *
	 * @return boolean TRUE if registration is possible, FALSE otherwise
	 */
	public function canSomebodyRegister() {
		$registrationManager = tx_seminars_registrationmanager::getInstance();
		$allowsRegistrationByDate
			= $registrationManager->allowsRegistrationByDate($this);
		$allowsRegistrationBySeats
			= $registrationManager->allowsRegistrationBySeats($this);

		return $this->needsRegistration() && !$this->isCanceled()
			&& $allowsRegistrationByDate && $allowsRegistrationBySeats;
	}

	/**
	 * Checks whether it is possible at all to register for this seminar,
	 * ie. it needs registration at all,
	 *     has not been canceled,
	 *     has either a date set (registration for events without a date is allowed),
	 *     has not begun yet,
	 *     the registration deadline is not over yet
	 *     and there are still vacancies,
	 * and returns a localized error message if registration is not possible.
	 *
	 * @return string empty string if everything is OK, else a localized
	 *                error message
	 */
	public function canSomebodyRegisterMessage() {
		$message = '';
		$registrationManager = tx_seminars_registrationmanager::getInstance();

		if (!$this->needsRegistration()) {
			$message = $this->translate('message_noRegistrationNecessary');
		} elseif ($this->isCanceled()) {
			$message = $this->translate('message_seminarCancelled');
		} elseif (!$this->hasDate() &&
			!$this->getConfValueBoolean('allowRegistrationForEventsWithoutDate')
		) {
			$message = $this->translate('message_noDate');
		} elseif ($this->hasDate() && $this->isRegistrationDeadlineOver()) {
			$message = $this->translate('message_seminarRegistrationIsClosed');
		} elseif (!$registrationManager->allowsRegistrationBySeats($this)) {
			$message = $this->translate('message_noVacancies');
		} elseif (!$registrationManager->registrationHasStarted($this)) {
			$message = sprintf(
				$this->translate('message_registrationOpensOn'),
				$this->getRegistrationBegin()
			);
		}

		return $message;
	}

	/**
	 * Checks whether this event has been canceled.
	 *
	 * @return boolean TRUE if the event has been canceled, FALSE otherwise
	 */
	public function isCanceled() {
		return ($this->getStatus() == self::STATUS_CANCELED);
	}

	/**
	 * Checks whether the latest possibility to register for this event is over.
	 *
	 * The latest moment is either the time the event starts, or a set
	 * registration deadline.
	 *
	 * @return boolean TRUE if the deadline has passed, FALSE otherwise
	 */
	public function isRegistrationDeadlineOver() {
		return ($GLOBALS['SIM_EXEC_TIME']
			>= $this->getLatestPossibleRegistrationTime());
	}

	/**
	 * Checks whether the latest possibility to register with early bird rebate for this event is over.
	 *
	 * The latest moment is just before a set early bird deadline.
	 *
	 * @return boolean TRUE if the deadline has passed, FALSE otherwise
	 */
	public function isEarlyBirdDeadlineOver() {
		return ($GLOBALS['SIM_EXEC_TIME']
			>= $this->getLatestPossibleEarlyBirdRegistrationTime());
	}

	/**
	 * Checks whether registration is necessary for this event.
	 *
	 * @return boolean TRUE if registration is necessary, FALSE otherwise
	 */
	public function needsRegistration() {
		return (!$this->isEventTopic()
			&& $this->getRecordPropertyBoolean('needs_registration')
		);
	}

	/**
	 * Checks whether this event has unlimited vacancies (needs_registration
	 * TRUE and max_attendances 0)
	 *
	 * @return boolean TRUE if this event has unlimited vacancies, FALSE
	 *                 otherwise
	 */
	public function hasUnlimitedVacancies() {
		return ($this->needsRegistration() && ($this->getAttendancesMax() == 0));
	}

	/**
	 * Checks whether this event allows multiple registrations by the same
	 * FE user.
	 *
	 * @return boolean TRUE if multiple registrations are allowed,
	 *                 FALSE otherwise
	 */
	public function allowsMultipleRegistrations() {
		return $this->getTopicBoolean('allows_multiple_registrations');
	}

	/**
	 * (Re-)calculates the number of participants for this seminar.
	 */
	public function calculateStatistics() {
		$this->numberOfAttendances = $this->countAttendances(
			'registration_queue=0'
		) + $this->getOfflineRegistrations();
		$this->numberOfAttendancesPaid = $this->countAttendances(
			'datepaid != 0 AND registration_queue = 0'
		);
		$this->numberOfAttendancesOnQueue = $this->countAttendances(
			'registration_queue=1'
		);
		$this->statisticsHaveBeenCalculated = TRUE;
	}

	/**
	 * Queries the DB for the number of visible attendances for this event
	 * and returns the result of the DB query with the number stored in 'num'
	 * (the result will be zero if the query fails).
	 *
	 * This function takes multi-seat registrations into account as well.
	 *
	 * An additional string can be added to the WHERE clause to look only for
	 * certain attendances, e.g. only the paid ones.
	 *
	 * Note that this does not write the values back to the seminar record yet.
	 * This needs to be done in an additional step after this.
	 *
	 * @param string string that will be prepended to the WHERE clause
	 *               using AND, e.g. 'pid=42' (the AND and the enclosing
	 *               spaces are not necessary for this parameter)
	 *
	 * @return integer the number of attendances, will be >= 0
	 */
	private function countAttendances($queryParameters = '1=1') {
		$result = 0;

		$dbResultSingleSeats = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(*) AS number',
			SEMINARS_TABLE_ATTENDANCES,
			$queryParameters .
				' AND seminar=' . $this->getUid() .
				' AND seats=0' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_ATTENDANCES)
		);

		if ($dbResultSingleSeats) {
			$fieldsSingleSeats = $GLOBALS['TYPO3_DB']->sql_fetch_assoc(
				$dbResultSingleSeats
			);
			$result += $fieldsSingleSeats['number'];
		}

		$dbResultMultiSeats = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'SUM(seats) AS number',
			SEMINARS_TABLE_ATTENDANCES,
			$queryParameters .
				' AND seminar=' . $this->getUid() .
				' AND seats!=0' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_ATTENDANCES)
		);

		if ($dbResultMultiSeats) {
			$fieldsMultiSeats = $GLOBALS['TYPO3_DB']->sql_fetch_assoc(
				$dbResultMultiSeats
			);
			$result += $fieldsMultiSeats['number'];
		}

		return $result;
	}

	/**
	 * Retrieves the topic from the DB and returns it as an object.
	 *
	 * In case of an error, the return value will be null.
	 *
	 * @return tx_seminars_seminar the topic object (will be null if an error
	 *                             has occured)
	 */
	private function retrieveTopic() {
		$result = null;

		// Check whether this event has an topic set.
		if ($this->hasRecordPropertyInteger('topic')) {
			if (tx_seminars_objectfromdb::recordExists(
				$this->getRecordPropertyInteger('topic'),
				SEMINARS_TABLE_SEMINARS)
			) {
				$result = tx_oelib_ObjectFactory::make(
					'tx_seminars_seminar',
					$this->getRecordPropertyInteger('topic')
				);
			}
		}
		return $result;
	}

	/**
	 * Checks whether we are a date record.
	 *
	 * @return boolean TRUE if we are a date record, FALSE otherwise.
	 */
	public function isEventDate() {
		return ($this->getRecordPropertyInteger('object_type')
			== SEMINARS_RECORD_TYPE_DATE);
	}

	/**
	 * Checks whether we are a topic record.
	 *
	 * @return boolean TRUE if we are a topic record, FALSE otherwise.
	 */
	public function isEventTopic() {
		return ($this->getRecordPropertyInteger('object_type')
			== SEMINARS_RECORD_TYPE_TOPIC);
	}

	/**
	 * Checks whether we are a date record and have a topic.
	 *
	 * @return boolean TRUE if we are a date record and have a topic,
	 *                 FALSE otherwise
	 */
	private function isTopicOkay() {
		return ($this->isEventDate() && $this->topic && $this->topic->isOk());
	}

	/**
	 * Gets the UID of the topic record if we are a date record. Otherwise, the
	 * UID of this record is returned.
	 *
	 * @return integer either the UID of this record or its topic record,
	 *                 depending on whether we are a date record
	 */
	public function getTopicUid() {
		if ($this->isTopicOkay()) {
			return $this->topic->getUid();
		} else {
			return $this->getUid();
		}
	}

	/**
	 * Checks a integer element of the record data array for existence and
	 * non-emptiness. If we are a date record, it'll be retrieved from the
	 * corresponding topic record.
	 *
	 * @param string key of the element to check
	 *
	 * @return boolean TRUE if the corresponding integer exists and is non-empty
	 */
	protected function hasTopicInteger($key) {
		$result = FALSE;

		if ($this->isTopicOkay()) {
			$result = $this->topic->hasRecordPropertyInteger($key);
		} else {
			$result = $this->hasRecordPropertyInteger($key);
		}

		return $result;
	}

	/**
	 * Gets an (intval'ed) integer element of the record data array.
	 * If the array has not been initialized properly, 0 is returned instead.
	 * If we are a date record, it'll be retrieved from the corresponding
	 * topic record.
	 *
	 * @param string the name of the field to retrieve
	 *
	 * @return integer the corresponding element from the record data array
	 */
	protected function getTopicInteger($key) {
		$result = 0;

		if ($this->isTopicOkay()) {
			$result = $this->topic->getRecordPropertyInteger($key);
		} else {
			$result = $this->getRecordPropertyInteger($key);
		}

		return $result;
	}

	/**
	 * Checks a string element of the record data array for existence and
	 * non-emptiness. If we are a date record, it'll be retrieved from the
	 * corresponding topic record.
	 *
	 * @param string key of the element to check
	 *
	 * @return boolean TRUE if the corresponding string exists and is non-empty
	 */
	private function hasTopicString($key) {
		$result = FALSE;

		if ($this->isTopicOkay()) {
			$result = $this->topic->hasRecordPropertyString($key);
		} else {
			$result = $this->hasRecordPropertyString($key);
		}

		return $result;
	}

	/**
	 * Gets a trimmed string element of the record data array.
	 * If the array has not been initialized properly, an empty string is
	 * returned instead. If we are a date record, it'll be retrieved from the
	 * corresponding topic record.
	 *
	 * @param string the name of the field to retrieve
	 *
	 * @return string the corresponding element from the record data array
	 */
	private function getTopicString($key) {
		$result = '';

		if ($this->isTopicOkay()) {
			$result = $this->topic->getRecordPropertyString($key);
		} else {
			$result = $this->getRecordPropertyString($key);
		}

		return $result;
	}

	/**
	 * Checks a decimal element of the record data array for existence and a
	 * value != 0.00. If we are a date record, it'll be retrieved from the
	 * corresponding topic record.
	 *
	 * @param string key of the element to check
	 *
	 * @return boolean TRUE if the corresponding decimal value exists
	 *                 and is not 0.00
	 */
	private function hasTopicDecimal($key) {
		$result = FALSE;

		if ($this->isTopicOkay()) {
			$result = $this->topic->hasRecordPropertyDecimal($key);
		} else {
			$result = $this->hasRecordPropertyDecimal($key);
		}

		return $result;
	}

	/**
	 * Gets a decimal element of the record data array.
	 * If the array has not been initialized properly, an empty string is
	 * returned instead. If we are a date record, it'll be retrieved from the
	 * corresponding topic record.
	 *
	 * @param string the name of the field to retrieve
	 *
	 * @return string the corresponding element from the record data array
	 */
	private function getTopicDecimal($key) {
		$result = '';

		if ($this->isTopicOkay()) {
			$result = $this->topic->getRecordPropertyDecimal($key);
		} else {
			$result = $this->getRecordPropertyDecimal($key);
		}

		return $result;
	}

	/**
	 * Gets an element of the record data array, converted to a boolean.
	 * If the array has not been initialized properly, FALSE is returned.
	 *
	 * If we are a date record, it'll be retrieved from the corresponding topic
	 * record.
	 *
	 * @param string the name of the field to retrieve
	 *
	 * @return boolean the corresponding element from the record data array
	 */
	private function getTopicBoolean($key) {
		return ($this->isTopicOkay())
			? $this->topic->getRecordPropertyBoolean($key)
			: $this->getRecordPropertyBoolean($key);
	}

	/**
	 * Checks whether we have any lodging options.
	 *
	 * @return boolean TRUE if we have at least one lodging option,
	 *                 FALSE otherwise
	 */
	public function hasLodgings() {
		return $this->hasRecordPropertyInteger('lodgings');
	}

	/**
	 * Gets the lodging options associated with this event.
	 *
	 * @return array an array of lodging options, consisting each of a nested
	 *               array with the keys "caption" (for the title) and "value"
	 *               (for the UID), might be empty
	 */
	public function getLodgings() {
		$result = array();

		if ($this->hasLodgings()) {
			$result = $this->getMmRecords(
				SEMINARS_TABLE_LODGINGS,
				SEMINARS_TABLE_SEMINARS_LODGINGS_MM,
				FALSE
			);
		}

		return $result;
	}

	/**
	 * Checks whether we have any food options.
	 *
	 * @return boolean TRUE if we have at least one food option, FALSE otherwise
	 */
	public function hasFoods() {
		return $this->hasRecordPropertyInteger('foods');
	}

	/**
	 * Gets the food options associated with this event.
	 *
	 * @return array an array of food options, consisting each of a nested array
	 *               with the keys "caption" (for the title) and "value" (for
	 *               the UID), might be empty
	 */
	public function getFoods() {
		$result = array();

		if ($this->hasFoods()) {
			$result = $this->getMmRecords(
				SEMINARS_TABLE_FOODS,
				SEMINARS_TABLE_SEMINARS_FOODS_MM,
				FALSE
			);
		}

		return $result;
	}

	/**
	 * Checks whether we have any option checkboxes. If we are a date record,
	 * the corresponding topic record will be checked.
	 *
	 * @return boolean TRUE if we have at least one option checkbox,
	 *                 FALSE otherwise
	 */
	public function hasCheckboxes() {
		return $this->hasTopicInteger('checkboxes');
	}

	/**
	 * Gets the option checkboxes associated with this event. If we are a date
	 * record, the option checkboxes of the corresponding topic record will be
	 * retrieved.
	 *
	 * @return array an array of option checkboxes, consisting each of a nested
	 *               array with the keys "caption" (for the title) and "value"
	 *               (for the UID), might be empty
	 */
	public function getCheckboxes() {
		$result = array();

		if ($this->hasCheckboxes()) {
			$result = $this->getMmRecords(
				SEMINARS_TABLE_CHECKBOXES,
				SEMINARS_TABLE_SEMINARS_CHECKBOXES_MM,
				TRUE
			);
		}

		return $result;
	}

	/**
	 * Gets the uids and titles of records referenced by this record. If we are
	 * a date record and $useTopicRecord is TRUE, the referenced records of the
	 * corresponding topic record will be retrieved.
	 *
	 * @param string the name of the foreign table (must not be empty),
	 *               must have the fields uid and title
	 * @param string the name of the m:m table, having the fields uid_local,
	 *               uid_foreign and sorting, must not be empty
	 * @param boolean TRUE if the referenced records of the corresponding
	 *                topic record should be retrieved, FALSE otherwise
	 *
	 * @return array an array of referenced records, consisting each of a nested
	 *               array with the keys "caption" (for the title) and "value"
	 *               (for the UID), might be empty
	 */
	private function getMmRecords($foreignTable, $mmTable, $useTopicRecord) {
		$result = array();

		$uid = ($useTopicRecord) ?
			$this->getTopicInteger('uid') : $this->getUid();

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, title, sorting',
			$foreignTable.', '.$mmTable,
			// uid_local and uid_foreign are from the m:m table;
			// uid and sorting are from the foreign table.
			'uid_local=' . $uid . ' AND uid_foreign=uid' .
				tx_oelib_db::enableFields($foreignTable),
			'',
			'sorting'
		);

		if ($dbResult) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result[$row['uid']] = array(
					'caption' => $row['title'],
					'value'   => $row['uid']
				);
			}
		}

		return $result;
	}

	/**
	 * Converts an array m:m records (each having a "value" and a "caption"
	 * element) to a LF-separated string.
	 *
	 * @param array m:n elements, each having a "value" and "caption"
	 *              element, may be empty
	 *
	 * @return string the captions of the array contents separated by
	 *                LF, will be empty if the array is empty
	 */
	private function mmRecordsToText($records) {
		$result = '';

		if (!empty($records)) {
			foreach ($records as $currentRecord) {
				if (!empty($result)) {
					$result .= LF;
				}
				$result .= $currentRecord['caption'];
			}
		}

		return $result;
	}

	/**
	 * Gets the PID of the system folder where the registration records of this
	 * event should be stored. If no folder is set in this event's topmost
	 * organizer record (ie. the page configured in
	 * plugin.tx_seminars.attendancesPID should be used), this function will
	 * return 0.
	 *
	 * @return integer the PID of the systen folder where registration records
	 *                 for this event should be stored or 0 if no folder is set
	 */
	public function getAttendancesPid() {
		if (!$this->hasOrganizers()) {
			return 0;
		}

		$organizers = $this->getOrganizerBag();
		$firstOrganizer = $organizers->current();
		$organizers->__destruct();

		return $firstOrganizer->getAttendancesPid();
	}

	/**
	 * Checks whether this event's topmost organizer has a PID set to store the
	 * registration records in.
	 *
	 * @return boolean TRUE if a the systen folder for registration
	 *                 records is specified in this event's topmost
	 *                 organizers record, FALSE otherwise
	 */
	public function hasAttendancesPid() {
		return (boolean) $this->getAttendancesPid();
	}

	/**
	 * Gets this event's owner (the FE user who has created this event).
	 *
	 * @return tx_oelib_Model_FrontEndUser the owner, will be null if the event
	 *                                     has no owner
	 */
	public function getOwner() {
		if (!$this->hasRecordPropertyInteger('owner_feuser')) {
			return null;
		}

		return tx_oelib_MapperRegistry::get('tx_oelib_Mapper_FrontEndUser')
			->find($this->getRecordPropertyInteger('owner_feuser'));
	}

	/**
	 * Checks whether this event has an existing owner (the FE user who has
	 * created this event).
	 *
	 * @return boolean TRUE if this event has an existing owner, FALSE otherwise
	 */
	public function hasOwner() {
		return $this->hasRecordPropertyInteger('owner_feuser');
	}

	/**
	 * Checks whether the logged-in FE user is the owner of this event.
	 *
	 * @return boolean TRUE if a FE user is logged in and the user is
	 *                 the owner of this event, FALSE otherwise
	 */
	public function isOwnerFeUser() {
		return $this->hasRecordPropertyInteger('owner_feuser')
			&& ($this->getRecordPropertyInteger('owner_feuser')
				== $this->getFeUserUid());
	}

	/**
	 * Checkes whether the "travelling terms" checkbox (ie. the second "terms"
	 * checkbox) should be displayed in the registration form for this event.
	 *
	 * If we are a date record, this is checked for the corresponding topic
	 * record.
	 *
	 * Note: This is not related to entries in the showRegistrationFields
	 * configuration variable. This function checks this on a per-event basis
	 * whereas showRegistrationFields is a global option.
	 *
	 * @return boolean TRUE if the "travelling terms" checkbox should
	 *                 be displayed, FALSE otherwise
	 */
	public function hasTerms2() {
		return $this->getTopicBoolean('uses_terms_2');
	}

	/**
	 * Gets the teaser text (not RTE'ed). If this is a date record, the
	 * corresponding topic's teaser text is retrieved.
	 *
	 * @return string this event's teaser text (or '' if there is an error)
	 */
	public function getTeaser() {
		return $this->getTopicString('teaser');
	}

	/**
	 * Checks whether this event (or this event' topic record) has a teaser
	 * text.
	 *
	 * @return boolean TRUE if we have a non-empty teaser text,
	 *                 FALSE otherwise
	 */
	public function hasTeaser() {
		return $this->hasTopicString('teaser');
	}

	/**
	 * Retrieves a value from this record. The return value will be an empty
	 * string if the key is not defined in $this->recordData or if it has not
	 * been filled in.
	 *
	 * If the data needs to be decoded to be readable (eg. the speakers
	 * payment or the gender), this function will already return the clear text
	 * version.
	 *
	 * @param string the key of the data to retrieve (the key doesn't
	 *               need to be trimmed)
	 *
	 * @return string the data retrieved from $this->recordData, may be empty
	 */
	public function getEventData($key) {
		$trimmedKey = trim($key);

		switch ($trimmedKey) {
			case 'uid':
				$result = $this->getUid();
				break;
			case 'tstamp':
				// The fallthrough is intended.
			case 'crdate':
				$result = strftime(
					$this->getConfValueString('dateFormatYMD').' '
						.$this->getConfValueString('timeFormat'),
					$this->getRecordPropertyInteger($trimmedKey)
				);
				break;
			case 'title':
				$result = $this->getTitle();
				break;
			case 'subtitle':
				$result = $this->getSubtitle();
				break;
			case 'teaser':
				$result = $this->getTeaser();
				break;
			case 'description':
				$result = $this->getDescription();
				break;
			case 'event_type':
				$result = $this->getEventType();
				break;
			case 'accreditation_number':
				$result = $this->getAccreditationNumber();
				break;
			case 'credit_points':
				$result = $this->getCreditPoints();
				break;
			case 'date':
				$result = $this->getDate('-');
				break;
			case 'time':
				$result = $this->getTime('-');
				break;
			case 'deadline_registration':
				$result = $this->getRegistrationDeadline();
				break;
			case 'deadline_early_bird':
				$result = $this->getEarlyBirdDeadline();
				break;
			case 'deadline_unregistration':
				$result = $this->getUnregistrationDeadline();
				break;
			case 'place':
				$result = $this->getPlaceWithDetailsRaw();
				break;
			case 'room':
				$result = $this->getRoom();
				break;
			case 'lodgings':
				$result = $this->mmRecordsToText($this->getLodgings());
				break;
			case 'foods':
				$result = $this->mmRecordsToText($this->getFoods());
				break;
			case 'speakers':
				// The fallthrough is intended.
			case 'partners':
				// The fallthrough is intended.
			case 'tutors':
				// The fallthrough is intended.
			case 'leaders':
				$result = $this->getSpeakersWithDescriptionRaw($trimmedKey);
				break;
			case 'price_regular':
				$result = $this->getPriceRegular();
				break;
			case 'price_regular_early':
				$result = $this->getEarlyBirdPriceRegular();
				break;
			case 'price_regular_board':
				$result = $this->getPriceRegularBoard();
				break;
			case 'price_special':
				$result = $this->getPriceSpecial();
				break;
			case 'price_special_early':
				$result = $this->getEarlyBirdPriceSpecial();
				break;
			case 'price_special_board':
				$result = $this->getPriceSpecialBoard();
				break;
			case 'additional_information':
				$result = $this->getAdditionalInformation();
				break;
			case 'payment_methods':
				$result = $this->getPaymentMethodsPlainShort();
				break;
			case 'organizers':
				$result = $this->getOrganizersRaw();
				break;
			case 'attendees_min':
				$result = $this->getAttendancesMin();
				break;
			case 'attendees_max':
				$result = $this->getAttendancesMax();
				break;
			case 'attendees':
				$result = $this->getAttendances();
				break;
			case 'vacancies':
				$result = $this->getVacancies();
				break;
			case 'enough_attendees':
				$result = ($this->hasEnoughAttendances())
					? $this->translate('label_yes')
					: $this->translate('label_no');
				break;
			case 'is_full':
				$result = ($this->isFull())
					? $this->translate('label_yes')
					: $this->translate('label_no');
				break;
			case 'cancelled':
				$result = ($this->isCanceled())
					? $this->translate('label_yes')
					: $this->translate('label_no');
				break;
			default:
				$result = '';
				break;
		}

		$carriageReturnRemoved = (strpos($result, CR) === FALSE)
			? $result
			: str_replace(CR, LF, $result);

		return preg_replace('/\\x0a{2,}/', LF, $carriageReturnRemoved);
	}

	/**
	 * Gets the list of available prices, prepared for a drop-down list.
	 * In the sub-arrays, the "caption" element contains the description of
	 * the price (e.g. "Standard price" or "Early-bird price"), the "value"
	 * element contains a code for the price, but not the price itself (so two
	 * different price categories that cost the same are no problem). In
	 * addition, the "amount" element contains the amount (without currency).
	 *
	 * If there is an early-bird price available and the early-bird deadline has
	 * not passed yet, the early-bird price is used.
	 *
	 * This function returns an array of arrays, e.g.
	 *
	 * 'regular' => (
	 *   'value'   => 'regular',
	 *   'amount'  => '50.00',
	 *   'caption' => 'Regular price: 50 EUR'
	 * ),
	 * 'regular_board' => (
	 *   'value'   => 'regular_board',
	 *   'amount'  => '80.00',
	 *   'caption' => 'Regular price with full board: 80 EUR'
	 * )
	 *
	 * So the keys for the sub-arrays and their "value" elements are the same.
	 *
	 * The possible keys are:
	 * regular, regular_early, regular_board,
	 * special, special_early, special_board
	 *
	 * The return array's pointer will already be reset to its first element.
	 *
	 * @return array the available prices as a reset array of arrays
	 *               with the keys "caption" (for the title) and "value"
	 *               (for the price code), might be empty
	 */
	public function getAvailablePrices() {
		$result = array();

		if ($this->hasEarlyBirdPriceRegular() && $this->earlyBirdApplies()) {
			$result['regular_early'] = array(
				'value' => 'regular_early',
				'amount' => $this->getEarlyBirdPriceRegularAmount(),
				'caption' => $this->translate('label_price_earlybird_regular')
					.': '.$this->getEarlyBirdPriceRegular()
			);
		} else {
			$result['regular'] = array(
				'value' => 'regular',
				'amount' => $this->getPriceRegularAmount(),
				'caption' => $this->translate('label_price_regular')
					.': '.$this->getPriceRegular()
			);
		}
		if ($this->hasPriceRegularBoard()) {
			$result['regular_board'] = array(
				'value' => 'regular_board',
				'amount' => $this->getPriceRegularBoardAmount(),
				'caption' => $this->translate('label_price_board_regular')
					.': '.$this->getPriceRegularBoard()
			);
		}

		if ($this->hasPriceSpecial()) {
			if ($this->hasEarlyBirdPriceSpecial() && $this->earlyBirdApplies()) {
				$result['special_early'] = array(
					'value' => 'special_early',
					'amount' => $this->getEarlyBirdPriceSpecialAmount(),
					'caption' => $this->translate('label_price_earlybird_special')
						.': '.$this->getEarlyBirdPriceSpecial()
				);
			} else {
				$result['special'] = array(
					'value' => 'special',
					'amount' => $this->getPriceSpecialAmount(),
					'caption' => $this->translate('label_price_special')
						.': '.$this->getPriceSpecial()
				);
			}
		}
		if ($this->hasPriceSpecialBoard()) {
			$result['special_board'] = array(
				'value' => 'special_board',
					'amount' => $this->getPriceSpecialBoardAmount(),
				'caption' => $this->translate('label_price_board_special')
					.': '.$this->getPriceSpecialBoard()
			);
		}

		// reset the pointer for the result array to the first element
		reset($result);

		return $result;
	}

	/**
	 * Checks whether a given price category currently is available for this
	 * event.
	 *
	 * The allowed price category codes are:
	 * regular, regular_early, regular_board,
	 * special, special_early, special_board
	 *
	 * @param string code for the price category to check, may be empty
	 *               or null
	 *
	 * @return boolean TRUE if $priceCode matches a currently available
	 *                 price, FALSE otherwise
	 */
	public function isPriceAvailable($priceCode) {
		$availablePrices = $this->getAvailablePrices();

		return !empty($priceCode) && isset($availablePrices[$priceCode]);
	}

	/**
	 * Checks whether this event currently has at least one non-free price
	 * (taking into account whether we still are in the early-bird period).
	 *
	 * @return boolean TRUE if this event currently has at least one
	 *                 non-zero price, FALSE otherwise
	 */
	public function hasAnyPrice() {
		if ($this->earlyBirdApplies()) {
			$result = $this->hasEarlyBirdPriceRegular()
				|| $this->hasEarlyBirdPriceSpecial();
		} else {
			$result = $this->hasPriceRegular()
				|| $this->hasPriceSpecial();
		}

		// There is no early-bird version of the prices that include full board.
		$result = $result || $this->hasPriceRegularBoard()
			|| $this->hasPriceSpecialBoard();

		return $result;
	}

	/**
	 * Checks whether a front-end user is already blocked during the time for
	 * a given event by other booked events.
	 *
	 * For this, only events that forbid multiple registrations are checked.
	 *
	 * @param integer UID of the FE user to check, must be > 0
	 *
	 * @return boolean TRUE if user is blocked by another registration,
	 *                 FALSE otherwise
	 */
	public function isUserBlocked($feUserUid) {
		$result = FALSE;

		// If no user is logged in or this event allows multiple registrations,
		// the user is not considered to be blocked for this event.
		// If this event doesn't have a date yet, the time cannot be blocked
		// either.
		if (($feUserUid > 0) && !$this->allowsMultipleRegistrations()
			&& $this->hasDate()  && !$this->skipCollisionCheck()) {

			$additionalTables = SEMINARS_TABLE_ATTENDANCES;
			$queryWhere = $this->getQueryForCollidingEvents();
			// Filter to those events to which the given FE user is registered.
			$queryWhere .= ' AND '.SEMINARS_TABLE_SEMINARS.'.uid='
					.SEMINARS_TABLE_ATTENDANCES.'.seminar'
				.' AND '.SEMINARS_TABLE_ATTENDANCES.'.user='.$feUserUid;

			$seminarBag = tx_oelib_ObjectFactory::make(
				'tx_seminars_seminarbag', $queryWhere, $additionalTables
			);

			// One blocking event is enough.
			$result = !$seminarBag->isEmpty();
		}

		return $result;
	}

	/**
	 * Checkes whether the collision check should be skipped for this event.
	 *
	 * @return boolean TRUE if the collision check should be skipped for
	 *                 this event, FALSE otherwise
	 */
	private function skipCollisionCheck() {
		return $this->getConfValueBoolean('skipRegistrationCollisionCheck') ||
			$this->getRecordPropertyBoolean('skip_collision_check');
	}

	/**
	 * Creates a WHERE clause that selects events that collide with this event's
	 * times.
	 *
	 * This query will only take events into account that do *not* allow
	 * multiple registrations.
	 *
	 * For open-ended events, only the begin date is checked.
	 *
	 * @return string WHERE clause (without the "WHERE" keyword), will not
	 *                be empty
	 */
	private function getQueryForCollidingEvents() {
		$beginDate = $this->getBeginDateAsTimestamp();
		$endDate = $this->getEndDateAsTimestampEvenIfOpenEnded();

		$result = SEMINARS_TABLE_SEMINARS.'.uid!='.$this->getUid()
			.' AND allows_multiple_registrations=0'
			.' AND skip_collision_check=0'
			.' AND ('
				.'('
					// Check for events that have a begin date in our
					// time-frame.
					// This will automatically rule out events without a date.
					.'begin_date>'.$beginDate.' AND begin_date<'.$endDate
				.') OR ('
					// Check for events that have an end date in our time-frame.
					// This will automatically rule out events without a date.
					.'end_date>'.$beginDate.' AND end_date<'.$endDate
				.') OR ('
					// Check for events that have a non-zero start date,
					// start before this event and end after it.
					.'begin_date>0 AND '
					.'begin_date<='.$beginDate.' AND end_date>='.$endDate
				.')'
			.')';

		return $result;
	}

	/**
	 * Gets the date.
	 * Returns an empty string if the seminar record is a topic record.
	 * Otherwise will return the date or a localized string "will be
	 * announced" if there's no date set.
	 *
	 * Returns just one day if we take place on only one day.
	 * Returns a date range if we take several days.
	 *
	 * @param string the character or HTML entity used to separate
	 *               start date and end date
	 *
	 * @return string the seminar date (or an empty string or a
	 *                localized message)
	 */
	public function getDate($dash = '&#8211;') {
		$result = '';

		if ($this->getRecordPropertyInteger('object_type')
			!= SEMINARS_RECORD_TYPE_TOPIC
		) {
			$result = parent::getDate($dash);
		}

		return $result;
	}

	/**
	 * Returns TRUE if the seminar is hidden, otherwise FALSE.
	 *
	 * @return boolean TRUE if the seminar is hidden, FALSE otherwise
	 */
	public function isHidden() {
		return $this->getRecordPropertyBoolean('hidden');
	}

	/**
	 * Returns TRUE if unregistration is possible. That means the unregistration
	 * deadline hasn't been reached yet.
	 *
	 * If the unregistration deadline is not set globally via TypoScript and not
	 * set in the current event record, the unregistration will not be possible
	 * and this method returns FALSE.
	 *
	 * @return boolean TRUE if unregistration is possible, FALSE otherwise
	 */
	public function isUnregistrationPossible() {
		if (!$this->needsRegistration()) {
			return FALSE;
		}

		$canUnregisterByQueue = $this->getConfValueBoolean(
			'allowUnregistrationWithEmptyWaitingList'
		) || (
			$this->hasRegistrationQueue()
				&& $this->hasAttendancesOnRegistrationQueue()
		);

		$deadline = $this->getUnregistrationDeadlineFromModelAndConfiguration();
		if ($this->hasBeginDate() || ($deadline != 0)) {
			$canUnregisterByDate = ($GLOBALS['SIM_EXEC_TIME'] < $deadline);
		} else {
			$canUnregisterByDate =
				($this->getUnregistrationDeadlineFromConfiguration() != 0);
		}

		return $canUnregisterByQueue && $canUnregisterByDate;
	}

	/**
	 * Checks if this event has a registration queue.
	 *
	 * @return boolean TRUE if this event has a registration queue, FALSE
	 *                 otherwise
	 */
	public function hasRegistrationQueue() {
		return $this->getRecordPropertyBoolean('queue_size');
	}

	/**
	 * Gets the number of attendances on the registration queue.
	 *
	 * @return integer number of attendances on the registration queue
	 */
	public function getAttendancesOnRegistrationQueue() {
		if (!$this->statisticsHaveBeenCalculated) {
			$this->calculateStatistics();
		}

		return $this->numberOfAttendancesOnQueue;
	}

	/**
	 * Checks whether there is at least one registration on the waiting list.
	 *
	 * @return boolean TRUE if there is at least one registration on the
	 *                 waiting list, FALSE otherwise
	 */
	public function hasAttendancesOnRegistrationQueue() {
		return ($this->getAttendancesOnRegistrationQueue() > 0);
	}

	/**
	 * Returns an array of UIDs for records of a given m:n table that contains
	 * relations to this event record.
	 *
	 * Example: To find out which places are related to this event, just call
	 * this method with the name of the seminars -> places m:n table. The result
	 * is an array that contains the UIDs of all the places that are related to
	 * this event.
	 *
	 * @param string the name of the m:n table to query, must not be empty
	 *
	 * @return array array of foreign record's UIDs, ordered by the field
	 *               uid_foreign in the m:n table, may be empty
	 */
	public function getRelatedMmRecordUids($tableName) {
		$result = array();

		// Fetches all the corresponding records for this event from the
		// selected m:n table.
		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid_foreign',
			$tableName,
			'uid_local='.$this->getUid(),
			'',
			'sorting'
		);

		// Adds the uid to the result array when the DB result contains at least
		// one entry.
		if ($dbResult) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result[] = $row['uid_foreign'];
			}
		}

		return $result;
	}

	/**
	 * Checks whether there's a (begin) date set or any time slots exist.
	 * If there's an end date but no begin date, this function still will return
	 * FALSE.
	 *
	 * @return boolean TRUE if we have a begin date, FALSE otherwise.
	 */
	public function hasDate() {
		return ($this->hasBeginDate() || $this->hasTimeslots());
	}

	/**
	 * Returns TRUE if the seminar has at least one time slot, otherwise FALSE.
	 *
	 * @return boolean TRUE if the seminar has at least one time slot,
	 *                 otherwise FALSE
	 */
	public function hasTimeslots() {
		return $this->hasRecordPropertyInteger('timeslots');
	}

	/**
	 * Returns our begin date and time as a UNIX timestamp.
	 *
	 * @return integer our begin date and time as a UNIX timestamp or 0 if
	 *                 we don't have a begin date
	 */
	public function getBeginDateAsTimestamp() {
		if (!$this->hasTimeslots()) {
			return parent::getBeginDateAsTimestamp();
		}

		$result = 0;

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'MIN('.SEMINARS_TABLE_TIME_SLOTS.'.begin_date) AS begin_date',
			SEMINARS_TABLE_TIME_SLOTS,
			SEMINARS_TABLE_TIME_SLOTS . '.seminar=' . $this->getUid() .
				tx_oelib_db::enableFields(SEMINARS_TABLE_TIME_SLOTS)
		);

		if ($dbResult) {
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result = $row['begin_date'];
			}
		}

		return $result;
	}

	/**
	 * Returns our end date and time as a UNIX timestamp.
	 *
	 * @return integer our end date and time as a UNIX timestamp or 0 if we
	 *                 don't have an end date
	 */
	public function getEndDateAsTimestamp() {
		if (!$this->hasTimeslots()) {
			return parent::getEndDateAsTimestamp();
		}

		$result = 0;

		$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			SEMINARS_TABLE_TIME_SLOTS.'.end_date AS end_date',
			SEMINARS_TABLE_TIME_SLOTS,
			SEMINARS_TABLE_TIME_SLOTS . '.seminar=' . $this->getUid() .
				tx_oelib_db::enableFields(SEMINARS_TABLE_TIME_SLOTS),
			'',
			SEMINARS_TABLE_TIME_SLOTS.'.begin_date DESC',
			'0,1'
		);

		if ($dbResult) {
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$result = $row['end_date'];
			}
		}

		return $result;
	}

	/**
	 * Updates the place relations of the event in replacing them with the place
	 * relations of the time slots.
	 * This function will remove existing place relations and adds relations to
	 * all places of the event's time slots in the database.
	 * This function is a no-op for events without time slots.
	 *
	 * @return integer the number of place relations of the event, will be >= 0
	 */
	public function updatePlaceRelationsFromTimeSlots() {
		if (!$this->hasTimeslots()) {
			return 0;
		}
		$timeSlotBag = $this->createTimeSlotBag();
		if ($timeSlotBag->isEmpty()) {
			return $this->getNumberOfPlaces();
		}

		// Removes all place relations of the current event.
		tx_oelib_db::delete(
			SEMINARS_TABLE_SEMINARS_SITES_MM,
			SEMINARS_TABLE_SEMINARS_SITES_MM . '.uid_local = ' . $this->getUid()
		);

		// Creates an array with all place UIDs which should be related to this
		// event.
		$placesOfTimeSlots = array();
		foreach ($timeSlotBag as $timeSlot) {
			if ($timeSlot->hasPlace()) {
				$placesOfTimeSlots[] = $timeSlot->getPlace();
			}
		}

		return $this->createMmRecords(
			SEMINARS_TABLE_SEMINARS_SITES_MM, $placesOfTimeSlots
		);
	}

	/**
	 * Creates a time slot bag for the time slots associated with this event.
	 *
	 * @return tx_seminars_timeslotbag bag with this event's time slots, may be
	 *                                 empty
	 */
	private function createTimeSlotBag() {
		return tx_oelib_ObjectFactory::make(
			'tx_seminars_timeslotbag',
			SEMINARS_TABLE_TIME_SLOTS . '.seminar = ' . $this->getUid() .
				' AND ' . SEMINARS_TABLE_TIME_SLOTS . '.place > 0',
			'',
			SEMINARS_TABLE_TIME_SLOTS . '.place',
			SEMINARS_TABLE_TIME_SLOTS . '.begin_date ASC'
		);
	}

	/**
	 * Returns our time slots in an array.
	 *
	 * @return array an array of time slots or an empty array if there are no
	 *               time slots
	 *               the array contains the following elements:
	 *               - 'date' as key and the time slot's begin date as value
	 *               - 'time' as key and the time slot's time as value
	 *               - 'entry_date' as key and the time slot's entry date as
	 *               value
	 *               - 'room' as key and the time slot's room as value
	 *               - 'place' as key and the time slot's place as value
	 *               - 'speakers' as key and the time slot's speakers as value
	 */
	public function getTimeSlotsAsArrayWithMarkers() {
		$result = array();

		$timeSlotBag = tx_oelib_ObjectFactory::make(
			'tx_seminars_timeslotbag',
			SEMINARS_TABLE_TIME_SLOTS . '.seminar = ' . $this->getUid(),
			'',
			'',
			SEMINARS_TABLE_TIME_SLOTS . '.begin_date ASC'
		);

		foreach ($timeSlotBag as $timeSlot) {
			$result[] = array(
				'uid' => $timeSlot->getUid(),
				'date' => $timeSlot->getDate(),
				'time' => $timeSlot->getTime(),
				'entry_date' => $timeSlot->getEntryDate(),
				'room' => $timeSlot->getRoom(),
				'place' => $timeSlot->getPlaceShort(),
				'speakers' => $timeSlot->getSpeakersShortCommaSeparated()
			);
		}

		return $result;
	}

	/**
	 * Checks whether this seminar has at least one category.
	 *
	 * @return boolean TRUE if the seminar has at least one category,
	 *                 FALSE otherwise
	 */
	public function hasCategories() {
		return $this->hasTopicInteger('categories');
	}

	/**
	 * Gets the number of categories associated with this event.
	 *
	 * @return integer the number of categories associated with this event,
	 *                 will be >= 0
	 */
	public function getNumberOfCategories() {
		return $this->getTopicInteger('categories');
	}

	/**
	 * Gets this event's category titles and icons as an associative
	 * array (which may be empty), using the category UIDs as keys.
	 *
	 * @return array 2 level associative array with the UID as first level key
	 *               and "title" and "icon" as second level keys. "Title" will
	 *               contain the category title and "icon" will contain the
	 *               category icon. Will be an empty array in if the event has
	 *               no categories.
	 */
	public function getCategories() {
		if (!$this->hasCategories()) {
			return array();
		}

		$builder = tx_oelib_ObjectFactory::make('tx_seminars_categorybagbuilder');
		$builder->limitToEvents($this->getTopicUid());
		$builder->sortByRelationOrder();
		$bag = $builder->build();

		$result = array();
		foreach ($bag as $key => $category) {
			$result[$key] =
				array(
					'title' => $category->getTitle(),
					'icon'  => $category->getIcon(),
				);
		}
		$bag->__destruct();

		return $result;
	}

	/**
	 * Returns whether this event has at least one attached file.
	 *
	 * @return boolean TRUE if this event has at least one attached file,
	 *                 FALSE otherwise
	 */
	public function hasAttachedFiles() {
		return $this->hasRecordPropertyString('attached_files');
	}

	/**
	 * Gets our attached files as an array of arrays with the elements "name"
	 * and "size" of the attached file.
	 *
	 * The displayed file name is relative to the tx_seminars upload directory
	 * and is linked to the actual file's URL.
	 *
	 * The file size will have, depending on the file size, one of the following
	 * units appended: K for Kilobytes, M for Megabytes and G for Gigabytes.
	 *
	 * The returned array will be sorted like the files are sorted in the back-
	 * end form.
	 *
	 * Note: This functions' return values already are htmlspecialchared.
	 *
	 * @param tslib_pibase a tslib_pibase object for a live page
	 *
	 * @return array an array of arrays with the elements "name" and
	 *               "size" of the attached file, will be empty if
	 *               there are no attached files
	 */
	public function getAttachedFiles(tslib_pibase $plugin) {
		if (!$this->hasAttachedFiles()) {
			return array();
		}

		$result = array();
		$uploadFolderPath = PATH_site . 'uploads/tx_seminars/';
		$uploadFolderUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL') .
			'uploads/tx_seminars/';

		$attachedFiles = t3lib_div::trimExplode(
			',', $this->getRecordPropertyString('attached_files'), TRUE
		);

		foreach ($attachedFiles as $attachedFile) {
			$matches = array();
			preg_match('/\.(\w+)$/', basename($attachedFile), $matches);

			$result[] = array(
				'name' => $plugin->cObj->typoLink(
					htmlspecialchars(basename($attachedFile)),
					array('parameter' => $uploadFolderUrl . $attachedFile)
				),
				'type' => htmlspecialchars((isset($matches[1]) ? $matches[1] : 'none')),
				'size' => t3lib_div::formatSize(filesize($uploadFolderPath . $attachedFile)),
			);
		}

		return $result;
	}

	/**
	 * Sets our attached files.
	 *
	 * @param string a comma-separated list of the names of attached files
	 *               which have to exist in "uploads/tx_seminars/"
	 */
	public function setAttachedFiles($attachedFiles) {
		$this->setRecordPropertyString('attached_files', $attachedFiles);
	}

	/**
	 * Gets the file name of our image.
	 *
	 * @return string the file name of our image (relative to the extension's
	 *                upload path) or '' if this event has no image
	 */
	public function getImage() {
		return $this->getTopicString('image');
	}

	/**
	 * Checks whether we have an image.
	 *
	 * @return boolean TRUE if we have an non-empty image, FALSE otherwise.
	 */
	public function hasImage() {
		return $this->hasTopicString('image');
	}

	/**
	 * Creates the style for the title of the seminar.
	 *
	 * @param integer maximum width of the image, must be > 0
	 * @param integer maximum height of the image, must be > 0
	 *
	 * @return string the complete style attribute for the seminar title
	 *                containing the seminar image starting with a space, will
	 *                be empty if seminar has no image
	 */
	public function createImageForSingleView($maxImageWidth, $maxImageHeight) {
		if (!$this->hasImage()) {
			return '';
		}

		$imageWidth = array();
		$imageHeight = array();
		$imageUrl = array();
		$imageWithTag = $this->createRestrictedImage(
			SEMINARS_UPLOAD_PATH . $this->getImage(),
			'',
			$maxImageWidth,
			$maxImageHeight
		);

		preg_match('/width="([^"]*)"/', $imageWithTag, $imageWidth);
		preg_match('/height="([^"]*)"/', $imageWithTag, $imageHeight);
		preg_match('/src="([^"]*)"/', $imageWithTag, $imageUrl);

		return ' style="' .
			'background-image: url(\'' . $imageUrl[1] . '\'); ' .
			'background-repeat: no-repeat; ' .
			'padding-left: ' . $imageWidth[1] . 'px; '.
			'height: ' . $imageHeight[1] . 'px;"';
	}

	/**
	 * Checks whether this event has any requiring events, ie. topics that are
	 * prerequisite for this event
	 *
	 * @return boolean TRUE if this event has any requiring events, FALSE
	 *                 otherwise
	 */
	public function hasRequirements() {
		return $this->hasTopicInteger('requirements');
	}

	/**
	 * Checks whether this event has any depending events, ie. topics for which
	 * this event is prerequisite.
	 *
	 * @return boolean TRUE if this event has any depending events, FALSE
	 *                 otherwise
	 */
	public function hasDependencies() {
		return $this->hasTopicInteger('dependencies');
	}

	/**
	 * Returns the required events for the current event topic, ie. topics that
	 * are prerequisites for this event.
	 *
	 * @return tx_seminars_seminarbag the required events, will be empty if this
	 *                                event has no required events
	 */
	public function getRequirements() {
		$builder = tx_oelib_ObjectFactory::make('tx_seminars_seminarbagbuilder');
		$builder->limitToRequiredEventTopics($this->getTopicUid());

		return $builder->build();
	}

	/**
	 * Returns the depending events for the current event topic, ie. topics for
	 * which this event is a prerequisite.
	 *
	 * @return tx_seminars_seminarbag the depending events, will be empty if
	 *                                this event has no depending events
	 */
	public function getDependencies() {
		$builder = tx_oelib_ObjectFactory::make('tx_seminars_seminarbagbuilder');
		$builder->limitToDependingEventTopics($this->getTopicUid());

		return $builder->build();
	}

	/**
	 * Checks whether this event has been confirmed.
	 *
	 * @return boolean TRUE if the event has been confirmed, FALSE otherwise
	 */
	public function isConfirmed() {
		return ($this->getStatus() == self::STATUS_CONFIRMED);
	}

	/**
	 * Checks whether this event has been planned.
	 *
	 * @return boolean TRUE if the event has been planned, FALSE otherwise
	 */
	public function isPlanned() {
		return ($this->getStatus() == self::STATUS_PLANNED);
	}

	/**
	 * Gets the staus of this event.
	 *
	 * @return integer the status of this event, will be >= 0
	 */
	private function getStatus() {
		return $this->getRecordPropertyInteger('cancelled');
	}

	/**
	 * Sets whether this event is planned, canceled or confirmed.
	 *
	 * @param integer STATUS_PLANNED, STATUS_CONFIRMED or STATUS_CANCELED
	 */
	public function setStatus($status) {
		$this->setRecordPropertyInteger('cancelled', $status);
	}

	/**
	 * Returns the cancelation deadline of this event, depending on the
	 * cancelation deadlines of the speakers.
	 *
	 * Before this function is called assure that this event has a begin date.
	 *
	 * @return integer the cancelation deadline of this event as timestamp,
	 *                 will be >= 0
	 */
	public function getCancelationDeadline() {
		if (!$this->hasBeginDate()) {
			throw new BadMethodCallException(
				'The event has no begin date. Please call this function only if the event has a begin date.', 1333291877
			);
		}
		if (!$this->hasSpeakers()) {
			return $this->getBeginDateAsTimestamp();
		}

		$beginDate = $this->getBeginDateAsTimestamp();
		$deadline = $beginDate;
		$speakers = $this->getSpeakerBag();

		foreach ($speakers as $speaker) {
			$speakerDeadline = $beginDate -
				($speaker->getCancelationPeriodInDays()
					* tx_seminars_timespan::SECONDS_PER_DAY
				);
			$deadline = min($speakerDeadline, $deadline);
		}

		$speakers->__destruct();

		return $deadline;
	}

	/**
	 * Sets the "cancelation_deadline_reminder_sent" flag.
	 */
	public function setCancelationDeadlineReminderSentFlag() {
		$this->setRecordPropertyBoolean(
			'cancelation_deadline_reminder_sent', TRUE
		);
	}

	/**
	 * Sets the "event_takes_place_reminder_sent" flag.
	 */
	public function setEventTakesPlaceReminderSentFlag() {
		$this->setRecordPropertyBoolean(
			'event_takes_place_reminder_sent', TRUE
		);
	}

	/**
	 * Checks whether this event has a license expiry.
	 *
	 * @return boolean TRUE if this event has a license expiry, FALSE otherwise
	 */
	public function hasExpiry() {
		return $this->hasRecordPropertyInteger('expiry');
	}

	/**
	 * Gets this event's license expiry date as a formatted date.
	 *
	 * @return string this event's license expiry date as a formatted date,
	 *                will be empty if this event has no license expiry
	 */
	public function getExpiry() {
		if (!$this->hasExpiry()) {
			return '';
		}

		return strftime(
			$this->getConfValueString('dateFormatYMD'),
			$this->getRecordPropertyInteger('expiry')
		);
	}

	/**
	 * Checks whether this event has a begin date for the registration.
	 *
	 * @return boolean TRUE if this event has a begin date for the registration,
	 *                 FALSE otherwise
	 */
	public function hasRegistrationBegin() {
		return $this->hasRecordPropertyInteger('begin_date_registration');
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
		return $this->getRecordPropertyInteger('begin_date_registration');
	}

	/**
	 * Returns the begin date for the registration of this event.
	 * The returned string is formatted using the format configured in
	 * dateFormatYMD and timeFormat.
	 *
	 * This function will return an empty string if this event does not have a
	 * registration begin date.
	 *
	 * @return string the date and time of the registration begin date, will be
	 *                an empty string if this event registration begin date
	 */
	public function getRegistrationBegin() {
		if (!$this->hasRegistrationBegin()) {
			return '';
		}

		return strftime(
			$this->getConfValueString('dateFormatYMD') . ' '
				. $this->getConfValueString('timeFormat'),
			$this->getRecordPropertyInteger('begin_date_registration')
		);
	}

	/**
	 * Returns the places associated with this event.
	 *
	 * @return tx_oelib_List with the models for the places of this event, will
	 *                       be empty if this event has no places
	 */
	public function getPlaces() {
		if (!$this->hasPlace()) {
			return tx_oelib_ObjectFactory::make('tx_oelib_List');
		}

		$places = tx_oelib_db::selectMultiple(
			'uid, title, address, city, country, homepage, directions',
			SEMINARS_TABLE_SITES . ', ' . SEMINARS_TABLE_SEMINARS_SITES_MM,
			'uid_local = ' . $this->getUid() . ' AND uid = uid_foreign' .
				tx_oelib_db::enableFields(SEMINARS_TABLE_SITES)
		);

		return tx_oelib_ObjectFactory::make('tx_seminars_Mapper_Place')
			->getListOfModels($places);
	}

	/**
	 * Checks whether this event has any offline registrations.
	 *
	 * @return boolean TRUE if this event has at least one offline registration,
	 *                 FALSE otherwise
	 */
	public function hasOfflineRegistrations() {
		return $this->hasRecordPropertyInteger('offline_attendees');
	}

	/**
	 * Returns the number of offline registrations for this event.
	 *
	 * @return integer the number of offline registrations for this event, will
	 *                 be 0 if this event has no offline registrations
	 */
	public function getOfflineRegistrations() {
		return $this->getRecordPropertyInteger('offline_attendees');
	}

	/**
	 * Returns the unregistration deadline set by configuration and the begin
	 * date as UNIX timestamp.
	 *
	 * This function may only be called if this event has a begin date.
	 *
	 * @return integer the unregistration deadline as UNIX timestamp determined
	 *                 by configuration and the begin date, will be 0 if the
	 *                 unregistrationDeadlineDaysBeforeBeginDate is not set
	 */
	private function getUnregistrationDeadlineFromConfiguration() {
		if (!$this->hasConfValueInteger(
			'unregistrationDeadlineDaysBeforeBeginDate'
		)) {
			return 0;
		}

		$secondsForUnregistration = ONE_DAY * $this->getConfValueInteger(
			'unregistrationDeadlineDaysBeforeBeginDate'
		);

		return $this->getBeginDateAsTimestamp() - $secondsForUnregistration;
	}

	/**
	 * Returns the effective unregistration deadline for this event as UNIX
	 * timestamp.
	 *
	 * @return integer the unregistration deadline for this event as UNIX
	 *                 timestamp, will be 0 if this event has no begin date
	 */
	public function getUnregistrationDeadlineFromModelAndConfiguration() {
		if ($this->hasUnregistrationDeadline()) {
			return $this->getUnregistrationDeadlineAsTimestamp();
		}

		if (!$this->hasBeginDate()) {
			return 0;
		}

		return $this->getUnregistrationDeadlineFromConfiguration();
	}

	/**
	 * Returns this event's publication hash.
	 *
	 * The publication hash will be empty for published events and non-empty for
	 * events that have not been published yet.
	 *
	 * The publication hash is not related to whether an event is hidden:
	 * Visible events may also have a non-empty publication hash.
	 *
	 * @return string this event's publication hash, will be empty for published
	 *                events
	 */
	public function getPublicationHash() {
		return $this->getRecordPropertyString('publication_hash');
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
		$this->setRecordPropertyString('publication_hash', $hash);
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
		return !$this->hasRecordPropertyString('publication_hash');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_seminar.php']);
}
?>