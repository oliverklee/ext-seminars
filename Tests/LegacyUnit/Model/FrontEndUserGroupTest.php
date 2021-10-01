<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser as OelibBackEndUser;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Mapper\CategoryMapper;
use OliverKlee\Seminars\Mapper\OrganizerMapper;
use OliverKlee\Seminars\Model\FrontEndUserGroup;

final class FrontEndUserGroupTest extends TestCase
{
    /**
     * @var FrontEndUserGroup the object to test
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUserGroup();
    }

    ///////////////////////////////////////
    // Tests concerning getPublishSetting
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getPublishSettingWithoutPublishSettingReturnsPublishAll(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingWithPublishSettingSetToZeroReturnsPublishAll(): void
    {
        $this->subject->setData(['tx_seminars_publish_events' => 0]);

        self::assertEquals(
            FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingWithPublishSettingSetToOneReturnsHideNew(): void
    {
        $this->subject->setData(['tx_seminars_publish_events' => 1]);

        self::assertEquals(
            FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingWithPublishSettingSetToTwoReturnsHideEdited(): void
    {
        $this->subject->setData(['tx_seminars_publish_events' => 2]);

        self::assertEquals(
            FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->subject->getPublishSetting()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning getAuxiliaryRecordsPid().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithoutPidReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithPidReturnsPid(): void
    {
        $this->subject->setData(['tx_seminars_auxiliary_records_pid' => 42]);

        self::assertEquals(
            42,
            $this->subject->getAuxiliaryRecordsPid()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning hasAuxiliaryRecordsPid().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function hasAuxiliaryRecordsPidWithoutPidReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function hasAuxiliaryRecordsPidWithPidReturnsTrue(): void
    {
        $this->subject->setData(['tx_seminars_auxiliary_records_pid' => 42]);

        self::assertTrue(
            $this->subject->hasAuxiliaryRecordsPid()
        );
    }

    //////////////////////////////////
    // Tests concerning the reviewer
    //////////////////////////////////

    /**
     * @test
     */
    public function hasReviewerForGroupWithoutReviewerReturnsFalse(): void
    {
        $this->subject->setData(['tx_seminars_reviewer' => null]);

        self::assertFalse(
            $this->subject->hasReviewer()
        );
    }

    /**
     * @test
     */
    public function hasReviewerForGroupWithReviewerReturnsTrue(): void
    {
        $backEndUser = new OelibBackEndUser();

        $this->subject->setData(['tx_seminars_reviewer' => $backEndUser]);

        self::assertTrue(
            $this->subject->hasReviewer()
        );
    }

    /**
     * @test
     */
    public function getReviewerForGroupWithoutReviewerReturnsNull(): void
    {
        $this->subject->setData(['tx_seminars_reviewer' => null]);

        self::assertNull(
            $this->subject->getReviewer()
        );
    }

    /**
     * @test
     */
    public function getReviewerForGroupWithReviewerReturnsReviewer(): void
    {
        $backEndUser = new OelibBackEndUser();

        $this->subject->setData(['tx_seminars_reviewer' => $backEndUser]);

        self::assertSame(
            $backEndUser,
            $this->subject->getReviewer()
        );
    }

    //////////////////////////////////////////////////
    // Tests concerning the event record storage PID
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function hasEventRecordPidForNoPidSetReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function hasEventRecordPidForPidSetReturnsTrue(): void
    {
        $this->subject->setData(['tx_seminars_events_pid' => 42]);

        self::assertTrue(
            $this->subject->hasEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordPidForNoPidSetReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordPidForPidSetReturnsThisPid(): void
    {
        $this->subject->setData(['tx_seminars_events_pid' => 42]);

        self::assertEquals(
            42,
            $this->subject->getEventRecordPid()
        );
    }

    //////////////////////////////////////////
    // Tests concerning getDefaultCategories
    //////////////////////////////////////////

    /**
     * @test
     */
    public function getDefaultCategoriesForNoCategoriesReturnsAList(): void
    {
        $this->subject->setData(['tx_seminars_default_categories' => new Collection()]);

        self::assertInstanceOf(Collection::class, $this->subject->getDefaultCategories());
    }

    /**
     * @test
     */
    public function getDefaultCategoriesForOneAssignedCategoryReturnsThisCategoryInList(): void
    {
        $list = new Collection();
        $category = MapperRegistry::get(CategoryMapper::class)->getNewGhost();

        $list->add($category);
        $this->subject->setData(['tx_seminars_default_categories' => $list]);

        self::assertSame(
            $category,
            $this->subject->getDefaultCategories()->first()
        );
    }

    //////////////////////////////////////////
    // Tests concerning hasDefaultCategories
    //////////////////////////////////////////

    /**
     * @test
     */
    public function hasDefaultCategoriesForNoAssignedCategoriesReturnsFalse(): void
    {
        $this->subject->setData(['tx_seminars_default_categories' => new Collection()]);

        self::assertFalse(
            $this->subject->hasDefaultCategories()
        );
    }

    /**
     * @test
     */
    public function hasDefaultCategoriesForOneAssignedCategoryReturnsTrue(): void
    {
        $list = new Collection();
        $list->add(MapperRegistry::get(CategoryMapper::class)->getNewGhost());

        $this->subject->setData(['tx_seminars_default_categories' => $list]);

        self::assertTrue(
            $this->subject->hasDefaultCategories()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests concerning getDefaultOrganizer and hasDefaultOrganizer
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getDefaultOrganizerForSetOrganizerReturnsIt(): void
    {
        $organizer = MapperRegistry::get(OrganizerMapper::class)->getNewGhost();
        $this->subject->setData(['tx_seminars_default_organizer' => $organizer]);

        self::assertSame(
            $organizer,
            $this->subject->getDefaultOrganizer()
        );
    }

    /**
     * @test
     */
    public function hasDefaultOrganizerForSetOrganizerReturnsTrue(): void
    {
        $organizer = MapperRegistry::get(OrganizerMapper::class)->getNewGhost();
        $this->subject->setData(['tx_seminars_default_organizer' => $organizer]);

        self::assertTrue(
            $this->subject->hasDefaultOrganizer()
        );
    }

    /**
     * @test
     */
    public function hasDefaultOrganizerForNotSetOrganizerReturnsFalse(): void
    {
        $this->subject->setData(['tx_seminars_default_organizer' => null]);

        self::assertFalse(
            $this->subject->hasDefaultOrganizer()
        );
    }
}
