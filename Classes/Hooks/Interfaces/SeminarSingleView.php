<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

use OliverKlee\Seminars\Hooks\Interfaces\Hook;

/**
 * Use this interface for hooks concerning the seminar single view.
 *
 * It supersedes the deprecated `EventSingleView` interface.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface SeminarSingleView extends Hook
{
    /**
     * Modifies the seminar details view.
     *
     * This function will be called for all types of seminars (single events, topics, and dates).
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     *
     * @return void
     */
    public function modifySingleView(\Tx_Seminars_FrontEnd_DefaultController $controller);
}
