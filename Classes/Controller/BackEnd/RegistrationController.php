<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
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
            $this->view->assign('regularRegistrations', $regularRegistrations);

            if ($event->hasWaitingList()) {
                $waitingListRegistrations = $this->registrationRepository
                    ->findWaitingListRegistrationsByEvent($eventUid);
                $this->view->assign('waitingListRegistrations', $waitingListRegistrations);
            }
        }
    }
}
