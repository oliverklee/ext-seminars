<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Seminars\Tests\Functional\Traits\CollectionHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventMapperTest extends FunctionalTestCase
{
    use CollectionHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_Mapper_Event();
    }

    /**
     * @test
     */
    public function findWithUidReturnsEventInstance()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->find(1);

        self::assertInstanceOf(\Tx_Seminars_Model_Event::class, $result);
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->find(1);

        self::assertSame('a complete event', $result->getTitle());
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailIgnoresEventWithoutRegistrationsWithoutDigestDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertNotContainsModelWithUid($result, 1);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailIgnoresEventWithoutRegistrationsWithDigestDateInPast()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertNotContainsModelWithUid($result, 2);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailFindsEventWithRegistrationAndWithoutDigestDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertContainsModelWithUid($result, 3);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailSortsEventsByBeginDateInAscendingOrder()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();
        self::assertContainsModelWithUid($result, 3);
        self::assertContainsModelWithUid($result, 4);

        $uids = GeneralUtility::intExplode(',', $result->getUids(), true);
        $indexOfLaterEvent = \array_search(3, $uids, true);
        $indexOfEarlierEvent = \array_search(4, $uids, true);

        self::assertTrue($indexOfEarlierEvent < $indexOfLaterEvent);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailFindsEventWithRegistrationAfterDigestDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertContainsModelWithUid($result, 4);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailIgnoresEventWithRegistrationOnlyBeforeDigestDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertNotContainsModelWithUid($result, 5);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailFindsEventWithRegistrationsBeforeAndAfterDigestDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertContainsModelWithUid($result, 8);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailFindsDateWithRegistrationAfterDigestDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertContainsModelWithUid($result, 9);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailIgnoresTopicWithRegistrationAfterDigestDate()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertNotContainsModelWithUid($result, 10);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailIgnoresHiddenEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertNotContainsModelWithUid($result, 11);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailIgnoresDeletedEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertNotContainsModelWithUid($result, 7);
    }

    /**
     * @test
     */
    public function findForRegistrationDigestEmailIgnoresDeletedRegistration()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $result = $this->subject->findForRegistrationDigestEmail();

        self::assertNotContainsModelWithUid($result, 6);
    }

    /**
     * @test
     */
    public function getDependenciesReturnsEmptyList()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $model = $this->subject->find(1);
        $result = $model->getDependencies();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function getRequirementsReturnsEmptyList()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $model = $this->subject->find(1);
        $result = $model->getRequirements();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }
}
