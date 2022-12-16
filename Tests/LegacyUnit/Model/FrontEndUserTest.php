<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper;
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

    /**
     * @test
     */
    public function isModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
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
}
