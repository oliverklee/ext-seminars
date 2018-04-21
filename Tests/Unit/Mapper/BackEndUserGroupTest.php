<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_BackEndUserGroupTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework for creating dummy records
     */
    private $testingFramework;
    /**
     * @var Tx_Seminars_Mapper_BackEndUserGroup the object to test
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

        $this->fixture = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_BackEndUserGroup::class);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /////////////////////////////////////////
    // Tests concerning the basic functions
    /////////////////////////////////////////

    /**
     * @test
     */
    public function findReturnsBackEndUserGroupInstance()
    {
        $uid = $this->fixture->getNewGhost()->getUid();

        self::assertInstanceOf(Tx_Seminars_Model_BackEndUserGroup::class, $this->fixture->find($uid));
    }

    /**
     * @test
     */
    public function loadForExistingUserGroupCanLoadUserGroupData()
    {
        /** @var Tx_Seminars_Model_BackEndUserGroup $userGroup */
        $userGroup = $this->fixture->find(
            $this->testingFramework->createBackEndUserGroup(
                ['title' => 'foo']
            )
        );

        $this->fixture->load($userGroup);

        self::assertEquals(
            'foo',
            $userGroup->getTitle()
        );
    }
}
