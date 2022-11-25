<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
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

    /**
     * @var OneTimeAccountConnector
     */
    private $oneTimeAccountConnector;

    public function injectRegistrationRepository(RegistrationRepository $repository): void
    {
        $this->registrationRepository = $repository;
    }

    public function injectOneTimeAccountConnector(OneTimeAccountConnector $connector): void
    {
        $this->oneTimeAccountConnector = $connector;
    }

    private function getContext(): Context
    {
        return GeneralUtility::makeInstance(Context::class);
    }

    /**
     * Throws an exception if the event is not a bookable event type.
     *
     * We should probably replace this with a redirect to the deny action:
     * https://github.com/oliverklee/ext-seminars/issues/1978
     *
     * @internal
     * @deprecated will be removed without notice sometime after seminars 5.0, but before seminars 6.0
     *
     * @throws \InvalidArgumentException
     */
    public function assertBookableEventType(Event $event): void
    {
        if (!$event instanceof SingleEvent && !$event instanceof EventDate) {
            throw new \InvalidArgumentException('The event must be a SingleEvent or an EventDate.', 1669377348);
        }
    }

    /**
     * @return ($event is EventDateInterface ? bool : false)
     */
    public function isRegistrationPossibleAtAnyTimeAtAll(Event $event): bool
    {
        $isPossible = $event instanceof EventDateInterface && $event->isRegistrationRequired();
        if ($isPossible && $event instanceof EventDate) {
            $isPossible = $event->getTopic() instanceof EventTopic;
        }

        return $isPossible;
    }

    public function isRegistrationPossibleByDate(EventDateInterface $event): bool
    {
        $registrationDeadline = $this->getRegistrationDeadlineForEvent($event);
        if (!$registrationDeadline instanceof \DateTimeInterface) {
            return false;
        }

        $now = $this->now();
        $registrationIsEarlyEnough = $now < $registrationDeadline;
        $registrationIsLateEnough = true;

        $registrationStart = $event->getRegistrationStart();
        if ($registrationStart instanceof \DateTimeInterface) {
            $registrationIsLateEnough = $now >= $registrationStart;
        }

        return $registrationIsEarlyEnough && $registrationIsLateEnough;
    }

    private function getRegistrationDeadlineForEvent(EventDateInterface $event): ?\DateTime
    {
        if ($event->getStart() === null && $event->getRegistrationDeadline() === null) {
            return null;
        }

        $deadline = $event->getStart();
        if ($event->getRegistrationDeadline() instanceof \DateTimeInterface) {
            $deadline = $event->getRegistrationDeadline();
        }

        return $deadline;
    }

    private function now(): \DateTimeImmutable
    {
        return $this->getContext()->getPropertyFromAspect('date', 'full');
    }

    public function isFreeFromRegistrationConflicts(EventInterface $event, int $userUid): bool
    {
        return $event->isMultipleRegistrationPossible()
            || !$this->registrationRepository->existsRegistrationForEventAndUser($event, $userUid);
    }

    public function existsFrontEndUserUidInSession(): bool
    {
        return (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn')
            || \is_int($this->oneTimeAccountConnector->getOneTimeAccountUserUid());
    }

    /**
     * @return positive-int|null
     */
    public function getFrontEndUserUidFromSession(): ?int
    {
        $userUidFromLogin = (int)$this->getContext()->getPropertyFromAspect('frontend.user', 'id');
        if ($userUidFromLogin <= 0) {
            $userUidFromLogin = null;
        }

        $userUidFromOneTimeAccount = $this->oneTimeAccountConnector->getOneTimeAccountUserUid();

        return \is_int($userUidFromLogin) ? $userUidFromLogin : $userUidFromOneTimeAccount;
    }
}
