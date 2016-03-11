<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This interface needs to be used for hooks concerning the registration process.
 *
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
