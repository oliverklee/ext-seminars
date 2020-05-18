<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks;

use OliverKlee\Seminars\Hooks\Interfaces\DataSanitization;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class DataHandlerHook
{
    /**
     * @var string
     */
    const TABLE_EVENTS = 'tx_seminars_seminars';

    /**
     * @var string
     */
    const TABLE_TIME_SLOTS = 'tx_seminars_timeslots';

    /**
     * @var string
     */
    const TABLE_PLACES_ASSOCIATION = 'tx_seminars_seminars_place_mm';

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
     * @param int|string $possibleUid
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
        /** @var array|bool $originalData */
        $originalData = $this->getConnectionForTable(self::TABLE_EVENTS)
            ->select(['*'], self::TABLE_EVENTS, ['uid' => $uid])->fetch();
        if (!\is_array($originalData)) {
            return;
        }

        $updatedData = $originalData;
        $this->copyPlacesFromTimeSlots($uid, $updatedData);
        $this->copyDatesFromTimeSlots($uid, $updatedData);
        $this->sanitizeEventDates($updatedData);

        /** @var HookProvider */
        $dataSanitizationHookProvider = GeneralUtility::makeInstance(HookProvider::class, DataSanitization::class);
        $updatedData = array_merge(
            $updatedData,
            $dataSanitizationHookProvider->executeHookReturningMergedArray('sanitizeEventData', $uid, $updatedData)
        );

        if ($updatedData !== $originalData) {
            $this->getConnectionForTable(self::TABLE_EVENTS)->update(self::TABLE_EVENTS, $updatedData, ['uid' => $uid]);
        }
    }

    /**
     * @param int $uid
     * @param array $data
     *
     * @return void
     */
    private function copyPlacesFromTimeSlots(int $uid, array &$data)
    {
        if ((int)$data['timeslots'] === 0) {
            return;
        }

        $this->getConnectionForTable(self::TABLE_PLACES_ASSOCIATION)
            ->delete(self::TABLE_PLACES_ASSOCIATION, ['uid_local' => $uid]);

        $timeSlots = $this->getConnectionForTable(self::TABLE_TIME_SLOTS)
            ->select(['*'], self::TABLE_TIME_SLOTS, ['seminar' => $uid])->fetchAll();

        $placesCount = 0;
        foreach ($timeSlots as $timeSlot) {
            $place = (int)$timeSlot['place'];
            if ($place === 0) {
                continue;
            }

            $this->getConnectionForTable(self::TABLE_PLACES_ASSOCIATION)
                ->insert(self::TABLE_PLACES_ASSOCIATION, ['uid_local' => $uid, 'uid_foreign' => $place]);
            $placesCount++;
        }

        $data['place'] = $placesCount;
    }

    /**
     * @param int $uid
     * @param array $data
     *
     * @return void
     */
    private function copyDatesFromTimeSlots(int $uid, array &$data)
    {
        if ((int)$data['timeslots'] === 0) {
            return;
        }

        $this->copyBeginDateFromTimeSlots($uid, $data);
        $this->copyEndDateFromTimeSlots($uid, $data);
    }

    /**
     * @param int $uid
     * @param array $data
     *
     * @return void
     */
    private function copyBeginDateFromTimeSlots(int $uid, array &$data)
    {
        $query = $this->getQueryBuilderForTable(self::TABLE_TIME_SLOTS);
        $result = $query->addSelectLiteral($query->expr()->min('begin_date', 'begin_date'))
            ->from(self::TABLE_TIME_SLOTS)
            ->where($query->expr()->eq('seminar', $uid))
            ->execute()->fetch();

        if (\is_array($result)) {
            $data['begin_date'] = (int)$result['begin_date'];
        }
    }

    /**
     * @param int $uid
     * @param array $data
     *
     * @return void
     */
    private function copyEndDateFromTimeSlots(int $uid, array &$data)
    {
        $query = $this->getQueryBuilderForTable(self::TABLE_TIME_SLOTS);
        $result = $query->addSelectLiteral($query->expr()->max('end_date', 'end_date'))
            ->from(self::TABLE_TIME_SLOTS)
            ->where($query->expr()->eq('seminar', $uid))
            ->execute()->fetch();

        if (\is_array($result)) {
            $data['end_date'] = (int)$result['end_date'];
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

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($table);
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
