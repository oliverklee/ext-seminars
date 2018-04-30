<?php

use SJBR\StaticInfoTables\PiBaseApi;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a selector widget.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Mario Rimann <typo3-coding@rimann.org>
 */
class Tx_Seminars_FrontEnd_SelectorWidget extends \Tx_Seminars_FrontEnd_AbstractView
{
    /**
     * needed for the list view to convert ISO codes to country names and languages
     *
     * @var PiBaseApi
     */
    protected $staticInfo = null;

    /**
     * @var string[] the keys of the search fields which should be displayed in the search form
     */
    private $displayedSearchFields = [];

    /**
     * @var string the prefix of every subpart of the search widget
     */
    const SUBPART_PREFIX = 'SEARCH_PART_';

    /**
     * @var \Tx_Seminars_Bag_Event all seminars to show in the list view
     */
    private $seminarBag = null;

    /**
     * @var \Tx_Oelib_List all places which are assigned to at least one event
     */
    private $places = null;

    /**
     * Returns the selector widget if it is not hidden.
     *
     * The selector widget will automatically be hidden, if no search option is
     * selected to be displayed.
     *
     * @return string the HTML code of the selector widget, may be empty
     */
    public function render()
    {
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
        $this->fillOrHideSearchSubpart('categories');
        $this->fillOrHideFullTextSearch();
        $this->fillOrHideDateSearch();
        $this->fillOrHideAgeSearch();
        $this->fillOrHidePriceSearch();

        return $this->getSubpart('SELECTOR_WIDGET');
    }

    /**
     * Initializes some variables needed for further processing.
     *
     * @return void
     */
    private function initialize()
    {
        $this->displayedSearchFields = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString(
                'displaySearchFormFields',
                's_listView'
            ),
            true
        );

        $this->instantiateStaticInfo();
        /** @var \Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Event::class);
        $builder->limitToEventTypes(
            GeneralUtility::trimExplode(',', $this->getConfValueString('limitListViewToEventTypes', 's_listView'), true)
        );
        $builder->limitToOrganizers($this->getConfValueString('limitListViewToOrganizers', 's_listView'));
        $builder->limitToCategories($this->getConfValueString('limitListViewToCategories', 's_listView'));

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
     * @param string[] &$options options, may be empty
     *
     * @return void
     */
    private function addEmptyOptionIfNeeded(array &$options)
    {
        if (!$this->getConfValueBoolean(
            'showEmptyEntryInOptionLists',
            's_template_special'
        )) {
            return;
        }

        $options = [
            0 => $this->translate('label_selector_pleaseChoose'),
        ] + $options;
    }

    /**
     * Removes the dummy option from the submitted form data.
     *
     * @param array $formData the POST data submitted from the form, may be empty
     *
     * @return array the POST data without the dummy option
     */
    public static function removeDummyOptionFromFormData(array $formData)
    {
        $cleanedFormData = [];

        foreach ($formData as $value) {
            if ($value !== 0) {
                $cleanedFormData[] = $value;
            }
        }

        return $cleanedFormData;
    }

