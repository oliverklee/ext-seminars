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

    protected array $configurationToUseInTestInstance = [
        'FE' => [
            'cacheHash' => [
                'enforceValidation' => false,
            ],
        ],
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

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $expectedMessage = LocalizationUtility::translate('plugin.eventArchive.message.noEventsFound', 'seminars');
        self::assertIsString($expectedMessage);
        self::assertStringContainsString($expectedMessage, $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersTitleOfPastSingleEventInStorageFolder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersTitleOfPastSingleEventInSubfolderOfStorageFolder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEventInSubfolder.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function archiveActionIgnoresEventInOtherFolder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEventInOtherFolder.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringNotContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function archiveActionLinksEventTitleToSingleView(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertMatchesRegularExpression('#<a href="/event-single-view/1">.*Extension Development#s', $html);
    }

    /**
     * @test
     */
    public function archiveActionDoesDoesNotRenderFutureSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/FutureEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

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

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
        self::assertStringContainsString('Premier Inn', $html);
    }

    /**
     * @test
     */
    public function archiveActionRendersEventType(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/EventArchiveContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/PastEventWithEventType.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('workshop', $html);
    }

    /**
     * @test
     */
    public function outlookActionForNoEventsShowsMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $expectedMessage = LocalizationUtility::translate('plugin.eventArchive.message.noEventsFound', 'seminars');
        self::assertIsString($expectedMessage);
        self::assertStringContainsString($expectedMessage, $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersTitleOfFutureSingleEventInStorageFolder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersTitleOfFutureSingleEventInSubfolderOfStorageFolder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventInSubfolder.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function outlookActionIgnoresEventInOtherFolder(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventInOtherFolder.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringNotContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function outlookActionLinksEventTitleToSingleView(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertMatchesRegularExpression('#<a href="/event-single-view/1">.*Extension Development#s', $html);
    }

    /**
     * @test
     */
    public function outlookActionDoesDoesNotRenderPastSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/PastEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringNotContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersFutureDateWithTitleFromTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureDateWithTopic.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Extension Development with Extbase and Fluid', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersDateOfSingleDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/SingleDayFutureEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2039-12-01', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersStartAndEndOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/MultiDayFutureEvent.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2039-12-01–2039-12-02', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersCityOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithOneVenue.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Bonn', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersAllCitiesOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithTwoVenuesInDifferentCities.csv'
        );

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Bonn', $html);
        self::assertStringContainsString('Köln', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersOnlyOneCityForMultipleVenuesInSameCity(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithTwoVenuesInSameCity.csv'
        );

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Bonn', $html);
        self::assertStringNotContainsString('Bonn,', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersTitleOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithOneVenue.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersAllTitlesOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithTwoVenuesInDifferentCities.csv'
        );

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
        self::assertStringContainsString('Premier Inn', $html);
    }

    /**
     * @test
     */
    public function outlookActionRendersEventType(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithEventType.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('workshop', $html);
    }

    /**
     * @test
     */
    public function outlookActionForEventWithUnlimitedSeatsRendersUnlimitedSeats(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithUnlimitedSeats.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $unlimited = LocalizationUtility::translate(
            'plugin.eventOutlook.events.property.vacancies.unlimited',
            'seminars'
        );
        self::assertIsString($unlimited);
        self::assertStringContainsString($unlimited, $html);
        self::assertStringContainsString('🟢', $html);
    }

    /**
     * @test
     */
    public function outlookActionForEventWithMoreThanEnoughVacanciesRendersThreshold(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithMoreThanEnoughVacancies.csv'
        );

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('≥ 5 🟢', $html);
    }

    /**
     * @test
     */
    public function outlookActionForEventWithExactlyEnoughVacanciesRendersRendersThreshold(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithExactlyEnoughVacancies.csv'
        );

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('≥ 5 🟢', $html);
    }

    /**
     * @test
     */
    public function outlookActionForEventWithLessThanEnoughVacanciesRendersExactVacancies(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithLessThanEnoughVacancies.csv'
        );

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('4 🟡', $html);
    }

    /**
     * @test
     */
    public function outlookActionForEventWithOneVacancyRendersExactVacancies(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithOneVacancy.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('1 🟡', $html);
    }

    /**
     * @test
     */
    public function outlookActionForEventWithNoVacancyRendersFullyBooked(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/EventOutlookContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/outlookAction/FutureEventWithNoVacancies.csv');

        $request = (new InternalRequest())->withPageId(1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $fullyBooked = LocalizationUtility::translate(
            'plugin.eventOutlook.events.property.vacancies.fullyBooked',
            'seminars'
        );
        self::assertIsString($fullyBooked);
        self::assertStringContainsString($fullyBooked, $html);
        self::assertStringContainsString('🔴', $html);
    }

    /**
     * @test
     */
    public function showActionRendersTitleOfFutureSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertMatchesRegularExpression('#<h1>\\s*Extension Development#s', $html);
    }

    /**
     * @test
     */
    public function showActionRendersTitleOfPastSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/PastEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertMatchesRegularExpression('#<h1>\\s*Extension Development#s', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDisplayTitleOfEventDate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureDateWithTopic.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertMatchesRegularExpression('#<h1>\\s*Extension Development#s', $html);
        self::assertStringNotContainsString('date record', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDisplayTitleOfEventTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertMatchesRegularExpression('#<h1>\\s*Extension Development#s', $html);
    }

    /**
     * @test
     */
    public function showActionRendersEventType(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithEventType.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('workshop', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDateOfSingleDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/SingleDayPastEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2024-11-03', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDateOfSingleDayEventOnlyOnce(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/SingleDayPastEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertSame(1, \substr_count($html, '2024-11-03'));
    }

    /**
     * @test
     */
    public function showActionRendersStartAndEndOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/MultiDayPastEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2024-11-02–2024-11-03', $html);
    }

    /**
     * @test
     */
    public function showActionRendersStartAndEndTimeOfSingleDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/SingleDayPastEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('09:00–17:00', $html);
    }

    /**
     * @test
     */
    public function showActionRendersStartDateAndTimeOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/MultiDayPastEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2024-11-02', $html);
        self::assertStringContainsString('09:00', $html);
    }

    /**
     * @test
     */
    public function showActionRendersEndDateAndTimeOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/archiveAction/MultiDayPastEvent.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('2024-11-03', $html);
        self::assertStringContainsString('17:00', $html);
    }

    /**
     * @test
     */
    public function showActionRendersTitleOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/PastEventWithOneVenue.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
    }

    /**
     * @test
     */
    public function showActionRendersAddressOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/PastEventWithOneVenue.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Kurt-Georg-Kiesinger-Allee 1', $html);
        self::assertStringContainsString('53175 Bonn', $html);
    }

    /**
     * @test
     */
    public function showActionConvertsNewlinesToBreakInVenuAddress(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/PastEventWithOneVenue.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Kurt-Georg-Kiesinger-Allee 1<br />', $html);
    }

    /**
     * @test
     */
    public function showActionCanRenderMultipleVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/PastEventWithTwoVenuesInSameCity.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
        self::assertStringContainsString('Kameha Grand', $html);
    }

    /**
     * @test
     */
    public function showActionRendersRoom(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithAllScalarData.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('room 13 B', $html);
    }

    /**
     * @test
     */
    public function showActionForEventWithUnlimitedSeatsRendersUnlimitedSeats(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithUnlimitedSeats.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $unlimited = LocalizationUtility::translate(
            'plugin.eventSingleView.events.property.vacancies.unlimited',
            'seminars'
        );
        self::assertIsString($unlimited);
        self::assertStringContainsString($unlimited, $html);
        self::assertStringContainsString('🟢', $html);
    }

    /**
     * @test
     */
    public function showActionForEventWithMoreThanEnoughVacanciesRendersThreshold(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/showAction/FutureEventWithMoreThanEnoughVacancies.csv'
        );

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('≥ 5 🟢', $html);
    }

    /**
     * @test
     */
    public function showActionForEventWithExactlyEnoughVacanciesRendersRendersThreshold(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/showAction/FutureEventWithExactlyEnoughVacancies.csv'
        );

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('≥ 5 🟢', $html);
    }

    /**
     * @test
     */
    public function showActionForEventWithLessThanEnoughVacanciesRendersExactVacancies(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/showAction/FutureEventWithLessThanEnoughVacancies.csv'
        );

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('4 🟡', $html);
    }

    /**
     * @test
     */
    public function showActionForEventWithOneVacancyRendersExactVacancies(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithOneVacancy.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('1 🟡', $html);
    }

    /**
     * @test
     */
    public function showActionForEventWithNoVacancyRendersFullyBooked(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithNoVacancies.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $fullyBooked = LocalizationUtility::translate(
            'plugin.eventSingleView.events.property.vacancies.fullyBooked',
            'seminars'
        );
        self::assertIsString($fullyBooked);
        self::assertStringContainsString($fullyBooked, $html);
        self::assertStringContainsString('🔴', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDescriptionAsRichText(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithAllScalarData.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('a <b>big</b> event', $html);
    }

    /**
     * @test
     */
    public function showActionRendersNameOfSpeaker(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithOneSpeaker.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Oliver Klee', $html);
    }

    /**
     * @test
     */
    public function showActionCanRenderMultipleSpeakers(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithTwoSpeakers.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('Oliver Klee', $html);
        self::assertStringContainsString('Bilbo Baggins', $html);
    }

    /**
     * @test
     */
    public function showActionRendersOrganizationOfSpeaker(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithOneSpeaker.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('[oliverklee.de] TYPO3 und Workshops', $html);
    }

    /**
     * @test
     */
    public function showActionRendersExternalHomepageOfSpeakerAsLink(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/showAction/FutureEventWithSpeakerWithExternalHomepage.csv'
        );

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('href="https://www.oliverklee.de/', $html);
    }

    /**
     * @test
     */
    public function showActionRendersInternalHomepageOfSpeakerAsLink(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/EventController/showAction/FutureEventWithSpeakerWithInternalHomepage.csv'
        );

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        self::assertStringContainsString('href="/speaker-details', $html);
    }

    /**
     * @test
     */
    public function showActionWithOneSpeakerUsesSingularHeading(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithOneSpeaker.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $expectedLabel = LocalizationUtility::translate(
            'plugin.eventSingleView.events.property.speakers.one',
            'seminars'
        );
        self::assertIsString($expectedLabel);
        self::assertStringContainsString($expectedLabel, $html);
    }

    /**
     * @test
     */
    public function showActionWithTwoSpeakersUsesPluralHeading(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/EventSingleViewContentElement.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/EventController/showAction/FutureEventWithTwoSpeakers.csv');

        $request = (new InternalRequest())->withPageId(3)
            ->withQueryParameter('tx_seminars_eventsingleview[event]', 1);

        $html = (string)$this->executeFrontendSubRequest($request)->getBody();

        $expectedLabel = LocalizationUtility::translate(
            'plugin.eventSingleView.events.property.speakers.many',
            'seminars'
        );
        self::assertIsString($expectedLabel);
        self::assertStringContainsString($expectedLabel, $html);
    }
}
