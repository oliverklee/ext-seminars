<?php
declare(strict_types = 1);

/**
 * This interface needs to be used for hooks concerning the registration process.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 *
 * @deprecated will be removed in seminars 3; use hooks from RegistrationEmailHookInterface instead
 */
interface Tx_Seminars_Interface_Hook_Registration
{
    /**
     * Modifies the registration notification e-mail to an organizer.
     *
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Oelib_Template $emailTemplate
     *
     * @return void
     *
     * @deprecated will be removed in seminars 3;
     * use RegistrationEmailHookInterface::postProcessOrganizerEmail instead
     */
    public function modifyOrganizerNotificationEmail(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Oelib_Template $emailTemplate
    );

    /**
     * Modifies the registration or unregistration e-mail to an attendee.
     *
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Oelib_Template $emailTemplate
     *
     * @return void
     *
     * @deprecated will be removed in seminars 3;
     * use RegistrationEmailHookInterface::postProcessAttendeeEmail instead
     */
    public function modifyAttendeeEmailText(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Oelib_Template $emailTemplate
    );
}
