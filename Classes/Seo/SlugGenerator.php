<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Seo;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Seo\Event\AfterSlugGeneratedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Builds the slug for event records.
 *
 * @phpstan-type DatabaseColumn string|int|float|bool|null
 * @phpstan-type DatabaseRow array<string, DatabaseColumn>
 */
class SlugGenerator implements SingletonInterface
{
    /**
     * @var non-empty-string
     */
    private const TABLE_NAME_EVENTS = 'tx_seminars_seminars';

    private EventDispatcherInterface $eventDispatcher;

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
     * @param array{
     *          record: array{
     *            uid?: numeric-string|int<0, max>,
     *            title?: string,
     *            object_type?: numeric-string|int<0, max>,
     *            topic?: numeric-string|int<0, max>
     *          }
     *        } $parameters
     */
    public function generateSlug(array $parameters): string
    {
        $record = $parameters['record'];
        $recordType = (int)($record['object_type'] ?? 0);
        \assert($recordType >= 0);
        $eventUid = (int)($record['uid'] ?? 0);
        \assert($eventUid >= 0);
        $topicUid = (int)($record['topic'] ?? 0);
        \assert($topicUid >= 0);

        $title = '';
        if ($recordType === EventInterface::TYPE_EVENT_DATE) {
            $result = $this->getQueryBuilder()->select('title')->from(self::TABLE_NAME_EVENTS)
                ->where('uid = :uid')->setParameter('uid', $topicUid)
                ->executeQuery();
            /** @var DatabaseRow|false $data */
            $data = $result->fetchAssociative();
            if (\is_array($data)) {
                $title = (string)$data['title'];
            }
        } else {
            $title = $record['title'] ?? '';
        }

        $slugCandidate = (new SlugHelper(self::TABLE_NAME_EVENTS, 'slug', []))->sanitize($title);

        $slugContext = new SlugContext($eventUid, $title, $slugCandidate);
        $event = new AfterSlugGeneratedEvent($slugContext, $this->makeSlugUnique($slugCandidate, $eventUid));
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

    /**
     * Makes the given slug unique by appending a suffix if necessary. The resulting slug is allowed to be the same as
     * the slug from the event with the given UID, practically allowing events to keep their slug.
     */
    private function makeSlugUnique(string $slugCandidate, int $eventUid): string
    {
        $slug = $slugCandidate;
        $suffix = 0;

        while ($this->countEventsWithSlug($slug, $eventUid) > 0) {
            $suffix++;
            $slug = $slugCandidate . '-' . $suffix;
        }

        return $slug;
    }

    /**
     * Counts the number of events with the given slug, excluding the event with the given UID (so that existing events
     * can keep their slug).
     */
    private function countEventsWithSlug(string $slug, int $eventUid): int
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryResult = $queryBuilder
            ->count('*')
            ->from(self::TABLE_NAME_EVENTS)
            ->andWhere(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug, Connection::PARAM_STR)),
                $queryBuilder->expr()->neq('uid', $queryBuilder->createNamedParameter($eventUid, Connection::PARAM_INT))
            )->executeQuery();

        return (int)$queryResult->fetchOne();
    }
}
