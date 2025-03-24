<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Controller;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Controller\MyRegistrationsController
 */
final class MyRegistrationsControllerTest extends FunctionalTestCase
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
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/MyRegistrationsContentElement.csv'
        );
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
    public function indexActionForNoUserLoggedInShowsPleaseLogInMessage(): void
    {
        $request = (new InternalRequest())->withPageId(7);

        $response = $this->executeFrontendSubRequest($request);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.error.notLoggedIn', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionForNoUserLoggedInReturnsStatus403(): void
    {
        self::markTestSkipped('Currently, the HTTP status code gets lost when using executeFrontendSubRequest.');

        $request = (new InternalRequest())->withPageId(7);
        $status = $this->executeFrontendSubRequest($request)->getStatusCode();

        self::assertSame(403, $status);
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserReturnsStatus200(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserForNoRegistrationsReturnsNoRegistrationsMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate(
            'plugin.myRegistrations.messages.noRegistrations_formal',
            'seminars'
        );
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserForRegistrationsOfOtherUsersReturnsNoRegistrationsMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegistrationOfOtherUser.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate(
            'plugin.myRegistrations.messages.noRegistrations_formal',
            'seminars'
        );
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionWithLoggedInUserForRegistrationsOfOtherUsersDoesNotRenderEventTitle(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegistrationOfOtherUser.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('some other event', (string)$response->getBody());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function indexActionWithLoggedInUserForRegistrationsWithDeletedEventDoesNotCrash(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegistrationForDeletedEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $this->executeFrontendSubRequest($request, $requestContext);
    }

    /**
     * @test
     */
    public function indexActionRendersDateOfSingleDaySingleEventRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegistrationForSingleDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('2039-12-01', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersDateOfMultiDaySingleEventRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegistrationForMultiDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('2039-12-01–2039-12-02', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersEventTypeOfSingleEventRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegistrationWithEventType.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('workshop', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersTitleOfSingleEventRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/indexAction/Registration.csv');

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('the event title', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionDoesNotRenderHiddenRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/indexAction/HiddenRegistration.csv');

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('the event title', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersTopicTitleOfEventDateRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegistrationForEventDate.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('the topic title', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersRegularRegistrationStatusOfRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/indexAction/RegularRegistration.csv');

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.property.registrationStatus.0', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersWaitingListRegistrationStatusOfRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/indexAction/WaitingListRegistration.csv'
        );

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.property.registrationStatus.1', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionRendersNonBindingReserverationRegistrationStatusOfRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/indexAction/NonBindingReservation.csv');

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.property.registrationStatus.2', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function indexActionLinksEventTitleToShowAction(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/indexAction/Registration.csv');

        $request = (new InternalRequest())->withPageId(7);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $urlPrefix = '/my-events\\?tx_seminars_myregistrations%5Baction%5D=show&amp;'
            . 'tx_seminars_myregistrations%5Bcontroller%5D=MyRegistrations&amp;'
            . 'tx_seminars_myregistrations%5Bregistration%5D=1';
        self::assertMatchesRegularExpression(
            '#' . $urlPrefix . '[^"]*">.*the event title#s',
            (string)$response->getBody()
        );
    }

    /**
     * @test
     */
    public function showActionForNoUserLoggedInShowsPleaseLogInMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/Registration.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext());

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.error.notLoggedIn', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForNoUserLoggedInReturnsStatus403(): void
    {
        self::markTestSkipped('Currently, the HTTP status code gets lost when using executeFrontendSubRequest.');

        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/Registration.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext());
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertSame(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function showActionWithRegistrationOfLoggedInUserReturnsStatus200(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/Registration.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function showActionWithRegistrationOfOtherUserReturnsStatus404(): void
    {
        self::markTestSkipped('Currently, the HTTP status code gets lost when using executeFrontendSubRequest.');

        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/Registration.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function showActionWithRegistrationOfOtherUserRendersNotFoundMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationOfOtherUser.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);
        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.error.notFound', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionRendersTitleOfSingleEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/Registration.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertMatchesRegularExpression('#<h1>\\s*the event title#s', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDisplayTitleOfEventDate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationForEventDate.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertMatchesRegularExpression('#<h1>\\s*the topic title#s', $html);
        self::assertStringNotContainsString('the date title', $html);
    }

    /**
     * @test
     */
    public function showActionRendersEventType(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationWithEventType.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('workshop', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDateOfSingleDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationForSingleDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('2039-12-01', $html);
    }

    /**
     * @test
     */
    public function showActionRendersDateOfSingleDayEventOnlyOnce(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationForSingleDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertSame(1, \substr_count($html, '2039-12-01'));
    }

    /**
     * @test
     */
    public function showActionRendersStartAndEndOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationForMultiDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('2039-12-01–2039-12-02', $html);
    }

    /**
     * @test
     */
    public function showActionRendersStartAndEndTimeOfSingleDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationForSingleDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('09:00–17:00', $html);
    }

    /**
     * @test
     */
    public function showActionRendersStartDateAndTimeOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationForMultiDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('2039-12-01 09:00', $html);
    }

    /**
     * @test
     */
    public function showActionRendersEndDateAndTimeOfMultiDayEvent(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationForMultiDayEvent.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('2039-12-02 17:00', $html);
    }

    /**
     * @test
     */
    public function showActionRendersTitleOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationWithOneVenue.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();
        self::assertStringContainsString('Maritim Hotel', $html);
    }

    /**
     * @test
     */
    public function showActionRendersAddressOfVenue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationWithOneVenue.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();
        self::assertStringContainsString('Kurt-Georg-Kiesinger-Allee 1', $html);
        self::assertStringContainsString('53175 Bonn', $html);
    }

    /**
     * @test
     */
    public function showActionConvertsNewlinesToBreakInVenuAddress(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationWithOneVenue.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('Kurt-Georg-Kiesinger-Allee 1<br />', $html);
    }

    /**
     * @test
     */
    public function showActionCanRenderMultipleVenues(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationWithTwoVenuesInSameCity.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('Maritim Hotel', $html);
        self::assertStringContainsString('Kameha Grand', $html);
    }

    /**
     * @test
     */
    public function showActionRendersRoom(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/Registration.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $html = (string)$this->executeFrontendSubRequest($request, $requestContext)->getBody();

        self::assertStringContainsString('room 13 B', $html);
    }

    /**
     * @test
     */
    public function showActionRendersRegularRegistrationStatusOfRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegularRegistration.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.property.registrationStatus.0', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionRendersWaitingListRegistrationStatusOfRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/WaitingListRegistration.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.property.registrationStatus.1', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionRendersNonBindingReserverationRegistrationStatusOfRegistrationOfTheLoggedInUser(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/showAction/NonBindingReservation.csv');

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $expected = LocalizationUtility::translate('plugin.myRegistrations.property.registrationStatus.2', 'seminars');
        self::assertIsString($expected);
        self::assertStringContainsString($expected, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForRegistrationWithUnregistrationPossibleShowsLinkToUnregistration(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegularRegistrationWithUnregistrationPossible.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $urlPrefix = '/my-events\\?tx_seminars_myregistrations%5Baction%5D=checkPrerequisites&amp;'
            . 'tx_seminars_myregistrations%5Bcontroller%5D=EventUnregistration&amp;'
            . 'tx_seminars_myregistrations%5Bregistration%5D=1';
        $linkText = LocalizationUtility::translate('plugin.myRegistrations.show.toUnregistrationForm', 'seminars');
        self::assertIsString($linkText);
        self::assertMatchesRegularExpression(
            '#' . $urlPrefix . '[^"]*">.*' . $linkText . '#s',
            (string)$response->getBody()
        );
    }

    /**
     * @test
     */
    public function showActionForRegistrationWithUnregistrationNotPossibleDoesNotShowLinkToUnregistration(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegularRegistrationWithUnregistrationDeadlineOver.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        $urlPrefix = '/my-events?tx_seminars_myregistrations%5Baction%5D=checkPrerequisites&amp;'
            . 'tx_seminars_myregistrations%5Bcontroller%5D=EventUnregistration&amp;'
            . 'tx_seminars_myregistrations%5Bregistration%5D=1';
        self::assertStringNotContainsString($urlPrefix, (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForRegularRegistrationWithDownloadsRendersLinkToEventDownload(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegularRegistrationWithDownloadWithoutTitle.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('/speaker.jpg', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForRegularRegistrationWithDownloadWithoutTitleUsesFilenameAsLinkText(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegularRegistrationWithDownloadWithoutTitle.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertMatchesRegularExpression('#>\\s*speaker\\.jpg\\s*</a>#', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForRegularRegistrationWithDownloadWithTitleUsesTitleAsLinkText(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegularRegistrationWithDownloadWithTitle.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertMatchesRegularExpression('#>\\s*speaker portrait\\s*</a>#', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForWaitingListRegistrationWithDownloadsDoesNotRendersDownload(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/WaitingListRegistrationWithDownload.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('speaker.jpg', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForNonBindingReservationWithDownloadsDoesNotRendersDownload(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/NonBindingReservationWithDownload.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('speaker.jpg', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForRegularRegistrationWithDownloadStartDateInPastRendersDownload(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationWithDownloadStartInPast.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringContainsString('speaker.jpg', (string)$response->getBody());
    }

    /**
     * @test
     */
    public function showActionForRegularRegistrationWithDownloadStartDateInFuturesDoesNotRenderDownload(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/MyRegistrationsController/FrontEndUserAndGroup.csv');
        $this->importCSVDataSet(
            __DIR__ . '/Fixtures/MyRegistrationsController/showAction/RegistrationWithDownloadStartInFuture.csv'
        );

        $request = (new InternalRequest())->withPageId(7)
            ->withQueryParameter('tx_seminars_myregistrations[action]', 'show')
            ->withQueryParameter('tx_seminars_myregistrations[controller]', 'MyRegistrations')
            ->withQueryParameter('tx_seminars_myregistrations[registration]', 1);
        $requestContext = (new InternalRequestContext())->withFrontendUserId(1);

        $response = $this->executeFrontendSubRequest($request, $requestContext);

        self::assertStringNotContainsString('speaker.jpg', (string)$response->getBody());
    }
}
