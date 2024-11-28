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
    public function archiveActionRendersPastSingleEvent(): void
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
}
