<?php

declare(strict_types=1);

/**
 * This class represents a view helper for rendering date ranges.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_ViewHelper_DateRange
{
    /**
     * @var \Tx_Oelib_Configuration
     */
    protected $configuration = null;

    /**
     * @var \Tx_Oelib_Translator
     */
    protected $translator = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->configuration = \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars');
        $this->translator = \Tx_Oelib_TranslatorRegistry::get('seminars');
    }

    /**
     * Gets the date.
     * Returns a localized string "will be announced" if there's no date set.
     *
     * Returns just one day if the timespan takes place on only one day.
     * Returns a date range if the timespan takes several days.
     *
     * @param \Tx_Seminars_Model_AbstractTimeSpan $timeSpan the timespan to get the date for
     * @param string $dash the character or HTML entity used to separate start date and end date
     *
     * @return string the timespan date
     */
    public function render(\Tx_Seminars_Model_AbstractTimeSpan $timeSpan, $dash = '&#8211;'): string
    {
        if (!$timeSpan->hasBeginDate()) {
            return $this->translator->translate('message_willBeAnnounced');
        }

        $beginDate = $timeSpan->getBeginDateAsUnixTimeStamp();
        $endDate = $timeSpan->getEndDateAsUnixTimeStamp();

        // Is the timespan open-ended or does it span one day only?
        if (!$timeSpan->hasEndDate() || $this->isSameDay($beginDate, $endDate)) {
            return $this->getAsDateFormatYmd($beginDate);
        }

        if ($this->configuration->getAsBoolean('abbreviateDateRanges')) {
            $formattedBeginDate = $this->getAsAbbreviatedDateRange($beginDate, $endDate);
        } else {
            $formattedBeginDate = $this->getAsDateFormatYmd($beginDate);
        }

        return $formattedBeginDate . $dash . $this->getAsDateFormatYmd($endDate);
    }

    /**
     * Renders the UNIX timestamps in $beginDate and $endDate as an abbreviated date range.
     *
     * @param int $beginDate
     * @param int $endDate
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
     * @param int $beginDate
     * @param int $endDate
     *
     * @return bool TRUE if $beginDate and $endDate are on the same day, otherwise FALSE
     */
    protected function isSameDay($beginDate, $endDate): bool
    {
        return $this->getAsDateFormatYmd($beginDate) === $this->getAsDateFormatYmd($endDate);
    }

    /**
     * Returns whether the UNIX timestamps in $beginDate and $endDate are in the same month.
     *
     * @param int $beginDate
     * @param int $endDate
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
     * @param int $beginDate
     * @param int $endDate
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
     * @param int $timestamp the UNIX timestamp to render
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatYMD
     */
    protected function getAsDateFormatYmd($timestamp): string
    {
        return strftime($this->configuration->getAsString('dateFormatYMD'), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatY.
     *
     * @param int $timestamp the UNIX timestamp to render
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatY
     */
    protected function getAsDateFormatY(int $timestamp): string
    {
        return strftime($this->configuration->getAsString('dateFormatY'), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatM.
     *
     * @param int $timestamp the UNIX timestamp to render
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatM
     */
    protected function getAsDateFormatM(int $timestamp): string
    {
        return strftime($this->configuration->getAsString('dateFormatM'), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatMD.
     *
     * @param int $timestamp the UNIX timestamp to render
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatMD
     */
    protected function getAsDateFormatMd(int $timestamp): string
    {
        return strftime($this->configuration->getAsString('dateFormatMD'), $timestamp);
    }

    /**
     * Renders a UNIX timestamp in the strftime format specified in plugin.tx_seminars_seminars.dateFormatD.
     *
     * @param int $timestamp the UNIX timestamp to render
     *
     * @return string the UNIX timestamp rendered using the strftime format in plugin.tx_seminars_seminars.dateFormatD
     */
    protected function getAsDateFormatD(int $timestamp): string
    {
        return strftime($this->configuration->getAsString('dateFormatD'), $timestamp);
    }
}
