<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Csv\CsvResponse;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
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

    /**
     * @var EventStatisticsCalculator
     */
    private $eventStatisticsCalculator;

    /**
     * @var LanguageService
     */
    private $languageService;

    public function __construct(
        EventRepository $eventRepository,
        Permissions $permissions,
        EventStatisticsCalculator $eventStatisticsCalculator
    ) {
        $this->eventRepository = $eventRepository;
        $this->permissions = $permissions;
        $this->eventStatisticsCalculator = $eventStatisticsCalculator;
        $languageService = $GLOBALS['LANG'] ?? null;
        \assert($languageService instanceof LanguageService);
        $this->languageService = $languageService;
    }

    /**
     * @param int<0, max> $pageUid
     *
     * @return string|ResponseInterface
     *
     * @deprecated will be removed in version 6.0.0 in #3134
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

    private function redirectToOverviewAction(): void
    {
        $this->redirect('overview', 'BackEnd\\Module');
    }

    /**
     * @param positive-int $eventUid
     */
    public function hideAction(int $eventUid): void
    {
        $this->eventRepository->hideViaDataHandler($eventUid);

        $this->redirectToOverviewAction();
    }

    /**
     * @param positive-int $eventUid
     */
    public function unhideAction(int $eventUid): void
    {
        $this->eventRepository->unhideViaDataHandler($eventUid);

        $this->redirectToOverviewAction();
    }

    /**
     * @param positive-int $eventUid
     */
    public function deleteAction(int $eventUid): void
    {
        $this->eventRepository->deleteViaDataHandler($eventUid);

        $message = $this->languageService
            ->sL('LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:backEndModule.message.eventDeleted');
        $this->addFlashMessage($message);

        $this->redirectToOverviewAction();
    }

    /**
     * @param int<0, max> $pageUid
     * @param string $searchTerm
     */
    public function searchAction(int $pageUid, string $searchTerm = ''): void
    {
        $this->view->assign('permissions', $this->permissions);
        $this->view->assign('pageUid', $pageUid);

        $events = $this->eventRepository->findBySearchTermInBackEndMode($pageUid, $searchTerm);
        $this->eventRepository->enrichWithRawData($events);
        foreach ($events as $event) {
            $this->eventStatisticsCalculator->enrichWithStatistics($event);
        }
        $this->view->assign('events', $events);

        $this->view->assign('searchTerm', \trim($searchTerm));
    }

    /**
     * @param positive-int $eventUid
     */
    public function duplicateAction(int $eventUid): void
    {
        $this->eventRepository->duplicateViaDataHandler($eventUid);

        $this->redirectToOverviewAction();
    }
}
