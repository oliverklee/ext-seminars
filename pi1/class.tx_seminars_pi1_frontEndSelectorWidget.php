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
	 * @var array all languages that may be shown in the option box of the
	 *            selector widget
	 */
	private $allLanguages = array();

	/**
	 * @var array all countries that may be shown in the option box of the
	 *            selector widget
	 */
	private $allCountries = array();

	/**
	 * @var array all places that may be shown in the option box of the
	 *            selector widget
	 */
	private $allPlaces = array();

	/**
	 * @var array all cities that may be shown in the option box of the
	 *            selector widget
	 */
	private $allCities = array();

	/**
	 * @var array all event types
	 */
	private $allEventTypes = array();

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
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		if ($this->seminarBag) {
			$this->seminarBag->__destruct();
			unset($this->seminarBag);
		}
		unset($this->staticInfo);

		parent::__destruct();
	}

	/**
	 * Returns the selector widget if it is not hidden through the TypoScript
	 * setup configuration option "hideSelectorWidget".
	 *
	 * @return string the HTML code of the selector widget, may be empty
	 */
	public function render() {
		if ($this->getConfValueBoolean(
			'hideSelectorWidget', 's_template_special'
		)) {
			return '';
		}

		$this->initialize();
		$this->fillOrHideSearchSubpart('event_type');
		$this->fillOrHideSearchSubpart('language');
		$this->fillOrHideSearchSubpart('place');

		$this->createAllowedValuesForSelectorWidget();

		return $this->createSelectorWidget();
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
		$this->seminarBag = t3lib_div::makeInstance('tx_seminars_seminarbag');
	}

	/**
	 * Gathers all the allowed entries for the option boxes of the selector
	 * widget. This includes the languages, places, countries and event types of
	 * the events that are selected and in the seminar bag for the current list
	 * view.
	 *
	 * IMPORTANT: The lists for each option box contain only the values that
	 * are coming from the selected events! So there's not a huge list of
	 * languages of which 99% are not selected for any event (and thus would
	 * result in no found events).
	 *
	 * The data will be written to global variables as arrays that contain the
	 * value (value of the form field) and the label (text shown in the option
	 * box) for each entry.
	 */
	private function createAllowedValuesForSelectorWidget() {
		$allPlaceUids = array();

		// Creates a separate seminar bag that contains all the events.
		// We can't use the regular seminar bag that is used for the list
		// view as it contains only part of the events.
		$seminarBag = t3lib_div::makeInstance('tx_seminars_seminarbag');

		// Walks through all events in the seminar bag to read the needed data
		// from each event object.
		foreach ($seminarBag as $event) {
			// Reads the place(s) from the event record. The country will be
			// read from the place record later.
			$placeUids = $event->getRelatedMmRecordUids(
				SEMINARS_TABLE_SEMINARS_SITES_MM
			);
			$allPlaceUids = array_merge($allPlaceUids, $placeUids);
		}
		$seminarBag->__destruct();
		unset($seminarBag);

		// Fetches the name of the location, the city and the country and adds
		// it to the final array.
		if (empty($allPlaceUids)) {
			$allPlaceUids = array(0);
		}

		foreach ($this->createPlaceBag($allPlaceUids) as $uid => $place) {
			if (!isset($this->allPlaces[$uid])) {
				$this->allPlaces[$uid] = $place->getTitle();
			}
			$countryIsoCode = $place->getCountryIsoCode();
			if (!isset($this->allCountries[$countryIsoCode])) {
				$this->allCountries[$countryIsoCode]
					= $this->staticInfo->getStaticInfoName(
						'COUNTRIES', $countryIsoCode
					);
			}

			$cityName = $place->getCity();
			if (!isset($this->allCities[$cityName])) {
				$this->allCities[$cityName] = $cityName;
			}
		}

		// Brings the options into alphabetical order.
		asort($this->allPlaces);
		asort($this->allCities);
		asort($this->allCountries);

		// Adds an empty option to each list of options if this is needed.
		$this->addEmptyOptionIfNeeded($this->allPlaces);
		$this->addEmptyOptionIfNeeded($this->allCities);
		$this->addEmptyOptionIfNeeded($this->allCountries);
	}

	/**
	 * Adds a dummy option to the array of allowed values. This is needed if the
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
	 * Creates the selector widget HTML that is shown on the list view.
	 *
	 * The selector widget is a form on which the user can set filter criteria
	 * that should apply to the list view of events. There is a text field for
	 * a text search. And there are multiple option boxes that contain the allowed
	 * values for e.g. the field "language".
	 *
	 * @return string the HTML source for the selector widget
	 */
	private function createSelectorWidget() {
		// Shows or hides the text search field.
		if (!$this->getConfValueBoolean('hideSearchForm', 's_template_special')) {
			// Sets the previous search string into the text search box.
			$this->setMarker(
				'searchbox_value', htmlspecialchars($this->piVars['sword'])
			);
		} else {
			$this->hideSubparts('wrapper_searchbox');
		}

		// Defines the list of option boxes that should be shown in the form.
		$allOptionBoxes = array('country', 'city');

		// Renders each option box.
		foreach ($allOptionBoxes as $currentOptionBox) {
			$this->createOptionBox($currentOptionBox);
		}

		return $this->getSubpart('SELECTOR_WIDGET');
	}

	/**
	 * Creates the HTML code for a single option box of the selector widget.
	 *
	 * The selector widget contains multiple option boxes. Each of them contains
	 * a list of options for a certain sort of records. The option box for the
	 * field "language" could contain the entries "English" and "German".
	 *
	 * @param string the name of the option box to generate, must not contain
	 *               spaces and there must be a localized label "label_xyz"
	 *               with this name, may not be empty
	 */
	private function createOptionBox($optionBoxName) {
		// Sets the header that is shown in the label of this selector box.
		$this->setMarker(
			'options_header', $this->translate('label_' . $optionBoxName)
		);

		// Sets the name of this option box in the HTML source. This is needed
		// to separate the different option boxes for further form processing.
		// The additional pair of brackets is needed as we need to submit multiple
		// values per field.
		$this->setMarker(
			'optionbox_name', $this->prefixId . '[' . $optionBoxName . '][]'
		);

		$this->setMarker(
			'optionbox_id', $this->prefixId . '-' . $optionBoxName
		);

		// Fetches the possible entries for the current option box and renders
		// them as HTML <option> entries for the <select> field.
		$optionsList = '';
		switch ($optionBoxName) {
			case 'event_type':
				$availableOptions = $this->allEventTypes;
				break;
			case 'language':
				$availableOptions = $this->allLanguages;
				break;
			case 'country':
				$availableOptions = $this->allCountries;
				break;
			case 'city':
				$availableOptions = $this->allCities;
				break;
			case 'place':
				$availableOptions = $this->allPlaces;
				break;
			default:
				$availableOptions = array();
				break;
		}
		foreach ($availableOptions as $currentValue => $currentLabel) {
			$this->setMarker('option_label', $currentLabel);
			$this->setMarker('option_value', $currentValue);

			// Preselects the option if it was selected by the user.
			if (isset($this->piVars[$optionBoxName])
				&& ($currentValue != 'none')
				&& (in_array($currentValue, $this->piVars[$optionBoxName]))
			) {
				$isSelected = ' selected="1"';
			} else {
				$isSelected = '';
			}
			$this->setMarker('option_selected', $isSelected);

			$optionsList .= $this->getSubpart('OPTIONS_ENTRY');
		}
		$this->setMarker('options', $optionsList);
		$this->setMarker(
			'options_' . $optionBoxName, $this->getSubpart('OPTIONS_BOX')
		);
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
	private function createSingleOptionBox($name, array $options) {
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
	 * Returns a place bag object that contains all seminar places that are in
	 * the list of given UIDs.
	 *
	 * @param array all the UIDs to include in the bag, must not be empty
	 *
	 * @return tx_seminars_placebag place bag object
	 */
	private function createPlaceBag(array $placeUids) {
		$placeUidsAsCommaSeparatedList = implode(',', $placeUids);
		$queryWhere = 'uid IN(' . $placeUidsAsCommaSeparatedList . ')';
		$placeBagClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_placebag'
		);
		$placeBag = new $placeBagClassname($queryWhere);

		return $placeBag;
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
	 * Fills or hides the subpart for the given search field.
	 *
	 * @param string the key of the search field, must be one of the following:
	 *               "event_type", "language", "country", "city", "places"
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
			default:
				throw new Exception('The given search field .
					"' . $searchField . '" was not an allowed value. ' .
					'Allowed values are: "event_type", "language", "country", ' .
					'"city" or "places".'
				);
				break;
		}

		$optionBox = $this->createSingleOptionBox($searchField, $optionData);

		$this->setMarker('options_' . $searchField, $optionBox);
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

		$whereClause = SEMINARS_TABLE_SITES . '.uid = uid_foreign AND ' .
			'uid_local IN (' . $this->seminarBag->getUids() . ')';


		$placeBagClassname = t3lib_div::makeInstanceClassName(
			'tx_seminars_placebag'
		);
		$placeBag = new $placeBagClassname(
			$whereClause, SEMINARS_TABLE_SEMINARS_SITES_MM
		);

		foreach ($placeBag as $place) {
			$result[$place->getUid()] = $place->getTitle();
		}

		$placeBag->__destruct();

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndSelectorWidget.php']);
}
?>