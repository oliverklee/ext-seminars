<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Bag\AbstractBag;

final class CategoryBagBuilderTest extends TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Category
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_BagBuilder_Category();
        $this->subject->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic builder functions.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function builderBuildsABag()
    {
        self::assertInstanceOf(AbstractBag::class, $this->subject->build());
    }

    /**
     * @test
     */
    public function builtBagIsSortedAscendingByTitle()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Title 2']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Title 1']
        );

        $categoryBag = $this->subject->build();
        self::assertEquals(
            2,
            $categoryBag->count()
        );

        self::assertEquals(
            'Title 1',
            $categoryBag->current()->getTitle()
        );
        self::assertEquals(
            'Title 2',
            $categoryBag->next()->getTitle()
        );
    }

    ///////////////////////////////////////////////////////////////
    // Test for limiting the bag to categories of certain events.
    ///////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function skippingLimitToEventResultsInAllCategories()
    {
        $this->testingFramework->createRecord('tx_seminars_categories');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function toLimitEmptyEventUidsResultsInAllCategories()
    {
        $this->testingFramework->createRecord('tx_seminars_categories');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToEvents('');
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToZeroEventUidFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('0');
    }

    /**
     * @test
     */
    public function limitToNegativeEventUidFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('-2');
    }

    /**
     * @test
     */
    public function limitToInvalidEventUidAtTheStartFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('0,1');
    }

    /**
     * @test
     */
    public function limitToInvalidEventUidAtTheEndFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('1,0');
    }

    /**
     * @test
     */
    public function limitToInvalidEventUidInTheMiddleFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('1,0,2');
    }

    /**
     * @test
     */
    public function limitToEventsCanResultInOneCategory()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToEvents((string)$eventUid);
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventsCanResultInTwoCategoriesForOneEvent()
    {
        $this->testingFramework->createRecord('tx_seminars_categories');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid2
        );

        $this->subject->limitToEvents((string)$eventUid);
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventsCanResultInTwoCategoriesForTwoEvents()
    {
        $this->testingFramework->createRecord('tx_seminars_categories');

        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->subject->limitToEvents($eventUid1 . ',' . $eventUid2);
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWillExcludeUnassignedCategories()
    {
        $this->testingFramework->createRecord('tx_seminars_categories');

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToEvents((string)$eventUid);
        $bag = $this->subject->build();

        self::assertFalse(
            $bag->isEmpty()
        );
        self::assertEquals(
            $categoryUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWillExcludeCategoriesOfOtherEvents()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->subject->limitToEvents((string)$eventUid1);
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
        self::assertEquals(
            $categoryUid1,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToEventsResultsInAnEmptyBagIfThereAreNoMatches()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );

        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->subject->limitToEvents((string)$eventUid2);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    //////////////////////////////////
    // Tests for sortByRelationOrder
    //////////////////////////////////

    /**
     * @test
     */
    public function sortByRelationOrderThrowsExceptionIfLimitToEventsHasNotBeenCalledBefore()
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'The event UIDs were empty. This means limitToEvents has not been called. LimitToEvents has to be called before ' .
            'calling this function.'
        );

        $this->subject->sortByRelationOrder();
    }
}
