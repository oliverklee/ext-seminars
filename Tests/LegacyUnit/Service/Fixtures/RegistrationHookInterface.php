<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures;

use OliverKlee\Oelib\Templating\Template;

/**
 * Interface for building mocks for registrations hook tests.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface RegistrationHookInterface
{
    /**
     * @param \Tx_Seminars_OldModel_Event $event
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return bool
     */
    public function canRegisterForSeminar(
        \Tx_Seminars_OldModel_Event $event,
        \Tx_Seminars_Model_FrontEndUser $user
    ): bool;

    /**
     * @param \Tx_Seminars_OldModel_Event $event
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return string
     */
    public function canRegisterForSeminarMessage(
        \Tx_Seminars_OldModel_Event $event,
        \Tx_Seminars_Model_FrontEndUser $user
    ): string;

    /**
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return void
     */
    public function seminarRegistrationCreated(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    );

    /**
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return void
     */
    public function seminarRegistrationRemoved(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    );

    /**
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *
     * @return void
     */
    public function seminarRegistrationMovedFromQueue(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Seminars_Model_FrontEndUser $user
    );

    /**
     * @param \Tx_Oelib_Mail $email
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return void
     *
     * @deprecated
     */
    public function modifyThankYouEmail(
        \Tx_Oelib_Mail $email,
        \Tx_Seminars_Model_Registration $registration
    );

    /**
     * @return void
     *
     * @deprecated
     */
    public function modifyOrganizerNotificationEmail(
        \Tx_Seminars_OldModel_Registration $registration,
        Template $emailTemplate
    );

    /**
     * @return void
     *
     * @deprecated
     */
    public function modifyAttendeeEmailText(
        \Tx_Seminars_OldModel_Registration $registration,
        Template $emailTemplate
    );
}
