<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Bag\AbstractBag;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CategoryBagBuilderTest extends TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Category
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

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

    public function testBuilderBuildsABag()
    {
        self::assertInstanceOf(AbstractBag::class, $this->subject->build());
    }

    public function testBuiltBagIsSortedAscendingByTitle()
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

    public function testSkippingLimitToEventResultsInAllCategories()
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

    public function testToLimitEmptyEventUidsResultsInAllCategories()
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

    public function testLimitToZeroEventUidFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('0');
    }

    public function testLimitToNegativeEventUidFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('-2');
    }

    public function testLimitToInvalidEventUidAtTheStartFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('0,1');
    }

    public function testLimitToInvalidEventUidAtTheEndFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('1,0');
    }

    public function testLimitToInvalidEventUidInTheMiddleFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$eventUids must be a comma-separated list of positive integers.'
        );
        $this->subject->limitToEvents('1,0,2');
    }

    public function testLimitToEventsCanResultInOneCategory()
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

    public function testLimitToEventsCanResultInTwoCategoriesForOneEvent()
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

    public function testLimitToEventsCanResultInTwoCategoriesForTwoEvents()
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

    public function testLimitToEventsWillExcludeUnassignedCategories()
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

    public function testLimitToEventsWillExcludeCategoriesOfOtherEvents()
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

    public function testLimitToEventsResultsInAnEmptyBagIfThereAreNoMatches()
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

    public function testSortByRelationOrderThrowsExceptionIfLimitToEventsHasNotBeenCalledBefore()
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
