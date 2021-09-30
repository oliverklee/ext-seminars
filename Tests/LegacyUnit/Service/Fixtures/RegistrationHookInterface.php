<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;

/**
 * Interface for building mocks for registrations hook tests.
 */
interface RegistrationHookInterface
{
    public function canRegisterForSeminar(LegacyEvent $event, FrontEndUser $user): bool;

    public function canRegisterForSeminarMessage(LegacyEvent $event, FrontEndUser $user): string;

    public function seminarRegistrationCreated(LegacyRegistration $registration, FrontEndUser $user): void;

    public function seminarRegistrationRemoved(LegacyRegistration $registration, FrontEndUser $user): void;

    public function seminarRegistrationMovedFromQueue(LegacyRegistration $registration, FrontEndUser $user): void;
}
