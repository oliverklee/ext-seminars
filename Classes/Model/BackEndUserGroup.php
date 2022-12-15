<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\BackEndUserGroup as OelibBackEndUserGroup;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a back-end user group.
 */
class BackEndUserGroup extends OelibBackEndUserGroup implements Titled
{
    /**
     * Returns the PID for the storage of new events.
     *
     * @return int the PID for the storage of new events, will be 0 if no
     *                 PID has been set
     */
    public function getEventFolder(): int
    {
        return $this->getAsInteger('tx_seminars_events_folder');
    }

    /**
     * Returns the PID for the storage of new registrations.
     *
     * @return int the PID for the storage of new registrations, will be 0
     *                 if no PID has been set
     */
    public function getRegistrationFolder(): int
    {
        return $this->getAsInteger('tx_seminars_registrations_folder');
    }
}
