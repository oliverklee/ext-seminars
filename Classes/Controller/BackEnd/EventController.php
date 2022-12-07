<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Csv\CsvResponse;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
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
     * @var Permissions
     */
    private $permissions;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventStatisticsCalculator
     */
    private $eventStatisticsCalculator;

    public function injectPermissions(Permissions $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function injectEventRepository(EventRepository $repository): void
    {
        $this->eventRepository = $repository;
    }

    public function injectEventStatisticsCalculator(EventStatisticsCalculator $calculator): void
    {
        $this->eventStatisticsCalculator = $calculator;
    }

    /**
     * This method is only public for unit testing.
     *
     * @return 0|positive-int
     */
    public function getPageUid(): int
    {
        return (int)(GeneralUtility::_GP('id') ?? 0);
    }

    public function indexAction(): void
    {
        $pageUid = $this->getPageUid();

        $this->view->assign('permissions', $this->permissions);
        $this->view->assign('pageUid', $pageUid);

        $events = $this->eventRepository->findByPageUidInBackEndMode($pageUid);
        $this->eventRepository->enrichWithRawData($events);
        foreach ($events as $event) {
            $this->eventStatisticsCalculator->enrichWithStatistics($event);
        }
        $this->view->assign('events', $events);
    }

    /**
     * @param 0|positive-int $pageUid
     *
     * @return string|ResponseInterface
     */
    public function exportCsvAction(int $pageUid)
    {
        $GLOBALS['_GET']['table'] = self::TABLE_NAME;
        $GLOBALS['_GET']['pid'] = $pageUid;

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
}
