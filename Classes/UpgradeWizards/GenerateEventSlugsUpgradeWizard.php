<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\UpgradeWizards;

use Doctrine\DBAL\Driver\ResultStatement;
use OliverKlee\Seminars\Seo\SlugGenerator;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Generates slugs for events.
 */
class GenerateEventSlugsUpgradeWizard implements UpgradeWizardInterface, RepeatableInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var non-empty-string
     */
    private const TABLE_NAME_EVENTS = 'tx_seminars_seminars';

    public function getIdentifier(): string
    {
        return 'seminars_generateEventSlugs';
    }

    public function getTitle(): string
    {
        return 'Generates slugs for all events that do not have a slug yet.';
    }

    public function getDescription(): string
    {
        return 'Automatically generates the slugs for all events using their titles and UIDs.';
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
            ->from(self::TABLE_NAME_EVENTS)
            ->where($queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter('', Connection::PARAM_STR)))
            ->orWhere($queryBuilder->expr()->isNull('slug'));

        if (\method_exists($query, 'executeQuery')) {
            $queryResult = $query->executeQuery();
        } else {
            $queryResult = $query->execute();
        }
        if ($queryResult instanceof ResultStatement) {
            if (\method_exists($queryResult, 'fetchOne')) {
                $count = (int)$queryResult->fetchOne();
            } else {
                $count = (int)$queryResult->fetchColumn(0);
            }
        } else {
            $count = 0;
        }

        return $count > 0;
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = $this->getQueryBuilder();
        $query = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME_EVENTS)
            ->where($queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter('', Connection::PARAM_STR)))
            ->orWhere($queryBuilder->expr()->isNull('slug'))
            ->orderBy('uid');

        if (\method_exists($query, 'executeQuery')) {
            $queryResult = $query->executeQuery();
        } else {
            $queryResult = $query->execute();
        }

        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $slugGenerator = GeneralUtility::makeInstance(SlugGenerator::class, $eventDispatcher);
        $connection = $this->getConnectionPool()->getConnectionForTable(self::TABLE_NAME_EVENTS);
        if ($queryResult instanceof ResultStatement) {
            if (\method_exists($queryResult, 'fetchAllAssociative')) {
                /** @var array<string, string> $row */
                foreach ($queryResult->fetchAllAssociative() as $row) {
                    /** @var array{uid: int, title: string, object_type: int, topic: int} $row */
                    $slug = $slugGenerator->generateSlug(['record' => $row]);
                    $connection->update(self::TABLE_NAME_EVENTS, ['slug' => $slug], ['uid' => $row['uid']]);
                }
            } else {
                /** @var array<string, string> $row */
                foreach ($queryResult->fetchAll() as $row) {
                    /** @var array{uid: int, title: string, object_type: int, topic: int} $row */
                    $slug = $slugGenerator->generateSlug(['record' => $row]);
                    $connection->update(self::TABLE_NAME_EVENTS, ['slug' => $slug], ['uid' => $row['uid']]);
                }
            }
        }

        if ($this->logger instanceof LoggerAwareInterface) {
            $this->logger->info('All events that had no slug now have one.');
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