    /**
     * Creates the HTML code for a single option box of the selector widget.
     *
     * @param string $name
     *        the name of the option box to generate, must be one of the following:
     *        'event_type', 'language', 'country', 'city', 'places'
     * @param string[] $options
     *        the options for the option box with the option value as key and the option label as value, may be empty
     *
     * @return string the HTML content for the select, will not be empty
     */
    private function createOptionBox($name, array $options)
    {
        $this->setMarker('options_header', $this->translate('label_' . $name));
        $this->setMarker(
            'optionbox_name',
            $this->prefixId . '[' . $name . '][]'
        );
        $this->setMarker('optionbox_id', $this->prefixId . '-' . $name);

        $optionsList = '';
        foreach ($options as $key => $label) {
            $this->setMarker('option_label', htmlspecialchars($label));
            $this->setMarker('option_value', $key);

            // Preselects the option if it was selected by the user.
            if (isset($this->piVars[$name])
                && in_array((string)$key, $this->piVars[$name], true)
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
     * Creates a list of places with all places that are assigned to at least
     * one event.
     *
     * The list of places is stored in the member variable $this->places.
     *
     * Before this function is called, it must be assured that the seminar bag
     * is not empty.
     *
     * @return void
     */
    private function collectPlaces()
    {
        if ($this->seminarBag->isEmpty()) {
            throw new \BadMethodCallException('The seminar bag must not be empty when calling this function.', 1333293276);
        }
        if ($this->places) {
            return;
        }

        $dataOfPlaces = \Tx_Oelib_Db::selectMultiple(
            'tx_seminars_sites.*',
            'tx_seminars_sites, tx_seminars_seminars_place_mm',
            'tx_seminars_sites.uid = tx_seminars_seminars_place_mm.uid_foreign ' .
                'AND tx_seminars_seminars_place_mm.uid_local IN (' .
                $this->seminarBag->getUids() . ')'
        );

        /** @var \Tx_Seminars_Mapper_Place $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Place::class);
        $this->places = $mapper->getListOfModels($dataOfPlaces);
    }

    /**
     * Creates an instance of PiBaseApi if that has not happened yet.
     *
     * @return void
     */
    protected function instantiateStaticInfo()
    {
        if ($this->staticInfo !== null) {
            return;
        }

        $this->staticInfo = GeneralUtility::makeInstance(PiBaseApi::class);
        $this->staticInfo->init();
    }

    /**
     * Checks whether a given search field key should be displayed.
     *
     * @param string $fieldToCheck the search field name to check, must not be empty
     *
     * @return bool TRUE if the given field should be displayed as per
     *                 configuration, FALSE otherwise
     */
    protected function hasSearchField($fieldToCheck)
    {
        return in_array($fieldToCheck, $this->displayedSearchFields);
    }

    /**
     * Creates a drop-down, including an empty option at the top.
     *
     * @param string[] $options
     *        the options for the drop-down, the keys will be used as values and the array values as labels for the options,
     *        may be empty
     * @param string $name
     *        the HTML name of the drop-down, must be not empty and must be unique
     *
     * @return string the generated HTML, will not be empty
     */
    private function createDropDown($options, $name)
    {
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
     * @param string $searchField
     *        the key of the search field, must be one of the following:
     *        "event_type", "language", "country", "city", "places", "organizer"
     *
     * @return void
     */
    private function fillOrHideSearchSubpart($searchField)
    {
        if (!$this->hasSearchField($searchField)) {
            $this->hideSubparts(self::SUBPART_PREFIX . strtoupper($searchField));
            return;
        }

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
            case 'categories':
                $optionData = $this->getCategoryData();
                break;
            default:
                throw new \InvalidArgumentException(
                    'The given search field . "' . $searchField . '" was not an allowed value. ' .
                        'Allowed values are: "event_type", "language", "country", "city", "place" or "organizer".',
                    1333293298
                );
        }

        asort($optionData, SORT_STRING);
        $this->addEmptyOptionIfNeeded($optionData);
        $optionBox = $this->createOptionBox($searchField, $optionData);

        $this->setMarker('options_' . $searchField, $optionBox);
    }

    /**
     * Fills or hides the full text search subpart.
     *
     * @return void
     */
    private function fillOrHideFullTextSearch()
    {
        if (!$this->hasSearchField('full_text_search')) {
            $this->hideSubparts(self::SUBPART_PREFIX . 'TEXT');

            return;
        }

        $this->setMarker(
            'searchbox_value',
            htmlspecialchars($this->piVars['sword'])
        );
    }

    /**
     * Fills or hides the date search subpart.
     *
     * @return void
     */
    private function fillOrHideDateSearch()
    {
        if (!$this->hasSearchField('date')) {
            $this->hideSubparts(
                self::SUBPART_PREFIX . 'DATE'
            );

            return;
        }

        $dateArrays = $this->createDateArray();

        foreach (['from', 'to'] as $fromOrTo) {
            $dropDowns = '';
            foreach ($dateArrays as $dropDownPart => $dateArray) {
                $dropDowns .= $this->createDropDown(
                    $dateArray,
                    $fromOrTo . '_' . $dropDownPart
                );
            }
            $this->setMarker('options_date_' . $fromOrTo, $dropDowns);
        }
    }

    /**
     * Fills or hides the age search subpart.
     *
     * @return void
     */
    private function fillOrHideAgeSearch()
    {
        if (!$this->hasSearchField('age')) {
            $this->hideSubparts(
                self::SUBPART_PREFIX . 'AGE'
            );

            return;
        }
        $age = (int)$this->piVars['age'];

        $this->setMarker(
            'age_value',
            (($age > 0) ? $age : '')
        );
    }

    /**
     * Fills or hides the price search subpart.
     *
     * @return void
     */
    private function fillOrHidePriceSearch()
    {
        if (!$this->hasSearchField('price')) {
            $this->hideSubparts(
                self::SUBPART_PREFIX . 'PRICE'
            );

            return;
        }

        $priceFrom = (int)$this->piVars['price_from'];
        $priceTo = (int)$this->piVars['price_to'];

        $this->setMarker(
            'price_from_value',
            (($priceFrom > 0) ? $priceFrom : '')
        );
        $this->setMarker(
            'price_to_value',
            (($priceTo > 0) ? $priceTo : '')
        );
    }

    ///////////////////////////////////////////////////////
    // Functions for retrieving Data for the option boxes
    ///////////////////////////////////////////////////////

    /**
     * Gets the data for the eventy type search field options.
     *
     * @return string[] the data for the event type search field options, the key
     *               will be the UID of the event type and the value will be the
     *               title of the event type, will be empty if no data could be
     *               found
     */
    protected function getEventTypeData()
    {
        $result = [];

        /** @var \Tx_Seminars_OldModel_Event $event */
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
     * @return string[] the data for the language search field options, the key
     *               will be the ISO code of the language and the value will be
     *               the localized title of the language, will be empty if no
     *               data could be found
     */
    protected function getLanguageData()
    {
        $result = [];

        /** @var \Tx_Seminars_OldModel_Event $event */
        foreach ($this->seminarBag as $event) {
            if ($event->hasLanguage()) {
                // Reads the language from the event record.
                $languageIsoCode = $event->getLanguage();
                if (!empty($languageIsoCode)
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
     * @return string[] the data for the country search field options; the key
     *               will be the UID of the place and the value will be the
     *               title of the place, will be empty if no data could be found
     */
    protected function getPlaceData()
    {
        if ($this->seminarBag->isEmpty()) {
            return [];
        }

        $result = [];
        $this->collectPlaces();

        /** @var \Tx_Seminars_Model_Place $place */
        foreach ($this->places as $place) {
            $result[$place->getUid()] = $place->getTitle();
        }

        return $result;
    }

    /**
     * Gets the data for the city search field options.
     *
     * @return string[] the data for the city search field options; the key and the
     *               value will be the name of the city, will be empty if no
     *               data could be found
     */
    protected function getCityData()
    {
        if ($this->seminarBag->isEmpty()) {
            return [];
        }

        $result = [];
        $this->collectPlaces();

        /** @var \Tx_Seminars_Model_Place $place */
        foreach ($this->places as $place) {
            $city = $place->getCity();
            $result[$city] = $city;
        }

        return $result;
    }

    /**
     * Gets the data for the country search field options.
     *
     * @return string[] the data for the country search field options; the key will
     *               be the ISO-Alpha-2 code of the country the value will be
     *               the name of the country, will be empty if no data could be
     *               found
     */
    protected function getCountryData()
    {
        if ($this->seminarBag->isEmpty()) {
            return [];
        }

        /** @var string[] $result */
        $result = [];
        $this->collectPlaces();

        /** @var \Tx_Seminars_Model_Place $place */
        foreach ($this->places as $place) {
            if ($place->hasCountry()) {
                $countryIsoCode = $place->getCountry()->getIsoAlpha2Code();

                if (!isset($result[$countryIsoCode])) {
                    $result[$countryIsoCode] = $this->staticInfo->getStaticInfoName('COUNTRIES', $countryIsoCode);
                }
            }
        }

        return $result;
    }

    /**
     * Compiles the possible values for date selector.
     *
     * @return array[] the first level contains day,
     *         month and year as key, the second level has the day, month or
     *         year value as value and key, will not be empty
     */
    private function createDateArray()
    {
        $result = [
            'day' => [],
            'month' => [],
            'year' => [],
        ];

        for ($day = 1; $day <= 31; $day++) {
            $result['day'][$day] = $day;
        }

        for ($month = 1; $month <= 12; $month++) {
            $result['month'][$month] = $month;
        }

        $currentYear = (int)date('Y');
        $targetYear = $currentYear + $this->getConfValueInteger(
            'numberOfYearsInDateFilter',
            's_listView'
        );

        for ($year = $currentYear; $year < $targetYear; $year++) {
            $result['year'][$year] = $year;
        }

        return $result;
    }

    /**
     * Gets the data for the organizer search field options.
     *
     * @return string[] the data for the organizer search field options; the key
     *               will be the UID of the organizer and the value will be the
     *               name of the organizer, will be empty if no data could be
     *               found
     */
    private function getOrganizerData()
    {
        $result = [];

        /** @var \Tx_Seminars_OldModel_Event $event */
        foreach ($this->seminarBag as $event) {
            if ($event->hasOrganizers()) {
                $organizers = $event->getOrganizerBag();
                /** @var \Tx_Seminars_OldModel_Organizer $organizer */
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

    /**
     * Gets the data for the category search field options.
     *
     * @return string[] the data for the category search field options; the key
     *               will be the UID of the category and the value will be the
     *               name of the category, will be empty if no data could be
     *               found
     */
    private function getCategoryData()
    {
        $result = [];

        /** @var \Tx_Seminars_OldModel_Event $event */
        foreach ($this->seminarBag as $event) {
            if ($event->hasCategories()) {
                $categories = $event->getCategories();
                foreach ($categories as $uid => $category) {
                    if (!isset($result[$uid])) {
                        $result[$uid] = $category['title'];
                    }
                }
            }
        }

        return $result;
    }
}
