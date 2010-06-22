<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_seminars_Mapper_FrontEndUserGroup class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Mapper_FrontEndUserGroupTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Mapper_FrontEndUserGroup the object to test
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework the testing framework	 *
	 */
	private $testingFramework;

	public function setUp() {
		$this->fixture = new tx_seminars_Mapper_FrontEndUserGroup();
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
	}

	public function tearDown() {
		$this->fixture->__destruct();
		$this->testingFramework->cleanUp();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	public function test_Mapper_ForGhost_ReturnsSeminarsFrontEndUserGroupInstance() {
		$this->assertTrue(
			$this->fixture->getNewGhost()
				instanceof tx_seminars_Model_FrontEndUserGroup
		);
	}


	//////////////////////////////////
	// Tests concerning the reviewer
	//////////////////////////////////

	public function test_FrontEndUserGroup_CanReturnBackEndUserModel() {
		$backEndUser = tx_oelib_MapperRegistry::get(
			'tx_oelib_Mapper_BackEndUser')->getNewGhost();
		$frontEndUserGroup = $this->fixture->getLoadedTestingModel(
			array('tx_seminars_reviewer' => $backEndUser->getUid())
		);

		$this->assertTrue(
			$this->fixture->find($frontEndUserGroup->getUid())->getReviewer()
				instanceof tx_oelib_Model_BackEndUser
		);
	}


	////////////////////////////////////////////
	// Tests concerning the default categories
	////////////////////////////////////////////

	public function test_FrontEndUserGroup_ReturnsListOfCategories() {
		$categoryUid = $this->testingFramework->createRecord(
			'tx_seminars_categories', array()
		);
		$frontEndUserGroupUid = $this->testingFramework->createFrontEndUserGroup();

		$this->testingFramework->createRelationAndUpdateCounter(
			'fe_groups', $frontEndUserGroupUid, $categoryUid, 'tx_seminars_default_categories'
		);

		$this->assertTrue(
			$this->fixture->find($frontEndUserGroupUid)->getDefaultCategories()->first()
				instanceof tx_seminars_Model_Category
		);
	}
}
?>