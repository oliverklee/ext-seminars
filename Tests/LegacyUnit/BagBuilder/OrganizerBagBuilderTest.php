<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Bag\AbstractBag;

final class OrganizerBagBuilderTest extends TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Organizer
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_BagBuilder_Organizer();
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

    /////////////////////////////
    // Tests for limitToEvent()
    /////////////////////////////

    /**
     * @test
     */
    public function limitToEventWithNegativeEventUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $eventUid must be > 0.'
        );

        $this->subject->limitToEvent(-1);
    }

    /**
     * @test
     */
    public function limitToEventWithZeroEventUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $eventUid must be > 0.'
        );

        $this->subject->limitToEvent(0);
    }

    /**
     * @test
     */
    public function limitToEventFindsOneOrganizerOfEvent()
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

        $this->subject->limitToEvent($eventUid);
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->countWithoutLimit()
        );
    }

    /**
     * @test
     */
    public function limitToEventFindsTwoOrganizersOfEvent()
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

        $this->subject->limitToEvent($eventUid);
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->countWithoutLimit()
        );
    }

    /**
     * @test
     */
    public function limitToEventIgnoresOrganizerOfOtherEvent()
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

        $this->subject->limitToEvent($eventUid2);
        $bag = $this->subject->build();

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

        $this->subject->limitToEvent($eventUid);
        $bag = $this->subject->build();
        $bag->rewind();

        self::assertEquals(
            $organizerUid2,
            $bag->current()->getUid()
        );
    }
}
