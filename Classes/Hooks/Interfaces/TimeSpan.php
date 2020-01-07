<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the date span.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface TimeSpan extends Hook
{
    /**
     * Modifies the date span string.
     *
     * The date format for the date parts are configured in TypoScript (`dateFormatYMD` etc).
     * This allows modifying the assembly of start and end date to the date span.
     * E.g. for Hungarian: '01.-03.01.2019' -> '2019.01.01.-03.'.
     *
     * @param string $dateSpan the date span produced by `AbstractTimeSpan::getDate()`
     * @param string $beginDate the formatted begin date part (`dateFormatYMD`)
     * @param string $dash the glue used by `AbstractTimeSpan::getDate()` (may be HTML encoded)
     * @param string $endDate the formatted end date part (`dateFormatYMD`)
     *
     * @return string the modified date span to use
     */
    public function modifyDateSpan(string $dateSpan, string $beginDate, string $dash, string $endDate): string;

    /**
     * Modifies the time span string.
     *
     * The time format for the time parts is configured in TypoScript (`timeFormat`).
     * This allows modifying the assembly of start and end time to the time span.
     * E.g. for Hungarian: '9:00-10:30' -> '9:00tol 10:30ban'.
     *
     * @param string $timeSpan the time span produced by `AbstractTimeSpan::getTime()`
     * @param string $beginTime the formatted begin time part (`timeFormat`)
     * @param string $dash the glue used by `AbstractTimeSpan::getTime()` (may be HTML encoded)
     * @param string $endTime the formatted end time part (`timeFormat`)
     *
     * @return string the modified time span to use
     */
    public function modifyTimeSpan(string $timeSpan, string $beginTime, string $dash, string $endTime): string;
}
