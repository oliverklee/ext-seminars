<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_LodgingTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Seminars_Mapper_Lodging
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = new \Tx_Seminars_Mapper_Lodging();
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
    public function findWithUidReturnsLodgingInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_Lodging::class, $this->fixture->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['title' => 'Shack']
        );

        /** @var \Tx_Seminars_Model_Lodging $model */
        $model = $this->fixture->find($uid);
        self::assertEquals(
            'Shack',
            $model->getTitle()
        );
    }
}
