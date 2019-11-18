<?php

declare(strict_types=1);

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_BagBuilder_SpeakerTest extends TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Speaker
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_BagBuilder_Speaker();
        $this->subject->setTestMode();
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
        self::assertInstanceOf(\Tx_Seminars_Bag_Abstract::class, $this->subject->build());
    }
}
