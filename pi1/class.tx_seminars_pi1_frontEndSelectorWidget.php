<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2009 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'frontEndSelectorWidget' for the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Mario Rimann <typo3-coding@rimann.org>
 */
class tx_seminars_pi1_frontEndSelectorWidget extends tx_seminars_pi1_frontEndView {
	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php';

	/**
	 * @var tx_staticinfotables_pi1 needed for the list view to convert ISO
	 *                              codes to country names and languages
	 */
	protected $staticInfo = null;

	/**
	 * @var array the keys of the search fields which should be displayed in the
	 *            search form
	 */
	private $displayedSearchFields = array();

	/**
	 * @var string the prefix of every subpart of the search widget
	 */
	const SUBPART_PREFIX = 'SEARCH_PART_';

	/**
	 * @var tx_seminars_seminarbag all seminars to show in the list view
	 */
	private $seminarBag = null;

	/**
	 * @var tx_seminars_placebag all places which are assigned to at least one
	 *      seminar
	 */
	private $placeBag = null;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->seminarBag) {
			$this->seminarBag->__destruct();
			unset($this->seminarBag);
		}
		if ($this->placeBag) {
			$this->placeBag->__destruct();
			unset($this->placeBag);
		}
		unset($this->staticInfo);

		parent::__destruct();
	}

	/**
	 * Returns the selector widget if it is not hidden.
	 *
	 * The selector widget will automatically be hidden, if no search option is
	 * selected to be displayed.
	 *
	 * @return string the HTML code of the selector widget, may be empty
	 */
	public function render() {
		if (!$this->hasConfValueString('displaySearchFormFields', 's_listView')) {
			return '';
		}

		$this->initialize();

		$this->fillOrHideSearchSubpart('event_type');
		$this->fillOrHideSearchSubpart('language');
		$this->fillOrHideSearchSubpart('place');
		$this->fillOrHideSearchSubpart('country');
		$this->fillOrHideSearchSubpart('city');
		$this->fillOrHideSearchSubpart('organizer');
		$this->fillOrHideFullTextSearch();
		$this->fillOrHideDateSearch();
		$this->fillOrHideAgeSearch();
		$this->fillOrHidePriceSearch();

		return $this->getSubpart('SELECTOR_WIDGET');
	}

	/**
	 * Initializes some variables needed for further processing.
	 */
	private function initialize() {
		$this->displaySearchFormFields = t3lib_div::trimExplode(
			',',
			$this->getConfValueString(
				'displaySearchFormFields', 's_listView'),
			true
		);

		$this->instantiateStaticInfo();
		$builder = tx_oelib_ObjectFactory::make('tx_seminars_seminarbagbuilder');
		$builder->limitToEventTypes(
			t3lib_div::trimExplode(
				',',
				$this->getConfValueString(
					'limitListViewToEventTypes', 's_listView'
				),
				true
			)
		);

		$this->seminarBag = $builder->build();
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Adds a dummy option to the array of options. This is needed if the
	 * user wants to show the option box as drop-down selector instead of
	 * a multi-line select.
	 *
	 * With the default configuration, this method is a no-op as
	 * "showEmptyEntryInOptionLists" is disabled.
	 *
	 * If this option is activated in the TS configuration, the dummy option
	 * will be prepended to the existing arrays. So we can be sure that the
	 * dummy option will always be the first one in the array and thus shown
	 * first in the drop-down.
	 *
	 * @param array array of options, may be empty
	 */
	private function addEmptyOptionIfNeeded(array &$options) {
		if ($this->getConfValueBoolean(
			'showEmptyEntryInOptionLists', 's_template_special'
		)) {
			$completeOptionList = array(
				'none' => $this->translate('label_selector_pleaseChoose')
			);
			foreach ($options as $key => $value) {
				$completeOptionList[$key] = $value;
			}

			$options = $completeOptionList;
		}
	}

	/**
	 * Removes the dummy option from the submitted form data.
	 *
	 * @param array the POST data submitted from the form, may be empty
	 *
	 * @return array the POST data without the dummy option
	 */
	public static function removeDummyOptionFromFormData(array $formData) {
		$cleanedFormData = array();

		foreach ($formData as $value) {
			if ($value != 'none') {
				$cleanedFormData[] = $value;
			}
		}

		return $cleanedFormData;
	}

	/**
	 * Creates the HTML code for a single option box of the selector widget.
	 *
	 * @param string the name of the option box to generate, must be one of the
	 *               following: 'event_type', 'language', 'country', 'city',
	 *               'places'
	 * @param array the options for the option box with the option value as key
	 *              and the option label as value, may be empty
	 *
	 * @return string the HTML content for the select, will not be empty
	 */
	private function createOptionBox($name, array $options) {
		$this->setMarker('options_header', $this->translate('label_' . $name));
		$this->setMarker(
			'optionbox_name', $this->prefixId . '[' . $name . '][]'
		);
		$this->setMarker('optionbox_id', $this->prefixId . '-' . $name);

		$optionsList = '';
		foreach ($options as $key => $label) {
			$this->setMarker('option_label', htmlspecialchars($label));
			$this->setMarker('option_value', $key);

			// Preselects the option if it was selected by the user.
			if (isset($this->piVars[$name])
				&& (in_array($key, $this->piVars[$name]))
			) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$this->setMarker('option_selected', $selected);

			$optionsList .= $this->getSubpart('OPTIONS_ENTRY');
		}

		$this->setMarker('options', $optionsList);

		return $this->getSubpart('OPTIONS_BOX');
	}

	/**
	 * Creates a place bag with all places that are assigned to at least one
	 * event.
	 *
	 * The place bag is stored in the member variable $this->placeBag.
	 *
	 * Before this function is called, it must be assured that the seminar bag
	 * is not empty.
	 */
	private function createPlaceBag() {
		if ($this->seminarBag->isEmpty()) {
			throw new Exception('The seminar bag must not be empty when ' .
				'calling this function.'
			);
		}
		if ($this->placeBag) {
			return;
		}

		$whereClause = SEMINARS_TABLE_SITES . '.uid = uid_foreign AND ' .
			'uid_local IN (' . $this->seminarBag->getUids() . ')';

		$this->placeBag = tx_oelib_ObjectFactory::make(
			'tx_seminars_placebag', $whereClause, SEMINARS_TABLE_SEMINARS_SITES_MM
		);
	}

	/**
	 * Creates an instance of tx_staticinfotables_pi1 if that has not happened
	 * yet.
	 */
	protected function instantiateStaticInfo() {
		if ($this->staticInfo instanceof tx_staticinfotables_pi1) {
			return;
		}

		$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
		$this->staticInfo->init();
	}

	/**
	 * Checks whether a given search field key should be displayed.
	 *
	 * @param string the search field name to check, must not be empty
	 *
	 * @return boolean true if the given field should be displayed as per
	 *                 configuration, false otherwise
	 */
	private function hasSearchField($fieldToCheck) {
		return in_array($fieldToCheck, $this->displaySearchFormFields);
	}

	/**
	 * Creates a drop-down, including an empty option at the top.
	 *
	 * @param array the options for the drop-down, the keys will be used as
	 *              values and the array values as labels for the options, may
	 *              be empty
	 * @param string the HTML name of the drop-down, must be not empty and
	 *               unique
	 */
	private function createDropDown($options, $name) {
		$this->setMarker('dropdown_name', $this->prefixId . '[' . $name . ']');
		$this->setMarker('dropdown_id', $this->prefixId . '-' . $name);

		// Adds an empty option to the dropdown
		$this->setMarker('option_value', 0);
		$this->setMarker('option_label', '&nbsp;');
		$this->setMarker('option_selected', '');
		$optionsList = $this->getSubpart('OPTIONS_ENTRY');

		foreach ($options as $optionValue => $optionName) {
			$this->setMarker('option_value', $optionValue);
			$this->setMarker('option_label', $optionName);

			if ($this->piVars[$name] == $optionValue) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}

			$this->setMarker('option_selected', $selected);
			$optionsList .= $this->getSubpart('OPTIONS_ENTRY');
		}

		$this->setMarker('dropdown_options', $optionsList);

		return $this->getSubpart('SINGLE_DROPDOWN');
	}


	///////////////////////////////////////////////////////////////
	// Functions for hiding or filling the search widget subparts
	///////////////////////////////////////////////////////////////

	/**
	 * Fills or hides the subpart for the given search field.
	 *
	 * @param string the key of the search field, must be one of the following:
	 *               "event_type", "language", "country", "city", "places",
	 *               "organizer"
	 */
	private function fillOrHideSearchSubpart($searchField) {
		if (!$this->hasSearchField($searchField)) {
			$this->hideSubparts(
				self::SUBPART_PREFIX . strtoupper($searchField)
			);

			return;
		}

		$optionData = array();
		switch ($searchField) {
			case 'event_type':
				$optionData = $this->getEventTypeData();
				break;
			case 'language':
				$optionData = $this->getLanguageData();
				break;
			case 'place':
				$optionData = $this->getPlaceData();
				break;
			case 'city':
				$optionData = $this->getCityData();
				break;
			case 'country':
				$optionData = $this->getCountryData();
				break;
			case 'organizer':
				$optionData = $this->getOrganizerData();
				break;
			default:
				throw new Exception('The given search field .
					"' . $searchField . '" was not an allowed value. ' .
					'Allowed values are: "event_type", "language", "country", ' .
					'"city", "place" or "organizer".'
				);
				break;
		}

		$this->addEmptyOptionIfNeeded($optionData);
		$optionBox = $this->createOptionBox($searchField, $optionData);

		$this->setMarker('options_' . $searchField, $optionBox);
	}

	/**
	 * Fills or hides the full text search subpart.
	 */
	private function fillOrHideFullTextSearch() {
		if (!$this->hasSearchField('full_text_search')) {
			$this->hideSubparts(self::SUBPART_PREFIX . 'TEXT');

			return;
		}

		$this->setMarker(
			'searchbox_value', htmlspecialchars($this->piVars['sword'])
		);
	}

	/**
	 * Fills or hides the date search subpart.
	 */
	private function fillOrHideDateSearch() {
		if (!$this->hasSearchField('date')) {
			$this->hideSubparts(
				self::SUBPART_PREFIX . 'DATE'
			);

			return;
		}

		$dateArrays = $this->createDateArray();

		foreach (array('from', 'to') as $fromOrTo) {
			$dropdowns = '';
			foreach ($dateArrays as $dropdownPart => $dateArray) {
				$dropdowns .= $this->createDropDown(
					$dateArray, $fromOrTo . '_' . $dropdownPart
				);
			}
			$this->setMarker('options_date_' . $fromOrTo, $dropdowns);
		}
	}

	/**
	 * Fills or hides the age search subpart.
	 */
	private function fillOrHideAgeSearch() {
		if (!$this->hasSearchField('age')) {
			$this->hideSubparts(
				self::SUBPART_PREFIX . 'AGE'
			);

			return;
		}
		$age = intval($this->piVars['age']);

		$this->setMarker(
			'age_value', (($age > 0) ? $age : '')
		);
	}

	/**
	 * Fills or hides the price search subpart.
	 */
	private function fillOrHidePriceSearch() {
		if (!$this->hasSearchField('price')) {
			$this->hideSubparts(
				self::SUBPART_PREFIX . 'PRICE'
			);

			return;
		}

		$priceFrom = intval($this->piVars['price_from']);
		$priceTo = intval($this->piVars['price_to']);

		$this->setMarker(
			'price_from_value', (($priceFrom > 0) ? $priceFrom : '')
		);
		$this->setMarker(
			'price_to_value', (($priceTo > 0) ? $priceTo : '')
		);
	}


	///////////////////////////////////////////////////////
	// Functions for retrieving Data for the option boxes
	///////////////////////////////////////////////////////

	/**
	 * Gets the data for the eventy type search field options.
	 *
	 * @return array the data for the event type search field options, the key
	 *               will be the UID of the event type and the value will be the
	 *               title of the event type, will be empty if no data could be
	 *               found
	 */
	private function getEventTypeData() {
		$result = array();

		foreach ($this->seminarBag as $event) {
			$eventTypeUid = $event->getEventTypeUid();
			if ($eventTypeUid != 0) {
				$eventTypeName = $event->getEventType();
				if (!isset($result[$eventTypeUid])) {
					$result[$eventTypeUid] = $eventTypeName;
				}
			}
		}

		return $result;
	}

	/**
	 * Gets the data for the language search field options.
	 *
	 * @return array the data for the language search field options, the key
	 *               will be the ISO code of the language and the value will be
	 *               the localized title of the language, will be empty if no
	 *               data could be found
	 */
	private function getLanguageData() {
		$result = array();

		foreach ($this->seminarBag as $event) {
			if ($event->hasLanguage()) {
				// Reads the language from the event record.
				$languageIsoCode = $event->getLanguage();
				if ((!empty($languageIsoCode))
					&& !isset($result[$languageIsoCode])) {
					$languageName = $this->staticInfo->getStaticInfoName(
						'LANGUAGES',
						$languageIsoCode,
						'',
						'',
						0
					);
					$result[$languageIsoCode] = $languageName;
				}
			}
		}

		return $result;
	}

	/**
	 * Gets the data for the place search field options.
	 *
	 * @return array the data for the country search field options; the key
	 *               will be the UID of the place and the value will be the
	 *               title of the place, will be empty if no data could be found
	 */
	private function getPlaceData() {
		if ($this->seminarBag->isEmpty()) {
			return array();
		}

		$result = array();
		$this->createPlaceBag();

		foreach ($this->placeBag as $place) {
			$result[$place->getUid()] = $place->getTitle();
		}

		return $result;
	}

	/**
	 * Gets the data for the city search field options.
	 *
	 * @return array the data for the city search field options; the key and the
	 *               value will be the name of the city, will be empty if no
	 *               data could be found
	 */
	private function getCityData() {
		if ($this->seminarBag->isEmpty()) {
			return array();
		}

		$result = array();
		$this->createPlaceBag();

		foreach ($this->placeBag as $place) {
			$result[$place->getCity()] = $place->getCity();
		}

		return $result;
	}

	/**
	 * Gets the data for the country search field options.
	 *
	 * @return array the data for the country search field options; the key will
	 *               be the ISO-Alpha-2 code of the country the value will be
	 *               the name of the country, will be empty if no data could be
	 *               found
	 */
	private function getCountryData() {
		if ($this->seminarBag->isEmpty()) {
			return array();
		}

		$result = array();
		$this->createPlaceBag();

		foreach ($this->placeBag as $place) {
			$countryIsoCode = $place->getCountryIsoCode();

			if (($countryIsoCode != '0') && ($countryIsoCode != '')) {
				if (!isset($result[$countryIsoCode])) {
					$result[$countryIsoCode]
						= $this->staticInfo->getStaticInfoName(
							'COUNTRIES', $countryIsoCode
						);
				}
			}
		}

		return $result;
	}

	/**
	 * Compiles the possible values for date selector.
	 *
	 * @return array multi-dimensional array; the first level contains day,
	 *         month and year as key, the second level has the day, month or
	 *         year value as value and key, will not be empty
	 */
	private function createDateArray() {
		$result = array(
			'day' => array(),
			'month' => array(),
			'year' => array(),
		);

		for ($day = 1; $day <= 31; $day++) {
			$result['day'][$day] = $day;
		}

		for ($month = 1; $month <= 12; $month++) {
			$result['month'][$month] = $month;
		}

		$currentYear = intval(date('Y'));
		$targetYear = $currentYear + $this->getConfValueInteger(
			'numberOfYearsInDateFilter', 's_listView'
		);

		for ($year = $currentYear; $year < $targetYear; $year++) {
			$result['year'][$year] = $year;
		}

		return $result;
	}

	/**
	 * Gets the data for the organizer search field options.
	 *
	 * @return array the data for the organizer search field options; the key
	 *               will be the UID of the organizer and the value will be the
	 *               name of the organizer, will be empty if no data could be
	 *               found
	 */
	private function getOrganizerData() {
		$result = array();

		foreach ($this->seminarBag as $event) {
			if ($event->hasOrganizers()) {
				$organizers = $event->getOrganizerBag();
				foreach ($organizers as $organizer) {
					$organizerUid = $organizer->getUid();
					if (!isset($result[$organizerUid])) {
						$result[$organizerUid] = $organizer->getName();
					}
				}
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php']);
}
?>