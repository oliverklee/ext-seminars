<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository\Event;

use OliverKlee\FeUserExtraFields\Domain\Repository\DirectPersistTrait;
use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Event>
 */
class EventRepository extends Repository implements DirectPersist
{
    use DirectPersistTrait;

    private const TABLE_NAME = 'tx_seminars_seminars';

    /**
     * @return array<int, Event>
     */
    public function findSingleEventsByOwnerUid(int $ownerUid): array
    {
        if ($ownerUid <= 0) {
            return [];
        }

        $query = $this->createQuery();

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $query->setQuerySettings($querySettings);
        $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);

        $objectTypeMatcher = $query->equals('objectType', EventInterface::TYPE_SINGLE_EVENT);
        $ownerMatcher = $query->equals('ownerUid', $ownerUid);
        $query->matching($query->logicalAnd($objectTypeMatcher, $ownerMatcher));

        return $query->execute()->toArray();
    }

    /**
     * Updates the `Event.registrations` counter cache.
     *
     * @deprecated #1324 will be removed in seminars 5.0
     */
    public function updateRegistrationCounterCache(Event $event): void
    {
        $eventUid = $event->getUid();
        $registrationQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_seminars_attendances');
        $registrationCountQuery = $registrationQueryBuilder
            ->count('*')
            ->from('tx_seminars_attendances')
            ->where(
                $registrationQueryBuilder->expr()->eq(
                    'seminar',
                    $registrationQueryBuilder->createNamedParameter($eventUid, Connection::PARAM_INT)
                )
            );
        if (\method_exists($registrationCountQuery, 'executeQuery')) {
            $registrationCountQueryResult = $registrationCountQuery->executeQuery();
        } else {
            $registrationCountQueryResult = $registrationCountQuery->execute();
        }

        if (\method_exists($registrationCountQueryResult, 'fetchOne')) {
            $registrationCount = (int)$registrationCountQueryResult->fetchOne();
        } else {
            $registrationCount = (int)$registrationCountQueryResult->fetchColumn(0);
        }

        $eventQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_seminars_seminars');
        $eventUpdateQuery = $eventQueryBuilder
            ->update('tx_seminars_seminars')
            ->where(
                $eventQueryBuilder->expr()->eq(
                    'uid',
                    $eventQueryBuilder->createNamedParameter($eventUid, Connection::PARAM_INT)
                )
            )
            ->set('registrations', (string)$registrationCount);

        if (\method_exists($eventUpdateQuery, 'executeStatement')) {
            $eventUpdateQuery->executeStatement();
        } else {
            $eventUpdateQuery->execute();
        }
    }

    /**
     * Finds bookable events (i.e., single events and event dates) on the given page.
     *
     * This method works in back-end mode, i.e., it will ignore deleted records, but will find hidden or timed records.
     *
     * @param 0|positive-int $pageUid
     *
     * @return array<int, SingleEvent|EventDate>
     */
    public function findBookableEventsByPageUidInBackEndMode(int $pageUid): array
    {
        if ($pageUid <= 0) {
            return [];
        }

        $query = $this->createQuery();

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false)->setIgnoreEnableFields(true);
        $query->setQuerySettings($querySettings);
        $query->setOrderings(['begin_date' => QueryInterface::ORDER_DESCENDING]);

        $pageUidMatcher = $query->equals('pid', $pageUid);
        $objectTypeMatcher = $query->in(
            'objectType',
            [EventInterface::TYPE_SINGLE_EVENT, EventInterface::TYPE_EVENT_DATE]
        );
        $query->matching($query->logicalAnd($pageUidMatcher, $objectTypeMatcher));

        /** @var array<int, SingleEvent|EventDate> $events */
        $events = $query->execute()->toArray();

        return $events;
    }

    /**
     * Sets the raw data for the provided events.
     *
     * This is useful e.g., for creating icons in the backend.
     *
     * @param array<string|int, Event> $events
     *
     * @internal
     */
    public function enrichWithRawData(array $events): void
    {
        if ($events === []) {
            return;
        }

        /** @var array<positive-int, Event> $eventsByUid */
        $eventsByUid = [];
        foreach ($events as $event) {
            $eventsByUid[$event->getUid()] = $event;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();
        $query = $queryBuilder->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter(\array_keys($eventsByUid), Connection::PARAM_INT_ARRAY)
                )
            );
        if (\method_exists($query, 'executeQuery')) {
            $queryResult = $query->executeQuery();
        } else {
            $queryResult = $query->execute();
        }
        if (\method_exists($queryResult, 'fetchAllAssociative')) {
            $rows = $queryResult->fetchAllAssociative();
        } else {
            $rows = $queryResult->fetchAll();
        }

        foreach ($rows as $row) {
            $uid = (int)$row['uid'];
            $event = $eventsByUid[$uid] ?? null;
            if ($event instanceof Event) {
                $event->setRawData($row);
            }
        }
    }
}
