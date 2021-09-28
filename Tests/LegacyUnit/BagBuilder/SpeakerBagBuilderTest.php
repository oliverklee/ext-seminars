<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Bag\AbstractBag;

final class SpeakerBagBuilderTest extends TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Speaker
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_BagBuilder_Speaker();
        $this->subject->setTestMode();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic builder functions.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function builderBuildsABag(): void
    {
        self::assertInstanceOf(AbstractBag::class, $this->subject->build());
    }
}
