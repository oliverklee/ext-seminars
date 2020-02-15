<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the date or the time span.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface DateTimeSpan extends Hook
{
    /**
     * Modifies the date span string.
     *
     * This allows modifying the assembly of start and end date to the date span.
     * E.g., for Hungarian: '01.-03.01.2019' -> '2019.01.01.-03.'.
     *
     * The date format for the date parts are configured in TypoScript (`dateFormatYMD` etc.).
     * Get them from `$dateTimeSpan->getConfValueString('dateFormatYMD')` etc. The event
     * dates are also retrievable:
     * `$beginDateTime = $dateTimeSpan->getBeginDateAsTimestamp();`
     * `$endDateTime = $dateTimeSpan->getEndDateAsTimestamp();`
     *
     * @param string $dateSpan the date span produced by `AbstractTimeSpan::getDate()`
     * @param \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan the date provider
     * @param string $dash the glue used by `AbstractTimeSpan::getDate()` (may be HTML encoded)
     *
     * @return string the modified date span to use
     */
    public function modifyDateSpan(
        string $dateSpan,
        \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan,
        string $dash
    ): string;

    /**
     * Modifies the time span string.
     *
     * This allows modifying the assembly of start and end time to the time span.
     * E.g., for Hungarian: '9:00-10:30' -> '9:00tol 10:30ban'.
     *
     * The time format for the time parts is configured in TypoScript (`timeFormat`).
     * Get it from `$dateTimeSpan->getConfValueString('timeFormat')`. The event
     * times are also retrievable:
     * `$beginDateTime = $dateTimeSpan->getBeginDateAsTimestamp();`
     * `$endDateTime = $dateTimeSpan->getEndDateAsTimestamp();`
     *
     * @param string $timeSpan the time span produced by `AbstractTimeSpan::getTime()`
     * @param \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan the date provider
     * @param string $dash the glue used by `AbstractTimeSpan::getTime()` (may be HTML encoded)
     *
     * @return string the modified time span to use
     */
    public function modifyTimeSpan(
        string $timeSpan,
        \Tx_Seminars_OldModel_AbstractTimeSpan $dateTimeSpan,
        string $dash
    ): string;
}
