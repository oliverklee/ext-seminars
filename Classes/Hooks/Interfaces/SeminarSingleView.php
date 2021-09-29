<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

use OliverKlee\Seminars\FrontEnd\DefaultController;

/**
 * Use this interface for hooks concerning the seminar single view.
 *
 * It supersedes the deprecated `EventSingleView` interface.
 */
interface SeminarSingleView extends Hook
{
    /**
     * Modifies the seminar details view.
     *
     * This function will be called for all types of seminars (single events, topics, and dates).
     *
     * @param DefaultController $controller the calling controller
     */
    public function modifySingleView(DefaultController $controller): void;
}
