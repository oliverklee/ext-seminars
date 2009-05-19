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

	public function test_Render_WithAllSearchOptionsHidden_ReturnsEmptyString() {
		$this->fixture->setConfigurationValue('displaySearchFormFields', '');

		$this->assertEquals(
			'',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledSearchWidget_ContainsSearchingHints() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->assertContains(
			$this->fixture->translate('label_searching_hints'),
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledSearchWidget_ContainsSubmitButton() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->assertContains(
			'<input type="submit" value="' .
				$this->fixture->translate('label_selector_submit') . '" />',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledSearchWidget_ContainsResetButton() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->assertContains(
			'<input type="submit" value="' .
				$this->fixture->translate('label_selector_reset') . '"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledShowEmtpyEntryInOptionLists_ContainsEmptyOption() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);
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

	public function text_Render_ForTwoEnabledSearchParts_RendersBothSearchParts() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type,language'
		);

		$output = $this->fixture->render();

		$this->assertContains(
			$this->fixture->translate('label_event_type'),
			$output
		);
		$this->assertContains(
			$this->fixture->translate('label_language'),
			$output
		);
	}

	public function text_Render_ForTwoEnabledSearchParts_AddsBothSearchFieldsToJavascript() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type,language'
		);

		$this->assertContains(
			'\'event_type\', \'language\'',
			$this->fixture->render()
		);
	}

	public function text_Render_ForEnabledSearchWidget_DoesNotHaveUnreplacedMarkers() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'full_text_search,language'
		);

		$this->assertNotContains(
			'###',
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
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_EVENT_TYPE')
		);
	}

	public function test_Render_ForEnabledEventTypeOptions_ContainsJavascriptPartForEventType() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$this->assertRegExp(
			'/var suffixes = new Array(.*\'event_type\'.*);/s',
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
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

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

	public function test_Render_ForEnabledLanguageOptions_ContainsJavascriptPartForLanguage() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'language'
		);

		$this->assertRegExp(
			'/var suffixes = new Array(.*\'language\'.*);/s',
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

	public function test_Render_ForEnabledPlaceOptions_ContainsJavascriptPartForPlace() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'place'
		);

		$this->assertRegExp(
			'/var suffixes = new Array(.*\'place\'.*);/s',
			$this->fixture->render()
		);
	}

	public function test_Render_ForDisabledPlaceOptions_HidesPlaceSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
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


	//////////////////////////////////////////////////////////
	// Tests concerning the rendering of the city option box
	//////////////////////////////////////////////////////////

	public function test_Render_ForEnabledCityOptions_ContainsJavascriptPartForCity() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->assertRegExp(
			'/var suffixes = new Array(.*\'city\'.*);/s',
			$this->fixture->render()
		);
	}

	public function test_Render_ForDisabledCityOptions_HidesCitySubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'country'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_CITY')
		);
	}

	public function test_Render_ForEnabledCityOptions_CanContainCityOption() {
		$this->fixture->setConfigurationValue('displaySearchFormFields', 'city');

		$cityName = 'test city';
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => $cityName)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);

		$this->assertContains(
			'<option value="' . $cityName . '">' . $cityName . '</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledCityOptions_CanContainTwoCityOptiona() {
		$this->fixture->setConfigurationValue('displaySearchFormFields', 'city');
		$cityName1 = 'foo city';
		$cityName2 = 'bar city';

		$placeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => $cityName1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => $cityName2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid2, $placeUid2, 'place'
		);

		$output = $this->fixture->render();

		$this->assertContains(
			'<option value="' . $cityName1 . '">' . $cityName1 . '</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $cityName2 . '">' . $cityName2 . '</option>',
			$output
		);
	}

	public function test_Render_ForEnabledCityOptions_CanPreselectCityOption() {
		$this->fixture->setConfigurationValue('displaySearchFormFields', 'city');
		$cityTitle = 'test city';
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => $cityTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);

		$this->fixture->piVars['city'][] = $cityTitle;

		$this->assertContains(
			'<option value="' . $cityTitle . '" selected="selected">' .
				$cityTitle . '</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledCityOptions_CanPreselectMultipleCityOptions() {
		$this->fixture->setConfigurationValue('displaySearchFormFields', 'city');
		$cityTitle1 = 'bar city';
		$cityTitle2 = 'foo city';

		$placeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => $cityTitle1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('city' => $cityTitle2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid2, $placeUid2, 'place'
		);

		$this->fixture->piVars['city'][] = $cityTitle1;
		$this->fixture->piVars['city'][] = $cityTitle2;

		$output = $this->fixture->render();

		$this->assertContains(
			'<option value="' . $cityTitle1 . '" selected="selected">' .
				$cityTitle1 . '</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $cityTitle2 . '" selected="selected">' .
				$cityTitle2 . '</option>',
			$output
		);
	}


	/////////////////////////////////////////////////////////////
	// Tests concerning the rendering of the country option box
	/////////////////////////////////////////////////////////////

	public function test_Render_ForEnabledCountryOptions_ContainsJavascriptPartForCountry() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'country'
		);

		$this->assertRegExp(
			'/var suffixes = new Array(.*\'country\'.*);/s',
			$this->fixture->render()
		);
	}

	public function test_Render_ForDisabledCountryOptions_HidesCountrySubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_COUNTRY')
		);
	}

	public function test_Render_ForEnabledCountryOptions_CanContainCountryOption() {
		$this->instantiateStaticInfo();
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'country'
		);

		$countryIsoCode = 'DE';
		$countryName = $this->staticInfo->getStaticInfoName(
			'COUNTRIES', $countryIsoCode
		);
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('country' => $countryIsoCode)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);

		$this->assertContains(
			'<option value="' . $countryIsoCode . '">' . $countryName .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledCountryOptions_CanContainMultipleCountryOptions() {
		$this->instantiateStaticInfo();
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'country'
		);

		$countryIsoCode1 = 'DE';
		$countryName1 = $this->staticInfo->getStaticInfoName(
			'COUNTRIES', $countryIsoCode1
		);
		$countryIsoCode2 = 'GB';
		$countryName2 = $this->staticInfo->getStaticInfoName(
			'COUNTRIES', $countryIsoCode2
		);

		$placeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('country' => $countryIsoCode1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('country' => $countryIsoCode2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid2, $placeUid2, 'place'
		);

		$output = $this->fixture->render();

		$this->assertContains(
			'<option value="' . $countryIsoCode1 . '">' . $countryName1 .
				'</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $countryIsoCode2 . '">' . $countryName2 .
				'</option>',
			$output
		);
	}

	public function test_Render_ForEnabledCountryOptions_CanPreselectOneCountryOption() {
		$this->instantiateStaticInfo();
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'country'
		);

		$countryIsoCode = 'DE';
		$countryName = $this->staticInfo->getStaticInfoName(
			'COUNTRIES', $countryIsoCode
		);
		$placeUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('country' => $countryIsoCode)
		);
		$eventUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid, $placeUid, 'place'
		);

		$this->fixture->piVars['country'][] = $countryIsoCode;

		$this->assertContains(
			'<option value="' . $countryIsoCode . '" selected="selected">' .
				$countryName . '</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledCountryOptions_CanPreselectMultipleCountryOptions() {
		$this->instantiateStaticInfo();
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'country'
		);

		$countryIsoCode1 = 'DE';
		$countryName1 = $this->staticInfo->getStaticInfoName(
			'COUNTRIES', $countryIsoCode1
		);
		$countryIsoCode2 = 'GB';
		$countryName2 = $this->staticInfo->getStaticInfoName(
			'COUNTRIES', $countryIsoCode2
		);

		$placeUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('country' => $countryIsoCode1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SITES, array('country' => $countryIsoCode2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SEMINARS
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			SEMINARS_TABLE_SEMINARS, $eventUid2, $placeUid2, 'place'
		);

		$this->fixture->piVars['country'][] = $countryIsoCode1;
		$this->fixture->piVars['country'][] = $countryIsoCode2;

		$output = $this->fixture->render();

		$this->assertContains(
			'<option value="' . $countryIsoCode1 . '" selected="selected">' .
				$countryName1 . '</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $countryIsoCode2 . '" selected="selected">' .
				$countryName2 . '</option>',
			$output
		);
	}


	////////////////////////////////////////////////
	// Tests concerning the full text search input
	////////////////////////////////////////////////

	public function test_Render_ForDisabledFullTextSearch_HidesFullTextSearchSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_TEXT')
		);
	}

	public function test_Render_ForEnabledFullTextSearch_ContainsFullTextSearchSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'full_text_search'
		);

		$this->fixture->render();

		$this->assertTrue(
			$this->fixture->isSubpartVisible('SEARCH_PART_TEXT')
		);
	}

	public function test_Render_ForEnabledFullTextSearch_ContainsFullTextJavaSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'full_text_search'
		);

		$this->assertContains(
			'document.getElementById(\'tx_seminars_pi1_sword\')',
			$this->fixture->render()
		);
	}

	public function test_Render_ForDisabledFullTextSearch_HidesFullTextJavaSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'language'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_JAVASCRIPT_TEXT')
		);
	}

	public function test_Render_ForEnabledFullTextSearch_CanFillSearchedWordIntoTextbox() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'full_text_search'
		);

		$searchWord = 'foo bar';
		$this->fixture->piVars['sword'] = $searchWord;

		$this->assertContains(
			$searchWord,
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledFullTextSearch_htmlSpecialcharsSearchedWord() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'full_text_search'
		);

		$searchWord = '<>';
		$this->fixture->piVars['sword'] = $searchWord;

		$this->assertContains(
			htmlspecialchars($searchWord),
			$this->fixture->render()
		);
	}


	/////////////////////////////////////
	// Tests concerning the date search
	/////////////////////////////////////

	public function test_Render_ForDisabledDateSearch_HidesDateSearchSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'country'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_DATE')
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsDayFromDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[date_from][day]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsMonthFromDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[date_from][month]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsYearFromDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[date_from][year]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsDayToDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[date_to][day]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsMonthToDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[date_to][month]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsYearToDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[date_to][year]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearchAndNumberOfYearsInDateFilterSetToTwo_ContainsThreeYearsInDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);
		$this->fixture->setConfigurationValue(
			'numberOfYearsInDateFilter', 2
		);

		$output = $this->fixture->render();
		$currentYear = intval(date('Y'));

		$this->assertContains(
			'<option value="' . $currentYear . '">' . $currentYear .'</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $currentYear + 1 . '">' .
				$currentYear + 1 .'</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $currentYear + 2 . '">' .
				$currentYear + 2 .'</option>',
			$output
		);
	}

	public function test_Render_ForEnabledDateSearch_AddsAnEmptyOptionToTheDropDown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<option value="0">&nbsp;</option>',
			$this->fixture->render()
		);
	}
}
?>