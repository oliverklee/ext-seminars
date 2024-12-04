<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Controller;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\EventController
 */
final class EventControllerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected array $coreExtensionsToLoad = [
        'typo3/cms-extensionmanager',
        'typo3/cms-fluid-styled-content',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/seminars/Tests/Functional/Controller/Fixtures/Sites/' => 'typo3conf/sites',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Sites/SiteStructure.csv');
        $this->setUpFrontendRootPage(1, [
            'constants' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/constants.typoscript',
                'EXT:seminars/Configuration/TypoScript/constants.typoscript',
            ],
            'setup' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
                'EXT:seminars/Configuration/TypoScript/setup.typoscript',
                'EXT:seminars/Tests/Functional/Controller/Fixtures/TypoScript/Setup/Rendering.typoscript',
                'EXT:seminars/Tests/Functional/Controller/Fixtures/TypoScript/Setup/PluginConfiguration.typoscript',
            ],
        ]);
    }

    /**
     * @test
     */
    public function archiveActionForNoEventsShowsMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $expectedMessage = LocalizationUtility::translate('plugin.eventArchive.message.noEventsFound', 'seminars');
        self::assertIsString($expectedMessage);
        self::assertStringContainsString($expectedMessage, $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersTitleOfPastSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEvent.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function archiveActionDoesDoesNotRenderFutureSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/FutureEvent.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringNotContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersPastDateWithTitleFromTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastDateWithTopic.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersDateOfSingleDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/SingleDayPastEvent.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2024-11-03', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersStartAndEndOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/MultiDayPastEvent.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2024-11-02–2024-11-03', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersCityOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEventWithOneVenue.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Bonn', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersAllCitiesOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/archiveAction/PastEventWithTwoVenuesInDifferentCities.csv'
        );

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Bonn', $html);
        self::assertStringContainsString('Köln', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersOnlyOneCityForMultipleVenuesInSameCity(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/archiveAction/PastEventWithTwoVenuesInSameCity.csv'
        );

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Bonn', $html);
        self::assertStringNotContainsString('Bonn,', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersTitleOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEventWithOneVenue.csv');

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersAllTitlesOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/archiveAction/PastEventWithTwoVenuesInDifferentCities.csv'
        );

        $request = new InternalRequest();
        $request = $request->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
        self::assertStringContainsString('Premier Inn', $html);
    }
}
