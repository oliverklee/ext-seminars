<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

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
}
