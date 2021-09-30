<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

use OliverKlee\Seminars\Bag\EventBag;

/**
 * Use this interface for hooks concerning the seminar selector widget.
 */
interface SeminarSelectorWidget extends Hook
{
    /**
     * Modifies the seminar widget, just before the subpart is fetched.
     *
     * This function will be called for all types of seminar lists, if `displaySearchFormFields` is configured for it.
     *
     * @param EventBag $seminarBag the seminars used to create the selector widget
     */
    public function modifySelectorWidget(
        \Tx_Seminars_FrontEnd_SelectorWidget $selectorWidget,
        EventBag $seminarBag
    ): void;
}
