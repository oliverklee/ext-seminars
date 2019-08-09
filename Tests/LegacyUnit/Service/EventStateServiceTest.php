<?php
namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Service\EventStatusService;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EventStateServiceTest extends TestCase
{
    /**
     * @var EventStatusService
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Event|\PHPUnit_Framework_MockObject_MockObject
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

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

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
        $event->setData(['registrations' => new \Tx_Oelib_List(), 'automatic_confirmation_cancelation' => 1]);
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
                'registrations' => new \Tx_Oelib_List(),
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
                'registrations' => new \Tx_Oelib_List(),
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
                'registrations' => new \Tx_Oelib_List(),
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
                'registrations' => new \Tx_Oelib_List(),
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
                'registrations' => new \Tx_Oelib_List(),
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
        $event->setData(['registrations' => new \Tx_Oelib_List(), 'automatic_confirmation_cancelation' => 1]);
        $event->setStatus(\Tx_Seminars_Model_Event::STATUS_CANCELED);

        $result = $this->subject->updateStatusAndSave($event);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithoutRegistrationDeadlineReturnsFalse(
    ) {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new \Tx_Oelib_List(),
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithRegistrationDeadlineInFutureReturnsFalse(
    ) {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new \Tx_Oelib_List(),
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithRegistrationDeadlineInPastReturnsTrue(
    ) {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new \Tx_Oelib_List(),
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithRegistrationDeadlineInPastCancelsEvent(
    ) {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new \Tx_Oelib_List(),
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
    public function updateStatusAndSaveForPlannedEventWithNotEnoughRegistrationsWithRegistrationDeadlineInPastSavesEvent(
    ) {
        $event = new \Tx_Seminars_Model_Event();
        $event->setData(
            [
                'registrations' => new \Tx_Oelib_List(),
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
