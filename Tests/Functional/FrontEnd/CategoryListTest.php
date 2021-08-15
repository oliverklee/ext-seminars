<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class CategoryListTest extends FunctionalTestCase
{
    use FalHelper;

    use LanguageHelper;

    /**
     * @var string
     */
    const BLANK_GIF = 'R0lGODlhAQABAJH/AP///wAAAMDAwAAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==';

    /**
     * @var string[]
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
     * @var \Tx_Seminars_FrontEnd_CategoryList
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['TSFE'] = new TypoScriptFrontendController(null, 0, 0);

        $this->subject = new \Tx_Seminars_FrontEnd_CategoryList(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            new ContentObjectRenderer()
        );
    }

    protected function tearDown()
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
    public function renderWithoutCategoriesDoesNotCreateCategoryTable()
    {
        $this->subject->setConfigurationValue('pages', 1);

        $result = $this->subject->render();

        self::assertNotContains('<table', $result);
    }

    /**
     * @test
     */
    public function renderWithoutCategoriesOutputsMessageAboutNoCategories()
    {
        $this->subject->setConfigurationValue('pages', 1);

        $result = $this->subject->render();

        self::assertContains($this->getLanguageService()->getLL('label_no_categories'), $result);
    }

    /**
     * @test
     */
    public function renderIncludesTitleOfCategoryWithEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/CategoryList/OneCategoryWithAsciiTitle.xml');

        $result = $this->subject->render();

        self::assertContains('category with ASCII title', $result);
    }

    /**
     * @test
     */
    public function renderEncodesCategoryTitles()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/CategoryList/OneCategoryWithSpecialCharactersInTitle.xml');

        $result = $this->subject->render();

        self::assertContains('category with ampersand &amp;', $result);
    }

    /**
     * @test
     */
    public function createCategoryListRendersIconWithEncodedCategoryTitleAsImageTitle()
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
