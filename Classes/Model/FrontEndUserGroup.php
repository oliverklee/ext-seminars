<?php

declare(strict_types=1);

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Model\FrontEndUserGroup as OelibFrontEndUserGroup;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a front-end user group.
 */
class Tx_Seminars_Model_FrontEndUserGroup extends OelibFrontEndUserGroup implements Titled
{
    /**
     * @var int the publish setting to immediately publish all events edited
     */
    const PUBLISH_IMMEDIATELY = 0;

    /**
     * @var int the publish setting for hiding only new events created
     */
    const PUBLISH_HIDE_NEW = 1;

    /**
     * @var int the publish setting for hiding newly created and edited
     *              events
     */
    const PUBLISH_HIDE_EDITED = 2;

    /**
     * Returns the setting for event publishing.
     *
     * If no publish settings have been set, PUBLISH_IMMEDIATELY is returned.
     *
     * @return int the class constants PUBLISH_IMMEDIATELY, PUBLISH_HIDE_NEW
     *                 or PUBLISH_HIDE_EDITED
     */
    public function getPublishSetting(): int
    {
        return $this->getAsInteger('tx_seminars_publish_events');
    }

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

    /**
     * Returns whether this user group has a PID for auxiliary records set.
     *
     * @return bool TRUE if this user group has PID for auxiliary records set,
     *                 FALSE otherwise
     */
    public function hasAuxiliaryRecordsPid(): bool
    {
        return $this->hasInteger('tx_seminars_auxiliary_records_pid');
    }

    /**
     * Checks whether this user group has a reviewer set.
     *
     * @return bool TRUE if a reviewer is set, FALSE otherwise
     */
    public function hasReviewer(): bool
    {
        return $this->getReviewer() !== null;
    }

    public function getReviewer(): ?BackEndUser
    {
        /** @var BackEndUser|null $reviewer */
        $reviewer = $this->getAsModel('tx_seminars_reviewer');

        return $reviewer;
    }

    /**
     * Checks whether this user group has a storage PID for event records set.
     *
     * @return bool TRUE if this user group has a event storage PID, FALSE
     *                  otherwise
     */
    public function hasEventRecordPid(): bool
    {
        return $this->hasInteger('tx_seminars_events_pid');
    }

    /**
     * Gets this user group's storage PID for event records.
     *
     * @return int the PID for the storage of event records, will be zero
     *                 if no PID has been set
     */
    public function getEventRecordPid(): int
    {
        return $this->getAsInteger('tx_seminars_events_pid');
    }

    /**
     * Gets this user group's assigned default categories.
     *
     * @return Collection the list of default categories assigned to this
     *                       group, will be empty if no default categories are
     *                       assigned to this group
     */
    public function getDefaultCategories(): Collection
    {
        return $this->getAsCollection('tx_seminars_default_categories');
    }

    /**
     * Checks whether this user group has default categories assigned.
     *
     * @return bool TRUE if this group has at least one default category,
     *                 FALSE otherwise
     */
    public function hasDefaultCategories(): bool
    {
        return !$this->getDefaultCategories()->isEmpty();
    }

    /**
     * Returns this user group's default organizer for the FE editor.
     */
    public function getDefaultOrganizer(): ?\Tx_Seminars_Model_Organizer
    {
        /** @var \Tx_Seminars_Model_Organizer|null $organizer */
        $organizer = $this->getAsModel('tx_seminars_default_organizer');

        return $organizer;
    }

    /**
     * Checks whether this user group has a default organizer set.
     *
     * @return bool
     */
    public function hasDefaultOrganizer(): bool
    {
        return $this->getDefaultOrganizer() !== null;
    }
}
