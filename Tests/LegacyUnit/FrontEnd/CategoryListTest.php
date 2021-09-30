<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\FrontEnd\CategoryList;
use OliverKlee\Seminars\Model\Event;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\CategoryList
 */
final class CategoryListTest extends TestCase
{
    /**
     * @var string
     */
    private const BLANK_GIF = 'R0lGODlhAQABAJH/AP///wAAAMDAwAAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==';

    /**
     * @var CategoryList
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int PID of a dummy system folder
     */
    private $systemFolderPid = 0;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new TestingFramework('tx_seminars');
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($pageUid);

        $this->systemFolderPid = $this->testingFramework->createSystemFolder();
        $this->subject = new CategoryList(
            [
                'isStaticTemplateLoaded' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'pages' => $this->systemFolderPid,
                'pidList' => $this->systemFolderPid,
                'recursive' => 1,
            ],
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    // Tests for render

    /**
     * @test
     */
    public function renderCreatesCategoryListContainingTwoCategoryTitles(): void
    {
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'first category']
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'second category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 2,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid2
        );

        $output = $this->subject->render();
        self::assertStringContainsString(
            'first category',
            $output
        );
        self::assertStringContainsString(
            'second category',
            $output
        );
    }

    /**
     * @test
     */
    public function renderCreatesCategoryListWhichIsSortedAlphabetically(): void
    {
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'category B']
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'category A']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 2,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid2
        );

        $output = $this->subject->render();
        self::assertTrue(
            strpos($output, 'category A') < strpos($output, 'category B')
        );
    }

    /**
     * @test
     */
    public function renderCreatesCategoryListByUsingRecursion(): void
    {
        $systemSubFolderUid = $this->testingFramework->createSystemFolder(
            $this->systemFolderPid
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $systemSubFolderUid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringContainsString(
            'one category',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderIgnoresOtherSysFolders(): void
    {
        $otherSystemFolderUid = $this->testingFramework->createSystemFolder();
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $otherSystemFolderUid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringNotContainsString(
            'one category',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanReadFromAllSystemFolders(): void
    {
        $this->subject->setConfigurationValue('pages', '');

        $otherSystemFolderUid = $this->testingFramework->createSystemFolder();
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $otherSystemFolderUid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringContainsString(
            'one category',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderIgnoresCanceledEvents(): void
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 1,
                'cancelled' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringNotContainsString(
            'one category',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderFindsConfirmedEvents(): void
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'my_title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 1,
                'cancelled' => Event::STATUS_CONFIRMED,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringContainsString(
            'one category',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCreatesCategoryListOfEventsFromSelectedTimeFrames(): void
    {
        $this->subject->setConfigurationValue(
            'timeframeInList',
            'currentAndUpcoming'
        );

        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
                'categories' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringContainsString(
            'one category',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderIgnoresEventsFromDeselectedTimeFrames(): void
    {
        $this->subject->setConfigurationValue(
            'timeframeInList',
            'currentAndUpcoming'
        );

        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 2000,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
                'categories' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringNotContainsString(
            'one category',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCreatesCategoryListContainingLinksToListPageLimitedToCategory(): void
    {
        $this->subject->setConfigurationValue(
            'listPID',
            $this->testingFramework->createFrontEndPage()
        );

        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'one category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->systemFolderPid,
                'title' => 'my title',
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
                'categories' => 1,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertStringContainsString(
            'tx_seminars_pi1%5Bcategory%5D=' . $categoryUid,
            $this->subject->render()
        );
    }

    // Tests concerning createCategoryList

    /**
     * @test
     */
    public function createCategoryListWithNoGivenCategoriesReturnsEmptyString(): void
    {
        self::assertEquals(
            '',
            $this->subject->createCategoryList([])
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithConfigurationValueSetToTextReturnsCategoryTitle(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'text');
        $singleCategory =
            [
                99 => [
                    'title' => 'test',
                    'icon' => '',
                ],
            ];

        self::assertEquals(
            'test',
            $this->subject->createCategoryList($singleCategory)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithConfigurationValueSetToTextDoesNotReturnIcon(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'text');
        $singleCategory =
            [
                99 => [
                    'title' => 'test',
                    'icon' => 'foo.gif',
                ],
            ];
        $this->testingFramework->createDummyFile('foo.gif', base64_decode(self::BLANK_GIF, true));

        self::assertStringNotContainsString(
            'foo.gif',
            $this->subject->createCategoryList($singleCategory)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithInvalidConfigurationValueReturnsHtmlspecialcharedCategoryTitle(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'foo');
        $singleCategory =
            [
                99 => [
                    'title' => 'test & more',
                    'icon' => '',
                ],
            ];

        self::assertEquals(
            'test &amp; more',
            $this->subject->createCategoryList($singleCategory)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithBothAsDisplayModeCreatesHtmlspecialcharedCategoryTitle(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'both');
        $singleCategory =
            [
                99 => [
                    'title' => 'test & more',
                    'icon' => '',
                ],
            ];

        self::assertStringContainsString(
            'test &amp; more',
            $this->subject->createCategoryList($singleCategory)
        );
        self::assertStringNotContainsString(
            'test & more',
            $this->subject->createCategoryList($singleCategory)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithTextAsDisplayModeCreatesHtmlspecialcharedCategoryTitle(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'text');
        $singleCategory =
            [
                99 => [
                    'title' => 'test & more',
                    'icon' => '',
                ],
            ];

        self::assertSame(
            'test &amp; more',
            $this->subject->createCategoryList($singleCategory)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithConfigurationValueSetToTextCanReturnMultipleCategoryTitles(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'text');
        $multipleCategories =
            [
                99 => [
                    'title' => 'foo',
                    'icon' => 'foo.gif',
                ],
                100 => [
                    'title' => 'bar',
                    'icon' => 'foo2.gif',
                ],
            ];

        self::assertRegExp(
            '/foo.*bar/',
            $this->subject->createCategoryList($multipleCategories)
        );
    }

    /**
     * @test
     */
    public function createCategoryForCategoryWithoutImageAndListWithConfigurationValueSetToIconUsesCommasAsSeparators(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'icon');
        $multipleCategories =
            [
                99 => [
                    'title' => 'foo',
                    'icon' => '',
                ],
                100 => [
                    'title' => 'bar',
                    'icon' => 'foo2.gif',
                ],
            ];

        self::assertRegExp(
            '/foo.*,.*bar/',
            $this->subject->createCategoryList($multipleCategories)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithConfigurationValueSetToTextUsesCommasAsSeparators(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'text');
        $multipleCategories =
            [
                99 => [
                    'title' => 'foo',
                    'icon' => 'foo.gif',
                ],
                100 => [
                    'title' => 'bar',
                    'icon' => 'foo2.gif',
                ],
            ];

        self::assertRegExp(
            '/foo.*,.*bar/',
            $this->subject->createCategoryList($multipleCategories)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithConfigurationValueSetToBothUsesCommasAsSeparators(): void
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'both');
        $this->testingFramework->createDummyFile('foo.gif', base64_decode(self::BLANK_GIF, true));
        $this->testingFramework->createDummyFile('foo2.gif', base64_decode(self::BLANK_GIF, true));
        $multipleCategories =
            [
                99 => [
                    'title' => 'foo',
                    'icon' => 'foo.gif',
                ],
                100 => [
                    'title' => 'bar',
                    'icon' => 'foo2.gif',
                ],
            ];

        self::assertRegExp(
            '/foo.*,.*bar/',
            $this->subject->createCategoryList($multipleCategories)
        );
    }
}
