<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

/**
 * Interface for building mocks for registrations hook tests.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface RegistrationHookInterface
{
    /**
     * @return bool
     */
    public function canRegisterForSeminar(
        \Tx_Seminars_OldModel_Event $event,
        \Tx_Seminars_Model_FrontEndUser $user
    ): bool;

    /**
     * @return string
     */
    public function canRegisterForSeminarMessage(
        \Tx_Seminars_OldModel_Event $event,
        \Tx_Seminars_Model_FrontEndUser $user
    ): string;

    /**
     * @return void
     */
    public function seminarRegistrationCreated(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    );

    /**
     * @return void
     */
    public function seminarRegistrationRemoved(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    );

    /**
     * @return void
     */
    public function seminarRegistrationMovedFromQueue(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    );
}
