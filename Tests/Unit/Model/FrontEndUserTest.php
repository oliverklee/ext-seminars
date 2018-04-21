<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_FrontEndUserTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_FrontEndUser the object to test
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new Tx_Seminars_Model_FrontEndUser();
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ////////////////////////////////////////
    // Tests concerning getPublishSettings
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingZeroReturnsPublishAll()
    {
        $userGroup = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUserGroup::class)
            ->getLoadedTestingModel(
                [
                'tx_seminars_publish_events'
                    => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            ]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->fixture->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingOneReturnsHideNew()
    {
        $userGroup = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUserGroup::class)
            ->getLoadedTestingModel(
                [
                'tx_seminars_publish_events'
                    => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            ]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->fixture->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingTwoReturnsHideEdited()
    {
        $userGroup = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUserGroup::class)
            ->getLoadedTestingModel(
                [
                'tx_seminars_publish_events'
                    => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            ]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->fixture->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithoutGroupReturnsPublishAll()
    {
        $list = new Tx_Oelib_List();
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->fixture->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingZeroAndOneReturnsHideNew()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
        ]);

        $userGroup2 = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
        ]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->fixture->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingOneAndTwoReturnsHideEdited()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
        ]);

        $userGroup2 = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
        ]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->fixture->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingTwoAndZeroReturnsHideEdited()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
        ]);

        $userGroup2 = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
        ]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->fixture->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndBothGroupPublishSettingsOneReturnsHideNew()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
        ]);

        $userGroup2 = $groupMapper->getLoadedTestingModel([
            'tx_seminars_publish_events'
                => Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
        ]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->fixture->getPublishSetting()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning getAuxiliaryRecordsPid().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithoutUserGroupReturnsZero()
    {
        $list = new Tx_Oelib_List();
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->fixture->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithUserGroupWithoutPidReturnsZero()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->fixture->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithUserGroupWithPidReturnsPid()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->fixture->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->fixture->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnPidOfFirstUserGroup()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 24]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->fixture->setData(['usergroup' => $list]);

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
    public function getReviewerFromGroupForUserWithoutGroupsReturnsNull()
    {
        $list = new Tx_Oelib_List();
        $this->fixture->setData(['usergroup' => $list]);

        self::assertNull(
            $this->fixture->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithGroupWithNoReviewerReturnsNull()
    {
        $userGroup = new Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup->setData(['tx_seminars_reviewer' => null]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertNull(
            $this->fixture->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithGroupWithReviewerReturnsReviewer()
    {
        $backEndUser = new Tx_Oelib_Model_BackEndUser();

        $userGroup = new Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup->setData(['tx_seminars_reviewer' => $backEndUser]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertSame(
            $backEndUser,
            $this->fixture->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithTwoGroupsOneWithReviewerOneWithoutReviewerReturnsReviewer()
    {
        $backEndUser = new Tx_Oelib_Model_BackEndUser();

        $userGroup1 = new Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup2 = new Tx_Seminars_Model_FrontEndUserGroup();

        $userGroup1->setData(['tx_seminars_reviewer' => null]);
        $userGroup2->setData(['tx_seminars_reviewer' => $backEndUser]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup1);
        $list->add($userGroup2);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertSame(
            $backEndUser,
            $this->fixture->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithTwoGroupsWithReviewersReturnsReviewerOfFirstGroup()
    {
        $backEndUser1 = new Tx_Oelib_Model_BackEndUser();
        $backEndUser2 = new Tx_Oelib_Model_BackEndUser();

        $userGroup1 = new Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup2 = new Tx_Seminars_Model_FrontEndUserGroup();

        $userGroup1->setData(['tx_seminars_reviewer' => $backEndUser1]);
        $userGroup2->setData(['tx_seminars_reviewer' => $backEndUser2]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup1);
        $list->add($userGroup2);

        $this->fixture->setData(['usergroup' => $list]);

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
    public function getEventRecordsPidWithoutUserGroupReturnsZero()
    {
        $list = new Tx_Oelib_List();
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->fixture->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordsPidWithUserGroupWithoutPidReturnsZero()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $list = new Tx_Oelib_List();
        $list->add($userGroup);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->fixture->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordsPidWithUserGroupWithPidReturnsPid()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 42]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->fixture->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 42]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->fixture->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnsPidOfFirstUserGroup()
    {
        $groupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 24]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 42]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->fixture->setData(['usergroup' => $list]);

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
    public function getDefaultCategoriesFromGroupForUserWithGroupWithoutCategoriesReturnsEmptyList()
    {
        $userGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => new Tx_Oelib_List()]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertTrue(
            $this->fixture->getDefaultCategoriesFromGroup()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithOneGroupWithCategoryReturnsThisCategory()
    {
        $categories = new Tx_Oelib_List();
        $categories->add(
            Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
                ->getNewGhost()
        );

        $userGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            1,
            $this->fixture->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithOneGroupWithTwoCategoriesReturnsTwoCategories()
    {
        $categoryMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_Category::class
        );
        $categories = new Tx_Oelib_List();
        $categories->add($categoryMapper->getNewGhost());
        $categories->add($categoryMapper->getNewGhost());

        $userGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            2,
            $this->fixture->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsOneWithCategoryReturnsOneCategory()
    {
        $frontEndGroupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(
            ['tx_seminars_default_categories' => new Tx_Oelib_List()]
        );

        $categories = new Tx_Oelib_List();
        $categories->add(
            Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
                ->getNewGhost()
        );

        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup1);
        $list->add($userGroup2);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            1,
            $this->fixture->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithSameCategoryReturnsOneCategory()
    {
        $categoryGhost = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_Category::class
        )->getNewGhost();
        $categories = new Tx_Oelib_List();
        $categories->add($categoryGhost);

        $frontEndGroupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup1);
        $list->add($userGroup2);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertEquals(
            1,
            $this->fixture->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithCategoriesReturnsTwoCategories()
    {
        $categoryMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_Category::class
        );
        $frontEndGroupMapper = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        );

        $categoryGhost1 = $categoryMapper->getNewGhost();
        $categories1 = new Tx_Oelib_List();
        $categories1->add($categoryGhost1);
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(
            ['tx_seminars_default_categories' => $categories1]
        );

        $categoryGhost2 = $categoryMapper->getNewGhost();
        $categories2 = new Tx_Oelib_List();
        $categories2->add($categoryGhost2);
        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(
            ['tx_seminars_default_categories' => $categories2]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup1);
        $list->add($userGroup2);
        $this->fixture->setData(['usergroup' => $list]);

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
    public function hasDefaultCategoriesForUserWithOneGroupWithoutCategoryReturnsFalse()
    {
        $userGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => new Tx_Oelib_List()]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

        self::assertFalse(
            $this->fixture->hasDefaultCategories()
        );
    }

    /**
     * @test
     */
    public function hasDefaultCategoriesForUserWithOneGroupWithCategoryReturnsTrue()
    {
        $categories = new Tx_Oelib_List();
        $categories->add(
            Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Category::class)
                ->getNewGhost()
        );

        $userGroup = Tx_Oelib_MapperRegistry::get(
            Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Tx_Oelib_List();
        $list->add($userGroup);
        $this->fixture->setData(['usergroup' => $list]);

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
    public function getRegistrationReturnsRegistration()
    {
        $registration = new Tx_Seminars_Model_Registration();
        $this->fixture->setData(
            ['tx_seminars_registration' => $registration]
        );

        self::assertSame(
            $registration,
            $this->fixture->getRegistration()
        );
    }

    /**
     * @test
     */
    public function setRegistrationSetsRegistration()
    {
        $registration = new Tx_Seminars_Model_Registration();
        $this->fixture->setRegistration($registration);

        self::assertSame(
            $registration,
            $this->fixture->getRegistration()
        );
    }

    /**
     * @test
     */
    public function setRegistrationWithNullIsAllowed()
    {
        $this->fixture->setRegistration(null);

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
    public function getDefaultOrganizersForGroupWithoutDefaultOrganizersReturnsEmptyList()
    {
        $userGroup = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_organizer' => null]);
        $groups = new Tx_Oelib_List();
        $groups->add($userGroup);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->fixture->getDefaultOrganizers()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getDefaultOrganizerForGroupWithDefaultOrganizerReturnsThatOrganizer()
    {
        $organizer = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $userGroup = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_organizer' => $organizer]);
        $groups = new Tx_Oelib_List();
        $groups->add($userGroup);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertSame(
            $organizer,
            $this->fixture->getDefaultOrganizers()->first()
        );
    }

    /**
     * @test
     */
    public function getDefaultOrganizersForTwoGroupsWithDefaultOrganizersReturnsBothOrganizers()
    {
        $organizer1 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $userGroup1 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup1->setData(['tx_seminars_default_organizer' => $organizer1]);

        $organizer2 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $userGroup2 = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup2->setData(['tx_seminars_default_organizer' => $organizer2]);

        $groups = new Tx_Oelib_List();
        $groups->add($userGroup1);
        $groups->add($userGroup2);
        $this->fixture->setData(['usergroup' => $groups]);

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
    public function hasDefaultOrganizersForEmptyDefaultOrganizersReturnsFalse()
    {
        $fixture = $this->getMock(
            Tx_Seminars_Model_FrontEndUser::class,
            ['getDefaultOrganizers']
        );
        $fixture->expects(self::any())->method('getDefaultOrganizers')
            ->will(self::returnValue(new Tx_Oelib_List()));

        self::assertFalse(
            $fixture->hasDefaultOrganizers()
        );
    }

    /**
     * @test
     */
    public function hasDefaultOrganizersForNonEmptyDefaultOrganizersReturnsTrue()
    {
        $organizer = Tx_Oelib_MapperRegistry
            ::get(Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $organizers = new Tx_Oelib_List();
        $organizers->add($organizer);

        $fixture = $this->getMock(
            Tx_Seminars_Model_FrontEndUser::class,
            ['getDefaultOrganizers']
        );
        $fixture->expects(self::any())->method('getDefaultOrganizers')
            ->will(self::returnValue($organizers));

        self::assertTrue(
            $fixture->hasDefaultOrganizers()
        );
    }
}
