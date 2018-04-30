<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_BackEndUserGroupTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Model_BackEndUserGroup
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new \Tx_Seminars_Model_BackEndUserGroup();
    }

    ////////////////////////////////
    // Tests concerning getTitle()
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleForNonEmptyGroupTitleReturnsGroupTitle()
    {
        $this->fixture->setData(['title' => 'foo']);

        self::assertEquals(
            'foo',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForEmptyGroupTitleReturnsEmptyString()
    {
        $this->fixture->setData(['title' => '']);

        self::assertEquals(
            '',
            $this->fixture->getTitle()
        );
    }

    ////////////////////////////////////
    // Tests concerning getEventFolder
    ////////////////////////////////////

    /**
     * @test
     */
    public function getEventFolderForNoSetEventFolderReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getEventFolder()
        );
    }

    /**
     * @test
     */
    public function getEventFolderForSetEventFolderReturnsEventFolderPid()
    {
        $this->fixture->setData(['tx_seminars_events_folder' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getEventFolder()
        );
    }

    ///////////////////////////////////////////
    // Tests concerning getRegistrationFolder
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function getRegistrationFolderForNoSetRegistrationFolderReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getRegistrationFolder()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderForSetRegistrationFolderReturnsRegistrationFolderPid()
    {
        $this->fixture->setData(['tx_seminars_registrations_folder' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getRegistrationFolder()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning getAuxiliaryRecordsFolder
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForNoSetAuxiliaryRecordsFolderReturnsZero()
    {
        $this->fixture->setData([]);

        self::assertEquals(
            0,
            $this->fixture->getAuxiliaryRecordFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForSetAuxiliaryRecordsFolderReturnsAuxiliaryRecordsFolderPid()
    {
        $this->fixture->setData(['tx_seminars_auxiliaries_folder' => 42]);

        self::assertEquals(
            42,
            $this->fixture->getAuxiliaryRecordFolder()
        );
    }
}
