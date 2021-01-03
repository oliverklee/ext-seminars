<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndUserTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_FrontEndUser the object to test
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_FrontEndUser();
        $this->testingFramework = new TestingFramework('tx_seminars');
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
        $userGroup = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)
            ->getLoadedTestingModel(
                [
                    'tx_seminars_publish_events'
                    => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
                ]
            );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingOneReturnsHideNew()
    {
        $userGroup = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)
            ->getLoadedTestingModel(
                [
                    'tx_seminars_publish_events'
                    => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
                ]
            );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithOneGroupAndGroupPublishSettingTwoReturnsHideEdited()
    {
        $userGroup = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)
            ->getLoadedTestingModel(
                [
                    'tx_seminars_publish_events'
                    => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
                ]
            );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithoutGroupReturnsPublishAll()
    {
        $list = new Collection();
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingZeroAndOneReturnsHideNew()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            ]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            ]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingOneAndTwoReturnsHideEdited()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            ]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            ]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndGroupPublishSettingTwoAndZeroReturnsHideEdited()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            ]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            ]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingsForUserWithTwoGroupsAndBothGroupPublishSettingsOneReturnsHideNew()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class);
        $userGroup = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            ]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            [
                'tx_seminars_publish_events'
                => \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            ]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->subject->getPublishSetting()
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
        $list = new Collection();
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->subject->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithUserGroupWithoutPidReturnsZero()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->subject->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithUserGroupWithPidReturnsPid()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->subject->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->subject->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnPidOfFirstUserGroup()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 24]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_auxiliary_records_pid' => 42]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            24,
            $this->subject->getAuxiliaryRecordsPid()
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
        $list = new Collection();
        $this->subject->setData(['usergroup' => $list]);

        self::assertNull(
            $this->subject->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithGroupWithNoReviewerReturnsNull()
    {
        $userGroup = new \Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup->setData(['tx_seminars_reviewer' => null]);

        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertNull(
            $this->subject->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithGroupWithReviewerReturnsReviewer()
    {
        $backEndUser = new BackEndUser();

        $userGroup = new \Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup->setData(['tx_seminars_reviewer' => $backEndUser]);

        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertSame(
            $backEndUser,
            $this->subject->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithTwoGroupsOneWithReviewerOneWithoutReviewerReturnsReviewer()
    {
        $backEndUser = new BackEndUser();

        $userGroup1 = new \Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup2 = new \Tx_Seminars_Model_FrontEndUserGroup();

        $userGroup1->setData(['tx_seminars_reviewer' => null]);
        $userGroup2->setData(['tx_seminars_reviewer' => $backEndUser]);

        $list = new Collection();
        $list->add($userGroup1);
        $list->add($userGroup2);

        $this->subject->setData(['usergroup' => $list]);

        self::assertSame(
            $backEndUser,
            $this->subject->getReviewerFromGroup()
        );
    }

    /**
     * @test
     */
    public function getReviewerFromGroupForUserWithTwoGroupsWithReviewersReturnsReviewerOfFirstGroup()
    {
        $backEndUser1 = new BackEndUser();
        $backEndUser2 = new BackEndUser();

        $userGroup1 = new \Tx_Seminars_Model_FrontEndUserGroup();
        $userGroup2 = new \Tx_Seminars_Model_FrontEndUserGroup();

        $userGroup1->setData(['tx_seminars_reviewer' => $backEndUser1]);
        $userGroup2->setData(['tx_seminars_reviewer' => $backEndUser2]);

        $list = new Collection();
        $list->add($userGroup1);
        $list->add($userGroup2);

        $this->subject->setData(['usergroup' => $list]);

        self::assertSame(
            $backEndUser1,
            $this->subject->getReviewerFromGroup()
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
        $list = new Collection();
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->subject->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordsPidWithUserGroupWithoutPidReturnsZero()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            0,
            $this->subject->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordsPidWithUserGroupWithPidReturnsPid()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 42]
        );

        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->subject->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel([]);

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 42]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            42,
            $this->subject->getEventRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnsPidOfFirstUserGroup()
    {
        $groupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 24]
        );

        $userGroup2 = $groupMapper->getLoadedTestingModel(
            ['tx_seminars_events_pid' => 42]
        );

        $list = new Collection();
        $list->add($userGroup);
        $list->add($userGroup2);

        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            24,
            $this->subject->getEventRecordsPid()
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
        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => new Collection()]
        );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertTrue(
            $this->subject->getDefaultCategoriesFromGroup()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithOneGroupWithCategoryReturnsThisCategory()
    {
        $categories = new Collection();
        $categories->add(
            \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
                ->getNewGhost()
        );

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            1,
            $this->subject->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithOneGroupWithTwoCategoriesReturnsTwoCategories()
    {
        $categoryMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Category::class
        );
        $categories = new Collection();
        $categories->add($categoryMapper->getNewGhost());
        $categories->add($categoryMapper->getNewGhost());

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            2,
            $this->subject->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsOneWithCategoryReturnsOneCategory()
    {
        $frontEndGroupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(
            ['tx_seminars_default_categories' => new Collection()]
        );

        $categories = new Collection();
        $categories->add(
            \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
                ->getNewGhost()
        );

        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Collection();
        $list->add($userGroup1);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            1,
            $this->subject->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithSameCategoryReturnsOneCategory()
    {
        $categoryGhost = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Category::class
        )->getNewGhost();
        $categories = new Collection();
        $categories->add($categoryGhost);

        $frontEndGroupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Collection();
        $list->add($userGroup1);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            1,
            $this->subject->getDefaultCategoriesFromGroup()->count()
        );
    }

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithCategoriesReturnsTwoCategories()
    {
        $categoryMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_Category::class
        );
        $frontEndGroupMapper = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        );

        $categoryGhost1 = $categoryMapper->getNewGhost();
        $categories1 = new Collection();
        $categories1->add($categoryGhost1);
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(
            ['tx_seminars_default_categories' => $categories1]
        );

        $categoryGhost2 = $categoryMapper->getNewGhost();
        $categories2 = new Collection();
        $categories2->add($categoryGhost2);
        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(
            ['tx_seminars_default_categories' => $categories2]
        );

        $list = new Collection();
        $list->add($userGroup1);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            2,
            $this->subject->getDefaultCategoriesFromGroup()->count()
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
        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => new Collection()]
        );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertFalse(
            $this->subject->hasDefaultCategories()
        );
    }

    /**
     * @test
     */
    public function hasDefaultCategoriesForUserWithOneGroupWithCategoryReturnsTrue()
    {
        $categories = new Collection();
        $categories->add(
            \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)
                ->getNewGhost()
        );

        $userGroup = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUserGroup::class
        )->getNewGhost();
        $userGroup->setData(
            ['tx_seminars_default_categories' => $categories]
        );

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertTrue(
            $this->subject->hasDefaultCategories()
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
        $registration = new \Tx_Seminars_Model_Registration();
        $this->subject->setData(
            ['tx_seminars_registration' => $registration]
        );

        self::assertSame(
            $registration,
            $this->subject->getRegistration()
        );
    }

    /**
     * @test
     */
    public function setRegistrationSetsRegistration()
    {
        $registration = new \Tx_Seminars_Model_Registration();
        $this->subject->setRegistration($registration);

        self::assertSame(
            $registration,
            $this->subject->getRegistration()
        );
    }

    /**
     * @test
     */
    public function setRegistrationWithNullIsAllowed()
    {
        $this->subject->setRegistration();

        self::assertNull(
            $this->subject->getRegistration()
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
        $userGroup = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_organizer' => null]);
        $groups = new Collection();
        $groups->add($userGroup);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->subject->getDefaultOrganizers()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getDefaultOrganizerForGroupWithDefaultOrganizerReturnsThatOrganizer()
    {
        $organizer = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $userGroup = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_organizer' => $organizer]);
        $groups = new Collection();
        $groups->add($userGroup);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            $organizer,
            $this->subject->getDefaultOrganizers()->first()
        );
    }

    /**
     * @test
     */
    public function getDefaultOrganizersForTwoGroupsWithDefaultOrganizersReturnsBothOrganizers()
    {
        $organizer1 = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $userGroup1 = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup1->setData(['tx_seminars_default_organizer' => $organizer1]);

        $organizer2 = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $userGroup2 = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_FrontEndUserGroup::class)->getNewGhost();
        $userGroup2->setData(['tx_seminars_default_organizer' => $organizer2]);

        $groups = new Collection();
        $groups->add($userGroup1);
        $groups->add($userGroup2);
        $this->subject->setData(['usergroup' => $groups]);

        $defaultOrganizers = $this->subject->getDefaultOrganizers();

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
        /** @var \Tx_Seminars_Model_FrontEndUser|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Model_FrontEndUser::class,
            ['getDefaultOrganizers']
        );
        $subject->method('getDefaultOrganizers')
            ->willReturn(new Collection());

        self::assertFalse(
            $subject->hasDefaultOrganizers()
        );
    }

    /**
     * @test
     */
    public function hasDefaultOrganizersForNonEmptyDefaultOrganizersReturnsTrue()
    {
        $organizer = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $organizers = new Collection();
        $organizers->add($organizer);

        /** @var \Tx_Seminars_Model_FrontEndUser|MockObject $subject */
        $subject = $this->createPartialMock(
            \Tx_Seminars_Model_FrontEndUser::class,
            ['getDefaultOrganizers']
        );
        $subject->method('getDefaultOrganizers')
            ->willReturn($organizers);

        self::assertTrue(
            $subject->hasDefaultOrganizers()
        );
    }
}
