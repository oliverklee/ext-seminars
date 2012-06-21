<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2012 Niels Pardon (mail@niels-pardon.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the tx_seminars_FrontEnd_CategoryList class in the "seminars"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_FrontEnd_CategoryListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_FrontEnd_CategoryList
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var integer the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	/**
	 * @var integer PID of a dummy system folder
	 */
	private $systemFolderPid = 0;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->systemFolderPid = $this->testingFramework->createSystemFolder();
		$this->seminarUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'Test event',
			)
		);

		$this->fixture = new tx_seminars_FrontEnd_CategoryList(
			array(
				'isStaticTemplateLoaded' => 1,
				'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
				'pages' => $this->systemFolderPid,
				'pidList' => $this->systemFolderPid,
				'recursive' => 1,
			),
			$GLOBALS['TSFE']->cObj
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	/*
	 * Tests for render
	 */

	public function testRenderCreatesEmptyCategoryList() {
		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$this->fixture->setConfigurationValue('pages', $otherSystemFolderUid);

		$output = $this->fixture->render();

		$this->assertNotContains(
			'<table',
			$output
		);
		$this->assertContains(
			$this->fixture->translate('label_no_categories'),
			$output
		);
	}

	/**
	 * @test
	 */
	public function renderCreatesCategoryListContainingOneHtmlspecialcharedCategoryTitle() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one & category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$result = $this->fixture->render();
		$this->assertContains(
			'one &amp; category',
			$result
		);
	}

	public function testRenderCreatesCategoryListContainingTwoCategoryTitles() {
		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'first category')
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'second category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 2
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid2
		);

		$output = $this->fixture->render();
		$this->assertContains(
			'first category',
			$output
		);
		$this->assertContains(
			'second category',
			$output
		);
	}

	public function testRenderCreatesCategoryListWhichIsSortedAlphabetically() {
		$categoryUid1 = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'category B')
		);
		$categoryUid2 = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'category A')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 2
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid1
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid2
		);

		$output = $this->fixture->render();
		$this->assertTrue(
			strpos($output, 'category A') < strpos($output, 'category B')
		);
	}

	public function testRenderCreatesCategoryListByUsingRecursion() {
		$systemSubFolderUid = $this->testingFramework->createSystemFolder(
			$this->systemFolderPid
		);
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $systemSubFolderUid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->render()
		);
	}

	public function testRenderIgnoresOtherSysFolders() {
		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $otherSystemFolderUid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->render()
		);
	}

	public function testRenderCanReadFromAllSystemFolders() {
		$this->fixture->setConfigurationValue('pages', '');

		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $otherSystemFolderUid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->render()
		);
	}

	public function testRenderIgnoresCanceledEvents() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 1,
				'cancelled' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->render()
		);
	}

	public function testRenderFindsConfirmedEvents() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my_title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 1,
				'cancelled' => tx_seminars_seminar::STATUS_CONFIRMED
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->render()
		);
	}

	public function testRenderCreatesCategoryListOfEventsFromSelectedTimeFrames() {
		$this->fixture->setConfigurationValue(
			'timeframeInList', 'currentAndUpcoming'
		);

		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] + 2000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertContains(
			'one category',
			$this->fixture->render()
		);
	}

	public function testRenderIgnoresEventsFromDeselectedTimeFrames() {
		$this->fixture->setConfigurationValue(
			'timeframeInList', 'currentAndUpcoming'
		);

		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 2000,
				'end_date' => $GLOBALS['SIM_EXEC_TIME'] - 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertNotContains(
			'one category',
			$this->fixture->render()
		);
	}

	public function testRenderCreatesCategoryListContainingLinksToListPageLimitedToCategory() {
		$this->fixture->setConfigurationValue(
			'listPID', $this->testingFramework->createFrontEndPage()
		);

		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories',
			array('title' => 'one category')
		);
		$eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->systemFolderPid,
				'title' => 'my title',
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 1000,
				'categories' => 1
			)
		);
		$this->testingFramework->createRelation(
			'tx_seminars_seminars_categories_mm', $eventUid, $categoryUid
		);

		$this->assertContains(
			'tx_seminars_pi1[category]='.$categoryUid,
			$this->fixture->render()
		);
	}


	/*
	 * Tests concerning createCategoryList
	 */

	public function testCreateCategoryListWithNoGivenCategoriesReturnsEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->createCategoryList(array())
		);
	}

	public function testCreateCategoryListWithConfigurationValueSetToTextReturnsCategoryTitle() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'text');
		$singleCategory =
			array(
				99 => array(
					'title' => 'test',
					'icon' => '',
				)
		);

		$this->assertEquals(
			'test',
			$this->fixture->createCategoryList($singleCategory)
		);
	}

	public function testCreateCategoryListWithConfigurationValueSetToTextDoesNotReturnIcon() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'text');
		$singleCategory =
			array(
				99 => array(
					'title' => 'test',
					'icon' => 'foo.gif',
				)
		);
		$this->testingFramework->createDummyFile('foo.gif');

		$this->assertNotContains(
			'foo.gif',
			$this->fixture->createCategoryList($singleCategory)
		);
	}

	/**
	 * @test
	 */
	public function createCategoryListWithInvalidConfigurationValueReturnsHtmlspecialcharedCategoryTitle() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'foo');
		$singleCategory =
			array(
				99 => array(
					'title' => 'test & more',
					'icon' => '',
				)
		);

		$this->assertEquals(
			'test &amp; more',
			$this->fixture->createCategoryList($singleCategory)
		);
	}

	/**
	 * @test
	 */
	public function createCategoryListWithBothAsDisplayModeCreatesHtmlspecialcharedCategoryTitle() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'both');
		$singleCategory =
			array(
				99 => array(
					'title' => 'test & more',
					'icon' => '',
				)
			);

		$this->assertContains(
			'test &amp; more',
			$this->fixture->createCategoryList($singleCategory)
		);
		$this->assertNotContains(
			'test & more',
			$this->fixture->createCategoryList($singleCategory)
		);
	}

	/**
	 * @test
	 */
	public function createCategoryListWithTextAsDisplayModeCreatesHtmlspecialcharedCategoryTitle() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'text');
		$singleCategory =
			array(
				99 => array(
					'title' => 'test & more',
					'icon' => '',
				)
			);

		$this->assertSame(
			'test &amp; more',
			$this->fixture->createCategoryList($singleCategory)
		);
	}

	/**
	 * @test
	 */
	public function createCategoryListWithConfigurationValueSetToIconSetsHtmlSpecialcharedCategoryTitleAsImageTitle() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'icon');
		$singleCategory =
			array(
				99 => array(
					'title' => 'te & st',
					'icon' => 'foo.gif',
				)
		);
		$this->testingFramework->createDummyFile('foo.gif');

		$this->assertRegExp(
			'/<img[^>]+title="te &amp; st"/',
			$this->fixture->createCategoryList($singleCategory)
		);
	}

	public function testCreateCategoryListWithConfigurationValueSetToIconDoesNotReturnTitleOutsideTheImageTag() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'icon');
		$singleCategory =
			array(
				99 => array(
					'title' => 'test',
					'icon' => 'foo.gif',
				)
		);

		$this->testingFramework->createDummyFile('foo.gif');


		$this->assertNotRegExp(
			'/<img[^>]*>.*test/',
			$this->fixture->createCategoryList($singleCategory)
		);
	}

	/**
	 * @test
	 */
	public function createCategoryListWithConfigurationValueSetToIconCanReturnMultipleIcons() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'icon');
		$multipleCategories =
			array(
				99 => array(
					'title' => 'test',
					'icon' => 'foo.gif',
				),
				100 => array(
					'title' => 'new_test',
					'icon' => 'foo2.gif',
				)
		);

		$this->testingFramework->createDummyFile('foo.gif');
		$this->testingFramework->createDummyFile('foo2.gif');

		$this->assertRegExp(
			'/<img[^>]+title="test"[^>]*>.*<img[^>]+title="new_test"[^>]*>/',
			$this->fixture->createCategoryList($multipleCategories)
		);
	}

	public function testCreateCategoryListWithConfigurationValueSetToTextCanReturnMultipleCategoryTitles() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'text');
		$multipleCategories =
			array(
				99 => array(
					'title' => 'foo',
					'icon' => 'foo.gif',
				),
				100 => array(
					'title' => 'bar',
					'icon' => 'foo2.gif',
				)
		);

		$this->assertRegExp(
			'/foo.*bar/',
			$this->fixture->createCategoryList($multipleCategories)
		);
	}

	public function testCreateCategoryListWithConfigurationValueSetToIconDoesNotUseCommasAsSeparators() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'icon');
		$this->testingFramework->createDummyFile('foo.gif');
		$this->testingFramework->createDummyFile('foo2.gif');
		$multipleCategories =
			array(
				99 => array(
					'title' => 'foo',
					'icon' => 'foo.gif',
				),
				100 => array(
					'title' => 'bar',
					'icon' => 'foo2.gif',
				)
		);

		$this->assertNotContains(
			',',
			$this->fixture->createCategoryList($multipleCategories)
		);
	}

	public function testCreateCategoryForCategoryWithoutImageAndListWithConfigurationValueSetToIconUsesCommasAsSeparators() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'icon');
		$multipleCategories =
			array(
				99 => array(
					'title' => 'foo',
					'icon' => '',
				),
				100 => array(
					'title' => 'bar',
					'icon' => 'foo2.gif',
				)
		);

		$this->assertRegExp(
			'/foo.*,.*bar/',
			$this->fixture->createCategoryList($multipleCategories)
		);
	}

	public function testCreateCategoryListWithConfigurationValueSetToTextUsesCommasAsSeparators() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'text');
		$multipleCategories =
			array(
				99 => array(
					'title' => 'foo',
					'icon' => 'foo.gif',
				),
				100 => array(
					'title' => 'bar',
					'icon' => 'foo2.gif',
				)
		);

		$this->assertRegExp(
			'/foo.*,.*bar/',
			$this->fixture->createCategoryList($multipleCategories)
		);
	}

	public function testCreateCategoryListWithConfigurationValueSetToBothUsesCommasAsSeparators() {
		$this->fixture->setConfigurationValue('categoriesInListView', 'both');
		$this->testingFramework->createDummyFile('foo.gif');
		$this->testingFramework->createDummyFile('foo2.gif');
		$multipleCategories =
			array(
				99 => array(
					'title' => 'foo',
					'icon' => 'foo.gif',
				),
				100 => array(
					'title' => 'bar',
					'icon' => 'foo2.gif',
				)
		);

		$this->assertRegExp(
			'/foo.*,.*bar/',
			$this->fixture->createCategoryList($multipleCategories)
		);
	}
}
?>