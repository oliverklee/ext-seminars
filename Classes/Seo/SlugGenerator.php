<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Seo;

use Doctrine\DBAL\Driver\ResultStatement;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
    private const TABLE_NAME = 'tx_seminars_seminars';

    public function getPrefix(): string
    {
        return '';
    }

    /**
     * @param array{record: array{title?: string, object_type?: int, topic?: int}} $parameters
     */
    public function generateSlug(array $parameters): string
    {
        $record = $parameters['record'];

        $title = $record['title'] ?? '';
        $recordType = $record['object_type'] ?? 0;
        if ($recordType === EventInterface::TYPE_EVENT_DATE) {
            $title = '';
            $topicUid = $record['topic'] ?? 0;

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable(self::TABLE_NAME);
            $queryBuilder->getRestrictions()->removeAll();
            $result = $queryBuilder->select('title')->from(self::TABLE_NAME)
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
        }

        return (new SlugHelper(self::TABLE_NAME, 'slug', []))->sanitize($title);
    }
}
