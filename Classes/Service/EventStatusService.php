<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class takes care of managing the status of events.
 */
class EventStatusService implements SingletonInterface
{
    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    protected $eventMapper;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->eventMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
    }

    /**
     * Automatically updates the status of the given event and saves it.
     *
     * If the event is not in the PLANNED status anymore or the automatic status for this event is disabled,
     * this method is a no-op.
     *
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return bool true if the status of $event has been changed, false otherwise
     */
    public function updateStatusAndSave(\Tx_Seminars_Model_Event $event): bool
    {
        if (!$event->shouldAutomaticallyConfirmOrCancel() || !$event->isPlanned()) {
            return false;
        }

        $eventWasUpdated = false;
        if ($event->hasEnoughRegistrations()) {
            $this->confirmAndSave($event);
            $eventWasUpdated = true;
        } elseif (
            $event->hasRegistrationDeadline()
            && $event->getRegistrationDeadlineAsUnixTimeStamp() < $GLOBALS['SIM_EXEC_TIME']
        ) {
            $this->cancelAndSave($event);
            $eventWasUpdated = true;
        }

        return $eventWasUpdated;
    }

    /**
     * Cancels and saves $event.
     *
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return void
     */
    public function cancelAndSave(\Tx_Seminars_Model_Event $event)
    {
        $event->cancel();
        $this->eventMapper->save($event);
    }

    /**
     * Confirms and saves $event.
     *
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return void
     */
    public function confirmAndSave(\Tx_Seminars_Model_Event $event)
    {
        $event->confirm();
        $this->eventMapper->save($event);
    }
}
