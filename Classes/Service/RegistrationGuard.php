<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides functions to check whether a registration for an event is possible.
 */
class RegistrationGuard implements SingletonInterface
{
    /**
     * @var RegistrationRepository
     */
    private $registrationRepository;

    public function injectRegistrationRepository(RegistrationRepository $repository): void
    {
        $this->registrationRepository = $repository;
    }

    public function isRegistrationPossibleAtAnyTimeAtAll(Event $event): bool
    {
        return $event instanceof EventDateInterface
            && $event->isRegistrationPossibleForThisClass() && $event->isRegistrationRequired();
    }

    public function isRegistrationPossibleByDate(EventDateInterface $event): bool
    {
        $registrationDeadline = $this->getRegistrationDeadlineForEvent($event);
        if (!$registrationDeadline instanceof \DateTimeImmutable) {
            return false;
        }

        $registrationIsEarlyEnough = $this->now() < $registrationDeadline;
        $registrationIsLateEnough = true;

        $registrationStart = $event->getRegistrationStart();
        if ($registrationStart instanceof \DateTimeImmutable) {
            $registrationIsLateEnough = $this->now() >= $registrationStart;
        }

        return $registrationIsEarlyEnough && $registrationIsLateEnough;
    }

    private function getRegistrationDeadlineForEvent(EventDateInterface $event): ?\DateTimeImmutable
    {
        if ($event->getStart() === null && $event->getRegistrationDeadline() === null) {
            return null;
        }

        $deadline = $event->getStart();
        if ($event->getRegistrationDeadline() instanceof \DateTimeImmutable) {
            $deadline = $event->getRegistrationDeadline();
        }

        return $deadline;
    }

    private function now(): \DateTimeImmutable
    {
        return GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'full');
    }

    public function isFreeFromRegistrationConflicts(EventInterface $event, int $userUid): bool
    {
        return $event->isMultipleRegistrationPossible()
            || !$this->registrationRepository->existsRegistrationForEventAndUser($event, $userUid);
    }
}
