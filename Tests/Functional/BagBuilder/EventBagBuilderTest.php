<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BagBuilder;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Bag\AbstractBag;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventBagBuilderTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_BagBuilder_Event
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_BagBuilder_Event();
    }

    /**
     * @param int $uid
     * @param AbstractBag $bag
     *
     * @return void
     */
    private static function assertBagContainsUid(int $uid, AbstractBag $bag)
    {
        $uids = GeneralUtility::intExplode(',', $bag->getUids(), true);
        self::assertContains($uid, $uids);
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithVacanciesAndOnlyOfflineAttendeesFindsThisEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithOneVacancyFindsThisEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertBagContainsUid(2, $bag);
    }
}
