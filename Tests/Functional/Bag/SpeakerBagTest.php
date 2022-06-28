<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Bag;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Bag\SpeakerBag;
use OliverKlee\Seminars\Tests\Functional\Traits\BagHelper;

/**
 * @covers \OliverKlee\Seminars\Bag\SpeakerBag
 */
final class SpeakerBagTest extends FunctionalTestCase
{
    use BagHelper;

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @test
     */
    public function canHaveAtLeastOneElement(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new SpeakerBag();

        self::assertGreaterThan(0, $bag->count());
    }

    /**
     * @test
     */
    public function containsVisibleSpeakers(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new SpeakerBag();

        self::assertBagHasUid($bag, 1);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresHiddenSpeakers(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new SpeakerBag();

        self::assertBagNotHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function withShowHiddenRecordsSetToMinusOneIgnoresHiddenSpeakers(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new SpeakerBag('1=1', '', '', '', '', -1);

        self::assertBagNotHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function withShowHiddenRecordsSetToOneFindsHiddenSpeakers(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new SpeakerBag('1=1', '', '', '', '', 1);

        self::assertBagHasUid($bag, 2);
    }
}
