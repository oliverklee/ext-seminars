<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\CategoryList;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\CategoryList
 */
final class CategoryListTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var CategoryList
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $this->subject = new CategoryList(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
            ],
            $this->getFrontEndController()->cObj
        );
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

        self::assertStringContainsString($this->translate('label_no_categories'), $result);
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
