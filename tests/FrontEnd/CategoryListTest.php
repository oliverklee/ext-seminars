<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
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
	 * @var int the UID of a seminar to which the fixture relates
	 */
	private $seminarUid;

	/**
	 * @var int PID of a dummy system folder
	 */
	private $systemFolderPid = 0;

	protected function setUp() {
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', FALSE);

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

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	/*
	 * Tests for render
	 */

	public function testRenderCreatesEmptyCategoryList() {
		$otherSystemFolderUid = $this->testingFramework->createSystemFolder();
		$this->fixture->setConfigurationValue('pages', $otherSystemFolderUid);

		$output = $this->fixture->render();

		self::assertNotContains(
			'<table',
			$output
		);
		self::assertContains(
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
		self::assertContains(
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
		self::assertContains(
			'first category',
			$output
		);
		self::assertContains(
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
		self::assertTrue(
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

		self::assertContains(
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

		self::assertNotContains(
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

		self::assertContains(
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

		self::assertNotContains(
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

		self::assertContains(
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

		self::assertContains(
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

		self::assertNotContains(
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

		self::assertContains(
			'tx_seminars_pi1[category]='.$categoryUid,
			$this->fixture->render()
		);
	}


	/*
	 * Tests concerning createCategoryList
	 */

	public function testCreateCategoryListWithNoGivenCategoriesReturnsEmptyString() {
		self::assertEquals(
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

		self::assertEquals(
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

		self::assertNotContains(
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

		self::assertEquals(
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

		self::assertContains(
			'test &amp; more',
			$this->fixture->createCategoryList($singleCategory)
		);
		self::assertNotContains(
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

		self::assertSame(
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

		self::assertRegExp(
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


		self::assertNotRegExp(
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

		self::assertRegExp(
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

		self::assertRegExp(
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

		self::assertNotContains(
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

		self::assertRegExp(
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

		self::assertRegExp(
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

		self::assertRegExp(
			'/foo.*,.*bar/',
			$this->fixture->createCategoryList($multipleCategories)
		);
	}
}