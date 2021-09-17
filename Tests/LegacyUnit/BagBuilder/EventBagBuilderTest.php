<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Bag\AbstractBag;

final class EventBagBuilderTest extends TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Event
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int a UNIX timestamp in the past.
     */
    private $past = 0;

    /**
     * @var int a UNIX timestamp in the future.
     */
    private $future = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        $this->future = $GLOBALS['SIM_EXEC_TIME'] + 50;
        $this->past = $GLOBALS['SIM_EXEC_TIME'] - 50;

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_BagBuilder_Event();
        $this->subject->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    // Tests for the basic builder functions.

    /**
     * @test
     */
    public function builderBuildsABag()
    {
        $bag = $this->subject->build();

        self::assertInstanceOf(AbstractBag::class, $bag);
    }

    /**
     * @test
     */
    public function builderFindsHiddenEventsInBackEndMode()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1]
        );

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertFalse(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function builderIgnoresTimedEventsByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function builderFindsTimedEventsInBackEndMode()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000]
        );

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertFalse(
            $bag->isEmpty()
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain categories.
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function skippingLimitToCategoriesResultsInAllEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
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

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEmptyCategoryUidResultsInAllEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToCategories('');
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEmptyCategoryAfterLimitToNonEmptyCategoriesUidResultsInAllEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToCategories((string)$categoryUid);
        $this->subject->limitToCategories('');
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesCanResultInOneEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToCategories((string)$categoryUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesCanResultInTwoEvents()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );

        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid
        );

        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid
        );

        $this->subject->limitToCategories((string)$categoryUid);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesExcludesUnassignedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToCategories((string)$categoryUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesExcludesEventsOfOtherCategories()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
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
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->subject->limitToCategories((string)$categoryUid1);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid1,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesForNoMatchesResultsInEmptyBag()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );

        $this->subject->limitToCategories((string)$categoryUid2);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesCanFindTopicRecords()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );

        $this->subject->limitToCategories((string)$categoryUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesForMatchingTopicFindsDateRecordAndTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $topicUid,
            $categoryUid
        );

        $this->subject->limitToCategories((string)$categoryUid);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );

        $matchingUids = explode(',', $bag->getUids());
        self::assertContains(
            $topicUid,
            $matchingUids
        );
        self::assertContains(
            $dateUid,
            $matchingUids
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesFindsDateRecordForSingle()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $topicUid,
            $categoryUid
        );

        $this->subject->limitToCategories((string)$categoryUid);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesIgnoresTopicOfDateRecord()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $topicUid,
            $categoryUid1
        );

        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $dateUid,
            $categoryUid2
        );

        $this->subject->limitToCategories((string)$categoryUid2);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCategoriesCanFindEventsFromMultipleCategories()
    {
        $categoryUid1 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid1,
            $categoryUid1
        );

        $categoryUid2 = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid2,
            $categoryUid2
        );

        $this->subject->limitToCategories($categoryUid1 . ',' . $categoryUid2);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain places
    ///////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToPlacesFindsEventsInOnePlace()
    {
        $siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToPlaces([$siteUid]);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToPlacesIgnoresEventsWithoutPlace()
    {
        $siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->subject->limitToPlaces([$siteUid]);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToPlacesFindsEventsInTwoPlaces()
    {
        $siteUid1 = $this->testingFramework->createRecord('tx_seminars_sites');
        $siteUid2 = $this->testingFramework->createRecord('tx_seminars_sites');
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid2,
            $siteUid2
        );
        $this->subject->limitToPlaces([$siteUid1, $siteUid2]);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToPlacesWithEmptyPlacesArrayFindsAllEvents()
    {
        $siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->subject->limitToPlaces([$siteUid]);
        $this->subject->limitToPlaces();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToPlacesIgnoresEventsWithDifferentPlace()
    {
        $siteUid1 = $this->testingFramework->createRecord('tx_seminars_sites');
        $siteUid2 = $this->testingFramework->createRecord('tx_seminars_sites');
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid1
        );
        $this->subject->limitToPlaces([$siteUid2]);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToPlacesWithOnePlaceFindsEventInTwoPlaces()
    {
        $siteUid1 = $this->testingFramework->createRecord('tx_seminars_sites');
        $siteUid2 = $this->testingFramework->createRecord('tx_seminars_sites');
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid2
        );
        $this->subject->limitToPlaces([$siteUid1]);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    //////////////////////////////////////
    // Tests concerning canceled events.
    //////////////////////////////////////

    /**
     * @test
     */
    public function builderFindsCanceledEventsByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => 1]
        );
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderIgnoresCanceledEventsWithHideCanceledEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => 1]
        );

        $this->subject->ignoreCanceledEvents();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function builderFindsConfirmedEventsWithHideCanceledEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED]
        );

        $this->subject->ignoreCanceledEvents();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderFindsCanceledEventsWithHideCanceledEventsDisabled()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => 1]
        );

        $this->subject->allowCanceledEvents();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderFindsCanceledEventsWithHideCanceledEventsEnabledThenDisabled()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => 1]
        );

        $this->subject->ignoreCanceledEvents();
        $this->subject->allowCanceledEvents();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderIgnoresCanceledEventsWithHideCanceledDisabledThenEnabled()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => 1]
        );

        $this->subject->allowCanceledEvents();
        $this->subject->ignoreCanceledEvents();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * validity checks
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameFailsWithEmptyKey()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The time-frame key  is not valid.'
        );
        $this->subject->setTimeFrame('');
    }

    /**
     * @test
     */
    public function setTimeFrameFailsWithInvalidKey()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The time-frame key foo is not valid.'
        );
        $this->subject->setTimeFrame('foo');
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * past events
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFramePastFindsPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('past');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastFindsOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('past');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastIgnoresCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('past');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastIgnoresUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('past');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastIgnoresUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('past');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastIgnoresEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('past');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * past and current events
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFramePastAndCurrentFindsPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('pastAndCurrent');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastAndCurrentFindsOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('pastAndCurrent');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastAndCurrentFindsCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('pastAndCurrent');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastAndCurrentIgnoresUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('pastAndCurrent');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastAndCurrentIgnoresUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('pastAndCurrent');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFramePastAndCurrentIgnoresEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('pastAndCurrent');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * current events
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameCurrentIgnoresPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('current');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentIgnoresOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('current');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentFindsCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('current');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentIgnoresUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('current');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentIgnoresUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('current');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentIgnoresEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('current');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * current and upcoming events
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameCurrentAndUpcomingIgnoresPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('currentAndUpcoming');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentAndUpcomingIgnoresOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('currentAndUpcoming');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentAndUpcomingFindsCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('currentAndUpcoming');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentAndUpcomingFindsUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('currentAndUpcoming');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentAndUpcomingFindsUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('currentAndUpcoming');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameCurrentAndUpcomingFindsEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('currentAndUpcoming');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * upcoming events
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameUpcomingIgnoresPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('upcoming');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingIgnoresOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('upcoming');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingIgnoresCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('upcoming');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingFindsUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('upcoming');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingFindsUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('upcoming');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingFindsEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('upcoming');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * upcoming events with begin date
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameUpcomingWithBeginDateIgnoresPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('upcomingWithBeginDate');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingWithBeginDateIgnoresOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('upcomingWithBeginDate');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingWithBeginDateIgnoresCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('upcomingWithBeginDate');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingWithBeginDateFindsUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('upcomingWithBeginDate');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingWithBeginDateFindsUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('upcomingWithBeginDate');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameUpcomingWithBeginDateNotFindsEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('upcomingWithBeginDate');
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * events for which the registration deadline is not over yet
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverIgnoresPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'deadline_registration' => 0,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverIgnoresOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
                'deadline_registration' => 0,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverIgnoresCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'deadline_registration' => 0,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverFindsUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
                'deadline_registration' => 0,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverFindsUpcomingEventsWithUpcomingDeadline()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + 2 * Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
                'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverIgnoresUpcomingEventsWithPassedDeadline()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
                'deadline_registration' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverFindsUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
                'deadline_registration' => 0,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameDeadlineNotOverFindsEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
                'deadline_registration' => 0,
            ]
        );

        $this->subject->setTimeFrame('deadlineNotOver');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * today
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameTodayFindsOpenEndedEventStartingToday()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameTodayNotFindsOpenEndedEventStartingTomorrow()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameTodayFindsEventStartingTodayEndingTomorrow()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameTodayFindsEventStartingYesterdayEndingToday()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameTodayFindsEventStartingYesterdayEndingTomorrow()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameTodayIgnoresEventStartingLastWeekEndingYesterday()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameTodayIgnoresEventStartingTomorrowEndingNextWeek()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameTodayIgnoresEventWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('today');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events in certain time-frames.
    //
    // * all events
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function setTimeFrameAllFindsPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('all');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameAllFindsOpenEndedPastEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_WEEK,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('all');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameAllFindsCurrentEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );

        $this->subject->setTimeFrame('all');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameAllFindsUpcomingEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_WEEK,
            ]
        );

        $this->subject->setTimeFrame('all');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameAllFindsUpcomingOpenEndedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('all');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function setTimeFrameAllFindsEventsWithoutDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 0,
            ]
        );

        $this->subject->setTimeFrame('all');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests for limiting the bag to events of certain event types
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function skippingLimitToEventTypesResultsInAllEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $typeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEmptyTypeUidResultsInAllEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $typeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );

        $this->subject->limitToEventTypes();
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEmptyTypeUidAfterLimitToNotEmptyTypesResultsInAllEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $typeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid]);
        $this->subject->limitToEventTypes();
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesCanResultInOneEvent()
    {
        $typeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid]);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesCanResultInTwoEvents()
    {
        $typeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid]);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesWillExcludeUnassignedEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $typeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid]);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesWillExcludeEventsOfOtherTypes()
    {
        $typeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid1,
            ]
        );

        $typeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid2,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid1]);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid1,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesResultsInAnEmptyBagIfThereAreNoMatches()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        $typeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid1,
            ]
        );

        $typeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );

        $this->subject->limitToEventTypes([$typeUid2]);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesFindsDateRecordForTopic()
    {
        $typeUid = $this->testingFramework->createRecord('tx_seminars_event_types');
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'event_type' => $typeUid,
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid]);
        $bag = $this->subject->build();

        self::assertSame(2, $bag->count());
        self::assertSame($topicUid . ',' . $dateUid, $bag->getUids());
    }

    /**
     * @test
     */
    public function limitToEventTypesFindsDateRecordForSingle()
    {
        $typeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid]);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesIgnoresTopicOfDateRecord()
    {
        $typeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid1,
            ]
        );

        $typeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'event_type' => $typeUid2,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid2]);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesCanFindEventsFromMultipleTypes()
    {
        $typeUid1 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid1,
            ]
        );

        $typeUid2 = $this->testingFramework->createRecord(
            'tx_seminars_event_types'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'event_type' => $typeUid2,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid1, $typeUid2]);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventTypesAndTopicsFindsTopicOfThisType()
    {
        $typeUid = $this->testingFramework->createRecord('tx_seminars_event_types');
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
                'event_type' => $typeUid,
            ]
        );

        $this->subject->limitToEventTypes([$typeUid]);
        $this->subject->limitToTopicRecords();

        /** @var \Tx_Seminars_Bag_Event $bag */
        $bag = $this->subject->build();

        self::assertSame(1, $bag->count());
        self::assertSame((string)$topicUid, $bag->getUids());
    }

    //////////////////////////////
    // Tests for limitToCities()
    //////////////////////////////

    /**
     * @test
     */
    public function limitToCitiesFindsEventsInOneCity()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 1']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCities(['test city 1']);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesIgnoresEventsInOtherCity()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 2']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCities(['test city 1']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesWithTwoCitiesFindsEventsEachInOneOfBothCities()
    {
        $siteUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 1']
        );
        $siteUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 2']
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid2,
            $siteUid2
        );
        $this->subject->limitToCities(['test city 1', 'test city 2']);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesWithEmptyCitiesArrayFindsEventsWithCities()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 1']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCities(['test city 2']);
        $this->subject->limitToCities();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesIgnoresEventsWithDifferentCity()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 1']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCities(['test city 2']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesIgnoresEventWithPlaceWithoutCity()
    {
        $siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCities(['test city 1']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesWithTwoCitiesFindsOneEventInBothCities()
    {
        $siteUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 1']
        );
        $siteUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 2']
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid2
        );
        $this->subject->limitToCities(['test city 1', 'test city 2']);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesWithOneCityFindsEventInTwoCities()
    {
        $siteUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 1']
        );
        $siteUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 2']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 2]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid2
        );
        $this->subject->limitToCities(['test city 1']);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCitiesWithTwoCitiesOneDifferentFindsEventInOneOfTheCities()
    {
        $siteUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 1']
        );
        $siteUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'test city 3']
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 2]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid2
        );
        $this->subject->limitToCities(['test city 2', 'test city 3']);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////
    // Tests for limitToCountries()
    /////////////////////////////////

    /**
     * @test
     */
    public function limitToCountriesFindsEventsInOneCountry()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'DE']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCountries(['DE']);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToCountriesIgnoresEventsInOtherCountry()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'DE']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCountries(['US']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCountriesFindsEventsInTwoCountries()
    {
        $siteUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'US']
        );
        $siteUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'DE']
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid2,
            $siteUid2
        );
        $this->subject->limitToCountries(['US', 'DE']);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCountriesWithEmptyCountriesArrayFindsAllEvents()
    {
        $siteUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'US']
        );
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid1,
            $siteUid1
        );
        $this->subject->limitToCountries(['DE']);
        $this->subject->limitToCountries();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCountriesIgnoresEventsWithDifferentCountry()
    {
        $siteUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'DE']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCountries(['US']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCountriesIgnoresEventsWithPlaceWithoutCountry()
    {
        $siteUid = $this->testingFramework->createRecord('tx_seminars_sites');
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid
        );
        $this->subject->limitToCountries(['DE']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToCountriesWithOneCountryFindsEventInTwoCountries()
    {
        $siteUid1 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'US']
        );
        $siteUid2 = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['country' => 'DE']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['place' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $siteUid2
        );
        $this->subject->limitToCountries(['US']);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////
    // Tests for limitToLanguages()
    /////////////////////////////////

    /**
     * @test
     */
    public function limitToLanguagesFindsEventsInOneLanguage()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => 'DE']
        );
        $this->subject->limitToLanguages(['DE']);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToLanguagesFindsEventsInTwoLanguages()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => 'EN']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => 'DE']
        );
        $this->subject->limitToLanguages(['EN', 'DE']);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToLanguagesWithEmptyLanguagesArrayFindsAllEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => 'EN']
        );
        $this->subject->limitToLanguages(['DE']);
        $this->subject->limitToLanguages();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToLanguagesIgnoresEventsWithDifferentLanguage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['language' => 'DE']
        );
        $this->subject->limitToLanguages(['EN']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToLanguagesIgnoresEventsWithoutLanguage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->subject->limitToLanguages(['EN']);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ////////////////////////////////////
    // Tests for limitToTopicRecords()
    ////////////////////////////////////

    /**
     * @test
     */
    public function limitToTopicRecordsFindsTopicEventRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->subject->limitToTopicRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToTopicRecordsIgnoresSingleEventRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->limitToTopicRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToTopicRecordsIgnoresEventDateRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]
        );
        $this->subject->limitToTopicRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    //////////////////////////////////////////
    // Tests for removeLimitToTopicRecords()
    //////////////////////////////////////////

    /**
     * @test
     */
    public function removeLimitToTopicRecordsFindsSingleEventRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->limitToTopicRecords();
        $this->subject->removeLimitToTopicRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function removeLimitToTopicRecordsFindsEventDateRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]
        );
        $this->subject->limitToTopicRecords();
        $this->subject->removeLimitToTopicRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////
    // Tests for limitToOwner()
    /////////////////////////////

    /**
     * @test
     */
    public function limitToOwnerWithNegativeFeUserUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $feUserUid must be >= 0.'
        );

        $this->subject->limitToOwner(-1);
    }

    /**
     * @test
     */
    public function limitToOwnerWithPositiveFeUserUidFindsEventsWithOwner()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['owner_feuser' => $feUserUid]
        );
        $this->subject->limitToOwner($feUserUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToOwnerWithPositiveFeUserUidIgnoresEventsWithoutOwner()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->subject->limitToOwner($feUserUid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToOwnerWithPositiveFeUserUidIgnoresEventsWithDifferentOwner()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['owner_feuser' => $feUserUid + 1]
        );
        $this->subject->limitToOwner($feUserUid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToOwnerWithZeroFeUserUidFindsEventsWithoutOwner()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->subject->limitToOwner($feUserUid);
        $this->subject->limitToOwner(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToOwnerWithZeroFeUserUidFindsEventsWithOwner()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['owner_feuser' => $feUserUid]
        );
        $this->subject->limitToOwner($feUserUid);
        $this->subject->limitToOwner(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    ////////////////////////////////////////////
    // Tests for limitToDateAndSingleRecords()
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToDateAndSingleRecordsFindsDateRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_DATE]
        );
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToDateAndSingleRecordsFindsSingleRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToDateAndSingleRecordsIgnoresTopicRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function removeLimitToDateAndSingleRecordsFindsTopicRecords()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->subject->limitToDateAndSingleRecords();
        $this->subject->removeLimitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    ////////////////////////////////////
    // Tests for limitToEventManager()
    ////////////////////////////////////

    /**
     * @test
     */
    public function limitToEventManagerWithNegativeFeUserUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $feUserUid must be >= 0.'
        );

        $this->subject->limitToEventManager(-1);
    }

    /**
     * @test
     */
    public function limitToEventManagerWithPositiveFeUserUidFindsEventsWithEventManager()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['vips' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_feusers_mm',
            $eventUid,
            $feUserUid
        );

        $this->subject->limitToEventManager($feUserUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventManagerWithPositiveFeUserUidIgnoresEventsWithoutEventManager()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->subject->limitToEventManager($feUserUid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventManagerWithZeroFeUserUidFindsEventsWithoutEventManager()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->subject->limitToEventManager($feUserUid);
        $this->subject->limitToEventManager(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////////
    // Tests for limitToEventsNextDay()
    /////////////////////////////////////

    /**
     * @test
     */
    public function limitToEventsNextDayFindsEventsNextDay()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => Time::SECONDS_PER_DAY, 'end_date' => Time::SECONDS_PER_DAY + 60 * 60]
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 2 * Time::SECONDS_PER_DAY,
                'end_date' => 60 * 60 + 2 * Time::SECONDS_PER_DAY,
            ]
        );
        $event = new \Tx_Seminars_OldModel_Event($eventUid1);
        $this->subject->limitToEventsNextDay($event);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid2,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToEventsNextDayIgnoresEarlierEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => Time::SECONDS_PER_DAY, 'end_date' => Time::SECONDS_PER_DAY + 60 * 60]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 0,
                'end_date' => 60 * 60,
            ]
        );
        $event = new \Tx_Seminars_OldModel_Event($eventUid);
        $this->subject->limitToEventsNextDay($event);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventsNextDayIgnoresEventsLaterThanOneDay()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => Time::SECONDS_PER_DAY, 'end_date' => Time::SECONDS_PER_DAY + 60 * 60]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => 3 * Time::SECONDS_PER_DAY,
                'end_date' => 60 * 60 + 3 * Time::SECONDS_PER_DAY,
            ]
        );
        $event = new \Tx_Seminars_OldModel_Event($eventUid);
        $this->subject->limitToEventsNextDay($event);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventsNextDayWithEventWithEmptyEndDateThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The event object given in the first parameter $event must ' .
            'have an end date set.'
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->subject->limitToEventsNextDay(
            new \Tx_Seminars_OldModel_Event($eventUid)
        );
    }

    //////////////////////////////////////////////
    // Tests for limitToOtherDatesForThisTopic()
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToOtherDatesForTopicFindsOtherDateForTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $dateUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateUid1);
        $this->subject->limitToOtherDatesForTopic($date);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid2,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToOtherDatesForTopicWithTopicRecordFindsAllDatesForTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $topic = new \Tx_Seminars_OldModel_Event($topicUid);
        $this->subject->limitToOtherDatesForTopic($topic);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToOtherDatesForTopicWithSingleEventRecordThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The first parameter $event must be either a date or a topic record.'
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $event = new \Tx_Seminars_OldModel_Event($eventUid);
        $this->subject->limitToOtherDatesForTopic($event);
    }

    /**
     * @test
     */
    public function limitToOtherDatesForTopicIgnoresDateForOtherTopic()
    {
        $topicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $topicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid1,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid2,
            ]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateUid1);
        $this->subject->limitToOtherDatesForTopic($date);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToOtherDatesForTopicIgnoresSingleEventRecordWithTopic()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'topic' => $topicUid,
            ]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateUid);
        $this->subject->limitToOtherDatesForTopic($date);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function removeLimitToOtherDatesForTopicRemovesLimitAndFindsAllDateAndTopicRecords()
    {
        $topicUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $topicUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid1,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid2,
            ]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateUid1);
        $this->subject->limitToOtherDatesForTopic($date);
        $this->subject->removeLimitToOtherDatesForTopic();
        $bag = $this->subject->build();

        self::assertSame(
            4,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function removeLimitToOtherDatesForTopicFindsSingleEventRecords()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'topic' => $topicUid,
            ]
        );
        $date = new \Tx_Seminars_OldModel_Event($dateUid);
        $this->subject->limitToOtherDatesForTopic($date);
        $this->subject->removeLimitToOtherDatesForTopic();
        $bag = $this->subject->build();

        self::assertSame(
            3,
            $bag->count()
        );
    }

    /////////////////////////////////////////////////////////////////
    // Tests for limitToFullTextSearch() for single event records
    /////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToFullTextSearchWithTwoCommasAsSearchWordFindsAllEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'avocado paprika event']
        );
        $this->subject->limitToFullTextSearch(',,');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchWithTwoSearchWordsSeparatedByTwoSpacesFindsEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'avocado paprika event']
        );
        $this->subject->limitToFullTextSearch('avocado  paprika');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchWithTwoCommasSeparatedByTwoSpacesFindsAllEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'avocado paprika event']
        );
        $this->subject->limitToFullTextSearch(',  ,');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchWithTooShortSearchWordFindsAllEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'avocado paprika event']
        );
        $this->subject->limitToFullTextSearch('o');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInAccreditationNumber()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'accreditation_number' => 'avocado paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInAccreditationNumber()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'accreditation_number' => 'paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInTitle()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'avocado paprika event']
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTitle()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'paprika event']
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInSubtitle()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['subtitle' => 'avocado paprika event']
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInSubtitle()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['subtitle' => 'paprika event']
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInDescription()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['description' => 'avocado paprika event']
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInDescription()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['description' => 'paprika event']
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInSpeakerTitle()
    {
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['title' => 'avocado paprika speaker']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'speakers' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $eventUid,
            $speakerUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInSpeakerTitle()
    {
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['title' => 'paprika speaker']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'speakers' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $eventUid,
            $speakerUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInPlaceTitle()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'avocado paprika place']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInPlaceTitle()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'paprika place']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInPlaceCity()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'avocado paprika city']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInPlaceCity()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'paprika city']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInEventTypeTitle()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'avocado paprika event type']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'event_type' => $eventTypeUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInEventTypeTitle()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'paprika event type']
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'event_type' => $eventTypeUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInCategoryTitle()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'avocado paprika category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'categories' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInCategoryTitle()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'paprika category']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'categories' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $eventUid,
            $categoryUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchWithTwoSearchWordsSeparatedBySpaceFindsTwoEventsWithSearchWordsInTitle()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'avocado event paprika']
        );
        $this->subject->limitToFullTextSearch('avocado paprika');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchWithTwoSearchWordsSeparatedByCommaFindsTwoEventsWithSearchWordsInTitle()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'avocado event paprika']
        );
        $this->subject->limitToFullTextSearch('avocado,paprika');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInTargetGroupTitle()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['title' => 'avocado paprika place']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'target_groups' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_target_groups_mm',
            $eventUid,
            $targetGroupUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTargetGroupTitle()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['title' => 'paprika place']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'target_groups' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_target_groups_mm',
            $eventUid,
            $targetGroupUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ////////////////////////////////////////////////////////////////
    // Tests for limitToFullTextSearch() for topic event records
    ////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInTopicTitle()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'avocado paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTopicTitle()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'title' => 'paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInTopicSubtitle()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'subtitle' => 'avocado paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTopicSubtitle()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'subtitle' => 'paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInTopicDescription()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'description' => 'avocado paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTopicDescription()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'description' => 'paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInTopicCategoryTitle()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'avocado paprika category']
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'categories' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $topicUid,
            $categoryUid
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTopicCategoryTitle()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'paprika category']
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'categories' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_categories_mm',
            $topicUid,
            $categoryUid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventWithSearchWordInTopicEventTypeTitle()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'avocado paprika event type']
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'event_type' => $eventTypeUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventWithoutSearchWordInTopicEventTypeTitle()
    {
        $eventTypeUid = $this->testingFramework->createRecord(
            'tx_seminars_event_types',
            ['title' => 'paprika event type']
        );
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'event_type' => $eventTypeUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $this->subject->limitToDateAndSingleRecords();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ///////////////////////////////////////////////////////////////
    // Tests for limitToFullTextSearch() for event date records
    ///////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventDateWithSearchWordInAccreditationNumber()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'accreditation_number' => 'avocado paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventDateWithoutSearchWordInAccreditationNumber()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'accreditation_number' => 'paprika event',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventDateWithSearchWordInSpeakerTitle()
    {
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['title' => 'avocado paprika speaker']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'speakers' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $eventUid,
            $speakerUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventDateWithoutSearchWordInSpeakerTitle()
    {
        $speakerUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['title' => 'paprika speaker']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'speakers' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_speakers_mm',
            $eventUid,
            $speakerUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventDateWithSearchWordInPlaceTitle()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'avocado paprika place']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventDateWithoutSearchWordInPlaceTitle()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['title' => 'paprika place']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchFindsEventDateWithSearchWordInPlaceCity()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'avocado paprika city']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $eventUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToFullTextSearchIgnoresEventDateWithoutSearchWordInPlaceCity()
    {
        $placeUid = $this->testingFramework->createRecord(
            'tx_seminars_sites',
            ['city' => 'paprika city']
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'place' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_place_mm',
            $eventUid,
            $placeUid
        );
        $this->subject->limitToFullTextSearch('avocado');
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ///////////////////////////////////////////
    // Tests concerning limitToRequiredEvents
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function limitToRequiredEventsCanFindOneRequiredEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['requirements' => 1]
        );
        $requiredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $eventUid,
            $requiredEventUid
        );
        $this->subject->limitToRequiredEventTopics($eventUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToRequiredEventsCanFindTwoRequiredEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['requirements' => 1]
        );
        $requiredEventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $requiredEventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $eventUid,
            $requiredEventUid1
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $eventUid,
            $requiredEventUid2
        );

        $this->subject->limitToRequiredEventTopics($eventUid);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToRequiredEventsFindsOnlyRequiredEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['requirements' => 1]
        );
        $requiredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $eventUid,
            $requiredEventUid
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $eventUid
        );

        $this->subject->limitToRequiredEventTopics($eventUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertNotEquals(
            $dependingEventUid,
            $bag->current()->getUid()
        );
    }

    ///////////////////////////////////////////
    // Tests concerning limitToDependingEvents
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function limitToDependingEventsCanFindOneDependingEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['requirements' => 1]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $eventUid
        );
        $this->subject->limitToDependingEventTopics($eventUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToDependingEventsCanFindTwoDependingEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['requirements' => 1]
        );
        $dependingEventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $dependingEventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid1,
            $eventUid
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid2,
            $eventUid
        );

        $this->subject->limitToDependingEventTopics($eventUid);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToDependingEventsFindsOnlyDependingEvents()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['requirements' => 1]
        );
        $dependingEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $requiredEventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $dependingEventUid,
            $eventUid
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $eventUid,
            $requiredEventUid
        );

        $this->subject->limitToDependingEventTopics($eventUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertNotEquals(
            $requiredEventUid,
            $bag->current()->getUid()
        );
    }

    ////////////////////////////////////////////////////////////
    // Tests concerning limitToTopicsWithoutRegistrationByUser
    ////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToTopicsWithoutRegistrationByUserFindsTopicWithoutDate()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser(
            $this->testingFramework->createFrontEndUser()
        );
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $topicUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToTopicsWithoutRegistrationByUserFindsTopicWithDate()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser(
            $this->testingFramework->createFrontEndUser()
        );
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $topicUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToTopicsWithoutRegistrationByUserNotFindsDate()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser(
            $this->testingFramework->createFrontEndUser()
        );
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToTopicsWithoutRegistrationByUserFindsTopicWithDateWithRegistrationByOtherUser()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $dateUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser(
            $this->testingFramework->createFrontEndUser()
        );
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $topicUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToWithoutRegistrationByUserDoesNotFindTopicWithDateRegistrationByTheUserWithoutExpiry()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'expiry' => 0,
            ]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $dateUid, 'user' => $userUid]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser($userUid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToWithoutRegistrationByUserDoesNotFindTopicWithDateRegistrationByTheUserWithFutureExpiry()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'expiry' => $this->future,
            ]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $dateUid, 'user' => $userUid]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser($userUid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToWithoutRegistrationByUserFindsTopicWithDateRegistrationByTheUserWithPastExpiry()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
                'expiry' => $this->past,
            ]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $dateUid, 'user' => $userUid]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser($userUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToWithoutRegistrationByUserDoesNotFindTopicWithDateRegistrationByTheUserAndOtherUser()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );
        $userUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $dateUid, 'user' => $userUid]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $dateUid,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $this->subject->limitToTopicsWithoutRegistrationByUser($userUid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToTopicsWithoutRegistrationByUserAndLimitToRequiredEventTopicsCanReturnOneEntry()
    {
        $requiredTopicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $requiredTopicUid,
            ]
        );

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'requirements' => 1,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_requirements_mm',
            $eventUid,
            $requiredTopicUid
        );

        $this->subject->limitToRequiredEventTopics($eventUid);
        $this->subject->limitToTopicsWithoutRegistrationByUser(
            $this->testingFramework->createFrontEndUser()
        );
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    //////////////////////////////////////////////////////
    // Test concerning limitToCancelationReminderNotSent
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToCancelationDeadlineReminderNotSentFindsEventWithCancelationReminderSentFlagFalse()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelation_deadline_reminder_sent' => 0]
        );

        $this->subject->limitToCancelationDeadlineReminderNotSent();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToCancelationDeadlineReminderNotSentNotFindsEventWithCancelationReminderSentFlagTrue()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelation_deadline_reminder_sent' => 1]
        );

        $this->subject->limitToCancelationDeadlineReminderNotSent();
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    //////////////////////////////////////////////////////////
    // Test concerning limitToEventTakesPlaceReminderNotSent
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToEventTakesPlaceReminderNotSentFindsEventWithConfirmationInformationSentFlagFalse()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_takes_place_reminder_sent' => 0]
        );

        $this->subject->limitToEventTakesPlaceReminderNotSent();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventTakesPlaceReminderNotSentNotFindsEventWithConfirmationInformationSentFlagTrue()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['event_takes_place_reminder_sent' => 1]
        );

        $this->subject->limitToEventTakesPlaceReminderNotSent();
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    ///////////////////////////////////
    // Tests concerning limitToStatus
    ///////////////////////////////////

    /**
     * @test
     */
    public function limitToStatusFindsEventWithStatusCanceledIfLimitIsStatusCanceled()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusNotFindsEventWithStatusPlannedIfLimitIsStatusCanceled()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusNotFindsEventWithStatusConfirmedIfLimitIsStatusCanceled()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusFindsEventWithStatusConfirmedIfLimitIsStatusConfirmed()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusNotFindsEventWithStatusPlannedIfLimitIsStatusConfirmed()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusNotFindsEventWithStatusCanceledIfLimitIsStatusConfirmed()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusFindsEventWithStatusPlannedIfLimitIsStatusPlanned()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusNotFindsEventWithStatusConfirmedIfLimitIsStatusPlanned()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToStatusNotFindsEventWithStatusCanceledIfLimitIsStatusPlanned()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED]
        );

        $this->subject->limitToStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    //////////////////////////////////////////////////
    // Tests concerning limitToDaysBeforeBeginDate
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function testlimitToDaysBeforeBeginDateFindsEventWithFutureBeginDateWithinProvidedDays()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY]
        );

        $this->subject->limitToDaysBeforeBeginDate(2);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function testlimitToDaysBeforeBeginDateFindsEventWithPastBeginDateWithinProvidedDays()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME'] - Time::SECONDS_PER_DAY]
        );

        $this->subject->limitToDaysBeforeBeginDate(3);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function testlimitToDaysBeforeBeginDateNotFindsEventWithFutureBeginDateOutOfProvidedDays()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME'] + (2 * Time::SECONDS_PER_DAY)]
        );

        $this->subject->limitToDaysBeforeBeginDate(1);
        $bag = $this->subject->build();

        self::assertSame(
            0,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function testlimitToDaysBeforeBeginDateFindsEventWithPastBeginDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME'] - (2 * Time::SECONDS_PER_DAY)]
        );

        $this->subject->limitToDaysBeforeBeginDate(1);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function testlimitToDaysBeforeBeginDateFindsEventWithNoBeginDate()
    {
        $this->testingFramework->createRecord('tx_seminars_seminars');

        $this->subject->limitToDaysBeforeBeginDate(1);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    // Tests concerning limitToEarliestBeginOrEndDate

    /**
     * @test
     */
    public function limitToEarliestBeginOrEndDateForEventWithoutBeginDateFindsThisEvent()
    {
        $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->subject->limitToEarliestBeginOrEndDate(42);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEarliestBeginOrEndDateForEventWithBeginDateEqualToGivenTimestampFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );
        $this->subject->limitToEarliestBeginOrEndDate(42);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEarliestBeginOrEndDateForEventWithGreaterBeginDateThanGivenTimestampFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );
        $this->subject->limitToEarliestBeginOrEndDate(21);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEarliestBeginOrEndDateForEventWithBeginDateLowerThanGivenTimestampDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );
        $this->subject->limitToEarliestBeginOrEndDate(84);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEarliestBeginOrEndDateForZeroGivenAsTimestampUnsetsFilter()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 21]
        );

        $this->subject->limitToEarliestBeginOrEndDate(42);

        $this->subject->limitToEarliestBeginOrEndDate(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEarliestBeginOrEndDateForFindsEventStartingBeforeAndEndingAfterDeadline()
    {
        $this->testingFramework->createRecord('tx_seminars_seminars', ['begin_date' => 8, 'end_date' => 10]);
        $this->subject->limitToEarliestBeginOrEndDate(9);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    // Tests concerning limitToLatestBeginOrEndDate

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForEventWithoutDateDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->subject->limitToLatestBeginOrEndDate(42);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForEventBeginDateEqualToGivenTimestampFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );
        $this->subject->limitToLatestBeginOrEndDate(42);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForEventWithBeginDateAfterGivenTimestampDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );
        $this->subject->limitToLatestBeginOrEndDate(21);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForEventBeginDateBeforeGivenTimestampFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );
        $this->subject->limitToLatestBeginOrEndDate(84);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForEventEndDateEqualToGivenTimestampFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['end_date' => 42]
        );
        $this->subject->limitToLatestBeginOrEndDate(42);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForEventWithEndDateAfterGivenTimestampDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['end_date' => 42]
        );
        $this->subject->limitToLatestBeginOrEndDate(21);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForEventEndDateBeforeGivenTimestampFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['end_date' => 42]
        );
        $this->subject->limitToLatestBeginOrEndDate(84);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToLatestBeginOrEndDateForZeroGivenUnsetsTheFilter()
    {
        $this->testingFramework->createRecord('tx_seminars_seminars');

        $this->subject->limitToLatestBeginOrEndDate(42);
        $this->subject->limitToLatestBeginOrEndDate(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    ///////////////////////////////////////
    // Tests concerning showHiddenRecords
    ///////////////////////////////////////

    /**
     * @test
     */
    public function showHiddenRecordsForHiddenEventFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1]
        );

        $this->subject->showHiddenRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function showHiddenRecordsForVisibleEventFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 0]
        );

        $this->subject->showHiddenRecords();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning limitToEventsWithVacancies
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithNoRegistrationNeededFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['needs_registration' => 0]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithUnlimitedVacanciesFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['needs_registration' => 1, 'attendees_max' => 0]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventNoVacanciesAndQueueDoesNotFindThisEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'needs_registration' => 1,
                'attendees_max' => 1,
                'queue_size' => 1,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'seats' => 1]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithNoVacanciesDoesNotFindThisEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['needs_registration' => 1, 'attendees_max' => 1]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'seats' => 1]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithNoVacanciesThroughOfflineRegistrationsDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'needs_registration' => 1,
                'attendees_max' => 10,
                'offline_attendees' => 10,
            ]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithNoVacanciesThroughRegistrationsWithMultipleSeatsNotFindsIt()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'needs_registration' => 1,
                'attendees_max' => 10,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'seats' => 10]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithNoVacanciesThroughRegularAndOfflineRegistrationsNotFindsIt()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'needs_registration' => 1,
                'attendees_max' => 10,
                'offline_attendees' => 5,
            ]
        );

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'seats' => 5]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithVacanciesAndNoAttendeesFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['needs_registration' => 1, 'attendees_max' => 10]
        );

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    ///////////////////////////////////////
    // Tests concerning limitToOrganizers
    ///////////////////////////////////////

    /**
     * @test
     */
    public function limitToOrganizersForOneProvidedOrganizerAndEventWithThisOrganizerFindsThisEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->subject->limitToOrganizers((string)$organizerUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToOrganizersForOneProvidedOrganizerAndEventWithoutOrganizerDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );

        $this->subject->limitToOrganizers((string)$organizerUid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToOrganizersForOneProvidedOrganizerAndEventWithOtherOrganizerDoesNotFindThisEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $organizerUid2 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid1,
            'organizers'
        );

        $this->subject->limitToOrganizers((string)$organizerUid2);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToOrganizersForTwoProvidedOrganizersAndEventWithFirstOrganizerFindsThisEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $organizerUid2 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid1,
            'organizers'
        );

        $this->subject->limitToOrganizers($organizerUid1 . ',' . $organizerUid2);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToOrganizersForProvidedOrganizerAndTwoEventsWithThisOrganizerFindsTheseEvents()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid1,
            $organizerUid,
            'organizers'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid2,
            $organizerUid,
            'organizers'
        );

        $this->subject->limitToOrganizers((string)$organizerUid);
        $bag = $this->subject->build();

        self::assertSame(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToOrganizersForProvidedOrganizerAndTopicWithOrganizerReturnsTheTopicsDate()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $topicUid,
            $organizerUid,
            'organizers'
        );

        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topicUid,
            ]
        );

        $this->subject->limitToOrganizers((string)$organizerUid);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToOrganizersForNoProvidedOrganizerFindsEventWithOrganizer()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $organizerUid,
            'organizers'
        );

        $this->subject->limitToOrganizers('');
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    ////////////////////////////////
    // Tests concerning limitToAge
    ////////////////////////////////

    /**
     * @test
     */
    public function limitToAgeForAgeWithinEventsAgeRangeFindsThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 50]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(6);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForAgeEqualToLowerLimitOfAgeRangeFindsThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 15, 'maximum_age' => 50]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForAgeEqualToHigherLimitOfAgeRangeFindsThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 15]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForNoLowerLimitAndAgeLowerThanMaximumAgeFindsThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 0, 'maximum_age' => 50]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForAgeHigherThanMaximumAgeDoesNotFindThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 0, 'maximum_age' => 50]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(51);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForNoHigherLimitAndAgeHigherThanMinimumAgeFindsThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 0]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForAgeLowerThanMinimumAgeFindsThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 0]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(4);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForEventWithoutTargetGroupAndAgeProvidedFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->subject->limitToAge(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForEventWithTargetGroupWithNoLimitsFindsThisEvent()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups'
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForEventWithTwoTargetGroupOneWithMatchingRangeAndOneWithoutMatchingRangeFindsThisEvent()
    {
        $targetGroupUid1 = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 20, 'maximum_age' => 50]
        );
        $targetGroupUid2 = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 20]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid1,
            'target_groups'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid2,
            'target_groups'
        );

        $this->subject->limitToAge(21);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForEventWithTwoTargetGroupBothWithMatchingRangesFindsThisEventOnlyOnce()
    {
        $targetGroupUid1 = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 50]
        );
        $targetGroupUid2 = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 20]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid1,
            'target_groups'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid2,
            'target_groups'
        );

        $this->subject->limitToAge(6);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToAgeForAgeZeroGivenFindsEventWithAgeLimits()
    {
        $targetGroupUid = $this->testingFramework->createRecord(
            'tx_seminars_target_groups',
            ['minimum_age' => 5, 'maximum_age' => 15]
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $eventUid,
            $targetGroupUid,
            'target_groups'
        );

        $this->subject->limitToAge(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////////////
    // Tests concerning limitToMaximumPrice
    /////////////////////////////////////////

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithRegularPriceLowerThanMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 42]
        );

        $this->subject->limitToMaximumPrice(43);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithRegularPriceZeroFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 0]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithRegularPriceEqualToMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 50]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithRegularPriceHigherThanMaximumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithSpecialPriceLowerThanMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51, 'price_special' => 49]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithSpecialPriceEqualToMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 43, 'price_special' => 42]
        );

        $this->subject->limitToMaximumPrice(42);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithSpecialPriceHigherThanMaximumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 43, 'price_special' => 43]
        );

        $this->subject->limitToMaximumPrice(42);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithRegularBoardPriceLowerThanMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51, 'price_regular_board' => 49]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithRegularBoardPriceEqualToMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51, 'price_regular_board' => 50]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithRegularBoardPriceHigherThanMaximumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51, 'price_regular_board' => 51]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithSpecialBoardPriceLowerThanMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51, 'price_special_board' => 49]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithSpecialBoardPriceEqualToMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51, 'price_special_board' => 50]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithSpecialBoardPriceHigherThanMaximumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 51, 'price_special_board' => 51]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForTopicWithRegularPriceLowerThanMaximumFindsTheDateForThisEvent()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 49,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $dateUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
        self::assertSame(
            $dateUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForTopicWithRegularPriceHigherThanMaximumDoesNotFindTheDateForThisEvent()
    {
        $topicUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'topic' => $topicUid,
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceLowerThanMaximumFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_regular_early' => 49,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndRegularEarlyPriceEqualToMaximumFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_regular_early' => 50,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithDeadlineInFutureAndRegularEarlyPriceHigherThanMaxNotFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_regular_early' => 51,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInPastAndRegularEarlyPriceLowerThanMaxNotFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_regular_early' => 49,
                'deadline_early_bird' => $this->past,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceLowerThanMaxFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_special_early' => 49,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndSpecialEarlyPriceEqualToMaxFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_special_early' => 50,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithDeadlineInFutureAndSpecialEarlyPriceHigherThanMaxNotFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_special_early' => 51,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForFutureEarlyBirdDeadlineAndNoEarlyBirdPriceDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInPastAndSpecialEarlyPriceLowerThanMaxNotFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_special_early' => 49,
                'deadline_early_bird' => $this->past,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceWithDeadlineInFutureAndRegularEarlyHigherThanAndRegularLowerThanMaxiNotFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 49,
                'price_regular_early' => 51,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceWithDeadlineInFutureAndNoSpecialEarlyAndRegularPriceLowerThanMaximumFindsIt()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 49,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndNoEarlySpecialPriceAndSpecialPriceLowerThanMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 51,
                'price_special' => 49,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForEventWithEarlyBirdDeadlineInFutureAndNoRegularEarlyPriceAndRegularPriceLowerThanMaximumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 49,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMaximumPrice(50);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMaximumPriceForZeroGivenFindsEventWithNonZeroPrice()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 15,
            ]
        );

        $this->subject->limitToMaximumPrice(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /////////////////////////////////////////
    // Tests concerning limitToMinimumPrice
    /////////////////////////////////////////

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithRegularPriceLowerThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 14]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForPriceGivenAndEventWithoutPricesDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord('tx_seminars_seminars');

        $this->subject->limitToMinimumPrice(16);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithRegularPriceEqualToMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 15]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithRegularPriceGreaterThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 16]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithRegularBoardPriceGreaterThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular_board' => 16]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithRegularBoardPriceEqualToMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular_board' => 15]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithRegularBoardPriceLowerThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular_board' => 14]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithSpecialBoardPriceGreaterThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_special_board' => 16]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithSpecialBoardPriceEqualToMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_special_board' => 15]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithSpecialBoardPriceLowerThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_special_board' => 14]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithSpecialPriceGreaterThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_special' => 16]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithSpecialPriceEqualToMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_special' => 15]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEventWithSpecialPriceLowerThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_special' => 14]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceZeroAndRegularPriceHigherThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular' => 16,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureNoPriceSetDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['deadline_early_bird' => $this->future]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceLowerThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular_early' => 14,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceEqualToMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular_early' => 15,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndRegularEarlyPriceHigherThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular_early' => 16,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceZeroAndSpecialPriceHigherThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_special' => 16,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceLowerThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_special_early' => 14,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceEqualToMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_special_early' => 15,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInFutureAndSpecialEarlyPriceHigherThanMinimumFindsThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_special_early' => 16,
                'deadline_early_bird' => $this->future,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInPastAndRegularEarlyPriceHigherThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_regular_early' => 16,
                'deadline_early_bird' => $this->past,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForEarlyBirdDeadlineInPastAndSpecialEarlyPriceHigherThanMinimumDoesNotFindThisEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'price_special_early' => 16,
                'deadline_early_bird' => $this->past,
            ]
        );

        $this->subject->limitToMinimumPrice(15);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToMinimumPriceForMinimumPriceZeroFindsEventWithRegularPrice()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['price_regular' => 16]
        );

        $this->subject->limitToMinimumPrice(0);
        $bag = $this->subject->build();

        self::assertSame(
            1,
            $bag->count()
        );
    }
}
