<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EventMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->subject = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ////////////////////////////////////
    // Tests regarding getTimeSlots().
    ////////////////////////////////////

    /**
     * @test
     */
    public function getTimeSlotsReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getTimeSlots());
    }

    /**
     * @test
     */
    public function getTimeSlotsWithOneTimeSlotReturnsListOfTimeSlots()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $uid]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $uid,
            ['timeslots' => 1]
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_TimeSlot::class,
            $model->getTimeSlots()->first()
        );
    }

    /**
     * @test
     */
    public function getTimeSlotsWithOneTimeSlotReturnsOneTimeSlot()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $uid]
        );
        $this->testingFramework->changeRecord(
            'tx_seminars_seminars',
            $uid,
            ['timeslots' => 1]
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $timeSlotUid,
            $model->getTimeSlots()->getUids()
        );
    }

    /////////////////////////////////
    // Tests regarding getPlaces().
    /////////////////////////////////

    /**
     * @test
     */
    public function getPlacesReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getPlaces());
    }

    /**
     * @test
     */
    public function getPlacesWithOnePlaceReturnsListOfPlaces()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $place = MapperRegistry::get(\Tx_Seminars_Mapper_Place::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $place->getUid(),
            'place'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Place::class, $model->getPlaces()->first());
    }

    /**
     * @test
     */
    public function getPlacesWithOnePlaceReturnsOnePlace()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $place = MapperRegistry::get(\Tx_Seminars_Mapper_Place::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $place->getUid(),
            'place'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $place->getUid(),
            $model->getPlaces()->getUids()
        );
    }

    ///////////////////////////////////
    // Tests regarding getLodgings().
    ///////////////////////////////////

    /**
     * @test
     */
    public function getLodgingsReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getLodgings());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsListOfLodgings()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $lodging = MapperRegistry::get(\Tx_Seminars_Mapper_Lodging::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $lodging->getUid(),
            'lodgings'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Lodging::class, $model->getLodgings()->first());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsOneLodging()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $lodging = MapperRegistry::get(\Tx_Seminars_Mapper_Lodging::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $lodging->getUid(),
            'lodgings'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $lodging->getUid(),
            $model->getLodgings()->getUids()
        );
    }

    ////////////////////////////////
    // Tests regarding getFoods().
    ////////////////////////////////

    /**
     * @test
     */
    public function getFoodsReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getFoods());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsListOfFoods()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $food = MapperRegistry::get(\Tx_Seminars_Mapper_Food::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $food->getUid(),
            'foods'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Food::class, $model->getFoods()->first());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsOneFood()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $food = MapperRegistry::get(\Tx_Seminars_Mapper_Food::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $food->getUid(),
            'foods'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $food->getUid(),
            $model->getFoods()->getUids()
        );
    }

    ///////////////////////////////////
    // Tests regarding getSpeakers().
    ///////////////////////////////////

    /**
     * @test
     */
    public function getSpeakersReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getSpeakers());
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsListOfSpeakers()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'speakers'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getSpeakers()->first()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsOneSpeaker()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'speakers'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getSpeakers()->getUids()
        );
    }

    ///////////////////////////////////
    // Tests regarding getPartners().
    ///////////////////////////////////

    /**
     * @test
     */
    public function getPartnersReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getPartners());
    }

    /**
     * @test
     */
    public function getPartnersWithOnePartnerReturnsListOfSpeakers()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'partners'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getPartners()->first()
        );
    }

    /**
     * @test
     */
    public function getPartnersWithOnePartnerReturnsOnePartner()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'partners'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getPartners()->getUids()
        );
    }

    ///////////////////////////////////
    // Tests regarding getTutors().
    ///////////////////////////////////

    /**
     * @test
     */
    public function getTutorsReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getTutors());
    }

    /**
     * @test
     */
    public function getTutorsWithOneTutorReturnsListOfSpeakers()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'tutors'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getTutors()->first()
        );
    }

    /**
     * @test
     */
    public function getTutorsWithOneTutorReturnsOneTutor()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'tutors'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getTutors()->getUids()
        );
    }

    ///////////////////////////////////
    // Tests regarding getLeaders().
    ///////////////////////////////////

    /**
     * @test
     */
    public function getLeadersReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getLeaders());
    }

    /**
     * @test
     */
    public function getLeadersWithOneLeaderReturnsListOfSpeakers()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'leaders'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getLeaders()->first()
        );
    }

    /**
     * @test
     */
    public function getLeadersWithOneLeaderReturnsOneLeader()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speaker = MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speaker->getUid(),
            'leaders'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getLeaders()->getUids()
        );
    }

    /////////////////////////////////////
    // Tests regarding getOrganizers().
    /////////////////////////////////////

    /**
     * @test
     */
    public function getOrganizersReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getOrganizers());
    }

    /**
     * @test
     */
    public function getOrganizersWithOneOrganizerReturnsListOfOrganizers()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers' => 1]
        );
        $organizer = MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class)
            ->getNewGhost();
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $uid,
            $organizer->getUid()
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Organizer::class, $model->getOrganizers()->first());
    }

    /**
     * @test
     */
    public function getOrganizersWithOneOrganizerReturnsOneOrganizer()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['organizers' => 1]
        );
        $organizer = MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class)
            ->getNewGhost();
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $uid,
            $organizer->getUid()
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $organizer->getUid(),
            $model->getOrganizers()->getUids()
        );
    }

    /////////////////////////////////////////////
    // Tests regarding getOrganizingPartners().
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getOrganizingPartnersReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getOrganizingPartners());
    }

    /**
     * @test
     */
    public function getOrganizingPartnersWithOneOrganizingReturnsListOfOrganizers()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $organizer = MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $organizer->getUid(),
            'organizing_partners'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Organizer::class, $model->getOrganizingPartners()->first());
    }

    /**
     * @test
     */
    public function getOrganizingPartnersWithOneOrganizingPartnersReturnsOneOrganizingPartner()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $organizer = MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $organizer->getUid(),
            'organizing_partners'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $organizer->getUid(),
            $model->getOrganizingPartners()->getUids()
        );
    }

    ////////////////////////////////
    // Tests regarding getOwner().
    ////////////////////////////////

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance()
    {
        /** @var FrontEndUser $frontEndUser */
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)
            ->getLoadedTestingModel([]);
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel(['owner_feuser' => $frontEndUser->getUid()]);

        self::assertInstanceOf(FrontEndUser::class, $testingModel->getOwner());
    }

    ////////////////////////////////////////
    // Tests regarding getEventManagers().
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getEventManagersReturnsListInstance()
    {
        /** @var \Tx_Seminars_Model_Event $testingModel */
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getEventManagers());
    }

    /**
     * @test
     */
    public function getEventManagersWithOneEventManagerReturnsListOfFrontEndUsers()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $frontEndUser->getUid(),
            'vips'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(FrontEndUser::class, $model->getEventManagers()->first());
    }

    /**
     * @test
     */
    public function getEventManagersWithOneEventManagerReturnsOneEventManager()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $frontEndUser->getUid(),
            'vips'
        );

        /** @var \Tx_Seminars_Model_Event $model */
        $model = $this->subject->find($uid);
        self::assertEquals(
            $frontEndUser->getUid(),
            $model->getEventManagers()->getUids()
        );
    }

    ///////////////////////////////////////////
    // Tests concerning findByPublicationHash
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function findByPublicationHashForEmptyPublicationHashGivenThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given publication hash was empty.'
        );

        $this->subject->findByPublicationHash('');
    }

    /**
     * @test
     */
    public function findByPublicationForEventWithProvidedPublicationHashReturnsThisEvent()
    {
        $publicationHash = 'blubb';

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['publication_hash' => $publicationHash]
        );

        self::assertEquals(
            $eventUid,
            $this->subject->findByPublicationHash($publicationHash)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByPublicationForNoEventWithProvidedPublicationHashReturnsNull()
    {
        $this->testingFramework->createRecord('tx_seminars_seminars');

        self::assertNull(
            $this->subject->findByPublicationHash('foo')
        );
    }

    /**
     * @test
     */
    public function findByPublicationForEventWithProvidedPublicationHashReturnsEventModel()
    {
        $publicationHash = 'blubb';

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['publication_hash' => $publicationHash]
        );

        self::assertInstanceOf(
            \Tx_Seminars_Model_Event::class,
            $this->subject->findByPublicationHash($publicationHash)
        );
    }

    ///////////////////////////////////////
    // Tests concerning the registrations
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getRegistrationsWithOneRegistrationReturnsOneRegistration()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['registrations' => 1]
        );
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid]
        );

        /** @var \Tx_Seminars_Model_Event $event */
        $event = $this->subject->find($eventUid);
        self::assertEquals(
            $registrationUid,
            $event->getRegistrations()->getUids()
        );
    }

    ////////////////////////////////////////
    // Tests concerning findAllByBeginDate
    ////////////////////////////////////////

    /**
     * @test
     *
     * @doesNotPerformAssertions
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForPositiveSameMinimumAndMaximumNotThrowsException()
    {
        $this->subject->findAllByBeginDate(42, 42);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForZeroMinimumAndPositiveMaximumNotThrowsException()
    {
        $this->subject->findAllByBeginDate(0, 1);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForZeroMinimumAndZeroMaximumThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findAllByBeginDate(0, 0);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForMinimumSmallerThanMaximumNotThrowsException()
    {
        $this->subject->findAllByBeginDate(1, 2);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForNegativeMinimumSmallerThanMaximumThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findAllByBeginDate(-1, 1);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForMinimumGreaterThanMaximumThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findAllByBeginDate(2, 1);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateNotFindsEventWithBeginDateSmallerThanMinimum()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 41]
        );

        self::assertTrue(
            $this->subject->findAllByBeginDate(42, 91)->isEmpty()
        );
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateFindsEventWithBeginDateEqualToMinimum()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );

        self::assertTrue(
            $this->subject->findAllByBeginDate(42, 91)->hasUid($uid)
        );
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateFindsEventWithBeginDateBetweenMinimumAndMaximum()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 2]
        );

        self::assertTrue(
            $this->subject->findAllByBeginDate(1, 3)->hasUid($uid)
        );
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateFindsEventWithBeginDateEqualToMaximum()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 91]
        );

        self::assertTrue(
            $this->subject->findAllByBeginDate(42, 91)->hasUid($uid)
        );
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateNotFindsEventWithBeginDateGreaterThanMaximum()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 92]
        );

        self::assertTrue(
            $this->subject->findAllByBeginDate(42, 91)->isEmpty()
        );
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateCanFindEventWithZeroBeginDate()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 0]
        );

        self::assertTrue(
            $this->subject->findAllByBeginDate(0, 1)->hasUid($uid)
        );
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateCanFindTwoEvents()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 42]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => 43]
        );

        self::assertEquals(
            2,
            $this->subject->findAllByBeginDate(42, 91)->count()
        );
    }

    //////////////////////////////////////
    // Tests concerning findNextUpcoming
    //////////////////////////////////////

    /**
     * @test
     */
    public function findNextUpcomingWithNoEventsThrowsEmptyQueryResultException()
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findNextUpcoming();
    }

    /**
     * @test
     */
    public function findNextUpcomingWithPastEventThrowsEmptyQueryResultException()
    {
        $this->expectException(NotFoundException::class);

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_ACCESS_TIME'] - 1000]
        );

        $this->subject->findNextUpcoming();
    }

    /**
     * @test
     */
    public function findNextUpcomingWithUpcomingEventReturnsModelOfUpcomingEvent()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000]
        );

        self::assertSame(
            $uid,
            $this->subject->findNextUpcoming()->getUid()
        );
    }

    /**
     * @test
     */
    public function findNextUpcomingWithTwoUpcomingEventsReturnsOnlyModelOfNextUpcomingEvent()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 2000]
        );
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_ACCESS_TIME'] + 1000]
        );

        self::assertSame(
            $uid,
            $this->subject->findNextUpcoming()->getUid()
        );
    }

    /*
     * Tests concerning findForAutomaticStatusChange
     */

    /**
     * @test
     */
    public function findForAutomaticStatusChangeForNoEventsReturnsEmptyList()
    {
        $result = $this->subject->findForAutomaticStatusChange();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function findForAutomaticStatusChangeFindsPlannedEventWithAutomaticStatusChange()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED, 'automatic_confirmation_cancelation' => 1]
        );

        $result = $this->subject->findForAutomaticStatusChange();

        self::assertFalse($result->isEmpty());
        self::assertSame((string)$uid, $result->getUids());
    }

    /**
     * @test
     */
    public function findForAutomaticStatusChangeNotFindsCanceledEventWithAutomaticStatusChange()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CANCELED, 'automatic_confirmation_cancelation' => 1]
        );

        $result = $this->subject->findForAutomaticStatusChange();

        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function findForAutomaticStatusChangeNotFindsConfirmedEventWithAutomaticStatusChange()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_CONFIRMED, 'automatic_confirmation_cancelation' => 1]
        );

        $result = $this->subject->findForAutomaticStatusChange();

        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function findForAutomaticStatusChangeNotFindsPlannedEventWithoutAutomaticStatusChange()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED, 'automatic_confirmation_cancelation' => 0]
        );

        $result = $this->subject->findForAutomaticStatusChange();

        self::assertTrue($result->isEmpty());
    }
}
