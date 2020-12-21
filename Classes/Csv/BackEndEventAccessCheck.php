<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

/**
 * This class provides the access check for the CSV export of events in the back end.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndEventAccessCheck extends AbstractBackEndAccessCheck
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
    public function hasAccess(): bool
    {
        return $this->canAccessTableAndPage(self::TABLE_NAME_EVENTS, $this->getPageUid());
    }
}
