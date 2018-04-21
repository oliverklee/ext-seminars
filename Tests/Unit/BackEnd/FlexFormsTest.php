<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_FlexFormsTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_FlexForms
     */
    private $fixture;
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->fixture = new Tx_Seminars_FlexForms();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function classCanBeInstantiated()
    {
        self::assertInstanceOf(Tx_Seminars_FlexForms::class, $this->fixture);
    }
}
