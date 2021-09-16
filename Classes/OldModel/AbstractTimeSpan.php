<?php

declare(strict_types=1);

use OliverKlee\Oelib\Interfaces\ConfigurationCheckable;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\DateTimeSpan;
use OliverKlee\Seminars\OldModel\AbstractModel;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class offers timespan-related methods for the time slot and seminar classes.
 */
abstract class Tx_Seminars_OldModel_AbstractTimeSpan extends AbstractModel implements ConfigurationCheckable
{
    /**
     * @var HookProvider|null
     */
    protected $dateTimeSpanHookProvider = null;

    /**
     * Gets the begin date.
     *
     * @return string the begin date (or the localized string "will be announced" if no begin date is set)
     */
    public function getBeginDate(): string
    {
        return $this->hasBeginDate()
            ? \strftime($this->getConfValueString('dateFormatYMD'), $this->getBeginDateAsTimestamp())
            : $this->translate('message_willBeAnnounced');
    }

    /**
     * Checks whether there's a begin date set.
     *
     * @return bool true if we have a begin date, false otherwise
     */
    public function hasBeginDate(): bool
    {
        return $this->getBeginDateAsTimestamp() > 0;
    }

    /**
     * Gets the end date.
     *
     * @return string the end date (or the localized string "will be
     *                announced" if no end date is set)
     */
    public function getEndDate(): string
    {
        return $this->hasEndDate()
            ? \strftime($this->getConfValueString('dateFormatYMD'), $this->getEndDateAsTimestamp())
            : $this->translate('message_willBeAnnounced');
    }

    /**
     * Checks whether there's an end date set.
     *
     * @return bool true if we have an end date, false otherwise
     */
    public function hasEndDate(): bool
    {
        return $this->getEndDateAsTimestamp() > 0;
    }

    /**
     * Checks whether there's a begin date set, and whether this has already
     * passed.
     *
     * @return bool true if the time-span has a begin date set that lies in
     *                 the future (time-span has not started yet), false otherwise
     */
    public function hasStarted(): bool
    {
        return $this->hasBeginDate() && (int)$GLOBALS['SIM_EXEC_TIME'] >= $this->getBeginDateAsTimestamp();
    }

    /**
     * Gets the date.
     * Returns a localized string "will be announced" if there's no date set.
     *
     * Returns just one day if the timespan takes place on only one day.
     * Returns a date range if the timespan takes several days.
     *
     * @param string $dash the character or HTML entity used to separate start date and end date
     *
     * @return string the seminar date
     */
    public function getDate(string $dash = '&#8211;'): string
    {
        if ($this->hasDate()) {
            $beginDate = $this->getBeginDateAsTimestamp();
            $endDate = $this->getEndDateAsTimestamp();

            $beginDateDay = \strftime($this->getConfValueString('dateFormatYMD'), $beginDate);
            $endDateDay = \strftime($this->getConfValueString('dateFormatYMD'), $endDate);

            // Does the workshop span only one day (or is open-ended)?
            if ($beginDateDay === $endDateDay || !$this->hasEndDate()) {
                $result = $beginDateDay;
            } else {
                if ($this->getConfValueBoolean('abbreviateDateRanges')) {
                    // Are the years different? Then includes the complete begin date.
                    if (
                        \strftime($this->getConfValueString('dateFormatY'), $beginDate)
                        !== \strftime($this->getConfValueString('dateFormatY'), $endDate)
                    ) {
                        $result = $beginDateDay;
                    } elseif (
                        \strftime($this->getConfValueString('dateFormatM'), $beginDate)
                        !== \strftime($this->getConfValueString('dateFormatM'), $endDate)
                    ) {
                        $result = \strftime($this->getConfValueString('dateFormatMD'), $beginDate);
                    } else {
                        $result = \strftime($this->getConfValueString('dateFormatD'), $beginDate);
                    }
                } else {
                    $result = $beginDateDay;
                }
                $result .= $dash . $endDateDay;
                $result = $this->getDateTimeSpanHookProvider()->executeHookReturningModifiedValue(
                    'modifyDateSpan',
                    $result,
                    $this,
                    $dash
                );
            }
        } else {
            $result = $this->translate('message_willBeAnnounced');
        }

        return (string)$result;
    }

    /**
     * Checks whether there's a (begin) date set.
     * If there's an end date but no begin date,
     * this function still will return false.
     *
     * @return bool
     */
    public function hasDate(): bool
    {
        return $this->hasRecordPropertyInteger('begin_date');
    }

