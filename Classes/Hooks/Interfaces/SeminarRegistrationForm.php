<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the registration form.
 */
interface SeminarRegistrationForm extends Hook
{
    /**
     * Modifies the header of the seminar registration form.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     */
    public function modifyRegistrationHeader(\Tx_Seminars_FrontEnd_DefaultController $controller): void;

    /**
     * Modifies the seminar registration form.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     */
    public function modifyRegistrationForm(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor
    ): void;

    /**
     * Modifies the footer of the seminar registration form.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     */
    public function modifyRegistrationFooter(\Tx_Seminars_FrontEnd_DefaultController $controller): void;
}
