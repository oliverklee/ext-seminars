<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Seo;

use Doctrine\DBAL\Driver\ResultStatement;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Seo\Event\AfterSlugGeneratedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Builds the slug for event records.
 *
 * @phpstan-type DatabaseColumn string|int|float|bool|null
 * @phpstan-type DatabaseRow array<string, DatabaseColumn>
 */
class SlugGenerator
{
    /**
     * @var non-empty-string
     */
    private const TABLE_NAME_EVENTS = 'tx_seminars_seminars';

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getPrefix(): string
    {
        return '';
    }

    /**
     * Generates a unique slug for the given record.
     *
     * @param array{record: array{uid?: string|int, title?: string, object_type?: string|int, topic?: string|int}} $parameters
     */
    public function generateSlug(array $parameters): string
    {
        $record = $parameters['record'];
        $recordType = (int)($record['object_type'] ?? 0);
        $eventUid = (int)($record['uid'] ?? 0);
        $topicUid = (int)($record['topic'] ?? 0);

        $title = '';
        if ($recordType === EventInterface::TYPE_EVENT_DATE) {
            $result = $this->getQueryBuilder()->select('title')->from(self::TABLE_NAME_EVENTS)
                ->where('uid = :uid')->setParameter('uid', $topicUid)
                ->execute();
            if ($result instanceof ResultStatement) {
                if (\method_exists($result, 'fetchAssociative')) {
                    /** @var DatabaseRow|false $data */
                    $data = $result->fetchAssociative();
                } else {
                    /** @var DatabaseRow|false $data */
                    $data = $result->fetch();
                }
                if (\is_array($data)) {
                    $title = (string)$data['title'];
                }
            }
        } else {
            $title = $record['title'] ?? '';
        }

        $slugCandidate = (new SlugHelper(self::TABLE_NAME_EVENTS, 'slug', []))->sanitize($title);

        $uniqueSlug = $this->makeSlugUnique($slugCandidate);
        $slugContext = new SlugContext($eventUid, $title, $uniqueSlug);
        $event = new AfterSlugGeneratedEvent($slugContext, $uniqueSlug);
        $this->eventDispatcher->dispatch($event);

        return $event->getSlug();
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

    private function makeSlugUnique(string $slugCandidate): string
    {
        $slug = $slugCandidate;
        $suffix = 0;

        while ($this->countEventsWithSlug($slug) > 0) {
            $suffix++;
            $slug = $slugCandidate . '-' . $suffix;
        }

        return $slug;
    }

    private function countEventsWithSlug(string $slug): int
    {
        $queryBuilder = $this->getQueryBuilder();
        $query = $queryBuilder
            ->count('*')
            ->from(self::TABLE_NAME_EVENTS)
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug, Connection::PARAM_STR))
            );

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

        return $count;
    }
}
