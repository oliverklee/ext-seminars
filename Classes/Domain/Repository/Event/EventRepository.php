<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository\Event;

use OliverKlee\FeUserExtraFields\Domain\Repository\DirectPersistTrait;
use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
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
        if (\method_exists($registrationCountQuery, 'executeStatement')) {
            $registrationCountQueryResult = $registrationCountQuery->executeStatement();
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
}
