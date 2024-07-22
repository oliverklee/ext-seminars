<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository\Event;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @extends AbstractRawDataCapableRepository<Event>
 */
class EventRepository extends AbstractRawDataCapableRepository implements DirectPersist
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist;

    /**
     * @return non-empty-string
     */
    protected function getTableName(): string
    {
        return 'tx_seminars_seminars';
    }

    /**
     * Finds a single event by UID, including hidden events.
     *
     * This method is particularly useful in the backend.
     *
     * @param int<0, max> $uid
     */
    public function findOneByUidForBackend(int $uid): ?Event
    {
        $query = $this->createQuery();
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $querySettings->setIgnoreEnableFields(true);
        $query->setQuerySettings($querySettings);

        return $query->matching($query->equals('uid', $uid))->execute()->getFirst();
    }

    /**
     * @return array<int, Event>
     */
    public function findSingleEventsByOwnerUid(int $ownerUid): array
    {
        if ($ownerUid <= 0) {
            return [];
        }

        $query = $this->createQuery();
        $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $query->setQuerySettings($querySettings);

        $objectTypeMatcher = $query->equals('objectType', EventInterface::TYPE_SINGLE_EVENT);
        $ownerMatcher = $query->equals('ownerUid', $ownerUid);
        $query->matching($query->logicalAnd($objectTypeMatcher, $ownerMatcher));

        return $query->execute()->toArray();
    }

    /**
     * Updates the `Event.registrations` counter cache.
     *
     * @deprecated #1324 will be removed in seminars 6.0
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
     * Finds events on the given page.
     *
     * This method works in back-end mode, i.e., it will ignore deleted records, but will find hidden or timed records.
     *
     * @param int<0, max> $pageUid
     *
     * @return array<int, Event>
     */
    public function findByPageUidInBackEndMode(int $pageUid): array
    {
        if ($pageUid <= 0) {
            return [];
        }

        $query = $this->createQuery();

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false)->setIgnoreEnableFields(true);
        $query->setQuerySettings($querySettings);
        $query->setOrderings(['begin_date' => QueryInterface::ORDER_DESCENDING]);

        $query->matching($query->equals('pid', $pageUid));

        return $query->execute()->toArray();
    }

    /**
     * @param positive-int $uid
     */
    public function hide(int $uid): void
    {
        $tableName = $this->getTableName();
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);

        $connection->update($tableName, ['hidden' => 1], ['uid' => $uid, 'deleted' => 0, 'hidden' => 0]);
    }
}
