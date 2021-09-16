<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\PhpUnit\TestCase;

/**
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndUserGroupTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_FrontEndUserGroup the object to test
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_FrontEndUserGroup();
    }

    ///////////////////////////////////////
    // Tests concerning getPublishSetting
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getPublishSettingWithoutPublishSettingReturnsPublishAll()
    {
        $this->subject->setData([]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingWithPublishSettingSetToZeroReturnsPublishAll()
    {
        $this->subject->setData(['tx_seminars_publish_events' => 0]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingWithPublishSettingSetToOneReturnsHideNew()
    {
        $this->subject->setData(['tx_seminars_publish_events' => 1]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_NEW,
            $this->subject->getPublishSetting()
        );
    }

    /**
     * @test
     */
    public function getPublishSettingWithPublishSettingSetToTwoReturnsHideEdited()
    {
        $this->subject->setData(['tx_seminars_publish_events' => 2]);

        self::assertEquals(
            \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED,
            $this->subject->getPublishSetting()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning getAuxiliaryRecordsPid().
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getAuxiliaryRecordsPidWithoutPidReturnsZero()
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
    public function getAuxiliaryRecordsPidWithPidReturnsPid()
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
    public function hasAuxiliaryRecordsPidWithoutPidReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasAuxiliaryRecordsPid()
        );
    }

    /**
     * @test
     */
    public function hasAuxiliaryRecordsPidWithPidReturnsTrue()
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
    public function hasReviewerForGroupWithoutReviewerReturnsFalse()
    {
        $this->subject->setData(['tx_seminars_reviewer' => null]);

        self::assertFalse(
            $this->subject->hasReviewer()
        );
    }

    /**
     * @test
     */
    public function hasReviewerForGroupWithReviewerReturnsTrue()
    {
        $backEndUser = new BackEndUser();

        $this->subject->setData(['tx_seminars_reviewer' => $backEndUser]);

        self::assertTrue(
            $this->subject->hasReviewer()
        );
    }

    /**
     * @test
     */
    public function getReviewerForGroupWithoutReviewerReturnsNull()
    {
        $this->subject->setData(['tx_seminars_reviewer' => null]);

        self::assertNull(
            $this->subject->getReviewer()
        );
    }

    /**
     * @test
     */
    public function getReviewerForGroupWithReviewerReturnsReviewer()
    {
        $backEndUser = new BackEndUser();

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
    public function hasEventRecordPidForNoPidSetReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function hasEventRecordPidForPidSetReturnsTrue()
    {
        $this->subject->setData(['tx_seminars_events_pid' => 42]);

        self::assertTrue(
            $this->subject->hasEventRecordPid()
        );
    }

    /**
     * @test
     */
    public function getEventRecordPidForNoPidSetReturnsZero()
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
    public function getEventRecordPidForPidSetReturnsThisPid()
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
    public function getDefaultCategoriesForNoCategoriesReturnsAList()
    {
        $this->subject->setData(['tx_seminars_default_categories' => new Collection()]);

        self::assertInstanceOf(Collection::class, $this->subject->getDefaultCategories());
    }

    /**
     * @test
     */
    public function getDefaultCategoriesForOneAssignedCategoryReturnsThisCategoryInList()
    {
        $list = new Collection();
        $category = MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)->getNewGhost();

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
    public function hasDefaultCategoriesForNoAssignedCategoriesReturnsFalse()
    {
        $this->subject->setData(['tx_seminars_default_categories' => new Collection()]);

        self::assertFalse(
            $this->subject->hasDefaultCategories()
        );
    }

    /**
     * @test
     */
    public function hasDefaultCategoriesForOneAssignedCategoryReturnsTrue()
    {
        $list = new Collection();
        $list->add(MapperRegistry::get(\Tx_Seminars_Mapper_Category::class)->getNewGhost());

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
    public function getDefaultOrganizerForSetOrganizerReturnsIt()
    {
        $organizer = MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $this->subject->setData(['tx_seminars_default_organizer' => $organizer]);

        self::assertSame(
            $organizer,
            $this->subject->getDefaultOrganizer()
        );
    }

    /**
     * @test
     */
    public function hasDefaultOrganizerForSetOrganizerReturnsTrue()
    {
        $organizer = MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class)->getNewGhost();
        $this->subject->setData(['tx_seminars_default_organizer' => $organizer]);

        self::assertTrue(
            $this->subject->hasDefaultOrganizer()
        );
    }

    /**
     * @test
     */
    public function hasDefaultOrganizerForNotSetOrganizerReturnsFalse()
    {
        $this->subject->setData(['tx_seminars_default_organizer' => null]);

        self::assertFalse(
            $this->subject->hasDefaultOrganizer()
        );
    }
}
