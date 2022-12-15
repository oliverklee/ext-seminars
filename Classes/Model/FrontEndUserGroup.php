<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\FrontEndUserGroup as OelibFrontEndUserGroup;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a front-end user group.
 */
class FrontEndUserGroup extends OelibFrontEndUserGroup implements Titled
{
    /**
     * Returns the PID where to store the auxiliary records created by this
     * front-end user group.
     *
     * @return int the PID where to store the auxiliary records created by
     *                 this front-end user group, will be 0 if no PID is set
     */
    public function getAuxiliaryRecordsPid(): int
    {
        return $this->getAsInteger('tx_seminars_auxiliary_records_pid');
    }

    public function hasAuxiliaryRecordsPid(): bool
    {
        return $this->hasInteger('tx_seminars_auxiliary_records_pid');
    }

    /**
     * Checks whether this user group has a storage PID for event records set.
     *
     * @return bool TRUE if this user group has a event storage PID, FALSE otherwise
     */
    public function hasEventRecordPid(): bool
    {
        return $this->hasInteger('tx_seminars_events_pid');
    }

    /**
     * Gets this user group's storage PID for event records.
     *
     * @return int the PID for the storage of event records, will be zero if no PID has been set
     */
    public function getEventRecordPid(): int
    {
        return $this->getAsInteger('tx_seminars_events_pid');
    }

    /**
     * Gets this user group's assigned default categories.
     *
     * @return Collection<Category> the default categories assigned to this
     *                       group, will be empty if no default categories are
     *                       assigned to this group
     */
    public function getDefaultCategories(): Collection
    {
        /** @var Collection<Category> $categories */
        $categories = $this->getAsCollection('tx_seminars_default_categories');

        return $categories;
    }

    public function hasDefaultCategories(): bool
    {
        return !$this->getDefaultCategories()->isEmpty();
    }

    /**
     * Returns this user group's default organizer for the FE editor.
     */
    public function getDefaultOrganizer(): ?Organizer
    {
        /** @var Organizer|null $organizer */
        $organizer = $this->getAsModel('tx_seminars_default_organizer');

        return $organizer;
    }

    /**
     * Checks whether this user group has a default organizer set.
     */
    public function hasDefaultOrganizer(): bool
    {
        return $this->getDefaultOrganizer() !== null;
    }
}
