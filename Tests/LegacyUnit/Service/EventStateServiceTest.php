<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Service\EventStatusService;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventStateServiceTest extends TestCase
{
    /**
     * @var EventStatusService
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Event|MockObject
     */
    private $eventMapper = null;

    /**
     * @var int
     */
    private $past = 0;

    /**
     * @var int
     */
    private $future = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        $this->past = $GLOBALS['SIM_EXEC_TIME'] - 1;
        $this->future = $GLOBALS['SIM_EXEC_TIME'] + 1;

        $this->testingFramework = new TestingFramework('tx_seminars');

        \Tx_Oelib_MapperRegistry::denyDatabaseAccess();
        \Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->eventMapper = $this->createMock(\Tx_Seminars_Mapper_Event::class);
        \Tx_Oelib_MapperRegistry::set(\Tx_Seminars_Mapper_Event::class, $this->eventMapper);

        $this->subject = new EventStatusService();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForAlreadyConfirmedEventAndFlagSetReturnsFalse()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(['registrations' => new Collection(), 'automatic_confirmation_cancelation' => 1]);
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_CONFIRMED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsReturnsTrue()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsConfirmsEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->subject->updateStatusAndSave($event);

        self::assertTrue($event->isConfirmed());
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsSavesEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->updateStatusAndSave($event);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsWithoutAutomaticFlagReturnsFalse()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 0,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsWithoutAutomaticFlagKeepsEventAsPlanned()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 0,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->subject->updateStatusAndSave($event);

        self::assertTrue($event->isPlanned());
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForAlreadyCanceledEventAndFlagSetReturnsFalse()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(['registrations' => new Collection(), 'automatic_confirmation_cancelation' => 1]);
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithoutRegistrationDeadlineIsFalse()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => 0,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedWithNotEnoughRegistrationsWithRegistrationDeadlineInFutureIsFalse()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->future,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithRegistrationDeadlineInPastIsTrue()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->past,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithDeadlineInPastCancelsEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->past,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->subject->updateStatusAndSave($event);

        self::assertTrue($event->isCanceled());
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithDeadlineInPastSavesEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->past,
            ]
        );
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_PLANNED);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->updateStatusAndSave($event);
    }

    /**
     * @test
     */
    public function cancelAndSaveCancelsEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->subject->cancelAndSave($event);

        self::assertTrue($event->isCanceled());
    }

    /**
     * @test
     */
    public function cancelAndSaveSavesEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->cancelAndSave($event);
    }

    /**
     * @test
     */
    public function confirmAndSaveConfirmsEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->subject->confirmAndSave($event);

        self::assertTrue($event->isConfirmed());
    }

    /**
     * @test
     */
    public function confirmAndSaveSavesEvent()
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->confirmAndSave($event);
    }
}
