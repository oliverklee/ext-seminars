<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
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
 * Testcase for the tx_seminars_Model_FrontEndUserGroup class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_FrontEndUserGroup_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_FrontEndUserGroup the object to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_FrontEndUserGroup();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	///////////////////////////////////////
	// Tests concerning getPublishSetting
	///////////////////////////////////////

	public function test_getPublishSetting_WithoutPublishSetting_ReturnsPublishAll() {
		$this->fixture->setData(array());

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSetting_WithPublishSettingSetToZero_ReturnsPublishAll() {
		$this->fixture->setData(array('tx_seminars_publish_events' => 0));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSetting_WithPublishSettingSetToOne_ReturnsHideNew() {
		$this->fixture->setData(array('tx_seminars_publish_events' => 1));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSetting_WithPublishSettingSetToTwo_ReturnsHideEdited() {
		$this->fixture->setData(array('tx_seminars_publish_events' => 2));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning getAuxiliaryRecordsPid().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithoutPidReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithPidReturnsPid() {
		$this->fixture->setData(array('tx_seminars_auxiliary_records_pid' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning hasAuxiliaryRecordsPid().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasAuxiliaryRecordsPidWithoutPidReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function hasAuxiliaryRecordsPidWithPidReturnsTrue() {
		$this->fixture->setData(array('tx_seminars_auxiliary_records_pid' => 42));

		$this->assertTrue(
			$this->fixture->hasAuxiliaryRecordsPid()
		);
	}


	//////////////////////////////////
	// Tests concerning the reviewer
	//////////////////////////////////

	public function test_hasReviewerForGroupWithoutReviewer_ReturnsFalse() {
		$this->fixture->setData(array('tx_seminars_reviewer' => null));

		$this->assertFalse(
			$this->fixture->hasReviewer()
		);
	}

	public function test_hasReviewerForGroupWithReviewer_ReturnsTrue() {
		$backEndUser = tx_oelib_ObjectFactory::make('tx_oelib_Model_BackEndUser');

		$this->fixture->setData(array('tx_seminars_reviewer' => $backEndUser));

		$this->assertTrue(
			$this->fixture->hasReviewer()
		);
	}

	public function test_getReviewerForGroupWithoutReviewer_ReturnsNull() {
		$this->fixture->setData(array('tx_seminars_reviewer' => null));

		$this->assertNull(
			$this->fixture->getReviewer()
		);
	}

	public function test_getReviewerForGroupWithReviewer_ReturnsReviewer() {
		$backEndUser = tx_oelib_ObjectFactory::make('tx_oelib_Model_BackEndUser');

		$this->fixture->setData(array('tx_seminars_reviewer' => $backEndUser));

		$this->assertSame(
			$backEndUser,
			$this->fixture->getReviewer()
		);
	}


	//////////////////////////////////////////////////
	// Tests concerning the event record storage PID
	//////////////////////////////////////////////////

	public function test_hasEventRecordPidForNoPidSet_ReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasEventRecordPid()
		);
	}

	public function test_hasEventRecordPidForPidSet_ReturnsTrue() {
		$this->fixture->setData(array('tx_seminars_events_pid' => 42));

		$this->assertTrue(
			$this->fixture->hasEventRecordPid()
		);
	}

	public function test_getEventRecordPidForNoPidSet_ReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getEventRecordPid()
		);
	}

	public function test_getEventRecordPidForPidSet_ReturnsThisPid() {
		$this->fixture->setData(array('tx_seminars_events_pid' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getEventRecordPid()
		);
	}

	//////////////////////////////////////////
	// Tests concerning getDefaultCategories
	//////////////////////////////////////////

	public function test_getDefaultCategoriesForNoCategories_ReturnsAList() {
		$this->fixture->setData(array(
			'tx_seminars_default_categories' => tx_oelib_ObjectFactory::make('tx_oelib_List'))
		);

		$this->assertTrue(
			$this->fixture->getDefaultCategories() instanceOf tx_oelib_List
		);
	}

	public function test_getDefaultCategoriesForOneAssignedCategory_ReturnsThisCategoryInList() {
		$list = tx_oelib_ObjectFactory::make('tx_oelib_List');
		$category = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
			->getNewGhost();

		$list->add($category);
		$this->fixture->setData(array('tx_seminars_default_categories' => $list));

		$this->assertSame(
			$category,
			$this->fixture->getDefaultCategories()->first()
		);
	}


	//////////////////////////////////////////
	// Tests concerning hasDefaultCategories
	//////////////////////////////////////////

	public function test_hasDefaultCategoriesForNoAssignedCategories_ReturnsFalse() {
		$this->fixture->setData(array(
			'tx_seminars_default_categories'
				=> tx_oelib_ObjectFactory::make('tx_oelib_List'))
		);

		$this->assertFalse(
			$this->fixture->hasDefaultCategories()
		);
	}

	public function test_hasDefaultCategoriesForOneAssignedCategory_ReturnsTrue() {
		$list = tx_oelib_ObjectFactory::make('tx_oelib_List');
		$list->add(
			tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
				->getNewGhost()
		);

		$this->fixture->setData(array('tx_seminars_default_categories' => $list));

		$this->assertTrue(
			$this->fixture->hasDefaultCategories()
		);
	}
}
?>