<?php

namespace OliverKlee\Seminars\Interfaces\Hook;

use OliverKlee\Seminars\Interfaces\Hook;

/**
 * This interface needs to be used for hooks concerning the backend registration list.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface BackEndRegistrationsListView extends Hook
{
    /**
     * Modifies the table row template content just before it is fetched by
     * getSubpart().
     *
     * @param \Tx_Seminars_OldModel_Registration $registration
     *        the registration the row is made from
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the output
     *
     * @return void
     */
    public function modifyTableRow(
        \Tx_Seminars_OldModel_Registration $registration,
        \Tx_Oelib_Template $template
    );

    /**
     * Modifies the table heading template content just before it is fetched by
     * getSubpart().
     *
     * @param \Tx_Seminars_Bag_Registration $registrationBag
     *        the registrationBag the heading is made for
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the output
     *
     * @return void
     */
    public function modifyTableHeading(
        \Tx_Seminars_Bag_Registration $registrationBag,
        \Tx_Oelib_Template $template
    );

    /**
     * Modifies the complete table template content just before it is fetched by
     * getSubpart().
     *
     * @param \Tx_Seminars_Bag_Registration $registrationBag
     *        the registrationBag the table is made for
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the output
     *
     * @return void
     */
    public function modifyTable(
        \Tx_Seminars_Bag_Registration $registrationBag,
        \Tx_Oelib_Template $template
    );
}
