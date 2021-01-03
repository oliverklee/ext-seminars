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
class CheckboxMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Checkbox
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_Checkbox();
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
    public function findWithUidReturnsCheckboxInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_Checkbox::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes',
            ['title' => 'I agree with the T&C.']
        );
        /** @var \Tx_Seminars_Model_Checkbox $model */
        $model = $this->subject->find($uid);

        self::assertEquals(
            'I agree with the T&C.',
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
        /** @var \Tx_Seminars_Model_Checkbox $model */
        $model = $this->subject->getLoadedTestingModel([]);

        self::assertNull($model->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance()
    {
        $frontEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)
            ->getLoadedTestingModel([]);
        /** @var \Tx_Seminars_Model_Checkbox $model */
        $model = $this->subject->getLoadedTestingModel(['owner' => $frontEndUser->getUid()]);

        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $model->getOwner());
    }
}
