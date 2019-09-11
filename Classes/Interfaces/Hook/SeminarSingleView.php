<?php

namespace OliverKlee\Seminars\Interfaces\Hook;

use OliverKlee\Seminars\Interfaces\Hook;

/**
 * This interface needs to be used for hooks concerning the seminar single view.
 * It superseeds the outdated EventSingleView interface.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface SeminarSingleView extends Hook
{
    /**
     * Modifies the seminar details view.
     *
     * This function will be called for all types of seminars.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifySingleView(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Oelib_Template $template
    );
}
