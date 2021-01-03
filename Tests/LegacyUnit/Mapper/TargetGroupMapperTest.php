<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class TargetGroupMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_TargetGroup
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_TargetGroup();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithUidReturnsTargetGroupInstance()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Model_TargetGroup::class,
            $this->subject->find(1)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['title' => 'Housewives']
        );

        /** @var \Tx_Seminars_Model_TargetGroup $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            'Housewives',
            $model->getTitle()
        );
    }

    ///////////////////////////////
    // Tests regarding the owner.
    ///////////////////////////////

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull()
    {
        /** @var \Tx_Seminars_Model_TargetGroup $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance()
    {
        $frontEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)->getLoadedTestingModel([]);
        /** @var \Tx_Seminars_Model_TargetGroup $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(
            ['owner' => $frontEndUser->getUid()]
        );

        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $testingModel->getOwner());
    }
}
