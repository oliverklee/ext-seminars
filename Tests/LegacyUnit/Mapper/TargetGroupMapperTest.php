<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\TargetGroupMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\TargetGroup;
use PHPUnit\Framework\TestCase;

final class TargetGroupMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var TargetGroupMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new TargetGroupMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsTargetGroupInstance(): void
    {
        self::assertInstanceOf(
            TargetGroup::class,
            $this->subject->find(1)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['title' => 'Housewives']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'Housewives',
            $model->getTitle()
        );
    }

    // Tests regarding the owner.

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance(): void
    {
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            ['owner' => $frontEndUser->getUid()]
        );

        self::assertInstanceOf(FrontEndUser::class, $testingModel->getOwner());
    }
}
