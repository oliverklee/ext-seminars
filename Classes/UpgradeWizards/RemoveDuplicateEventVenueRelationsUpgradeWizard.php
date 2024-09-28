<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\UpgradeWizards;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Removes duplicate event-venue relations.
 *
 * @deprecated will be removed in version 7.0.0 in #3576
 *
 * @internal
 */
class RemoveDuplicateEventVenueRelationsUpgradeWizard implements UpgradeWizardInterface, RepeatableInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var non-empty-string
     */
    private const TABLE_NAME_EVENTS = 'tx_seminars_seminars';

    public function getIdentifier(): string
    {
        return 'seminars_removeDuplicateEventVenueRelations';
    }

    public function getTitle(): string
    {
        return 'Remove duplicate event-venue relations';
    }

    public function getDescription(): string
    {
        return 'Removes extraneous event-venue relations created by a bug in old seminars versions.';
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getQueryBuilder();
        $query = $queryBuilder
            ->count('*')
            ->from('tx_seminars_seminars_place_mm')
            ->groupBy('uid_foreign', 'uid_local')
            ->having($queryBuilder->expr()->gt('COUNT(*)', 1));
        if (\method_exists($query, 'executeQuery')) {
            $queryResult = $query->executeQuery();
        } else {
            $queryResult = $query->execute();
        }
        if (\method_exists($queryResult, 'fetchOne')) {
            $count = (int)$queryResult->fetchOne();
        } else {
            $count = (int)$queryResult->fetchColumn(0);
        }

        return $count > 0;
    }

    public function executeUpdate(): bool
    {
        $query = $this->getQueryBuilder()
            ->select('uid_local', 'uid_foreign')
            ->from('tx_seminars_seminars_place_mm')
            ->orderBy('uid_local');
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
        /** @var array<non-empty-string, array{uid_local: int, uid_foreign: int, count: int}> $statistics */
        $statistics = [];
        foreach ($rows as $row) {
            $uidLocal = $row['uid_local'];
            \assert(\is_int($uidLocal));
            $uidForeign = $row['uid_foreign'];
            \assert(\is_int($uidForeign));

            $key = $uidLocal . '-' . $uidForeign;
            if (!isset($statistics[$key])) {
                $statistics[$key] = ['uid_local' => $uidLocal, 'uid_foreign' => $uidForeign, 'count' => 0];
            }
            $statistics[$key]['count']++;
        }
        $duplicates = \array_filter($statistics, static function (array $item): bool {
            return $item['count'] > 1;
        });

        $numberOfDeletions = 0;
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_seminars_place_mm');
        foreach ($duplicates as $item) {
            $sql = 'DELETE FROM tx_seminars_seminars_place_mm WHERE uid_local = ? AND uid_foreign = ? LIMIT ?';
            $statement = $connection->prepare($sql);
            if (\method_exists($statement, 'executeStatement')) {
                $numberOfDeletions += $statement->executeStatement([
                    $item['uid_local'],
                    $item['uid_foreign'],
                    $item['count'] - 1,
                ]);
            } else {
                $numberOfDeletions += (int)$statement->execute([
                    $item['uid_local'],
                    $item['uid_foreign'],
                    $item['count'] - 1,
                ]);
            }
        }

        if ($this->logger instanceof LoggerAwareInterface) {
            $this->logger->info(
                \sprintf('Removed %d duplicate event-venue relations.', $numberOfDeletions)
            );
        }

        return true;
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable(self::TABLE_NAME_EVENTS);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder;
    }
}
