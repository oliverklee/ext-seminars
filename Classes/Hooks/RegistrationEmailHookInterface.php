<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks;

use OliverKlee\Oelib\Templating\Template;

/**
 * Hook interface to customize emails after they has been processed.
 *
 * @deprecated will be removed in seminars 4; use `Hooks\Interfaces\Hook\RegistrationEmail` instead
 *
 * @author Pascal Rinker <projects@jweiland.net>
 */
interface RegistrationEmailHookInterface
{
    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_Model_Registration $registration
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4; use `RegistrationEmail::modifyAttendeeEmail` instead
     */
    public function postProcessAttendeeEmail(\Tx_Oelib_Mail $mail, \Tx_Seminars_Model_Registration $registration);

    /**
     * @return void
     *
     * @deprecated will be removed in seminars 4; use `RegistrationEmail::modifyAttendeeEmailBody` instead
     */
    public function postProcessAttendeeEmailText(
        \Tx_Seminars_OldModel_Registration $registration,
        Template $emailTemplate
    );

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4; use `RegistrationEmail::modifyOrganizerEmail` instead
     */
    public function postProcessOrganizerEmail(\Tx_Oelib_Mail $mail, \Tx_Seminars_OldModel_Registration $registration);

    /**
     * @param \Tx_Oelib_Mail $mail
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param string $emailReason see Tx_Seminars_Service_RegistrationManager::getReasonForNotification()
     *                            for information about possible values
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4; use `RegistrationEmail::modifyAdditionalEmail` instead
     */
    public function postProcessAdditionalEmail(
        \Tx_Oelib_Mail $mail,
        \Tx_Seminars_OldModel_Registration $registration,
        $emailReason = ''
    );
}
