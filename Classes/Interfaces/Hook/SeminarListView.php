<?php

namespace OliverKlee\Seminars\Interfaces\Hook;

use OliverKlee\Seminars\Interfaces\Hook;

/**
 * This interface needs to be used for hooks concerning the seminar list view.
 * It superseeds the outdated EventListView interface.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface SeminarListView extends Hook
{
    /**
     * Modifies a list row in the seminar list.
     *
     * This function will be called for all types of seminar lists.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyListRow(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Oelib_Template $template
    );

    /**
     * Modifies a list view row in the "my seminars" list.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyMySeminarsListRow(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Oelib_Template $template
    );

    /**
     * Modifies the list view header row in the seminars list.
     *
     * This function will be called for all types of seminar lists.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Oelib_Template $template
     *        the template from which the list header is built
     *
     * @return void
     */
    public function modifyListHeader(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Oelib_Template $template
    );

    /**
     * Modifies the list view footer in the seminars list.
     *
     * This function will be called for all types of seminar lists.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller
     *        the calling controller
     * @param \Tx_Oelib_Template $template
     *        the template from which the list footer is built
     *
     * @return void
     */
    public function modifyListFooter(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Oelib_Template $template
    );
}
