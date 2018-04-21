<?php

/**
 * This interface needs to be used for hooks concerning the registration process.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Seminars_Interface_Hook_Registration
{
    /**
     * Modifies the registration notification e-mail to an organizer.
     *
     * @param Tx_Seminars_OldModel_Registration $registration
     * @param Tx_Oelib_Template $emailTemplate
     *
     * @return void
     */
    public function modifyOrganizerNotificationEmail(Tx_Seminars_OldModel_Registration $registration, Tx_Oelib_Template $emailTemplate);

    /**
     * Modifies the registration or unregistration e-mail to an attendee.
     *
     * @param Tx_Seminars_OldModel_Registration $registration
     * @param Tx_Oelib_Template $emailTemplate
     *
     * @return void
     */
    public function modifyAttendeeEmailText(Tx_Seminars_OldModel_Registration $registration, Tx_Oelib_Template $emailTemplate);
}
