<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper as OelibFrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\FoodMapper;
use OliverKlee\Seminars\Mapper\LodgingMapper;
use OliverKlee\Seminars\Mapper\OrganizerMapper;
use OliverKlee\Seminars\Mapper\PlaceMapper;
use OliverKlee\Seminars\Mapper\SpeakerMapper;
use OliverKlee\Seminars\Model\Food;
use OliverKlee\Seminars\Model\Lodging;
use OliverKlee\Seminars\Model\Organizer;
use OliverKlee\Seminars\Model\Place;
use OliverKlee\Seminars\Model\Speaker;
use OliverKlee\Seminars\Model\TimeSlot;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\EventMapper
 */
final class EventMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var EventMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->subject = MapperRegistry::get(EventMapper::class);
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        parent::tearDown();
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
            TimeSlot::class,
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
        self::assertSame(
            (string)$timeSlotUid,
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
        $placeUid = MapperRegistry::get(PlaceMapper::class)->getNewGhost()->getUid();
        \assert($placeUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $placeUid,
            'place'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Place::class, $model->getPlaces()->first());
    }

    /**
     * @test
     */
    public function getPlacesWithOnePlaceReturnsOnePlace(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $placeUid = MapperRegistry::get(PlaceMapper::class)->getNewGhost()->getUid();
        \assert($placeUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $placeUid,
            'place'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$placeUid,
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
        $lodgingUid = MapperRegistry::get(LodgingMapper::class)->getNewGhost()->getUid();
        \assert($lodgingUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $lodgingUid,
            'lodgings'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Lodging::class, $model->getLodgings()->first());
    }

    /**
     * @test
     */
    public function getLodgingsWithOneLodgingReturnsOneLodging(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $lodgingUid = MapperRegistry::get(LodgingMapper::class)->getNewGhost()->getUid();
        \assert($lodgingUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $lodgingUid,
            'lodgings'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$lodgingUid,
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
        $foodUid = MapperRegistry::get(FoodMapper::class)->getNewGhost()->getUid();
        \assert($foodUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $foodUid,
            'foods'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Food::class, $model->getFoods()->first());
    }

    /**
     * @test
     */
    public function getFoodsWithOneFoodReturnsOneFood(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $foodUid = MapperRegistry::get(FoodMapper::class)->getNewGhost()->getUid();
        \assert($foodUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $foodUid,
            'foods'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$foodUid,
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
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'speakers'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            Speaker::class,
            $model->getSpeakers()->first()
        );
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsOneSpeaker(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'speakers'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$speakerUid,
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
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'partners'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            Speaker::class,
            $model->getPartners()->first()
        );
    }

    /**
     * @test
     */
    public function getPartnersWithOnePartnerReturnsOnePartner(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'partners'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$speakerUid,
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
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'tutors'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            Speaker::class,
            $model->getTutors()->first()
        );
    }

    /**
     * @test
     */
    public function getTutorsWithOneTutorReturnsOneTutor(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'tutors'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$speakerUid,
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
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'leaders'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            Speaker::class,
            $model->getLeaders()->first()
        );
    }

    /**
     * @test
     */
    public function getLeadersWithOneLeaderReturnsOneLeader(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $speakerUid,
            'leaders'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$speakerUid,
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
        $organizerUid = MapperRegistry::get(OrganizerMapper::class)->getNewGhost()->getUid();
        \assert($organizerUid > 0);
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $uid,
            $organizerUid
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Organizer::class, $model->getOrganizers()->first());
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
        $organizerUid = MapperRegistry::get(OrganizerMapper::class)->getNewGhost()->getUid();
        \assert($organizerUid > 0);
        $this->testingFramework->createRelation(
            'tx_seminars_seminars_organizers_mm',
            $uid,
            $organizerUid
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$organizerUid,
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
        $organizerUid = MapperRegistry::get(OrganizerMapper::class)->getNewGhost()->getUid();
        \assert($organizerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $organizerUid,
            'organizing_partners'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Organizer::class, $model->getOrganizingPartners()->first());
    }

    /**
     * @test
     */
    public function getOrganizingPartnersWithOneOrganizingPartnersReturnsOneOrganizingPartner(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $organizerUid = MapperRegistry::get(OrganizerMapper::class)->getNewGhost()->getUid();
        \assert($organizerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $organizerUid,
            'organizing_partners'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$organizerUid,
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
        $frontEndUserUid = MapperRegistry::get(OelibFrontEndUserMapper::class)->getNewGhost()->getUid();
        \assert($frontEndUserUid > 0);
        $testingModel = $this->subject->getLoadedTestingModel(['owner_feuser' => $frontEndUserUid]);

        self::assertInstanceOf(OelibFrontEndUser::class, $testingModel->getOwner());
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
        $frontEndUserUid = MapperRegistry::get(OelibFrontEndUserMapper::class)->getNewGhost()->getUid();
        \assert($frontEndUserUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $frontEndUserUid,
            'vips'
        );

        $model = $this->subject->find($uid);
        self::assertInstanceOf(OelibFrontEndUser::class, $model->getEventManagers()->first());
    }

    /**
     * @test
     */
    public function getEventManagersWithOneEventManagerReturnsOneEventManager(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $frontEndUserUid = MapperRegistry::get(OelibFrontEndUserMapper::class)->getNewGhost()->getUid();
        \assert($frontEndUserUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_seminars',
            $uid,
            $frontEndUserUid,
            'vips'
        );

        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$frontEndUserUid,
            $model->getEventManagers()->getUids()
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
            ['cancelled' => EventInterface::STATUS_PLANNED, 'automatic_confirmation_cancelation' => 1]
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
            ['cancelled' => EventInterface::STATUS_CANCELED, 'automatic_confirmation_cancelation' => 1]
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
            ['cancelled' => EventInterface::STATUS_CONFIRMED, 'automatic_confirmation_cancelation' => 1]
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
            ['cancelled' => EventInterface::STATUS_PLANNED, 'automatic_confirmation_cancelation' => 0]
        );

        $result = $this->subject->findForAutomaticStatusChange();

        self::assertTrue($result->isEmpty());
    }
}
