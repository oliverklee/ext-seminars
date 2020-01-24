<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
     * @var string
     */
    const TABLE_EVENTS = 'tx_seminars_seminars';

    /**
     * @var DataHandler
     */
    private $dataHandler = null;

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
        $this->dataHandler = $dataHandler;
        $this->processEvents();
    }

    /**
     * Processes all events.
     *
     * @return void
     */
    private function processEvents()
    {
        /** @var array[] $map */
        $map = (array)($this->dataHandler->datamap[self::TABLE_EVENTS] ?? []);

        /** @var int|string $possibleUid */
        foreach ($map as $possibleUid => $data) {
            $uid = $this->createRealUid($possibleUid);
            $this->processSingleEvent($uid);
        }
    }

    /**
     * @param int|int $possibleUid
     *
     * @return int
     */
    private function createRealUid($possibleUid): int
    {
        return $this->isRealUid($possibleUid)
            ? (int)$possibleUid
            : (int)$this->dataHandler->substNEWwithIDs[$possibleUid];
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
     * Processes a single event.
     *
     * @param int $uid
     *
     * @return void
     */
    private function processSingleEvent(int $uid)
    {
        /** @var \Tx_Seminars_OldModel_Event $event */
        $event = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_Event::class, $uid, false, true);
        $event->saveToDatabase($event->getUpdateArray());

        /** @var array|bool $originalData */
        $originalData = $this->getConnectionForTable(self::TABLE_EVENTS)
            ->select(['*'], self::TABLE_EVENTS, ['uid' => $uid])->fetch();
        if (!\is_array($originalData)) {
            return;
        }

        $updatedData = $originalData;
        $this->sanitizeEventDates($updatedData);

        if ($updatedData !== $originalData) {
            $this->getConnectionForTable(self::TABLE_EVENTS)->update(self::TABLE_EVENTS, $updatedData, ['uid' => $uid]);
        }
    }

    /**
     * @param array $data data, might get changed
     *
     * @return void
     */
    private function sanitizeEventDates(array &$data)
    {
        $beginDate = (int)$data['begin_date'];
        $registrationDeadline = (int)$data['deadline_registration'];
        $earlyBirdDeadline = (int)$data['deadline_early_bird'];

        if ($registrationDeadline > $beginDate) {
            $registrationDeadline = 0;
            $data['deadline_registration'] = 0;
        }
        if ($earlyBirdDeadline > $beginDate || $earlyBirdDeadline > $registrationDeadline) {
            $data['deadline_early_bird'] = 0;
        }
    }

    protected function getConnectionForTable(string $table): Connection
    {
        return $this->getConnectionPool()->getConnectionForTable($table);
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
