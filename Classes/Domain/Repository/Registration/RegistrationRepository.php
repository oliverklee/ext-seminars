<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository\Registration;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @extends AbstractRawDataCapableRepository<Registration>
 */
class RegistrationRepository extends AbstractRawDataCapableRepository implements DirectPersist
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist;
    use StoragePageAgnostic;

    /**
     * @return non-empty-string
     */
    protected function getTableName(): string
    {
        return 'tx_seminars_attendances';
    }

    public function existsRegistrationForEventAndUser(EventInterface $event, int $userUid): bool
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('seminar', $event),
                $query->equals('user', $userUid)
            )
        );

        return $query->count() > 0;
    }

    /**
     * @param int<0, max> $pageUid
     *
     * @return int<0, max>
     */
    public function countRegularRegistrationsByPageUid(int $pageUid): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->getTableName());

        $count = $connection->count('*', $this->getTableName(), ['pid' => $pageUid, 'registration_queue' => 0]);
        \assert($count >= 0);

        return $count;
    }

    /**
     * Sums up the regular (i.e., non-waiting-list) seats of all registrations for the given event UID.
     *
     * Registrations with 0 seats will be ignored.
     *
     * @param positive-int $eventUid
     *
     * @return positive-int|0
     */
    public function countRegularSeatsByEvent(int $eventUid): int
    {
        return $this->countSeatsByEvent($eventUid, false);
    }

    /**
     * Sums up the waiting-list seats of all registrations for the given event UID.
     *
     * Registrations with 0 seats will be ignored.
     *
     * @param positive-int $eventUid
     *
     * @return positive-int|0
     */
    public function countWaitingListSeatsByEvent(int $eventUid): int
    {
        return $this->countSeatsByEvent($eventUid, true);
    }

    /**
     * Sums up the seats of all registrations for the given event UID.
     *
     * Registrations with 0 seats will be ignored.
     *
     * @param positive-int $eventUid
     * @param bool $onWaitingList whether to count waiting list or regular registrations
     *
     * @return positive-int|0
     */
    private function countSeatsByEvent(int $eventUid, bool $onWaitingList): int
    {
        $tableName = $this->getTableName();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $query = $queryBuilder->addSelectLiteral($queryBuilder->expr()->sum('seats'))
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->eq(
                    'seminar',
                    $queryBuilder->createNamedParameter($eventUid, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'registration_queue',
                    $queryBuilder->createNamedParameter((int)$onWaitingList, Connection::PARAM_INT)
                )
            );
        if (\method_exists($query, 'executeQuery')) {
            $queryResult = $query->executeQuery();
        } else {
            $queryResult = $query->execute();
        }
        if (\method_exists($queryResult, 'fetchOne')) {
            $registrationCount = (int)$queryResult->fetchOne();
        } else {
            $registrationCount = (int)$queryResult->fetchColumn(0);
        }

        return $registrationCount;
    }

    /**
     * @param positive-int $eventUid
     *
     * @return array<int, Registration>
     */
    public function findRegularRegistrationsByEvent(int $eventUid): array
    {
        return $this->findByEventAndWaitingListStatus($eventUid, false);
    }

    /**
     * @param positive-int $eventUid
     *
     * @return array<int, Registration>
     */
    public function findWaitingListRegistrationsByEvent(int $eventUid): array
    {
        return $this->findByEventAndWaitingListStatus($eventUid, true);
    }

    /**
     * @param positive-int $eventUid
     *
     * @return array<int, Registration>
     */
    private function findByEventAndWaitingListStatus(int $eventUid, bool $onWaitingList): array
    {
        $query = $this->createQuery();
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $query->setQuerySettings($querySettings);

        $query->matching(
            $query->logicalAnd(
                $query->equals('event', $eventUid),
                $query->equals('onWaitingList', $onWaitingList)
            )
        );

        return $query->execute()->toArray();
    }
}
