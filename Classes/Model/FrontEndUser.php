<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;

/**
 * This class represents a front-end user.
 */
class FrontEndUser extends OelibFrontEndUser
{
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
