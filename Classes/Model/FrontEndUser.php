<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\BackEndUser as OelibBackEndUser;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;

/**
 * This class represents a front-end user.
 */
class FrontEndUser extends OelibFrontEndUser
{
    /**
     * Returns the publish setting for the user groups the user is assigned to.
     *
     * The function will always return PUBLISH_IMMEDIATELY if the user has no
     * groups.
     *
     * If the user has more than one group, the strictest setting of the groups
     * will be returned.
     *
     * @return int one of the class constants
     *                 FrontEndUserGroup::PUBLISH_IMMEDIATELY,
     *                 FrontEndUserGroup::PUBLISH_HIDE_NEW or
     *                 FrontEndUserGroup::PUBLISH_HIDE_EDITED
     */
    public function getPublishSetting(): int
    {
        $userGroups = $this->getUserGroups();
        if ($userGroups->isEmpty()) {
            return FrontEndUserGroup::PUBLISH_IMMEDIATELY;
        }

        $result = FrontEndUserGroup::PUBLISH_IMMEDIATELY;

        /** @var FrontEndUserGroup $userGroup */
        foreach ($userGroups as $userGroup) {
            $groupPermissions = $userGroup->getPublishSetting();

            $result = ($groupPermissions > $result) ? $groupPermissions : $result;
        }

        return $result;
    }

    /**
     * Returns the PID where to store the auxiliary records created by this
     * front-end user.
     *
     * The PID is retrieved from the first user group which has a PID set.
     *
     * @return int the PID where to store auxiliary records created by this
     *                 front-end user, will be 0 if no PID is set
     */
    public function getAuxiliaryRecordsPid(): int
    {
        if ($this->getUserGroups()->isEmpty()) {
            return 0;
        }

        $auxiliaryRecordsPid = 0;

        /** @var FrontEndUserGroup $userGroup */
        foreach ($this->getUserGroups() as $userGroup) {
            if ($userGroup->hasAuxiliaryRecordsPid()) {
                $auxiliaryRecordsPid = $userGroup->getAuxiliaryRecordsPid();
                break;
            }
        }

        return $auxiliaryRecordsPid;
    }

    /**
     * Returns the reviewer set in the groups of this user.
     *
     * Will return the first reviewer found.
     */
    public function getReviewerFromGroup(): ?OelibBackEndUser
    {
        $result = null;

        /** @var FrontEndUserGroup $userGroup */
        foreach ($this->getUserGroups() as $userGroup) {
            if ($userGroup->hasReviewer()) {
                $result = $userGroup->getReviewer();
                break;
            }
        }

        return $result;
    }

    /**
     * Returns the PID where to store the event records for this user.
     *
     * This will return the first PID found for events in this user's groups.
     *
     * @return int the PID for the event records to store, will be 0 if no
     *                 event record PID has been set in any of this user's
     *                 groups
     */
    public function getEventRecordsPid(): int
    {
        if ($this->getUserGroups()->isEmpty()) {
            return 0;
        }

        $eventRecordPid = 0;

        /** @var FrontEndUserGroup $userGroup */
        foreach ($this->getUserGroups() as $userGroup) {
            if ($userGroup->hasEventRecordPid()) {
                $eventRecordPid = $userGroup->getEventRecordPid();
                break;
            }
        }

        return $eventRecordPid;
    }

    /**
     * Returns all default categories assigned to this user's groups.
     *
     * @return Collection<Category> the categories assigned to this user's groups, will
     *                       be empty if no default categories have been assigned
     *                       to any of the user's groups
     */
    public function getDefaultCategoriesFromGroup(): Collection
    {
        /** @var Collection<Category> $categories */
        $categories = new Collection();

        /** @var FrontEndUserGroup $group */
        foreach ($this->getUserGroups() as $group) {
            if ($group->hasDefaultCategories()) {
                $categories->append($group->getDefaultCategories());
            }
        }

        return $categories;
    }

    /**
     * Checks whether this user's groups have any default categories.
     */
    public function hasDefaultCategories(): bool
    {
        return !$this->getDefaultCategoriesFromGroup()->isEmpty();
    }

    /**
     * Returns all default organizers assigned to this user's groups.
     *
     * @return Collection<Organizer> the organizers assigned to this user's groups, will
     *                       be empty if no default organizers have been assigned
     *                       to any of the user's groups
     */
    public function getDefaultOrganizers(): Collection
    {
        /** @var Collection<Organizer> $organizers */
        $organizers = new Collection();

        /** @var FrontEndUserGroup $group */
        foreach ($this->getUserGroups() as $group) {
            if ($group->hasDefaultOrganizer()) {
                $organizers->add($group->getDefaultOrganizer());
            }
        }

        return $organizers;
    }

    public function hasDefaultOrganizers(): bool
    {
        return !$this->getDefaultOrganizers()->isEmpty();
    }

    /**
     * Gets the registration record for which this user is related to as "additional registered person".
     */
    public function getRegistration(): ?Registration
    {
        /** @var Registration|null $registration */
        $registration = $this->getAsModel('tx_seminars_registration');

        return $registration;
    }

    /**
     * Sets the registration record for which this user is related to as "additional registered person".
     */
    public function setRegistration(?Registration $registration = null): void
    {
        $this->set('tx_seminars_registration', $registration);
    }
}
