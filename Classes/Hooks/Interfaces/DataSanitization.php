<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the data sanitization.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface DataSanitization extends Hook
{
    /**
     * Sanitize event data values.
     *
     * The TCE form event values need to be sanitized when storing them into the
     * DB. Check the values with additional constraints and provide the modified
     * values to use back in a returned array.
     *
     * @param int $uid
     * @param array $data
     *
     * @return mixed[] the data to change, [] for no changes
     */
    public function sanitizeEventData(int $uid, array $data): array;
}
