<?php

namespace OliverKlee\Seminars\Interfaces\Hook;

use OliverKlee\Seminars\Interfaces\Hook;

/**
 * This interface needs to be used for hooks concerning the registration view.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface RegistrationView extends Hook
{
    /**
     * Modifies the heading of the registration view.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the heading output
     *
     * @return void
     */
    public function modifyRegistrationHeading(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Oelib_Template $template
    );

    /**
     * Modifies the registration form.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor
     *        the registration form renderer that will be used to create the form
     *
     * @return void
     */
    public function modifyRegistrationForm(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor
    );

    /**
     * Modifies the footer of the registration view.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the footer output
     *
     * @return void
     */
    public function modifyRegistrationFooter(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Oelib_Template $template
    );
}
