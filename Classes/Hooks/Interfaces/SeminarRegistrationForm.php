<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the registration form.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface SeminarRegistrationForm extends Hook
{
    /**
     * Modifies the header of the seminar registration form.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     *
     * @return void
     */
    public function modifyRegistrationHeader(\Tx_Seminars_FrontEnd_DefaultController $controller);

    /**
     * Modifies the seminar registration form.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     * @param \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor the registration form
     *
     * @return void
     */
    public function modifyRegistrationForm(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor
    );

    /**
     * Modifies the footer of the seminar registration form.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     *
     * @return void
     */
    public function modifyRegistrationFooter(\Tx_Seminars_FrontEnd_DefaultController $controller);
}
