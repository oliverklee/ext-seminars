<?php

/**
 * This builder class creates customized organizer bag objects.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_BagBuilder_Organizer extends Tx_Seminars_BagBuilder_Abstract
{
    /**
     * @var string class name of the bag class that will be built
     */
    protected $bagClassName = Tx_Seminars_Bag_Organizer::class;

    /**
     * @var string the table name of the bag to build
     */
    protected $tableName = 'tx_seminars_organizers';

    /**
     * Limits the bag to contain only organizers of the event given in the
     * parameter $eventUid (must be a single or date event as topic events don't
     * have any organizers).
     *
     * @param int $eventUid the event UID to limit the organizers for, must be > 0
     *
     * @return void
     */
    public function limitToEvent($eventUid)
    {
        if ($eventUid <= 0) {
            throw new InvalidArgumentException('The parameter $eventUid must be > 0.', 1333292898);
        }

        $this->whereClauseParts['event'] = 'EXISTS (' .
            'SELECT * FROM tx_seminars_seminars_organizers_mm' .
            ' WHERE uid_local = ' . $eventUid . ' AND uid_foreign = ' .
            'tx_seminars_organizers.uid)';

        $this->orderBy = '(SELECT sorting ' .
            'FROM tx_seminars_seminars_organizers_mm WHERE uid_local = ' .
            $eventUid . ' AND uid_foreign = tx_seminars_organizers.uid)';
    }
}
