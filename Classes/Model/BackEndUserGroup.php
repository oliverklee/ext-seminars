<?php

declare(strict_types=1);

use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a back-end user group.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Model_BackEndUserGroup extends \Tx_Oelib_Model_BackEndUserGroup implements Titled
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

    /**
     * Returns the PID for the storage of auxiliary records.
     *
     * Auxiliary records are all seminars record types with the exception of
     * events and registrations.
     *
     * @return int the PID for the storage of new auxiliary records, will
     *                 be 0 if no PID has been set
     */
    public function getAuxiliaryRecordFolder(): int
    {
        return $this->getAsInteger('tx_seminars_auxiliaries_folder');
    }
}
