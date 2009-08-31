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
 * Testcase for the tx_seminars_Model_FrontEndUser class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_Model_FrontEndUser_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_FrontEndUser the object to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_FrontEndUser();
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	////////////////////////////////////////
	// Tests concerning getPublishSettings
	////////////////////////////////////////

	public function test_getPublishSettings_ForUserWithOneGroupAndGroupPublishSettingZero_ReturnsPublishAll() {
		$userGroup = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup')
			->getLoadedTestingModel(array(
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
			)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSettings_ForUserWithOneGroupAndGroupPublishSettingOne_ReturnsHideNew() {
		$userGroup = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup')
			->getLoadedTestingModel(array(
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
			)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSettings_ForUserWithOneGroupAndGroupPublishSettingTwo_ReturnsHideEdited() {
		$userGroup = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup')
			->getLoadedTestingModel(array(
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
			)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSettings_ForUserWithoutGroup_ReturnsPublishAll() {
		$list = new tx_oelib_List();
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSettings_ForUserWithTwoGroupsAndGroupPublishSettingZeroAndOne_ReturnsHideNew() {
		$groupMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		));

		$userGroup2 = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		));

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSettings_ForUserWithTwoGroupsAndGroupPublishSettingOneAndTwo_ReturnsHideEdited() {
		$groupMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		));

		$userGroup2 = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		));

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSettings_ForUserWithTwoGroupsAndGroupPublishSettingTwoAndZero_ReturnsHideEdited() {
		$groupMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
		));

		$userGroup2 = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
		));

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}

	public function test_getPublishSettings_ForUserWithTwoGroupsAndBothGroupPublishSettingsOne_ReturnsHideNew() {
		$groupMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup');
		$userGroup = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		));

		$userGroup2 = $groupMapper->getLoadedTestingModel(array(
			'tx_seminars_publish_events'
				=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
		));

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
			$this->fixture->getPublishSetting()
		);
	}


	///////////////////////////////////////////////
	// Tests concerning getAuxiliaryRecordsPid().
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithoutUserGroupReturnsZero() {
		$list = new tx_oelib_List();
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			0,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithUserGroupWithoutPidReturnsZero() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(array());

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			0,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithUserGroupWithPidReturnsPid() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_auxiliary_records_pid' => 42)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			42,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(array());

		$userGroup2 = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_auxiliary_records_pid' => 42)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			42,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnPidOfFirstUserGroup() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_auxiliary_records_pid' => 24)
		);

		$userGroup2 = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_auxiliary_records_pid' => 42)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			24,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getReviewerFromGroup
	//////////////////////////////////////////

	public function test_getReviewerFromGroupForUserWithoutGroups_ReturnsNull() {
		$list = new tx_oelib_List();
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertNull(
			$this->fixture->getReviewerFromGroup()
		);
	}

	public function test_getReviewerFromGroupForUserWithGroupWithNoReviewer_ReturnsNull() {
		$userGroup = tx_oelib_ObjectFactory::make('tx_seminars_Model_FrontEndUserGroup');
		$userGroup->setData(array('tx_seminars_reviewer' => null));

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertNull(
			$this->fixture->getReviewerFromGroup()
		);
	}

	public function test_getReviewerFromGroupForUserWithGroupWithReviewer_ReturnsReviewer() {
		$backEndUser = tx_oelib_ObjectFactory::make('tx_oelib_Model_BackEndUser');

		$userGroup = tx_oelib_ObjectFactory::make(
			'tx_seminars_Model_FrontEndUserGroup'
		);
		$userGroup->setData(array('tx_seminars_reviewer' => $backEndUser));

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertSame(
			$backEndUser,
			$this->fixture->getReviewerFromGroup()
		);
	}

	public function test_getReviewerFromGroupForUserWithTwoGroupsOneWithReviewerOneWithoutReviewer_ReturnsReviewer() {
		$backEndUser = tx_oelib_ObjectFactory::make('tx_oelib_Model_BackEndUser');

		$userGroup1 = tx_oelib_ObjectFactory::make(
			'tx_seminars_Model_FrontEndUserGroup'
		);
		$userGroup2 = tx_oelib_ObjectFactory::make(
			'tx_seminars_Model_FrontEndUserGroup'
		);

		$userGroup1->setData(array('tx_seminars_reviewer' => null));
		$userGroup2->setData(array('tx_seminars_reviewer' => $backEndUser));

		$list = new tx_oelib_List();
		$list->add($userGroup1);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertSame(
			$backEndUser,
			$this->fixture->getReviewerFromGroup()
		);
	}

	public function test_getReviewerFromGroupForUserWithTwoGroupsWithReviewers_ReturnsReviewerOfFirstGroup() {
		$backEndUser1 = tx_oelib_ObjectFactory::make('tx_oelib_Model_BackEndUser');
		$backEndUser2 = tx_oelib_ObjectFactory::make('tx_oelib_Model_BackEndUser');

		$userGroup1 = tx_oelib_ObjectFactory::make(
			'tx_seminars_Model_FrontEndUserGroup'
		);
		$userGroup2 = tx_oelib_ObjectFactory::make(
			'tx_seminars_Model_FrontEndUserGroup'
		);

		$userGroup1->setData(array('tx_seminars_reviewer' => $backEndUser1));
		$userGroup2->setData(array('tx_seminars_reviewer' => $backEndUser2));

		$list = new tx_oelib_List();
		$list->add($userGroup1);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertSame(
			$backEndUser1,
			$this->fixture->getReviewerFromGroup()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getEventRecordsPid()
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEventRecordsPidWithoutUserGroupReturnsZero() {
		$list = new tx_oelib_List();
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			0,
			$this->fixture->getEventRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getEventRecordsPidWithUserGroupWithoutPidReturnsZero() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(array());

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			0,
			$this->fixture->getEventRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getEventRecordsPidWithUserGroupWithPidReturnsPid() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_events_pid' => 42)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			42,
			$this->fixture->getEventRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getEventRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(array());

		$userGroup2 = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_events_pid' => 42)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			42,
			$this->fixture->getEventRecordsPid()
		);
	}

	/**
	 * @test
	 */
	public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnsPidOfFirstUserGroup() {
		$groupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup'
		);
		$userGroup = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_events_pid' => 24)
		);

		$userGroup2 = $groupMapper->getLoadedTestingModel(
			array('tx_seminars_events_pid' => 42)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			24,
			$this->fixture->getEventRecordsPid()
		);
	}


	///////////////////////////////////////////////////
	// Tests concerning getDefaultCategoriesFromGroup
	///////////////////////////////////////////////////

	public function test_getDefaultCategoriesFromGroupForUserWithGroupWithoutCategories_ReturnsEmptyList() {
		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => new tx_oelib_List())
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->fixture->getDefaultCategoriesFromGroup()->isEmpty()
		);
	}

	public function test_getDefaultCategoriesFromGroupForUserWithOneGroupWithCategory_ReturnsThisCategory() {
		$categories = new tx_oelib_List();
		$categories->add(
			tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
				->getNewGhost()
		);

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => $categories)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			1,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	public function test_getDefaultCategoriesFromGroupForUserWithOneGroupWithTwoCategories_ReturnsTwoCategories() {
		$categoryMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Category');
		$categories = new tx_oelib_List();
		$categories->add($categoryMapper->getNewGhost());
		$categories->add($categoryMapper->getNewGhost());

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => $categories)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			2,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	public function test_getDefaultCategoriesFromGroupForUserWithTwoGroupsOneWithCategory_ReturnsOneCategory() {
		$frontEndGroupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup');
		$userGroup1 = $frontEndGroupMapper->getNewGhost();
		$userGroup1->setData(
			array('tx_seminars_default_categories' => new tx_oelib_List())
		);

		$categories = new tx_oelib_List();
		$categories->add(
			tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
				->getNewGhost()
		);

		$userGroup2 = $frontEndGroupMapper->getNewGhost();
		$userGroup2->setData(
			array('tx_seminars_default_categories' => $categories)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup1);
		$list->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			1,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	public function test_getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithSameCategory_ReturnsOneCategory() {
		$categoryGhost = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Category')->getNewGhost();
		$categories = new tx_oelib_List();
		$categories->add($categoryGhost);

		$frontEndGroupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup');
		$userGroup1 = $frontEndGroupMapper->getNewGhost();
		$userGroup1->setData(
			array('tx_seminars_default_categories' => $categories)
		);

		$userGroup2 = $frontEndGroupMapper->getNewGhost();
		$userGroup2->setData(
			array('tx_seminars_default_categories' => $categories)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup1);
		$list->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			1,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	public function test_getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithCategories_ReturnsTwoCategories() {
		$categoryMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_Category'
		);
		$frontEndGroupMapper = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup');

		$categoryGhost1 = $categoryMapper->getNewGhost();
		$categories1 = new tx_oelib_List();
		$categories1->add($categoryGhost1);
		$userGroup1 = $frontEndGroupMapper->getNewGhost();
		$userGroup1->setData(
			array('tx_seminars_default_categories' => $categories1)
		);

		$categoryGhost2 = $categoryMapper->getNewGhost();
		$categories2 = new tx_oelib_List();
		$categories2->add($categoryGhost2);
		$userGroup2 = $frontEndGroupMapper->getNewGhost();
		$userGroup2->setData(
			array('tx_seminars_default_categories' => $categories2)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup1);
		$list->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertEquals(
			2,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}


	//////////////////////////////////////////
	// Tests concerning hasDefaultCategories
	//////////////////////////////////////////

	public function test_hasDefaultCategoriesForUserWithOneGroupWithoutCategory_ReturnsFalse() {
		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => new tx_oelib_List())
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertFalse(
			$this->fixture->hasDefaultCategories()
		);
	}

	public function test_hasDefaultCategoriesForUserWithOneGroupWithCategory_ReturnsTrue() {
		$categories = new tx_oelib_List();
		$categories->add(
			tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Category')
				->getNewGhost()
		);

		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => $categories)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		$this->assertTrue(
			$this->fixture->hasDefaultCategories()
		);
	}
}
?>