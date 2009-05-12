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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

require_once(t3lib_extMgm::extPath('static_info_tables') . 'pi1/class.tx_staticinfotables_pi1.php');

/**
 * Testcase for the 'frontEndSelectorWidget' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_frontEndSelectorWidget_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_pi1_frontEndSelectorWidget
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_staticinfotables_pi1 needed to convert ISO codes to country and
	 *                              language names
	 */
	protected $staticInfo;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->fixture = new tx_seminars_pi1_frontEndSelectorWidget(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
			),
			$GLOBALS['TSFE']->cObj
		);
	}

	public function tearDown() {
		if ($this->staticInfo) {
			unset($this->staticInfo);
		}

		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();

		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Creates and initializes an instance of tx_staticinfotables_pi1 in
	 * $this->staticInfo.
	 */
	private function instantiateStaticInfo() {
		$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
		$this->staticInfo->init();
	}


	////////////////////////////////////
	// Tests for the utility functions
	////////////////////////////////////

	public function testInstantiateStaticInfoCreateStaticInfoInstance() {
		$this->instantiateStaticInfo();

		$this->assertTrue(
			$this->staticInfo instanceof tx_staticinfotables_pi1
		);
	}


	//////////////////////////////////////////
	// General tests concerning the fixture.
	//////////////////////////////////////////

	public function testFixtureIsAFrontEndSelectorWidgetObject() {
		$this->assertTrue(
			$this->fixture instanceof tx_seminars_pi1_frontEndSelectorWidget
		);
	}


	///////////////////////
	// Tests for render()
	///////////////////////

	public function testRenderWithSelectorWidgetHiddenThroughTypoScriptReturnsEmptyString() {
		$this->fixture->setConfigurationValue('hideSelectorWidget', true);

		$this->assertEquals(
			'',
			$this->fixture->render()
		);
	}

	public function testRenderCanContainSearchWord() {
		$searchWord = 'foo bar';
		$this->fixture->piVars['sword'] = $searchWord;

		$this->assertContains(
			$searchWord,
			$this->fixture->render()
		);
	}

	public function testRenderWithSearchBoxHiddenThroughTypoScriptDoesNotContainSearchWord() {
		$this->fixture->setConfigurationValue('hideSearchForm', true);
		$searchWord = 'foo bar';
		$this->fixture->piVars['sword'] = $searchWord;

		$this->assertNotContains(
			$searchWord,
			$this->fixture->render()
		);
	}

	public function testRenderContainsSearchingHints() {
		$this->assertContains(
			$this->fixture->translate('label_searching_hints'),
			$this->fixture->render()
		);
	}

	public function testRenderContainsSearchForm() {
		$this->assertContains(
			$this->fixture->translate('label_selector_searchbox') .
				': <input type="text" id="tx_seminars_pi1_sword" ' .
				'name="tx_seminars_pi1[sword]" value="" />',
			$this->fixture->render()
		);
	}

	public function testRenderContainsSubmitButton() {
		$this->assertContains(
			'<input type="submit" value="' .
				$this->fixture->translate('label_selector_submit') . '" />',
			$this->fixture->render()
		);
	}

	public function testRenderContainsResetButton() {
		$this->assertContains(
			'<input type="submit" value="' .
				$this->fixture->translate('label_selector_reset') . '"',
			$this->fixture->render()
		);
	}

	public function testRenderContainsLabelForCountriesSelector() {
		$this->assertContains(
			'<label for="tx_seminars_pi1-country">' .
				$this->fixture->translate('label_country') . '</label>',
			$this->fixture->render()
		);
	}

	public function testRenderContainsSelectorForCountries() {
		$this->assertContains(
			'<select name="tx_seminars_pi1[country][]" ' .
				'id="tx_seminars_pi1-country" size="5" multiple="multiple">',
			$this->fixture->render()
		);
	}

	public function testRenderContainsLabelForCitiesSelector() {
		$this->assertContains(
			'<label for="tx_seminars_pi1-city">' .
				$this->fixture->translate('label_city') . '</label>',
			$this->fixture->render()
		);
	}

	public function testRenderContainsSelectorForCities() {
		$this->assertContains(
			'<select name="tx_seminars_pi1[city][]" ' .
				'id="tx_seminars_pi1-city" size="5" multiple="multiple">',
			$this->fixture->render()
		);
	}

	public function testRenderCanContainEmptyOption() {
		$this->fixture->setConfigurationValue(
			'showEmptyEntryInOptionLists', true
		);

		$this->assertContains(
			'<option value="none">' .
				$this->fixture->translate('label_selector_pleaseChoose') .
				'</option>',
			$this->fixture->render()
		);
	}

	public function testRenderContainsCountryOption() {
		$this->instantiateStaticInfo();

		$countryIsoCode = 'DE';
		$countryName = $this->staticInfo->getStaticInfoName(
			'COUNTRIES', $countryIsoCode
		);
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('country' => $countryIsoCode)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM, $eventUid, $placeUid
		);

		$this->assertContains(
			'<option value="' . $countryIsoCode . '">' . $countryName .
				'</option>',
			$this->fixture->render()
		);
	}

	public function testRenderCanContainCityOption() {
		$cityName = 'test city';
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => $cityName)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('place' => 1)
		);
		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SEMINARS_SITES_MM, $eventUid, $placeUid
		);

		$this->assertContains(
			'<option value="' . $cityName . '">' . $cityName . '</option>',
			$this->fixture->render()
		);
	}


	/////////////////////////////////////////////
	// Test for removeDummyOptionFromFormData()
	/////////////////////////////////////////////

	public function testRemoveDummyOptionFromFormDataRemovesDummyOptionAtBeginningOfArray() {
		$this->assertEquals(
			array('CH', 'DE'),
			tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
				array('none', 'CH', 'DE')
			)
		);
	}

	public function testRemoveDummyOptionFromFormDataRemovesDummyOptionInMiddleOfArray() {
		$this->assertEquals(
			array('CH', 'DE'),
			tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
				array('CH', 'none', 'DE')
			)
		);
	}

	public function testRemoveDummyOptionFromFormDataWithEmptyFormDataReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			tx_seminars_pi1_frontEndSelectorWidget::removeDummyOptionFromFormData(
				array()
			)
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests concerning the rendering of the event_type option box
	////////////////////////////////////////////////////////////////

	public function test_Render_ForEventTypeHiddenInConfiguration_HidesEventTypeSubpart() {
		$this->fixture->setConfigurationValue('displaySearchFormFields', '');

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_EVENT_TYPE')
		);
	}

	public function test_Render_ForEnabledEventType_ContainsLabelForEventTypeSelector() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$this->assertContains(
			'<label for="tx_seminars_pi1-event_type">' .
				$this->fixture->translate('label_event_type') . '</label>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledEventType_CanContainEventTypeOption() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$eventTypeTitle = 'test event type';
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES, array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('event_type' => $eventTypeUid)
		);

		$this->assertContains(
			'<option value="' . $eventTypeUid . '">' . $eventTypeTitle .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledEventType_HtmlSpecialCharsTheEventTypeTitle() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$eventTypeTitle = '< Test >';
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES, array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('event_type' => $eventTypeUid)
		);

		$this->assertContains(
			'<option value="' . $eventTypeUid . '">' .
				htmlspecialchars($eventTypeTitle) .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledEventType_PreselectsSelectedValue() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$eventTypeTitle = 'test event type';
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES, array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('event_type' => $eventTypeUid)
		);

		$this->fixture->piVars['event_type'][] = $eventTypeUid;

		$this->assertContains(
			$eventTypeUid . '" selected="selected">' . $eventTypeTitle .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledEventType_CanPreselectTwoValues() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$eventTypeTitle = 'foo';
		$eventTypeTitle2 = 'bar';
		$eventTypeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES, array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('event_type' => $eventTypeUid)
		);

		$eventTypeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_EVENT_TYPES, array('title' => $eventTypeTitle2)
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('event_type' => $eventTypeUid2)
		);

		$this->fixture->piVars['event_type'][] = $eventTypeUid;
		$this->fixture->piVars['event_type'][] = $eventTypeUid2;

		$output = $this->fixture->render();

		$this->assertContains(
			$eventTypeUid . '" selected="selected">' . $eventTypeTitle .
				'</option>',
			$output
		);
		$this->assertContains(
			$eventTypeUid2 . '" selected="selected">' . $eventTypeTitle2 .
				'</option>',
			$output
		);
	}

	public function test_Render_ForEnabledEventType_ContainsSelectorForEventTypes() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[event_type][]" ' .
				'id="tx_seminars_pi1-event_type" size="5" multiple="multiple">',
			$this->fixture->render()
		);
	}


	//////////////////////////////////////////////////////////////
	// Tests concerning the rendering of the language option box
	//////////////////////////////////////////////////////////////

	public function test_Render_ForLanguageOptionsHiddenInConfiguration_HidesLanguageSubpart() {
		$this->fixture->setConfigurationValue('displaySearchFormFields', '');

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_LANGUAGE')
		);
	}

	public function test_Render_ForEnabledLanguageOptions_ContainsLanguageOption() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'language'
		);

		$this->instantiateStaticInfo();

		$languageIsoCode = 'DE';
		$languageName = $this->staticInfo->getStaticInfoName(
			'LANGUAGES', $languageIsoCode, '', '', 0
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('language' => $languageIsoCode)
		);

		$this->assertContains(
			'<option value="' . $languageIsoCode . '">' . $languageName .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledLanguageOptions_ContainsLanguagesSelectorLabel() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'language'
		);

		$this->assertContains(
			'<label for="tx_seminars_pi1-language">' .
				$this->fixture->translate('label_language') . '</label>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledLanguageOptions_ContainsSelectorForLanguages() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'language'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[language][]" ' .
				'id="tx_seminars_pi1-language" size="5" multiple="multiple">',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledLanguageOptions_CanPreselectSelectedLanguage() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'language'
		);

		$this->instantiateStaticInfo();

		$languageIsoCode = 'DE';
		$languageName = $this->staticInfo->getStaticInfoName(
			'LANGUAGES', $languageIsoCode, '', '', 0
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('language' => $languageIsoCode)
		);

		$this->fixture->piVars['language'][] = $languageIsoCode;

		$this->assertContains(
			$languageIsoCode . '" selected="selected">' . $languageName .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledLanguageOptions_CanPreselectMultipleLanguages() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'language'
		);
		$this->instantiateStaticInfo();

		$languageIsoCode = 'DE';
		$languageName = $this->staticInfo->getStaticInfoName(
			'LANGUAGES', $languageIsoCode, '', '', 0
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('language' => $languageIsoCode)
		);

		$languageIsoCode2 = 'EN';
		$languageName2 = $this->staticInfo->getStaticInfoName(
			'LANGUAGES', $languageIsoCode2, '', '', 0
		);
		$this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS, array('language' => $languageIsoCode2)
		);

		$this->fixture->piVars['language'][] = $languageIsoCode;
		$this->fixture->piVars['language'][] = $languageIsoCode2;

		$output = $this->fixture->render();

		$this->assertContains(
			$languageIsoCode . '" selected="selected">' . $languageName .
				'</option>',
			$output
		);
		$this->assertContains(
			$languageIsoCode2 . '" selected="selected">' . $languageName2 .
				'</option>',
			$output
		);
	}


	///////////////////////////////////////////////////////////
	// Tests concerning the rendering of the place option box
	///////////////////////////////////////////////////////////

	public function test_Render_ForEnabledPlaceOptions_ContainsLabelOfPlacesSelector() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'place'
		);

		$this->assertContains(
			'<label for="tx_seminars_pi1-place">' .
				$this->fixture->translate('label_place') . '</label>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForDisabledPlaceOptions_HidesPlaceSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', ''
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_PLACE')
		);
	}

	public function test_Render_ForEnabledPlaceOptions_ContainsPlaceOptions() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'place'
		);
		$placeTitle = 'test place';
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => $placeTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);

		$this->assertContains(
			'<option value="' . $placeUid . '">' . $placeTitle . '</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPlaceOptions_HtmlSpecialCharsThePlaceTitle() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'place'
		);
		$placeTitle = '<>';
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => $placeTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);

		$this->assertContains(
			'<option value="' . $placeUid . '">' .
				htmlspecialchars($placeTitle) . '</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPlaceOptions_ContainsSelectorForPlaces() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'place'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[place][]" ' .
				'id="tx_seminars_pi1-place" size="5" multiple="multiple">',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPlaceOptions_CanPreselectPlaceOption() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'place'
		);
		$placeTitle = 'test place';
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => $placeTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);

		$this->fixture->piVars['place'][] = $placeUid;

		$this->assertContains(
			'<option value="' . $placeUid . '" selected="selected">' . $placeTitle . '</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPlaceOptions_CanPreselectMultiplePlaceOptions() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'place'
		);
		$placeTitle = 'foo';
		$placeTitle2 = 'bar';
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => $placeTitle)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('title' => $placeTitle2)
		);

		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid2, 'place'
		);

		$this->fixture->piVars['place'][] = $placeUid;
		$this->fixture->piVars['place'][] = $placeUid2;

		$output = $this->fixture->render();

		$this->assertContains(
			'<option value="' . $placeUid . '" selected="selected">' .
				$placeTitle . '</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $placeUid2 . '" selected="selected">' .
				$placeTitle2 . '</option>',
			$output
		);
	}
}
?>