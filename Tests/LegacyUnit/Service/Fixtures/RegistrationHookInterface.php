<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;

/**
 * Interface for building mocks for registrations hook tests.
 */
interface RegistrationHookInterface
{
    /**
     * @return bool
     */
    public function canRegisterForSeminar(LegacyEvent $event, \Tx_Seminars_Model_FrontEndUser $user): bool;

    /**
     * @return string
     */
    public function canRegisterForSeminarMessage(LegacyEvent $event, \Tx_Seminars_Model_FrontEndUser $user): string;

    /**
     * @return void
     */
    public function seminarRegistrationCreated(
        LegacyRegistration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    ): void;

    /**
     * @return void
     */
    public function seminarRegistrationRemoved(
        LegacyRegistration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    ): void;

    /**
     * @return void
     */
    public function seminarRegistrationMovedFromQueue(
        LegacyRegistration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    ): void;
}
