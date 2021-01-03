<?php

declare(strict_types=1);

use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Language\Translator;
use OliverKlee\Oelib\Language\TranslatorRegistry;

/**
 * This class represents a view helper for rendering a countdown.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_ViewHelper_Countdown
{
    /**
     * @var Translator
     */
    protected $translator = null;

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->translator = TranslatorRegistry::get('seminars');
    }

    /**
     * Returns a localized string representing an amount of seconds in words.
     * For example:
     * 150000 seconds -> "1 day"
     * 200000 seconds -> "2 days"
     * 50000 seconds -> "13 hours"
     * The function uses localized strings and also looks for proper usage of singular/plural.
     *
     * @param int $targetTime the target UNIX timestamp to count up to, must be >= 0
     *
     * @return string a localized string representing the time left until the event starts
     */
    public function render(int $targetTime): string
    {
        $seconds = $targetTime - (int)$GLOBALS['SIM_ACCESS_TIME'];

        if ($seconds >= Time::SECONDS_PER_DAY) {
            $result = $this->getAsDays($seconds);
        } elseif ($seconds >= Time::SECONDS_PER_HOUR) {
            $result = $this->getAsHours($seconds);
        } elseif ($seconds >= Time::SECONDS_PER_MINUTE) {
            $result = $this->getAsMinutes($seconds);
        } else {
            $result = $this->getAsSeconds($seconds);
        }

        return $result;
    }

    /**
     * Returns the given duration in days.
     *
     * @param int $seconds the duration in seconds, must be >= 0
     *
     * @return string the duration in days
     */
    protected function getAsDays(int $seconds): string
    {
        $countdownValue = (int)\round($seconds / Time::SECONDS_PER_DAY);
        if ($countdownValue > 1 || $countdownValue === 0) {
            $countdownText = $this->translator->translate('countdown_days_plural');
        } else {
            $countdownText = $this->translator->translate('countdown_days_singular');
        }

        return $this->getFormattedMessage($countdownValue, $countdownText);
    }

    /**
     * Returns the given duration in hours.
     *
     * @param int $seconds the duration in seconds, must be >= 0
     *
     * @return string the duration in hours
     */
    protected function getAsHours(int $seconds): string
    {
        $countdownValue = (int)\round($seconds / Time::SECONDS_PER_HOUR);
        if ($countdownValue > 1 || $countdownValue === 0) {
            $countdownText = $this->translator->translate('countdown_hours_plural');
        } else {
            $countdownText = $this->translator->translate('countdown_hours_singular');
        }

        return $this->getFormattedMessage($countdownValue, $countdownText);
    }

    /**
     * Returns the given duration in minutes.
     *
     * @param int $seconds the duration in seconds, must be >= 0
     *
     * @return string the duration in minutes
     */
    protected function getAsMinutes(int $seconds): string
    {
        $countdownValue = (int)\round($seconds / Time::SECONDS_PER_MINUTE);
        if ($countdownValue > 1 || $countdownValue === 0) {
            $countdownText = $this->translator->translate('countdown_minutes_plural');
        } else {
            $countdownText = $this->translator->translate('countdown_minutes_singular');
        }

        return $this->getFormattedMessage($countdownValue, $countdownText);
    }

    /**
     * Returns the given duration in seconds.
     *
     * @param int $seconds the duration in seconds, must be >= 0
     *
     * @return string the duration in seconds
     */
    protected function getAsSeconds(int $seconds): string
    {
        $countdownValue = $seconds;
        $countdownText = $this->translator->translate('countdown_seconds_plural');

        return $this->getFormattedMessage($countdownValue, $countdownText);
    }

    /**
     * Returns the formatted countdown message using $countdownValue and $countdownText.
     *
     * @param int $countdownValue
     * @param string $countdownText
     *
     * @return string the formatted countdown message
     */
    protected function getFormattedMessage(int $countdownValue, string $countdownText): string
    {
        return \sprintf($this->translator->translate('message_countdown'), $countdownValue, $countdownText);
    }
}
