<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Seminars\FrontEnd\CategoryList;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\CategoryList
 */
final class CategoryListTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var CategoryList
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerNullPageCache();

        // Needed in TYPO3 V10; can be removed in V11.
        $GLOBALS['_SERVER']['HTTP_HOST'] = 'typo3-test.dev';
        if (Typo3Version::isAtLeast(10)) {
            $this->disableCoreCaches();
            $frontEnd = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                new Site('test', 0, []),
                new SiteLanguage(0, 'en_US.utf8', new Uri(), [])
            );
        } else {
            $frontEnd = GeneralUtility::makeInstance(
                TypoScriptFrontendController::class,
                $GLOBALS['TYPO3_CONF_VARS'],
                0,
                0
            );
        }
        $GLOBALS['TSFE'] = $frontEnd;

        $this->subject = new CategoryList(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            new ContentObjectRenderer()
        );
    }

    private function registerNullPageCache(): void
    {
        $cacheKey = $this->getCacheKeyPrefix() . 'pages';
        $cacheManager = $this->getCacheManager();
        if ($cacheManager->hasCache($cacheKey)) {
            return;
        }

        $backEnd = GeneralUtility::makeInstance(NullBackend::class, 'Testing');
        $frontEnd = GeneralUtility::makeInstance(VariableFrontend::class, $cacheKey, $backEnd);
        $cacheManager->registerCache($frontEnd);
    }

    private function getCacheKeyPrefix(): string
    {
        return Typo3Version::isAtLeast(10) ? '' : '_cache';
    }

    /**
     * Sets the following Core caches to the null backen: l10n, rootline, runtime
     */
    private function disableCoreCaches(): void
    {
        $this->getCacheManager()->setCacheConfigurations(
            [
                'l10n' => ['backend' => NullBackend::class],
                'rootline' => ['backend' => NullBackend::class],
                'runtime' => ['backend' => NullBackend::class],
            ]
        );
    }

    private function getCacheManager(): CacheManager
    {
        return GeneralUtility::makeInstance(CacheManager::class);
    }

    /**
     * @test
     */
    public function renderWithoutCategoriesDoesNotCreateCategoryTable(): void
    {
        $this->subject->setConfigurationValue('pages', 1);

        $result = $this->subject->render();

        self::assertStringNotContainsString('<table', $result);
    }

    /**
     * @test
     */
    public function renderWithoutCategoriesOutputsMessageAboutNoCategories(): void
    {
        $this->subject->setConfigurationValue('pages', 1);

        $result = $this->subject->render();

        self::assertStringContainsString($this->getLanguageService()->getLL('label_no_categories'), $result);
    }

    /**
     * @test
     */
    public function renderIncludesTitleOfCategoryWithEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/CategoryList/OneCategoryWithAsciiTitle.xml');

        $result = $this->subject->render();

        self::assertStringContainsString('category with ASCII title', $result);
    }

    /**
     * @test
     */
    public function renderEncodesCategoryTitles(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/CategoryList/OneCategoryWithSpecialCharactersInTitle.xml');

        $result = $this->subject->render();

        self::assertStringContainsString('category with ampersand &amp;', $result);
    }
}
