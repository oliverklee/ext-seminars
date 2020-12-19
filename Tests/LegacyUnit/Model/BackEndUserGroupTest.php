<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class BackEndUserGroupTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_BackEndUserGroup
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_BackEndUserGroup();
    }

    ////////////////////////////////
    // Tests concerning getTitle()
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleForNonEmptyGroupTitleReturnsGroupTitle()
    {
        $this->subject->setData(['title' => 'foo']);

        self::assertEquals(
            'foo',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForEmptyGroupTitleReturnsEmptyString()
    {
        $this->subject->setData(['title' => '']);

        self::assertEquals(
            '',
            $this->subject->getTitle()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getEventFolder()
        );
    }

    /**
     * @test
     */
    public function getEventFolderForSetEventFolderReturnsEventFolderPid()
    {
        $this->subject->setData(['tx_seminars_events_folder' => 42]);

        self::assertEquals(
            42,
            $this->subject->getEventFolder()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getRegistrationFolder()
        );
    }

    /**
     * @test
     */
    public function getRegistrationFolderForSetRegistrationFolderReturnsRegistrationFolderPid()
    {
        $this->subject->setData(['tx_seminars_registrations_folder' => 42]);

        self::assertEquals(
            42,
            $this->subject->getRegistrationFolder()
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
        $this->subject->setData([]);

        self::assertEquals(
            0,
            $this->subject->getAuxiliaryRecordFolder()
        );
    }

    /**
     * @test
     */
    public function getAuxiliaryRecordsFolderForSetAuxiliaryRecordsFolderReturnsAuxiliaryRecordsFolderPid()
    {
        $this->subject->setData(['tx_seminars_auxiliaries_folder' => 42]);

        self::assertEquals(
            42,
            $this->subject->getAuxiliaryRecordFolder()
        );
    }
}
