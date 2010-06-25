<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2010 Niels Pardon (mail@niels-pardon.de)
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

/**
 * Testcase for the tx_seminars_FrontEnd_SelectorWidget class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_FrontEnd_SelectorWidgetTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_SelectorWidget
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

		$this->fixture = new tx_seminars_FrontEnd_SelectorWidget(
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

		tx_seminars_registrationmanager::purgeInstance();
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
			$this->fixture instanceof tx_seminars_FrontEnd_SelectorWidget
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

	public function test_Render_ForEnabledShowEmptyEntryInOptionLists_ContainsEmptyOption() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);
		$this->fixture->setConfigurationValue(
			'showEmptyEntryInOptionLists', TRUE
		);

		$this->assertContains(
			'<option value="0">' .
				$this->fixture->translate('label_selector_pleaseChoose') .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForTwoEnabledSearchParts_RendersBothSearchParts() {
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

	public function test_Render_ForEnabledSearchWidget_DoesNotHaveUnreplacedMarkers() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields',
			'event_type,language,country,city,place,full_text_search,date,' .
				'age,organizer,price'
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
			tx_seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData(
				array(0, 'CH', 'DE')
			)
		);
	}

	public function testRemoveDummyOptionFromFormDataRemovesDummyOptionInMiddleOfArray() {
		$this->assertEquals(
			array('CH', 'DE'),
			tx_seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData(
				array('CH', 0, 'DE')
			)
		);
	}

	public function testRemoveDummyOptionFromFormDataWithEmptyFormDataReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			tx_seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData(
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

	public function test_Render_ForEnabledEventType_CanContainEventTypeOption() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$eventTypeTitle = 'test event type';
		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('event_type' => $eventTypeUid)
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
			'tx_seminars_event_types', array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('event_type' => $eventTypeUid)
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
			'tx_seminars_event_types', array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('event_type' => $eventTypeUid)
		);

		$this->fixture->piVars['event_type'][] = (string) $eventTypeUid;

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
			'tx_seminars_event_types', array('title' => $eventTypeTitle)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('event_type' => $eventTypeUid)
		);

		$eventTypeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('title' => $eventTypeTitle2)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('event_type' => $eventTypeUid2)
		);

		$this->fixture->piVars['event_type'][] = (string) $eventTypeUid;
		$this->fixture->piVars['event_type'][] = (string) $eventTypeUid2;

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

	/**
	 * @test
	 */
	public function itemsInSearchBoxAreSortedAlphabetically() {
		$fixture = $this->getMock(
			'tx_seminars_FrontEnd_SelectorWidget',
			array(
				'initialize', 'hasSearchField', 'getEventTypeData',
				'getLanguageData', 'getPlaceData', 'getCityData',
				'getCountryData'
			),
			array(
				array(
					'isStaticTemplateLoaded' => 1,
					'templateFile' => 'EXT:seminars/pi1/seminars_pi1.tmpl',
					'displaySearchFormFields' => 'event_type',
				),
				$GLOBALS['TSFE']->cObj
			)
		);
		$fixture->expects($this->any())->method('hasSearchField')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->once())->method('getEventTypeData')
			->will($this->returnValue(array(1 => 'Foo', 2 => 'Bar')));
		$fixture->expects($this->any())->method('getLanguageData')
			->will($this->returnValue(array()));
		$fixture->expects($this->any())->method('getPlaceData')
			->will($this->returnValue(array()));
		$fixture->expects($this->any())->method('getCityData')
			->will($this->returnValue(array()));
		$fixture->expects($this->any())->method('getCountryData')
			->will($this->returnValue(array()));

		$output = $fixture->render();
		$this->assertTrue(
			strpos($output, 'Bar') < strpos($output, 'Foo')
		);

		$fixture->__destruct();
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

	public function test_Render_ForLanguageOptionsHiddenInConfiguration_DoesNotShowLanguageOptionsMarker() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->assertNotContains(
			'###OPTIONS_LANGUAGE###',
			$this->fixture->render()
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
			'tx_seminars_seminars', array('language' => $languageIsoCode)
		);

		$this->assertContains(
			'<option value="' . $languageIsoCode . '">' . $languageName .
				'</option>',
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
			'tx_seminars_seminars', array('language' => $languageIsoCode)
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
			'tx_seminars_seminars', array('language' => $languageIsoCode)
		);

		$languageIsoCode2 = 'EN';
		$languageName2 = $this->staticInfo->getStaticInfoName(
			'LANGUAGES', $languageIsoCode2, '', '', 0
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('language' => $languageIsoCode2)
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
			'tx_seminars_sites', array('title' => $placeTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
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
			'tx_seminars_sites', array('title' => $placeTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
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
			'tx_seminars_sites', array('title' => $placeTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
		);

		$this->fixture->piVars['place'][] = (string) $placeUid;

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
			'tx_seminars_sites', array('title' => $placeTitle)
		);
		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('title' => $placeTitle2)
		);

		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid2, 'place'
		);

		$this->fixture->piVars['place'][] = (string) $placeUid;
		$this->fixture->piVars['place'][] = (string) $placeUid2;

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
			'tx_seminars_sites', array('city' => $cityName)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
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
			'tx_seminars_sites', array('city' => $cityName1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('city' => $cityName2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid2, $placeUid2, 'place'
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
			'tx_seminars_sites', array('city' => $cityTitle)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
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
			'tx_seminars_sites', array('city' => $cityTitle1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('city' => $cityTitle2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid2, $placeUid2, 'place'
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

	public function test_Render_ForDisabledCountryOptions_HidesCountrySubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_COUNTRY')
		);
	}

	public function test_Render_ForDisabledCountryOptions_DoesNotShowCountryOptionsMarker() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->assertNotcontains(
			'###OPTIONS_COUNTRY###',
			$this->fixture->render()
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
			'tx_seminars_sites', array('country' => $countryIsoCode)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
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
			'tx_seminars_sites', array('country' => $countryIsoCode1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('country' => $countryIsoCode2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid2, $placeUid2, 'place'
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
			'tx_seminars_sites', array('country' => $countryIsoCode)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $placeUid, 'place'
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
			'tx_seminars_sites', array('country' => $countryIsoCode1)
		);
		$eventUid1 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid1, $placeUid1, 'place'
		);

		$placeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_sites', array('country' => $countryIsoCode2)
		);
		$eventUid2 = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid2, $placeUid2, 'place'
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
			'<select name="tx_seminars_pi1[from_day]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsMonthFromDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[from_month]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsYearFromDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[from_year]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsDayToDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[to_day]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsMonthToDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[to_month]"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledDateSearch_ContainsYearToDropdown() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->assertContains(
			'<select name="tx_seminars_pi1[to_year]"',
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

	public function test_Render_ForSentToMonthValue_PreselectsToMonthValue() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);

		$this->fixture->piVars['to_month'] = 5;


		$this->assertContains(
			'<option value="5" selected="selected">5</option>',
			$this->fixture->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForSentFromDatePreselectsFromDateValues() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);
		$this->fixture->setConfigurationValue(
			'numberOfYearsInDateFilter', 2
		);

		$thisYear = date('Y', mktime());
		$this->fixture->piVars['from_day'] = 2;
		$this->fixture->piVars['from_month'] = 5;
		$this->fixture->piVars['from_year'] = $thisYear;

		$output = $this->fixture->render();

		$this->assertContains(
			'<option value="2" selected="selected">2</option>',
			$output
		);
		$this->assertContains(
			'<option value="5" selected="selected">5</option>',
			$output
		);
		$this->assertContains(
			'<option value="' . $thisYear . '" selected="selected">' .
				$thisYear . '</option>',
			$output
		);
	}

	public function test_Render_ForNoSentDate_PreselectsNoDateValues() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);
		$this->fixture->setConfigurationValue(
			'numberOfYearsInDateFilter', 2
		);

		$this->assertNotContains(
			'selected="selected"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForBothSentDatesZero_PreselectsNoDateValues() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'date'
		);
		$this->fixture->setConfigurationValue(
			'numberOfYearsInDateFilter', 2
		);

		$this->fixture->piVars['from_day'] = 0;
		$this->fixture->piVars['from_month'] = 0;
		$this->fixture->piVars['from_year'] = 0;
		$this->fixture->piVars['to_day'] = 0;
		$this->fixture->piVars['to_month'] = 0;
		$this->fixture->piVars['to_year'] = 0;

		$this->assertNotContains(
			'selected="selected"',
			$this->fixture->render()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning the event type limitation
	///////////////////////////////////////////////

	public function test_Render_ForEventTypeLimitedAndEventTypeDisplayed_ShowsTheLimitedEventType() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('title' => 'foo_type')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('event_type' => $eventTypeUid)
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToEventTypes', $eventTypeUid
		);

		$this->assertContains(
			'foo_type',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEventTypeLimitedAndEventTypeDisplayed_hidesEventTypeNotLimited() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'event_type'
		);

		$eventTypeUid = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('title' => 'foo_type')
		);
		$eventTypeUid2 = $this->testingFramework->createRecord(
			'tx_seminars_event_types', array('title' => 'bar_type')
		);
		$this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array('event_type' => $eventTypeUid2)
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToEventTypes', $eventTypeUid
		);

		$this->assertNotContains(
			'bar_type',
			$this->fixture->render()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning the organizer search widget
	////////////////////////////////////////////////

	public function test_Render_ForOrganizersLimitedAndOrganizerDisplayed_ShowsTheLimitedOrganizers() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'organizer'
		);

		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('title' => 'Organizer Foo')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$this->testingFramework->createRecord('tx_seminars_seminars'),
			$organizerUid,
			'organizers'
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToOrganizers', $organizerUid
		);

		$this->assertContains(
			'Organizer Foo',
			$this->fixture->render()
		);
	}

	public function test_Render_ForOrganizerLimitedAndOrganizersDisplayed_hidesTheOrganizersWhichAreNotTheLimitedOnes() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'organizer'
		);

		$organizerUid1 = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('title' => 'Organizer Bar')
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars',
			$this->testingFramework->createRecord('tx_seminars_seminars'),
			$organizerUid1,
			'organizers'
		);

		$organizerUid2 = $this->testingFramework->createRecord(
			'tx_seminars_organizers'
		);

		$this->fixture->setConfigurationValue(
			'limitListViewToOrganizers', $organizerUid2
		);

		$this->assertNotContains(
			'Organizer Bar',
			$this->fixture->render()
		);
	}


	//////////////////////////////////////////
	// Tests concerning the age search input
	//////////////////////////////////////////

	public function test_Render_ForDisabledAgeSearch_HidesAgeSearchSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_AGE')
		);
	}

	public function test_Render_ForEnabledAgeSearch_ContainsAgeSearchSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'age'
		);

		$this->fixture->render();

		$this->assertTrue(
			$this->fixture->isSubpartVisible('SEARCH_PART_AGE')
		);
	}

	public function test_Render_ForEnabledAgeSearch_CanFillSearchedAgeIntoTextbox() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'age'
		);

		$searchedAge = 15;
		$this->fixture->piVars['age'] = $searchedAge;

		$this->assertContains(
			(string) $searchedAge,
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledAgeSearchAndAgeValueZero_DoesNotShowAgeValueZero() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'age'
		);

		$searchedAge = 0;
		$this->fixture->piVars['age'] = $searchedAge;

		$this->assertNotContains(
			'age]" value="' . $searchedAge . '"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledAgeSearch_DoesNotIncludeNonIntegerAgeAsValue() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'age'
		);

		$searchedAge = 'Hallo';
		$this->fixture->piVars['age'] = $searchedAge;

		$this->assertNotContains(
			$searchedAge,
			$this->fixture->render()
		);
	}


	////////////////////////////////////////////////////////////////
	// Tests concerning the rendering of the organizer option box
	////////////////////////////////////////////////////////////////

	public function test_Render_ForOrganizerHiddenInConfiguration_HidesOrganizerSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_ORGANIZER')
		);
	}

	public function test_Render_ForEnabledOrganizer_ContainsOrganizerOption() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'organizer'
		);

		$organizerName = 'test organizer';
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('title' => $organizerName)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid, 'organizers'
		);

		$this->assertContains(
			'<option value="' . $organizerUid . '">' . $organizerName .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledOrganizer_HtmlSpecialCharsTheOrganizersName() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'organizer'
		);

		$organizerName = '< Organizer Name >';
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('title' => $organizerName)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid, 'organizers'
		);

		$this->assertContains(
			'<option value="' . $organizerUid . '">' .
				htmlspecialchars($organizerName) .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledOrganizer_PreselectsSelectedValue() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'organizer'
		);

		$organizerName = 'Organizer Name';
		$organizerUid = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('title' => $organizerName)
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid, 'organizers'
		);

		$this->fixture->piVars['organizer'][] = (string) $organizerUid;

		$this->assertContains(
			$organizerUid . '" selected="selected">' . $organizerName .
				'</option>',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledOrganizer_CanPreselectTwoValues() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'organizer'
		);

		$eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');

		$organizerName1 = 'Organizer 1';
		$organizerUid1 = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('title' => $organizerName1)
		);
		$organizerName2 = 'Organizer 2';
		$organizerUid2 = $this->testingFramework->createRecord(
			'tx_seminars_organizers', array('title' => $organizerName2)
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid1, 'organizers'
		);
		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_seminars', $eventUid, $organizerUid2, 'organizers'
		);

		$this->fixture->piVars['organizer'][] = (string) $organizerUid1;
		$this->fixture->piVars['organizer'][] = (string) $organizerUid2;

		$output = $this->fixture->render();

		$this->assertContains(
			$organizerUid1 . '" selected="selected">' . $organizerName1 .
				'</option>',
			$output
		);
		$this->assertContains(
			$organizerUid2 . '" selected="selected">' . $organizerName2 .
				'</option>',
			$output
		);
	}

	public function test_Render_ForEnabledOrganizer_ContainsOrganizersSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'organizer'
		);

		$this->fixture->render();

		$this->assertTrue(
			$this->fixture->isSubpartVisible('SEARCH_PART_ORGANIZER')
		);
	}


	////////////////////////////////////////////
	// Tests concerning the price search input
	////////////////////////////////////////////

	public function test_Render_ForDisabledPriceSearch_HidesPriceSearchSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'city'
		);

		$this->fixture->render();

		$this->assertFalse(
			$this->fixture->isSubpartVisible('SEARCH_PART_PRICE')
		);
	}

	public function test_Render_ForEnabledPriceSearch_ContainsPriceSearchSubpart() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'price'
		);

		$this->fixture->render();

		$this->assertTrue(
			$this->fixture->isSubpartVisible('SEARCH_PART_PRICE')
		);
	}

	public function test_Render_ForEnabledPriceSearch_CanFillSearchedPriceFromIntoTextbox() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'price'
		);

		$priceFrom = 10;
		$this->fixture->piVars['price_from'] = $priceFrom;

		$this->assertContains(
			(string) $priceFrom,
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPriceSearch_CanFillSearchedPriceToIntoTextbox() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'price'
		);

		$priceTo = 50;
		$this->fixture->piVars['price_to'] = $priceTo;

		$this->assertContains(
			(string) $priceTo,
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPriceSearchAndPriceFromZero_DoesNotShowZeroForPriceFrom() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'price'
		);

		$priceFrom = 0;
		$this->fixture->piVars['price_from'] = $priceFrom;

		$this->assertNotContains(
			'price_from]" value="' . $priceFrom . '"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPriceSearchAndPriceToZero_DoesNotShowZeroForPriceTo() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'price'
		);

		$priceTo = 0;
		$this->fixture->piVars['price_to'] = $priceTo;

		$this->assertNotContains(
			'price_to]" value="' . $priceTo . '"',
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPriceSearch_DoesNotIncludeNonIntegerPriceFromAsValue() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'price'
		);

		$priceFrom = 'Hallo';
		$this->fixture->piVars['price_from'] = $priceFrom;

		$this->assertNotContains(
			$priceFrom,
			$this->fixture->render()
		);
	}

	public function test_Render_ForEnabledPriceSearch_DoesNotIncludeNonIntegerPriceToAsValue() {
		$this->fixture->setConfigurationValue(
			'displaySearchFormFields', 'price'
		);

		$priceTo = 'Hallo';
		$this->fixture->piVars['price_from'] = $priceTo;

		$this->assertNotContains(
			$priceTo,
			$this->fixture->render()
		);
	}
}
?>