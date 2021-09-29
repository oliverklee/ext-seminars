<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning data sanitization.
 */
interface DataSanitization extends Hook
{
    /**
     * Sanitizes event data values.
     *
     * The TCE form event values need to be sanitized when storing them into the
     * database. Check the values with additional constraints and provide the modified
     * values to use back in a returned array.
     *
     * @param array $data the events data as stored in database
     *
     * @return array the data to change, [] for no changes
     */
    public function sanitizeEventData(int $uid, array $data): array;
}