    /**
     * Gets the time.
     * Returns a localized string "will be announced" if there's no time set
     * (i.e. both begin time and end time are 00:00).
     * Returns only the begin time if begin time and end time are the same.
     *
     * @param string $dash the character or HTML entity used to separate begin time and end time
     *
     * @return string the time
     */
    public function getTime($dash = '&#8211;'): string
    {
        if (!$this->hasTime()) {
            return $this->translate('message_willBeAnnounced');
        }

        $timeFormat = $this->getConfValueString('timeFormat');
        $beginTime = \strftime($timeFormat, $this->getBeginDateAsTimestamp());
        $endTime = \strftime($timeFormat, $this->getEndDateAsTimestamp());

        $result = $beginTime;

        // Only display the end time if the event has an end date/time set
        // and the end time is not the same as the begin time.
        if (($beginTime !== $endTime) && $this->hasEndTime()) {
            $result .= $dash . $endTime;
            $result = $this->getDateTimeSpanHookProvider()->executeHookReturningModifiedValue(
                'modifyTimeSpan',
                $result,
                $this,
                $dash
            );
        }
        $hours = $this->translate('label_hours');
        $result .= ' ' . $hours;

        return $result;
    }

    /**
     * Checks whether there's a time set (begin time != 00:00)
     * If there's no date/time set, the result will be false.
     *
     * @return bool true if we have a begin time, false otherwise
     */
    public function hasTime(): bool
    {
        $beginTime = \strftime('%H:%M', $this->getBeginDateAsTimestamp());

        return $this->hasDate() && $beginTime !== '00:00';
    }

    /**
     * Checks whether there's an end time set (end time != 00:00)
     * If there's no end date/time set, the result will be false.
     *
     * @return bool true if we have an end time, false otherwise
     */
    public function hasEndTime(): bool
    {
        $endTime = strftime('%H:%M', $this->getEndDateAsTimestamp());

        return $this->hasEndDate() && $endTime !== '00:00';
    }

    /**
     * Returns our begin date and time as a UNIX timestamp.
     *
     * @return int our begin date and time as a UNIX timestamp or 0 if we don't have a begin date
     */
    public function getBeginDateAsTimestamp(): int
    {
        return $this->getRecordPropertyInteger('begin_date');
    }

    /**
     * Returns our end date and time as a UNIX timestamp.
     *
     * @return int our end date and time as a UNIX timestamp or 0 if
     *                 we don't have an end date
     */
    public function getEndDateAsTimestamp(): int
    {
        return $this->getRecordPropertyInteger('end_date');
    }

    /**
     * Gets our end date and time as a UNIX timestamp. If this event is
     * open-ended, midnight after the begin date and time is returned.
     * If we don't even have a begin date, 0 is returned.
     *
     * @return int our end date and time as a UNIX timestamp, 0 if we don't have a begin date
     */
    public function getEndDateAsTimestampEvenIfOpenEnded(): int
    {
        $result = 0;

        if ($this->hasBeginDate()) {
            if ($this->isOpenEnded()) {
                $splitBeginDate = getdate($this->getBeginDateAsTimestamp());
                $result = mktime(
                    0,
                    0,
                    0,
                    (int)$splitBeginDate['mon'],
                    (int)$splitBeginDate['mday'] + 1,
                    (int)$splitBeginDate['year']
                );
            } else {
                $result = $this->getEndDateAsTimestamp();
            }
        }

        return $result;
    }

    /**
     * Gets the seminar room (not the site).
     *
     * @return string the seminar room (may be empty)
     */
    public function getRoom(): string
    {
        return $this->getRecordPropertyString('room');
    }

    /**
     * Checks whether we have a room set.
     *
     * @return bool true if we have a non-empty room, false otherwise.
     */
    public function hasRoom(): bool
    {
        return $this->hasRecordPropertyString('room');
    }

    /**
     * Checks whether this time span is open-ended.
     *
     * A time span is considered to be open-ended if it does not have an end
     * date.
     *
     * @return bool true if this time span is open-ended, false otherwise
     */
    public function isOpenEnded(): bool
    {
        return !$this->hasEndDate();
    }

    /**
     * Checks whether we have a place (or places) set.
     *
     * @return bool true if we have a non-empty places list, false otherwise
     */
    public function hasPlace(): bool
    {
        return $this->hasRecordPropertyInteger('place');
    }

    /**
     * Gets the number of places associated with this record.
     *
     * @return int the number of places associated with this record, will be >= 0
     */
    public function getNumberOfPlaces(): int
    {
        return $this->getRecordPropertyInteger('place');
    }

    /**
     * Gets our place(s) as plain text (just the places name).
     * Returns a localized string "will be announced" if the time slot has no
     * place set.
     *
     * @return string our places or an empty string if the timespan has no places
     */
    abstract public function getPlaceShort(): string;

    /**
     * Gets the hook provider for the date and time span.
     *
     * @return HookProvider
     */
    protected function getDateTimeSpanHookProvider(): HookProvider
    {
        if (!$this->dateTimeSpanHookProvider instanceof HookProvider) {
            $this->dateTimeSpanHookProvider = GeneralUtility::makeInstance(
                HookProvider::class,
                DateTimeSpan::class
            );
        }

        return $this->dateTimeSpanHookProvider;
    }
}
