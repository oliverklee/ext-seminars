<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository\Registration;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Registration>
 */
class RegistrationRepository extends Repository implements DirectPersist
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist;
    use StoragePageAgnostic;

    /**
     * @var non-empty-string
     */
    private const TABLE_NAME = 'tx_seminars_attendances';

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
     * Sums up the non-waiting-list seats of all registrations for the given event UID.
     *
     * Registrations with 0 seats will be ignored.
     *
     * @param positive-int $eventUid
     * @return positive-int|0
     */
    public function countSeatsByEvent(int $eventUid): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $query = $queryBuilder->addSelectLiteral($queryBuilder->expr()->sum('seats'))
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'seminar',
                    $queryBuilder->createNamedParameter($eventUid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'registration_queue',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
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
}
