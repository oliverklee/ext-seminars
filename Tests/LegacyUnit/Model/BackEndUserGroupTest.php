<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\BackEndUserGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\BackEndUserGroup
 */
final class BackEndUserGroupTest extends TestCase
{
    /**
     * @var BackEndUserGroup
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BackEndUserGroup();
    }

    ////////////////////////////////
    // Tests concerning getTitle()
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleForNonEmptyGroupTitleReturnsGroupTitle(): void
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
    public function getTitleForEmptyGroupTitleReturnsEmptyString(): void
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
    public function getEventFolderForNoSetEventFolderReturnsZero(): void
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
    public function getEventFolderForSetEventFolderReturnsEventFolderPid(): void
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
    public function getRegistrationFolderForNoSetRegistrationFolderReturnsZero(): void
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
    public function getRegistrationFolderForSetRegistrationFolderReturnsRegistrationFolderPid(): void
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
    public function getAuxiliaryRecordsFolderForNoSetAuxiliaryRecordsFolderReturnsZero(): void
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
    public function getAuxiliaryRecordsFolderForSetAuxiliaryRecordsFolderReturnsAuxiliaryRecordsFolderPid(): void
    {
        $this->subject->setData(['tx_seminars_auxiliaries_folder' => 42]);

        self::assertEquals(
            42,
            $this->subject->getAuxiliaryRecordFolder()
        );
    }
}
