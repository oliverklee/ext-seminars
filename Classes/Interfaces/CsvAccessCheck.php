<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv\Interfaces;

/**
 * This interface is used for the access check to CSV exports.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface CsvAccessCheck
{
    /**
     * Checks whether the logged-in user (if any) in the current environment has access to a CSV export.
     *
     * @return bool whether the logged-in user (if any) in the current environment has access to a CSV export.
     */
    public function hasAccess(): bool;
}
