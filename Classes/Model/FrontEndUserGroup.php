<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\FrontEndUserGroup as OelibFrontEndUserGroup;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a front-end user group.
 */
class FrontEndUserGroup extends OelibFrontEndUserGroup implements Titled
{
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
}
