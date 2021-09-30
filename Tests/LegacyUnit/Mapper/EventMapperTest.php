<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper as OelibFrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Mapper\EventMapper;

/**
 * @covers \OliverKlee\Seminars\Mapper\EventMapper
 */
final class EventMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var EventMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->subject = MapperRegistry::get(EventMapper::class);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests regarding getTimeSlots().

    /**
     * @test
     */
    public function getTimeSlotsReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getTimeSlots());
    }

    /**
     * @test
     */
    public function getTimeSlotsWithOneTimeSlotReturnsListOfTimeSlots(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_TimeSlot::class,
            $model->getTimeSlots()->first()
        );
    }

    /**
     * @test
     */
    public function getTimeSlotsWithOneTimeSlotReturnsOneTimeSlot(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $timeSlotUid,
            $model->getTimeSlots()->getUids()
        );
    }

    // Tests regarding getPlaces().

    /**
     * @test
     */
    public function getPlacesReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getPlaces());
    }

    /**
     * @test
     */
    public function getPlacesWithOnePlaceReturnsListOfPlaces(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Place::class, $model->getPlaces()->first());
    }

    /**
     * @test
     */
    public function getPlacesWithOnePlaceReturnsOnePlace(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $place->getUid(),
            $model->getPlaces()->getUids()
        );
    }

    // Tests regarding getLodgings().

    /**
     * @test
     */
    public function getLodgingsReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getLodgings());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsListOfLodgings(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Lodging::class, $model->getLodgings()->first());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsOneLodging(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $lodging->getUid(),
            $model->getLodgings()->getUids()
        );
    }

    // Tests regarding getFoods().

    /**
     * @test
     */
    public function getFoodsReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getFoods());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsListOfFoods(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Food::class, $model->getFoods()->first());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsOneFood(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $food->getUid(),
            $model->getFoods()->getUids()
        );
    }

    // Tests regarding getSpeakers().

    /**
     * @test
     */
    public function getSpeakersReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getSpeakers());
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsListOfSpeakers(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getSpeakers()->first()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsOneSpeaker(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getSpeakers()->getUids()
        );
    }

    // Tests regarding getPartners().

    /**
     * @test
     */
    public function getPartnersReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getPartners());
    }

    /**
     * @test
     */
    public function getPartnersWithOnePartnerReturnsListOfSpeakers(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getPartners()->first()
        );
    }

    /**
     * @test
     */
    public function getPartnersWithOnePartnerReturnsOnePartner(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getPartners()->getUids()
        );
    }

    // Tests regarding getTutors().

    /**
     * @test
     */
    public function getTutorsReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getTutors());
    }

    /**
     * @test
     */
    public function getTutorsWithOneTutorReturnsListOfSpeakers(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getTutors()->first()
        );
    }

    /**
     * @test
     */
    public function getTutorsWithOneTutorReturnsOneTutor(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getTutors()->getUids()
        );
    }

    // Tests regarding getLeaders().

    /**
     * @test
     */
    public function getLeadersReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getLeaders());
    }

    /**
     * @test
     */
    public function getLeadersWithOneLeaderReturnsListOfSpeakers(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $model->getLeaders()->first()
        );
    }

    /**
     * @test
     */
    public function getLeadersWithOneLeaderReturnsOneLeader(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $speaker->getUid(),
            $model->getLeaders()->getUids()
        );
    }

    // Tests regarding getOrganizers().

    /**
     * @test
     */
    public function getOrganizersReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getOrganizers());
    }

    /**
     * @test
     */
    public function getOrganizersWithOneOrganizerReturnsListOfOrganizers(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Organizer::class, $model->getOrganizers()->first());
    }

    /**
     * @test
     */
    public function getOrganizersWithOneOrganizerReturnsOneOrganizer(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $organizer->getUid(),
            $model->getOrganizers()->getUids()
        );
    }

    // Tests regarding getOrganizingPartners().

    /**
     * @test
     */
    public function getOrganizingPartnersReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getOrganizingPartners());
    }

    /**
     * @test
     */
    public function getOrganizingPartnersWithOneOrganizingReturnsListOfOrganizers(): void
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

        $model = $this->subject->find($uid);
        self::assertInstanceOf(\Tx_Seminars_Model_Organizer::class, $model->getOrganizingPartners()->first());
    }

    /**
     * @test
     */
    public function getOrganizingPartnersWithOneOrganizingPartnersReturnsOneOrganizingPartner(): void
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

        $model = $this->subject->find($uid);
        self::assertEquals(
            $organizer->getUid(),
            $model->getOrganizingPartners()->getUids()
        );
    }

    // Tests regarding getOwner().

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance(): void
    {
        $frontEndUser = MapperRegistry::get(OelibFrontEndUserMapper::class)
            ->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(['owner_feuser' => $frontEndUser->getUid()]);

        self::assertInstanceOf(FrontEndUser::class, $testingModel->getOwner());
    }

    // Tests regarding getEventManagers().

    /**
     * @test
     */
    public function getEventManagersReturnsListInstance(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertInstanceOf(Collection::class, $testingModel->getEventManagers());
    }

    /**
     * @test
     */
    public function getEventManagersWithOneEventManagerReturnsListOfFrontEndUsers(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $frontEndUser = MapperRegistry::get(OelibFrontEndUserMapper::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $frontEndUser->getUid(),
            'vips'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(FrontEndUser::class, $model->getEventManagers()->first());
    }

    /**
     * @test
     */
    public function getEventManagersWithOneEventManagerReturnsOneEventManager(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $frontEndUser = MapperRegistry::get(OelibFrontEndUserMapper::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $frontEndUser->getUid(),
            'vips'
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            $frontEndUser->getUid(),
            $model->getEventManagers()->getUids()
        );
    }

    // Tests concerning findByPublicationHash

    /**
     * @test
     */
    public function findByPublicationHashForEmptyPublicationHashGivenThrowsException(): void
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
    public function findByPublicationForEventWithProvidedPublicationHashReturnsThisEvent(): void
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
    public function findByPublicationForNoEventWithProvidedPublicationHashReturnsNull(): void
    {
        $this->testingFramework->createRecord('tx_seminars_seminars');

        self::assertNull(
            $this->subject->findByPublicationHash('foo')
        );
    }

    /**
     * @test
     */
    public function findByPublicationForEventWithProvidedPublicationHashReturnsEventModel(): void
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
    public function getRegistrationsWithOneRegistrationReturnsOneRegistration(): void
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['registrations' => 1]
        );
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid]
        );

        $event = $this->subject->find($eventUid);
        self::assertEquals(
            $registrationUid,
            $event->getRegistrations()->getUids()
        );
    }

    // Tests concerning findAllByBeginDate

    /**
     * @test
     *
     * @doesNotPerformAssertions
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForPositiveSameMinimumAndMaximumNotThrowsException(): void
    {
        $this->subject->findAllByBeginDate(42, 42);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForZeroMinimumAndPositiveMaximumNotThrowsException(): void
    {
        $this->subject->findAllByBeginDate(0, 1);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForZeroMinimumAndZeroMaximumThrowsException(): void
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
    public function findAllByBeginDateForMinimumSmallerThanMaximumNotThrowsException(): void
    {
        $this->subject->findAllByBeginDate(1, 2);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForNegativeMinimumSmallerThanMaximumThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findAllByBeginDate(-1, 1);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateForMinimumGreaterThanMaximumThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findAllByBeginDate(2, 1);
    }

    /**
     * @test
     * @group findAllByBeginDate
     */
    public function findAllByBeginDateNotFindsEventWithBeginDateSmallerThanMinimum(): void
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
    public function findAllByBeginDateFindsEventWithBeginDateEqualToMinimum(): void
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
    public function findAllByBeginDateFindsEventWithBeginDateBetweenMinimumAndMaximum(): void
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
    public function findAllByBeginDateFindsEventWithBeginDateEqualToMaximum(): void
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
    public function findAllByBeginDateNotFindsEventWithBeginDateGreaterThanMaximum(): void
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
    public function findAllByBeginDateCanFindEventWithZeroBeginDate(): void
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
    public function findAllByBeginDateCanFindTwoEvents(): void
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
    public function findNextUpcomingWithNoEventsThrowsEmptyQueryResultException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findNextUpcoming();
    }

    /**
     * @test
     */
    public function findNextUpcomingWithPastEventThrowsEmptyQueryResultException(): void
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
    public function findNextUpcomingWithUpcomingEventReturnsModelOfUpcomingEvent(): void
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
    public function findNextUpcomingWithTwoUpcomingEventsReturnsOnlyModelOfNextUpcomingEvent(): void
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

    // Tests concerning findForAutomaticStatusChange

    /**
     * @test
     */
    public function findForAutomaticStatusChangeForNoEventsReturnsEmptyList(): void
    {
        $result = $this->subject->findForAutomaticStatusChange();

        self::assertInstanceOf(Collection::class, $result);
        self::assertTrue($result->isEmpty());
    }

    /**
     * @test
     */
    public function findForAutomaticStatusChangeFindsPlannedEventWithAutomaticStatusChange(): void
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
    public function findForAutomaticStatusChangeNotFindsCanceledEventWithAutomaticStatusChange(): void
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
    public function findForAutomaticStatusChangeNotFindsConfirmedEventWithAutomaticStatusChange(): void
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
    public function findForAutomaticStatusChangeNotFindsPlannedEventWithoutAutomaticStatusChange(): void
    {
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['cancelled' => \Tx_Seminars_Model_Event::STATUS_PLANNED, 'automatic_confirmation_cancelation' => 0]
        );

        $result = $this->subject->findForAutomaticStatusChange();

        self::assertTrue($result->isEmpty());
    }
}
