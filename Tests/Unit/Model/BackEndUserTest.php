<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_BackEndUserTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_BackEndUser
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new Tx_Seminars_Model_BackEndUser();
    }

    /////////////////////////////////////////////
    // Tests concerning getEventFolderFromGroup
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getEventFolderFromGroupForNoGroupsReturnsZero()
    {
        $this->fixture->setData(['usergroup' => new Tx_Oelib_List()]);

        self::assertEquals(
            0,
            $this->fixture->getEventFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getEventFolderFromGroupForOneGroupWithoutEventPidReturnsZero()
    {
        $group = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new Tx_Oelib_List();
        $groups->add($group);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertEquals(
            0,
            $this->fixture->getEventFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getEventFolderFromGroupForOneGroupWithEventPidReturnsThisPid()
    {
        $group = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_events_folder' => 42]
        );
        $groups = new Tx_Oelib_List();
        $groups->add($group);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertEquals(
            42,
            $this->fixture->getEventFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getEventFolderFromGroupForTwoGroupsBothWithDifferentEventPidsReturnsOnlyOneOfThePids()
    {
        $group1 = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_events_folder' => 23]
        );
        $group2 = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_events_folder' => 42]
        );
        $groups = new Tx_Oelib_List();
        $groups->add($group1);
        $groups->add($group2);
        $this->fixture->setData(['usergroup' => $groups]);
        $eventFolder = $this->fixture->getEventFolderFromGroup();

        self::assertTrue(
            ($eventFolder == 23) || ($eventFolder == 42)
        );
    }

    ////////////////////////////////////////////////////
    // Tests concerning getRegistrationFolderFromGroup
    ////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForNoGroupsReturnsZero()
    {
        $this->fixture->setData(['usergroup' => new Tx_Oelib_List()]);

        self::assertEquals(
            0,
            $this->fixture->getRegistrationFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForOneGroupWithoutRegistrationPidReturnsZero()
    {
        $group = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new Tx_Oelib_List();
        $groups->add($group);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertEquals(
            0,
            $this->fixture->getRegistrationFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForOneGroupWithRegistrationPidReturnsThisPid()
    {
        $group = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_registrations_folder' => 42]
        );
        $groups = new Tx_Oelib_List();
        $groups->add($group);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertEquals(
            42,
            $this->fixture->getRegistrationFolderFromGroup()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderFromGroupForTwoGroupsBothWithDifferentRegistrationPidsReturnsOnlyOneOfThePids()
    {
        $group1 = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_registrations_folder' => 23]
        );
        $group2 = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_registrations_folder' => 42]
        );
        $groups = new Tx_Oelib_List();
        $groups->add($group1);
        $groups->add($group2);
        $this->fixture->setData(['usergroup' => $groups]);
        $eventFolder = $this->fixture->getRegistrationFolderFromGroup();

        self::assertTrue(
            ($eventFolder == 23) || ($eventFolder == 42)
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning getAuxiliaryRecordsFolder
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForNoGroupsReturnsZero()
    {
        $this->fixture->setData(['usergroup' => new Tx_Oelib_List()]);

        self::assertEquals(
            0,
            $this->fixture->getAuxiliaryRecordsFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForOneGroupWithoutAuxiliaryRecordPidReturnsZero()
    {
        $group = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new Tx_Oelib_List();
        $groups->add($group);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertEquals(
            0,
            $this->fixture->getAuxiliaryRecordsFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForOneGroupWithAuxiliaryRecordsPidReturnsThisPid()
    {
        $group = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_auxiliaries_folder' => 42]
        );
        $groups = new Tx_Oelib_List();
        $groups->add($group);
        $this->fixture->setData(['usergroup' => $groups]);

        self::assertEquals(
            42,
            $this->fixture->getAuxiliaryRecordsFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForTwoGroupsBothWithDifferentAuxiliaryRecordPidsReturnsOnlyOneOfThePids()
    {
        $group1 = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_auxiliaries_folder' => 23]
        );
        $group2 = Tx_Oelib_MapperRegistry::
            get(Tx_Seminars_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
                ['tx_seminars_auxiliaries_folder' => 42]
        );
        $groups = new Tx_Oelib_List();
        $groups->add($group1);
        $groups->add($group2);
        $this->fixture->setData(['usergroup' => $groups]);
        $eventFolder = $this->fixture->getAuxiliaryRecordsFolder();

        self::assertTrue(
            ($eventFolder == 23) || ($eventFolder == 42)
        );
    }
}
