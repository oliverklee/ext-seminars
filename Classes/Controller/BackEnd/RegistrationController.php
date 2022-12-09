<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Csv\CsvResponse;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * Controller for the registration list in the BE module.
 */
class RegistrationController extends AbstractController
{
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

    public function injectRegistrationRepository(RegistrationRepository $repository): void
    {
        $this->registrationRepository = $repository;
    }

    /**
     * @Extbase\IgnoreValidation("event")
     */
    public function showForEventAction(Event $event): void
    {
        $this->view->assign('permissions', $this->permissions);
        $this->view->assign('pageUid', $this->getPageUid());

        $this->eventStatisticsCalculator->enrichWithStatistics($event);
        $this->view->assign('event', $event);

        if ($event instanceof EventDateInterface) {
            $eventUid = $event->getUid();
            $regularRegistrations = $this->registrationRepository->findRegularRegistrationsByEvent($eventUid);
            $this->registrationRepository->enrichWithRawData($regularRegistrations);
            $this->view->assign('regularRegistrations', $regularRegistrations);

            if ($event->hasWaitingList()) {
                $waitingListRegistrations = $this->registrationRepository
                    ->findWaitingListRegistrationsByEvent($eventUid);
                $this->registrationRepository->enrichWithRawData($waitingListRegistrations);
                $this->view->assign('waitingListRegistrations', $waitingListRegistrations);
            }
        }
    }

    /**
     * @return string|ResponseInterface
     */
    public function exportCsvForEventAction(Event $event)
    {
        $GLOBALS['_GET']['table'] = self::TABLE_NAME;
        $GLOBALS['_GET']['eventUid'] = $event->getUid();

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
