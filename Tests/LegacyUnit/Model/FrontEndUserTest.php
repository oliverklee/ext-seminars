<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\CategoryMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Seminars\Mapper\OrganizerMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Registration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\FrontEndUser
 */
final class FrontEndUserTest extends TestCase
{
    /**
     * @var FrontEndUser the object to test
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUser();
        $this->testingFramework = new TestingFramework('tx_seminars');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning getAuxiliaryRecordsPid().

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithoutUserGroupReturnsZero(): void
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
    public function getAuxiliaryRecordsPidWithUserGroupWithoutPidReturnsZero(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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
    public function getAuxiliaryRecordsPidWithUserGroupWithPidReturnsPid(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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
    public function getAuxiliaryRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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
    public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnPidOfFirstUserGroup(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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

    // Tests concerning getEventRecordsPid()

    /**
     * @test
     */
    public function getEventRecordsPidWithoutUserGroupReturnsZero(): void
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
    public function getEventRecordsPidWithUserGroupWithoutPidReturnsZero(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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
    public function getEventRecordsPidWithUserGroupWithPidReturnsPid(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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
    public function getEventRecordsPidWithTwoUserGroupsAndSecondUserGroupHasPidReturnsPid(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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
    public function getAuxiliaryRecordPidWithTwoUserGroupsAndBothUserGroupsHavePidReturnsPidOfFirstUserGroup(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
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

    // Tests concerning getDefaultCategoriesFromGroup

    /**
     * @test
     */
    public function getDefaultCategoriesFromGroupForUserWithGroupWithoutCategoriesReturnsEmptyList(): void
    {
        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_categories' => new Collection()]);

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
    public function getDefaultCategoriesFromGroupForUserWithOneGroupWithCategoryReturnsThisCategory(): void
    {
        $categories = new Collection();
        $categories->add(MapperRegistry::get(CategoryMapper::class)->getNewGhost());

        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_categories' => $categories]);

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
    public function getDefaultCategoriesFromGroupForUserWithOneGroupWithTwoCategoriesReturnsTwoCategories(): void
    {
        $categoryMapper = MapperRegistry::get(CategoryMapper::class);
        $categories = new Collection();
        $categories->add($categoryMapper->getNewGhost());
        $categories->add($categoryMapper->getNewGhost());

        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_categories' => $categories]);

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
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsOneWithCategoryReturnsOneCategory(): void
    {
        $frontEndGroupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(['tx_seminars_default_categories' => new Collection()]);

        $categories = new Collection();
        $categories->add(MapperRegistry::get(CategoryMapper::class)->getNewGhost());

        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(['tx_seminars_default_categories' => $categories]);

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
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithSameCategoryReturnsOneCategory(): void
    {
        $categoryGhost = MapperRegistry::get(CategoryMapper::class)->getNewGhost();
        $categories = new Collection();
        $categories->add($categoryGhost);

        $frontEndGroupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(['tx_seminars_default_categories' => $categories]);

        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(['tx_seminars_default_categories' => $categories]);

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
    public function getDefaultCategoriesFromGroupForUserWithTwoGroupsBothWithCategoriesReturnsTwoCategories(): void
    {
        $categoryMapper = MapperRegistry::get(CategoryMapper::class);
        $frontEndGroupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);

        $categoryGhost1 = $categoryMapper->getNewGhost();
        $categories1 = new Collection();
        $categories1->add($categoryGhost1);
        $userGroup1 = $frontEndGroupMapper->getNewGhost();
        $userGroup1->setData(['tx_seminars_default_categories' => $categories1]);

        $categoryGhost2 = $categoryMapper->getNewGhost();
        $categories2 = new Collection();
        $categories2->add($categoryGhost2);
        $userGroup2 = $frontEndGroupMapper->getNewGhost();
        $userGroup2->setData(['tx_seminars_default_categories' => $categories2]);

        $list = new Collection();
        $list->add($userGroup1);
        $list->add($userGroup2);
        $this->subject->setData(['usergroup' => $list]);

        self::assertEquals(
            2,
            $this->subject->getDefaultCategoriesFromGroup()->count()
        );
    }

    // Tests concerning hasDefaultCategories

    /**
     * @test
     */
    public function hasDefaultCategoriesForUserWithOneGroupWithoutCategoryReturnsFalse(): void
    {
        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_categories' => new Collection()]);

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
    public function hasDefaultCategoriesForUserWithOneGroupWithCategoryReturnsTrue(): void
    {
        $categories = new Collection();
        $categories->add(MapperRegistry::get(CategoryMapper::class)->getNewGhost());

        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
        $userGroup->setData(['tx_seminars_default_categories' => $categories]);

        $list = new Collection();
        $list->add($userGroup);
        $this->subject->setData(['usergroup' => $list]);

        self::assertTrue(
            $this->subject->hasDefaultCategories()
        );
    }

    // Tests concerning getRegistration and setRegistration

    /**
     * @test
     */
    public function getRegistrationReturnsRegistration(): void
    {
        $registration = new Registration();
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
    public function setRegistrationSetsRegistration(): void
    {
        $registration = new Registration();
        $this->subject->setRegistration($registration);

        self::assertSame(
            $registration,
            $this->subject->getRegistration()
        );
    }

    /**
     * @test
     */
    public function setRegistrationWithNullIsAllowed(): void
    {
        $this->subject->setRegistration();

        self::assertNull(
            $this->subject->getRegistration()
        );
    }

    // Tests concerning getDefaultOrganizers

    /**
     * @test
     */
    public function getDefaultOrganizersForGroupWithoutDefaultOrganizersReturnsEmptyList(): void
    {
        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
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
    public function getDefaultOrganizerForGroupWithDefaultOrganizerReturnsThatOrganizer(): void
    {
        $organizer = MapperRegistry::get(OrganizerMapper::class)->getNewGhost();
        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
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
    public function getDefaultOrganizersForTwoGroupsWithDefaultOrganizersReturnsBothOrganizers(): void
    {
        $organizer1 = MapperRegistry::get(OrganizerMapper::class)->getNewGhost();
        $userGroup1 = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
        $userGroup1->setData(['tx_seminars_default_organizer' => $organizer1]);

        $organizer2 = MapperRegistry::get(OrganizerMapper::class)->getNewGhost();
        $userGroup2 = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
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

    // Tests concerning hasDefaultOrganizers

    /**
     * @test
     */
    public function hasDefaultOrganizersForEmptyDefaultOrganizersReturnsFalse(): void
    {
        $subject = $this->createPartialMock(
            FrontEndUser::class,
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
    public function hasDefaultOrganizersForNonEmptyDefaultOrganizersReturnsTrue(): void
    {
        $organizer = MapperRegistry::get(OrganizerMapper::class)->getNewGhost();
        $organizers = new Collection();
        $organizers->add($organizer);

        $subject = $this->createPartialMock(
            FrontEndUser::class,
            ['getDefaultOrganizers']
        );
        $subject->method('getDefaultOrganizers')->willReturn($organizers);

        self::assertTrue(
            $subject->hasDefaultOrganizers()
        );
    }
}
