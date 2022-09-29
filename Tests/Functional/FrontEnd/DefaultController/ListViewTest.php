<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd\DefaultController;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\FrontEnd\Fixtures\TestingDefaultController;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\DefaultController
 */
final class ListViewTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->initializeBackEndLanguage();
    }

    protected function tearDown(): void
    {
        RegistrationManager::purgeInstance();
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    private function buildSubjectForListView(string $fixtureFileName): TestingDefaultController
    {
        $this->importDataSet(__DIR__ . '/Fixtures/' . $fixtureFileName . '.xml');
        $this->testingFramework->createFakeFrontEnd(1);

        $frontEndController = $this->getFrontEndController();
        $subject = new TestingDefaultController();
        $subject->cObj = $frontEndController->cObj;
        $subject->init(
            [
                'isStaticTemplateLoaded' => 1,
                'enableRegistration' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'what_to_display' => 'seminar_list',
                'pages' => 1,
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

    // Tests concerning the list view

    /**
     * @test
     */
    public function listViewShowsVisibleSingleEvent(): void
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertStringContainsString('test event', $result);
    }

    /**
     * @test
     */
    public function listViewEncodesEventTitle(): void
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertStringContainsString('test &amp; event', $result);
    }

    /**
     * @test
     */
    public function listViewHidesHiddenSingleEvent(): void
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('hidden single event', $result);
    }

    /**
     * @test
     */
    public function listViewHidesDeletedSingleEvent(): void
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('deleted single event', $result);
    }

    /**
     * @test
     */
    public function listViewByDefaultShowsEventWithCategory(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByDefaultShowsEventWithType(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByDefaultShowsEventWithPlace(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewShowsVisibleTopicByTopicTitle(): void
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertStringContainsString('test topic', $result);
    }

    /**
     * @test
     */
    public function listViewNotShowsDateTitle(): void
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('test date', $result);
    }

    /**
     * @test
     */
    public function listViewHidesHiddenDate(): void
    {
        $subject = $this->buildSubjectForListView('EventList');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('another topic', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryShowsSingleEventWithSelectedCategory(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesSingleEventWithoutCategory(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertStringNotContainsString('visible event without category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesSingleEventOnlyWithOtherCategory(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '2';

        $result = $subject->main('', []);

        self::assertStringNotContainsString('visible event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesHiddenSingleEventWithSelectedCategory(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertStringNotContainsString('hidden event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByCategoryHidesDeletedSingleEventWithSelectedCategory(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithCategories');
        $subject->piVars['category'] = '1';

        $result = $subject->main('', []);

        self::assertStringNotContainsString('deleted event with category', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeShowsSingleEventWithSelectedType(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeShowsSingleEventWithOneOfMultipleSelectedTypes(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1', '2'];

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesSingleEventWithoutType(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertStringNotContainsString('visible event without type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesSingleEventWithOtherType(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['2'];

        $result = $subject->main('', []);

        self::assertStringNotContainsString('visible event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesHiddenSingleEventWithSelectedType(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertStringNotContainsString('hidden event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByTypeHidesDeletedSingleEventWithSelectedType(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithTypes');
        $subject->piVars['event_type'] = ['1'];

        $result = $subject->main('', []);

        self::assertStringNotContainsString('deleted event with first type', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceShowsSingleEventWithSelectedPlace(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceShowsSingleEventWithOneOfMultipleSelectedPlaces(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1,2');

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceShowsSingleEventWithAllSelectedPlaces(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1,2');

        $result = $subject->main('', []);

        self::assertStringContainsString('visible event with two places', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesSingleEventWithoutPlace(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('visible event without place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesSingleEventOnlyWithOtherPlace(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '2');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('visible event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesHiddenSingleEventWithSelectedPlace(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('hidden event with place', $result);
    }

    /**
     * @test
     */
    public function listViewByPlaceHidesDeletedSingleEventWithSelectedPlace(): void
    {
        $subject = $this->buildSubjectForListView('EventListWithPlaces');
        $subject->setConfigurationValue('limitListViewToPlaces', '1');

        $result = $subject->main('', []);

        self::assertStringNotContainsString('deleted event with place', $result);
    }
}
