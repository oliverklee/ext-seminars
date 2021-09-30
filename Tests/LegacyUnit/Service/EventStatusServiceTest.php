<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Service\EventStatusService;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @covers \OliverKlee\Seminars\Service\EventStatusService
 */
final class EventStatusServiceTest extends TestCase
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
     * @var EventMapper&MockObject
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

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        $this->past = $GLOBALS['SIM_EXEC_TIME'] - 1;
        $this->future = $GLOBALS['SIM_EXEC_TIME'] + 1;

        $this->testingFramework = new TestingFramework('tx_seminars');

        MapperRegistry::denyDatabaseAccess();
        MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        /** @var EventMapper&MockObject $eventMapper */
        $eventMapper = $this->createMock(EventMapper::class);
        $this->eventMapper = $eventMapper;
        MapperRegistry::set(EventMapper::class, $this->eventMapper);

        $this->subject = new EventStatusService();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function classIsSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForAlreadyConfirmedEventAndFlagSetReturnsFalse(): void
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
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsReturnsTrue(): void
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
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsConfirmsEvent(): void
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
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsSavesEvent(): void
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
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsWithoutAutomaticFlagReturnsFalse(): void
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
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsWithoutAutomaticFlagKeepsEventAsPlanned(): void
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
    public function updateStatusAndSaveForAlreadyCanceledEventAndFlagSetReturnsFalse(): void
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithoutRegistrationDeadlineIsFalse(): void
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
    public function updateStatusAndSaveForPlannedWithNotEnoughRegistrationsWithRegistrationDeadlineInFutureIsFalse(): void
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithRegistrationDeadlineInPastIsTrue(): void
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithDeadlineInPastCancelsEvent(): void
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithDeadlineInPastSavesEvent(): void
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
    public function cancelAndSaveCancelsEvent(): void
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->subject->cancelAndSave($event);

        self::assertTrue($event->isCanceled());
    }

    /**
     * @test
     */
    public function cancelAndSaveSavesEvent(): void
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->cancelAndSave($event);
    }

    /**
     * @test
     */
    public function confirmAndSaveConfirmsEvent(): void
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->subject->confirmAndSave($event);

        self::assertTrue($event->isConfirmed());
    }

    /**
     * @test
     */
    public function confirmAndSaveSavesEvent(): void
    {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData([]);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->confirmAndSave($event);
    }
}
