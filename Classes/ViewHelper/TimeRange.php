<?php

/**
 * This class represents a view helper for rendering time ranges.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_ViewHelper_TimeRange
{
    /**
     * @var Tx_Oelib_Configuration
     */
    protected $configuration = null;

    /**
     * @var Tx_Oelib_Translator
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
     * Gets the time.
     * Returns a localized string "will be announced" if there's no time set (i.e. both begin time and end time are 00:00).
     * Returns only the begin time if begin time and end time are the same.
     *
     * @param Tx_Seminars_Model_AbstractTimeSpan $timeSpan the timespan to get the date for
     * @param string $dash the character or HTML entity used to separate begin time and end time
     *
     * @return string the time
     */
    public function render(Tx_Seminars_Model_AbstractTimeSpan $timeSpan, $dash = '&#8211;')
    {
        if (!$this->hasTime($timeSpan)) {
            return $this->translator->translate('message_willBeAnnounced');
        }

        $beginTime = $this->getAsTime($timeSpan->getBeginDateAsUnixTimeStamp());
        $endTime = $this->getAsTime($timeSpan->getEndDateAsUnixTimeStamp());

        $result = $beginTime;

        // Only display the end time if the event has an end date/time set
        // and the end time is not the same as the begin time.
        if ($this->hasEndTime($timeSpan) && ($beginTime !== $endTime)) {
            $result .= $dash . $endTime;
        }

        $result .= ' ' . $this->translator->translate('label_hours');

        return $result;
    }

    /**
     * Checks whether there's a time set (begin time !== 00:00).
     * If there's no date/time set, the result will be FALSE.
     *
     * @return bool TRUE if we have a begin time, FALSE otherwise
     */
    protected function hasTime(Tx_Seminars_Model_AbstractTimeSpan $timeSpan)
    {
        if (!$timeSpan->hasBeginDate()) {
            return false;
        }

        return $this->getTimeFromTimestamp($timeSpan->getBeginDateAsUnixTimeStamp()) !== '00:00';
    }

    /**
     * Checks whether there's an end time set (end time !== 00:00).
     * If there's no end date/time set, the result will be FALSE.
     *
     * @return bool TRUE if we have an end time, FALSE otherwise
     */
    protected function hasEndTime(Tx_Seminars_Model_AbstractTimeSpan $timeSpan)
    {
        if (!$timeSpan->hasEndDate()) {
            return false;
        }

        return $this->getTimeFromTimestamp($timeSpan->getEndDateAsUnixTimeStamp()) !== '00:00';
    }

    /**
     * Returns the time portion of the given UNIX timestamp in the format specified in plugin.tx_seminars.timeFormat.
     *
     * @param int $timestamp the UNIX timestamp to convert, must be >= 0
     *
     * @return string the time portion of the UNIX timestamp formatted according to the format in plugin.tx_seminars.timeFormat
     */
    protected function getAsTime($timestamp)
    {
        return strftime($this->configuration->getAsString('timeFormat'), $timestamp);
    }

    /**
     * Returns the time portion of the given UNIX timestamp.
     *
     * @param int $timestamp the UNIX timestamp to convert, must be >= 0
     *
     * @return string the time portion of the UNIX timestamp
     */
    protected function getTimeFromTimestamp($timestamp)
    {
        return strftime('%H:%M', $timestamp);
    }
}
