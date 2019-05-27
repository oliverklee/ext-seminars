<?php

namespace OliverKlee\Seminars\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class holds functions used to validate submitted forms in the back end.
 *
 * These functions are called from DataHandler via hooks.
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DataHandlerHook
{
    /**
     * @var string[]
     */
    private $registeredTables = ['tx_seminars_seminars', 'tx_seminars_timeslots'];

    /**
     * @var array[]
     */
    private $tceMainFieldArrays = [];

    /**
     * Handles data after everything had been written to the database.
     *
     * @return void
     */
    public function processDatamap_afterAllOperations()
    {
        $this->processTimeSlots();
        $this->processEvents();
    }

    /**
     * Builds $this->tceMainFieldArrays if the right tables were modified.
     *
     * Some of the parameters of this function are not used in this function.
     * But they are given by the hook in DataHandler.
     *
     * Note: When using the hook after INSERT operations, you will only get the
     * temporary NEW... id passed to your hook as $id, but you can easily
     * translate it to the real uid of the inserted record using the
     * $pObj->substNEWwithIDs array.
     *
     * @param string $status the status of this record (new/update)
     * @param string $table the affected table name
     * @param string|int $uid the UID of the affected record (may be 0)
     * @param string[] &$fieldArray an array of all fields that got changed (as reference)
     * @param DataHandler $pObj reference to calling object
     *
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $uid, array &$fieldArray, DataHandler $pObj)
    {
        if (!\in_array($table, $this->registeredTables, true)) {
            return;
        }

        $realUid = $this->createRealUid($status, $uid, $pObj);
        $this->tceMainFieldArrays[$table][$realUid] = $fieldArray;
    }

    /**
     * @param string $status
     * @param string|int $uid
     * @param DataHandler $pObj
     *
     * @return int
     */
    private function createRealUid($status, $uid, DataHandler $pObj)
    {
        return $status === 'new' ? (int)$pObj->substNEWwithIDs[$uid] : (int)$uid;
    }

    /**
     * Processes all time slots.
     *
     * @return void
     */
    private function processTimeSlots()
    {
        $tableName = 'tx_seminars_timeslots';
        if (!$this->hasDataForTable($tableName)) {
            return;
        }

        foreach ($this->tceMainFieldArrays[$tableName] as $uid => $_) {
            $this->processSingleTimeSlot((int)$uid);
        }
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    private function hasDataForTable($tableName)
    {
        return isset($this->tceMainFieldArrays[$tableName]) && \is_array($this->tceMainFieldArrays[$tableName]);
    }

    /**
     * Processes all events.
     *
     * @return void
     */
    private function processEvents()
    {
        $tableName = 'tx_seminars_seminars';
        if (!$this->hasDataForTable($tableName)) {
            return;
        }

        foreach ($this->tceMainFieldArrays[$tableName] as $uid => $fieldArray) {
            $this->processSingleEvent((int)$uid, $fieldArray);
        }
    }

    /**
     * Processes a single time slot.
     *
     * @param int $uid
     *
     * @return void
     */
    private function processSingleTimeSlot($uid)
    {
        /** @var \Tx_Seminars_OldModel_TimeSlot $timeSlot */
        $timeSlot = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_TimeSlot::class, $uid, false);

        if ($timeSlot->isOk()) {
            $timeSlot->saveToDatabase($timeSlot->getUpdateArray());
        }
    }

    /**
     * Processes a single event.
     *
     * @param int $uid
     * @param string[] $fieldArray an array of all fields that got changed
     *
     * @return void
     */
    private function processSingleEvent($uid, array $fieldArray)
    {
        /** @var \Tx_Seminars_OldModel_Event $event */
        $event = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_Event::class, $uid, false, true);

        if ($event->isOk()) {
            $event->saveToDatabase($event->getUpdateArray($fieldArray));
        }
    }
}
