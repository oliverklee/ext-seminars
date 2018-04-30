<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_BackEndUserTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework for creating dummy records
     */
    private $testingFramework;
    /**
     * @var \Tx_Seminars_Mapper_BackEndUser the object to test
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = new \Tx_Seminars_Mapper_BackEndUser();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    public function testFindWithUidOfExistingRecordReturnsBackEndUserInstance()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Model_BackEndUser::class,
            $this->fixture->find($this->testingFramework->createBackEndUser())
        );
    }
}
