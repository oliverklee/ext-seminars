<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning data sanitization.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
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
     * @param int $uid
     * @param mixed[] $data the events data as stored in database
     *
     * @return mixed[] the data to change, [] for no changes
     */
    public function sanitizeEventData(int $uid, array $data): array;
}
