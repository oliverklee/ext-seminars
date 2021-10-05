<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\FrontEnd\CategoryList;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\CategoryList
 */
final class CategoryListTest extends FunctionalTestCase
{
    use FalHelper;
    use LanguageHelper;

    /**
     * @var string
     */
    private const BLANK_GIF = 'R0lGODlhAQABAJH/AP///wAAAMDAwAAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==';

    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var string[]
     */
    protected $additionalFoldersToCreate = ['uploads/tx_seminars'];

    /**
     * @var string[]
     */
    private $filesToDelete = [];

    /**
     * @var CategoryList
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TSFE'] = new TypoScriptFrontendController(null, 0, 0);

        $this->subject = new CategoryList(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            new ContentObjectRenderer()
        );
    }

    protected function tearDown(): void
    {
        foreach ($this->filesToDelete as $file) {
            \unlink($this->getInstancePath() . '/' . $file);
        }
    }

    private function createBlankGif(): string
    {
        $fileName = 'blank.gif';
        $this->filesToDelete[] = $fileName;
        $fullPath = $this->getInstancePath() . '/uploads/tx_seminars/' . $fileName;
        \file_put_contents($fullPath, \base64_decode(self::BLANK_GIF));

        return $fileName;
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

    /**
     * @test
     */
    public function createCategoryListRendersIconWithEncodedCategoryTitleAsImageTitle(): void
    {
        $this->provideAdminBackEndUserForFal();
        $title = 'a & b';
        $fileName = $this->createBlankGif();
        $categoryData = [1 => ['title' => $title, 'icon' => $fileName]];
        $this->subject->setConfigurationValue('categoriesInListView', 'icon');

        $result = $this->subject->createCategoryList($categoryData);

        self::assertRegExp('/<img[^>]+title="' . \htmlspecialchars($title, ENT_QUOTES | ENT_HTML5) . '"/', $result);
    }
}
