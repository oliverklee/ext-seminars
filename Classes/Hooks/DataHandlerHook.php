<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * This class holds functions used to validate submitted forms in the back end.
 *
 * These functions are called from DataHandler via hooks.
 *
 * phpcs:disable PSR1.Methods.CamelCapsMethodName
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
    private $registeredTables = ['tx_seminars_seminars'];

    /**
     * @var array[]
     */
    private $tceMainFieldArrays = [];

    /**
     * Builds $this->tceMainFieldArrays if the right tables were modified.
     *
     * This method is called once per record.
     *
     * @param string $status the status of this record ("new" or "update"), unused
     * @param string $tableName
     * @param int|string $uid UID of the record (either an int UID or a string like "NEW5e0f43477dcd4869591288")
     * @param string[] $changedFields
     * @param DataHandler $dataHandler
     *
     * @return void
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $tableName,
        $uid,
        array &$changedFields,
        DataHandler $dataHandler
    ) {
        if (!\in_array($tableName, $this->registeredTables, true)) {
            return;
        }

        $realUid = $this->createRealUid($uid, $dataHandler);
        $this->tceMainFieldArrays[$tableName][$realUid] = $changedFields;
    }

    /**
     * @param string|int $uid
     * @param DataHandler $dataHandler
     *
     * @return int
     */
    private function createRealUid($uid, DataHandler $dataHandler): int
    {
        if ($this->isRealUid($uid)) {
            return (int)$uid;
        }

        return (int)$dataHandler->substNEWwithIDs[$uid];
    }

    /**
     * @param int|string $uid
     *
     * @return bool
     */
    private function isRealUid($uid): bool
    {
        return \is_int($uid) || MathUtility::canBeInterpretedAsInteger($uid);
    }

    /**
     * Handles data after everything had been written to the database.
     *
     * This method is called once for all records together.
     *
     * @param DataHandler $dataHandler
     *
     * @return void
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler)
    {
        $this->processEvents();
    }

    /**
     * Processes all events.
     *
     * @return void
     */
    private function processEvents()
    {
        $table = 'tx_seminars_seminars';
        if (!$this->hasDataForTable($table)) {
            return;
        }

        foreach ($this->tceMainFieldArrays[$table] as $uid => $fieldArray) {
            $this->processSingleEvent((int)$uid, $fieldArray);
        }
    }

    /**
     * @param string $table
     *
     * @return bool
     */
    private function hasDataForTable($table): bool
    {
        return isset($this->tceMainFieldArrays[$table]) && \is_array($this->tceMainFieldArrays[$table]);
    }

    /**
     * Processes a single event.
     *
     * @param int $uid
     * @param string[] $changedFields
     *
     * @return void
     */
    private function processSingleEvent(int $uid, array $changedFields)
    {
        /** @var \Tx_Seminars_OldModel_Event $event */
        $event = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_Event::class, $uid, false, true);

        if ($event->comesFromDatabase()) {
            $event->saveToDatabase($event->getUpdateArray($changedFields));
        }
    }
}
