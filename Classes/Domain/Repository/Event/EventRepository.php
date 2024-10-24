<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository\Event;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Repository\AbstractRawDataCapableRepository;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * @extends AbstractRawDataCapableRepository<Event>
 */
class EventRepository extends AbstractRawDataCapableRepository implements DirectPersist
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist;

    /**
     * @var list<non-empty-string>
     */
    private const FIELDS_NOT_TO_COPY = [
        'cancelation_deadline_reminder_sent',
        'cancelled',
        'date_of_last_registration_digest',
        'event_takes_place_reminder_sent',
        'offline_attendees',
        'organizers_notified_about_minimum_reached',
        // @deprecated #1324 will be removed in seminars 6.0
        'registrations',
        'slug',
        'webinar_url',
    ];

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
        $this->setQuerySettingsForBackEndWithoutStoragePageUid($query);

        return $query->matching($query->equals('uid', $uid))->execute()->getFirst();
    }

    /**
     * @param QueryInterface<Event> $query
     */
    private function setQuerySettingsForBackEndWithoutStoragePageUid(QueryInterface $query): void
    {
        $query->setQuerySettings($this->buildQuerySettingsForBackEnd()->setRespectStoragePage(false));
    }

    /**
     * @param QueryInterface<Event> $query
     * @param positive-int $storagePageUid
     */
    private function setQuerySettingsForBackEndWithStoragePageUid(QueryInterface $query, int $storagePageUid): void
    {
        $query->setQuerySettings($this->buildQuerySettingsForBackEnd()->setStoragePageIds([$storagePageUid]));
    }

    private function buildQuerySettingsForBackEnd(): QuerySettingsInterface
    {
        return GeneralUtility::makeInstance(Typo3QuerySettings::class)->setIgnoreEnableFields(true);
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
        $registrationCountQueryResult = $registrationCountQuery->executeQuery();
        $registrationCount = (int)$registrationCountQueryResult->fetchOne();

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

        $eventUpdateQuery->executeStatement();
    }

    /**
     * Finds events on the given page.
     *
     * This method works in back-end mode, i.e., it will ignore deleted records, but will find hidden or timed records.
     *
     * @param int<0, max> $pageUid
     *
     * @return list<Event>
     */
    public function findByPageUidInBackEndMode(int $pageUid): array
    {
        if ($pageUid <= 0) {
            return [];
        }

        $query = $this->createQuery();
        $this->setQuerySettingsForBackEndWithStoragePageUid($query, $pageUid);
        $query->setOrderings(['begin_date' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute()->toArray();
    }

    /**
     * Finds events on the given page that either have the given search term as a substring, or that have the search
     * term as UID (if the search term is an integer-like string).
     *
     * If the search term is empty, all events on the given page are returned.
     *
     * This method works in back-end mode, i.e., it will ignore deleted records, but will find hidden or timed records.
     *
     * @param int<0, max> $pageUid
     *
     * @return list<Event>
     */
    public function findBySearchTermInBackEndMode(int $pageUid, string $searchTerm): array
    {
        if ($pageUid <= 0) {
            return [];
        }

        $trimmedSearchTerm = \trim($searchTerm);
        if ($trimmedSearchTerm === '') {
            return $this->findByPageUidInBackEndMode($pageUid);
        }

        if (\ctype_digit($trimmedSearchTerm)) {
            $eventUid = (int)$trimmedSearchTerm;
            \assert($eventUid >= 0);
            return $this->findByEventUidAndPageUidInBackEndMode($eventUid, $pageUid);
        }

        return $this->findBySearchTermAndPageUidInBackEndMode($trimmedSearchTerm, $pageUid);
    }

    /**
     * Finds the event with the given UID (if it exists) on the given page.
     *
     * This method works in back-end mode, i.e., it will ignore deleted records, but will find hidden or timed records.
     *
     * @param int<0, max> $eventUid
     * @param int<1, max> $pageUid
     *
     * @return list<Event>
     */
    private function findByEventUidAndPageUidInBackEndMode(int $eventUid, int $pageUid): array
    {
        $query = $this->createQuery();
        $this->setQuerySettingsForBackEndWithStoragePageUid($query, $pageUid);

        $query->matching($query->equals('uid', $eventUid));

        return $query->execute()->toArray();
    }

    /**
     * Finds events on the given page that have the given search term as a substring.
     *
     * This method works in back-end mode, i.e., it will ignore deleted records, but will find hidden or timed records.
     *
     * @param non-empty-string $searchTerm
     * @param int<1, max> $pageUid
     *
     * @return list<Event>
     */
    private function findBySearchTermAndPageUidInBackEndMode(string $searchTerm, int $pageUid): array
    {
        $query = $this->createQuery();
        $this->setQuerySettingsForBackEndWithStoragePageUid($query, $pageUid);

        $query->matching($query->like('title', '%' . \addcslashes($searchTerm, '_%') . '%'));
        $query->setOrderings(['begin_date' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute()->toArray();
    }

    /**
     * Hides the event with the given UID.
     *
     * Note: As this method uses the `DataHandler`, it can only be used within a backend context.
     *
     * The `DataHandler` will also take care of checking the permissions of the logged-in BE user.
     *
     * @param positive-int $uid
     */
    public function hideViaDataHandler(int $uid): void
    {
        $this->updateEventViaDataHandler($uid, ['hidden' => 1]);
    }

    /**
     * Unhides the event with the given UID.
     *
     * Note: As this method uses the `DataHandler`, it can only be used within a backend context.
     *
     * The `DataHandler` will also take care of checking the permissions of the logged-in BE user.
     *
     * @param positive-int $uid
     */
    public function unhideViaDataHandler(int $uid): void
    {
        $this->updateEventViaDataHandler($uid, ['hidden' => 0]);
    }

    /**
     * @param positive-int $uid
     * @param array<string, int> $eventData
     */
    private function updateEventViaDataHandler(int $uid, array $eventData): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $data = [
            $this->getTableName() => [
                $uid => $eventData,
            ],
        ];
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
    }

    /**
     * Deletes the event with the given UID.
     *
     * Note: As this method uses the `DataHandler`, it can only be used within a backend context.
     *
     * The `DataHandler` will also take care of checking the permissions of the logged-in BE user.
     *
     * @param positive-int $uid
     */
    public function deleteViaDataHandler(int $uid): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start(
            [],
            [
                $this->getTableName() => [
                    $uid => ['delete' => 1],
                ],
            ]
        );
        $dataHandler->process_cmdmap();
    }

    /**
     * Duplicates the event with the given UID.
     *
     * Note: As this method uses the `DataHandler`, it can only be used within a backend context.
     *
     * The `DataHandler` will also take care of checking the permissions of the logged-in BE user.
     *
     * @param positive-int $uid
     */
    public function duplicateViaDataHandler(int $uid): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], []);
        $excludeFields = \implode(',', self::FIELDS_NOT_TO_COPY);
        // Note: We're not calling `$dataHandler->process_cmdmap();` here
        // because that would not allow us to provide the exclude fields.
        $dataHandler->copyRecord($this->getTableName(), $uid, -$uid, true, [], $excludeFields);
    }
}
