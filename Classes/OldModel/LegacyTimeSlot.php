<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

use OliverKlee\Seminars\Bag\SpeakerBag;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a time slot.
 */
class LegacyTimeSlot extends AbstractTimeSpan
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static string $tableName = 'tx_seminars_timeslots';

    /**
     * Creates and returns a speaker bag.
     */
    private function getSpeakerBag(): SpeakerBag
    {
        return GeneralUtility::makeInstance(
            SpeakerBag::class,
            'tx_seminars_timeslots_speakers_mm.uid_local = ' . $this->getUid() . ' AND uid = uid_foreign',
            'tx_seminars_timeslots_speakers_mm',
            '',
            'sorting'
        );
    }

    /**
     * Gets the speakers of the time slot as a plain-text comma-separated list.
     *
     * @return string the comma-separated plain text list of speakers (or '' if there was an error)
     */
    public function getSpeakersShortCommaSeparated(): string
    {
        $result = [];
        /** @var LegacySpeaker $speaker */
        foreach ($this->getSpeakerBag() as $speaker) {
            $result[] = $speaker->getTitle();
        }

        return \implode(', ', $result);
    }

    /**
     * Gets our place as plain text (just the name).
     * Returns a localized string "will be announced" if the time slot has no place set.
     *
     * @return string our places or an empty string if this timeslot lot has no place assigned
     */
    public function getPlaceShort(): string
    {
        if (!$this->hasPlace()) {
            return '';
        }

        $table = 'tx_seminars_sites';
        $row = self::getConnectionForTable($table)
            ->select(['title'], $table, ['uid' => $this->getPlace()])->fetchAssociative();

        return \is_array($row) ? $row['title'] : '';
    }

    /**
     * Gets the place UID.
     */
    public function getPlace(): int
    {
        return $this->getRecordPropertyInteger('place');
    }

    /**
     * Gets the entry date and time as a formatted date. If the begin date of
     * this time slot is on the same day as the entry date, only the time will be returned.
     *
     * @return string the entry date and time (or an empty string if no entry date is set)
     */
    public function getEntryDate(): string
    {
        if (!$this->hasEntryDate()) {
            return '';
        }

        $beginDate = $this->getBeginDateAsTimestamp();
        $entryDate = $this->getRecordPropertyInteger('entry_date');

        if (\date('Y-m-d', $entryDate) !== \date('Y-m-d', $beginDate)) {
            $dateFormat = $this->getDateFormat() . ' ';
        } else {
            $dateFormat = '';
        }
        $dateFormat .= $this->getTimeFormat();

        return \date($dateFormat, $entryDate);
    }

    public function hasEntryDate(): bool
    {
        return $this->hasRecordPropertyInteger('entry_date');
    }
}
