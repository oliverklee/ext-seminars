<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Hooks\Interfaces\SeminarSelectorWidget;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use SJBR\StaticInfoTables\PiBaseApi;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class SelectorWidgetTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Seminars_FrontEnd_SelectorWidget
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var PiBaseApi
     */
    protected $staticInfo = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->subject = new \Tx_Seminars_FrontEnd_SelectorWidget(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    //////////////////////
    // Utility functions
    //////////////////////

    /**
     * Creates and initializes $this->staticInfo.
     *
     * @return void
     */
    private function instantiateStaticInfo()
    {
        $this->staticInfo = new PiBaseApi();
        $this->staticInfo->init();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    ////////////////////////////////////
    // Tests for the utility functions
    ////////////////////////////////////

    public function testInstantiateStaticInfoCreateStaticInfoInstance()
    {
        $this->instantiateStaticInfo();

        self::assertInstanceOf(
            PiBaseApi::class,
            $this->staticInfo
        );
    }

    //////////////////////////////////////////
    // General tests concerning the fixture.
    //////////////////////////////////////////

    public function testFixtureIsAFrontEndSelectorWidgetObject()
    {
        self::assertInstanceOf(\Tx_Seminars_FrontEnd_SelectorWidget::class, $this->subject);
    }

    ///////////////////////
    // Tests for render()
    ///////////////////////

    /**
     * @test
     */
    public function renderWithAllSearchOptionsHiddenReturnsEmptyString()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', '');

        self::assertEquals(
            '',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledSearchWidgetContainsSearchingHints()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_searching_hints'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledSearchWidgetContainsSubmitButton()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        self::assertStringContainsString(
            '<input type="submit" value="' .
            $this->getLanguageService()->getLL('label_selector_submit') . '" />',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledSearchWidgetContainsResetButton()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        self::assertStringContainsString(
            '<input type="submit" value="' .
            $this->getLanguageService()->getLL('label_selector_reset') . '"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledShowEmptyEntryInOptionListsContainsEmptyOption()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );
        $this->subject->setConfigurationValue(
            'showEmptyEntryInOptionLists',
            true
        );

        self::assertStringContainsString(
            '<option value="0">' .
            $this->getLanguageService()->getLL('label_selector_pleaseChoose') .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForTwoEnabledSearchPartsRendersBothSearchParts()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type,language'
        );

        $output = $this->subject->render();

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_event_type'),
            $output
        );
        self::assertStringContainsString(
            $this->getLanguageService()->getLL('label_language'),
            $output
        );
    }

    /**
     * @test
     */
    public function renderForEnabledSearchWidgetDoesNotHaveUnreplacedMarkers()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type,language,country,city,place,full_text_search,date,' .
            'age,organizer,price'
        );

        self::assertStringNotContainsString(
            '###',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledSearchWidgetCallsSeminarSelectorWidgetHook()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'city');

        $hook = $this->createMock(SeminarSelectorWidget::class);
        $hook->expects(self::once())->method('modifySelectorWidget')->with($this->subject, self::anything());

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][SeminarSelectorWidget::class][] = $hookClass;
        GeneralUtility::addInstance($hookClass, $hook);

        $this->subject->render();
    }

    /////////////////////////////////////////////
    // Test for removeDummyOptionFromFormData()
    /////////////////////////////////////////////

    public function testRemoveDummyOptionFromFormDataRemovesDummyOptionAtBeginningOfArray()
    {
        self::assertEquals(
            ['CH', 'DE'],
            \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData(
                [0, 'CH', 'DE']
            )
        );
    }

    public function testRemoveDummyOptionFromFormDataRemovesDummyOptionInMiddleOfArray()
    {
        self::assertEquals(
            ['CH', 'DE'],
            \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData(
                ['CH', 0, 'DE']
            )
        );
    }

    public function testRemoveDummyOptionFromFormDataWithEmptyFormDataReturnsEmptyArray()
    {
        self::assertEquals(
            [],
            \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData(
                []
            )
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests concerning the rendering of the event_type option box
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForEventTypeHiddenInConfigurationHidesEventTypeSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_EVENT_TYPE')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledEventTypeCanContainEventTypeOption()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );

        $eventTypeTitle = 'test event type';
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => $eventTypeTitle]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_type' => $eventTypeUid]
        );

        self::assertStringContainsString(
            '<option value="' . $eventTypeUid . '">' . $eventTypeTitle .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledEventTypeHtmlSpecialCharsTheEventTypeTitle()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );

        $eventTypeTitle = '< Test >';
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => $eventTypeTitle]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_type' => $eventTypeUid]
        );

        self::assertStringContainsString(
            '<option value="' . $eventTypeUid . '">' .
            \htmlspecialchars($eventTypeTitle, ENT_QUOTES | ENT_HTML5) .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledEventTypePreselectsSelectedValue()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );

        $eventTypeTitle = 'test event type';
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => $eventTypeTitle]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_type' => $eventTypeUid]
        );

        $this->subject->piVars['event_type'][] = (string)$eventTypeUid;

        self::assertStringContainsString(
            $eventTypeUid . '" selected="selected">' . $eventTypeTitle .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledEventTypeCanPreselectTwoValues()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );

        $eventTypeTitle = 'foo';
        $eventTypeTitle2 = 'bar';
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => $eventTypeTitle]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_type' => $eventTypeUid]
        );

        $eventTypeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => $eventTypeTitle2]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_type' => $eventTypeUid2]
        );

        $this->subject->piVars['event_type'][] = (string)$eventTypeUid;
        $this->subject->piVars['event_type'][] = (string)$eventTypeUid2;

        $output = $this->subject->render();

        self::assertStringContainsString(
            $eventTypeUid . '" selected="selected">' . $eventTypeTitle .
            '</option>',
            $output
        );
        self::assertStringContainsString(
            $eventTypeUid2 . '" selected="selected">' . $eventTypeTitle2 .
            '</option>',
            $output
        );
    }

    /**
     * @test
     */
    public function renderForEnabledEventTypeContainsSelectorForEventTypes()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[event_type][]" ' .
            'id="tx_seminars_pi1-event_type" size="5" multiple="multiple">',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function itemsInSearchBoxAreSortedAlphabetically()
    {
        /** @var \Tx_Seminars_FrontEnd_SelectorWidget&MockObject $subject */
        $subject = $this->getMockBuilder(\Tx_Seminars_FrontEnd_SelectorWidget::class)
            ->setMethods(
                [
                    'hasSearchField',
                    'getEventTypeData',
                    'getLanguageData',
                    'getPlaceData',
                    'getCityData',
                    'getCountryData',
                ]
            )->setConstructorArgs(
                [
                    [
                        'isStaticTemplateLoaded' => 1,
                        'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                        'displaySearchFormFields' => 'event_type',
                    ],
                    $this->getFrontEndController()->cObj,
                ]
            )->getMock();
        $subject->method('hasSearchField')
            ->willReturn(true);
        $subject->expects(self::once())->method('getEventTypeData')
            ->willReturn([1 => 'Foo', 2 => 'Bar']);
        $subject->method('getLanguageData')
            ->willReturn([]);
        $subject->method('getPlaceData')
            ->willReturn([]);
        $subject->method('getCityData')
            ->willReturn([]);
        $subject->method('getCountryData')
            ->willReturn([]);

        $output = $subject->render();
        self::assertTrue(
            strpos($output, 'Bar') < strpos($output, 'Foo')
        );
    }

    //////////////////////////////////////////////////////////////
    // Tests concerning the rendering of the language option box
    //////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForLanguageOptionsHiddenInConfigurationHidesLanguageSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_LANGUAGE')
        );
    }

    /**
     * @test
     */
    public function renderForLanguageOptionsHiddenInConfigurationDoesNotShowLanguageOptionsMarker()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        self::assertStringNotContainsString(
            '###OPTIONS_LANGUAGE###',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledLanguageOptionsContainsLanguageOption()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'language'
        );

        $this->instantiateStaticInfo();

        $languageIsoCode = 'DE';
        $languageName = $this->staticInfo->getStaticInfoName(
            'LANGUAGES',
            $languageIsoCode,
            '',
            '',
            0
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => $languageIsoCode]
        );

        self::assertStringContainsString(
            '<option value="' . $languageIsoCode . '">' . $languageName .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledLanguageOptionsContainsSelectorForLanguages()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'language'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[language][]" ' .
            'id="tx_seminars_pi1-language" size="5" multiple="multiple">',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledLanguageOptionsCanPreselectSelectedLanguage()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'language'
        );

        $this->instantiateStaticInfo();

        $languageIsoCode = 'DE';
        $languageName = $this->staticInfo->getStaticInfoName(
            'LANGUAGES',
            $languageIsoCode,
            '',
            '',
            0
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => $languageIsoCode]
        );

        $this->subject->piVars['language'][] = $languageIsoCode;

        self::assertStringContainsString(
            $languageIsoCode . '" selected="selected">' . $languageName .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledLanguageOptionsCanPreselectMultipleLanguages()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'language'
        );
        $this->instantiateStaticInfo();

        $languageIsoCode = 'DE';
        $languageName = $this->staticInfo->getStaticInfoName(
            'LANGUAGES',
            $languageIsoCode,
            '',
            '',
            0
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => $languageIsoCode]
        );

        $languageIsoCode2 = 'EN';
        $languageName2 = $this->staticInfo->getStaticInfoName(
            'LANGUAGES',
            $languageIsoCode2,
            '',
            '',
            0
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => $languageIsoCode2]
        );

        $this->subject->piVars['language'][] = $languageIsoCode;
        $this->subject->piVars['language'][] = $languageIsoCode2;

        $output = $this->subject->render();

        self::assertStringContainsString(
            $languageIsoCode . '" selected="selected">' . $languageName .
            '</option>',
            $output
        );
        self::assertStringContainsString(
            $languageIsoCode2 . '" selected="selected">' . $languageName2 .
            '</option>',
            $output
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests concerning the rendering of the place option box
    ///////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForDisabledPlaceOptionsHidesPlaceSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_PLACE')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPlaceOptionsContainsPlaceOptions()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'place'
        );
        $placeTitle = 'test place';
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => $placeTitle]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );

        self::assertStringContainsString(
            '<option value="' . $placeUid . '">' . $placeTitle . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPlaceOptionsHtmlSpecialCharsThePlaceTitle()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'place'
        );
        $placeTitle = '<>';
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => $placeTitle]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );

        self::assertStringContainsString(
            '<option value="' . $placeUid . '">' .
            \htmlspecialchars($placeTitle, ENT_QUOTES | ENT_HTML5) . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPlaceOptionsContainsSelectorForPlaces()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'place'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[place][]" ' .
            'id="tx_seminars_pi1-place" size="5" multiple="multiple">',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPlaceOptionsCanPreselectPlaceOption()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'place'
        );
        $placeTitle = 'test place';
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => $placeTitle]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );

        $this->subject->piVars['place'][] = (string)$placeUid;

        self::assertStringContainsString(
            '<option value="' . $placeUid . '" selected="selected">' . $placeTitle . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPlaceOptionsCanPreselectMultiplePlaceOptions()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'place'
        );
        $placeTitle = 'foo';
        $placeTitle2 = 'bar';
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => $placeTitle]
        );
        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => $placeTitle2]
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid2,
            'place'
        );

        $this->subject->piVars['place'][] = (string)$placeUid;
        $this->subject->piVars['place'][] = (string)$placeUid2;

        $output = $this->subject->render();

        self::assertStringContainsString(
            '<option value="' . $placeUid . '" selected="selected">' .
            $placeTitle . '</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="' . $placeUid2 . '" selected="selected">' .
            $placeTitle2 . '</option>',
            $output
        );
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the rendering of the city option box
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForDisabledCityOptionsHidesCitySubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'country'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_CITY')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCityOptionsCanContainCityOption()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'city');

        $cityName = 'test city';
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => $cityName]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );

        self::assertStringContainsString(
            '<option value="' . $cityName . '">' . $cityName . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCityOptionsCanContainTwoCityOptiona()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'city');
        $cityName1 = 'foo city';
        $cityName2 = 'bar city';

        $placeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => $cityName1]
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid1,
            $placeUid1,
            'place'
        );

        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => $cityName2]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid2,
            $placeUid2,
            'place'
        );

        $output = $this->subject->render();

        self::assertStringContainsString(
            '<option value="' . $cityName1 . '">' . $cityName1 . '</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="' . $cityName2 . '">' . $cityName2 . '</option>',
            $output
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCityOptionsCanPreselectCityOption()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'city');
        $cityTitle = 'test city';
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => $cityTitle]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );

        $this->subject->piVars['city'][] = $cityTitle;

        self::assertStringContainsString(
            '<option value="' . $cityTitle . '" selected="selected">' .
            $cityTitle . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCityOptionsCanPreselectMultipleCityOptions()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'city');
        $cityTitle1 = 'bar city';
        $cityTitle2 = 'foo city';

        $placeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => $cityTitle1]
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid1,
            $placeUid1,
            'place'
        );

        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => $cityTitle2]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid2,
            $placeUid2,
            'place'
        );

        $this->subject->piVars['city'][] = $cityTitle1;
        $this->subject->piVars['city'][] = $cityTitle2;

        $output = $this->subject->render();

        self::assertStringContainsString(
            '<option value="' . $cityTitle1 . '" selected="selected">' .
            $cityTitle1 . '</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="' . $cityTitle2 . '" selected="selected">' .
            $cityTitle2 . '</option>',
            $output
        );
    }

    /////////////////////////////////////////////////////////////
    // Tests concerning the rendering of the country option box
    /////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForDisabledCountryOptionsHidesCountrySubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_COUNTRY')
        );
    }

    /**
     * @test
     */
    public function renderForDisabledCountryOptionsDoesNotShowCountryOptionsMarker()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        self::assertStringNotContainsString(
            '###OPTIONS_COUNTRY###',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCountryOptionsCanContainCountryOption()
    {
        $this->instantiateStaticInfo();
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'country'
        );

        $countryIsoCode = 'DE';
        $countryName = $this->staticInfo->getStaticInfoName(
            'COUNTRIES',
            $countryIsoCode
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => $countryIsoCode]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );

        self::assertStringContainsString(
            '<option value="' . $countryIsoCode . '">' . $countryName .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCountryOptionsCanContainMultipleCountryOptions()
    {
        $this->instantiateStaticInfo();
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'country'
        );

        $countryIsoCode1 = 'DE';
        $countryName1 = $this->staticInfo->getStaticInfoName(
            'COUNTRIES',
            $countryIsoCode1
        );
        $countryIsoCode2 = 'GB';
        $countryName2 = $this->staticInfo->getStaticInfoName(
            'COUNTRIES',
            $countryIsoCode2
        );

        $placeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => $countryIsoCode1]
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid1,
            $placeUid1,
            'place'
        );

        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => $countryIsoCode2]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid2,
            $placeUid2,
            'place'
        );

        $output = $this->subject->render();

        self::assertStringContainsString(
            '<option value="' . $countryIsoCode1 . '">' . $countryName1 .
            '</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="' . $countryIsoCode2 . '">' . $countryName2 .
            '</option>',
            $output
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCountryOptionsCanPreselectOneCountryOption()
    {
        $this->instantiateStaticInfo();
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'country'
        );

        $countryIsoCode = 'DE';
        $countryName = $this->staticInfo->getStaticInfoName(
            'COUNTRIES',
            $countryIsoCode
        );
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => $countryIsoCode]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $placeUid,
            'place'
        );

        $this->subject->piVars['country'][] = $countryIsoCode;

        self::assertStringContainsString(
            '<option value="' . $countryIsoCode . '" selected="selected">' .
            $countryName . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCountryOptionsCanPreselectMultipleCountryOptions()
    {
        $this->instantiateStaticInfo();
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'country'
        );

        $countryIsoCode1 = 'DE';
        $countryName1 = $this->staticInfo->getStaticInfoName(
            'COUNTRIES',
            $countryIsoCode1
        );
        $countryIsoCode2 = 'GB';
        $countryName2 = $this->staticInfo->getStaticInfoName(
            'COUNTRIES',
            $countryIsoCode2
        );

        $placeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => $countryIsoCode1]
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid1,
            $placeUid1,
            'place'
        );

        $placeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => $countryIsoCode2]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid2,
            $placeUid2,
            'place'
        );

        $this->subject->piVars['country'][] = $countryIsoCode1;
        $this->subject->piVars['country'][] = $countryIsoCode2;

        $output = $this->subject->render();

        self::assertStringContainsString(
            '<option value="' . $countryIsoCode1 . '" selected="selected">' .
            $countryName1 . '</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="' . $countryIsoCode2 . '" selected="selected">' .
            $countryName2 . '</option>',
            $output
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning the full text search input
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForDisabledFullTextSearchHidesFullTextSearchSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_TEXT')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledFullTextSearchContainsFullTextSearchSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'full_text_search'
        );

        $this->subject->render();

        self::assertTrue(
            $this->subject->isSubpartVisible('SEARCH_PART_TEXT')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledFullTextSearchCanFillSearchedWordIntoTextbox()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'full_text_search'
        );

        $searchWord = 'foo bar';
        $this->subject->piVars['sword'] = $searchWord;

        self::assertStringContainsString(
            $searchWord,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledFullTextSearchHtmlSpecialCharsSearchedWord()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'full_text_search'
        );

        $searchWord = '<>';
        $this->subject->piVars['sword'] = $searchWord;

        self::assertStringContainsString(
            \htmlspecialchars($searchWord, ENT_QUOTES | ENT_HTML5),
            $this->subject->render()
        );
    }

    /////////////////////////////////////
    // Tests concerning the date search
    /////////////////////////////////////

    /**
     * @test
     */
    public function renderForDisabledDateSearchHidesDateSearchSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'country'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_DATE')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchContainsDayFromDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[from_day]"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchContainsMonthFromDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[from_month]"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchContainsYearFromDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[from_year]"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchContainsDayToDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[to_day]"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchContainsMonthToDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[to_month]"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchContainsYearToDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        self::assertStringContainsString(
            '<select name="tx_seminars_pi1[to_year]"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchAndNumberOfYearsInDateFilterSetToTwoContainsTwoYearsInDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );
        $this->subject->setConfigurationValue(
            'numberOfYearsInDateFilter',
            2
        );

        $output = $this->subject->render();
        $currentYear = (int)date('Y');

        self::assertStringContainsString(
            '<option value="' . $currentYear . '">' . $currentYear . '</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="' . ($currentYear + 1) . '">' . ($currentYear + 1) . '</option>',
            $output
        );
    }

    /**
     * @test
     */
    public function renderForEnabledDateSearchAddsAnEmptyOptionToTheDropDown()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        self::assertStringContainsString(
            '<option value="0">&nbsp;</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForSentToMonthValuePreselectsToMonthValue()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );

        $this->subject->piVars['to_month'] = 5;

        self::assertStringContainsString(
            '<option value="5" selected="selected">5</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForSentFromDatePreselectsFromDateValues()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );
        $this->subject->setConfigurationValue(
            'numberOfYearsInDateFilter',
            2
        );

        $thisYear = date('Y');
        $this->subject->piVars['from_day'] = 2;
        $this->subject->piVars['from_month'] = 5;
        $this->subject->piVars['from_year'] = $thisYear;

        $output = $this->subject->render();

        self::assertStringContainsString(
            '<option value="2" selected="selected">2</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="5" selected="selected">5</option>',
            $output
        );
        self::assertStringContainsString(
            '<option value="' . $thisYear . '" selected="selected">' .
            $thisYear . '</option>',
            $output
        );
    }

    /**
     * @test
     */
    public function renderForNoSentDatePreselectsNoDateValues()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );
        $this->subject->setConfigurationValue(
            'numberOfYearsInDateFilter',
            2
        );

        self::assertStringNotContainsString(
            'selected="selected"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForBothSentDatesZeroPreselectsNoDateValues()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'date'
        );
        $this->subject->setConfigurationValue(
            'numberOfYearsInDateFilter',
            2
        );

        $this->subject->piVars['from_day'] = 0;
        $this->subject->piVars['from_month'] = 0;
        $this->subject->piVars['from_year'] = 0;
        $this->subject->piVars['to_day'] = 0;
        $this->subject->piVars['to_month'] = 0;
        $this->subject->piVars['to_year'] = 0;

        self::assertStringNotContainsString(
            'selected="selected"',
            $this->subject->render()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning the event type limitation
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForEventTypeLimitedAndEventTypeDisplayedShowsTheLimitedEventType()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );

        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo_type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_type' => $eventTypeUid]
        );

        $this->subject->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid
        );

        self::assertStringContainsString(
            'foo_type',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEventTypeLimitedAndEventTypeDisplayedHidesEventTypeNotLimited()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'event_type'
        );

        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'foo_type']
        );
        $eventTypeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'bar_type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_type' => $eventTypeUid2]
        );

        $this->subject->setConfigurationValue(
            'limitListViewToEventTypes',
            $eventTypeUid
        );

        self::assertStringNotContainsString(
            'bar_type',
            $this->subject->render()
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning the organizer search widget
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForOrganizersLimitedAndOrganizerDisplayedShowsTheLimitedOrganizers()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'organizer'
        );

        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'Organizer Foo']
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->testingFramework->createRecord('tx_seminars_seminars'),
            $organizerUid,
            'organizers'
        );

        $this->subject->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid
        );

        self::assertStringContainsString(
            'Organizer Foo',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForOrganizerLimitedAndOrganizersDisplayedHidesTheOrganizersWhichAreNotTheLimitedOnes()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'organizer'
        );

        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'Organizer Bar']
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

        $this->subject->setConfigurationValue(
            'limitListViewToOrganizers',
            $organizerUid2
        );

        self::assertStringNotContainsString(
            'Organizer Bar',
            $this->subject->render()
        );
    }

    // Tests concerning the category search widget

    /**
     * @test
     */
    public function renderForCategoriesLimitedAndCategoryDisplayedShowsTheLimitedCategories()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'categories');

        $categoryUid = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => 'Category Foo']);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->testingFramework->createRecord('tx_seminars_seminars'),
            $categoryUid,
            'categories'
        );

        $this->subject->setConfigurationValue('limitListViewToCategories', $categoryUid);

        self::assertStringContainsString(
            'Category Foo',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCategoryLimitedAndCategoriesDisplayedHidesTheCategoriesWhichAreNotTheLimitedOnes()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'categories');

        $categoryUid1 = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => 'Category Bar']);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $this->testingFramework->createRecord('tx_seminars_seminars'),
            $categoryUid1,
            'categories'
        );

        $categoryUid2 = $this->testingFramework->createRecord('tx_seminars_categories');

        $this->subject->setConfigurationValue('limitListViewToCategories', $categoryUid2);

        self::assertStringNotContainsString(
            'Category Bar',
            $this->subject->render()
        );
    }

    //////////////////////////////////////////
    // Tests concerning the age search input
    //////////////////////////////////////////

    /**
     * @test
     */
    public function renderForDisabledAgeSearchHidesAgeSearchSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_AGE')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledAgeSearchContainsAgeSearchSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'age'
        );

        $this->subject->render();

        self::assertTrue(
            $this->subject->isSubpartVisible('SEARCH_PART_AGE')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledAgeSearchCanFillSearchedAgeIntoTextbox()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'age'
        );

        $searchedAge = 15;
        $this->subject->piVars['age'] = $searchedAge;

        self::assertStringContainsString(
            (string)$searchedAge,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledAgeSearchAndAgeValueZeroDoesNotShowAgeValueZero()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'age'
        );

        $searchedAge = 0;
        $this->subject->piVars['age'] = $searchedAge;

        self::assertStringNotContainsString(
            'age]" value="' . $searchedAge . '"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledAgeSearchDoesNotIncludeNonIntegerAgeAsValue()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'age'
        );

        $searchedAge = 'Hallo';
        $this->subject->piVars['age'] = $searchedAge;

        self::assertStringNotContainsString(
            $searchedAge,
            $this->subject->render()
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests concerning the rendering of the organizer option box
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForOrganizerHiddenInConfigurationHidesOrganizerSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_ORGANIZER')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledOrganizerContainsOrganizerOption()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'organizer'
        );

        $organizerName = 'test organizer';
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => $organizerName]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        self::assertStringContainsString(
            '<option value="' . $organizerUid . '">' . $organizerName .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledOrganizerHtmlSpecialCharsTheOrganizersName()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'organizer'
        );

        $organizerName = '< Organizer Name >';
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => $organizerName]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        self::assertStringContainsString(
            '<option value="' . $organizerUid . '">' .
            \htmlspecialchars($organizerName, ENT_QUOTES | ENT_HTML5) .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledOrganizerPreselectsSelectedValue()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'organizer'
        );

        $organizerName = 'Organizer Name';
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => $organizerName]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->subject->piVars['organizer'][] = (string)$organizerUid;

        self::assertStringContainsString(
            $organizerUid . '" selected="selected">' . $organizerName .
            '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledOrganizerCanPreselectTwoValues()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'organizer'
        );

        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        $organizerName1 = 'Organizer 1';
        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => $organizerName1]
        );
        $organizerName2 = 'Organizer 2';
        $organizerUid2 = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => $organizerName2]
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid1,
            'organizers'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid2,
            'organizers'
        );

        $this->subject->piVars['organizer'][] = (string)$organizerUid1;
        $this->subject->piVars['organizer'][] = (string)$organizerUid2;

        $output = $this->subject->render();

        self::assertStringContainsString(
            $organizerUid1 . '" selected="selected">' . $organizerName1 .
            '</option>',
            $output
        );
        self::assertStringContainsString(
            $organizerUid2 . '" selected="selected">' . $organizerName2 .
            '</option>',
            $output
        );
    }

    /**
     * @test
     */
    public function renderForEnabledOrganizerContainsOrganizersSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'organizer'
        );

        $this->subject->render();

        self::assertTrue(
            $this->subject->isSubpartVisible('SEARCH_PART_ORGANIZER')
        );
    }

    // Tests concerning the rendering of the category option box

    /**
     * @test
     */
    public function renderForCategoryHiddenInConfigurationHidesCategorySubpart()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'city');

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_ORGANIZER')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCategoryContainsCategoryOption()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'categories');

        $categoryName = 'test category';
        $categoryUid = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => $categoryName]);
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $categoryUid,
            'categories'
        );

        self::assertStringContainsString(
            '<option value="' . $categoryUid . '">' . $categoryName . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCategoryHtmlSpecialCharsTheCategoriesName()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'categories');

        $categoryName = '< Category Name >';
        $categoryUid = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => $categoryName]);
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $categoryUid,
            'categories'
        );

        self::assertStringContainsString(
            '<option value="' . $categoryUid . '">' . \htmlspecialchars(
                $categoryName,
                ENT_QUOTES | ENT_HTML5
            ) . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCategoryPreselectsSelectedValue()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'categories');

        $categoryName = 'Category Name';
        $categoryUid = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => $categoryName]);
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $categoryUid,
            'categories'
        );

        $this->subject->piVars['categories'][] = (string)$categoryUid;

        self::assertStringContainsString(
            $categoryUid . '" selected="selected">' . $categoryName . '</option>',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCategoryCanPreselectTwoValues()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'categories');

        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        $categoryName1 = 'Category 1';
        $categoryUid1 = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => $categoryName1]);
        $categoryName2 = 'Category 2';
        $categoryUid2 = $this->testingFramework->createRecord('tx_seminars_categories', ['title' => $categoryName2]);

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $categoryUid1,
            'categories'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $categoryUid2,
            'categories'
        );

        $this->subject->piVars['categories'][] = (string)$categoryUid1;
        $this->subject->piVars['categories'][] = (string)$categoryUid2;

        $output = $this->subject->render();

        self::assertStringContainsString(
            $categoryUid1 . '" selected="selected">' . $categoryName1 . '</option>',
            $output
        );
        self::assertStringContainsString(
            $categoryUid2 . '" selected="selected">' . $categoryName2 . '</option>',
            $output
        );
    }

    /**
     * @test
     */
    public function renderForEnabledCategoryContainsCategoriesSubpart()
    {
        $this->subject->setConfigurationValue('displaySearchFormFields', 'categories');
        $this->subject->render();

        self::assertTrue(
            $this->subject->isSubpartVisible('SEARCH_PART_CATEGORIES')
        );
    }

    ////////////////////////////////////////////
    // Tests concerning the price search input
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function renderForDisabledPriceSearchHidesPriceSearchSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'city'
        );

        $this->subject->render();

        self::assertFalse(
            $this->subject->isSubpartVisible('SEARCH_PART_PRICE')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPriceSearchContainsPriceSearchSubpart()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'price'
        );

        $this->subject->render();

        self::assertTrue(
            $this->subject->isSubpartVisible('SEARCH_PART_PRICE')
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPriceSearchCanFillSearchedPriceFromIntoTextbox()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'price'
        );

        $priceFrom = 10;
        $this->subject->piVars['price_from'] = $priceFrom;

        self::assertStringContainsString(
            (string)$priceFrom,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPriceSearchCanFillSearchedPriceToIntoTextbox()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'price'
        );

        $priceTo = 50;
        $this->subject->piVars['price_to'] = $priceTo;

        self::assertStringContainsString(
            (string)$priceTo,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPriceSearchAndPriceFromZeroDoesNotShowZeroForPriceFrom()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'price'
        );

        $priceFrom = 0;
        $this->subject->piVars['price_from'] = $priceFrom;

        self::assertStringNotContainsString(
            'price_from]" value="' . $priceFrom . '"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPriceSearchAndPriceToZeroDoesNotShowZeroForPriceTo()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'price'
        );

        $priceTo = 0;
        $this->subject->piVars['price_to'] = $priceTo;

        self::assertStringNotContainsString(
            'price_to]" value="' . $priceTo . '"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPriceSearchDoesNotIncludeNonIntegerPriceFromAsValue()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'price'
        );

        $priceFrom = 'Hallo';
        $this->subject->piVars['price_from'] = $priceFrom;

        self::assertStringNotContainsString(
            $priceFrom,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEnabledPriceSearchDoesNotIncludeNonIntegerPriceToAsValue()
    {
        $this->subject->setConfigurationValue(
            'displaySearchFormFields',
            'price'
        );

        $priceTo = 'Hallo';
        $this->subject->piVars['price_from'] = $priceTo;

        self::assertStringNotContainsString(
            $priceTo,
            $this->subject->render()
        );
    }
}
