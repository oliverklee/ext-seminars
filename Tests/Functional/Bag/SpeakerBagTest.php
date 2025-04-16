<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Bag;

use OliverKlee\Seminars\Bag\SpeakerBag;
use OliverKlee\Seminars\Tests\Functional\Traits\BagHelper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Bag\SpeakerBag
 */
final class SpeakerBagTest extends FunctionalTestCase
{
    use BagHelper;

    protected array $testExtensionsToLoad = [
        'sjbr/static-info-tables',
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

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
