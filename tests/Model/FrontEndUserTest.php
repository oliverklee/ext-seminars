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
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_FrontEndUserTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_FrontEndUser the object to test
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_FrontEndUser();
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}


	////////////////////////////////////////
	// Tests concerning getPublishSettings
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingZeroReturnsPublishAll() {
		$userGroup = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup')
			->getLoadedTestingModel(array(
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
			)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		self::assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingOneReturnsHideNew() {
		$userGroup = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup')
			->getLoadedTestingModel(array(
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW
			)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		self::assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
			$this->fixture->getPublishSetting()
		);
	}

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingTwoReturnsHideEdited() {
		$userGroup = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_FrontEndUserGroup')
			->getLoadedTestingModel(array(
				'tx_seminars_publish_events'
					=> tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
			)
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		self::assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithoutGroupReturnsPublishAll() {
		$list = new tx_oelib_List();
		$this->fixture->setData(array('usergroup' => $list));

		self::assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
			$this->fixture->getPublishSetting()
		);
	}

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingZeroAndOneReturnsHideNew() {
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

		self::assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
			$this->fixture->getPublishSetting()
		);
	}

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingOneAndTwoReturnsHideEdited() {
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

		self::assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingTwoAndZeroReturnsHideEdited() {
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

		self::assertEquals(
			tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
			$this->fixture->getPublishSetting()
		);
	}

	/**
	 * @test
	 */
	public function getPublishSettingsForUserWithTwoGroupsAndBothGroupPublishSettingsOneReturnsHideNew() {
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
			24,
			$this->fixture->getAuxiliaryRecordsPid()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getReviewerFromGroup
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function getReviewerFromGroupForUserWithoutGroupsReturnsNull() {
		$list = new tx_oelib_List();
		$this->fixture->setData(array('usergroup' => $list));

		self::assertNull(
			$this->fixture->getReviewerFromGroup()
		);
	}

	/**
	 * @test
	 */
	public function getReviewerFromGroupForUserWithGroupWithNoReviewerReturnsNull() {
		$userGroup = new tx_seminars_Model_FrontEndUserGroup();
		$userGroup->setData(array('tx_seminars_reviewer' => NULL));

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		self::assertNull(
			$this->fixture->getReviewerFromGroup()
		);
	}

	/**
	 * @test
	 */
	public function getReviewerFromGroupForUserWithGroupWithReviewerReturnsReviewer() {
		$backEndUser = new tx_oelib_Model_BackEndUser();

		$userGroup = new tx_seminars_Model_FrontEndUserGroup();
		$userGroup->setData(array('tx_seminars_reviewer' => $backEndUser));

		$list = new tx_oelib_List();
		$list->add($userGroup);

		$this->fixture->setData(array('usergroup' => $list));

		self::assertSame(
			$backEndUser,
			$this->fixture->getReviewerFromGroup()
		);
	}

	/**
	 * @test
	 */
	public function getReviewerFromGroupForUserWithTwoGroupsOneWithReviewerOneWithoutReviewerReturnsReviewer() {
		$backEndUser = new tx_oelib_Model_BackEndUser();

		$userGroup1 = new tx_seminars_Model_FrontEndUserGroup();
		$userGroup2 = new tx_seminars_Model_FrontEndUserGroup();

		$userGroup1->setData(array('tx_seminars_reviewer' => NULL));
		$userGroup2->setData(array('tx_seminars_reviewer' => $backEndUser));

		$list = new tx_oelib_List();
		$list->add($userGroup1);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		self::assertSame(
			$backEndUser,
			$this->fixture->getReviewerFromGroup()
		);
	}

	/**
	 * @test
	 */
	public function getReviewerFromGroupForUserWithTwoGroupsWithReviewersReturnsReviewerOfFirstGroup() {
		$backEndUser1 = new tx_oelib_Model_BackEndUser();
		$backEndUser2 = new tx_oelib_Model_BackEndUser();

		$userGroup1 = new tx_seminars_Model_FrontEndUserGroup();
		$userGroup2 = new tx_seminars_Model_FrontEndUserGroup();

		$userGroup1->setData(array('tx_seminars_reviewer' => $backEndUser1));
		$userGroup2->setData(array('tx_seminars_reviewer' => $backEndUser2));

		$list = new tx_oelib_List();
		$list->add($userGroup1);
		$list->add($userGroup2);

		$this->fixture->setData(array('usergroup' => $list));

		self::assertSame(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
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

		self::assertEquals(
			24,
			$this->fixture->getEventRecordsPid()
		);
	}


	///////////////////////////////////////////////////
	// Tests concerning getDefaultCategoriesFromGroup
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getDefaultCategoriesFromGroupForUserWithGroupWithoutCategoriesReturnsEmptyList() {
		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => new tx_oelib_List())
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		self::assertTrue(
			$this->fixture->getDefaultCategoriesFromGroup()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultCategoriesFromGroupForUserWithOneGroupWithCategoryReturnsThisCategory() {
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

		self::assertEquals(
			1,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultCategoriesFromGroupForUserWithOneGroupWithTwoCategoriesReturnsTwoCategories() {
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

		self::assertEquals(
			2,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultCategoriesFromGroupForUserWithTwoGroupsOneWithCategoryReturnsOneCategory() {
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

		self::assertEquals(
			1,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithSameCategoryReturnsOneCategory() {
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

		self::assertEquals(
			1,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithCategoriesReturnsTwoCategories() {
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

		self::assertEquals(
			2,
			$this->fixture->getDefaultCategoriesFromGroup()->count()
		);
	}


	//////////////////////////////////////////
	// Tests concerning hasDefaultCategories
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasDefaultCategoriesForUserWithOneGroupWithoutCategoryReturnsFalse() {
		$userGroup = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(
			array('tx_seminars_default_categories' => new tx_oelib_List())
		);

		$list = new tx_oelib_List();
		$list->add($userGroup);
		$this->fixture->setData(array('usergroup' => $list));

		self::assertFalse(
			$this->fixture->hasDefaultCategories()
		);
	}

	/**
	 * @test
	 */
	public function hasDefaultCategoriesForUserWithOneGroupWithCategoryReturnsTrue() {
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

		self::assertTrue(
			$this->fixture->hasDefaultCategories()
		);
	}


	/////////////////////////////////////////////////////////
	// Tests concerning getRegistration and setRegistration
	/////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRegistrationReturnsRegistration() {
		$registration = new tx_seminars_Model_Registration();
		$this->fixture->setData(
			array('tx_seminars_registration' => $registration)
		);

		self::assertSame(
			$registration,
			$this->fixture->getRegistration()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationSetsRegistration() {
		$registration = new tx_seminars_Model_Registration();
		$this->fixture->setRegistration($registration);

		self::assertSame(
			$registration,
			$this->fixture->getRegistration()
		);
	}

	/**
	 * @test
	 */
	public function setRegistrationWithNullIsAllowed() {
		$this->fixture->setRegistration(NULL);

		self::assertNull(
			$this->fixture->getRegistration()
		);
	}


	//////////////////////////////////////////
	// Tests concerning getDefaultOrganizers
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function getDefaultOrganizersForGroupWithoutDefaultOrganizersReturnsEmptyList() {
		$userGroup = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(array('tx_seminars_default_organizer' => NULL));
		$groups = new tx_oelib_List();
		$groups->add($userGroup);
		$this->fixture->setData(array('usergroup' => $groups));

		self::assertTrue(
			$this->fixture->getDefaultOrganizers()->isEmpty()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultOrganizerForGroupWithDefaultOrganizerReturnsThatOrganizer() {
		$organizer = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Organizer')->getNewGhost();
		$userGroup = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup->setData(array('tx_seminars_default_organizer' => $organizer));
		$groups = new tx_oelib_List();
		$groups->add($userGroup);
		$this->fixture->setData(array('usergroup' => $groups));

		self::assertSame(
			$organizer,
			$this->fixture->getDefaultOrganizers()->first()
		);
	}

	/**
	 * @test
	 */
	public function getDefaultOrganizersForTwoGroupsWithDefaultOrganizersReturnsBothOrganizers() {
		$organizer1 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Organizer')->getNewGhost();
		$userGroup1 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup1->setData(array('tx_seminars_default_organizer' => $organizer1));

		$organizer2 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Organizer')->getNewGhost();
		$userGroup2 = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_FrontEndUserGroup')->getNewGhost();
		$userGroup2->setData(array('tx_seminars_default_organizer' => $organizer2));

		$groups = new tx_oelib_List();
		$groups->add($userGroup1);
		$groups->add($userGroup2);
		$this->fixture->setData(array('usergroup' => $groups));

		$defaultOrganizers = $this->fixture->getDefaultOrganizers();

		self::assertTrue(
			$defaultOrganizers->hasUid($organizer1->getUid()),
			'The first organizer is missing.'
		);
		self::assertTrue(
			$defaultOrganizers->hasUid($organizer2->getUid()),
			'The second organizer is missing.'
		);
	}


	//////////////////////////////////////////
	// Tests concerning hasDefaultOrganizers
	//////////////////////////////////////////

	/**
	 * @test
	 */
	public function hasDefaultOrganizersForEmptyDefaultOrganizersReturnsFalse() {
		$fixture = $this->getMock(
			'tx_seminars_Model_FrontEndUser', array('getDefaultOrganizers')
		);
		$fixture->expects(self::any())->method('getDefaultOrganizers')
			->will(self::returnValue(new tx_oelib_List()));

		self::assertFalse(
			$fixture->hasDefaultOrganizers()
		);
	}

	/**
	 * @test
	 */
	public function hasDefaultOrganizersForNonEmptyDefaultOrganizersReturnsTrue() {
		$organizer = tx_oelib_MapperRegistry
			::get('tx_seminars_Mapper_Organizer')->getNewGhost();
		$organizers = new tx_oelib_List();
		$organizers->add($organizer);

			$fixture = $this->getMock(
			'tx_seminars_Model_FrontEndUser', array('getDefaultOrganizers')
		);
		$fixture->expects(self::any())->method('getDefaultOrganizers')
			->will(self::returnValue($organizers));

		self::assertTrue(
			$fixture->hasDefaultOrganizers()
		);
	}
}