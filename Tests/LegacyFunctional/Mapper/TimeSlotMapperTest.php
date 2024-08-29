<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\PlaceMapper;
use OliverKlee\Seminars\Mapper\SpeakerMapper;
use OliverKlee\Seminars\Mapper\TimeSlotMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\Place;
use OliverKlee\Seminars\Model\Speaker;
use OliverKlee\Seminars\Model\TimeSlot;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\TimeSlotMapper
 */
final class TimeSlotMapperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var TimeSlotMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new TimeSlotMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsTimeSlotInstance(): void
    {
        self::assertInstanceOf(
            TimeSlot::class,
            $this->subject->find(1)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

        $model = $this->subject->find($uid);

        self::assertSame($uid, $model->getUid());
    }

    // Tests regarding the speakers.

    /**
     * @test
     */
    public function getSpeakersReturnsListInstance(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Collection::class, $model->getSpeakers());
    }

    /**
     * @test
     */
    public function getSpeakersWithOneSpeakerReturnsListOfSpeakers(): void
    {
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots'
        );
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_timeslots',
            $timeSlotUid,
            $speakerUid,
            'speakers'
        );

        $model = $this->subject->find($timeSlotUid);
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
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots'
        );
        $speakerUid = MapperRegistry::get(SpeakerMapper::class)->getNewGhost()->getUid();
        \assert($speakerUid > 0);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_timeslots',
            $timeSlotUid,
            $speakerUid,
            'speakers'
        );

        $model = $this->subject->find($timeSlotUid);
        self::assertSame(
            (string)$speakerUid,
            $model->getSpeakers()->getUids()
        );
    }

    // Tests regarding the place.

    /**
     * @test
     */
    public function getPlaceWithoutPlaceReturnsNull(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

        $model = $this->subject->find($uid);
        self::assertNull(
            $model->getPlace()
        );
    }

    /**
     * @test
     */
    public function getPlaceWithPlaceReturnsPlaceInstance(): void
    {
        $place = MapperRegistry::get(PlaceMapper::class)->getNewGhost();
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['place' => $place->getUid()]
        );

        $model = $this->subject->find($timeSlotUid);
        self::assertInstanceOf(Place::class, $model->getPlace());
    }

    // Tests regarding the seminar.

    /**
     * @test
     */
    public function getSeminarWithoutSeminarReturnsNull(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_timeslots');

        $model = $this->subject->find($uid);
        self::assertNull(
            $model->getSeminar()
        );
    }

    /**
     * @test
     */
    public function getSeminarWithSeminarReturnsEventInstance(): void
    {
        $seminar = MapperRegistry::get(EventMapper::class)->getNewGhost();
        $timeSlotUid = $this->testingFramework->createRecord(
            'tx_seminars_timeslots',
            ['seminar' => $seminar->getUid()]
        );

        $model = $this->subject->find($timeSlotUid);
        self::assertInstanceOf(
            Event::class,
            $model->getSeminar()
        );
    }
}
