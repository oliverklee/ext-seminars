<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2013 Niels Pardon (mail@niels-pardon.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_BackEndExtJs_Ajax_StateProviderTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_BackEndExtJs_Ajax_StateProvider
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

	/**
	 * back-up of $GLOBALS['BE_USER']
	 *
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackUp = NULL;

	public function setUp() {
		$this->backEndUserBackUp = $GLOBALS['BE_USER'];
		$this->postBackup = $_POST;
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->fixture = new tx_seminars_BackEndExtJs_Ajax_StateProvider();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$_POST = $this->postBackup;
		$GLOBALS['BE_USER'] = $this->backEndUserBackUp;
		unset($this->fixture, $this->testingFramework, $this->postBackup, $this->backEndUserBackUp);
	}


	////////////////////////////////
	// Tests regarding setState().
	////////////////////////////////

	/**
	 * @test
	 */
	public function setStateReturnsSuccessTrue() {
		$_POST['name'] = 'testing name';
		$_POST['value'] = json_encode('testing value');

		$this->assertEquals(
			array('success' => TRUE),
			$this->fixture->setState()
		);
	}

	/**
	 * @test
	 */
	public function setSateWithEmptyComponentNameReturnsSuccessFalse() {
		$_POST['name'] = '';
		$_POST['value'] = json_encode('testing value');

		$this->assertEquals(
			array('success' => FALSE),
			$this->fixture->setState()
		);
	}

	/**
	 * @test
	 */
	public function setSateWithUndecodableExtJsStateDataReturnsSuccessFalse() {
		$_POST['name'] = 'testing_name';
		$_POST['value'] = ']}invalid JSON{[';

		$this->assertEquals(
			array('success' => FALSE),
			$this->fixture->setState()
		);
	}

	/**
	 * @test
	 */
	public function setStateSetsExtJsStateDataToUserConfig() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('writeUC')
		);
		$GLOBALS['BE_USER']->uc = array();

		$_POST['name'] = 'testing name';
		$_POST['value'] = json_encode('testing value');

		$this->fixture->setState();

		$this->assertEquals(
			array('testing name' => 'testing value'),
			$GLOBALS['BE_USER']->uc['tx_seminars_BackEndExtJs_State']
		);
	}

	/**
	 * @test
	 */
	public function setStateWritesExtJsStateDataInUserConfigToDatabase() {
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('writeUC')
		);
		$GLOBALS['BE_USER']->uc = array();

		$_POST['name'] = 'testing name';
		$_POST['value'] = json_encode('testing value');

		$GLOBALS['BE_USER']->expects($this->once())
			->method('writeUC')
			->with('tx_seminars_BackEndExtJs_State');

		$this->fixture->setState();
	}


	////////////////////////////////
	// Tests regarding getState().
	////////////////////////////////

	/**
	 * @test
	 */
	public function getStateSetsResponseContentToExtJsStateDataInUserConfig() {
		$this->backEndUserBackUp = $GLOBALS['BE_USER'];
		$GLOBALS['BE_USER'] = $this->getMock(
			't3lib_beUserAuth',
			array('writeUC')
		);
		$GLOBALS['BE_USER']->uc = array(
			'tx_seminars_BackEndExtJs_State' => array(
				'testing name' => 'testing value',
			),
		);

		$this->assertEquals(
			array(
				'success' => TRUE,
				'data' => array(array(
					'name' => 'testing name',
					'value' => 'testing value',
				)),
			),
			$this->fixture->getState()
		);
	}
}