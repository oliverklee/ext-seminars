<?php

/**
 * This class provides the access check for the CSV export of events in the back end.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Csv_BackEndEventAccessCheck extends Tx_Seminars_Csv_AbstractBackEndAccessCheck
{
    /**
     * @var string
     */
    const TABLE_NAME_EVENTS = 'tx_seminars_seminars';

    /**
     * Checks whether the logged-in user (if any) in the current environment has access to a CSV export.
     *
     * @return bool whether the logged-in user (if any) in the current environment has access to a CSV export.
     */
    public function hasAccess()
    {
        return $this->canAccessTableAndPage(self::TABLE_NAME_EVENTS, $this->getPageUid());
    }
}
