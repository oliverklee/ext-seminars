<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_CheckboxTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var Tx_Seminars_Mapper_Checkbox
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = new Tx_Seminars_Mapper_Checkbox();
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
        self::assertInstanceOf(Tx_Seminars_Model_Checkbox::class, $this->fixture->find(1));
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
        /** @var Tx_Seminars_Model_Checkbox $model */
        $model = $this->fixture->find($uid);

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
        /** @var Tx_Seminars_Model_Checkbox $model */
        $model = $this->fixture->getLoadedTestingModel([]);

        self::assertNull($model->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance()
    {
        $frontEndUser = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)
            ->getLoadedTestingModel([]);
        /** @var Tx_Seminars_Model_Checkbox $model */
        $model = $this->fixture->getLoadedTestingModel(['owner' => $frontEndUser->getUid()]);

        self::assertInstanceOf(Tx_Seminars_Model_FrontEndUser::class, $model->getOwner());
    }
}
