<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Csv\CsvResponse;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller for the registration list in the BE module.
 */
class RegistrationController extends ActionController
{
    use EventStatisticsTrait;
    use PageUidTrait;
    use PermissionsTrait;

    /**
     * @var non-empty-string
     */
    private const CSV_FILENAME = 'registrations.csv';

    /**
     * @var non-empty-string
     */
    private const TABLE_NAME = 'tx_seminars_attendances';

    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    public function __construct(RegistrationRepository $registrationRepository, EventRepository $eventRepository)
    {
        $this->registrationRepository = $registrationRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param int<1, max> $eventUid
     *
     * @throws \RuntimeException
     */
    public function showForEventAction(int $eventUid): void
    {
        $event = $this->eventRepository->findOneByUidForBackend($eventUid);
        if (!($event instanceof Event)) {
            throw new \RuntimeException('Event with UID ' . $eventUid . ' not found.', 1698859637);
        }

        $this->view->assign('permissions', $this->permissions);
        $this->view->assign('pageUid', $this->getPageUid());

        $this->eventStatisticsCalculator->enrichWithStatistics($event);
        $this->view->assign('event', $event);

        if ($event instanceof EventDateInterface) {
            $regularRegistrations = $this->registrationRepository->findRegularRegistrationsByEvent($eventUid);
            $this->registrationRepository->enrichWithRawData($regularRegistrations);
            $this->view->assign('regularRegistrations', $regularRegistrations);

            $waitingListRegistrations = $this->registrationRepository->findWaitingListRegistrationsByEvent($eventUid);
            $this->registrationRepository->enrichWithRawData($waitingListRegistrations);
            $this->view->assign('waitingListRegistrations', $waitingListRegistrations);
        }
    }

    /**
     * @param int<1, max> $eventUid
     *
     * @return string|ResponseInterface
     */
    public function exportCsvForEventAction(int $eventUid)
    {
        $_GET['eventUid'] = $eventUid;

        return $this->exportCsvAction();
    }

    /**
     * @param int<1, max> $pageUid
     *
     * @return string|ResponseInterface
     */
    public function exportCsvForPageUidAction(int $pageUid)
    {
        $_GET['pid'] = $pageUid;

        return $this->exportCsvAction();
    }

    /**
     * @return string|ResponseInterface
     */
    private function exportCsvAction()
    {
        $_GET['table'] = self::TABLE_NAME;
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
     * @param positive-int $registrationUid
     * @param positive-int $eventUid
     */
    public function deleteAction(int $registrationUid, int $eventUid): void
    {
        $this->registrationRepository->deleteViaDataHandler($registrationUid);

        $this->redirect('showForEvent', 'BackEnd\\Registration', null, ['eventUid' => $eventUid]);
    }
}
