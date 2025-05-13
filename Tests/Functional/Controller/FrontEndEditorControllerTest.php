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
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => $eventUid,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesFromHtml($html);
    }

    private function getTrustedPropertiesFromNewForm(int $userUid): string
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesFromHtml($html);
    }

    private function getTrustedPropertiesFromHtml(string $html): string
    {
        $matches = [];
        \preg_match('/__trustedProperties]" value="([a-zA-Z0-9&{};:,_\\[\\]]+)"/', $html, $matches);
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
     * @return array<string, array{0: non-empty-string}>
     */
    public static function nonDateFormFieldKeysForSingleEventDataProvider(): array
    {
        return [
            'internalTitle' => ['internalTitle'],
            'description' => ['description'],
            'registrationRequired' => ['registrationRequired'],
            'waitingList' => ['waitingList'],
            'minimumNumberOfRegistrations' => ['minimumNumberOfRegistrations'],
            'maximumNumberOfRegistrations' => ['maximumNumberOfRegistrations'],
            'numberOfOfflineRegistrations' => ['numberOfOfflineRegistrations'],
            'standardPrice' => ['standardPrice'],
            'earlyBirdPrice' => ['earlyBirdPrice'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider nonDateFormFieldKeysForSingleEventDataProvider
     */
    public function editSingleEventActionHasAllNonDateFormFields(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function dateFormFieldKeysForSingleEventDataProvider(): array
    {
        return [
            'start' => ['start'],
            'end' => ['end'],
            'earlyBirdDeadline' => ['earlyBirdDeadline'],
            'registrationDeadline' => ['registrationDeadline'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider dateFormFieldKeysForSingleEventDataProvider
     */
    public function editSingleEventActionHasAllDateFormFields(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . '][date]"', $html);
        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . '][dateFormat]"', $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function singleAssociationFormFieldKeysForSingleEventDataProvider(): array
    {
        return [
            'eventType' => ['eventType'],
        ];
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function multiAssociationFormFieldKeysForSingleEventDataProvider(): array
    {
        return [
            'venues' => ['venues'],
            'speakers' => ['speakers'],
            'organizers' => ['organizers'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider singleAssociationFormFieldKeysForSingleEventDataProvider
     * @dataProvider multiAssociationFormFieldKeysForSingleEventDataProvider
     */
    public function editSingleEventActionHasAllAssociationFormFields(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider multiAssociationFormFieldKeysForSingleEventDataProvider
     */
    public function editSingleEventActionForEventWithAllAssociationsHasSelectedMultiAssociationOptions(
        string $key
    ): void {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwnerAndAllAssociations.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString(
            'name="tx_seminars_frontendeditor[event][' . $key . '][]" value="1" checked="checked"',
            $html
        );
    }

    /**
     * @test
     */
    public function editSingleEventActionForEventWithAllAssociationsHasSelectedEventTypeOption(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwnerAndAllAssociations.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('<option value="1" selected="selected">workshop</option>', $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function auxiliaryRecordTitlesDataProvider(): array
    {
        return [
            'eventType' => ['workshop'],
            'venues' => ['Jugendherberge Bonn'],
            'speakers' => ['Ned Knowledge'],
            'organizers' => ['Training Inc.'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $title
     * @dataProvider auxiliaryRecordTitlesDataProvider
     */
    public function editSingleEventActionHasTitlesOfAuxiliaryRecords(string $title): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString($title, $html);
    }

    /**
     * @test
     */
    public function editSingleEventActionWithOwnEventAssignsProvidedEventToView(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
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
    public function editSingleEventActionForUserWithDefaultOrganizerHasNoOrganizerFormField(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/FrontEndUserWithDefaultOrganizer.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwnerWithDefaultOrganizer.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][organizers]"', $html);
    }

    /**
     * @test
     */
    public function editSingleEventActionWithOwnEventRendersNumberOfOfflineRegistrations(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('value="59"', $html);
    }

    /**
     * @test
     */
    public function editSingleEventActionWithEventFromOtherUserThrowsException(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventFromDifferentOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
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
    public function editSingleEventActionWithEventWithoutOwnerThrowsException(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithoutOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
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
    public function updateSingleEventActionWithOwnEventUpdatesEvent(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromEditForm(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][numberOfOfflineRegistrations]' => '5',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/UpdatedEvent.csv'
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionCanSetOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromEditForm(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][organizers]' => '',
            'tx_seminars_frontendeditor[event][organizers][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/UpdatedEventWithOrganizer.csv'
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionForUserWithDefaultOrganizerKeepsOrganizerUnchanged(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/FrontEndUserWithDefaultOrganizer.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwnerWithDefaultOrganizer.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromEditForm(1, 2),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'event with owner',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwnerWithDefaultOrganizer.csv'
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionUpdatesSlug(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv'
        );

        $newTitle = 'Karaoke party';
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromEditForm(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => $newTitle,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/UpdatedEventWithSlug.csv'
        );
    }

    /**
     * @test
     */
    public function newSingleEventActionCanBeRendered(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('Create new event', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider nonDateFormFieldKeysForSingleEventDataProvider
     */
    public function newSingleEventActionHasAllNonDateFormFields(string $key): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider dateFormFieldKeysForSingleEventDataProvider
     */
    public function newSingleEventActionHasAllDateFormFields(string $key): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . '][date]"', $html);
        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . '][dateFormat]"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider multiAssociationFormFieldKeysForSingleEventDataProvider
     * @dataProvider singleAssociationFormFieldKeysForSingleEventDataProvider
     */
    public function newSingleEventActionHasAllAssociationFormFields(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/AuxiliaryRecords.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     */
    public function newSingleEventActionForUserWithDefaultOrganizerHasNoOrganizerFormField(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/FrontEndUserWithDefaultOrganizer.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][organizers]"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $title
     * @dataProvider auxiliaryRecordTitlesDataProvider
     */
    public function newSingleEventActionHasTitlesOfAuxiliaryRecords(string $title): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/AuxiliaryRecords.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString($title, $html);
    }

    /**
     * @test
     */
    public function createSingleEventActionSetsLoggedInUserAsOwnerOfProvidedEvent(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromNewForm(1),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/NewlyCreatedEvent.csv'
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionSetsSlug(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromNewForm(1),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithSlug.csv'
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionCanSetNumberOfOfflineRegistrations(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromNewForm(1),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][numberOfOfflineRegistrations]' => '3',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithOfflineRegistrations.csv'
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionCanSetOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/AuxiliaryRecords.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromNewForm(1),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][organizers]' => '',
            'tx_seminars_frontendeditor[event][organizers][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithOrganizer.csv'
        );
    }

    /**
     * @test
     */
    public function createSingleEventForUserWithDefaultOrganizerSetsDefaultOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/AuxiliaryRecords.csv'
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/FrontEndUserWithDefaultOrganizer.csv'
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesFromNewForm(2),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithDefaultOrganizer.csv'
        );
    }
}
