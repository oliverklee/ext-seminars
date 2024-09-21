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
use TYPO3\CMS\Core\Localization\LanguageService;
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
     * @var RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var LanguageService
     */
    private $languageService;

    public function __construct(RegistrationRepository $registrationRepository, EventRepository $eventRepository)
    {
        $this->registrationRepository = $registrationRepository;
        $this->eventRepository = $eventRepository;
        $languageService = $GLOBALS['LANG'] ?? null;
        \assert($languageService instanceof LanguageService);
        $this->languageService = $languageService;
    }

    /**
     * @param int<1, max> $eventUid
     *
     * @throws \RuntimeException
     */
    public function showForEventAction(int $eventUid): ResponseInterface
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

        return $this->htmlResponse();
    }

    /**
     * @param int<1, max> $eventUid
     */
    public function exportCsvForEventAction(int $eventUid): ResponseInterface
    {
        $_GET['eventUid'] = $eventUid;

        return $this->exportCsvAction();
    }

    /**
     * @param int<1, max> $pageUid
     */
    public function exportCsvForPageUidAction(int $pageUid): ResponseInterface
    {
        $_GET['pid'] = $pageUid;

        return $this->exportCsvAction();
    }

    private function exportCsvAction(): ResponseInterface
    {
        $csvContent = GeneralUtility::makeInstance(CsvDownloader::class)->main();

        return GeneralUtility::makeInstance(CsvResponse::class, $csvContent, self::CSV_FILENAME);
    }

    /**
     * @param positive-int $registrationUid
     * @param positive-int $eventUid
     */
    public function deleteAction(int $registrationUid, int $eventUid): void
    {
        $this->registrationRepository->deleteViaDataHandler($registrationUid);

        $message = $this->languageService
            ->sL('LLL:EXT:seminars/Resources/Private/Language/locallang.xml:backEndModule.message.registrationDeleted');
        $this->addFlashMessage($message);

        $this->redirect('showForEvent', 'BackEnd\\Registration', null, ['eventUid' => $eventUid]);
    }
}
