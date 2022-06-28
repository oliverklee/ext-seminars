<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd\DefaultController;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\FrontEnd\Fixtures\TestingDefaultController;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\DefaultController
 */
final class SingleViewTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var positive-int
     */
    private const CURRENT_PAGE_UID = 1;

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var TypoScriptFrontendController|null
     */
    private $frontEndController = null;

    protected function setUp(): void
    {
        parent::setUp();
        (new CacheNullifyer())->setAllCoreCaches();
        $this->initializeBackEndLanguage();
    }

    protected function tearDown(): void
    {
        RegistrationManager::purgeInstance();

        parent::tearDown();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        if ($this->frontEndController instanceof TypoScriptFrontendController) {
            return $this->frontEndController;
        }

        $contentObject = new ContentObjectRenderer();
        $contentObject->setLogger(new NullLogger());

        // Needed in TYPO3 V10; can be removed in V11.
        $GLOBALS['_SERVER']['HTTP_HOST'] = 'typo3-test.dev';
        if (Typo3Version::isAtLeast(10)) {
            $frontEndController = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                new Site('test', self::CURRENT_PAGE_UID, []),
                new SiteLanguage(0, 'en_US.utf8', new Uri(), [])
            );
        } else {
            $frontEndController = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                self::CURRENT_PAGE_UID,
                0
            );
        }
        $frontEndController->fe_user = $this->prophesize(FrontendUserAuthentication::class)->reveal();
        $frontEndController->setLogger($this->prophesize(LoggerInterface::class)->reveal());
        $frontEndController->determineId();
        $frontEndController->cObj = $contentObject;

        $this->frontEndController = $frontEndController;
        $GLOBALS['TSFE'] = $frontEndController;

        return $frontEndController;
    }

    private function buildSubjectForSingleView(string $fixtureFileName): TestingDefaultController
    {
        $this->importDataSet(__DIR__ . '/Fixtures/' . $fixtureFileName . '.xml');

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
}
