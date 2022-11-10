<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Service\EventStatusService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @covers \OliverKlee\Seminars\Service\EventStatusService
 */
final class EventStatusServiceTest extends TestCase
{
    /**
     * @var EventStatusService
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var EventMapper&MockObject
     */
    private $eventMapper;

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

        $this->eventMapper = $this->createMock(EventMapper::class);
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
        $event = new Event();
        $event->setData(['registrations' => new Collection(), 'automatic_confirmation_cancelation' => 1]);
        $event->setStatus(EventInterface::STATUS_CONFIRMED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsReturnsTrue(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsConfirmsEvent(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $this->subject->updateStatusAndSave($event);

        self::assertTrue($event->isConfirmed());
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsSavesEvent(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->updateStatusAndSave($event);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsWithoutAutomaticFlagReturnsFalse(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 0,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithEnoughRegistrationsWithoutAutomaticFlagKeepsEventAsPlanned(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 0,
                'attendees_min' => 1,
                'offline_attendees' => 1,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $this->subject->updateStatusAndSave($event);

        self::assertTrue($event->isPlanned());
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForAlreadyCanceledEventAndFlagSetReturnsFalse(): void
    {
        $event = new Event();
        $event->setData(['registrations' => new Collection(), 'automatic_confirmation_cancelation' => 1]);
        $event->setStatus(EventInterface::STATUS_CANCELED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithoutRegistrationDeadlineIsFalse(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => 0,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedWithNotEnoughRegistrationsWithRegistrationDeadlineInFutureIsFalse(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->future,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithRegistrationDeadlineInPastIsTrue(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->past,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithDeadlineInPastCancelsEvent(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->past,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $this->subject->updateStatusAndSave($event);

        self::assertTrue($event->isCanceled());
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithDeadlineInPastSavesEvent(): void
    {
        $event = new Event();
        $event->setData(
            [
                'registrations' => new Collection(),
                'automatic_confirmation_cancelation' => 1,
                'attendees_min' => 1,
                'offline_attendees' => 0,
                'deadline_registration' => $this->past,
            ]
        );
        $event->setStatus(EventInterface::STATUS_PLANNED);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->updateStatusAndSave($event);
    }

    /**
     * @test
     */
    public function cancelAndSaveCancelsEvent(): void
    {
        $event = new Event();
        $event->setData([]);

        $this->subject->cancelAndSave($event);

        self::assertTrue($event->isCanceled());
    }

    /**
     * @test
     */
    public function cancelAndSaveSavesEvent(): void
    {
        $event = new Event();
        $event->setData([]);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->cancelAndSave($event);
    }

    /**
     * @test
     */
    public function confirmAndSaveConfirmsEvent(): void
    {
        $event = new Event();
        $event->setData([]);

        $this->subject->confirmAndSave($event);

        self::assertTrue($event->isConfirmed());
    }

    /**
     * @test
     */
    public function confirmAndSaveSavesEvent(): void
    {
        $event = new Event();
        $event->setData([]);

        $this->eventMapper->expects(self::once())->method('save')->with($event);

        $this->subject->confirmAndSave($event);
    }
}
