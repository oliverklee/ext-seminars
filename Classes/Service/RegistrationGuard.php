<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides functions to check whether a registration for an event is possible.
 */
class RegistrationGuard implements SingletonInterface
{
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
}
