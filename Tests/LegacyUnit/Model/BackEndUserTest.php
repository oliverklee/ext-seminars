<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\BackEndUserGroupMapper;
use OliverKlee\Seminars\Model\BackEndUser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\BackEndUser
 */
final class BackEndUserTest extends TestCase
{
    /**
     * @var BackEndUser
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BackEndUser();
    }

    /////////////////////////////////////////////
    // Tests concerning getEventFolderFromGroup
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getEventFolderFromGroupForNoGroupsReturnsZero(): void
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
    public function getEventFolderFromGroupForOneGroupWithoutEventPidReturnsZero(): void
    {
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
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
    public function getEventFolderFromGroupForOneGroupWithEventPidReturnsThisPid(): void
    {
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel(
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
    public function getEventFolderFromGroupForTwoGroupsBothWithDifferentEventPidsReturnsOnlyOneOfThePids(): void
    {
        $group1 = MapperRegistry::get(BackEndUserGroupMapper::class)
            ->getLoadedTestingModel(['tx_seminars_events_folder' => 23]);
        $group2 = MapperRegistry::get(BackEndUserGroupMapper::class)
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
    public function getRegistrationFolderFromGroupForNoGroupsReturnsZero(): void
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
    public function getRegistrationFolderFromGroupForOneGroupWithoutRegistrationPidReturnsZero(): void
    {
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel([]);
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
    public function getRegistrationFolderFromGroupForOneGroupWithRegistrationPidReturnsThisPid(): void
    {
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel(
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
    public function getRegistrationFolderFromGroupForTwoGroupsBothWithDifferentRegistrationPidsReturnsOnlyOneOfThePids(): void
    {
        $group1 = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel(
            ['tx_seminars_registrations_folder' => 23]
        );
        $group2 = MapperRegistry::get(BackEndUserGroupMapper::class)->getLoadedTestingModel(
            ['tx_seminars_registrations_folder' => 42]
        );
        $groups = new Collection();
        $groups->add($group1);
        $groups->add($group2);
        $this->subject->setData(['usergroup' => $groups]);
        $eventFolder = $this->subject->getRegistrationFolderFromGroup();

        self::assertTrue($eventFolder === 23 || $eventFolder === 42);
    }
}
