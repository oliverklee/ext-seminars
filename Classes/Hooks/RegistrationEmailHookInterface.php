<?php
namespace OliverKlee\Seminars\Hooks;

/*
 * This file is part of the seminars project.
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
 * Interface RegistrationEmailHookInterface
 */
interface RegistrationEmailHookInterface
{
    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return void
     */
    public function postProcessAttendeeEmail(\Tx_Oelib_Mail $mail, \Tx_Seminars_Model_Registration $registration);

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     *
     * @return void
     */
    public function postProcessOrganizerEmail(\Tx_Oelib_Mail $mail, \Tx_Seminars_OldModel_Registration $registration);

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason see Tx_Seminars_Service_RegistrationManager::getReasonForNotification()
     *                            for information about possible values
     *
     * @return void
     */
    public function postProcessAdditionalEmail(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_OldModel_Registration $registration,
        $emailReason = ''
    );
}
