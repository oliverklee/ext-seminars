<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\BagBuilder\SpeakerBagBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\BagBuilder\SpeakerBagBuilder
 */
final class SpeakerBagBuilderTest extends TestCase
{
    /**
     * @var SpeakerBagBuilder
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new SpeakerBagBuilder();
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
