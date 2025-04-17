<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Controller;

use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\FrontEndEditorController
 */
final class FrontEndEditorControllerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/FrontEndEditorContentElement.csv');

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
    public function indexActionWithoutLoggedInUserDoesNotRenderEventsWithoutOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithoutOwner.csv');

        $request = (new InternalRequest())->withPageId(8);
        $response = $this->executeFrontendSubRequest($request);

        self::assertStringNotContainsString('event without owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithoutLoggedInUserDoesNotRenderEventsWithOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithOwner.csv');

        $request = (new InternalRequest())->withPageId(8);
        $response = $this->executeFrontendSubRequest($request);

        self::assertStringNotContainsString('event with owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserDoesNotRenderEventsWithoutOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithoutOwner.csv');

        $request = (new InternalRequest())->withPageId(8);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('event without owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserDoesNotRenderEventsFromOtherOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventFromDifferentOwner.csv');

        $request = (new InternalRequest())->withPageId(8);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('event from different owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserRendersEventsFromOwnedByLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithOwner.csv');

        $request = (new InternalRequest())->withPageId(8);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('event with owner', (string)$response->getBody());
    }
}
