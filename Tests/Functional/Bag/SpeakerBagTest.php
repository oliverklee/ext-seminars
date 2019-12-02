<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BagBuilder;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class SpeakerBagTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    private static function assertBagHasUid(AbstractBag $bag, int $uid)
    {
        self::assertTrue(self::bagHasUid($bag, $uid), 'The bag does not have this UID: ' . $uid);
    }

    private static function assertBagNotHasUid(AbstractBag $bag, int $uid)
    {
        self::assertFalse(self::bagHasUid($bag, $uid), 'The bag has this UID, but was expected not to: ' . $uid);
    }

    private static function bagHasUid(AbstractBag $bag, int $uid): bool
    {
        $found = false;

        /** @var AbstractModel $element */
        foreach ($bag as $element) {
            if ($element->getUid() === $uid) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    /**
     * @test
     */
    public function canHaveAtLeastOneElement()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new \Tx_Seminars_Bag_Speaker();

        self::assertGreaterThan(0, $bag->count());
    }

    /**
     * @test
     */
    public function containsVisibleSpeakers()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new \Tx_Seminars_Bag_Speaker();

        self::assertBagHasUid($bag, 1);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresHiddenSpeakers()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new \Tx_Seminars_Bag_Speaker();

        self::assertBagNotHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function withShowHiddenRecordsSetToMinusOneIgnoresHiddenSpeakers()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new \Tx_Seminars_Bag_Speaker('1=1', '', '', '', '', -1);

        self::assertBagNotHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function withShowHiddenRecordsSetToOneFindsHiddenSpeakers()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Speakers.xml');

        $bag = new \Tx_Seminars_Bag_Speaker('1=1', '', '', '', '', 1);

        self::assertBagHasUid($bag, 2);
    }
}
