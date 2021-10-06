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
     * Renders the time span in a human-readable way.
     *
     * Returns a localized string "will be announced" if there's no date set.
     *
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

        $isOpenEnded = !$timeSpan->hasEndDate();
        if ($isOpenEnded || $this->isSameDay($beginDate, $endDate)) {
            return $this->getAsDateFormatYmd($beginDate);
        }

        return $this->getAsDateFormatYmd($beginDate) . $dash . $this->getAsDateFormatYmd($endDate);
    }

    /**
     * Returns whether the two given timestamps are on the same day.
     */
    protected function isSameDay(int $beginDate, int $endDate): bool
    {
        return $this->getAsDateFormatYmd($beginDate) === $this->getAsDateFormatYmd($endDate);
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
}
