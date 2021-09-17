<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

final class RegistrationBagBuilderTest extends TestCase
{
    /**
     * @var \Tx_Seminars_BagBuilder_Registration
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_BagBuilder_Registration();
        $this->subject->setTestMode();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        \Tx_Seminars_Service_RegistrationManager::purgeInstance();
    }

    ///////////////////////////////////////////
    // Tests for the basic builder functions.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function bagBuilderBuildsARegistrationBag()
    {
        self::assertInstanceOf(\Tx_Seminars_Bag_Registration::class, $this->subject->build());
    }

    /**
     * @test
     */
    public function buildReturnsBagWhichIsSortedAscendingByCrDate()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Title 2', 'crdate' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Title 1', 'crdate' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $registrationBag = $this->subject->build();
        self::assertEquals(
            2,
            $registrationBag->count()
        );

        self::assertEquals(
            'Title 1',
            $registrationBag->current()->getTitle()
        );
        self::assertEquals(
            'Title 2',
            $registrationBag->next()->getTitle()
        );
    }

    /**
     * @test
     */
    public function buildWithoutLimitReturnsBagWithAllRegistrations()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Attendance 1', 'seminar' => $eventUid1]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Attendance 2', 'seminar' => $eventUid1]
        );
        $registrationBag = $this->subject->build();

        self::assertEquals(
            2,
            $registrationBag->count()
        );
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
    public function limitToEventWithValidEventUidFindsRegistrationOfEvent()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Attendance 1', 'seminar' => $eventUid1]
        );
        $this->subject->limitToEvent($eventUid1);
        $registrationBag = $this->subject->build();

        self::assertEquals(
            'Attendance 1',
            $registrationBag->current()->getTitle()
        );
    }

    /**
     * @test
     */
    public function limitToEventWithValidEventUidIgnoresRegistrationOfOtherEvent()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Attendance 2', 'seminar' => $eventUid2]
        );
        $this->subject->limitToEvent($eventUid1);
        $registrationBag = $this->subject->build();

        self::assertTrue(
            $registrationBag->isEmpty()
        );
    }

    ////////////////////////////
    // Tests for limitToPaid()
    ////////////////////////////

    /**
     * @test
     */
    public function limitToPaidFindsPaidRegistration()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Attendance 2', 'datepaid' => $GLOBALS['SIM_EXEC_TIME']]
        );
        $this->subject->limitToPaid();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertTrue($currentModel->isPaid());
    }

    /**
     * @test
     */
    public function limitToPaidIgnoresUnpaidRegistration()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['title' => 'Attendance 1', 'datepaid' => 0]
        );
        $this->subject->limitToPaid();
        $registrationBag = $this->subject->build();

        self::assertTrue(
            $registrationBag->isEmpty()
        );
    }

    //////////////////////////////
    // Tests for limitToUnpaid()
    //////////////////////////////

    /**
     * @test
     */
    public function limitToUnpaidFindsUnpaidRegistration()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['datepaid' => 0]
        );
        $this->subject->limitToUnpaid();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertFalse($currentModel->isPaid());
    }

    /**
     * @test
     */
    public function limitToUnpaidIgnoresPaidRegistration()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['datepaid' => $GLOBALS['SIM_EXEC_TIME']]
        );
        $this->subject->limitToUnpaid();
        $registrationBag = $this->subject->build();

        self::assertTrue(
            $registrationBag->isEmpty()
        );
    }

    ////////////////////////////////////////
    // Tests for removePaymentLimitation()
    ////////////////////////////////////////

    /**
     * @test
     */
    public function removePaymentLimitationRemovesPaidLimit()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['datepaid' => 0]
        );
        $this->subject->limitToPaid();
        $this->subject->removePaymentLimitation();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertFalse($currentModel->isPaid());
    }

    /**
     * @test
     */
    public function removePaymentLimitationRemovesUnpaidLimit()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['datepaid' => $GLOBALS['SIM_EXEC_TIME']]
        );
        $this->subject->limitToUnpaid();
        $this->subject->removePaymentLimitation();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertTrue($currentModel->isPaid());
    }

    ///////////////////////////////
    // Tests for limitToOnQueue()
    ///////////////////////////////

    /**
     * @test
     */
    public function limitToOnQueueFindsRegistrationOnQueue()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['registration_queue' => 1]
        );
        $this->subject->limitToOnQueue();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertTrue($currentModel->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function limitToOnQueueIgnoresRegularRegistration()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['registration_queue' => 0]
        );
        $this->subject->limitToOnQueue();
        $registrationBag = $this->subject->build();

        self::assertTrue(
            $registrationBag->isEmpty()
        );
    }

    ///////////////////////////////
    // Tests for limitToRegular()
    ///////////////////////////////

    /**
     * @test
     */
    public function limitToRegularFindsRegularRegistration()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['registration_queue' => 0]
        );
        $this->subject->limitToRegular();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertFalse($currentModel->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function limitToRegularIgnoresRegistrationOnQueue()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['registration_queue' => 1]
        );
        $this->subject->limitToRegular();
        $registrationBag = $this->subject->build();

        self::assertTrue(
            $registrationBag->isEmpty()
        );
    }

    //////////////////////////////////////
    // Tests for removeQueueLimitation()
    //////////////////////////////////////

    /**
     * @test
     */
    public function removeQueueLimitationRemovesOnQueueLimit()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['registration_queue' => 0]
        );
        $this->subject->limitToOnQueue();
        $this->subject->removeQueueLimitation();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertFalse($currentModel->isOnRegistrationQueue());
    }

    /**
     * @test
     */
    public function removeQueueLimitationRemovesRegularLimit()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['registration_queue' => 1]
        );
        $this->subject->limitToRegular();
        $this->subject->removeQueueLimitation();
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertTrue($currentModel->isOnRegistrationQueue());
    }

    ///////////////////////////////////
    // Tests for limitToSeatsAtMost()
    ///////////////////////////////////

    /**
     * @test
     */
    public function limitToSeatsAtMostWithNegativeVacanciesThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $seats must be >= 0.'
        );

        $this->subject->limitToSeatsAtMost(-1);
    }

    /**
     * @test
     */
    public function limitToSeatsAtMostFindsRegistrationWithEqualSeats()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seats' => 2]
        );
        $this->subject->limitToSeatsAtMost(2);
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertEquals(2, $currentModel->getSeats());
    }

    /**
     * @test
     */
    public function limitToSeatsAtMostFindsRegistrationWithLessSeats()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seats' => 1]
        );
        $this->subject->limitToSeatsAtMost(2);
        /** @var \Tx_Seminars_OldModel_Registration $currentModel */
        $currentModel = $this->subject->build()->current();

        self::assertEquals(1, $currentModel->getSeats());
    }

    /**
     * @test
     */
    public function limitToSeatsAtMostIgnoresRegistrationWithMoreSeats()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seats' => 2]
        );
        $this->subject->limitToSeatsAtMost(1);
        $registrationBag = $this->subject->build();

        self::assertTrue(
            $registrationBag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToSeatsAtMostWithZeroSeatsFindsAllRegistrations()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seats' => 2]
        );
        $this->subject->limitToSeatsAtMost(1);
        $this->subject->limitToSeatsAtMost();
        $registrationBag = $this->subject->build();

        self::assertFalse(
            $registrationBag->isEmpty()
        );
    }

    //////////////////////////////
    // Tests for limitToAttendee
    //////////////////////////////

    /**
     * @test
     */
    public function limitToAttendeeWithUserFindsRegistrationsWithAttendee()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'user' => $feUserUid]
        );

        $user = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)->find($feUserUid);
        $this->subject->limitToAttendee($user);
        $bag = $this->subject->build();

        self::assertEquals(
            $registrationUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToAttendeeWithUserFindsRegistrationsWithUserAsAdditionalRegisteredPerson()
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars'
        );
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'additional_persons' => 1]
        );
        $feUserUid = $this->testingFramework->createFrontEndUser(
            '',
            ['tx_seminars_registration' => $registrationUid]
        );

        $user = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)->find($feUserUid);
        $this->subject->limitToAttendee($user);
        $bag = $this->subject->build();

        self::assertEquals(
            $registrationUid,
            $bag->current()->getUid()
        );
    }

    /**
     * @test
     */
    public function limitToAttendeeWithUserIgnoresRegistrationsWithoutAttendee()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();
        $this->testingFramework->createRecord('tx_seminars_seminars');

        $user = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)->find($feUserUid);
        $this->subject->limitToAttendee($user);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToAttendeeWithUserIgnoresRegistrationsWithOtherAttendee()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $feUserUid = $this->testingFramework->createFrontEndUser($feUserGroupUid);
        $feUserUid2 = $this->testingFramework->createFrontEndUser($feUserGroupUid);
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'user' => $feUserUid2]
        );

        $user = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)->find($feUserUid);
        $this->subject->limitToAttendee($user);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToAttendeeWithNullFindsRegistrationsWithOtherAttendee()
    {
        $feUserGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $feUserUid = $this->testingFramework->createFrontEndUser($feUserGroupUid);
        $feUserUid2 = $this->testingFramework->createFrontEndUser($feUserGroupUid);
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid, 'user' => $feUserUid2]
        );

        $user = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)->find($feUserUid);
        $this->subject->limitToAttendee($user);
        $this->subject->limitToAttendee();
        $bag = $this->subject->build();

        self::assertEquals(
            $registrationUid,
            $bag->current()->getUid()
        );
    }

    // Tests for setOrderByEventColumn()

    /**
     * @test
     */
    public function setOrderByEventColumnCanSortAscendingByEventTitle()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'test title 1']
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'test title 2']
        );
        $registrationUid1 = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid1]
        );
        $registrationUid2 = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid2]
        );

        $this->subject->setOrderByEventColumn(
            'tx_seminars_seminars.title ASC'
        );
        $bag = $this->subject->build();

        self::assertEquals(
            $bag->current()->getUid(),
            $registrationUid1
        );
        self::assertEquals(
            $bag->next()->getUid(),
            $registrationUid2
        );
    }

    /**
     * @test
     */
    public function setOrderByEventColumnCanSortDescendingByEventTitle()
    {
        $eventUid1 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'test title 1']
        );
        $eventUid2 = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'test title 2']
        );
        $registrationUid1 = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid1]
        );
        $registrationUid2 = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['seminar' => $eventUid2]
        );

        $this->subject->setOrderByEventColumn(
            'tx_seminars_seminars.title DESC'
        );
        $bag = $this->subject->build();

        self::assertEquals(
            $bag->current()->getUid(),
            $registrationUid2
        );
        self::assertEquals(
            $bag->next()->getUid(),
            $registrationUid1
        );
    }

    //////////////////////////////////////////
    // Tests concerning limitToExistingUsers
    //////////////////////////////////////////

    /**
     * @test
     */
    public function limitToExistingUsersFindsRegistrationWithExistingUser()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['user' => $this->testingFramework->createFrontEndUser()]
        );
        $this->subject->limitToExistingUsers();
        $bag = $this->subject->build();

        self::assertFalse(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToExistingUsersDoesNotFindRegistrationWithDeletedUser()
    {
        $feUserUid = $this->testingFramework->createFrontEndUser();

        $this->testingFramework->changeRecord(
            'fe_users',
            $feUserUid,
            ['deleted' => 1]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            ['user' => $feUserUid]
        );
        $this->subject->limitToExistingUsers();
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }
}
