<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\BackEndUser as OelibBackEndUser;

/**
 * This class represents a back-end user.
 */
class BackEndUser extends OelibBackEndUser
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
     * Returns the PID for newly created records of the given type.
     *
     * This will be the first set PID found in the user's groups.
     *
     * @param string $type the type of the record, must be "event", "registration" or "auxiliary"
     *
     * @return int the PID for newly created records, will be 0 if no group
     *                 has a PID set for new records of the given type
     *
     * @throws \InvalidArgumentException
     */
    private function getRecordFolderFromGroup(string $type): int
    {
        $groups = $this->getAllGroups();
        if ($groups->isEmpty()) {
            return 0;
        }

        $result = 0;

        /** @var BackEndUserGroup $group */
        foreach ($groups as $group) {
            switch ($type) {
                case 'event':
                    $recordFolderPid = $group->getEventFolder();
                    break;
                case 'registration':
                    $recordFolderPid = $group->getRegistrationFolder();
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
