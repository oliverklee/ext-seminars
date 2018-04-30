<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BagBuilder_OrganizerTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Organizer
     */
    private $fixture;
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = new \Tx_Seminars_BagBuilder_Organizer();
        $this->fixture->setTestMode();
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
        self::assertInstanceOf(\Tx_Seminars_Bag_Abstract::class, $this->fixture->build());
    }

    /////////////////////////////
    // Tests for limitToEvent()
    /////////////////////////////

    public function testLimitToEventWithNegativeEventUidThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $eventUid must be > 0.'
        );

        $this->fixture->limitToEvent(-1);
    }

    public function testLimitToEventWithZeroEventUidThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $eventUid must be > 0.'
        );

        $this->fixture->limitToEvent(0);
    }

    public function testLimitToEventFindsOneOrganizerOfEvent()
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers' => 1]
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $eventUid,
            $organizerUid
        );

        $this->fixture->limitToEvent($eventUid);
        $bag = $this->fixture->build();

        self::assertEquals(
            1,
            $bag->countWithoutLimit()
        );
    }

    public function testLimitToEventFindsTwoOrganizersOfEvent()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers' => 2]
        );
        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $eventUid,
            $organizerUid1
        );
        $organizerUid2 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $eventUid,
            $organizerUid2
        );

        $this->fixture->limitToEvent($eventUid);
        $bag = $this->fixture->build();

        self::assertEquals(
            2,
            $bag->countWithoutLimit()
        );
    }

    public function testLimitToEventIgnoresOrganizerOfOtherEvent()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers' => 1]
        );
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $eventUid1,
            $organizerUid
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );

        $this->fixture->limitToEvent($eventUid2);
        $bag = $this->fixture->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToEventSortsByRelationSorting()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers' => 2]
        );
        $organizerUid1 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $organizerUid2 = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );

        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $eventUid,
            $organizerUid2
        );
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $eventUid,
            $organizerUid1
        );

        $this->fixture->limitToEvent($eventUid);
        $bag = $this->fixture->build();
        $bag->rewind();

        self::assertEquals(
            $organizerUid2,
            $bag->current()->getUid()
        );
    }
}
