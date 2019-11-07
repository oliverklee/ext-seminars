<?php
namespace OliverKlee\Seminars\Hooks;

/**
 * Hook interface to customize emails after they has been processed.
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
     */
    public function postProcessAttendeeEmail(\Tx_Oelib_Mail $mail, \Tx_Seminars_Model_Registration $registration);

    /**
     * @param \Tx_Seminars_OldModel_Registration $registration
     * @param \Tx_Oelib_Template $emailTemplate
     *
     * @return void
     */
    public function postProcessAttendeeEmailText(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Oelib_Template $emailTemplate
    );

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
