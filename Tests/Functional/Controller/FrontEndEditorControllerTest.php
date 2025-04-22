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
    /**
     * @var positive-int
     */
    private const PAGE_UID = 8;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    protected array $coreExtensionsToLoad = [
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

    private function getTrustedPropertiesFromEditForm(int $eventUid, int $userUid): string
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'edit',
            'tx_seminars_frontendeditor[event]' => $eventUid,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesFromHtml($html);
    }

    private function getTrustedPropertiesFromNewForm(int $userUid): string
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'new',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesFromHtml($html);
    }

    private function getTrustedPropertiesFromHtml(string $html): string
    {
        $matches = [];
        \preg_match('/__trustedProperties]" value="([a-zA-Z0-9&{};:,_]+)"/', $html, $matches);
        if (!isset($matches[1])) {
            throw new \RuntimeException('Could not fetch trustedProperties from returned HTML.', 1744911802);
        }

        return \html_entity_decode($matches[1]);
    }

    /**
     * @test
     */
    public function indexActionWithoutLoggedInUserDoesNotRenderEventsWithoutOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithoutOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $response = $this->executeFrontendSubRequest($request);

        self::assertStringNotContainsString('event without owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithoutLoggedInUserDoesNotRenderEventsWithOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $response = $this->executeFrontendSubRequest($request);

        self::assertStringNotContainsString('event with owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserDoesNotRenderEventsWithoutOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithoutOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
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

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
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

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('event with owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function editActionWithOwnEventAssignsProvidedEventToView(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'edit',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString(
            '<input type="hidden" name="tx_seminars_frontendeditor[event][__identity]" value="1" />',
            $html
        );
        self::assertStringContainsString('event with owner', $html);
    }

    /**
     * @test
     */
    public function editActionWithEventFromOtherUserThrowsException(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/editAction/EventFromDifferentOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'edit',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->executeFrontendSubRequest($request, $context);
    }

    /**
     * @test
     */
    public function editActionWithEventWithoutOwnerThrowsException(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithoutOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'edit',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You do not have permission to edit this event.');
        $this->expectExceptionCode(1666954310);

        $this->executeFrontendSubRequest($request, $context);
    }

    /**
     * @test
     */
    public function updateActionWithOwnEventUpdatesEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventWithOwner.csv');

        $newTitle = 'Karaoke party';
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromEditForm(1, 1),
            'tx_seminars_frontendeditor[action]' => 'update',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => $newTitle,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        self::assertSame($newTitle, $this->getAllRecords('tx_seminars_seminars')[0]['title'] ?? null);
    }

    /**
     * @test
     */
    public function newActionWithNoProvidedEventCanBeRendered(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'new',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('Create new event', $html);
    }

    /**
     * @test
     */
    public function newActionWithEventProvidedRendersProvidedEventData(): void
    {
        $newTitle = 'Karaoke party';
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromNewForm(1),
            'tx_seminars_frontendeditor[action]' => 'new',
            'tx_seminars_frontendeditor[event][internalTitle]' => $newTitle,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString($newTitle, $html);
    }

    /**
     * @test
     */
    public function createActionSetsLoggedInUserAsOwnerOfProvidedEvent(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromNewForm(1),
            'tx_seminars_frontendeditor[action]' => 'create',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        self::assertSame(1, $this->getAllRecords('tx_seminars_seminars')[0]['owner_feuser'] ?? null);
    }
}
