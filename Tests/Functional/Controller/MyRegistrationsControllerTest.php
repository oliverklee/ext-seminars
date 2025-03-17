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
}
