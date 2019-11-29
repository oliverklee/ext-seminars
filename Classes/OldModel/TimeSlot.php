<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a time slot.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_OldModel_TimeSlot extends \Tx_Seminars_OldModel_AbstractTimeSpan
{
    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static $tableName = 'tx_seminars_timeslots';

    /**
     * Creates and returns a speakerbag object.
     *
     * @return \Tx_Seminars_Bag_Speaker a speakerbag object
     */
    private function getSpeakerBag(): \Tx_Seminars_Bag_Speaker
    {
        /** @var \Tx_Seminars_Bag_Speaker $bag */
        $bag = GeneralUtility::makeInstance(
            \Tx_Seminars_Bag_Speaker::class,
            'tx_seminars_timeslots_speakers_mm.uid_local = ' . $this->getUid() . ' AND uid = uid_foreign',
            'tx_seminars_timeslots_speakers_mm',
            '',
            'sorting'
        );

        return $bag;
    }

    /**
     * @return int[]
     */
    public function getSpeakersUids(): array
    {
        $table = 'tx_seminars_timeslots_speakers_mm';
        $rows = self::getConnectionForTable($table)
            ->select(['uid_foreign'], $table, ['uid_local' => $this->getUid()])->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = (int)$row['uid_foreign'];
        }

        return $result;
    }

    /**
     * Gets the speakers of the time slot as a plain-text comma-separated list.
     *
     * @return string the comma-separated plain text list of speakers (or '' if there was an error)
     */
    public function getSpeakersShortCommaSeparated(): string
    {
        $result = [];
        /** @var \Tx_Seminars_OldModel_Speaker $speaker */
        foreach ($this->getSpeakerBag() as $speaker) {
            $result[] = $speaker->getTitle();
        }

        return \implode(', ', $result);
    }

    /**
     * Gets our place as plain text (just the name).
     * Returns a localized string "will be announced" if the time slot has no place set.
     *
     * @return string our places or a localized string "will be announced" if this times lot has no place assigned
     */
    public function getPlaceShort(): string
    {
        if (!$this->hasPlace()) {
            return $this->translate('message_willBeAnnounced');
        }

        $table = 'tx_seminars_sites';
        $row = self::getConnectionForTable($table)
            ->select(['title'], $table, ['uid' => $this->getPlace()])->fetch();

        return \is_array($row) ? $row['title'] : '';
    }

    /**
     * Gets the place UID.
     *
     * @return int
     */
    public function getPlace(): int
    {
        return $this->getRecordPropertyInteger('place');
    }

    /**
     * Gets the entry date and time as a formatted date. If the begin date of
     * this time slot is on the same day as the entry date, only the time will be returned.
     *
     * @return string the entry date and time (or the localized string "will be announced" if no entry date is set)
     */
    public function getEntryDate(): string
    {
        if (!$this->hasEntryDate()) {
            return $this->translate('message_willBeAnnounced');
        }

        $beginDate = $this->getBeginDateAsTimestamp();
        $entryDate = $this->getRecordPropertyInteger('entry_date');

        if (\strftime('%d-%m-%Y', $entryDate) !== \strftime('%d-%m-%Y', $beginDate)) {
            $dateFormat = $this->getConfValueString('dateFormatYMD') . ' ';
        } else {
            $dateFormat = '';
        }
        $dateFormat .= $this->getConfValueString('timeFormat');

        return \strftime($dateFormat, $entryDate);
    }

    /**
     * Checks whether the time slot has a entry date set.
     *
     * @return bool
     */
    public function hasEntryDate(): bool
    {
        return $this->hasRecordPropertyInteger('entry_date');
    }

    /**
     * Returns an associative array, containing field name/value pairs that need
     * to be updated in the database. Update means "set the title" so far.
     *
     * @return string[] data to update the database entry of the time slot, might be empty
     */
    public function getUpdateArray(): array
    {
        return ['title' => \html_entity_decode($this->getDate(), ENT_QUOTES | ENT_HTML5, 'utf-8')];
    }
}
