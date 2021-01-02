<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CategoryListTest extends TestCase
{
    /**
     * @var string
     */
    const BLANK_GIF = 'R0lGODlhAQABAJH/AP///wAAAMDAwAAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==';

    /**
     * @var \Tx_Seminars_FrontEnd_CategoryList
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

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();

        $this->systemFolderPid = $this->testingFramework->createSystemFolder();
        $this->subject = new \Tx_Seminars_FrontEnd_CategoryList(
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

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /*
     * Tests for render
     */

    public function testRenderCreatesCategoryListContainingTwoCategoryTitles()
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
        self::assertContains(
            'first category',
            $output
        );
        self::assertContains(
            'second category',
            $output
        );
    }

    public function testRenderCreatesCategoryListWhichIsSortedAlphabetically()
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

    public function testRenderCreatesCategoryListByUsingRecursion()
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

        self::assertContains(
            'one category',
            $this->subject->render()
        );
    }

    public function testRenderIgnoresOtherSysFolders()
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

        self::assertNotContains(
            'one category',
            $this->subject->render()
        );
    }

    public function testRenderCanReadFromAllSystemFolders()
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

        self::assertContains(
            'one category',
            $this->subject->render()
        );
    }

    public function testRenderIgnoresCanceledEvents()
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

        self::assertNotContains(
            'one category',
            $this->subject->render()
        );
    }

    public function testRenderFindsConfirmedEvents()
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
                'cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        self::assertContains(
            'one category',
            $this->subject->render()
        );
    }

    public function testRenderCreatesCategoryListOfEventsFromSelectedTimeFrames()
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

        self::assertContains(
            'one category',
            $this->subject->render()
        );
    }

    public function testRenderIgnoresEventsFromDeselectedTimeFrames()
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

        self::assertNotContains(
            'one category',
            $this->subject->render()
        );
    }

    public function testRenderCreatesCategoryListContainingLinksToListPageLimitedToCategory()
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

        self::assertContains(
            'tx_seminars_pi1%5Bcategory%5D=' . $categoryUid,
            $this->subject->render()
        );
    }

    /*
     * Tests concerning createCategoryList
     */

    public function testCreateCategoryListWithNoGivenCategoriesReturnsEmptyString()
    {
        self::assertEquals(
            '',
            $this->subject->createCategoryList([])
        );
    }

    public function testCreateCategoryListWithConfigurationValueSetToTextReturnsCategoryTitle()
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

    public function testCreateCategoryListWithConfigurationValueSetToTextDoesNotReturnIcon()
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

        self::assertNotContains(
            'foo.gif',
            $this->subject->createCategoryList($singleCategory)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithInvalidConfigurationValueReturnsHtmlspecialcharedCategoryTitle()
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
    public function createCategoryListWithBothAsDisplayModeCreatesHtmlspecialcharedCategoryTitle()
    {
        $this->subject->setConfigurationValue('categoriesInListView', 'both');
        $singleCategory =
            [
                99 => [
                    'title' => 'test & more',
                    'icon' => '',
                ],
            ];

        self::assertContains(
            'test &amp; more',
            $this->subject->createCategoryList($singleCategory)
        );
        self::assertNotContains(
            'test & more',
            $this->subject->createCategoryList($singleCategory)
        );
    }

    /**
     * @test
     */
    public function createCategoryListWithTextAsDisplayModeCreatesHtmlspecialcharedCategoryTitle()
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

    public function testCreateCategoryListWithConfigurationValueSetToTextCanReturnMultipleCategoryTitles()
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

    public function testCreateCategoryForCategoryWithoutImageAndListWithConfigurationValueSetToIconUsesCommasAsSeparators()
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

    public function testCreateCategoryListWithConfigurationValueSetToTextUsesCommasAsSeparators()
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

    public function testCreateCategoryListWithConfigurationValueSetToBothUsesCommasAsSeparators()
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
