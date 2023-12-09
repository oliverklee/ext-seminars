<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd\DefaultController;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Seo\SingleViewPageTitleProvider;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\FrontEnd\Fixtures\TestingDefaultController;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\DefaultController
 */
final class SingleViewTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $extensionConfiguration = new DummyConfiguration();
        $extensionConfiguration->setAsBoolean('enableConfigCheck', false);
        ConfigurationProxy::setInstance('seminars', $extensionConfiguration);

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

    private function buildSubjectForSingleView(string $fixtureFileName): TestingDefaultController
    {
        $this->importDataSet(__DIR__ . '/Fixtures/' . $fixtureFileName . '.xml');
        $this->testingFramework->createFakeFrontEnd(1);

        $frontEndController = $this->getFrontEndController();
        $subject = new TestingDefaultController();
        $subject->cObj = $frontEndController->cObj;
        $subject->init(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'what_to_display' => 'single_view',
            ]
        );

        return $subject;
    }

    // Tests concerning the single view

    /**
     * @return array<string, array{0: positive-int, 1: non-empty-string}>
     */
    public function singleViewDataDataProvider(): array
    {
        return [
            'title' => [1, 'test &amp; event'],
            'subtitle' => [1, 'subtitle &amp; more'],
            'room' => [1, 'Rooms 2 &amp; 3'],
            'accreditation number' => [1, '4 &amp; 5'],
            'organizer name' => [2, 'Rupf &amp; Knack Deckendienste'],
            'organizer description' => [2, 'Best organizer!'],
            'linked organizer homepage' => [2, 'href="https://www.example.com"'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider singleViewDataDataProvider
     */
    public function singleViewContainsHtmlspecialcharedEventData(int $uid, string $expected): void
    {
        $subject = $this->buildSubjectForSingleView('EventSingleView');
        $subject->piVars['showUid'] = (string)$uid;

        $result = $subject->main('', []);

        self::assertStringContainsString($expected, $result);
    }

    /**
     * @test
     */
    public function singleViewProvidesPageTitleProviderWithEventTitleAsTitle(): void
    {
        $pageTitleProvider = new SingleViewPageTitleProvider();
        GeneralUtility::setSingletonInstance(SingleViewPageTitleProvider::class, $pageTitleProvider);

        $subject = $this->buildSubjectForSingleView('EventSingleView');
        $subject->piVars['showUid'] = '1';

        $subject->main('', []);

        self::assertSame('test & event', $pageTitleProvider->getTitle());
    }
}
