<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\ViewHelpers;

use OliverKlee\Seminars\Configuration\Traits\SharedPluginConfiguration;
use OliverKlee\Seminars\Model\AbstractTimeSpan;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This class represents a view helper for rendering date ranges.
 */
class DateRangeViewHelper
{
    use SharedPluginConfiguration;

    /**
     * Gets the date.
     * Returns a localized string "will be announced" if there's no date set.
     *
     * Returns just one day if the timespan takes place on only one day.
     * Returns a date range if the timespan takes several days.
     *
     * @param AbstractTimeSpan $timeSpan the timespan to get the date for
     * @param string $dash the character or HTML entity used to separate start date and end date
     *
     * @return string the timespan date
     */
    public function render(AbstractTimeSpan $timeSpan, string $dash = '&#8211;'): string
    {
        if (!$timeSpan->hasBeginDate()) {
            return LocalizationUtility::translate('message_willBeAnnounced', 'seminars');
        }

        $beginDate = $timeSpan->getBeginDateAsUnixTimeStamp();
        $endDate = $timeSpan->getEndDateAsUnixTimeStamp();

        // Is the timespan open-ended or does it span one day only?
        if (!$timeSpan->hasEndDate() || $this->isSameDay($beginDate, $endDate)) {
            return $this->getAsDateFormatYmd($beginDate);
        }

        if ($this->getSharedConfiguration()->getAsBoolean('abbreviateDateRanges')) {
            $formattedBeginDate = $this->getAsAbbreviatedDateRange($beginDate, $endDate);
        } else {
            $formattedBeginDate = $this->getAsDateFormatYmd($beginDate);
        }

        return $formattedBeginDate . $dash . $this->getAsDateFormatYmd($endDate);
    }

    /**
     * Renders the UNIX timestamps in $beginDate and $endDate as an abbreviated date range.
     *
     * @return string the abbreviated date range
     */
    protected function getAsAbbreviatedDateRange(int $beginDate, int $endDate): string
    {
        // Are the years different? Then include the complete begin date.
        if (!$this->isSameYear($beginDate, $endDate)) {
            return $this->getAsDateFormatYmd($beginDate);
        }

        // Are the months different? Then include day and month.
        if (!$this->isSameMonth($beginDate, $endDate)) {
            return $this->getAsDateFormatMd($beginDate);
        }

        return $this->getAsDateFormatD($beginDate);
    }

    /**
     * Returns whether the UNIX timestamps in $beginDate and $endDate are on the same day.
     *
     * @return bool TRUE if $beginDate and $endDate are on the same day, otherwise FALSE
     */
    protected function isSameDay(int $beginDate, int $endDate): bool
    {
        return $this->getAsDateFormatYmd($beginDate) === $this->getAsDateFormatYmd($endDate);
    }

    /**
     * Returns whether the UNIX timestamps in $beginDate and $endDate are in the same month.
     *
     * @return bool TRUE if $beginDate and $endDate are in the same month, otherwise FALSE
     */
    protected function isSameMonth(int $beginDate, int $endDate): bool
    {
        return $this->getAsDateFormatM($beginDate) === $this->getAsDateFormatM($endDate);
    }

    /**
     * Returns whether the UNIX timestamps in $beginDate and $endDate are in the same year.
     *
     * @return bool TRUE if $beginDate and $endDate are in the same year, otherwise FALSE
     */
    protected function isSameYear(int $beginDate, int $endDate): bool
    {
        return $this->getAsDateFormatY($beginDate) === $this->getAsDateFormatY($endDate);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatYMD.
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatYMD
     */
    protected function getAsDateFormatYmd(int $timestamp): string
    {
        return \strftime($this->getDateFormat(), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatY.
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatY
     */
    protected function getAsDateFormatY(int $timestamp): string
    {
        return \strftime($this->getSharedConfiguration()->getAsString('dateFormatY'), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatM.
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatM
     */
    protected function getAsDateFormatM(int $timestamp): string
    {
        return \strftime($this->getSharedConfiguration()->getAsString('dateFormatM'), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatMD.
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatMD
     */
    protected function getAsDateFormatMd(int $timestamp): string
    {
        return \strftime($this->getSharedConfiguration()->getAsString('dateFormatMD'), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatD.
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatD
     */
    protected function getAsDateFormatD(int $timestamp): string
    {
        return \strftime($this->getSharedConfiguration()->getAsString('dateFormatD'), $timestamp);
    }
}
