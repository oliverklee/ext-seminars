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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_ViewHelper_CommaSeparatedTitlesTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_ViewHelper_CommaSeparatedTitles
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_oelib_List
	 */
	private $list;

	/**
	 * @var string
	 */
	const TIME_FORMAT = '%H:%M';

	protected function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');
		$this->list = new tx_oelib_List();
		$this->fixture = new tx_seminars_ViewHelper_CommaSeparatedTitles();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}

	/**
	 * @test
	 */
	public function renderWithEmptyListReturnsEmptyString() {
		self::assertSame(
			'',
			$this->fixture->render($this->list)
		);
	}

	/**
	 * @test
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage All elements in $list must implement the interface tx_seminars_Interface_Titled.
	 * @expectedExceptionCode 1333658899
	 */
	public function renderWithElementsInListWithoutGetTitleMethodThrowsBadMethodCallException() {
		$model = new tx_seminars_tests_fixtures_Model_UntitledTestingModel();
		$model->setData(array());

		$this->list->add($model);

		$this->fixture->render($this->list);
	}

	/**
	 * @test
	 */
	public function renderWithOneElementListReturnsOneElementsTitle() {
		$model = new tx_seminars_tests_fixtures_Model_TitledTestingModel();
		$model->setData(array('title' => 'Testing model'));

		$this->list->add($model);

		self::assertSame(
			$model->getTitle(),
			$this->fixture->render($this->list)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTwoElementsListReturnsTwoElementTitlesSeparatedByComma() {
		$firstModel = new tx_seminars_tests_fixtures_Model_TitledTestingModel();
		$firstModel->setData(array('title' => 'First testing model'));
		$secondModel = new tx_seminars_tests_fixtures_Model_TitledTestingModel();
		$secondModel->setData(array('title' => 'Second testing model'));

		$this->list->add($firstModel);
		$this->list->add($secondModel);

		self::assertSame(
			$firstModel->getTitle() . ', ' . $secondModel->getTitle(),
			$this->fixture->render($this->list)
		);
	}

	/**
	 * @test
	 */
	public function renderWithOneElementListReturnsOneElementsTitleHtmlspecialchared() {
		$model = new tx_seminars_tests_fixtures_Model_TitledTestingModel();
		$model->setData(array('title' => '<test>Testing model</test>'));

		$this->list->add($model);

		self::assertSame(
			htmlspecialchars($model->getTitle()),
			$this->fixture->render($this->list)
		);
	}
}