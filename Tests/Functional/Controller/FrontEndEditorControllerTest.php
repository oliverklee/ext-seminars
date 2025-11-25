<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Controller;

use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
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

    /**
     * @var array<non-empty-string, 1|array<non-empty-string, 1>>
     */
    private const FORM_ELEMENTS_FOR_SINGLE_EVENT_FORM = [
        'internalTitle' => 1,
        'description' => 1,
        'eventType' => 1,
        'start' => ['date' => 1, 'dateFormat' => 1],
        'end' => ['date' => 1, 'dateFormat' => 1],
        'earlyBirdDeadline' => ['date' => 1, 'dateFormat' => 1],
        'registrationDeadline' => ['date' => 1, 'dateFormat' => 1],
        'registrationRequired' => 1,
        'waitingList' => 1,
        'minimumNumberOfRegistrations' => 1,
        'maximumNumberOfRegistrations' => 1,
        'numberOfOfflineRegistrations' => 1,
        'standardPrice' => 1,
        'earlyBirdPrice' => 1,
    ];

    /**
     * @var array<non-empty-string, 1|array<non-empty-string, 1>>
     */
    private const FORM_ELEMENTS_FOR_EVENT_DATE_FORM = [
        'internalTitle' => 1,
        'topic' => 1,
        'start' => ['date' => 1, 'dateFormat' => 1],
        'end' => ['date' => 1, 'dateFormat' => 1],
        'earlyBirdDeadline' => ['date' => 1, 'dateFormat' => 1],
        'registrationDeadline' => ['date' => 1, 'dateFormat' => 1],
        'registrationRequired' => 1,
        'waitingList' => 1,
        'minimumNumberOfRegistrations' => 1,
        'maximumNumberOfRegistrations' => 1,
        'numberOfOfflineRegistrations' => 1,
    ];

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

    /**
     * @param positive-int $eventUid
     * @param positive-int $userUid
     */
    private function getTrustedPropertiesForEditSingleEventFormLegacy(int $eventUid, int $userUid): string
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => $eventUid,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesForHtml($html);
    }

    /**
     * @param positive-int $eventUid
     */
    private function getTrustedPropertiesForEditSingleEventForm(int $eventUid): string
    {
        $stuff = self::FORM_ELEMENTS_FOR_SINGLE_EVENT_FORM;
        $stuff['__identity'] = $eventUid;

        return $this->getTrustedPropertiesForFormInput(['event' => $stuff]);
    }

    /**
     * @param positive-int $userUid
     */
    private function getTrustedPropertiesForNewSingleEventFormLegacy(int $userUid): string
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesForHtml($html);
    }

    /**
     * @return non-empty-string
     */
    private function getTrustedPropertiesForNewSingleEventForm(): string
    {
        $stuff = self::FORM_ELEMENTS_FOR_SINGLE_EVENT_FORM;

        return $this->getTrustedPropertiesForFormInput(['event' => $stuff]);
    }

    /**
     * @param positive-int $eventUid
     * @param positive-int $userUid
     *
     * @return non-empty-string
     */
    private function getTrustedPropertiesForEditEventDateFormLegacy(int $eventUid, int $userUid): string
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => $eventUid,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesForHtml($html);
    }

    /**
     * @param positive-int $eventUid
     *
     * @return non-empty-string
     */
    private function getTrustedPropertiesForEditEventDateForm(int $eventUid): string
    {
        $stuff = self::FORM_ELEMENTS_FOR_EVENT_DATE_FORM;
        $stuff['__identity'] = $eventUid;

        return $this->getTrustedPropertiesForFormInput(['event' => $stuff]);
    }

    /**
     * @param positive-int $userUid
     *
     * @return non-empty-string
     */
    private function getTrustedPropertiesForNewEventDateFormLegacy(int $userUid): string
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId($userUid);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        return $this->getTrustedPropertiesForHtml($html);
    }

    /**
     * @return non-empty-string
     */
    private function getTrustedPropertiesForNewEventDateForm(): string
    {
        $stuff = self::FORM_ELEMENTS_FOR_EVENT_DATE_FORM;

        return $this->getTrustedPropertiesForFormInput(['event' => $stuff]);
    }

    //    /**
    //     * @test
    //     */
    //    public function newSingleEventFormHashesAreTheSame(): void
    //    {
    //        $legacyHash = $this->getTrustedPropertiesForNewSingleEventFormLegacy(1);
    //        $newHash = $this->getTrustedPropertiesForNewSingleEventForm();
    //
    //        self::assertSame($legacyHash, $newHash);
    //    }
    //
    //    /**
    //     * @test
    //     */
    //    public function editSingleEventFormHashesAreTheSame(): void
    //    {
    //        $this->importCSVDataSet(
    //            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv',
    //        );
    //        $legacyHash = $this->getTrustedPropertiesForEditSingleEventFormLegacy(1, 1);
    //        $newHash = $this->getTrustedPropertiesForEditSingleEventForm(1);
    //
    //        self::assertSame($legacyHash, $newHash);
    //    }
    //
    //    /**
    //     * @test
    //     */
    //    public function newEventDateFormHashesAreTheSame(): void
    //    {
    //        $legacyHash = $this->getTrustedPropertiesForNewEventDateFormLegacy(1);
    //        $newHash = $this->getTrustedPropertiesForNewEventDateForm();
    //
    //        self::assertSame($legacyHash, $newHash);
    //    }
    //
    //    /**
    //     * @test
    //     */
    //    public function editEventDateFormHashesAreTheSame(): void
    //    {
    //        $this->importCSVDataSet(
    //            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithOwner.csv',
    //        );
    //        $legacyHash = $this->getTrustedPropertiesForEditEventDateFormLegacy(1, 1);
    //        $newHash = $this->getTrustedPropertiesForEditEventDateForm(1);
    //
    //        self::assertSame($legacyHash, $newHash);
    //    }

    /**
     * @param array<non-empty-string, array<non-empty-string, int|array<non-empty-string, 1>>> $trustedProperties
     *
     * @return non-empty-string
     */
    private function getTrustedPropertiesForFormInput(array $trustedProperties): string
    {
        $result = $this
            ->get(HashService::class)
            ->appendHmac(\json_encode($trustedProperties, JSON_THROW_ON_ERROR));

        self::assertNotSame('', $result);

        return $result;
    }

    /**
     * @return non-empty-string
     */
    private function getTrustedPropertiesForHtml(string $html): string
    {
        $matches = [];
        \preg_match('/__trustedProperties]" value="([a-zA-Z0-9&{};:,_\\[\\]]+)"/', $html, $matches);
        if (!isset($matches[1])) {
            throw new \RuntimeException('Could not fetch trustedProperties from returned HTML.', 1744911802);
        }

        $result = \html_entity_decode($matches[1]);

        self::assertNotSame('', $result);

        return $result;
    }

    /**
     * @test
     */
    public function indexActionWithoutLoggedInUserDoesNotRenderEventsWithoutOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithoutOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $response = $this->executeFrontendSubRequest($request);

        self::assertStringNotContainsString('event without owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithoutLoggedInUserDoesNotRenderEventsWithOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $response = $this->executeFrontendSubRequest($request);

        self::assertStringNotContainsString('event with owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionHasHeadline(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.frontEndEditor.index.headline', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionByDefaultHasLinkToNewSingleEventAction(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=newSingleEvent'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor';
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionConfiguredForSingleEventsHasLinkToNewSingleEventAction(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/pageAndContentElementForCreatingSingleEvents.csv',
        );

        $request = (new InternalRequest())->withPageId(99);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=newSingleEvent'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor';
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionConfiguredForEventDatesHasLinkToNewEventDateAction(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/pageAndContentElementForCreatingEventDates.csv',
        );

        $request = (new InternalRequest())->withPageId(99);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=newEventDate'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor';
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserDoesNotRenderSingleEventsWithoutOwner(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithoutOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('event without owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserDoesNotRenderSingleEventsFromOtherOwner(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventFromDifferentOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('event from different owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserRendersSingleEventsOwnedByLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('event with owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserRendersEventDateOwnedByLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventDateWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('event date with owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserDoesNotRenderEventTopicOwnedByLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventTopicWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('event topic with owner', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersEventUid(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithOwnerAndHigherUid.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('1337', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersDateOfOneDayEvent(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/OneDaySingleEventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('2025-10-28', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersDateOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/TwoDaySingleEventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);
        $body = (string)$response->getBody();

        self::assertStringContainsString('2025-10-28', $body);
        self::assertStringContainsString('2025-10-29', $body);
    }

    /**
     * @test
     */
    public function indexActionForEventWithRegularRegistrationsRendersRegistrationCount(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithRegistrations.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);
        $body = (string)$response->getBody();

        self::assertStringContainsString(' 4', $body);
    }

    /**
     * @test
     */
    public function indexActionForEventWithVacanciesRendersVacanciesCount(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithVacancies.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);
        $body = (string)$response->getBody();

        self::assertStringContainsString('≥ 5 🟢', $body);
    }

    /**
     * @test
     */
    public function indexActionWithSingleEventHasEditSingleEventLink(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/SingleEventWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=editSingleEvent'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor'
            . '&amp;tx_seminars_frontendeditor%5Bevent%5D=1';
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithEventDateHasEditEventDateLink(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/indexAction/EventDateWithOwner.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=editEventDate'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor'
            . '&amp;tx_seminars_frontendeditor%5Bevent%5D=1';
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function editSingleEventActionHasUpdateSingleEventFormAction(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=updateSingleEvent'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor';
        self::assertStringContainsString($expected, $html);
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
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
            'categories' => ['categories'],
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
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
    public static function formFieldKeysIrrelevantForSingleEventsDataProvider(): array
    {
        return [
            'topic' => ['topic'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider formFieldKeysIrrelevantForSingleEventsDataProvider
     */
    public function editSingleEventActionHasNoFormFieldsIrrelevantForSingleEvents(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
        );
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwnerAndAllAssociations.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString(
            'name="tx_seminars_frontendeditor[event][' . $key . '][]" value="1" checked="checked"',
            $html,
        );
    }

    /**
     * @test
     */
    public function editSingleEventActionForEventWithAllAssociationsHasSelectedEventTypeOption(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwnerAndAllAssociations.csv',
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
    public static function auxiliaryRecordTitlesForSingleEventDataProvider(): array
    {
        return [
            'categories' => ['cooking'],
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
     * @dataProvider auxiliaryRecordTitlesForSingleEventDataProvider
     */
    public function editSingleEventActionHasTitlesOfAuxiliaryRecords(string $title): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editSingleEvent',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString(
            '<input type="hidden" name="tx_seminars_frontendeditor[event][__identity]" value="1" />',
            $html,
        );
        self::assertStringContainsString('event with owner', $html);
    }

    /**
     * @test
     */
    public function editSingleEventActionForUserWithDefaultOrganizerHasNoOrganizerFormField(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/FrontEndUserWithDefaultOrganizer.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwnerWithDefaultOrganizer.csv',
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithOwner.csv',
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventFromDifferentOwner.csv',
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
            __DIR__ . '/Fixtures/FrontEndEditorController/editSingleEventAction/EventWithoutOwner.csv',
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
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]'
            => $this->getTrustedPropertiesForEditSingleEventFormLegacy(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][numberOfOfflineRegistrations]' => '5',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/UpdatedEvent.csv',
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionKeepsPidUnchanged(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithDifferentPid.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]'
            => $this->getTrustedPropertiesForEditSingleEventFormLegacy(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithDifferentPid.csv',
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionCanSetOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]'
            => $this->getTrustedPropertiesForEditSingleEventFormLegacy(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][organizers]' => '',
            'tx_seminars_frontendeditor[event][organizers][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/UpdatedEventWithOrganizer.csv',
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionForUserWithDefaultOrganizerKeepsOrganizerUnchanged(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/FrontEndUserWithDefaultOrganizer.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwnerWithDefaultOrganizer.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]'
            => $this->getTrustedPropertiesForEditSingleEventFormLegacy(1, 2),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'event with owner',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwnerWithDefaultOrganizer.csv',
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionCanSetCategory(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]'
            => $this->getTrustedPropertiesForEditSingleEventFormLegacy(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][categories]' => '',
            'tx_seminars_frontendeditor[event][categories][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/UpdatedEventWithCategory.csv',
        );
    }

    /**
     * @test
     */
    public function updateSingleEventActionUpdatesSlug(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/EventWithOwner.csv',
        );

        $newTitle = 'Karaoke party';
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]'
            => $this->getTrustedPropertiesForEditSingleEventFormLegacy(1, 1),
            'tx_seminars_frontendeditor[action]' => 'updateSingleEvent',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => $newTitle,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateSingleEventAction/UpdatedEventWithSlug.csv',
        );
    }

    /**
     * @test
     */
    public function editEventDateActionHasUpdateEventDateFormAction(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=updateEventDate'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor';
        self::assertStringContainsString($expected, $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function nonDateFormFieldKeysForEventDateDataProvider(): array
    {
        return [
            'internalTitle' => ['internalTitle'],
            'registrationRequired' => ['registrationRequired'],
            'waitingList' => ['waitingList'],
            'minimumNumberOfRegistrations' => ['minimumNumberOfRegistrations'],
            'maximumNumberOfRegistrations' => ['maximumNumberOfRegistrations'],
            'numberOfOfflineRegistrations' => ['numberOfOfflineRegistrations'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider nonDateFormFieldKeysForEventDateDataProvider
     */
    public function editEventDateActionHasAllNonDateFormFields(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function dateFormFieldKeysForEventDateDataProvider(): array
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
     * @dataProvider dateFormFieldKeysForEventDateDataProvider
     */
    public function editEventDateActionHasAllDateFormFields(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
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
    public static function singleAssociationFormFieldKeysForEventDateDataProvider(): array
    {
        return [
            'topic' => ['topic'],
        ];
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function multiAssociationFormFieldKeysForEventDateDataProvider(): array
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
     * @dataProvider singleAssociationFormFieldKeysForEventDateDataProvider
     * @dataProvider multiAssociationFormFieldKeysForEventDateDataProvider
     */
    public function editEventDateActionHasAllAssociationFormFields(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/Topic.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function formFieldKeysIrrelevantForEventDatesDataProvider(): array
    {
        return [
            'description' => ['description'],
            'eventType' => ['eventType'],
            'categories' => ['categories'],
            'standardPrice' => ['standardPrice'],
            'earlyBirdPrice' => ['earlyBirdPrice'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider formFieldKeysIrrelevantForEventDatesDataProvider
     */
    public function editEventDateActionHasNoFormFieldsIrrelevantForEventDates(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider multiAssociationFormFieldKeysForEventDateDataProvider
     */
    public function editEventDateActionForEventWithAllAssociationsHasSelectedMultiAssociationOptions(
        string $key
    ): void {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwnerAndAllAssociations.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString(
            'name="tx_seminars_frontendeditor[event][' . $key . '][]" value="1" checked="checked"',
            $html,
        );
    }

    /**
     * @test
     */
    public function editEventDateActionForEventWithTopicHasSelectedTopicOption(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwnerAndTopic.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('<option value="2" selected="selected">OOP with PHP</option>', $html);
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public static function auxiliaryRecordTitlesForEventDateDataProvider(): array
    {
        return [
            'venues' => ['Jugendherberge Bonn'],
            'speakers' => ['Ned Knowledge'],
            'organizers' => ['Training Inc.'],
            'topic' => ['OOP with PHP'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $title
     * @dataProvider auxiliaryRecordTitlesForEventDateDataProvider
     */
    public function editEventDateActionHasTitlesOfAuxiliaryRecords(string $title): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/Topic.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString($title, $html);
    }

    /**
     * @test
     */
    public function editEventDateActionWithOwnEventAssignsProvidedEventToView(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString(
            '<input type="hidden" name="tx_seminars_frontendeditor[event][__identity]" value="1" />',
            $html,
        );
        self::assertStringContainsString('event with owner', $html);
    }

    /**
     * @test
     */
    public function editEventDateActionForUserWithDefaultOrganizerHasNoOrganizerFormField(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/FrontEndUserWithDefaultOrganizer.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwnerWithDefaultOrganizer.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][organizers]"', $html);
    }

    /**
     * @test
     */
    public function editEventDateActionWithOwnEventRendersNumberOfOfflineRegistrations(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
            'tx_seminars_frontendeditor[event]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('value="59"', $html);
    }

    /**
     * @test
     */
    public function editEventDateActionWithEventFromOtherUserThrowsException(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventFromDifferentOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
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
    public function editEventDateActionWithEventWithoutOwnerThrowsException(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/editEventDateAction/EventWithoutOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'editEventDate',
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
    public function updateEventDateActionWithOwnEventUpdatesEvent(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForEditEventDateFormLegacy(
                1,
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'updateEventDate',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][numberOfOfflineRegistrations]' => '5',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/UpdatedEvent.csv',
        );
    }

    /**
     * @test
     */
    public function updateEventDateActionKeepsPidUnchanged(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithDifferentPid.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForEditEventDateFormLegacy(
                1,
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'updateEventDate',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithDifferentPid.csv',
        );
    }

    /**
     * @test
     */
    public function updateEventDateActionCanSetOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithOwner.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForEditEventDateFormLegacy(
                1,
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'updateEventDate',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][organizers]' => '',
            'tx_seminars_frontendeditor[event][organizers][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/UpdatedEventWithOrganizer.csv',
        );
    }

    /**
     * @test
     */
    public function updateEventDateActionCanSetTopic(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithOwner.csv',
        );
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForEditEventDateFormLegacy(
                1,
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'updateEventDate',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][topic]' => '2',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/UpdatedEventWithTopic.csv',
        );
    }

    /**
     * @test
     */
    public function updateEventDateActionForUserWithDefaultOrganizerKeepsOrganizerUnchanged(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/FrontEndUserWithDefaultOrganizer.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithOwnerWithDefaultOrganizer.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForEditEventDateFormLegacy(
                1,
                2,
            ),
            'tx_seminars_frontendeditor[action]' => 'updateEventDate',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'event with owner',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithOwnerWithDefaultOrganizer.csv',
        );
    }

    /**
     * @test
     */
    public function updateEventDateActionForEventWithTopicUpdatesSlug(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithTopicAndOwner.csv',
        );

        $newTitle = 'Karaoke party';
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForEditEventDateFormLegacy(
                1,
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'updateEventDate',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => $newTitle,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/UpdatedEventWithTopicAndSlug.csv',
        );
    }

    /**
     * @test
     */
    public function updateEventDateActionForEventWithoutTopicSetsSlugToUidOnly(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/EventWithOwner.csv',
        );

        $newTitle = 'Karaoke party';
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForEditEventDateFormLegacy(
                1,
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'updateEventDate',
            'tx_seminars_frontendeditor[event][__identity]' => '1',
            'tx_seminars_frontendeditor[event][internalTitle]' => $newTitle,
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/updateEventDateAction/UpdatedEventWithUidOnlySlug.csv',
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
     */
    public function newSingleEventActionHasFormTargetCreateSingleEventAction(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=createSingleEvent'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor';
        self::assertStringContainsString($expected, $html);
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
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/AuxiliaryRecords.csv',
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
     *
     * @param non-empty-string $key
     * @dataProvider formFieldKeysIrrelevantForSingleEventsDataProvider
     */
    public function newSingleEventEventActionHasNoFormFieldsIrrelevantForSingleEvents(string $key): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newSingleEvent',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     */
    public function newSingleEventActionForUserWithDefaultOrganizerHasNoOrganizerFormField(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/FrontEndUserWithDefaultOrganizer.csv',
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
     * @dataProvider auxiliaryRecordTitlesForSingleEventDataProvider
     */
    public function newSingleEventActionHasTitlesOfAuxiliaryRecords(string $title): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newSingleEventAction/AuxiliaryRecords.csv',
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
    public function createSingleEventActionCreatesSingleEvent(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedSingleEvent.csv',
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionSetsLoggedInUserAsOwnerOfProvidedEvent(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithOwner.csv',
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionSetsPidFromConfiguration(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithPid.csv',
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionSetsSlug(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithSlug.csv',
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionCanSetNumberOfOfflineRegistrations(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][numberOfOfflineRegistrations]' => '3',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithOfflineRegistrations.csv',
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionCanSetOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/AuxiliaryRecords.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][organizers]' => '',
            'tx_seminars_frontendeditor[event][organizers][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithOrganizer.csv',
        );
    }

    /**
     * @test
     */
    public function createSingleEventForUserWithDefaultOrganizerSetsDefaultOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/FrontEndUserWithDefaultOrganizer.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                2,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithDefaultOrganizer.csv',
        );
    }

    /**
     * @test
     */
    public function createSingleEventActionCanSetCategory(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/AuxiliaryRecords.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewSingleEventFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createSingleEvent',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][categories]' => '',
            'tx_seminars_frontendeditor[event][categories][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createSingleEventAction/CreatedEventWithCategory.csv',
        );
    }

    /**
     * @test
     */
    public function newEventDateActionCanBeRendered(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('Create new event', $html);
    }

    /**
     * @test
     */
    public function newEventDateActionHasFormTargetCreateEventDateAction(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        $expected = '?tx_seminars_frontendeditor%5Baction%5D=createEventDate'
            . '&amp;tx_seminars_frontendeditor%5Bcontroller%5D=FrontEndEditor';
        self::assertStringContainsString($expected, $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider nonDateFormFieldKeysForEventDateDataProvider
     */
    public function newEventDateActionHasAllNonDateFormFields(string $key): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider dateFormFieldKeysForEventDateDataProvider
     */
    public function newEventDateActionHasAllDateFormFields(string $key): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
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
     * @dataProvider multiAssociationFormFieldKeysForEventDateDataProvider
     * @dataProvider singleAssociationFormFieldKeysForEventDateDataProvider
     */
    public function newEventDateActionHasAllAssociationFormFields(string $key): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/newEventDateAction/AuxiliaryRecords.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $key
     * @dataProvider formFieldKeysIrrelevantForEventDatesDataProvider
     */
    public function newEventDateEventActionHasNoFormFieldsIrrelevantForEventDates(string $key): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/newEventDateAction/AuxiliaryRecords.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/newEventDateAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][' . $key . ']"', $html);
    }

    /**
     * @test
     */
    public function newEventDateActionForUserWithDefaultOrganizerHasNoOrganizerFormField(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/newEventDateAction/AuxiliaryRecords.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/newEventDateAction/FrontEndUserWithDefaultOrganizer.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringNotContainsString('name="tx_seminars_frontendeditor[event][organizers]"', $html);
    }

    /**
     * @test
     *
     * @param non-empty-string $title
     * @dataProvider auxiliaryRecordTitlesForEventDateDataProvider
     */
    public function newEventDateActionHasTitlesOfAuxiliaryRecords(string $title): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/newEventDateAction/AuxiliaryRecords.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/newEventDateAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[action]' => 'newEventDate',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $context)->getBody();

        self::assertStringContainsString($title, $html);
    }

    /**
     * @test
     */
    public function createEventDateActionCreatesEventDate(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventDate.csv',
        );
    }

    /**
     * @test
     */
    public function createEventDateActionSetsLoggedInUserAsOwnerOfProvidedEvent(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventWithOwner.csv',
        );
    }

    /**
     * @test
     */
    public function createEventDateActionSetsPidFromConfiguration(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventWithPid.csv',
        );
    }

    /**
     * @test
     */
    public function createEventDateActionCanSetTopic(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][topic]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventWithTopic.csv',
        );
    }

    /**
     * @test
     */
    public function createEventDateActionSetsSlug(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/Topic.csv');

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][topic]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventWithTopicAndSlug.csv',
        );
    }

    /**
     * @test
     */
    public function createEventDateActionCanSetNumberOfOfflineRegistrations(): void
    {
        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][numberOfOfflineRegistrations]' => '3',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventWithOfflineRegistrations.csv',
        );
    }

    /**
     * @test
     */
    public function createEventDateActionCanSetOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/AuxiliaryRecords.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                1,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
            'tx_seminars_frontendeditor[event][organizers]' => '',
            'tx_seminars_frontendeditor[event][organizers][]' => '1',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(1);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventWithOrganizer.csv',
        );
    }

    /**
     * @test
     */
    public function createEventDateForUserWithDefaultOrganizerSetsDefaultOrganizer(): void
    {
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/AuxiliaryRecords.csv',
        );
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/FrontEndUserWithDefaultOrganizer.csv',
        );

        $request = (new InternalRequest())->withPageId(self::PAGE_UID)->withQueryParameters([
            'tx_seminars_frontendeditor[__trustedProperties]' => $this->getTrustedPropertiesForNewEventDateFormLegacy(
                2,
            ),
            'tx_seminars_frontendeditor[action]' => 'createEventDate',
            'tx_seminars_frontendeditor[event][internalTitle]' => 'Karaoke party',
        ]);
        $context = (new InternalRequestContext())->withFrontendUserId(2);

        $this->executeFrontendSubRequest($request, $context);

        $this->assertCSVDataSet(
            __DIR__ . '/Fixtures/FrontEndEditorController/createEventDateAction/CreatedEventWithDefaultOrganizer.csv',
        );
    }
}
