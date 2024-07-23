<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Csv\CsvResponse;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for the event list in the BE module.
 */
class EventController extends ActionController
{
    /**
     * @var non-empty-string
     */
    private const CSV_FILENAME = 'events.csv';

    /**
     * @var non-empty-string
     */
    private const TABLE_NAME = 'tx_seminars_seminars';

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var Permissions
     */
    private $permissions;

    public function __construct(EventRepository $eventRepository, Permissions $permissions)
    {
        $this->eventRepository = $eventRepository;
        $this->permissions = $permissions;
    }

    /**
     * @param int<0, max> $pageUid
     *
     * @return string|ResponseInterface
     */
    public function exportCsvAction(int $pageUid)
    {
        $_GET['table'] = self::TABLE_NAME;
        $_GET['pid'] = $pageUid;

        $csvContent = GeneralUtility::makeInstance(CsvDownloader::class)->main();

        if (isset($this->response)) {
            // 10LTS path
            $this->response->setHeader('Content-Type', 'text/csv; header=present; charset=utf-8');
            $contentDisposition = 'attachment; filename=' . self::CSV_FILENAME;
            $this->response->setHeader('Content-Disposition', $contentDisposition);

            return $csvContent;
        }

        // 11LTS path
        return GeneralUtility::makeInstance(CsvResponse::class, $csvContent, self::CSV_FILENAME);
    }

    /**
     * @param positive-int $eventUid
     */
    public function hideAction(int $eventUid): void
    {
        if ($this->permissions->hasWriteAccessToEvents()) {
            $this->eventRepository->hide($eventUid);
        }

        $this->redirect('overview', 'BackEnd\\Module');
    }

    /**
     * @param positive-int $eventUid
     */
    public function unhideAction(int $eventUid): void
    {
        if ($this->permissions->hasWriteAccessToEvents()) {
            $this->eventRepository->unhide($eventUid);
        }

        $this->redirect('overview', 'BackEnd\\Module');
    }
}
