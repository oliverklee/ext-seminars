<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the event model.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface EventModel extends Hook
{
    /**
     * Validate TCE values.
     *
     * The TCE form values need to be validated before storing them into the DB.
     * Check the values with additional constraints and provide the validation
     * result as seen in `\Tx_Seminars_OldModel_Event::validateTceValues()` and
     * `\Tx_Seminars_OldModel_Event::getUpdateArray()`.
     *
     * The return value is an associative array using the fieldname as the key
     * string and an array for the validation result:
     * `['price_regular_early' => ['status' => false, 'newValue' => '0.00']]`
     *
     * @param \Tx_Seminars_OldModel_Event $event the event model
     * @param string[] &$fieldArray
     *        associative array containing the values entered in the TCE form
     *
     * @return array[] associative array of associative arrays containing the validation results
     */
    public function validateTceValues(\Tx_Seminars_OldModel_Event $event, array &$fieldArray): array;
}
