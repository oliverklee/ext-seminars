<?php

namespace OliverKlee\Seminars\Interfaces\Hook;

use OliverKlee\Seminars\Interfaces\Hook;

/**
 * This interface needs to be used for hooks concerning the selector widget.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface SelectorWidget extends Hook
{
    /**
     * Modifies the selector widget, just before the subpart is fetched.
     *
     * This function will be called for all types of seminar lists.
     *
     * @param \Tx_Seminars_FrontEnd_SelectorWidget $selectorWidget
     *        the calling selector widget builder
     * @param \Tx_Seminars_Bag_Event $seminarBag
     *        the seminars used to create the selector widget
     *
     * @return void
     */
    public function modifySelectorWidget(
        \Tx_Seminars_FrontEnd_SelectorWidget $selectorWidget,
        \Tx_Seminars_Bag_Event $seminarBag
    );
}
