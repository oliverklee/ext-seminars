<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2012 Niels Pardon (mail@niels-pardon.de)
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

/**
 * Testcase for the tx_seminars_BackEndExtJs_Ajax_AbstractList class in the
 * "seminars" extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_AbstractListTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework = NULL;

	/**
	 * back-up of $_POST
	 *
	 * @var array
	 */
	private $postBackup;

	public function setUp() {
		$this->postBackup = $_POST;
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixture = new tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$_POST = $this->postBackup;
		unset($this->fixture, $this->testingFramework, $this->postBackup);
	}


	//////////////////////////////////
	// Tests regarding createList().
	//////////////////////////////////

	/**
	 * @test
	 */
	public function createListWithoutIdPostParameterCallsIsPageUidValidWithZero() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(0);

		unset($_POST['id']);
		$fixture->createList();
	}

	/**
	 * @test
	 */
	public function createListWithNegativeIdPostParameterCallsIsPageUidValidWithNegativePageUid() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(-1);

		$_POST['id'] = -1;
		$fixture->createList();
	}

	/**
	 * @test
	 */
	public function createListWithZeroIdPostParameterCallsIsPageUidValidWithZeroPageUid() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(0);

		$_POST['id'] = 0;
		$fixture->createList();
	}

	/**
	 * @test
	 */
	public function createListWithPositiveIdPostParameterCallsIsPageUidValidWithPositivePageUid() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(42);

		$_POST['id'] = 42;
		$fixture->createList();
	}

	/**
	 * @test
	 */
	public function createListWithNonIntegerPostParameterCallsIsPageUidValidWithZeroPageUid() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->with(0);

		$_POST['id'] = 'foo';
		$fixture->createList();
	}

	/**
	 * @test
	 */
	public function createListWithInvalidPageUidReturnsSuccessFalse() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(FALSE));

		$this->assertEquals(
			array('success' => FALSE),
			$fixture->createList()
		);
	}

	/**
	 * @test
	 */
	public function createListWithHasAccessNotReturnsSuccessFalse() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('hasAccess', 'isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->once())
			->method('hasAccess')
			->will($this->returnValue(TRUE));

		$result = $fixture->createList();

		$this->assertTrue($result['success']);
	}

	/**
	 * @test
	 */
	public function createListWithDoesNotHaveAccessListReturnsSuccessFalse() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('hasAccess', 'isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->once())
			->method('hasAccess')
			->will($this->returnValue(FALSE));

		$this->assertEquals(
			array('success' => FALSE),
			$fixture->createList()
		);
	}

	/**
	 * @test
	 */
	public function createListWithRetrieveModelsReturningEmptyListReturnsSuccessTrueAndEmptyRows() {
		$mapper = $this->getMock(
			'tx_oelib_tests_fixtures_TestingMapper',
			array('countByPageUid')
		);
		$mapper->expects($this->any())
			->method('countByPageUid')
			->will($this->returnValue(0));
		tx_oelib_MapperRegistry::set('tx_oelib_tests_fixtures_TestingMapper', $mapper);

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('retrieveModels', 'isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->atLeastOnce())
			->method('retrieveModels')
			->will($this->returnValue(new tx_oelib_List()));

		$this->assertEquals(
			array('success' => TRUE, 'total' => 0, 'rows' => array()),
			$fixture->createList()
		);
	}

	/**
	 * @test
	 */
	public function createListWithRetrieveModelsReturningOneModelReturnsSuccessTrueAndOneRow() {
		$list = new tx_oelib_List();

		$mapper = $this->getMock(
			'tx_oelib_tests_fixtures_TestingMapper',
			array('countByPageUid')
		);
		$mapper->expects($this->any())
			->method('countByPageUid')
			->will($this->returnValue(1));
		tx_oelib_MapperRegistry::set('tx_oelib_tests_fixtures_TestingMapper', $mapper);

		$model = $mapper->getLoadedTestingModel(array());
		$list->add($model);

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('retrieveModels', 'isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->atLeastOnce())
			->method('retrieveModels')
			->will($this->returnValue($list));

		$this->assertEquals(
			array(
				'success' => TRUE,
				'total' => 1,
				'rows' => array(array(
					'uid' => $model->getUid(),
					'pid' => $model->getPageUid(),
				))
			),
			$fixture->createList()
		);
	}

	/**
	 * @test
	 */
	public function createListWithRetrieveModelsReturningTwoModelsReturnsSuccessTrueAndTwoRows() {
		$list = new tx_oelib_List();

		$mapper = $this->getMock(
			'tx_oelib_tests_fixtures_TestingMapper',
			array('countByPageUid')
		);
		$mapper->expects($this->any())
			->method('countByPageUid')
			->will($this->returnValue(2));
		tx_oelib_MapperRegistry::set('tx_oelib_tests_fixtures_TestingMapper', $mapper);

		$model1 = $mapper->getLoadedTestingModel(array());
		$list->add($model1);

		$model2 = $mapper->getLoadedTestingModel(array());
		$list->add($model2);

		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('retrieveModels', 'isPageUidValid')
		);
		$fixture->expects($this->any())
			->method('isPageUidValid')
			->will($this->returnValue(TRUE));
		$fixture->expects($this->atLeastOnce())
			->method('retrieveModels')
			->will($this->returnValue($list));

		$this->assertEquals(
			array(
				'success' => TRUE,
				'total' => 2,
				'rows' => array(
					array(
						'uid' => $model1->getUid(),
						'pid' => $model1->getPageUid(),
					),
					array(
						'uid' => $model2->getUid(),
						'pid' => $model2->getPageUid(),
					),
				)
			),
			$fixture->createList()
		);
	}


	//////////////////////////////////////
	// Tests regarding isPageUidValid().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function isPageUidValidWithZeroPageUidReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isPageUidValid(0)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithNegativePageUidReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isPageUidValid(-1)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithNonExistingPageUidReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isPageUidValid(
				$this->testingFramework->getAutoIncrement('pages')
			)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithExistingNonSystemFolderUidReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isPageUidValid(
				$this->testingFramework->createFrontEndPage()
			)
		);
	}

	/**
	 * @test
	 */
	public function isPageUidValidWithExistingSystemFolderUidReturnsTrue() {
		$this->assertTrue(
			$this->fixture->isPageUidValid(
				$this->testingFramework->createSystemFolder()
			)
		);
	}


	//////////////////////////////////////
	// Tests regarding retrieveModels().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function retrieveModelsReturnsListReturnedByMapper() {
		$mapper = $this->getMock(
			'tx_oelib_tests_fixtures_TestingMapper',
			array('findByPageUid')
		);
		tx_oelib_MapperRegistry::set(
			'tx_oelib_tests_fixtures_TestingMapper', $mapper
		);

		$list = new tx_oelib_List();
		$mapper->expects($this->atLeastOnce())
			->method('findByPageUid')
			->will($this->returnValue($list));

		$this->assertSame(
			$list,
			$this->fixture->retrieveModels()
		);
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithPageUidWithSubFolderCallsMapperFindByPageUidWithRecursivePageList() {
		$parent = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($parent);

		$recursivePageList = tx_oelib_db::createRecursivePageList($parent, 255);

		$mapper = $this->getMock(
			'tx_oelib_tests_fixtures_TestingMapper',
			array('findByPageUid')
		);
		$mapper->expects($this->atLeastOnce())
			->method('findByPageUid')
			->with($recursivePageList);

		tx_oelib_MapperRegistry::set(
			'tx_oelib_tests_fixtures_TestingMapper', $mapper
		);

		$_POST['id'] = $parent;
		$this->fixture->retrieveModels();
	}

	/**
	 * @test
	 */
	public function retrieveModelsWithStartAndLimitPostParametersCallsFindByPageUidWithCombinedStartAndLimit() {
		$fixture = $this->getMock(
			'tx_seminars_tests_fixtures_BackEndExtJs_Ajax_TestingAbstractList',
			array('getRecursivePageList')
		);
		$fixture->expects($this->any())
			->method('getRecursivePageList')
			->will($this->returnValue(''));

		$mapper = $this->getMock(
			'tx_oelib_tests_fixtures_TestingMapper',
			array('findByPageUid')
		);
		$mapper->expects($this->atLeastOnce())
			->method('findByPageUid')
			->with('', '', '1,2');

		tx_oelib_MapperRegistry::set(
			'tx_oelib_tests_fixtures_TestingMapper', $mapper
		);

		$_POST['start'] = 1;
		$_POST['limit'] = 2;
		$this->fixture->retrieveModels();
	}
}
?>