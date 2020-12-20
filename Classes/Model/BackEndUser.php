<?php

declare(strict_types=1);

/**
 * This class represents a back-end user.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Model_BackEndUser extends \Tx_Oelib_Model_BackEndUser
{
    /**
     * Returns the PID for newly created event records.
     *
     * This will be the first set PID found in the user's groups.
     *
     * @return int the PID for newly created event records, will be 0 if no
     *                 group has a PID set for new event records
     */
    public function getEventFolderFromGroup(): int
    {
        return $this->getRecordFolderFromGroup('event');
    }

    /**
     * Returns the PID for newly created registration records.
     *
     * This will be the first set PID found in the user's groups.
     *
     * @return int the PID for newly created registration records, will be
     *                 0 if no group has a PID set for new registration records
     */
    public function getRegistrationFolderFromGroup(): int
    {
        return $this->getRecordFolderFromGroup('registration');
    }

    /**
     * Returns the PID for newly created auxiliary records.
     *
     * This will be the first set PID found in the user's groups.
     *
     * @return int the PID for newly created auxiliary records, will be
     *                 0 if no group has a PID set for new auxiliary records
     */
    public function getAuxiliaryRecordsFolder(): int
    {
        return $this->getRecordFolderFromGroup('auxiliary');
    }

    /**
     * Returns the PID for newly created records of the given type.
     *
     * This will be the first set PID found in the user's groups.
     *
     * @param string $type
     *        the type of the record, must be "event", "registration" or
     *        "auxiliary"
     *
     * @return int the PID for newly created records, will be 0 if no group
     *                 has a PID set for new records of the given type
     */
    private function getRecordFolderFromGroup(string $type): int
    {
        $groups = $this->getAllGroups();
        if ($groups->isEmpty()) {
            return 0;
        }

        $result = 0;

        /** @var \Tx_Seminars_Model_BackEndUserGroup $group */
        foreach ($groups as $group) {
            switch ($type) {
                case 'event':
                    $recordFolderPid = $group->getEventFolder();
                    break;
                case 'registration':
                    $recordFolderPid = $group->getRegistrationFolder();
                    break;
                case 'auxiliary':
                    $recordFolderPid = $group->getAuxiliaryRecordFolder();
                    break;
                default:
                    throw new \InvalidArgumentException(
                        'The given record folder type "' . $type . '" was not valid.',
                        1333296088
                    );
            }

            if ($recordFolderPid > 0) {
                $result = $recordFolderPid;
                break;
            }
        }

        return $result;
    }
}
