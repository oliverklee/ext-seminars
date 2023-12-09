<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\BagBuilder\SpeakerBagBuilder;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\BagBuilder\SpeakerBagBuilder
 */
final class SpeakerBagBuilderTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var SpeakerBagBuilder
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new SpeakerBagBuilder();
        $this->subject->setTestMode();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        parent::tearDown();
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
