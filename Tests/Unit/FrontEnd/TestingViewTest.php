<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_TestingViewTest extends Tx_Phpunit_TestCase
{
    /**
     * the fixture to test
     *
     * @var Tx_Seminars_Tests_Unit_Fixtures_FrontEnd_TestingView
     */
    private $fixture;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();
        $this->fixture = new Tx_Seminars_Tests_Unit_Fixtures_FrontEnd_TestingView(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            $GLOBALS['TSFE']->cObj
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    public function testRenderCanReturnAViewsContent()
    {
        self::assertEquals(
            'Hi, I am the testingFrontEndView!',
            $this->fixture->render()
        );
    }
}
