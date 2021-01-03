<?php

declare(strict_types=1);

use OliverKlee\Oelib\Email\Mail;

/**
 * This interface needs to be used for hooks concerning the back-end module.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Seminars_Interface_Hook_BackEndModule
{
    /**
     * Modifies the general e-mail sent via the back-end module.
     *
     * Note: This hook does not get called yet. It is just here so the interface
     * is finalized.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to which the e-mail refers
     * @param Mail $eMail
     *        the e-mail that will be sent
     *
     * @return void
     */
    public function modifyGeneralEmail(\Tx_Seminars_Model_Registration $registration, Mail $eMail);

    /**
     * Modifies the confirmation e-mail sent via the back-end module.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to which the e-mail refers
     * @param Mail $eMail
     *        the e-mail that will be sent
     *
     * @return void
     */
    public function modifyConfirmEmail(\Tx_Seminars_Model_Registration $registration, Mail $eMail);

    /**
     * Modifies the cancelation e-mail sent via the back-end module.
     *
     * Note: This hook does not get called yet. It is just here so the interface
     * is finalized.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to which the e-mail refers
     * @param Mail $eMail
     *        the e-mail that will be sent
     *
     * @return void
     */
    public function modifyCancelEmail(\Tx_Seminars_Model_Registration $registration, Mail $eMail);
}
