<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd\Fixtures\TestingDefaultController;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class DefaultControllerTest extends FunctionalTestCase
{
    /**
     * @var int
     */
    const CURRENT_PAGE_UID = 1;

    /**
     * @var int
     */
    const FOLDER_UID = 1;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var TypoScriptFrontendController
     */
    private $frontEndController = null;

    protected function tearDown()
    {
        \Tx_Seminars_Service_RegistrationManager::purgeInstance();

        parent::tearDown();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        if ($this->frontEndController instanceof TypoScriptFrontendController) {
            return $this->frontEndController;
        }

        $contentObject = new ContentObjectRenderer();
        $frontEndController = new TypoScriptFrontendController(null, self::CURRENT_PAGE_UID, 0);
        $frontEndController->fe_user = $this->prophesize(FrontendUserAuthentication::class)->reveal();
        if ($frontEndController instanceof LoggerAwareInterface) {
            $frontEndController->logger = $this->prophesize(LoggerInterface::class)->reveal();
        }
        $frontEndController->determineId();
        $frontEndController->cObj = $contentObject;
        if (Typo3Version::isNotHigherThan(8)) {
            $frontEndController->initTemplate();
        }

        $this->frontEndController = $frontEndController;
        $GLOBALS['TSFE'] = $frontEndController;

        return $frontEndController;
    }

    /*
     * Tests concerning the plugin definition
     */

    private function getContentRenderingConfiguration(): string
    {
        return (string)$GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];
    }

    /**
     * Extracts the class name from something like '...->foo'.
     *
     * @param string $reference
     *
     * @return string class name
     */
    private function extractClassNameFromUserFunction(string $reference): string
    {
        $parts = \explode('->', $reference);

        return \array_shift($parts);
    }

    /**
     * Extracts the method name from something like '...->foo'.
     *
     * @param string $reference
     *
     * @return string method name
     */
    private function extractMethodNameFromUserFunction(string $reference): string
    {
        $parts = \explode('->', $reference);

        return \array_pop($parts);
    }

    /**
     * @test
     */
    public function defaultContentRenderingIsGenerated()
    {
        $configuration = $this->getContentRenderingConfiguration();

        self::assertContains('TypoScript added by extension "seminars"', $configuration);
        self::assertContains('tt_content.list.20.seminars_pi1 = < plugin.tx_seminars_pi1', $configuration);
    }

    /**
     * @test
     */
    public function pluginUserFuncPointsToExistingMethodInExistingDefaultControllerClass()
    {
        $configuration = $this->getContentRenderingConfiguration();

        $matches = [];
        \preg_match('/plugin\\.tx_seminars_pi1\\.userFunc = ([^\\s]+)/', $configuration, $matches);
        $className = $this->extractClassNameFromUserFunction($matches[1]);
        $methodName = $this->extractMethodNameFromUserFunction($matches[1]);

        self::assertSame(\Tx_Seminars_FrontEnd_DefaultController::class, $className);

        self::assertTrue(
            \method_exists(new \Tx_Seminars_FrontEnd_DefaultController(), $methodName),
            'Method ' . $methodName . ' does not exist in class ' . $className
        );
    }

    /*
     * Tests concerning the list view
     */

    private function buildSubjectForListView(string $fixtureFileName): TestingDefaultController
    {
        $this->importDataSet(__DIR__ . '/Fixtures/' . $fixtureFileName . '.xml');

        $subject = new TestingDefaultController();
        $subject->cObj = $this->getFrontEndController()->cObj;
        $subject->init(
            [
                'isStaticTemplateLoaded' => 1,
                'enableRegistration' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'what_to_display' => 'seminar_list',
                'pages' => self::FOLDER_UID,
                'recursive' => 0,
                'listView.' => [
                    'orderBy' => 'data',
                    'descFlag' => 0,
                    'results_at_a_time' => 999,
                    'maxPages' => 5,
                ],
                'linkToSingleView' => 'always',
            ]
        );

        return $subject;
    }

    /**
     * @test
     */
    public function listViewShowsVisibleSingleEvent()
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertContains('test event', $result);
    }

    /**
     * @test
     */
    public function listViewEncodesEventTitle()
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertContains('test &amp; event', $result);
    }

    /**
     * @test
     */
    public function listViewHidesHiddenSingleEvent()
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertNotContains('hidden single event', $result);
    }

    /**
     * @test
     */
    public function listViewHidesDeletedSingleEvent()
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertNotContains('deleted single event', $result);
    }

    /**
     * @test
     */
    public function listViewByDefaultShowsEventWithCategory()
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');

        $result = $subject->main('', []);

        self::assertContains('visible event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByDefaultShowsEventWithType()
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');

        $result = $subject->main('', []);

        self::assertContains('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByDefaultShowsEventWithPlace()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');

        $result = $subject->main('', []);

        self::assertContains('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewShowsVisibleTopicByTopicTitle()
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertContains('test topic', $result);
    }

    /**
     * @test
     */
    public function listViewNotShowsDateTitle()
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertNotContains('test date', $result);
    }

    /**
     * @test
     */
    public function listViewHidesHiddenDate()
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertNotContains('another topic', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryShowsSingleEventWithSelectedCategory()
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertContains('visible event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesSingleEventWithoutCategory()
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertNotContains('visible event without category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesSingleEventOnlyWithOtherCategory()
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '2';

        $result = $subject->main('', []);

        self::assertNotContains('visible event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesHiddenSingleEventWithSelectedCategory()
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertNotContains('hidden event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesDeletedSingleEventWithSelectedCategory()
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertNotContains('deleted event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeShowsSingleEventWithSelectedType()
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertContains('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeShowsSingleEventWithOneOfMultipleSelectedTypes()
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1', '2'];

        $result = $subject->main('', []);

        self::assertContains('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesSingleEventWithoutType()
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertNotContains('visible event without type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesSingleEventWithOtherType()
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['2'];

        $result = $subject->main('', []);

        self::assertNotContains('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesHiddenSingleEventWithSelectedType()
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertNotContains('hidden event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesDeletedSingleEventWithSelectedType()
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertNotContains('deleted event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceShowsSingleEventWithSelectedPlace()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertContains('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceShowsSingleEventWithOneOfMultipleSelectedPlaces()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1,2');

        $result = $subject->main('', []);

        self::assertContains('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceShowsSingleEventWithAllSelectedPlaces()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1,2');

        $result = $subject->main('', []);

        self::assertContains('visible event with two places', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesSingleEventWithoutPlace()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertNotContains('visible event without place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesSingleEventOnlyWithOtherPlace()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '2');

        $result = $subject->main('', []);

        self::assertNotContains('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesHiddenSingleEventWithSelectedPlace()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertNotContains('hidden event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesDeletedSingleEventWithSelectedPlace()
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertNotContains('deleted event with place', $result);
    }
}
