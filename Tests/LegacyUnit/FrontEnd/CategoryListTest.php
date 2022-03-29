<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\CategoryList;
use OliverKlee\Seminars\Model\Event;
use PHPUnit\Framework\TestCase;
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

    /**
     * @var int
     */
    private $rootPageUid;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->changeRecord('pages', $this->rootPageUid, ['slug' => '/home']);
        $this->testingFramework->createFakeFrontEnd($this->rootPageUid);

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
        $listPageUid = $this->testingFramework->createFrontEndPage($this->rootPageUid);
        $this->testingFramework->changeRecord('pages', $listPageUid, ['slug' => '/eventList']);
        $this->subject->setConfigurationValue('listPID', $listPageUid);

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
}
