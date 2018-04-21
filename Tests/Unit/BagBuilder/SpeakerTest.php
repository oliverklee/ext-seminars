<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BagBuilder_SpeakerTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_BagBuilder_Speaker
     */
    private $fixture;
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = new Tx_Seminars_BagBuilder_Speaker();
        $this->fixture->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic builder functions.
    ///////////////////////////////////////////

    public function testBuilderBuildsABag()
    {
        self::assertInstanceOf(Tx_Seminars_Bag_Abstract::class, $this->fixture->build());
    }
}
