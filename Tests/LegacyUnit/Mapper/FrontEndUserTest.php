<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_FrontEndUserTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser the object to test
     */
    private $fixture;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUser::class
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////////////////
    // Tests for the basic functionality
    //////////////////////////////////////

    /**
     * @test
     */
    public function mapperForGhostReturnsSeminarsFrontEndUserInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $this->fixture->getNewGhost());
    }

    ///////////////////////////////////
    // Tests concerning the relations
    ///////////////////////////////////

    /**
     * @test
     */
    public function relationToRegistrationIsReadFromRegistrationMapper()
    {
        $registration = \Tx_Oelib_MapperRegistry
            ::get(\Tx_Seminars_Mapper_Registration::class)->getNewGhost();

        /** @var \Tx_Seminars_Model_FrontEndUser $model */
        $model = $this->fixture->getLoadedTestingModel(
            ['tx_seminars_registration' => $registration->getUid()]
        );

        self::assertSame(
            $registration,
            $model->getRegistration()
        );
    }
}
