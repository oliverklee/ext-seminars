<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\PhpUnit\TestCase;

/**
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class BackEndUserTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_BackEndUser
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_BackEndUser();
    }

    /////////////////////////////////////////////
    // Tests concerning getEventFolderFromGroup
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getEventFolderFromGroupForNoGroupsReturnsZero()
    {
        $this->subject->setData(['usergroup' => new Collection()]);

        self::assertEquals(
            0,
            $this->subject->getEventFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getEventFolderFromGroupForOneGroupWithoutEventPidReturnsZero()
    {
        $group = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new Collection();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertEquals(
            0,
            $this->subject->getEventFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getEventFolderFromGroupForOneGroupWithEventPidReturnsThisPid()
    {
        $group = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['tx_seminars_events_folder' => 42]
        );
        $groups = new Collection();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertEquals(
            42,
            $this->subject->getEventFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getEventFolderFromGroupForTwoGroupsBothWithDifferentEventPidsReturnsOnlyOneOfThePids()
    {
        $group1 = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)
            ->getLoadedTestingModel(['tx_seminars_events_folder' => 23]);
        $group2 = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)
            ->getLoadedTestingModel(['tx_seminars_events_folder' => 42]);
        $groups = new Collection();
        $groups->add($group1);
        $groups->add($group2);
        $this->subject->setData(['usergroup' => $groups]);
        $eventFolder = $this->subject->getEventFolderFromGroup();

        self::assertTrue($eventFolder === 23 || $eventFolder === 42);
    }

    // Tests concerning getRegistrationFolderFromGroup

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForNoGroupsReturnsZero()
    {
        $this->subject->setData(['usergroup' => new Collection()]);

        self::assertEquals(
            0,
            $this->subject->getRegistrationFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForOneGroupWithoutRegistrationPidReturnsZero()
    {
        $group = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new Collection();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertEquals(
            0,
            $this->subject->getRegistrationFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForOneGroupWithRegistrationPidReturnsThisPid()
    {
        $group = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['tx_seminars_registrations_folder' => 42]
        );
        $groups = new Collection();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertEquals(
            42,
            $this->subject->getRegistrationFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForTwoGroupsBothWithDifferentRegistrationPidsReturnsOnlyOneOfThePids()
    {
        $group1 = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['tx_seminars_registrations_folder' => 23]
        );
        $group2 = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['tx_seminars_registrations_folder' => 42]
        );
        $groups = new Collection();
        $groups->add($group1);
        $groups->add($group2);
        $this->subject->setData(['usergroup' => $groups]);
        $eventFolder = $this->subject->getRegistrationFolderFromGroup();

        self::assertTrue($eventFolder === 23 || $eventFolder === 42);
    }

    // Tests concerning getAuxiliaryRecordsFolder

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForNoGroupsReturnsZero()
    {
        $this->subject->setData(['usergroup' => new Collection()]);

        self::assertEquals(
            0,
            $this->subject->getAuxiliaryRecordsFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForOneGroupWithoutAuxiliaryRecordPidReturnsZero()
    {
        $group = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new Collection();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertEquals(
            0,
            $this->subject->getAuxiliaryRecordsFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForOneGroupWithAuxiliaryRecordsPidReturnsThisPid()
    {
        $group = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['tx_seminars_auxiliaries_folder' => 42]
        );
        $groups = new Collection();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertEquals(
            42,
            $this->subject->getAuxiliaryRecordsFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForTwoGroupsBothWithDifferentAuxiliaryRecordPidsReturnsOnlyOneOfThePids()
    {
        $group1 = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['tx_seminars_auxiliaries_folder' => 23]
        );
        $group2 = MapperRegistry::get(\Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['tx_seminars_auxiliaries_folder' => 42]
        );
        $groups = new Collection();
        $groups->add($group1);
        $groups->add($group2);
        $this->subject->setData(['usergroup' => $groups]);
        $eventFolder = $this->subject->getAuxiliaryRecordsFolder();

        self::assertTrue($eventFolder === 23 || $eventFolder === 42);
    }
}
